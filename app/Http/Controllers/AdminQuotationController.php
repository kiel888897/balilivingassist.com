<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationRevision;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminQuotationController extends Controller
{
    public function index()
    {
        return view('admin.quotations.index', [
            'quotations' => Quotation::with('customer')->withCount('items')->latest()->get(),
            'newCount' => Quotation::where('status', 'new')->count(),
        ]);
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['items', 'revisions.editor']);

        return view('admin.quotations.edit', [
            'quotation' => $quotation,
            'products' => Product::with('variants')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('for_sale', true)->orWhere('for_rental', true);
                })
                ->orderBy('name')
                ->get(),
            'variants' => ProductVariant::where('is_active', true)->with(['product', 'values.attribute'])->orderBy('sku')->get(),
            'statuses' => Quotation::STATUSES,
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Quotation::STATUSES)],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:quotation_items,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.remove' => ['nullable', 'boolean'],
            'items.*.rental_period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
            'new_item_type' => ['nullable', Rule::in(['product', 'service', 'delivery', 'discount', 'custom'])],
            'new_product_id' => ['nullable', 'required_if:new_item_type,product', 'integer', 'exists:products,id'],
            'new_product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'new_service_id' => ['nullable', 'required_if:new_item_type,service', 'integer', 'exists:services,id'],
            'new_description' => ['nullable', 'string', 'max:255'],
            'new_quantity' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'new_unit_price' => ['nullable', 'required_with:new_item_type', 'numeric', 'min:0', 'max:9999999999.99'],
            'new_items' => ['nullable', 'array', 'max:50'],
            'new_items.*.type' => ['required', Rule::in(['product', 'rental', 'service', 'delivery', 'discount', 'custom'])],
            'new_items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'new_items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'new_items.*.description' => ['nullable', 'string', 'max:255'],
            'new_items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'new_items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'new_items.*.rental_period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        if ($data['status'] === 'payment_requested' && empty($data['payment_instructions'])) {
            throw ValidationException::withMessages([
                'payment_instructions' => 'Enter payment instructions before requesting payment.',
            ]);
        }

        $submittedItemIds = collect($data['items'])->pluck('id')->map(function ($id) {
            return (int) $id;
        });
        $ownedItemIds = $quotation->items()->whereIn('id', $submittedItemIds)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->sort()
            ->values();
        if ($submittedItemIds->sort()->values()->all() !== $ownedItemIds->all()) {
            throw ValidationException::withMessages([
                'items' => 'The quotation item list has changed. Reload the quotation and try again.',
            ]);
        }

        $this->validateNewItem($data);
        $newItems = collect($data['new_items'] ?? []);
        if (!empty($data['new_item_type'])) {
            $newItems->push([
                'type' => $data['new_item_type'],
                'product_id' => $data['new_product_id'] ?? null,
                'product_variant_id' => $data['new_product_variant_id'] ?? null,
                'service_id' => $data['new_service_id'] ?? null,
                'description' => $data['new_description'] ?? null,
                'quantity' => $data['new_quantity'] ?? 1,
                'unit_price' => $data['new_unit_price'] ?? null,
            ]);
        }
        $this->validateNewItems($newItems->values()->all());

        $quotationId = $quotation->id;
        DB::transaction(function () use ($data, $newItems, $quotationId) {
            $quotation = Quotation::whereKey($quotationId)->lockForUpdate()->firstOrFail();
            $quotation->load('items');
            $currentItemIds = $quotation->items->pluck('id')->map(function ($id) {
                return (int) $id;
            })->sort()->values()->all();
            $submittedItemIds = collect($data['items'])->pluck('id')->map(function ($id) {
                return (int) $id;
            })->sort()->values()->all();
            if ($currentItemIds !== $submittedItemIds) {
                throw ValidationException::withMessages([
                    'items' => 'The quotation changed while you were editing it. Reload the page and try again.',
                ]);
            }
            foreach ($data['items'] as $itemData) {
                $item = $quotation->items->firstWhere('id', $itemData['id']);
                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'A quotation item could not be found.',
                    ]);
                }
                if (!empty($itemData['remove'])) {
                    $item->delete();
                    continue;
                }

                $item->quantity = $itemData['quantity'];
                $item->description = $itemData['description'];
                $item->unit_price = $itemData['unit_price'];
                $item->line_total = $this->lineTotal(
                    $item->item_type,
                    $item->quantity,
                    (float) $item->unit_price
                );
                if ($item->item_type === 'rental') {
                    $period = $itemData['rental_period'] ?? null;
                    if (!in_array($period, ['daily', 'weekly', 'monthly'], true)) {
                        throw ValidationException::withMessages([
                            "items.{$item->id}.rental_period" => 'Choose a rental period.',
                        ]);
                    }
                    $item->options_snapshot = [[
                        'attribute' => 'Rental period',
                        'value' => $period,
                    ]];
                }
                $item->save();
            }

            foreach ($newItems as $newItem) {
                $this->addItem($quotation, $newItem);
            }
            if ($quotation->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'items' => 'A quotation must contain at least one item.',
                ]);
            }

            $quotation->shipping_address = $data['shipping_address'];
            $quotation->customer_note = $data['customer_note'] ?? null;
            $quotation->admin_note = $data['admin_note'] ?? null;
            $quotation->payment_instructions = $data['payment_instructions'] ?? null;
            $quotation->status = $data['status'];

            $totals = $this->totals($quotation->items()->get());
            if ($totals['discount_amount'] > $totals['subtotal'] + $totals['delivery_fee']) {
                throw ValidationException::withMessages([
                    'items' => 'The discount cannot be greater than the quotation value.',
                ]);
            }
            if ($totals['total'] > 9999999999.99) {
                throw ValidationException::withMessages([
                    'items' => 'The quotation total exceeds the supported amount.',
                ]);
            }

            $quotation->subtotal = $totals['subtotal'];
            $quotation->delivery_fee = $totals['delivery_fee'];
            $quotation->discount_amount = $totals['discount_amount'];
            $quotation->total = $totals['total'];

            if ($quotation->status === 'payment_requested' && !$quotation->payment_requested_at) {
                $quotation->payment_requested_at = now();
            }
            if ($quotation->status === 'payment_confirmed' && !$quotation->payment_confirmed_at) {
                $quotation->payment_confirmed_at = now();
            }
            $quotation->save();

            QuotationRevision::create([
                'quotation_id' => $quotation->id,
                'user_id' => auth()->id(),
                'event' => 'admin_update',
                'snapshot' => $this->snapshot($quotation->fresh('items')),
            ]);
        });

        return redirect()->route('admin.quotations.edit', $quotation)
            ->with('status', 'Quotation updated and revision saved.');
    }

    private function validateNewItem(array $data): void
    {
        if (empty($data['new_item_type'])) {
            return;
        }

        if (in_array($data['new_item_type'], ['custom', 'delivery', 'discount'], true) && empty($data['new_description'])) {
            throw ValidationException::withMessages([
                'new_description' => 'Enter a description for the new quotation item.',
            ]);
        }

        if (!empty($data['new_product_variant_id'])) {
            $variant = ProductVariant::with('product')
                ->whereKey($data['new_product_variant_id'])
                ->firstOrFail();
            if ($data['new_item_type'] !== 'product' || (int) $variant->product_id !== (int) $data['new_product_id']) {
                throw ValidationException::withMessages([
                    'new_product_variant_id' => 'Choose a variant that belongs to the selected product.',
                ]);
            }
        }

        if (($data['new_item_type'] === 'product') && empty($data['new_product_variant_id'])) {
            $product = Product::with('variants')->findOrFail($data['new_product_id']);
            if ($product->variants->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'new_product_variant_id' => 'Select a variant for this product.',
                ]);
            }
        }

        if (in_array($data['new_item_type'], ['delivery', 'discount'], true) && (int) ($data['new_quantity'] ?? 1) !== 1) {
            throw ValidationException::withMessages([
                'new_quantity' => 'Delivery and discount lines must have a quantity of one.',
            ]);
        }
    }

    private function validateNewItems(array $items): void
    {
        foreach ($items as $index => $item) {
            $isProduct = $item['type'] === 'product';
            $isRental = $item['type'] === 'rental';
            if (!$isProduct && !$isRental) {
                $hasSelectedLegacyService = $item['type'] === 'service' && !empty($item['service_id']);
                if (empty($item['description']) && !$hasSelectedLegacyService) {
                    throw ValidationException::withMessages([
                        "new_items.$index.description" => 'Enter a description for this quotation item.',
                    ]);
                }

                continue;
            }

            if ($isRental && !in_array($item['rental_period'] ?? null, ['daily', 'weekly', 'monthly'], true)) {
                throw ValidationException::withMessages([
                    "new_items.$index.rental_period" => 'Choose a rental period.',
                ]);
            }

            if (empty($item['product_id'])) {
                throw ValidationException::withMessages([
                    "new_items.$index.product_id" => 'Select a product.',
                ]);
            }

            $product = Product::with('variants')
                ->where('is_active', true)
                ->where($isRental ? 'for_rental' : 'for_sale', true)
                ->find($item['product_id']);
            if (!$product) {
                throw ValidationException::withMessages([
                    "new_items.$index.product_id" => $isRental
                        ? 'Select an active product available for rental.'
                        : 'Select an active product available for sale.',
                ]);
            }

            if ($isRental && !empty($item['product_variant_id'])) {
                throw ValidationException::withMessages([
                    "new_items.$index.product_variant_id" => 'Rental items use product-level rental pricing.',
                ]);
            }

            $quantity = (int) ($item['quantity'] ?? 1);
            $variantId = $item['product_variant_id'] ?? null;
            if ($isProduct && $product->variants->isNotEmpty() && empty($variantId)) {
                throw ValidationException::withMessages([
                    "new_items.$index.product_variant_id" => 'Select a product variant.',
                ]);
            }
            if (!$variantId) {
                continue;
            }

            $variant = $product->variants->firstWhere('id', (int) $variantId);
            if (!$variant) {
                throw ValidationException::withMessages([
                    "new_items.$index.product_variant_id" => 'Choose a variant belonging to the selected product.',
                ]);
            }
            if ($variant->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    "new_items.$index.quantity" => 'The selected variant does not have enough stock.',
                ]);
            }
        }
    }

    private function addItem(Quotation $quotation, array $data): void
    {
        $type = $data['type'];
        $quantity = in_array($type, ['delivery', 'discount', 'service', 'custom'], true)
            ? 1
            : (int) ($data['quantity'] ?? 1);
        $description = $data['description'] ?? null;
        $productId = null;
        $variantId = null;
        $serviceId = $data['service_id'] ?? null;
        $sku = null;
        $options = null;

        if (in_array($type, ['product', 'rental'], true)) {
            $isRental = $type === 'rental';
            $product = Product::with(['variants.values.attribute'])
                ->where('is_active', true)
                ->where($isRental ? 'for_rental' : 'for_sale', true)
                ->findOrFail($data['product_id']);
            $productId = $product->id;
            $description = $description ?: $product->name;
            if ($isRental) {
                $options = [[
                    'attribute' => 'Rental period',
                    'value' => $data['rental_period'],
                ]];
            } elseif (!empty($data['product_variant_id'])) {
                $variant = $product->variants->firstWhere('id', (int) $data['product_variant_id']);
                if (!$variant) {
                    throw ValidationException::withMessages([
                        'new_items' => 'The selected variant is no longer available.',
                    ]);
                }
                $variantId = $variant->id;
                if ($variant->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'new_items' => 'The selected variant does not have enough stock.',
                    ]);
                }
                $sku = $variant->sku;
                $options = $variant->values->map(function ($value) {
                    return ['attribute' => $value->attribute->name, 'value' => $value->value];
                })->values()->all();
            } else {
                $sku = $product->sku;
            }
        } elseif ($type === 'service' && !empty($serviceId)) {
            $service = Service::where('is_active', true)->findOrFail($serviceId);
            $description = $description ?: $service->name;
        } elseif ($type === 'delivery') {
            $description = $description ?: 'Delivery fee';
        } elseif ($type === 'discount') {
            $description = $description ?: 'Discount';
        }

        $unitPrice = (float) $data['unit_price'];
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'service_id' => $serviceId,
            'item_type' => $type,
            'description' => $description,
            'sku' => $sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $this->lineTotal($type, $quantity, $unitPrice),
            'options_snapshot' => $options,
            'sort_order' => ((int) $quotation->items()->max('sort_order')) + 1,
        ]);
    }

    private function lineTotal(string $type, int $quantity, float $unitPrice): float
    {
        $amount = round($quantity * $unitPrice, 2);

        return $type === 'discount' ? -$amount : $amount;
    }

    private function totals($items): array
    {
        $subtotal = 0;
        $deliveryFee = 0;
        $discountAmount = 0;

        foreach ($items as $item) {
            if ($item->item_type === 'delivery') {
                $deliveryFee += (float) $item->line_total;
            } elseif ($item->item_type === 'discount') {
                $discountAmount += abs((float) $item->line_total);
            } else {
                $subtotal += (float) $item->line_total;
            }
        }

        $subtotal = round($subtotal, 2);
        $deliveryFee = round($deliveryFee, 2);
        $discountAmount = round($discountAmount, 2);

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount_amount' => $discountAmount,
            'total' => round($subtotal + $deliveryFee - $discountAmount, 2),
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
