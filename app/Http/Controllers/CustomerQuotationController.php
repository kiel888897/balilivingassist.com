<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerQuotationController extends Controller
{
    public function checkout(Request $request)
    {
        $this->ensureCustomer();
        $cartItems = $request->user()->cartItems()
            ->where('is_selected', true)
            ->with(['product.images', 'product.variants.values.attribute', 'product.variants.priceTiers'])
            ->orderBy('id')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => __('site.cart.select_items')]);
        }

        $lines = [];
        $subtotal = 0;
        foreach ($cartItems as $cartItem) {
            $line = $this->pricedCartLine($cartItem);
            if ($line === null) {
                return redirect()->route('cart')->withErrors(['cart' => __('site.cart.checkout_unavailable')]);
            }
            $subtotal = round($subtotal + $line['line_total'], 2);
            $lines[] = $line;
        }

        return view('customer.checkout', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'customer' => $request->user(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureCustomer();
        $data = $request->validate([
            'customer_phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\s().-]{7,32}$/'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $user = $request->user();

        $quotation = DB::transaction(function () use ($user, $data) {
            $cartItems = $user->cartItems()
                ->where('is_selected', true)
                ->with(['product.variants.values.attribute', 'product.variants.priceTiers'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                return null;
            }

            $lines = [];
            $subtotal = 0;
            foreach ($cartItems as $cartItem) {
                $line = $this->pricedCartLine($cartItem);
                if ($line === null) {
                    return null;
                }
                $lines[] = $line;
                $subtotal = round($subtotal + $line['line_total'], 2);
            }

            $quotation = Quotation::create([
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'customer_note' => $data['customer_note'] ?? null,
                'status' => 'new',
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'discount_amount' => 0,
                'total' => $subtotal,
            ]);
            $quotation->update([
                'quote_number' => sprintf('BLA-Q-%s-%06d', now()->format('ymd'), $quotation->id),
            ]);

            foreach ($lines as $index => $line) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $line['variant'] ? $line['variant']->id : null,
                    'item_type' => $line['item_type'],
                    'description' => $line['product']->name,
                    'sku' => $line['variant'] ? $line['variant']->sku : $line['product']->sku,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                    'options_snapshot' => $line['options'],
                    'sort_order' => $index,
                ]);
            }

            $user->update(['phone' => $data['customer_phone']]);
            QuotationRevision::create([
                'quotation_id' => $quotation->id,
                'user_id' => $user->id,
                'event' => 'submitted',
                'snapshot' => $this->snapshot($quotation->fresh('items')),
            ]);

            CartItem::whereIn('id', $cartItems->pluck('id'))->delete();

            return $quotation;
        });

        if (!$quotation) {
            return redirect()->route('cart')->withErrors(['cart' => __('site.cart.checkout_unavailable')]);
        }

        return redirect()->route('customer.quotations.show', $quotation)
            ->with('status', __('site.quotation.submitted'));
    }

    public function quotations(Request $request)
    {
        $this->ensureCustomer();
        $quotations = $request->user()->quotations()
            ->whereNotIn('status', Quotation::ORDER_STATUSES)
            ->withCount('items')
            ->latest()
            ->get();

        return view('customer.quotations.index', ['quotations' => $quotations]);
    }

    public function showQuotation(Request $request, Quotation $quotation)
    {
        $this->ensureCustomer();
        abort_unless($quotation->user_id === $request->user()->id, 404);
        $quotation->load('items');

        return view('customer.quotations.show', ['quotation' => $quotation]);
    }

    public function orders(Request $request)
    {
        $this->ensureCustomer();
        $orders = $request->user()->quotations()
            ->whereIn('status', Quotation::ORDER_STATUSES)
            ->withCount('items')
            ->latest()
            ->get();

        return view('customer.orders.index', ['orders' => $orders]);
    }

    public function showOrder(Request $request, Quotation $quotation)
    {
        $this->ensureCustomer();
        abort_unless($quotation->user_id === $request->user()->id && $quotation->isOrder(), 404);
        $quotation->load('items');

        return view('customer.quotations.show', ['quotation' => $quotation]);
    }

    private function ensureCustomer(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('customer') && !Auth::user()->hasPermissionTo('admin.access'), 403);
    }

    private function pricedCartLine(CartItem $cartItem): ?array
    {
        $product = $cartItem->product;
        if (!$product || !$product->is_active) {
            return null;
        }

        if ($cartItem->rental_period !== '') {
            $unitPrice = $product->for_rental
                ? $product->rentalPriceForPeriod($cartItem->rental_period)
                : null;
            if ($cartItem->variant_id !== null || $cartItem->variant_key !== 0 || $unitPrice === null || $unitPrice <= 0) {
                return null;
            }

            return [
                'cart_item' => $cartItem,
                'product' => $product,
                'variant' => null,
                'item_type' => 'rental',
                'quantity' => $cartItem->quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $cartItem->quantity, 2),
                'options' => [[
                    'attribute' => 'Rental period',
                    'value' => $cartItem->rental_period,
                ]],
            ];
        }

        if (!$product->for_sale) {
            return null;
        }

        $variant = $cartItem->variant_id
            ? $product->variants->firstWhere('id', $cartItem->variant_id)
            : null;
        if ($cartItem->variant_id && (!$variant || $variant->stock_quantity < $cartItem->quantity)) {
            return null;
        }
        if (!$cartItem->variant_id && ($product->variants->isNotEmpty() || $product->sale_price === null)) {
            return null;
        }

        $unitPrice = $variant
            ? (float) $variant->unitPriceForQuantity($cartItem->quantity)
            : (float) $product->sale_price;
        $options = $variant
            ? $variant->values->map(function ($value) {
                return [
                    'attribute' => $value->attribute->name,
                    'value' => $value->value,
                ];
            })->values()->all()
            : [];

        return [
            'cart_item' => $cartItem,
            'product' => $product,
            'variant' => $variant,
            'item_type' => 'product',
            'quantity' => $cartItem->quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $cartItem->quantity, 2),
            'options' => $options,
        ];
    }

    private function snapshot(Quotation $quotation): array
    {
        return [
            'quote_number' => $quotation->quote_number,
            'status' => $quotation->status,
            'customer_name' => $quotation->customer_name,
            'customer_phone' => $quotation->customer_phone,
            'shipping_address' => $quotation->shipping_address,
            'customer_note' => $quotation->customer_note,
            'admin_note' => $quotation->admin_note,
            'payment_instructions' => $quotation->payment_instructions,
            'subtotal' => $quotation->subtotal,
            'delivery_fee' => $quotation->delivery_fee,
            'discount_amount' => $quotation->discount_amount,
            'total' => $quotation->total,
            'items' => $quotation->items->map(function ($item) {
                return $item->only([
                    'item_type',
                    'description',
                    'sku',
                    'quantity',
                    'unit_price',
                    'line_total',
                    'options_snapshot',
                ]);
            })->all(),
        ];
    }
}
