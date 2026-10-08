@extends('layouts.public')

@section('title', __('site.home.title'))
@section('meta_description', __('site.home.intro'))

@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-bla-50 via-white to-teal-50">
    <div class="mx-auto grid w-[min(1200px,calc(100%-32px))] items-center gap-12 py-16 md:grid-cols-2 md:py-24">
        <div>
            <span class="inline-flex rounded-full bg-bla-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[.16em] text-bla-700">
                {{ __('site.home.eyebrow') }}
            </span>
            <h1 class="mt-6 max-w-2xl font-display text-4xl font-extrabold leading-tight tracking-tight text-bla-900 md:text-6xl">
                {{ __('site.home.title') }}
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">{{ __('site.home.intro') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a class="inline-flex items-center gap-2 rounded-xl bg-bla-600 px-6 py-3.5 font-bold text-white shadow-lg shadow-bla-900/10 transition hover:-translate-y-0.5 hover:bg-bla-700" href="{{ route('shop') }}">
                    {{ __('site.home.shop_now') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
                <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 font-bold text-bla-800 transition hover:border-bla-200 hover:bg-bla-50" href="{{ route('contact') }}">
                    {{ __('site.home.assistance') }}
                </a>
            </div>
            <div class="mt-10 flex items-center gap-3 text-sm font-semibold text-slate-500">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-white text-bla-600 shadow-sm"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                {{ __('site.home.trusted') }}
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('shop') }}" class="group rounded-3xl bg-bla-900 p-7 text-white shadow-xl sm:col-span-2">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-xl text-teal-200"><i class="fa-solid fa-house-chimney" aria-hidden="true"></i></span>
                <h2 class="mt-8 font-display text-2xl font-extrabold">{{ __('site.nav.shop') }}</h2>
                <p class="mt-2 text-sm leading-6 text-teal-50/70">{{ __('site.home.shop_subtitle') }}</p>
                <span class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-teal-200">{{ __('site.home.view_all') }} <i class="fa-solid fa-arrow-right transition group-hover:translate-x-1" aria-hidden="true"></i></span>
            </a>
            <a href="{{ route('services') }}" class="rounded-3xl border border-bla-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-bla-50 text-lg text-bla-700"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span>
                <h2 class="mt-5 font-display text-xl font-extrabold text-bla-900">{{ __('site.nav.services') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('site.home.service_subtitle') }}</p>
            </a>
            <a href="{{ route('rental') }}" class="rounded-3xl border border-amber-100 bg-amber-50 p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-white text-lg text-amber-700"><i class="fa-solid fa-truck-ramp-box" aria-hidden="true"></i></span>
                <h2 class="mt-5 font-display text-xl font-extrabold text-bla-900">{{ __('site.nav.rental') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('site.home.rental_subtitle') }}</p>
            </a>
        </div>
    </div>
</section>

@if ($categories->isNotEmpty())
<section class="py-16 md:py-20">
    <div class="mx-auto w-[min(1200px,calc(100%-32px))]">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.shop') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.home.shop_categories') }}</h2>
                <p class="mt-2 text-slate-500">{{ __('site.home.shop_subtitle') }}</p>
            </div>
            <a href="{{ route('shop') }}" class="font-bold text-bla-700 hover:text-bla-900">{{ __('site.home.view_all') }} <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i></a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                @if ($category->image_path)
                <img class="h-48 w-full object-cover" src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" loading="lazy" width="600" height="360">
                @else
                <div class="grid h-48 place-items-center bg-gradient-to-br from-bla-100 to-teal-50 text-5xl font-extrabold text-bla-600" aria-hidden="true">{{ strtoupper(substr($category->name, 0, 1)) }}</div>
                @endif
                <div class="p-5">
                    <h3 class="font-display text-lg font-extrabold text-bla-900">{{ $category->name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $category->description }}</p>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($serviceGroups->isNotEmpty())
<section class="bg-white py-16 md:py-20">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.services') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.home.service_categories') }}</h2>
                <p class="mt-2 text-slate-500">{{ __('site.home.service_subtitle') }}</p>
            </div>
            <a href="{{ route('services') }}" class="font-bold text-bla-700 hover:text-bla-900">{{ __('site.home.view_all') }} <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i></a>
        </div>
        @include('public.partials.service-area-cards', ['serviceGroups' => $serviceGroups])
    </div>
</section>
@endif

@if ($rentalCategories->isNotEmpty())
<section class="py-16 md:py-20">
    <div class="mx-auto w-[min(1200px,calc(100%-32px))]">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.rental') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.home.rental_categories') }}</h2>
                <p class="mt-2 text-slate-500">{{ __('site.home.rental_subtitle') }}</p>
            </div>
            <a href="{{ route('rental') }}" class="font-bold text-bla-700 hover:text-bla-900">{{ __('site.home.view_all') }} <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i></a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($rentalCategories as $category)
            <a href="{{ route('rental') }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-lg">
                @if ($category->image_path)
                <img class="h-36 w-full object-cover" src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" loading="lazy" width="480" height="288">
                @else
                <div class="grid h-36 place-items-center bg-gradient-to-br from-amber-50 to-bla-50 text-3xl text-bla-600"><i class="fa-solid fa-toolbox" aria-hidden="true"></i></div>
                @endif
                <div class="p-4"><h3 class="font-bold text-bla-900">{{ $category->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $category->description }}</p></div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($products->isNotEmpty())
<section class="bg-bla-50 py-16 md:py-20">
    <div class="mx-auto w-[min(1200px,calc(100%-32px))]">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.shop') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.home.featured') }}</h2>
            </div>
            <a href="{{ route('shop') }}" class="font-bold text-bla-700 hover:text-bla-900">{{ __('site.home.view_all') }} <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i></a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if ($product->primary_image_path)
                <div class="grid h-44 place-items-center bg-white p-4"><img src="{{ asset('storage/' . $product->primary_image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-contain" width="480" height="320" loading="lazy"></div>
                @else
                <div class="grid h-44 place-items-center bg-gradient-to-br from-bla-100 to-amber-50 text-5xl text-bla-500"><i class="fa-solid fa-box-open" aria-hidden="true"></i></div>
                @endif
                <div class="p-5">
                    <div class="flex justify-between gap-3 text-xs font-bold uppercase tracking-wide text-slate-500"><span>{{ $product->category->name ?? 'BLA' }}</span><span>{{ $product->for_rental ? __('site.shop.rent') : __('site.shop.buy') }}</span></div>
                    <h3 class="mt-3 font-display text-lg font-extrabold text-bla-900">{{ $product->name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ Str::limit(strip_tags((string) $product->description), 90) }}</p>
                    <p class="mt-4 font-display text-lg font-extrabold text-bla-700">{{ __('site.common.currency') }}{{ number_format($product->for_sale ? $product->sale_price : $product->rental_price, 0, ',', '.') }}</p>
                    <a href="{{ route('product.show', $product->slug) }}" class="mt-4 inline-flex items-center gap-2 font-bold text-bla-700">{{ __('site.shop.view_details') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-16 md:py-20">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        <div class="mb-10">
            <h2 class="font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.home.how_title') }}</h2>
            <p class="mt-3 max-w-2xl text-slate-500">{{ __('site.home.how_subtitle') }}</p>
        </div>
        <div class="relative grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-5">
            <span class="pointer-events-none absolute left-[10%] right-[10%] top-8 hidden h-[3px] bg-amber-600 shadow-sm lg:block" aria-hidden="true"></span>
            @foreach (__('site.home.steps') as $index => $step)
            <article class="relative grid grid-cols-[64px_minmax(0,1fr)] items-start gap-x-4 text-left md:flex md:flex-col md:items-center md:text-center">
                @if (!$loop->last)
                <span class="absolute bottom-[-2rem] left-[31px] top-16 w-0.5 bg-amber-600 md:hidden" aria-hidden="true"></span>
                @endif
                <span class="relative z-10 grid h-16 w-16 place-items-center rounded-full bg-bla-900 font-display text-sm font-extrabold text-white shadow-md">{{ sprintf('%02d', $index + 1) }}</span>
                <div class="pt-2 md:mt-8 md:pt-0">
                    <h3 class="font-display text-lg font-extrabold leading-6 text-bla-900">{{ $step['title'] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $step['description'] }}</p>
                </div>
            </article>
            @endforeach
        </div>
        <p class="mt-12 text-center font-display text-lg font-extrabold leading-7 text-slate-800 md:mt-16 md:text-xl">{{ __('site.home.work_support') }}</p>
    </div>
</section>

<section class="bg-bla-900 py-14 text-white">
    <div class="mx-auto flex w-[min(1200px,calc(100%-32px))] flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-display text-2xl font-extrabold">{{ __('site.home.assistance') }}</h2>
            <p class="mt-2 text-teal-50/70">{{ __('site.contact.intro') }}</p>
        </div>
        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 font-bold text-bla-800 hover:bg-teal-50">{{ __('site.footer.contact') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>
@endsection
