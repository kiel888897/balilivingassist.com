@extends('admin.layout')

@section('title', 'Edit ' . $quotation->quote_number)
@section('page_title', 'Review quotation ' . $quotation->quote_number)
@section('page_description', 'Edit line items, delivery, discount, address and status. Every save creates an audit revision.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.quotations') }}">Back to quotations</a>
@endsection

@section('content')
@php
$pendingQuoteItems = old('new_items', []);
if (!is_array($pendingQuoteItems)) {
    $pendingQuoteItems = [];
}
$pendingQuoteItems = array_filter($pendingQuoteItems, function ($item) {
    return is_array($item);
});
$rentalPeriodLabels = [
    'daily' => 'Daily (1 day)',
    'weekly' => 'Weekly (7 days)',
    'monthly' => 'Monthly (28 days)',
];
@endphp
@if ($errors->any())
<div class="status-error" role="alert">
    <strong>Please correct the following:</strong>
    <ul style="margin:7px 0 0;padding-left:20px;">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="content-grid quotation-editor-layout" style="align-items:start;">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Quotation details</h2>
                <span class="subtext">Submitted {{ $quotation->created_at->format('d M Y H:i') }}</span>
            </div>
            <span class="badge">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span>
        </div>
        <form action="{{ route('admin.quotations.update', $quotation) }}" method="POST" class="detail-form">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="field">
                    <label for="customer_name">Customer name</label>
                    <input id="customer_name" value="{{ $quotation->customer_name }}" readonly aria-readonly="true">
                </div>
                <div class="field">
                    <label for="customer_phone">Customer WhatsApp</label>
                    <input id="customer_phone" type="tel" value="{{ $quotation->customer_phone }}" readonly aria-readonly="true">
                </div>
                <div class="field field-wide">
                    <label for="shipping_address">Delivery address</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" required maxlength="2000">{{ old('shipping_address', $quotation->shipping_address) }}</textarea>
                </div>
                <div class="field field-wide">
                    <label for="customer_note">Customer note</label>
                    <textarea id="customer_note" name="customer_note" rows="2" maxlength="2000">{{ old('customer_note', $quotation->customer_note) }}</textarea>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach ($statuses as $status)
                        <option value="{{ $status }}" {{ old('status', $quotation->status) === $status ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field field-wide">
                    <label for="admin_note">Message / note for customer</label>
                    <textarea id="admin_note" name="admin_note" rows="3" maxlength="2000">{{ old('admin_note', $quotation->admin_note) }}</textarea>
                </div>
                <div class="field field-wide">
                    <label for="payment_instructions">Manual payment instructions</label>
                    <textarea id="payment_instructions" name="payment_instructions" rows="3" maxlength="2000">{{ old('payment_instructions', $quotation->payment_instructions) }}</textarea>
                    <span class="subtext">Required when status is set to PAYMENT REQUESTED. This system does not collect payments.</span>
                </div>
            </div>

            <div class="panel detail-panel quote-items-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Quote items</h2>
                        <p>Adjust quantity and price, remove existing lines, or add one or more items.</p>
                    </div>
                    <button class="btn" type="button" data-open-quote-item-dialog><i class="fa-solid fa-plus" aria-hidden="true"></i>&nbsp; Add item</button>
                </div>
                <div class="detail-form">
                    <div class="quote-item-list" data-quote-items>
                        @foreach ($quotation->items as $item)
                        @php
                        $removeItem = old('items.' . $item->id . '.remove', false);
                        $rentalPeriodOption = collect($item->options_snapshot ?? [])->firstWhere('attribute', 'Rental period');
                        $rentalPeriod = is_array($rentalPeriodOption) && in_array($rentalPeriodOption['value'] ?? null, ['daily', 'weekly', 'monthly'], true)
                            ? $rentalPeriodOption['value']
                            : 'daily';
                        $itemOptions = collect($item->options_snapshot ?? [])->map(function ($option) use ($rentalPeriodLabels) {
                            if (($option['attribute'] ?? null) === 'Rental period') {
                                return 'Rental period: ' . ($rentalPeriodLabels[$option['value'] ?? ''] ?? 'Daily (1 day)');
                            }
                            return ($option['attribute'] ?? '') . ': ' . ($option['value'] ?? '');
                        });
                        @endphp
                        <article class="quote-item-row {{ $removeItem ? 'is-marked-remove' : '' }}" data-quote-item-row data-quote-type="{{ $item->item_type }}">
                            <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                            <div class="quote-item-description">
                                <div class="quote-item-heading">
                                    <span class="badge">{{ strtoupper(str_replace('_', ' ', $item->item_type)) }}</span>
                                    @if ($item->sku)
                                    <span class="quote-item-sku">SKU: {{ $item->sku }}</span>
                                    @endif
                                </div>
                                <label class="sr-only" for="item_description_{{ $item->id }}">Description</label>
                                <input class="quote-description-input" id="item_description_{{ $item->id }}" name="items[{{ $item->id }}][description]" value="{{ old('items.' . $item->id . '.description', $item->description) }}" maxlength="255" required>
                                @if ($item->options_snapshot)
                                <p class="quote-item-options">{{ $itemOptions->implode(' · ') }}</p>
                                @elseif ($item->item_type === 'discount')
                                <p class="quote-item-options">Enter the discount as a positive amount.</p>
                                @endif
                            </div>
                            <div class="quote-item-controls">
                                @if ($item->item_type === 'rental')
                                <div class="quote-item-control">
                                    <label for="item_rental_period_{{ $item->id }}">Rental period</label>
                                    <select id="item_rental_period_{{ $item->id }}" name="items[{{ $item->id }}][rental_period]" required>
                                        @foreach ($rentalPeriodLabels as $periodValue => $periodLabel)
                                        <option value="{{ $periodValue }}" {{ $rentalPeriod === $periodValue ? 'selected' : '' }}>{{ $periodLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="quote-item-control">
                                    <label for="item_quantity_{{ $item->id }}">Quantity</label>
                                    <div class="quote-quantity-control">
                                        <button type="button" data-quote-step="-1" aria-label="Decrease quantity">−</button>
                                        <input id="item_quantity_{{ $item->id }}" name="items[{{ $item->id }}][quantity]" type="number" min="1" max="99999" value="{{ old('items.' . $item->id . '.quantity', $item->quantity) }}" data-quote-quantity required>
                                        <button type="button" data-quote-step="1" aria-label="Increase quantity">+</button>
                                    </div>
                                </div>
                                <div class="quote-item-control quote-price-control">
                                    <label for="item_price_{{ $item->id }}">{{ $item->item_type === 'discount' ? 'Discount amount (IDR)' : 'Unit price (IDR)' }}</label>
                                    <input id="item_price_{{ $item->id }}" name="items[{{ $item->id }}][unit_price]" type="number" min="0" step="0.01" value="{{ old('items.' . $item->id . '.unit_price', $item->unit_price) }}" data-quote-price required>
                                </div>
                                <div class="quote-line-total" data-quote-line-total aria-live="polite"></div>
                                <input type="checkbox" name="items[{{ $item->id }}][remove]" value="1" data-quote-remove-input {{ $removeItem ? 'checked' : '' }} hidden>
                                <button class="btn btn-light quote-remove-button" type="button" data-remove-quote-item aria-pressed="{{ $removeItem ? 'true' : 'false' }}">{{ $removeItem ? 'Undo remove' : 'Remove' }}</button>
                            </div>
                        </article>
                        @endforeach
                        @foreach ($pendingQuoteItems as $pendingIndex => $pendingItem)
                        @php
                        $pendingType = is_string($pendingItem['type'] ?? null) ? $pendingItem['type'] : 'custom';
                        $pendingProductId = is_scalar($pendingItem['product_id'] ?? null) && is_numeric($pendingItem['product_id']) ? (int) $pendingItem['product_id'] : null;
                        $pendingVariantId = is_scalar($pendingItem['product_variant_id'] ?? null) && is_numeric($pendingItem['product_variant_id']) ? (int) $pendingItem['product_variant_id'] : null;
                        $pendingProduct = $pendingProductId ? $products->firstWhere('id', $pendingProductId) : null;
                        $pendingVariant = $pendingVariantId ? $variants->firstWhere('id', $pendingVariantId) : null;
                        $pendingDescription = is_scalar($pendingItem['description'] ?? null) ? (string) $pendingItem['description'] : ($pendingProduct->name ?? '');
                        $pendingQuantity = is_numeric($pendingItem['quantity'] ?? null) ? (int) $pendingItem['quantity'] : 1;
                        $pendingPrice = is_numeric($pendingItem['unit_price'] ?? null) ? $pendingItem['unit_price'] : '';
                        $pendingRentalPeriod = in_array($pendingItem['rental_period'] ?? null, ['daily', 'weekly', 'monthly'], true)
                            ? $pendingItem['rental_period']
                            : 'daily';
                        @endphp
                        <article class="quote-item-row" data-quote-item-row data-pending-item-index="{{ $pendingIndex }}" data-quote-type="{{ $pendingType }}">
                            <div class="quote-item-description">
                                <div class="quote-item-heading">
                                    <span class="badge">{{ strtoupper(str_replace('_', ' ', $pendingType)) }}</span>
                                    @if ($pendingVariant)
                                    <span class="quote-item-sku">SKU: {{ $pendingVariant->sku }}</span>
                                    @elseif ($pendingProduct && $pendingProduct->sku)
                                    <span class="quote-item-sku">SKU: {{ $pendingProduct->sku }}</span>
                                    @endif
                                </div>
                                <label class="sr-only" for="pending_item_description_{{ $pendingIndex }}">Description</label>
                                <input class="quote-description-input" id="pending_item_description_{{ $pendingIndex }}" name="new_items[{{ $pendingIndex }}][description]" value="{{ $pendingDescription }}" maxlength="255" required>
                                @if ($pendingVariant && $pendingVariant->values->isNotEmpty())
                                <p class="quote-item-options">{{ $pendingVariant->values->map(function ($value) { return $value->attribute->name . ': ' . $value->value; })->implode(' · ') }}</p>
                                @elseif ($pendingType === 'discount')
                                <p class="quote-item-options">Enter the discount as a positive amount.</p>
                                @elseif ($pendingType === 'rental')
                                <p class="quote-item-options">Rental period: {{ ucfirst($pendingRentalPeriod) }}</p>
                                @endif
                                <input type="hidden" name="new_items[{{ $pendingIndex }}][type]" value="{{ $pendingType }}">
                                <input type="hidden" name="new_items[{{ $pendingIndex }}][product_id]" value="{{ $pendingProductId ?? '' }}">
                                <input type="hidden" name="new_items[{{ $pendingIndex }}][product_variant_id]" value="{{ $pendingVariantId ?? '' }}">
                                @if ($pendingType === 'rental')
                                <input type="hidden" name="new_items[{{ $pendingIndex }}][rental_period]" value="{{ $pendingRentalPeriod }}">
                                @endif
                            </div>
                            <div class="quote-item-controls">
                                @if (in_array($pendingType, ['product', 'rental'], true))
                                <div class="quote-item-control">
                                    <label for="pending_item_quantity_{{ $pendingIndex }}">Quantity</label>
                                    <div class="quote-quantity-control">
                                        <button type="button" data-quote-step="-1" aria-label="Decrease quantity">−</button>
                                        <input id="pending_item_quantity_{{ $pendingIndex }}" name="new_items[{{ $pendingIndex }}][quantity]" type="number" min="1" max="{{ $pendingVariant ? min(99999, max(1, $pendingVariant->stock_quantity)) : 99999 }}" value="{{ $pendingQuantity }}" data-quote-quantity required>
                                        <button type="button" data-quote-step="1" aria-label="Increase quantity">+</button>
                                    </div>
                                </div>
                                @else
                                <input type="hidden" name="new_items[{{ $pendingIndex }}][quantity]" value="1" data-new-fixed-quantity>
                                @endif
                                <div class="quote-item-control quote-price-control">
                                    <label for="pending_item_price_{{ $pendingIndex }}">{{ $pendingType === 'discount' ? 'Discount amount (IDR)' : 'Unit price (IDR)' }}</label>
                                    <input id="pending_item_price_{{ $pendingIndex }}" name="new_items[{{ $pendingIndex }}][unit_price]" type="number" min="0" step="0.01" value="{{ $pendingPrice }}" data-quote-price required>
                                </div>
                                <div class="quote-line-total" data-quote-line-total aria-live="polite"></div>
                                <button class="btn btn-light quote-remove-button" type="button" data-remove-new-quote-item>Remove</button>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>

                <dialog class="quote-item-dialog" data-quote-item-dialog aria-labelledby="quote-item-dialog-title">
                    <div class="quote-dialog-content">
                        <div class="quote-dialog-heading">
                            <div>
                                <h2 id="quote-item-dialog-title">Add an item</h2>
                                <p>Choose a type and enter its details.</p>
                            </div>
                            <button class="btn btn-light quote-dialog-close" type="button" data-close-quote-item-dialog aria-label="Close">×</button>
                        </div>
                        <div class="quote-dialog-fields">
                            <div class="field">
                                <label for="modal_item_type">Item type</label>
                                <select id="modal_item_type" data-modal-item-type>
                                    <option value="">Select item type</option>
                                    <option value="product">Product</option>
                                    <option value="rental">Rental</option>
                                    <option value="service">Service</option>
                                    <option value="delivery">Delivery fee</option>
                                    <option value="discount">Discount</option>
                                    <option value="custom">Custom charge</option>
                                </select>
                            </div>
                            <div class="field" data-modal-product-group hidden>
                                <label for="modal_product_id">Product</label>
                                <select id="modal_product_id" data-modal-product>
                                    <option value="">Select a product</option>
                                    @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->sale_price ?? 0 }}" data-rental-daily-price="{{ $product->rental_price ?? '' }}" data-rental-weekly-price="{{ $product->weekly_rental_price ?? '' }}" data-rental-monthly-price="{{ $product->monthly_rental_price ?? '' }}" data-sku="{{ $product->sku }}" data-has-variants="{{ $product->variants->isNotEmpty() ? '1' : '0' }}" data-for-sale="{{ $product->for_sale ? '1' : '0' }}" data-for-rental="{{ $product->for_rental ? '1' : '0' }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field" data-modal-variant-group hidden>
                                <label for="modal_variant_id">Product variant</label>
                                <select id="modal_variant_id" data-modal-variant>
                                    <option value="">Select a variant</option>
                                    @foreach ($variants as $variant)
                                    <option value="{{ $variant->id }}" data-product-id="{{ $variant->product_id }}" data-price="{{ $variant->price }}" data-stock="{{ $variant->stock_quantity }}" data-sku="{{ $variant->sku }}" data-options="{{ $variant->values->map(function ($value) { return $value->attribute->name . ': ' . $value->value; })->implode(' · ') }}">
                                        {{ $variant->product->name }} · {{ $variant->values->map(function ($value) { return $value->attribute->name . ': ' . $value->value; })->implode(', ') }}{{ $variant->stock_quantity < 1 ? ' (Out of stock)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field" data-modal-rental-period-group hidden>
                                <label for="modal_rental_period">Rental period</label>
                                <select id="modal_rental_period" data-modal-rental-period>
                                    <option value="daily">Daily (1 day)</option>
                                    <option value="weekly">Weekly (7 days)</option>
                                    <option value="monthly">Monthly (28 days)</option>
                                </select>
                            </div>
                            <div class="field" data-modal-description-group hidden>
                                <label for="modal_item_description">Description</label>
                                <input id="modal_item_description" type="text" maxlength="255" data-modal-description>
                            </div>
                            <div class="field" data-modal-price-group hidden>
                                <label for="modal_item_price">Price / amount (IDR)</label>
                                <input id="modal_item_price" type="number" min="0" step="0.01" data-modal-price>
                                <span class="subtext" data-modal-discount-hint hidden>Enter the discount as a positive amount; it will be deducted from the quote.</span>
                            </div>
                        </div>
                        <div class="quote-dialog-actions">
                            <button class="btn btn-light" type="button" data-close-quote-item-dialog>Cancel</button>
                            <button class="btn" type="button" data-confirm-add-quote-item disabled>Add to quote items</button>
                        </div>
                    </div>
                </dialog>

                <template data-new-quote-item-template>
                    <article class="quote-item-row" data-quote-item-row data-quote-type="">
                        <div class="quote-item-description">
                            <div class="quote-item-heading">
                                <span class="badge" data-new-item-type-label></span>
                                <span class="quote-item-sku" data-new-item-sku hidden></span>
                            </div>
                            <label class="sr-only" data-new-description-label>Description</label>
                            <input class="quote-description-input" type="text" maxlength="255" required data-new-description-value>
                            <p class="quote-item-options" data-new-item-options hidden></p>
                            <input type="hidden" data-new-item-type-value>
                            <input type="hidden" data-new-product-id>
                            <input type="hidden" data-new-variant-id>
                            <input type="hidden" data-new-rental-period>
                        </div>
                        <div class="quote-item-controls">
                            <div class="quote-item-control" data-new-quantity-control>
                                <label data-new-quantity-label>Quantity</label>
                                <div class="quote-quantity-control">
                                    <button type="button" data-quote-step="-1" aria-label="Decrease quantity">−</button>
                                    <input type="number" min="1" max="99999" value="1" data-quote-quantity required>
                                    <button type="button" data-quote-step="1" aria-label="Increase quantity">+</button>
                                </div>
                            </div>
                            <input type="hidden" value="1" data-new-fixed-quantity>
                            <div class="quote-item-control quote-price-control">
                                <label data-new-price-label>Unit price (IDR)</label>
                                <input type="number" min="0" step="0.01" required data-quote-price>
                            </div>
                            <div class="quote-line-total" data-quote-line-total aria-live="polite"></div>
                            <button class="btn btn-light quote-remove-button" type="button" data-remove-new-quote-item>Remove</button>
                        </div>
                    </article>
                </template>
            </div>

            <div class="detail-actions">
                <button class="btn" type="submit">Save quotation changes</button>
            </div>
        </form>
    </section>

    <aside class="detail-list quotation-summary">
        <section class="panel">
            <div class="panel-heading"><h2>Current totals</h2></div>
            <div class="panel-body">
                <p class="flex justify-between"><span>Subtotal</span><strong>Rp <span data-quote-subtotal>{{ number_format($quotation->subtotal, 0, ',', '.') }}</span></strong></p>
                <p class="mt-3 flex justify-between"><span>Delivery</span><strong>Rp <span data-quote-delivery>{{ number_format($quotation->delivery_fee, 0, ',', '.') }}</span></strong></p>
                <p class="mt-3 flex justify-between"><span>Discount</span><strong>- Rp <span data-quote-discount>{{ number_format($quotation->discount_amount, 0, ',', '.') }}</span></strong></p>
                <p class="mt-4 flex justify-between border-t border-slate-200 pt-4 text-base"><strong>Total</strong><strong>Rp <span data-quote-total>{{ number_format($quotation->total, 0, ',', '.') }}</span></strong></p>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading"><h2>Revision history</h2></div>
            <div class="detail-form">
                @forelse ($quotation->revisions as $revision)
                <div class="detail-card">
                    <strong>{{ strtoupper(str_replace('_', ' ', $revision->event)) }}</strong>
                    <span class="subtext">{{ $revision->editor->name ?? 'Customer' }} · {{ $revision->created_at->format('d M Y H:i') }}</span>
                    <span class="subtext">Total: Rp {{ number_format($revision->snapshot['total'] ?? 0, 0, ',', '.') }} · {{ strtoupper(str_replace('_', ' ', $revision->snapshot['status'] ?? '')) }}</span>
                </div>
                @empty
                <p class="muted">No revisions recorded yet.</p>
                @endforelse
            </div>
        </section>

        @if ($quotation->status === 'payment_requested')
        @php
        $paymentMessage = 'Hi ' . $quotation->customer_name . ', your quotation ' . $quotation->quote_number . ' is ready. Total: Rp ' . number_format($quotation->total, 0, ',', '.') . '. Payment instructions: ' . $quotation->payment_instructions;
        $paymentWhatsApp = 'https://wa.me/' . preg_replace('/\D+/', '', $quotation->customer_phone) . '?text=' . rawurlencode($paymentMessage);
        @endphp
        <a class="btn quotation-payment-link" style="min-height:46px;background:#16805d;" href="{{ $paymentWhatsApp }}" target="_blank" rel="noopener noreferrer">
            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>&nbsp; Send payment request on WhatsApp
        </a>
        @endif
    </aside>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var itemDialog = document.querySelector('[data-quote-item-dialog]');
        var itemsContainer = document.querySelector('[data-quote-items]');
        var itemTemplate = document.querySelector('[data-new-quote-item-template]');
        if (!itemDialog || !itemsContainer || !itemTemplate) return;

        var itemType = itemDialog.querySelector('[data-modal-item-type]');
        var productGroup = itemDialog.querySelector('[data-modal-product-group]');
        var productSelect = itemDialog.querySelector('[data-modal-product]');
        var variantGroup = itemDialog.querySelector('[data-modal-variant-group]');
        var variantSelect = itemDialog.querySelector('[data-modal-variant]');
        var rentalPeriodGroup = itemDialog.querySelector('[data-modal-rental-period-group]');
        var rentalPeriodSelect = itemDialog.querySelector('[data-modal-rental-period]');
        var descriptionGroup = itemDialog.querySelector('[data-modal-description-group]');
        var descriptionInput = itemDialog.querySelector('[data-modal-description]');
        var priceGroup = itemDialog.querySelector('[data-modal-price-group]');
        var priceInput = itemDialog.querySelector('[data-modal-price]');
        var discountHint = itemDialog.querySelector('[data-modal-discount-hint]');
        var confirmButton = itemDialog.querySelector('[data-confirm-add-quote-item]');
        var rowIndex = 0;
        itemsContainer.querySelectorAll('[data-pending-item-index]').forEach(function (row) {
            rowIndex = Math.max(rowIndex, Number(row.dataset.pendingItemIndex) + 1);
        });

        function formatPrice(amount) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(amount);
        }

        function updateTotals() {
            var subtotal = 0;
            var delivery = 0;
            var discount = 0;

            itemsContainer.querySelectorAll('[data-quote-item-row]').forEach(function (row) {
                var removalInput = row.querySelector('[data-quote-remove-input]');
                var totalOutput = row.querySelector('[data-quote-line-total]');
                if (removalInput && removalInput.checked) {
                    if (totalOutput) totalOutput.textContent = 'Marked for removal';
                    return;
                }

                var quantityInput = row.querySelector('[data-quote-quantity]');
                var fixedQuantity = row.querySelector('[data-new-fixed-quantity]');
                var priceInput = row.querySelector('[data-quote-price]');
                var quantity = Number(quantityInput && !quantityInput.disabled
                    ? quantityInput.value
                    : fixedQuantity ? fixedQuantity.value : quantityInput ? quantityInput.value : 1) || 1;
                var unitPrice = Number(priceInput ? priceInput.value : 0) || 0;
                var amount = Math.round(quantity * unitPrice * 100) / 100;
                var type = row.dataset.quoteType;

                if (type === 'delivery') {
                    delivery += amount;
                } else if (type === 'discount') {
                    discount += amount;
                } else {
                    subtotal += amount;
                }

                if (totalOutput) {
                    totalOutput.textContent = (type === 'discount' ? '− ' : '') + 'Rp ' + formatPrice(amount);
                }
            });

            var subtotalOutput = document.querySelector('[data-quote-subtotal]');
            var deliveryOutput = document.querySelector('[data-quote-delivery]');
            var discountOutput = document.querySelector('[data-quote-discount]');
            var totalOutput = document.querySelector('[data-quote-total]');
            if (subtotalOutput) subtotalOutput.textContent = formatPrice(subtotal);
            if (deliveryOutput) deliveryOutput.textContent = formatPrice(delivery);
            if (discountOutput) discountOutput.textContent = formatPrice(discount);
            if (totalOutput) totalOutput.textContent = formatPrice(subtotal + delivery - discount);
        }

        function updateVariantOptions(requireVariant) {
            var productId = productSelect.value;
            var productOption = productSelect.options[productSelect.selectedIndex];
            var hasVariants = requireVariant && productOption && productOption.dataset.hasVariants === '1';
            variantGroup.hidden = !hasVariants;
            variantSelect.required = Boolean(hasVariants);
            variantSelect.disabled = !hasVariants;

            Array.from(variantSelect.options).forEach(function (option) {
                if (!option.value) return;
                var belongsToProduct = option.dataset.productId === productId;
                var inStock = Number(option.dataset.stock || 0) > 0;
                option.hidden = !belongsToProduct;
                option.disabled = !belongsToProduct || !inStock;
                if ((!belongsToProduct || !inStock) && option.selected) {
                    variantSelect.value = '';
                }
            });
        }

        function updateModalFields() {
            var type = itemType.value;
            var isProduct = type === 'product';
            var isRental = type === 'rental';
            var selectsProduct = isProduct || isRental;
            var needsDescription = type !== '' && !selectsProduct;

            productGroup.hidden = !selectsProduct;
            productSelect.disabled = !selectsProduct;
            productSelect.required = selectsProduct;
            Array.from(productSelect.options).forEach(function (option) {
                if (!option.value) return;
                var isAvailableType = isProduct
                    ? option.dataset.forSale === '1'
                    : isRental && option.dataset.forRental === '1';
                option.hidden = !isAvailableType;
                option.disabled = !isAvailableType;
                if (!isAvailableType && option.selected) {
                    productSelect.value = '';
                }
            });
            rentalPeriodGroup.hidden = !isRental;
            rentalPeriodSelect.disabled = !isRental;
            rentalPeriodSelect.required = isRental;
            descriptionGroup.hidden = !needsDescription;
            descriptionInput.disabled = !needsDescription;
            descriptionInput.required = needsDescription;
            priceGroup.hidden = !needsDescription;
            priceInput.disabled = !needsDescription;
            priceInput.required = needsDescription;
            discountHint.hidden = type !== 'discount';

            if (!selectsProduct) {
                productSelect.value = '';
                variantSelect.value = '';
            } else if (isRental) {
                variantSelect.value = '';
            }
            updateVariantOptions(isProduct);

            var selectedProduct = productSelect.options[productSelect.selectedIndex];
            var hasVariants = isProduct && selectedProduct && selectedProduct.dataset.hasVariants === '1';
            var validProduct = selectsProduct && productSelect.value && (!hasVariants || variantSelect.value)
                && (!isRental || rentalPeriodSelect.value);
            var validCustomItem = needsDescription && descriptionInput.value.trim() && priceInput.value !== '';
            confirmButton.disabled = !(validProduct || validCustomItem);
        }

        function addQuoteItem() {
            var type = itemType.value;
            var productOption = productSelect.options[productSelect.selectedIndex];
            var variantOption = variantSelect.options[variantSelect.selectedIndex];
            var isProduct = type === 'product';
            var isRental = type === 'rental';
            var selectsProduct = isProduct || isRental;
            var variantId = isProduct && variantSelect.value ? variantSelect.value : '';
            var description = selectsProduct ? productOption.textContent.trim() : descriptionInput.value.trim();
            var rentalRateAttribute = 'rental' + rentalPeriodSelect.value.charAt(0).toUpperCase() + rentalPeriodSelect.value.slice(1) + 'Price';
            var unitPrice = isProduct
                ? Number(variantId ? variantOption.dataset.price : productOption.dataset.price)
                : isRental ? Number(productOption.dataset[rentalRateAttribute] || 0) : Number(priceInput.value);
            var row = itemTemplate.content.firstElementChild.cloneNode(true);
            var index = rowIndex++;
            var typeLabels = {
                product: 'PRODUCT',
                rental: 'RENTAL',
                service: 'SERVICE',
                delivery: 'DELIVERY',
                discount: 'DISCOUNT',
                custom: 'CUSTOM CHARGE'
            };

            row.dataset.quoteType = type;
            row.querySelector('[data-new-item-type-label]').textContent = typeLabels[type];
            row.querySelector('[data-new-description-label]').htmlFor = 'new_item_description_' + index;
            row.querySelector('[data-new-description-value]').id = 'new_item_description_' + index;
            row.querySelector('[data-new-description-value]').value = description;
            row.querySelector('[data-new-item-type-value]').name = 'new_items[' + index + '][type]';
            row.querySelector('[data-new-item-type-value]').value = type;
            row.querySelector('[data-new-product-id]').name = 'new_items[' + index + '][product_id]';
            row.querySelector('[data-new-product-id]').value = selectsProduct ? productSelect.value : '';
            row.querySelector('[data-new-variant-id]').name = 'new_items[' + index + '][product_variant_id]';
            row.querySelector('[data-new-variant-id]').value = variantId;
            var rentalPeriodInput = row.querySelector('[data-new-rental-period]');
            rentalPeriodInput.name = isRental ? 'new_items[' + index + '][rental_period]' : '';
            rentalPeriodInput.value = isRental ? rentalPeriodSelect.value : '';
            rentalPeriodInput.disabled = !isRental;

            var rowDescription = row.querySelector('[data-new-description-value]');
            rowDescription.name = 'new_items[' + index + '][description]';

            var quantityInput = row.querySelector('[data-quote-quantity]');
            quantityInput.id = 'new_item_quantity_' + index;
            row.querySelector('[data-new-quantity-label]').htmlFor = quantityInput.id;
            quantityInput.name = 'new_items[' + index + '][quantity]';
            var quantityControl = row.querySelector('[data-new-quantity-control]');
            var fixedQuantity = row.querySelector('[data-new-fixed-quantity]');
            fixedQuantity.name = 'new_items[' + index + '][quantity]';
            row.querySelector('[data-new-quantity-label]').textContent = 'Quantity';
            quantityControl.hidden = !selectsProduct;
            quantityInput.disabled = !selectsProduct;
            fixedQuantity.disabled = selectsProduct;
            if (variantId) {
                quantityInput.max = Math.min(99999, Number(variantOption.dataset.stock));
            } else {
                quantityInput.max = '99999';
            }

            var rowPrice = row.querySelector('[data-quote-price]');
            rowPrice.id = 'new_item_price_' + index;
            row.querySelector('[data-new-price-label]').htmlFor = rowPrice.id;
            rowPrice.name = 'new_items[' + index + '][unit_price]';
            rowPrice.value = Number.isFinite(unitPrice) ? unitPrice : 0;
            row.querySelector('[data-new-price-label]').textContent = type === 'discount'
                ? 'Discount amount (IDR)'
                : 'Unit price (IDR)';

            var skuLabel = row.querySelector('[data-new-item-sku]');
            var sku = selectsProduct ? (variantId ? variantOption.dataset.sku : productOption.dataset.sku) : '';
            if (sku) {
                skuLabel.textContent = 'SKU: ' + sku;
                skuLabel.hidden = false;
            }
            var optionsLabel = row.querySelector('[data-new-item-options]');
            if (isRental) {
                optionsLabel.textContent = 'Rental period: ' + rentalPeriodSelect.options[rentalPeriodSelect.selectedIndex].text;
                optionsLabel.hidden = false;
            } else if (variantId && variantOption.dataset.options) {
                optionsLabel.textContent = variantOption.dataset.options;
                optionsLabel.hidden = false;
            } else if (type === 'discount') {
                optionsLabel.textContent = 'Enter the discount as a positive amount.';
                optionsLabel.hidden = false;
            }

            itemsContainer.appendChild(row);
            itemDialog.close();
            resetDialog();
            updateTotals();
        }

        function resetDialog() {
            itemType.value = '';
            productSelect.value = '';
            variantSelect.value = '';
            rentalPeriodSelect.value = 'daily';
            descriptionInput.value = '';
            priceInput.value = '';
            updateModalFields();
        }

        document.querySelector('[data-open-quote-item-dialog]').addEventListener('click', function () {
            resetDialog();
            itemDialog.showModal();
            itemType.focus();
        });
        itemDialog.querySelectorAll('[data-close-quote-item-dialog]').forEach(function (button) {
            button.addEventListener('click', function () {
                itemDialog.close();
                resetDialog();
            });
        });
        itemDialog.addEventListener('close', resetDialog);
        itemDialog.addEventListener('click', function (event) {
            if (event.target === itemDialog) {
                itemDialog.close();
                resetDialog();
            }
        });
        itemType.addEventListener('change', updateModalFields);
        productSelect.addEventListener('change', function () {
            variantSelect.value = '';
            updateModalFields();
        });
        variantSelect.addEventListener('change', updateModalFields);
        rentalPeriodSelect.addEventListener('change', updateModalFields);
        descriptionInput.addEventListener('input', updateModalFields);
        priceInput.addEventListener('input', updateModalFields);
        confirmButton.addEventListener('click', addQuoteItem);
        itemDialog.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName !== 'BUTTON') {
                event.preventDefault();
                if (!confirmButton.disabled) addQuoteItem();
            }
        });

        itemsContainer.addEventListener('click', function (event) {
            var stepButton = event.target.closest('[data-quote-step]');
            if (stepButton) {
                var stepRow = stepButton.closest('[data-quote-item-row]');
                var stepInput = stepRow.querySelector('[data-quote-quantity]');
                var minimum = Number(stepInput.min || 1);
                var maximum = Number(stepInput.max || 99999);
                var current = Number.parseInt(stepInput.value, 10) || minimum;
                stepInput.value = Math.min(maximum, Math.max(minimum, current + Number(stepButton.dataset.quoteStep)));
                updateTotals();
                return;
            }

            var removeButton = event.target.closest('[data-remove-quote-item]');
            if (removeButton) {
                var row = removeButton.closest('[data-quote-item-row]');
                var removeInput = row.querySelector('[data-quote-remove-input]');
                removeInput.checked = !removeInput.checked;
                removeButton.textContent = removeInput.checked ? 'Undo remove' : 'Remove';
                removeButton.setAttribute('aria-pressed', String(removeInput.checked));
                row.classList.toggle('is-marked-remove', removeInput.checked);
                updateTotals();
                return;
            }

            var pendingRemoveButton = event.target.closest('[data-remove-new-quote-item]');
            if (pendingRemoveButton) {
                pendingRemoveButton.closest('[data-quote-item-row]').remove();
                updateTotals();
            }
        });
        itemsContainer.addEventListener('input', function (event) {
            if (event.target.matches('[data-quote-price], [data-quote-quantity], [name$="[description]"]')) {
                updateTotals();
            }
        });

        updateModalFields();
        updateTotals();
    });
</script>
@endpush
@endsection
