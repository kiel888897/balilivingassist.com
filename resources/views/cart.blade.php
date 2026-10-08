@extends('layouts.public')

@section('title', __('site.cart.title'))
@section('meta_description', __('site.cart.empty'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="min-h-[55vh] py-10 md:py-16">
    <div class="mx-auto w-[min(1200px,calc(100%-32px))]">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.cart') }}</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.cart.title') }}</h1>

        @if (session('status'))
        <p class="mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</p>
        @endif
        @if (session('warning'))
        <p class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900" role="status">{{ session('warning') }}</p>
        @endif
        @if ($errors->has('cart'))
        <p class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">{{ $errors->first('cart') }}</p>
        @endif

        @if (count($lines))
        <div class="mt-7 grid items-start gap-7 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-4" data-cart-lines>
                @foreach ($lines as $line)
                <article class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[128px_minmax(0,1fr)] sm:p-5" data-cart-line="{{ $line['key'] }}">
                    <a href="{{ route('product.show', ['slug' => $line['product']->slug, 'from' => $line['rental_period'] ? 'rental' : null]) }}" class="grid aspect-square place-items-center overflow-hidden rounded-xl bg-slate-50">
                        @if ($line['image_path'])
                        <img src="{{ asset('storage/' . $line['image_path']) }}" alt="{{ $line['product']->name }}" class="h-full w-full object-contain p-2" width="240" height="240" loading="lazy">
                        @else
                        <i class="fa-solid fa-box-open text-4xl text-slate-400" aria-hidden="true"></i>
                        @endif
                    </a>
                    <div class="flex min-w-0 flex-col">
                        <form id="cart-item-{{ $line['key'] }}" action="{{ route('cart.items.update') }}" method="POST" class="flex min-w-0 flex-1 flex-col" data-cart-item-form data-error-message="{{ __('site.cart.update_failed') }}">
                            @csrf
                            <input type="hidden" name="cart_item_id" value="{{ $line['key'] }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <label class="mt-1 inline-flex shrink-0 items-center" title="{{ __('site.cart.select_for_quote') }}">
                                        <input type="hidden" name="is_selected" value="0">
                                        <input type="checkbox" name="is_selected" value="1" data-cart-selection {{ $line['is_selected'] ? 'checked' : '' }} aria-label="{{ __('site.cart.select_for_quote') }}: {{ $line['product']->name }}">
                                    </label>
                                    <div class="min-w-0">
                                        <h2 class="font-display text-lg font-bold text-slate-900">
                                            <a href="{{ route('product.show', ['slug' => $line['product']->slug, 'from' => $line['rental_period'] ? 'rental' : null]) }}" class="hover:text-bla-700">{{ $line['product']->name }}</a>
                                        </h2>
                                        @if ($line['rental_period'])
                                        <p class="mt-1 text-sm text-slate-600">{{ __('site.cart.rental_period') }}: {{ __('site.rental.periods.' . $line['rental_period']) }}</p>
                                        @endif
                                        @if ($line['variant'])
                                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm text-slate-600">
                                            @foreach ($line['variant']->values as $value)
                                            <span>{{ $value->attribute->name }}: {{ $value->value }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @if ($line['line_total'] !== null)
                                <p class="font-display text-lg font-extrabold text-slate-900" data-cart-line-total>{{ __('site.common.currency') }} {{ number_format($line['line_total'], 0, ',', '.') }}</p>
                                @endif
                            </div>

                            @if ($line['unit_price'] !== null)
                            <p class="mt-2 text-sm text-slate-500">{{ __('site.product.unit_price') }}: {{ __('site.common.currency') }} <span data-cart-unit-price>{{ number_format($line['unit_price'], 0, ',', '.') }}</span></p>
                            @endif
                            @unless ($line['available'])
                            <p class="mt-2 text-sm font-semibold text-red-700">{{ __('site.product.out_of_stock') }}</p>
                            @endunless

                        </form>
                        <div class="mt-auto flex flex-wrap items-center justify-end gap-3 pt-4">
                            @if ($line['available'])
                            <div class="inline-flex h-10 items-center overflow-hidden rounded-lg border border-slate-200">
                                <button type="button" form="cart-item-{{ $line['key'] }}" class="grid h-10 w-10 place-items-center text-bla-700 hover:bg-bla-50 disabled:cursor-not-allowed disabled:text-slate-300" data-cart-step="-1" aria-label="{{ __('site.cart.decrease_quantity') }}" {{ $line['quantity'] <= 1 ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-minus text-xs" aria-hidden="true"></i>
                                </button>
                                <input type="number" form="cart-item-{{ $line['key'] }}" name="quantity" value="{{ $line['quantity'] }}" min="1" max="{{ $line['variant'] ? min(9999, max(1, $line['variant']->stock_quantity)) : 9999 }}" class="h-10 w-14 border-0 p-0 text-center text-sm font-semibold text-slate-800 focus:ring-0" data-cart-quantity aria-label="{{ __('site.product.quantity') }}">
                                <button type="button" form="cart-item-{{ $line['key'] }}" class="grid h-10 w-10 place-items-center text-bla-700 hover:bg-bla-50 disabled:cursor-not-allowed disabled:text-slate-300" data-cart-step="1" aria-label="{{ __('site.cart.increase_quantity') }}" {{ $line['variant'] && $line['quantity'] >= $line['variant']->stock_quantity ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                                </button>
                            </div>
                            @else
                            <span class="text-sm text-slate-500">{{ __('site.cart.quantity_unavailable') }}</span>
                            @endif
                            <form action="{{ route('cart.items.destroy') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cart_item_id" value="{{ $line['key'] }}">
                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-800" data-cart-remove>{{ __('site.cart.remove') }}</button>
                            </form>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-red-700" data-cart-feedback aria-live="polite"></p>
                    </div>
                </article>
                @endforeach
            </div>

            <aside class="rounded-2xl border border-slate-200 bg-white p-5 lg:sticky lg:top-24">
                <h2 class="font-display text-lg font-extrabold text-slate-900">{{ __('site.cart.subtotal') }}</h2>
                <div class="mt-3 border-t border-slate-100 pt-4">
                    <p class="flex items-center gap-1 text-sm text-slate-600">
                        <span>{{ __('site.cart.selected_subtotal') }}</span>
                        <span class="text-xs text-slate-500" data-cart-selected-count>({{ $selectedItemCount }})</span>
                    </p>
                    <p class="mt-2 text-right font-display text-xl font-extrabold text-slate-900">
                        {{ __('site.common.currency') }} <span data-cart-selected-subtotal>{{ number_format($subtotal, 0, ',', '.') }}</span>
                    </p>
                </div>
                <p class="mt-4 rounded-xl bg-bla-50 p-3 text-sm leading-6 text-bla-900">{{ __('site.cart.quote_notice') }}</p>
                <form action="{{ route('customer.checkout') }}" method="GET">
                    <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white transition hover:bg-bla-700 disabled:cursor-not-allowed disabled:bg-slate-300" data-cart-checkout {{ $selectedItemCount === 0 ? 'disabled' : '' }}>
                        {{ __('site.cart.checkout') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>
                <a href="{{ route('shop') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-3 font-bold text-slate-700 hover:bg-slate-50">{{ __('site.cart.continue') }}</a>
            </aside>
        </div>
        @else
        <div class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-bla-50 text-2xl text-bla-600"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
            <h2 class="mt-5 font-display text-2xl font-extrabold text-bla-900">{{ __('site.cart.empty_title') }}</h2>
            <p class="mx-auto mt-3 max-w-lg leading-7 text-slate-500">{{ __('site.cart.empty') }}</p>
            <a href="{{ route('shop') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white hover:bg-bla-700">{{ __('site.cart.shop') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        @endif
    </div>
</section>
@endsection
