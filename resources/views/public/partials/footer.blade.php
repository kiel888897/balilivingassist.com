<footer class="mt-16 bg-bla-900 text-white">
    <div class="mx-auto grid w-[min(1200px,calc(100%-32px))] gap-10 py-12 md:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <a href="{{ route('home') }}" class="inline-flex rounded-xl bg-white p-2">
                <img src="{{ asset('images/bla-logos.png') }}" width="230" height="50" alt="{{ __('site.brand') }}" class="h-12 w-auto">
            </a>
            <p class="mt-4 max-w-md leading-7 text-teal-50/75">{{ __('site.footer.tagline') }}</p>
        </div>
        <div>
            <h2 class="font-display text-sm font-extrabold uppercase tracking-wider text-teal-100">{{ __('site.footer.explore') }}</h2>
            <ul class="mt-4 grid gap-3 text-sm text-teal-50/80">
                <li><a class="hover:text-white" href="{{ route('shop') }}">{{ __('site.nav.shop') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('services') }}">{{ __('site.nav.services') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('rental') }}">{{ __('site.nav.rental') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('delivery') }}">{{ __('site.nav.delivery') }}</a></li>
                <li><a class="hover:text-white" href="{{ route('portfolio') }}">{{ __('site.nav.portfolio') }}</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-display text-sm font-extrabold uppercase tracking-wider text-teal-100">{{ __('site.footer.follow') }}</h2>
            <div class="mt-4 flex gap-3">
                @foreach ([
                    ['label' => 'Instagram', 'url' => config('bla.social.instagram'), 'icon' => 'fa-brands fa-instagram'],
                    ['label' => 'TikTok', 'url' => config('bla.social.tiktok'), 'icon' => 'fa-brands fa-tiktok'],
                    ['label' => 'Threads', 'url' => config('bla.social.threads'), 'icon' => 'fa-brands fa-threads'],
                    ['label' => 'Facebook', 'url' => config('bla.social.facebook'), 'icon' => 'fa-brands fa-facebook-f'],
                ] as $social)
                @if (filter_var($social['url'], FILTER_VALIDATE_URL))
                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="grid h-10 w-10 place-items-center rounded-full border border-white/20 text-lg text-white hover:bg-white/10" aria-label="{{ $social['label'] }}">
                    <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                </a>
                @endif
                @endforeach
            </div>
            <div class="mt-5 grid gap-3 text-sm text-teal-50/80">
                <a href="mailto:{{ config('bla.contact.email') }}" class="inline-flex items-center gap-3 hover:text-white">
                    <i class="fa-solid fa-envelope w-4" aria-hidden="true"></i>{{ config('bla.contact.email') }}
                </a>
                <a href="{{ config('bla.social.whatsapp') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 hover:text-white">
                    <i class="fa-brands fa-whatsapp w-4" aria-hidden="true"></i>{{ config('bla.contact.whatsapp_number') }}
                </a>
            </div>
            <a href="{{ route('contact') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-teal-100 hover:text-white">
                {{ __('site.footer.contact') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex w-[min(1200px,calc(100%-32px))] flex-wrap justify-between gap-2 py-5 text-xs text-teal-50/60">
            <span>© {{ now()->year }} {{ __('site.brand') }}. {{ __('site.footer.rights') }}</span>
            <span>{{ __('site.contact.area') }}</span>
        </div>
    </div>
</footer>