<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
    @php($cartQuantity = Auth::check() && Auth::user()->hasRole('customer') ? Auth::user()->cartItems()->sum('quantity') : 0)
    <div class="mx-auto flex min-h-[76px] w-[min(1200px,calc(100%-32px))] items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ __('site.brand') }} — {{ __('site.common.home') }}">
            <img src="{{ asset('images/bla-logos.png') }}" width="230" height="50" alt="{{ __('site.brand') }}" class="h-12 w-auto">
        </a>

        <nav class="hidden items-center gap-7 lg:flex" aria-label="Main navigation">
            <a class="font-semibold {{ request()->routeIs('shop', 'product.show') ? 'text-bla-700' : 'text-slate-600 hover:text-bla-700' }}" href="{{ route('shop') }}">{{ __('site.nav.shop') }}</a>
            <a class="font-semibold {{ request()->routeIs('services', 'service.show') ? 'text-bla-700' : 'text-slate-600 hover:text-bla-700' }}" href="{{ route('services') }}">{{ __('site.nav.services') }}</a>
            <a class="font-semibold {{ request()->routeIs('rental') ? 'text-bla-700' : 'text-slate-600 hover:text-bla-700' }}" href="{{ route('rental') }}">{{ __('site.nav.rental') }}</a>
            <a class="font-semibold {{ request()->routeIs('delivery') ? 'text-bla-700' : 'text-slate-600 hover:text-bla-700' }}" href="{{ route('delivery') }}">{{ __('site.nav.delivery') }}</a>
            <a class="font-semibold {{ request()->routeIs('portfolio') ? 'text-bla-700' : 'text-slate-600 hover:text-bla-700' }}" href="{{ route('portfolio') }}">{{ __('site.nav.portfolio') }}</a>
            <a class="font-semibold {{ request()->routeIs('contact') ? 'text-bla-700' : 'text-slate-600 hover:text-bla-700' }}" href="{{ route('contact') }}">{{ __('site.nav.contact') }}</a>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <div class="flex items-center rounded-lg border border-slate-200 p-1 text-xs font-bold" aria-label="{{ __('site.nav.language') }}">
                <a class="rounded-md px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-bla-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}" href="{{ route('language.switch', ['locale' => 'en', 'next' => request()->getRequestUri()]) }}" lang="en">EN</a>
                <a class="rounded-md px-2 py-1 {{ app()->getLocale() === 'id' ? 'bg-bla-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}" href="{{ route('language.switch', ['locale' => 'id', 'next' => request()->getRequestUri()]) }}" lang="id">ID</a>
            </div>
            @if (Auth::check() && Auth::user()->hasRole('customer'))
            <a href="{{ route('customer.account') }}" class="font-semibold text-slate-600 hover:text-bla-700">{{ __('site.nav.account') }}</a>
            @else
            <a href="{{ route('login') }}" class="font-semibold text-slate-600 hover:text-bla-700">{{ __('site.nav.login') }}</a>
            @endif
            <a href="{{ route('cart') }}" class="relative grid h-11 w-11 place-items-center rounded-xl border border-slate-200 text-lg text-bla-700 hover:bg-bla-50" aria-label="{{ __('site.nav.cart') }}">
                <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                @if ($cartQuantity > 0)
                <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-orange-600 px-1 text-[10px] font-bold leading-none text-white">{{ $cartQuantity > 99 ? '99+' : $cartQuantity }}</span>
                @endif
            </a>
        </div>

        <button type="button" class="grid h-11 w-11 place-items-center rounded-xl border border-slate-200 text-bla-800 lg:hidden" data-menu-toggle data-open-label="{{ __('site.nav.open_menu') }}" data-close-label="{{ __('site.nav.close_menu') }}" aria-expanded="false" aria-controls="mobile-navigation" aria-label="{{ __('site.nav.open_menu') }}">
            <i class="fa-solid fa-bars" data-menu-icon aria-hidden="true"></i>
        </button>
    </div>

    <div id="mobile-navigation" class="hidden border-t border-slate-100 bg-white px-4 pb-5 pt-3 lg:hidden" data-menu-panel>
        <nav class="mx-auto grid max-w-7xl gap-1" aria-label="Mobile navigation">
            <a class="rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-bla-50" href="{{ route('shop') }}"><i class="fa-solid fa-store mr-3 w-5 text-bla-600" aria-hidden="true"></i>{{ __('site.nav.shop') }}</a>
            <a class="rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-bla-50" href="{{ route('services') }}"><i class="fa-solid fa-screwdriver-wrench mr-3 w-5 text-bla-600" aria-hidden="true"></i>{{ __('site.nav.services') }}</a>
            <a class="rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-bla-50" href="{{ route('rental') }}"><i class="fa-solid fa-truck-ramp-box mr-3 w-5 text-bla-600" aria-hidden="true"></i>{{ __('site.nav.rental') }}</a>
            <a class="rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-bla-50" href="{{ route('delivery') }}"><i class="fa-solid fa-truck-fast mr-3 w-5 text-bla-600" aria-hidden="true"></i>{{ __('site.nav.delivery') }}</a>
            <a class="rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-bla-50" href="{{ route('portfolio') }}"><i class="fa-solid fa-images mr-3 w-5 text-bla-600" aria-hidden="true"></i>{{ __('site.nav.portfolio') }}</a>
            <a class="rounded-lg px-3 py-3 font-semibold text-slate-700 hover:bg-bla-50" href="{{ route('contact') }}"><i class="fa-solid fa-envelope mr-3 w-5 text-bla-600" aria-hidden="true"></i>{{ __('site.nav.contact') }}</a>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-4">
                <div class="flex gap-2">
                    <a class="rounded-md border px-3 py-2 text-sm font-bold {{ app()->getLocale() === 'en' ? 'border-bla-600 text-bla-700' : 'border-slate-200 text-slate-500' }}" href="{{ route('language.switch', ['locale' => 'en', 'next' => request()->getRequestUri()]) }}">EN</a>
                    <a class="rounded-md border px-3 py-2 text-sm font-bold {{ app()->getLocale() === 'id' ? 'border-bla-600 text-bla-700' : 'border-slate-200 text-slate-500' }}" href="{{ route('language.switch', ['locale' => 'id', 'next' => request()->getRequestUri()]) }}">ID</a>
                </div>
                <div class="flex items-center gap-5">
                    @if (Auth::check() && Auth::user()->hasRole('customer'))
                    <a href="{{ route('customer.account') }}" class="font-semibold text-bla-700">{{ __('site.nav.account') }}</a>
                    @else
                    <a href="{{ route('login') }}" class="font-semibold text-bla-700">{{ __('site.nav.login') }}</a>
                    @endif
                    <a href="{{ route('cart') }}" class="relative text-xl text-bla-700" aria-label="{{ __('site.nav.cart') }}">
                        <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                        @if ($cartQuantity > 0)
                        <span class="absolute -right-3 -top-2 grid h-5 min-w-5 place-items-center rounded-full bg-orange-600 px-1 text-[10px] font-bold leading-none text-white">{{ $cartQuantity > 99 ? '99+' : $cartQuantity }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </nav>
    </div>
</header>