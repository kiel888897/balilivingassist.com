@extends('layouts.public')

@section('title', __('site.addresses.title'))
@section('meta_description', __('site.addresses.intro'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="min-h-[60vh] py-10 md:py-14">
    <div class="mx-auto w-[min(900px,calc(100%-32px))]">
        <a href="{{ route('customer.account') }}" class="inline-flex items-center gap-2 text-sm font-bold text-bla-700 hover:text-bla-900"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>{{ __('site.addresses.back_account') }}</a>
        <div class="mt-5"><p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.account') }}</p><h1 class="mt-2 font-display text-3xl font-extrabold text-bla-900">{{ __('site.addresses.title') }}</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ __('site.addresses.intro') }}</p></div>

        @if (session('status'))<p class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800" role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <h2 class="font-display text-lg font-extrabold text-bla-900">{{ __('site.addresses.add_title') }}</h2>
            <form action="{{ route('customer.addresses.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
                <div class="grid gap-2"><label for="new-label" class="text-sm font-bold text-slate-700">{{ __('site.addresses.label') }}</label><input id="new-label" name="label" value="{{ old('label') }}" maxlength="100" placeholder="{{ __('site.addresses.label_placeholder') }}" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100"></div>
                <div class="grid gap-2 sm:col-span-2"><label for="new-address" class="text-sm font-bold text-slate-700">{{ __('site.addresses.full_address') }}</label><textarea id="new-address" name="address" rows="3" required maxlength="2000" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">{{ old('address') }}</textarea></div>
                <div class="sm:col-span-2"><button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-bla-700 px-5 py-2.5 font-bold text-white hover:bg-bla-800"><i class="fa-solid fa-plus" aria-hidden="true"></i>{{ __('site.addresses.add_button') }}</button></div>
            </form>
        </section>

        <div class="mt-6 grid gap-4">
            @forelse ($addresses as $address)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3 p-5 md:p-6">
                    <div class="flex min-w-0 gap-4"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-bla-50 text-bla-700"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span><div class="min-w-0"><h2 class="font-display text-lg font-extrabold text-bla-900">{{ $address->label ?: __('site.addresses.address') }}</h2><p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $address->address }}</p>@if ($address->last_used_at)<p class="mt-2 text-xs font-semibold text-emerald-700">{{ __('site.addresses.last_used_at', ['date' => $address->last_used_at->format('d M Y')]) }}</p>@endif</div></div>
                    <div class="flex shrink-0 gap-2"><a href="#edit-address-{{ $address->id }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:border-bla-400 hover:text-bla-700">{{ __('site.addresses.edit') }}</a><form action="{{ route('customer.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('{{ __('site.addresses.confirm_delete') }}')">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100">{{ __('site.addresses.delete') }}</button></form></div>
                </div>
                <details id="edit-address-{{ $address->id }}" class="border-t border-slate-100 bg-slate-50 px-5 py-4 md:px-6"><summary class="cursor-pointer text-sm font-bold text-bla-700">{{ __('site.addresses.edit_address') }}</summary><form action="{{ route('customer.addresses.update', $address) }}" method="POST" class="mt-4 grid gap-4">@csrf @method('PUT')<div class="grid gap-2"><label for="label-{{ $address->id }}" class="text-sm font-bold text-slate-700">{{ __('site.addresses.label') }}</label><input id="label-{{ $address->id }}" name="label" value="{{ $address->label }}" maxlength="100" class="rounded-xl border border-slate-300 bg-white px-4 py-3"></div><div class="grid gap-2"><label for="address-{{ $address->id }}" class="text-sm font-bold text-slate-700">{{ __('site.addresses.full_address') }}</label><textarea id="address-{{ $address->id }}" name="address" rows="3" required maxlength="2000" class="rounded-xl border border-slate-300 bg-white px-4 py-3">{{ $address->address }}</textarea></div><div><button type="submit" class="rounded-xl bg-bla-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-bla-800">{{ __('site.addresses.save_changes') }}</button></div></form></details>
            </article>
            @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center"><span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></span><h2 class="mt-4 font-display text-lg font-extrabold text-bla-900">{{ __('site.addresses.empty_title') }}</h2><p class="mt-1 text-sm text-slate-600">{{ __('site.addresses.empty_text') }}</p></div>
            @endforelse
        </div>
    </div>
</section>
@endsection
