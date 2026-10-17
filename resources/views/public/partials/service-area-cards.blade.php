<div class="grid items-stretch gap-5 md:grid-cols-2 xl:grid-cols-3">
    @foreach (__('site.services.areas') as $areaKey => $areaName)
    @php($areaServices = $serviceGroups->get($areaKey, collect()))
    @php($bgKey = !empty($withImages) ? $areaKey . '_bg' : null)
    <article class="{{ $bgKey ? 'relative isolate overflow-hidden ' : '' }}flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg md:p-8">
        @if ($bgKey)
        @include('public.partials.home-img', ['key' => $bgKey, 'widths' => [640, 1024], 'sizes' => '(min-width: 1280px) 440px, (min-width: 768px) 50vw, 100vw', 'class' => 'absolute inset-0 -z-20 h-full w-full object-cover', 'width' => 1024, 'height' => 683])
        <span class="absolute inset-0 -z-10 bg-white/90" aria-hidden="true"></span>
        @endif
        <span class="font-display text-sm font-extrabold tracking-wide text-amber-700">{{ sprintf('%02d', $loop->iteration) }}</span>
        <h2 class="mt-5 font-display text-xl font-extrabold leading-7 text-bla-900 md:text-2xl">{{ $areaName }}</h2>
        <ul class="mt-6 grid gap-4">
            @forelse ($areaServices as $service)
            <li class="flex items-start gap-3 text-sm leading-6 text-slate-700">
                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-600" aria-hidden="true"></span>
                <span>{{ $service->name }}</span>
            </li>
            @empty
            <li class="text-sm leading-6 text-slate-500">{{ __('site.services.empty') }}</li>
            @endforelse
        </ul>
    </article>
    @endforeach
</div>
