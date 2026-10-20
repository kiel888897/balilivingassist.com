@extends('admin.layout')

@section('title', $customer->exists ? 'Edit customer contact' : 'Add customer contact')
@section('page_title', $customer->exists ? 'Edit customer contact' : 'Add customer contact')
@section('page_description', 'Customer addresses are stored separately, so each customer can have multiple addresses.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.contacts') }}">Back to contacts</a>
@endsection

@section('content')
@php
$addressRows = old('addresses');
if (!is_array($addressRows)) {
    $addressRows = $customer->addresses->map(fn ($address) => ['id' => $address->id, 'label' => $address->label, 'address' => $address->address])->all();
}
@endphp
<form class="panel form-panel" action="{{ $formAction }}" method="POST">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="form-grid">
        <div class="field field-wide">
            <label for="name">Customer name</label>
            <input id="name" name="name" value="{{ old('name', $customer->name) }}" required maxlength="255" autocomplete="name">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="field">
            <label for="phone">Phone number</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="32" autocomplete="tel">
            @error('phone') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $customer->email) }}" required maxlength="255" autocomplete="email">
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="panel-heading" style="margin-top: 28px;">
        <div>
            <h2>Addresses</h2>
            <p class="muted">Add one or more addresses for this customer.</p>
        </div>
        <button class="btn btn-light" type="button" data-add-address><i class="fa-solid fa-plus" aria-hidden="true"></i> Add address</button>
    </div>
    <div class="grid gap-4" data-address-list data-next-index="{{ count($addressRows) }}">
        @foreach ($addressRows as $index => $address)
        <div class="rounded-xl border border-slate-200 p-4" data-address-row>
            @if (!empty($address['id']))<input type="hidden" name="addresses[{{ $index }}][id]" value="{{ $address['id'] }}">@endif
            <div class="form-grid">
                <div class="field">
                    <label for="address-label-{{ $index }}">Label (optional)</label>
                    <input id="address-label-{{ $index }}" name="addresses[{{ $index }}][label]" value="{{ $address['label'] ?? '' }}" maxlength="100" placeholder="Home, villa, office">
                </div>
                <div class="field field-wide">
                    <label for="address-value-{{ $index }}">Full address</label>
                    <textarea id="address-value-{{ $index }}" name="addresses[{{ $index }}][address]" rows="3" maxlength="2000" required>{{ $address['address'] ?? '' }}</textarea>
                    @error("addresses.$index.address") <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <button class="btn btn-light" type="button" data-remove-address><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Remove address</button>
        </div>
        @endforeach
    </div>
    @error('addresses') <div class="field-error">{{ $message }}</div> @enderror

    @if (!$customer->exists)
    <p class="muted" style="margin-top: 18px;">A secure temporary password will be generated. The customer can set their own password using “Forgot password?” on the sign-in page.</p>
    @endif

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.contacts') }}">Cancel</a>
        <button class="btn" type="submit">{{ $customer->exists ? 'Save changes' : 'Create customer' }}</button>
    </div>
</form>

<template id="customer-address-template">
    <div class="rounded-xl border border-slate-200 p-4" data-address-row>
        <div class="form-grid">
            <div class="field">
                <label for="address-label-__INDEX__">Label (optional)</label>
                <input id="address-label-__INDEX__" name="addresses[__INDEX__][label]" maxlength="100" placeholder="Home, villa, office">
            </div>
            <div class="field field-wide">
                <label for="address-value-__INDEX__">Full address</label>
                <textarea id="address-value-__INDEX__" name="addresses[__INDEX__][address]" rows="3" maxlength="2000" required></textarea>
            </div>
        </div>
        <button class="btn btn-light" type="button" data-remove-address><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Remove address</button>
    </div>
</template>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var list = document.querySelector('[data-address-list]');
        var template = document.getElementById('customer-address-template');
        var addButton = document.querySelector('[data-add-address]');
        if (!list || !template || !addButton) return;

        addButton.addEventListener('click', function () {
            var index = Number(list.dataset.nextIndex || 0);
            list.dataset.nextIndex = String(index + 1);
            var html = template.innerHTML.split('__INDEX__').join(String(index));
            list.insertAdjacentHTML('beforeend', html);
            list.lastElementChild.querySelector('textarea').focus();
        });

        list.addEventListener('click', function (event) {
            var removeButton = event.target.closest('[data-remove-address]');
            if (removeButton) removeButton.closest('[data-address-row]').remove();
        });
    });
</script>
@endpush
