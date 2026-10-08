@extends('layouts.public')

@section('title', __('site.nav.rental'))
@section('meta_description', __('site.rental.intro'))

@section('content')
<section class="py-8 md:py-12">
    <div class="mx-auto w-[min(1600px,calc(100%-32px))]">
        <header class="mb-5">
            @php
            $activeCategory = $selectedCategory ? $categories->firstWhere('slug', $selectedCategory) : null;
            @endphp
            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('rental') }}" class="hover:text-bla-700">{{ __('site.rental.products') }}</a>
                @if ($activeCategory)
                <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
                <span class="font-semibold text-slate-800" aria-current="page">{{ $activeCategory->name }}</span>
                @endif
            </nav>
            <h1 class="sr-only">{{ $activeCategory->name ?? __('site.rental.products') }}</h1>
        </header>

        <details class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 lg:hidden">
            <summary class="flex cursor-pointer list-none items-center justify-between font-bold text-slate-800">
                {{ __('site.shop.categories') }}
                <i class="fa-solid fa-chevron-down text-sm text-slate-500" aria-hidden="true"></i>
            </summary>
            <div class="mt-4">
                @include('public.partials.catalog-categories', [
                    'categoryRoute' => 'rental',
                    'allCategoriesLabel' => __('site.shop.all_products'),
                    'categoryLabel' => __('site.shop.categories'),
                ])
            </div>
        </details>

        <div class="grid gap-8 lg:grid-cols-[250px_minmax(0,1fr)]">
            <aside class="hidden lg:block">
                <div class="sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="mb-4 font-display text-lg font-extrabold text-slate-900">{{ __('site.shop.categories') }}</h2>
                    @include('public.partials.catalog-categories', [
                        'categoryRoute' => 'rental',
                        'allCategoriesLabel' => __('site.shop.all_products'),
                        'categoryLabel' => __('site.shop.categories'),
                    ])
                </div>
            </aside>

            <div class="min-w-0">
                <form action="{{ route('rental') }}" method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    <label class="relative block w-full sm:max-w-xl">
                        <span class="sr-only">{{ __('site.shop.search') }}</span>
                        <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('site.shop.search') }}" class="h-10 w-full rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-10 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-bla-500 focus:ring-2 focus:ring-bla-100">
                        <button type="submit" class="absolute inset-y-0 right-0 grid w-12 place-items-center text-slate-600 hover:text-bla-700" aria-label="{{ __('site.shop.search') }}">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        </button>
                    </label>

                    <label class="flex shrink-0 items-center gap-2 text-sm text-slate-600">
                        <span>{{ __('site.shop.sort_by') }}</span>
                        <select name="sort" data-shop-sort class="rounded-lg border-0 bg-transparent py-2 pr-8 font-semibold text-slate-900 focus:ring-2 focus:ring-bla-200">
                            <option value="name-asc" {{ $sort === 'name-asc' ? 'selected' : '' }}>{{ __('site.shop.sort_name_asc') }}</option>
                            <option value="name-desc" {{ $sort === 'name-desc' ? 'selected' : '' }}>{{ __('site.shop.sort_name_desc') }}</option>
                            <option value="price-asc" {{ $sort === 'price-asc' ? 'selected' : '' }}>{{ __('site.shop.sort_price_asc') }}</option>
                            <option value="price-desc" {{ $sort === 'price-desc' ? 'selected' : '' }}>{{ __('site.shop.sort_price_desc') }}</option>
                        </select>
                    </label>
                </form>

                @if ($products->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($products as $product)
                    @php
                    $rentalMessage = __('site.rental.whatsapp_message', [
                        'product' => $product->name,
                        'period' => __('site.rental.periods.daily'),
                    ]);
                    @endphp
                    <article class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:shadow-md">
                        <a href="{{ route('product.show', ['slug' => $product->slug, 'from' => 'rental']) }}" class="group relative grid aspect-[4/3] place-items-center overflow-hidden bg-white p-3" aria-label="{{ __('site.shop.view_details') }}: {{ $product->name }}">
                            @if ($product->primary_image_path)
                            <img src="{{ asset('storage/' . $product->primary_image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-105" width="600" height="600" loading="lazy">
                            @elseif ($product->category && $product->category->image_path)
                            <img src="{{ asset('storage/' . $product->category->image_path) }}" alt="{{ $product->category->name }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-105" width="600" height="600" loading="lazy">
                            @else
                            <span class="grid h-full w-full place-items-center rounded-2xl bg-gradient-to-br from-amber-50 to-bla-50 text-6xl text-bla-400">
                                <i class="fa-solid fa-toolbox" aria-hidden="true"></i>
                            </span>
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $product->category->name ?? __('site.nav.rental') }}</p>
                            <h3 class="mt-1 min-h-10 font-display text-sm font-bold leading-5 text-slate-900">
                                <a href="{{ route('product.show', ['slug' => $product->slug, 'from' => 'rental']) }}" class="hover:text-bla-700">{{ $product->name }}</a>
                            </h3>
                            <p class="mt-1 font-display text-sm font-extrabold text-slate-900">
                                @if ($product->rental_price === null || (float) $product->rental_price <= 0)
                                {{ __('site.rental.price_on_application') }}
                                @else
                                {{ __('site.common.currency') }} {{ number_format($product->rental_price, 0, ',', '.') }}
                                @endif
                            </p>
                            <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                                <a href="{{ route('product.show', ['slug' => $product->slug, 'from' => 'rental']) }}" class="grid h-9 w-9 place-items-center rounded-lg bg-orange-600 text-sm text-white transition hover:bg-orange-700" aria-label="{{ __('site.shop.view_details') }}: {{ $product->name }}">
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('bla.contact.whatsapp_number')) }}?text={{ rawurlencode($rentalMessage) }}" target="_blank" rel="noopener noreferrer" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-lg text-green-700 transition hover:border-green-600 hover:bg-green-50" aria-label="{{ __('site.shop.ask_whatsapp') }}: {{ $product->name }}">
                                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
                @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <i class="fa-solid fa-toolbox text-4xl text-bla-500" aria-hidden="true"></i>
                    <p class="mt-4 text-slate-600">{{ $search || $selectedCategory ? __('site.shop.no_results') : __('site.rental.empty') }}</p>
                    @if ($search || $selectedCategory)
                    <a href="{{ route('rental') }}" class="mt-5 inline-flex items-center gap-2 font-bold text-bla-700 hover:text-bla-900">{{ __('site.shop.clear_filters') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
