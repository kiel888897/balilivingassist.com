<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCustomer();

        $cartItems = $request->user()->cartItems()
            ->with('product')
            ->orderBy('id')
            ->get();
        $productIds = $cartItems->pluck('product_id')->unique()->values();
        $products = Product::with([
            'category',
            'images',
            'variants.values.attribute',
            'variants.priceTiers',
            'attributes',
        ])->whereIn('id', $productIds)->get()->keyBy('id');

        $lines = [];
        $subtotal = 0;
        $selectedItemCount = 0;

        foreach ($cartItems as $cartItem) {
            $product = $products->get($cartItem->product_id);
            if (!$product) {
                $cartItem->delete();
                continue;
            }

            $variant = $cartItem->variant_id
                ? $product->variants->firstWhere('id', $cartItem->variant_id)
                : null;
            $quantity = $cartItem->quantity;
            $hasVariants = $product->variants->isNotEmpty();
            $isRental = $cartItem->rental_period !== '';
            if ($isRental) {
                $unitPrice = $product->rentalPriceForPeriod($cartItem->rental_period);
                $available = $product->is_active
                    && $product->for_rental
                    && $cartItem->variant_id === null
                    && $cartItem->variant_key === 0
                    && $unitPrice !== null
                    && $unitPrice > 0;
            } else {
                $available = $product->is_active
                    && $product->for_sale
                    && ($cartItem->variant_id ? $variant !== null : !$hasVariants)
                    && ($variant === null
                        ? ($cartItem->variant_key === 0 && $product->sale_price !== null)
                        : $variant->stock_quantity >= $quantity);
                $unitPrice = $variant
                    ? (float) $variant->unitPriceForQuantity($quantity)
                    : ($cartItem->variant_key === 0 ? (float) $product->sale_price : null);
            }
            $lineTotal = $unitPrice === null ? null : round($unitPrice * $quantity, 2);

            if ($available && $lineTotal !== null && $cartItem->is_selected) {
                $subtotal = round($subtotal + $lineTotal, 2);
                $selectedItemCount++;
            }

            $lines[] = [
                'key' => $cartItem->id,
                'product' => $product,
                'variant' => $variant,
                'rental_period' => $isRental ? $cartItem->rental_period : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'available' => $available,
                'is_selected' => $cartItem->is_selected,
                'image_path' => $product->primary_image_path,
            ];
        }

        return view('cart', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'selectedItemCount' => $selectedItemCount,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'rental_period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        $product = Product::with(['attributes', 'variants.values', 'variants.priceTiers'])
            ->whereKey($data['product_id'])
            ->firstOrFail();
        $rentalPeriod = $data['rental_period'] ?? '';

        if (!Auth::check()) {
            $request->session()->put('url.intended', route('product.show', [
                'slug' => $product->slug,
                'from' => $rentalPeriod !== '' ? 'rental' : null,
            ]));

            return redirect()->route('login')->with('status', __('site.cart.login_required'));
        }
        $this->ensureCustomer();

        $variantId = $data['variant_id'] ?? null;
        $cartKey = $variantId ?: 0;
        $userId = $request->user()->id;
        $quantity = DB::transaction(function () use ($request, $product, $variantId, $cartKey, $rentalPeriod, $data, $userId) {
            $request->user()->newQuery()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $cartItem = $request->user()->cartItems()
                ->where('product_id', $product->id)
                ->where('variant_key', $cartKey)
                ->where('rental_period', $rentalPeriod)
                ->lockForUpdate()
                ->first();
            $quantity = $data['quantity'] + ($cartItem ? $cartItem->quantity : 0);
            if ($quantity > 9999) {
                return null;
            }

            $lineError = $this->lineError($product, $variantId, $quantity, $rentalPeriod);
            if ($lineError !== null) {
                return $lineError;
            }

            if ($cartItem) {
                $cartItem->update([
                    'variant_id' => $variantId,
                    'rental_period' => $rentalPeriod,
                    'quantity' => $quantity,
                ]);
            } else {
                $request->user()->cartItems()->create([
                    'product_id' => $product->id,
                    'variant_key' => $cartKey,
                    'variant_id' => $variantId,
                    'rental_period' => $rentalPeriod,
                    'quantity' => $quantity,
                ]);
            }

            return $quantity;
        });

        if ($quantity === null) {
            return back()->withErrors(['cart' => __('site.cart.quantity_limit')]);
        }
        if (is_string($quantity)) {
            return back()->withErrors(['cart' => $quantity]);
        }

        return back()->with('status', __('site.cart.added'));
    }

    public function update(Request $request)
    {
        $this->ensureCustomer();
        $data = $request->validate([
            'cart_item_id' => ['required', 'integer'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:9999'],
            'is_selected' => ['sometimes', 'required', 'boolean'],
        ]);

        $cartItem = $request->user()->cartItems()->whereKey($data['cart_item_id'])->first();
        if (!$cartItem) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('site.cart.item_not_found')], 404);
            }
            return back()->withErrors(['cart' => __('site.cart.item_not_found')]);
        }

        $product = Product::with(['attributes', 'variants.values', 'variants.priceTiers'])
            ->whereKey($cartItem->product_id)
            ->firstOrFail();
        $variantId = $cartItem->variant_id;
        $quantity = $data['quantity'] ?? $cartItem->quantity;
        $isSelected = array_key_exists('is_selected', $data)
            ? (bool) $data['is_selected']
            : $cartItem->is_selected;

        if ((int) $quantity !== $cartItem->quantity || $isSelected) {
            $lineError = $this->lineError($product, $variantId, $quantity, $cartItem->rental_period);
            if ($lineError !== null) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => $lineError], 422);
                }
                return back()->withErrors(['cart' => $lineError]);
            }
        }

        $cartItem->update([
            'quantity' => $quantity,
            'is_selected' => $isSelected,
        ]);

        if ($request->expectsJson()) {
            $freshItem = $cartItem->fresh();
            $variant = $freshItem->variant_id
                ? $product->variants->firstWhere('id', $freshItem->variant_id)
                : null;
            $unitPrice = $freshItem->rental_period !== ''
                ? $product->rentalPriceForPeriod($freshItem->rental_period)
                : ($variant
                    ? (float) $variant->unitPriceForQuantity($freshItem->quantity)
                    : ($freshItem->variant_key === 0 ? (float) $product->sale_price : null));
            $lineTotal = $unitPrice === null ? null : round($unitPrice * $freshItem->quantity, 2);
            $summary = $this->selectedCartSummary($request->user());

            return response()->json([
                'cart_item_id' => $freshItem->id,
                'quantity' => $freshItem->quantity,
                'is_selected' => $freshItem->is_selected,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'selected_subtotal' => $summary['subtotal'],
                'selected_item_count' => $summary['count'],
            ]);
        }

        return redirect()->route('cart')->with('status', __('site.cart.updated'));
    }

    public function destroy(Request $request)
    {
        $this->ensureCustomer();
        $data = $request->validate([
            'cart_item_id' => ['required', 'integer'],
        ]);

        $request->user()->cartItems()->whereKey($data['cart_item_id'])->delete();

        return redirect()->route('cart')->with('status', __('site.cart.removed'));
    }

    private function ensureCustomer(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('customer') && !Auth::user()->hasPermissionTo('admin.access'), 403);
    }

    private function lineError(Product $product, ?int $variantId, int $quantity, string $rentalPeriod = ''): ?string
    {
        if ($rentalPeriod !== '') {
            $unitPrice = $product->rentalPriceForPeriod($rentalPeriod);
            if (!$product->is_active || !$product->for_rental || $variantId !== null || $unitPrice === null || $unitPrice <= 0) {
                return __('site.cart.rental_unavailable');
            }

            return null;
        }

        if (!$product->is_active || !$product->for_sale) {
            return __('site.cart.product_unavailable');
        }

        if ($product->variants->isEmpty() && $product->sale_price === null) {
            return __('site.cart.product_unavailable');
        }

        if ($product->attributes->isNotEmpty() && $product->variants->isEmpty()) {
            return __('site.cart.variant_unavailable');
        }

        if ($product->variants->isNotEmpty()) {
            $variant = $variantId === null ? null : $product->variants->firstWhere('id', $variantId);
            if (!$variant) {
                return __('site.cart.select_variant');
            }

            if ($variant->values->count() !== $product->attributes->count()) {
                return __('site.cart.variant_unavailable');
            }

            if ($variant->stock_quantity < $quantity) {
                return __('site.cart.stock_limit', ['stock' => $variant->stock_quantity]);
            }
        } elseif ($variantId !== null) {
            return __('site.cart.variant_unavailable');
        }

        return null;
    }

    private function selectedCartSummary($user): array
    {
        $subtotal = 0;
        $count = 0;
        $cartItems = $user->cartItems()->with('product.variants.priceTiers')->get();

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            if (!$cartItem->is_selected || !$product || !$product->is_active) {
                continue;
            }

            if ($cartItem->rental_period !== '') {
                $rentalPrice = $product->for_rental
                    ? $product->rentalPriceForPeriod($cartItem->rental_period)
                    : null;
                if ($rentalPrice !== null && $rentalPrice > 0 && $cartItem->variant_key === 0 && $cartItem->variant_id === null) {
                    $subtotal = round($subtotal + ($rentalPrice * $cartItem->quantity), 2);
                    $count++;
                }
                continue;
            }

            if (!$product->for_sale) {
                continue;
            }
            $variant = $cartItem->variant_id
                ? $product->variants->firstWhere('id', $cartItem->variant_id)
                : null;
            $hasVariants = $product->variants->isNotEmpty();
            if ($cartItem->variant_id && (!$variant || $variant->stock_quantity < $cartItem->quantity)) {
                continue;
            }
            if (!$cartItem->variant_id && ($hasVariants || $product->sale_price === null)) {
                continue;
            }

            $unitPrice = $variant
                ? (float) $variant->unitPriceForQuantity($cartItem->quantity)
                : (float) $product->sale_price;
            $subtotal = round($subtotal + ($unitPrice * $cartItem->quantity), 2);
            $count++;
        }

        return ['subtotal' => $subtotal, 'count' => $count];
    }
}
