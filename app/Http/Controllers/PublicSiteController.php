<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;

class PublicSiteController extends Controller
{
    public function sitemap()
    {
        $routes = [
            ['home', []],
            ['shop', []],
            ['services', []],
            ['rental', []],
            ['delivery', []],
            ['portfolio', []],
            ['contact', []],
        ];

        $urls = [];
        foreach (['en', 'id'] as $locale) {
            foreach ($routes as [$name, $parameters]) {
                $urls[] = route($name, array_merge($parameters, ['lang' => $locale]));
            }

            foreach (Product::where('is_active', true)->pluck('slug') as $slug) {
                $urls[] = route('product.show', ['slug' => $slug, 'lang' => $locale]);
            }

            foreach (Service::where('is_active', true)->pluck('slug') as $slug) {
                $urls[] = route('service.show', ['slug' => $slug, 'lang' => $locale]);
            }
        }

        $entries = collect($urls)->unique()->map(function ($url) {
            return '<url><loc>' . e($url) . '</loc></url>';
        })->implode('');

        return response(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $entries . '</urlset>',
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8']
        );
    }
}
