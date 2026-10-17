{{-- Responsive WebP from public/images/home. Params: key, widths, sizes, alt, class, width, height, eager --}}
@php
    $srcset = collect($widths)->map(fn ($w) => asset("images/home/{$key}-{$w}.webp") . " {$w}w")->implode(', ');
    $fallback = asset("images/home/{$key}-" . $widths[0] . '.webp');
@endphp
<img src="{{ $fallback }}" srcset="{{ $srcset }}" sizes="{{ $sizes }}" alt="{{ $alt ?? '' }}"
     class="{{ $class ?? '' }}" width="{{ $width ?? 800 }}" height="{{ $height ?? 533 }}"
     @if (!empty($eager)) fetchpriority="high" @else loading="lazy" @endif decoding="async">
