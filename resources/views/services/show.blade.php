@extends('layouts.public')

@section('title', $service->name)
@section('meta_description', Str::limit(strip_tags($service->description ?: __('site.services.intro')), 155))
@section('og_type', 'article')

@section('content')
<section class="py-10 md:py-16">
    <div class="mx-auto w-[min(1000px,calc(100%-32px))]">
        <a href="{{ route('services') }}" class="inline-flex items-center gap-2 font-bold text-bla-700 hover:text-bla-900"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>{{ __('site.services.back') }}</a>
        <article class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            @if ($service->image_path)
            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->name }}" class="max-h-[460px] w-full object-cover" width="1200" height="720">
            @endif
            <div class="p-6 md:p-12">
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.services.areas.' . $service->service_area) }}</p>
                <h1 class="mt-3 font-display text-4xl font-extrabold text-bla-900 md:text-5xl">{{ $service->name }}</h1>
                @if ($service->description)
                <div class="public-rich-text mt-6">{!! $service->sanitized_description !!}</div>
                @endif
                <a class="mt-8 inline-flex items-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white hover:bg-bla-700" href="{{ route('contact') }}">{{ __('site.services.request') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </article>
    </div>
</section>
@endsection
