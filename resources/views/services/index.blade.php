@extends('layouts.public')

@section('title', __('site.services.title'))
@section('meta_description', __('site.services.intro'))

@section('content')
<section class="relative isolate overflow-hidden bg-bla-900">
    <picture>
        <source media="(min-width: 1024px)" type="image/webp"
                srcset="{{ asset('images/services/hero-desktop-1024.webp') }} 1024w, {{ asset('images/services/hero-desktop-1365.webp') }} 1365w"
                sizes="100vw">
        <source media="(min-width: 640px)" type="image/webp" srcset="{{ asset('images/services/hero-tablet.webp') }}">
        <img src="{{ asset('images/services/hero-mobile.webp') }}" alt="" width="720" height="900"
             fetchpriority="high" decoding="async"
             class="absolute inset-0 -z-20 h-full w-full object-cover">
    </picture>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-bla-900/90 via-bla-900/60 to-bla-900/30 md:bg-gradient-to-r md:from-bla-900/90 md:via-bla-900/60 md:to-bla-900/10"></div>
    <div class="mx-auto flex min-h-[420px] w-[min(1400px,calc(100%-32px))] flex-col justify-end py-12 md:min-h-[460px] md:justify-center md:py-20">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-teal-100">{{ __('site.services.eyebrow') }}</p>
        <h1 class="mt-3 max-w-3xl font-display text-4xl font-extrabold text-white md:text-5xl">{{ __('site.services.title') }}</h1>
        <p class="mt-4 max-w-xl text-lg leading-8 text-white/90">{{ __('site.services.intro') }}</p>
        <div class="mt-7">
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 font-bold text-bla-900 transition hover:bg-teal-50">{{ __('site.services.quote') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

<section class="py-12 pb-16 md:py-16 md:pb-20">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        @include('public.partials.service-area-cards', ['serviceGroups' => $serviceGroups, 'withImages' => true])
    </div>
</section>
@endsection
