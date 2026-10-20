@extends('layouts.public')

@section('title', app()->getLocale() === 'id' ? 'Edit Profil' : 'Edit Profile')
@section('meta_description', app()->getLocale() === 'id' ? 'Perbarui informasi profil pelanggan.' : 'Update your customer profile information.')
@section('meta_robots', 'noindex,nofollow')

@section('content')
@php($isId = app()->getLocale() === 'id')
<section class="mx-auto min-h-[60vh] w-[min(760px,calc(100%-32px))] py-10 md:py-14">
    <a href="{{ route('customer.account') }}" class="inline-flex items-center gap-2 text-sm font-bold text-bla-700 hover:text-bla-900">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>{{ $isId ? 'Kembali ke akun' : 'Back to account' }}
    </a>

    <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-9">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ $isId ? 'Akun pelanggan' : 'Customer account' }}</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold text-bla-900">{{ $isId ? 'Edit profil' : 'Edit profile' }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $isId ? 'Perbarui nama, email, dan nomor telepon. Kosongkan bagian kata sandi jika tidak ingin mengubahnya.' : 'Update your name, email, and phone number. Leave the password fields blank if you do not want to change it.' }}</p>

        @if (session('status'))
        <p class="mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <p class="font-bold">{{ $isId ? 'Periksa kembali isian berikut:' : 'Please check the following fields:' }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form class="mt-7 space-y-5" method="POST" action="{{ route('customer.profile.update') }}">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="mb-1.5 block text-sm font-bold text-slate-700">{{ $isId ? 'Nama lengkap' : 'Full name' }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-bla-500 focus:outline-none focus:ring-2 focus:ring-bla-100">
            </div>
            <div>
                <label for="email" class="mb-1.5 block text-sm font-bold text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-bla-500 focus:outline-none focus:ring-2 focus:ring-bla-100">
            </div>
            <div>
                <label for="phone" class="mb-1.5 block text-sm font-bold text-slate-700">{{ $isId ? 'Nomor telepon' : 'Phone number' }}</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="32" autocomplete="tel" placeholder="+62 812 3456 7890" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-bla-500 focus:outline-none focus:ring-2 focus:ring-bla-100">
            </div>

            <div class="border-t border-slate-200 pt-5">
                <h2 class="font-display text-lg font-extrabold text-bla-900">{{ $isId ? 'Ubah kata sandi' : 'Change password' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $isId ? 'Opsional. Minimal 6 karakter.' : 'Optional. At least 6 characters.' }}</p>
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-bold text-slate-700">{{ $isId ? 'Kata sandi baru' : 'New password' }}</label>
                <input id="password" name="password" type="password" minlength="6" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-bla-500 focus:outline-none focus:ring-2 focus:ring-bla-100">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-bold text-slate-700">{{ $isId ? 'Konfirmasi kata sandi baru' : 'Confirm new password' }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="6" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-bla-500 focus:outline-none focus:ring-2 focus:ring-bla-100">
            </div>

            <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-bla-600 px-5 py-3 font-bold text-white transition hover:bg-bla-700">{{ $isId ? 'Simpan perubahan' : 'Save changes' }}</button>
                <a href="{{ route('customer.account') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 px-5 py-3 font-bold text-slate-700 transition hover:bg-slate-50">{{ $isId ? 'Batal' : 'Cancel' }}</a>
            </div>
        </form>
    </div>
</section>
@endsection
