@extends('layouts.public')

@section('title', __('site.checkout.title'))
@section('meta_description', __('site.checkout.intro'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="min-h-[60vh] py-10 md:py-14">
    <div class="mx-auto w-[min(1100px,calc(100%-32px))]">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.cart.checkout') }}</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.checkout.title') }}</h1>
        <p class="mt-3 max-w-3xl leading-7 text-slate-600">{{ __('site.checkout.intro') }}</p>

        @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @php
        $defaultAddressChoice = $addresses->first()
            ? 'saved:' . $addresses->first()->id
            : 'new';
        $lastUsedAddressId = $addresses->first() && $addresses->first()->last_used_at
            ? $addresses->first()->id
            : null;
        $selectedAddressChoice = old('address_choice', $defaultAddressChoice);
        @endphp

        <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <form action="{{ route('customer.quotations.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                @csrf
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <label for="customer_phone" class="text-sm font-bold text-slate-700">{{ __('site.checkout.phone') }}</label>
                        <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $customer->phone) }}" required autocomplete="tel" maxlength="32" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">
                        @error('customer_phone') <p class="text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <fieldset class="grid gap-3">
                        <legend class="text-sm font-bold text-slate-700">{{ __('site.checkout.address') }}</legend>
                        <p class="-mt-1 text-xs leading-5 text-slate-500">{{ __('site.addresses.checkout_hint') }}</p>
                        @foreach ($addresses as $address)
                        @php($addressChoice = 'saved:' . $address->id)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition {{ $selectedAddressChoice === $addressChoice ? 'border-bla-500 bg-bla-50 ring-2 ring-bla-100' : 'border-slate-200 bg-white hover:border-bla-300' }}">
                            <input type="radio" name="address_choice" value="{{ $addressChoice }}" data-address-choice class="mt-1 accent-bla-700" {{ $selectedAddressChoice === $addressChoice ? 'checked' : '' }}>
                            <span class="min-w-0"><span class="flex flex-wrap items-center gap-2 font-bold text-slate-800">{{ $address->label ?: __('site.addresses.address') }}@if ($address->id === $lastUsedAddressId)<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-emerald-800">{{ __('site.addresses.last_used') }}</span>@endif</span><span class="mt-1 block whitespace-pre-line text-sm leading-6 text-slate-600">{{ $address->address }}</span></span>
                        </label>
                        @endforeach
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition {{ $selectedAddressChoice === 'new' ? 'border-bla-500 bg-bla-50 ring-2 ring-bla-100' : 'border-slate-200 bg-white hover:border-bla-300' }}">
                            <input type="radio" name="address_choice" value="new" data-address-choice class="accent-bla-700" {{ $selectedAddressChoice === 'new' ? 'checked' : '' }}>
                            <span class="font-bold text-bla-800"><i class="fa-solid fa-plus mr-2" aria-hidden="true"></i>{{ __('site.addresses.add_new') }}</span>
                        </label>
                        @error('address_choice') <p class="text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror

                        <div data-new-address-fields class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4" {{ $selectedAddressChoice === 'new' ? '' : 'hidden' }}>
                            <div class="grid gap-2"><label for="new_address_label" class="text-sm font-bold text-slate-700">{{ __('site.addresses.label') }}</label><input id="new_address_label" name="new_address_label" value="{{ old('new_address_label') }}" maxlength="100" placeholder="{{ __('site.addresses.label_placeholder') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100"></div>
                            <div class="grid gap-2"><label for="new_address" class="text-sm font-bold text-slate-700">{{ __('site.addresses.full_address') }}</label><textarea id="new_address" name="new_address" rows="4" maxlength="2000" autocomplete="street-address" class="rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">{{ old('new_address') }}</textarea><p class="text-xs leading-5 text-slate-500">{{ __('site.checkout.address_hint') }}</p>@error('new_address')<p class="text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
                            <label class="flex items-start gap-2 text-sm text-slate-700"><input type="checkbox" name="save_new_address" value="1" {{ old('save_new_address') ? 'checked' : '' }} class="mt-1 accent-bla-700"><span>{{ __('site.addresses.save_for_later') }}</span></label>
                        </div>
                    </fieldset>
                    <div class="grid gap-2">
                        <label for="customer_note" class="text-sm font-bold text-slate-700">{{ __('site.checkout.notes') }}</label>
                        <textarea id="customer_note" name="customer_note" rows="3" maxlength="2000" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">{{ old('customer_note') }}</textarea>
                        <p class="text-xs leading-5 text-slate-500">{{ __('site.checkout.notes_hint') }}</p>
                    </div>
                </div>
                <p class="mt-6 rounded-xl bg-bla-50 p-4 text-sm leading-6 text-bla-900">{{ __('site.checkout.no_payment') }}</p>
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('cart') }}" class="font-semibold text-slate-600 hover:text-bla-700">{{ __('site.checkout.back_to_cart') }}</a>
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-bla-600 px-6 py-3 font-bold text-white hover:bg-bla-700">
                        {{ __('site.checkout.submit') }} <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    </button>
                </div>
            </form>

            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <h2 class="font-display text-lg font-extrabold text-bla-900">{{ __('site.checkout.review_items') }}</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($lines as $line)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $line['product']->name }}</p>
                            @foreach ($line['options'] as $option)
                            <p class="mt-1 text-xs text-slate-500">
                                @if (($option['attribute'] ?? null) === 'Rental period')
                                {{ __('site.rental.rental_period') }}: {{ __('site.rental.periods.' . ($option['value'] ?? 'daily')) }}
                                @else
                                {{ $option['attribute'] }}: {{ $option['value'] }}
                                @endif
                            </p>
                            @endforeach
                            <p class="mt-1 text-xs text-slate-500">{{ $line['quantity'] }} × Rp {{ number_format($line['unit_price'], 0, ',', '.') }}</p>
                        </div>
                        <strong class="shrink-0 text-slate-800">Rp {{ number_format($line['line_total'], 0, ',', '.') }}</strong>
                    </div>
                    @endforeach
                </div>
                <p class="mt-3 flex justify-between border-t border-slate-100 pt-4">
                    <span class="text-sm text-slate-600">{{ __('site.checkout.subtotal') }}</span>
                    <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                </p>
                <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-600">{{ __('site.checkout.review_notice') }}</p>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var choices = document.querySelectorAll('[data-address-choice]');
        var newFields = document.querySelector('[data-new-address-fields]');
        var newAddress = document.getElementById('new_address');
        function updateAddressChoice() {
            var isNew = document.querySelector('[data-address-choice]:checked')?.value === 'new';
            if (!newFields) return;
            newFields.hidden = !isNew;
            if (newAddress) newAddress.required = isNew;
            newFields.closest('fieldset').querySelectorAll('label').forEach(function (label) {
                var input = label.querySelector('[data-address-choice]');
                if (!input) return;
                label.classList.toggle('border-bla-500', input.checked);
                label.classList.toggle('bg-bla-50', input.checked);
                label.classList.toggle('ring-2', input.checked);
                label.classList.toggle('ring-bla-100', input.checked);
            });
        }
        choices.forEach(function (choice) { choice.addEventListener('change', updateAddressChoice); });
        updateAddressChoice();
    });
</script>
@endpush
