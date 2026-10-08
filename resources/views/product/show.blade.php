@extends('layouts.public')

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags(($product->subtitle ? $product->subtitle . ' ' : '') . ($product->description ?: $product->name)), 155))
@section('og_type', 'product')

@php
    $productImages = $product->images;
    if ($productImages->isEmpty() && $product->image_path) {
        $productImages = collect([(object) ['image_path' => $product->image_path, 'alt_text' => $product->name]]);
    }

    $selectedVariant = $product->variants->first(function ($variant) {
        return $variant->stock_quantity > 0;
    }) ?: $product->variants->first();
    $selectedValueIds = $selectedVariant ? $selectedVariant->values->pluck('id')->map(function ($id) {
        return (int) $id;
    })->all() : [];
    $isRentalOrigin = request()->query('from') === 'rental';
    $rentalPrices = [
        'daily' => $product->rentalPriceForPeriod('daily'),
        'weekly' => $product->rentalPriceForPeriod('weekly'),
        'monthly' => $product->rentalPriceForPeriod('monthly'),
    ];
    $selectedRentalPeriod = collect($rentalPrices)->filter(function ($price) {
        return $price !== null && $price > 0;
    })->keys()->first() ?: 'daily';
    $basePrice = $isRentalOrigin
        ? (float) ($rentalPrices[$selectedRentalPeriod] ?? 0)
        : ($selectedVariant
            ? (float) $selectedVariant->unitPriceForQuantity(1)
            : (float) ($product->sale_price ?? $product->rental_price ?? 0));
    $specificationRows = collect(preg_split('/\r\n|\r|\n/', trim((string) $product->specifications)))
        ->map(function ($line) {
            $parts = explode('|', $line, 2);
            return ['name' => trim($parts[0]), 'details' => trim($parts[1] ?? '')];
        })
        ->filter(function ($row) {
            return $row['name'] !== '';
        })
        ->values();
    $variantData = $product->variants->map(function ($variant) {
        return [
    'id' => (int) $variant->id,
    'price' => (float) $variant->price,
            'stockQuantity' => (int) $variant->stock_quantity,
            'values' => $variant->values->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->all(),
            'priceTiers' => $variant->priceTiers->map(function ($tier) {
                return [
                    'minQuantity' => (int) $tier->min_quantity,
                    'unitPrice' => (float) $tier->unit_price,
                ];
            })->values()->all(),
        ];
    })->values();
    $isPurchasable = $product->for_sale && (
        $product->variants->isEmpty()
            ? $product->sale_price !== null && $product->attributes->isEmpty()
            : ($selectedVariant && $selectedVariant->stock_quantity > 0)
    );
    $isAvailable = $product->is_active && (
        $product->for_rental
        || ($product->for_sale && (
            $product->variants->isEmpty()
            || $product->variants->contains(function ($variant) {
                return $variant->stock_quantity > 0;
            })
        ))
    );
    $offerSchema = [
        '@type' => 'Offer',
        'priceCurrency' => 'IDR',
        'availability' => $isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ];
    if (!$isRentalOrigin || $basePrice > 0) {
        $offerSchema['price'] = $basePrice;
    }
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => Str::limit(strip_tags(($product->subtitle ? $product->subtitle . ' ' : '') . (string) $product->description), 300),
        'sku' => $selectedVariant->sku ?? $product->sku,
        'image' => $productImages->map(function ($image) {
            return asset('storage/' . $image->image_path);
        })->values()->all(),
        'category' => $product->category->name ?? null,
        'offers' => $offerSchema,
    ];
    $rentalWhatsappTemplate = __('site.rental.whatsapp_message', [
        'product' => $product->name,
        'period' => '__PERIOD__',
    ]);
@endphp

@push('head')
@if (!$isRentalOrigin || $basePrice > 0)
<meta property="product:price:amount" content="{{ $basePrice }}">
<meta property="product:price:currency" content="IDR">
@endif
<script type="application/ld+json">
    {!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
@endpush

@section('content')
<section class="py-7 md:py-10">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            @if ($isRentalOrigin)
            <a href="{{ route('rental') }}" class="hover:text-bla-700">{{ __('site.product.all_products') }}</a>
            @if ($product->category)
            <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
            <a href="{{ route('rental', ['category' => $product->category->slug]) }}" class="hover:text-bla-700">{{ $product->category->name }}</a>
            @endif
            @else
            <a href="{{ route('shop') }}" class="hover:text-bla-700">{{ __('site.product.all_products') }}</a>
            @if ($product->category)
            <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
            <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="hover:text-bla-700">{{ $product->category->name }}</a>
            @endif
            @endif
            <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
            <span class="font-semibold text-slate-800" aria-current="page">{{ $product->name }}</span>
        </nav>

        @if (session('status'))
        <p class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->has('cart'))
        <p class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">{{ $errors->first('cart') }}</p>
        @endif

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
            <div class="grid gap-3 sm:grid-cols-[64px_minmax(0,1fr)]">
                @if ($productImages->isNotEmpty())
                <div class="order-2 flex gap-2 overflow-x-auto sm:order-1 sm:flex-col sm:overflow-x-visible">
                    @foreach ($productImages as $image)
                    <button type="button" data-gallery-thumb data-image-url="{{ asset('storage/' . $image->image_path) }}" data-image-alt="{{ $image->alt_text ?: $product->name }}" aria-label="{{ __('site.product.view_image', ['number' => $loop->iteration]) }}" class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border {{ $loop->first ? 'border-orange-500' : 'border-slate-200' }} bg-white p-1 transition hover:border-orange-500">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="" class="h-full w-full object-contain" width="64" height="64" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    </button>
                    @endforeach
                </div>
                <div class="order-1 grid min-h-[320px] place-items-center overflow-hidden rounded-2xl bg-white sm:order-2 md:min-h-[480px]">
                    <img src="{{ asset('storage/' . $productImages->first()->image_path) }}" alt="{{ $productImages->first()->alt_text ?: $product->name }}" class="max-h-[480px] w-full object-contain p-4 md:p-8" width="900" height="900" data-gallery-main>
                </div>
                @else
                <div class="grid min-h-[320px] place-items-center rounded-2xl bg-gradient-to-br from-bla-50 via-white to-amber-50 text-8xl text-bla-500 sm:col-span-2 md:min-h-[480px]">
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                </div>
                @endif
            </div>

            <article class="lg:sticky lg:top-24 lg:self-start">
                @if ($product->category)
                <p class="text-xs font-extrabold uppercase tracking-[.14em] text-bla-600">{{ $product->category->name }}</p>
                @endif
                <h1 class="mt-2 font-display text-3xl font-extrabold leading-tight text-bla-900 md:text-4xl">{{ $product->name }}</h1>
                @if ($product->subtitle)
                <p class="mt-2 text-base text-slate-500">{{ $product->subtitle }}</p>
                @endif

                <div class="mt-5 border-b border-slate-200 pb-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $isRentalOrigin ? __('site.product.rental') : __('site.product.unit_price') }}</p>
                    <p class="mt-1 font-display text-2xl font-extrabold text-slate-900">
                        @if ($isRentalOrigin && $basePrice <= 0)
                        {{ __('site.rental.price_on_application') }}
                        @else
                        {{ __('site.common.currency') }} <span data-product-price>{{ number_format($basePrice, 0, ',', '.') }}</span>
                        @endif
                    </p>
                    @if (!$isRentalOrigin)
                    <p class="mt-1 text-sm text-slate-500" data-current-subtotal data-label="{{ __('site.product.subtotal') }}">
                        {{ __('site.product.subtotal') }}: {{ __('site.common.currency') }} {{ number_format($basePrice, 0, ',', '.') }}
                    </p>
                    @endif
                </div>

                @if (!$isRentalOrigin && $product->attributes->isNotEmpty())
                <div class="space-y-5 border-b border-slate-200 py-5">
                    @foreach ($product->attributes as $attribute)
                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-slate-800">{{ $attribute->name }}</legend>
                        @if ($attribute->input_type === 'select')
                        <select data-variant-attribute="{{ $attribute->id }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-bla-500 focus:ring-2 focus:ring-bla-100" aria-label="{{ $attribute->name }}">
                            @foreach ($attribute->values as $value)
                            <option value="{{ $value->id }}" {{ in_array((int) $value->id, $selectedValueIds, true) ? 'selected' : '' }}>{{ $value->value }}</option>
                            @endforeach
                        </select>
                        @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($attribute->values as $value)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:font-semibold has-[:checked]:text-slate-900">
                                <input type="radio" name="attribute_{{ $attribute->id }}" value="{{ $value->id }}" data-variant-attribute="{{ $attribute->id }}" class="h-3.5 w-3.5 border-slate-300 text-orange-600 focus:ring-orange-500" {{ in_array((int) $value->id, $selectedValueIds, true) ? 'checked' : '' }}>
                                {{ $value->value }}
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </fieldset>
                    @endforeach
                </div>
                @endif

                @if (!$isRentalOrigin)
                <p class="mt-3 min-h-5 text-sm font-semibold text-slate-600" data-variant-status data-available-label="{{ __('site.product.available') }}" role="status"></p>
                @endif

                @if ($isRentalOrigin && $product->for_rental)
                <div class="mt-5 border-b border-slate-200 pb-5">
                    <p class="mb-3 text-sm font-semibold text-slate-800">{{ __('site.rental.rental_period') }}</p>
                    <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('site.rental.rental_period') }}" data-rental-periods>
                        @foreach (['daily', 'weekly', 'monthly'] as $period)
                        @php $periodPrice = $rentalPrices[$period]; @endphp
                        <button type="button" class="rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $period === $selectedRentalPeriod ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-slate-200 text-slate-700 hover:border-orange-400' }} {{ $periodPrice === null || $periodPrice <= 0 ? 'cursor-not-allowed opacity-50' : '' }}" data-rental-period="{{ $period }}" data-period-label="{{ __('site.rental.periods.' . $period) }}" data-rental-price="{{ $periodPrice ?? '' }}" aria-pressed="{{ $period === $selectedRentalPeriod ? 'true' : 'false' }}" {{ $periodPrice === null || $periodPrice <= 0 ? 'disabled' : '' }}>
                            {{ __('site.rental.periods.' . $period) }}
                        </button>
                        @endforeach
                    </div>
                </div>
                <form action="{{ route('cart.items.store') }}" method="POST" class="mt-4" data-rental-add-form data-currency="{{ __('site.common.currency') }}" data-add-label="{{ __('site.product.add_to_cart') }}" data-unavailable-label="{{ __('site.product.rental_price_unavailable') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="rental_period" value="{{ $selectedRentalPeriod }}" data-rental-period-input>
                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-5">
                        <div class="inline-flex h-11 items-center rounded-lg border border-slate-200">
                            <button type="button" class="grid h-10 w-9 place-items-center text-slate-600 hover:text-bla-700" data-rental-quantity-step="-1" aria-label="{{ __('site.product.quantity') }} - 1"><i class="fa-solid fa-minus text-xs" aria-hidden="true"></i></button>
                            <input type="number" name="quantity" value="1" min="1" max="9999" data-rental-quantity class="h-10 w-10 border-0 p-0 text-center text-sm font-semibold text-slate-800 focus:ring-0" aria-label="{{ __('site.product.quantity') }}">
                            <button type="button" class="grid h-10 w-9 place-items-center text-slate-600 hover:text-bla-700" data-rental-quantity-step="1" aria-label="{{ __('site.product.quantity') }} + 1"><i class="fa-solid fa-plus text-xs" aria-hidden="true"></i></button>
                        </div>
                        <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-lg bg-orange-600 px-5 text-sm font-bold text-white transition hover:bg-orange-700 disabled:cursor-not-allowed disabled:bg-slate-300" data-rental-add-button {{ $basePrice <= 0 ? 'disabled' : '' }}>
                            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                            <span data-rental-add-label>{{ $basePrice > 0 ? __('site.product.add_to_cart') : __('site.product.out_of_stock') }}</span>
                        </button>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-slate-600" data-rental-subtotal data-label="{{ __('site.product.subtotal') }}">
                        {{ __('site.product.subtotal') }}:
                        @if ($basePrice > 0)
                        {{ __('site.common.currency') }} {{ number_format($basePrice, 0, ',', '.') }}
                        @else
                        {{ __('site.rental.price_on_application') }}
                        @endif
                    </p>
                </form>
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('bla.contact.whatsapp_number')) }}?text={{ rawurlencode(str_replace('__PERIOD__', __('site.rental.periods.' . $selectedRentalPeriod), $rentalWhatsappTemplate)) }}" data-rental-whatsapp data-message-template="{{ $rentalWhatsappTemplate }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex h-11 items-center gap-2 rounded-lg bg-green-600 px-5 text-sm font-bold text-white transition hover:bg-green-700">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>{{ __('site.rental.contact_us') }}
                </a>
                @elseif ($product->for_sale)
                <form action="{{ route('cart.items.store') }}" method="POST" class="mt-5" data-cart-add-form data-base-price="{{ $basePrice }}" data-currency="{{ __('site.common.currency') }}" data-out-of-stock-label="{{ __('site.product.out_of_stock') }}" data-variant-required-label="{{ __('site.product.select_variant') }}" data-variant-unavailable-label="{{ __('site.product.variant_unavailable') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" value="{{ $selectedVariant->id ?? '' }}" data-selected-variant>
                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-5">
                        <div class="inline-flex h-11 items-center rounded-lg border border-slate-200">
                            <button type="button" class="grid h-10 w-9 place-items-center text-slate-600 hover:text-bla-700" data-quantity-decrease aria-label="{{ __('site.product.quantity') }} - 1"><i class="fa-solid fa-minus text-xs" aria-hidden="true"></i></button>
                            <input type="number" name="quantity" value="1" min="1" max="{{ $selectedVariant ? min(9999, max(1, $selectedVariant->stock_quantity)) : 9999 }}" data-quantity-input class="h-10 w-10 border-0 p-0 text-center text-sm font-semibold text-slate-800 focus:ring-0" aria-label="{{ __('site.product.quantity') }}">
                            <button type="button" class="grid h-10 w-9 place-items-center text-slate-600 hover:text-bla-700" data-quantity-increase aria-label="{{ __('site.product.quantity') }} + 1"><i class="fa-solid fa-plus text-xs" aria-hidden="true"></i></button>
                        </div>
                        <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-lg bg-orange-600 px-5 text-sm font-bold text-white transition hover:bg-orange-700 disabled:cursor-not-allowed disabled:bg-slate-300" data-add-to-cart-button {{ !$isPurchasable ? 'disabled' : '' }}>
                            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                            <span data-add-to-cart-label data-add-label="{{ __('site.product.add_to_cart') }}" data-unavailable-label="{{ __('site.product.out_of_stock') }}">{{ $isPurchasable ? __('site.product.add_to_cart') : __('site.product.out_of_stock') }}</span>
                        </button>
                    </div>
                </form>
                @else
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('bla.contact.whatsapp_number')) }}?text={{ rawurlencode(__('site.product.whatsapp_message', ['product' => $product->name])) }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex h-11 items-center gap-2 rounded-lg bg-green-600 px-5 text-sm font-bold text-white transition hover:bg-green-700">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>{{ __('site.shop.ask_whatsapp') }}
                </a>
                @endif
                <p class="mt-5 text-sm italic leading-6 text-slate-600">
                    @if ($isRentalOrigin)
                    {{ __('site.rental.policy_notice') }}
                    @else
                    {{ __('site.product.delivery_note') }}
                    @endif
                    <a href="{{ route('contact') }}" class="font-semibold text-orange-600 underline underline-offset-2 hover:text-orange-700">{{ $isRentalOrigin ? __('site.rental.policy_link') : __('site.product.delivery_policy') }}</a>
                </p>
                <script type="application/json" data-product-variants>
                    {!! json_encode($variantData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
                </script>
            </article>
        </div>

        @if ($isRentalOrigin)
        <section class="mt-12 border-t border-slate-200 pt-8 md:mt-16 md:pt-10" data-product-tabs>
            <div class="flex gap-2 border-b border-slate-200" role="tablist">
                <button type="button" id="rental-specifications-tab" role="tab" aria-selected="true" aria-controls="rental-specifications-panel" data-product-tab="specifications" class="rounded-t-lg bg-orange-600 px-4 py-2 text-sm font-bold text-white">{{ __('site.rental.specifications') }}</button>
                <button type="button" id="rental-description-tab" role="tab" aria-selected="false" aria-controls="rental-description-panel" data-product-tab="description" class="rounded-t-lg px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100">{{ __('site.rental.description') }}</button>
            </div>
            <div id="rental-specifications-panel" role="tabpanel" aria-labelledby="rental-specifications-tab" data-product-tab-panel="specifications" class="pt-5">
                @if ($specificationRows->isNotEmpty())
                <div class="max-w-3xl overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left">
                                <th class="border border-slate-300 px-3 py-2">{{ __('site.rental.specifications') }}</th>
                                <th class="border border-slate-300 px-3 py-2">{{ __('site.rental.details') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($specificationRows as $specification)
                            <tr>
                                <th scope="row" class="w-1/2 border border-slate-300 px-3 py-2 text-left font-medium">{{ $specification['name'] }}</th>
                                <td class="border border-slate-300 px-3 py-2">{{ $specification['details'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-slate-600">{{ __('site.rental.no_specifications') }}</p>
                @endif
            </div>
            <div id="rental-description-panel" role="tabpanel" aria-labelledby="rental-description-tab" data-product-tab-panel="description" class="public-rich-text hidden pt-5 leading-7 text-slate-700">
                {!! $product->sanitized_description !!}
            </div>
        </section>
        @else
        <section class="mt-12 border-t border-slate-200 pt-8 md:mt-16 md:pt-10">
            <h2 class="inline-flex rounded-lg bg-orange-600 px-4 py-2 text-sm font-bold text-white">{{ __('site.product.description') }}</h2>
            <div class="public-rich-text mt-5 leading-7 text-slate-700">{!! $product->sanitized_description !!}</div>
        </section>
        @endif

        @if ($isRentalOrigin && $product->for_sale)
        @php
        $buyNowPrice = $selectedVariant ? $selectedVariant->unitPriceForQuantity(1) : $product->sale_price;
        @endphp
        <section class="mt-14 md:mt-16">
            <h2 class="text-center font-display text-2xl font-semibold text-orange-600 md:text-3xl">{{ __('site.rental.buy_now') }}</h2>
            <article class="mx-auto mt-6 grid max-w-lg grid-cols-[120px_minmax(0,1fr)] items-center gap-4 rounded-xl border border-slate-200 bg-white p-4">
                <a href="{{ route('product.show', $product->slug) }}" class="grid h-28 place-items-center bg-slate-50 p-2">
                    @if ($productImages->isNotEmpty())
                    <img src="{{ asset('storage/' . $productImages->first()->image_path) }}" alt="{{ $productImages->first()->alt_text ?: $product->name }}" class="h-full w-full object-contain" width="160" height="160" loading="lazy">
                    @else
                    <i class="fa-solid fa-box-open text-3xl text-slate-400" aria-hidden="true"></i>
                    @endif
                </a>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">{{ $product->name }}</h3>
                    @if ($buyNowPrice !== null)
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ __('site.common.currency') }} {{ number_format($buyNowPrice, 0, ',', '.') }}</p>
                    @endif
                    @if ($product->variants->isEmpty() && $product->attributes->isEmpty() && $product->sale_price !== null)
                    <form action="{{ route('cart.items.store') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-orange-600 px-3 py-2 text-xs font-bold text-white hover:bg-orange-700">
                            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>{{ __('site.product.add_to_cart') }}
                        </button>
                    </form>
                    @else
                    <a href="{{ route('product.show', $product->slug) }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-bla-700 hover:text-bla-900">{{ __('site.common.details') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
            </article>
        </section>
        @endif

        @if (!$isRentalOrigin && $relatedProducts->isNotEmpty())
        <section class="mt-14 md:mt-16">
            <h2 class="text-center font-display text-2xl font-semibold text-orange-600 md:text-3xl">{{ __('site.product.frequently_bought_together') }}</h2>
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedProducts as $related)
                @php
                    $relatedImage = $related->primary_image_path;
                    $relatedVariant = $related->variants->first();
                    $relatedPrice = $relatedVariant ? $relatedVariant->price : $related->sale_price;
                @endphp
                <article class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <a href="{{ route('product.show', $related->slug) }}" class="grid min-h-36 place-items-center bg-slate-50 p-3">
                        @if ($relatedImage)
                        <img src="{{ asset('storage/' . $relatedImage) }}" alt="{{ $related->name }}" class="h-32 w-full object-contain" width="240" height="200" loading="lazy">
                        @else
                        <i class="fa-solid fa-box-open text-4xl text-slate-400" aria-hidden="true"></i>
                        @endif
                    </a>
                    <div class="flex flex-col justify-center p-3">
                        <h3 class="text-sm font-semibold leading-5 text-slate-800">
                            <a href="{{ route('product.show', $related->slug) }}" class="hover:text-bla-700">{{ $related->name }}</a>
                        </h3>
                        @if ($relatedPrice !== null)
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ __('site.common.currency') }} {{ number_format($relatedPrice, 0, ',', '.') }}</p>
                        @endif
                        @if ($related->for_sale && $related->variants->isEmpty() && $related->attributes->isEmpty())
                        <form action="{{ route('cart.items.store') }}" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $related->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-orange-600 px-3 py-2 text-xs font-bold text-white hover:bg-orange-700">
                                <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>{{ __('site.product.add_to_cart') }}
                            </button>
                        </form>
                        @else
                        <a href="{{ route('product.show', ['slug' => $related->slug, 'from' => $isRentalOrigin ? 'rental' : null]) }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-bla-700 hover:text-bla-900">{{ __('site.common.details') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</section>
@endsection
