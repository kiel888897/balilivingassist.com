@extends('layouts.public')

@section('title', __('site.portfolio.title'))
@section('meta_description', __('site.portfolio.intro'))

@section('content')
<section class="bg-gradient-to-br from-bla-50 via-white to-teal-50 py-16 md:py-24">
    <div class="mx-auto w-[min(1000px,calc(100%-32px))] text-center">
        <span class="inline-flex rounded-full bg-bla-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[.16em] text-bla-700">{{ __('site.portfolio.eyebrow') }}</span>
        <h1 class="mt-5 font-display text-4xl font-extrabold text-bla-900 md:text-6xl">{{ __('site.portfolio.title') }}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-slate-600">{{ __('site.portfolio.intro') }}</p>
    </div>
</section>
<section class="py-14 md:py-20">
    <div class="mx-auto w-[min(1200px,calc(100%-32px))]">
        @if ($projects->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($projects as $project)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg" data-portfolio-slider>
                <div class="relative aspect-[4/3] overflow-hidden bg-bla-50">
                    @foreach ($project->images as $image)
                    <img
                        src="{{ asset('storage/' . $image->image_path) }}"
                        alt="{{ $image->alt_text ?: $project->title }}"
                        class="absolute inset-0 h-full w-full object-cover {{ $loop->first ? '' : 'hidden' }}"
                        data-portfolio-slide
                        aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                        width="800"
                        height="600"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    @endforeach
                    @if ($project->is_sample)
                    <span class="absolute left-4 top-4 z-10 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-extrabold text-amber-900 shadow-sm">{{ __('site.portfolio.example_badge') }}</span>
                    @endif
                    @if ($project->images->count() > 1)
                    <button type="button" class="absolute left-3 top-1/2 z-10 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-bla-900 shadow transition hover:bg-white" data-portfolio-step="-1" aria-label="{{ __('site.portfolio.previous_photo') }}: {{ $project->title }}">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="absolute right-3 top-1/2 z-10 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-bla-900 shadow transition hover:bg-white" data-portfolio-step="1" aria-label="{{ __('site.portfolio.next_photo') }}: {{ $project->title }}">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                    <span class="absolute bottom-3 right-3 z-10 rounded-full bg-slate-950/70 px-3 py-1 text-xs font-bold text-white" data-portfolio-count aria-live="polite">1 / {{ $project->images->count() }}</span>
                    @endif
                </div>
                <div class="p-5">
                    <h2 class="font-display text-xl font-extrabold text-bla-900">{{ $project->title }}</h2>
                    @if ($project->description)
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $project->description }}</p>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
        @else
        <div class="mx-auto max-w-2xl text-center">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-bla-50 text-2xl text-bla-600"><i class="fa-solid fa-images" aria-hidden="true"></i></span>
            <h2 class="mt-5 font-display text-2xl font-extrabold text-bla-900">{{ __('site.portfolio.empty_title') }}</h2>
            <p class="mt-3 leading-7 text-slate-500">{{ __('site.portfolio.empty') }}</p>
            <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white hover:bg-bla-700">{{ __('site.footer.contact') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        @endif
    </div>
</section>
@endsection
