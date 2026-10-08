<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('site.brand')) | {{ __('site.brand') }}</title>
    <meta name="description" content="@yield('meta_description', __('site.footer.tagline'))">
    <meta name="robots" content="@yield('meta_robots', 'index,follow')">
    <link rel="canonical" href="{{ request()->url() }}?lang={{ app()->getLocale() }}">
    <link rel="alternate" hreflang="en" href="{{ request()->url() }}?lang=en">
    <link rel="alternate" hreflang="id" href="{{ request()->url() }}?lang=id">
    <link rel="alternate" hreflang="x-default" href="{{ request()->url() }}?lang=en">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ __('site.brand') }}">
    <meta property="og:title" content="@yield('title', __('site.brand'))">
    <meta property="og:description" content="@yield('meta_description', __('site.footer.tagline'))">
    <meta property="og:url" content="{{ request()->url() }}?lang={{ app()->getLocale() }}">
    <meta property="og:image" content="{{ asset('images/bla-logos.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', __('site.brand'))">
    <meta name="twitter:description" content="@yield('meta_description', __('site.footer.tagline'))">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    @stack('head')
    @php
    $organizationSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => __('site.brand'),
    'url' => config('app.url'),
    'logo' => asset('images/bla-logos.png'),
    'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Bali, Indonesia'],
    ];
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    @include('public.partials.header')
    <main id="main-content">
        @yield('content')
    </main>
    @include('public.partials.footer')
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>

</html>