@extends('layouts.public')

@section('title', __('site.shop.title'))
@section('meta_description', __('site.shop.intro'))

@section('content')
<section class="py-8 md:py-12">
    <div class="mx-auto w-[min(1600px,calc(100%-32px))]">
        @if (session('status'))
        <p class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->has('cart'))
        <p class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">{{ $errors->first('cart') }}</p>
        @endif


        <details class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 lg:hidden">
            <summary class="flex cursor-pointer list-none items-center justify-between font-bold text-slate-800">
                {{ __('site.shop.categories') }}
                <i class="fa-solid fa-chevron-down text-sm text-slate-500" aria-hidden="true"></i>
            </summary>
            <div class="mt-4">
                @include('public.partials.catalog-categories', [
                'categoryRoute' => 'shop',
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
                    'categoryRoute' => 'shop',
                    'allCategoriesLabel' => __('site.shop.all_products'),
                    'categoryLabel' => __('site.shop.categories'),
                    ])
                </div>
            </aside>

            <div class="min-w-0">
                <form action="{{ route('shop') }}" method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    <label class="relative block w-full sm:max-w-xl">
                        <span class="sr-only">{{ __('site.shop.search') }}</span>
                        <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('site.shop.search') }}" class="h-14 w-full rounded-xl border border-slate-200 bg-white py-3 pl-4 pr-12 text-base text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-bla-500 focus:ring-2 focus:ring-bla-100">
                        <button type="submit" class="absolute inset-y-0 right-0 grid w-12 place-items-center text-slate-600 hover:text-bla-700" aria-label="{{ __('site.shop.search') }}">
                            <i class="fa-solid fa-magnifying-glass text-lg" aria-hidden="true"></i>
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
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach ($products as $product)
                    <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-lg">
                        <a href="{{ route('product.show', $product->slug) }}" class="group relative grid aspect-square place-items-center overflow-hidden bg-white p-3" aria-label="{{ __('site.shop.view_details') }}: {{ $product->name }}">
                            @if ($product->primary_image_path)
                            <img src="{{ asset('storage/' . $product->primary_image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-105" width="600" height="600" loading="lazy">
                            @elseif ($product->category && $product->category->image_path)
                            <img src="{{ asset('storage/' . $product->category->image_path) }}" alt="{{ $product->category->name }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-105" width="600" height="600" loading="lazy">
                            @else
                            <span class="grid h-full w-full place-items-center rounded-2xl bg-gradient-to-br from-slate-50 to-bla-50 text-6xl text-bla-400">
                                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                            </span>
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col p-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $product->category->name ?? 'BLA' }}</p>
                            <h2 class="mt-1 min-h-5 line-clamp-2 font-display text-base font-bold leading-5 text-slate-900">
                                <a href="{{ route('product.show', $product->slug) }}" class="hover:text-bla-700">{{ $product->name }}</a>
                            </h2>
                            <p class="mt-1 font-display text-base font-extrabold text-slate-900">
                                {{ __('site.common.currency') }} {{ number_format($product->for_sale ? $product->sale_price : $product->rental_price, 0, ',', '.') }}
                                @if (!$product->for_sale && $product->for_rental)
                                <span class="text-sm font-semibold text-slate-500">/ {{ __('site.shop.rent') }}</span>
                                @endif
                            </p>
                            <div class="mt-auto flex items-center justify-between gap-3 pt-3">
                                @if ($product->for_sale && $product->sale_price !== null && $product->variants_count === 0 && $product->attributes_count === 0)
                                <form action="{{ route('cart.items.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="grid h-10 w-10 place-items-center rounded-lg bg-orange-600 text-base text-white transition hover:bg-orange-700" aria-label="{{ __('site.product.add_to_cart') }}: {{ $product->name }}">
                                        <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('product.show', $product->slug) }}" class="grid h-10 w-10 place-items-center rounded-lg bg-orange-600 text-base text-white transition hover:bg-orange-700" aria-label="{{ __('site.shop.view_details') }}: {{ $product->name }}">
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                                @endif
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('bla.contact.whatsapp_number')) }}?text={{ rawurlencode(__('site.product.whatsapp_message', ['product' => $product->name])) }}" target="_blank" rel="noopener noreferrer" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-lg text-slate-700 transition hover:border-bla-600 hover:text-bla-700" aria-label="{{ __('site.shop.ask_whatsapp') }}: {{ $product->name }}">
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
                    <i class="fa-solid fa-box-open text-4xl text-bla-500" aria-hidden="true"></i>
                    <p class="mt-4 text-slate-600">{{ $search || $selectedCategory ? __('site.shop.no_results') : __('site.shop.empty') }}</p>
                    @if ($search || $selectedCategory)
                    <a href="{{ route('shop') }}" class="mt-5 inline-flex items-center gap-2 font-bold text-bla-700 hover:text-bla-900">{{ __('site.shop.clear_filters') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection