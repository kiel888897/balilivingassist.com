<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $availableLocales = config('app.available_locales', ['en', 'id']);
        $locale = $request->query('lang', $request->session()->get('locale', config('app.locale')));

        if (!in_array($locale, $availableLocales, true)) {
            $locale = config('app.locale');
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);
        URL::defaults(['lang' => $locale]);

        return $next($request);
    }
}
