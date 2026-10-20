@extends('layouts.public')

@section('title', __('site.auth.forgot_password_title'))
@section('meta_description', __('site.auth.forgot_password_intro'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="grid min-h-[65vh] place-items-center px-4 py-12">
    <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-7 shadow-sm md:p-10">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-bla-50 text-xl text-bla-700"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
        <h1 class="mt-5 font-display text-3xl font-extrabold text-bla-900">{{ __('site.auth.forgot_password_title') }}</h1>
        <p class="mt-2 text-slate-500">{{ __('site.auth.forgot_password_intro') }}</p>
        @if (session('status'))
        <p class="mt-5 rounded-xl border border-bla-200 bg-bla-50 px-4 py-3 text-sm font-semibold text-bla-800" role="status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="mt-7 grid gap-5">
            @csrf
            <div class="grid gap-2">
                <label for="email" class="text-sm font-bold text-slate-700">{{ __('site.auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-bla-500 focus:ring-4 focus:ring-bla-100">
                @error('email') <p class="text-sm font-semibold text-rose-600" role="alert">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white hover:bg-bla-700">{{ __('site.auth.send_reset_link') }} <i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-500"><a class="font-bold text-bla-700 hover:text-bla-900" href="{{ route('login') }}">{{ __('site.auth.login') }}</a></p>
    </div>
</section>
@endsection
