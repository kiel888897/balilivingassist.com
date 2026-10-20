@extends('admin.layout')

@section('title', 'Add quotation')
@section('page_title', 'Create quotation')
@section('page_description', 'Choose a customer contact and prepare a quotation with one or more items.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.quotations') }}">Back to quotations</a>
@endsection

@section('content')
@if ($errors->any())
<div class="status-error" role="alert">
    <strong>Please correct the following:</strong>
    <ul style="margin:7px 0 0;padding-left:20px;">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

@php
$formItems = old('items', [[]]);
if (!is_array($formItems)) $formItems = [[]];
$formItems = array_values(array_filter($formItems, 'is_array'));
if (!$formItems) $formItems = [[]];
$selectedCustomerId = (string) old('customer_id', '');
@endphp

<form class="panel form-panel" action="{{ route('admin.quotations.store') }}" method="POST" data-admin-quotation-create>
    @csrf
    <div class="form-grid">
        <div class="field field-wide">
            <label for="customer_id">Customer contact</label>
            <select id="customer_id" name="customer_id" required data-customer-select>
                <option value="">Select a customer</option>
                @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}" {{ $selectedCustomerId === (string) $customer->id ? 'selected' : '' }}>{{ $customer->name }} — {{ $customer->email }}</option>
                @endforeach
            </select>
            @error('customer_id') <div class="field-error">{{ $message }}</div> @enderror
            @if ($customers->isEmpty())<span class="muted">Create a customer first from Contacts.</span>@endif
        </div>
        <div class="field">
            <label for="customer_phone">Phone number for this quotation</label>
            <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="32" data-customer-phone>
            @error('customer_phone') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="field">
            <label for="saved_address">Saved customer address</label>
            <select id="saved_address" data-saved-address-select>
                <option value="">Choose a saved address (optional)</option>
                @foreach ($customers as $customer)
                    @foreach ($customer->addresses as $address)
                    <option value="{{ $address->id }}" data-customer-id="{{ $customer->id }}" data-address="{{ $address->address }}">{{ $customer->name }} — {{ $address->label ?: 'Address' }}: {{ \Illuminate\Support\Str::limit($address->address, 80) }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="field field-wide">
            <label for="shipping_address">Delivery address</label>
            <textarea id="shipping_address" name="shipping_address" rows="3" maxlength="2000" required data-shipping-address>{{ old('shipping_address') }}</textarea>
            @error('shipping_address') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="field field-wide">
            <label for="customer_note">Customer note (optional)</label>
            <textarea id="customer_note" name="customer_note" rows="2" maxlength="2000">{{ old('customer_note') }}</textarea>
        </div>
        <div class="field field-wide">
            <label for="admin_note">Internal note (optional)</label>
            <textarea id="admin_note" name="admin_note" rows="2" maxlength="2000">{{ old('admin_note') }}</textarea>
        </div>
    </div>

    <div class="panel-heading" style="margin-top: 28px;">
        <div>
            <h2>Quotation items</h2>
            <p class="muted">Choose catalog items or enter custom lines. Prices are in IDR.</p>
        </div>
        <button class="btn btn-light" type="button" data-add-quotation-item><i class="fa-solid fa-plus" aria-hidden="true"></i> Add item</button>
    </div>
    <div class="grid gap-4" data-quotation-items>
        @foreach ($formItems as $index => $item)
        @include('admin.quotations.partials.create-item-row', ['index' => $index, 'item' => $item, 'products' => $products, 'variants' => $variants, 'services' => $services])
        @endforeach
    </div>
    @error('items') <div class="field-error">{{ $message }}</div> @enderror

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.quotations') }}">Cancel</a>
        <button class="btn" type="submit" {{ $customers->isEmpty() ? 'disabled' : '' }}>Create quotation</button>
    </div>
</form>

<template id="quotation-item-template">
    @include('admin.quotations.partials.create-item-row', ['index' => '__INDEX__', 'item' => [], 'products' => $products, 'variants' => $variants, 'services' => $services])
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-admin-quotation-create]');
    if (!form) return;
    var customerSelect = form.querySelector('[data-customer-select]');
    var phoneInput = form.querySelector('[data-customer-phone]');
    var addressSelect = form.querySelector('[data-saved-address-select]');
    var shippingAddress = form.querySelector('[data-shipping-address]');
    var items = form.querySelector('[data-quotation-items]');
    var template = document.getElementById('quotation-item-template');
    var nextIndex = items.querySelectorAll('[data-quotation-item-row]').length;

    function updateCustomer(preservePhone) {
        var option = customerSelect.options[customerSelect.selectedIndex];
        if (option && option.value && (!preservePhone || !phoneInput.value)) phoneInput.value = option.dataset.phone || '';
        Array.from(addressSelect.options).forEach(function (addressOption) {
            if (!addressOption.value) return;
            var matches = addressOption.dataset.customerId === customerSelect.value;
            addressOption.hidden = !matches;
            addressOption.disabled = !matches;
            if (!matches && addressOption.selected) addressSelect.value = '';
        });
    }

    function updateRow(row) {
        var type = row.querySelector('[data-item-type]').value;
        var product = row.querySelector('[data-item-product]');
        var variant = row.querySelector('[data-item-variant]');
        var service = row.querySelector('[data-item-service]');
        var period = row.querySelector('[data-item-period]');
        var description = row.querySelector('[data-item-description]');
        var quantity = row.querySelector('[data-item-quantity]');
        var priceInput = row.querySelector('[data-item-price]');
        var productGroup = row.querySelector('[data-product-group]');
        var variantGroup = row.querySelector('[data-variant-group]');
        var serviceGroup = row.querySelector('[data-service-group]');
        var periodGroup = row.querySelector('[data-period-group]');
        var isProduct = type === 'product';
        var isRental = type === 'rental';
        var usesProduct = isProduct || isRental;
        var selectedProduct = product.options[product.selectedIndex];

        productGroup.hidden = !usesProduct;
        product.disabled = !usesProduct;
        product.required = usesProduct;
        Array.from(product.options).forEach(function (option) {
            if (!option.value) return;
            var allowed = isProduct ? option.dataset.forSale === '1' : (isRental && option.dataset.forRental === '1');
            option.hidden = !allowed;
            option.disabled = !allowed;
            if (!allowed && option.selected) product.value = '';
        });
        selectedProduct = product.options[product.selectedIndex];

        var needsVariant = isProduct && selectedProduct && selectedProduct.dataset.hasVariants === '1';
        variantGroup.hidden = !needsVariant;
        variant.disabled = !needsVariant;
        variant.required = Boolean(needsVariant);
        Array.from(variant.options).forEach(function (option) {
            if (!option.value) return;
            var available = needsVariant && option.dataset.productId === product.value && Number(option.dataset.stock || 0) > 0;
            option.hidden = !available;
            option.disabled = !available;
            if (!available && option.selected) variant.value = '';
        });

        serviceGroup.hidden = type !== 'service';
        service.disabled = type !== 'service';
        periodGroup.hidden = !isRental;
        period.disabled = !isRental;
        period.required = isRental;
        var serviceSelected = type === 'service' && service.value;
        description.required = ['delivery', 'discount', 'custom'].indexOf(type) !== -1 || (type === 'service' && !serviceSelected);
        quantity.readOnly = ['service', 'delivery', 'discount', 'custom'].indexOf(type) !== -1;
        if (quantity.readOnly) quantity.value = 1;

        if (usesProduct && selectedProduct && selectedProduct.value && !description.dataset.edited) {
            description.value = selectedProduct.textContent.trim();
        } else if (type === 'service' && serviceSelected && !description.dataset.edited) {
            description.value = service.options[service.selectedIndex].dataset.name || '';
        }
        if (isProduct && selectedProduct && selectedProduct.value) {
            var variantOption = variant.options[variant.selectedIndex];
            var price = variant.value && variantOption ? variantOption.dataset.price : selectedProduct.dataset.salePrice;
            if (price && !priceInput.value) priceInput.value = price;
        } else if (isRental && selectedProduct && selectedProduct.value) {
            var rentalPrice = selectedProduct.dataset['rental' + period.value.charAt(0).toUpperCase() + period.value.slice(1) + 'Price'];
            if (rentalPrice && !priceInput.value) priceInput.value = rentalPrice;
        } else if (serviceSelected) {
            var servicePrice = service.options[service.selectedIndex].dataset.price;
            if (servicePrice && !priceInput.value) priceInput.value = servicePrice;
        }
        row.dataset.quoteType = type;
    }

    customerSelect.addEventListener('change', function () {
        updateCustomer(false);
        addressSelect.value = '';
        shippingAddress.value = '';
    });
    addressSelect.addEventListener('change', function () {
        var option = addressSelect.options[addressSelect.selectedIndex];
        if (option && option.value) shippingAddress.value = option.dataset.address || '';
    });

    items.addEventListener('change', function (event) {
        var row = event.target.closest('[data-quotation-item-row]');
        if (!row) return;
        if (event.target.matches('[data-item-type], [data-item-product], [data-item-variant], [data-item-service], [data-item-period]')) {
            row.querySelector('[data-item-price]').value = '';
        }
        if (event.target.matches('[data-item-product]')) {
            row.querySelector('[data-item-variant]').value = '';
        }
        updateRow(row);
    });
    items.addEventListener('input', function (event) {
        if (event.target.matches('[data-item-description]')) event.target.dataset.edited = '1';
    });
    items.addEventListener('click', function (event) {
        var remove = event.target.closest('[data-remove-quotation-item]');
        if (remove) remove.closest('[data-quotation-item-row]').remove();
    });

    form.querySelector('[data-add-quotation-item]').addEventListener('click', function () {
        var html = template.innerHTML.split('__INDEX__').join(String(nextIndex++));
        items.insertAdjacentHTML('beforeend', html);
        var row = items.lastElementChild;
        updateRow(row);
        row.querySelector('[data-item-type]').focus();
    });

    updateCustomer(true);
    items.querySelectorAll('[data-quotation-item-row]').forEach(updateRow);
});
</script>
@endpush
