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

        <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <form action="{{ route('customer.quotations.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                @csrf
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <label for="customer_phone" class="text-sm font-bold text-slate-700">{{ __('site.checkout.phone') }}</label>
                        <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $customer->phone) }}" required autocomplete="tel" maxlength="32" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">
                        @error('customer_phone') <p class="text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-2">
                        <label for="shipping_address" class="text-sm font-bold text-slate-700">{{ __('site.checkout.address') }}</label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" required maxlength="2000" autocomplete="street-address" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">{{ old('shipping_address') }}</textarea>
                        <p class="text-xs leading-5 text-slate-500">{{ __('site.checkout.address_hint') }}</p>
                        @error('shipping_address') <p class="text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
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
