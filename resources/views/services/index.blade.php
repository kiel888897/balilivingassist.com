@extends('layouts.public')

@section('title', __('site.services.title'))
@section('meta_description', __('site.services.intro'))

@section('content')
<section class="bg-gradient-to-br from-bla-50 via-white to-teal-50 py-14 md:py-20">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.services.eyebrow') }}</p>
        <h1 class="mt-3 max-w-4xl font-display text-4xl font-extrabold text-bla-900 md:text-5xl">{{ __('site.services.title') }}</h1>
        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ __('site.services.intro') }}</p>
    </div>
</section>

<section class="py-12 pb-16 md:py-16 md:pb-20">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        @include('public.partials.service-area-cards', ['serviceGroups' => $serviceGroups])
    </div>
</section>
@endsection
