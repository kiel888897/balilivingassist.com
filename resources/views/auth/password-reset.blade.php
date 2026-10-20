@extends('layouts.public')

@section('title', __('site.auth.reset_password_title'))
@section('meta_description', __('site.auth.reset_password_intro'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="grid min-h-[65vh] place-items-center px-4 py-12">
    <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-7 shadow-sm md:p-10">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-bla-50 text-xl text-bla-700"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
        <h1 class="mt-5 font-display text-3xl font-extrabold text-bla-900">{{ __('site.auth.reset_password_title') }}</h1>
        <p class="mt-2 text-slate-500">{{ __('site.auth.reset_password_intro') }}</p>
        <form method="POST" action="{{ route('password.update') }}" class="mt-7 grid gap-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="grid gap-2">
                <label for="email" class="text-sm font-bold text-slate-700">{{ __('site.auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">
                @error('email') <p class="text-sm font-semibold text-rose-600" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-2">
                <label for="password" class="text-sm font-bold text-slate-700">{{ __('site.auth.new_password') }}</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">
                    <button type="button" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false" class="absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-bla-700"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                </div>
                @error('password') <p class="text-sm font-semibold text-rose-600" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-2">
                <label for="password_confirmation" class="text-sm font-bold text-slate-700">{{ __('site.auth.confirm_new_password') }}</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="6" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">
                    <button type="button" data-password-toggle="password_confirmation" aria-label="Tampilkan password" aria-pressed="false" class="absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-bla-700"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white hover:bg-bla-700">{{ __('site.auth.reset_password') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </form>
    </div>
</section>
@endsection
