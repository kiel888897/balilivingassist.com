@php
$itemType = $item['type'] ?? '';
$productId = $item['product_id'] ?? '';
$variantId = $item['product_variant_id'] ?? '';
$serviceId = $item['service_id'] ?? '';
$itemDescription = $item['description'] ?? '';
$quantity = $item['quantity'] ?? 1;
$unitPrice = $item['unit_price'] ?? '';
$rentalPeriod = $item['rental_period'] ?? 'daily';
@endphp
<article class="panel" style="padding: 18px;" data-quotation-item-row data-quote-type="{{ $itemType }}">
    <div class="form-grid">
        <div class="field">
            <label for="item-type-{{ $index }}">Item type</label>
            <select id="item-type-{{ $index }}" name="items[{{ $index }}][type]" required data-item-type>
                <option value="">Select item type</option>
                <option value="product" {{ $itemType === 'product' ? 'selected' : '' }}>Product</option>
                <option value="rental" {{ $itemType === 'rental' ? 'selected' : '' }}>Rental</option>
                <option value="service" {{ $itemType === 'service' ? 'selected' : '' }}>Service</option>
                <option value="delivery" {{ $itemType === 'delivery' ? 'selected' : '' }}>Delivery fee</option>
                <option value="discount" {{ $itemType === 'discount' ? 'selected' : '' }}>Discount</option>
                <option value="custom" {{ $itemType === 'custom' ? 'selected' : '' }}>Custom charge</option>
            </select>
        </div>
        <div class="field" data-product-group hidden>
            <label for="item-product-{{ $index }}">Product</label>
            <select id="item-product-{{ $index }}" name="items[{{ $index }}][product_id]" data-item-product disabled>
                <option value="">Select a product</option>
                @foreach ($products as $product)
                <option value="{{ $product->id }}"
                    data-for-sale="{{ $product->for_sale ? '1' : '0' }}"
                    data-for-rental="{{ $product->for_rental ? '1' : '0' }}"
                    data-has-variants="{{ $product->variants->isNotEmpty() ? '1' : '0' }}"
                    data-sale-price="{{ $product->sale_price ?? '' }}"
                    data-rental-daily-price="{{ $product->rental_price ?? '' }}"
                    data-rental-weekly-price="{{ $product->weekly_rental_price ?? '' }}"
                    data-rental-monthly-price="{{ $product->monthly_rental_price ?? '' }}"
                    {{ (string) $productId === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" data-variant-group hidden>
            <label for="item-variant-{{ $index }}">Product variant</label>
            <select id="item-variant-{{ $index }}" name="items[{{ $index }}][product_variant_id]" data-item-variant disabled>
                <option value="">Select a variant</option>
                @foreach ($variants as $variant)
                <option value="{{ $variant->id }}" data-product-id="{{ $variant->product_id }}" data-price="{{ $variant->price }}" data-stock="{{ $variant->stock_quantity }}" {{ (string) $variantId === (string) $variant->id ? 'selected' : '' }}>{{ $variant->product->name }} — {{ $variant->sku }}{{ $variant->stock_quantity < 1 ? ' (Out of stock)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" data-service-group hidden>
            <label for="item-service-{{ $index }}">Catalog service (optional)</label>
            <select id="item-service-{{ $index }}" name="items[{{ $index }}][service_id]" data-item-service disabled>
                <option value="">Use a custom service description</option>
                @foreach ($services as $service)
                <option value="{{ $service->id }}" data-name="{{ $service->name }}" data-price="{{ $service->price ?? '' }}" {{ (string) $serviceId === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" data-period-group hidden>
            <label for="item-period-{{ $index }}">Rental period</label>
            <select id="item-period-{{ $index }}" name="items[{{ $index }}][rental_period]" data-item-period disabled>
                <option value="daily" {{ $rentalPeriod === 'daily' ? 'selected' : '' }}>Daily (1 day)</option>
                <option value="weekly" {{ $rentalPeriod === 'weekly' ? 'selected' : '' }}>Weekly (7 days)</option>
                <option value="monthly" {{ $rentalPeriod === 'monthly' ? 'selected' : '' }}>Monthly (28 days)</option>
            </select>
        </div>
        <div class="field field-wide">
            <label for="item-description-{{ $index }}">Description</label>
            <input id="item-description-{{ $index }}" name="items[{{ $index }}][description]" value="{{ $itemDescription }}" maxlength="255" data-item-description data-edited="{{ $itemDescription !== '' ? '1' : '0' }}">
        </div>
        <div class="field">
            <label for="item-quantity-{{ $index }}">Quantity</label>
            <input id="item-quantity-{{ $index }}" type="number" name="items[{{ $index }}][quantity]" value="{{ $quantity }}" min="1" max="99999" required data-item-quantity>
        </div>
        <div class="field">
            <label for="item-price-{{ $index }}">Unit price / amount (IDR)</label>
            <input id="item-price-{{ $index }}" type="number" name="items[{{ $index }}][unit_price]" value="{{ $unitPrice }}" min="0" max="9999999999.99" step="0.01" required data-item-price>
        </div>
    </div>
    <button class="btn btn-light" type="button" style="margin-top: 14px;" data-remove-quotation-item><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Remove item</button>
</article>
