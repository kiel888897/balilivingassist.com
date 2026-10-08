<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminDeliveryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProductDetailController;
use App\Http\Controllers\AdminPortfolioController;
use App\Http\Controllers\AdminQuotationController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerQuotationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Serve storage files (alternatif untuk symlink yang disabled di shared hosting)
Route::get('/storage/{path}', function (string $path) {
    $file = storage_path('app/public/' . $path);

    // Security: prevent directory traversal
    $realBase = realpath(storage_path('app/public'));
    $realPath = realpath($file);

    if (!$realPath || !str_starts_with($realPath, $realBase) || !is_file($realPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';

    return response()->file($realPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000', // Cache 1 tahun
    ]);
})->where('path', '.*')->name('storage.serve');

Route::get('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, config('app.available_locales', ['en', 'id']), true), 404);
    $request->session()->put('locale', $locale);

    $next = (string) $request->query('next', '/');
    if ($next === '' || $next[0] !== '/' || substr($next, 0, 2) === '//') {
        $next = '/';
    }

    $path = parse_url($next, PHP_URL_PATH) ?: '/';
    $baseUrl = rtrim($request->getBaseUrl(), '/');
    if ($baseUrl !== '' && ($path === $baseUrl || strpos($path, $baseUrl . '/') === 0)) {
        $path = substr($path, strlen($baseUrl)) ?: '/';
    }

    $query = [];
    parse_str(parse_url($next, PHP_URL_QUERY) ?: '', $query);
    $query['lang'] = $locale;

    return redirect()->to($path . '?' . http_build_query($query));
})->name('language.switch');

Route::middleware('locale')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [HomeController::class, 'shop'])->name('shop');
    Route::get('/shop/{slug}', [HomeController::class, 'product'])->name('product.show');
    Route::get('/services', [HomeController::class, 'services'])->name('services');
    Route::get('/services/{slug}', [HomeController::class, 'service'])->name('service.show');
    Route::get('/rental', [HomeController::class, 'rental'])->name('rental');
    Route::get('/delivery', [HomeController::class, 'delivery'])->name('delivery');
    Route::get('/portfolio', [HomeController::class, 'portfolio'])->name('portfolio');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->withoutMiddleware('locale')->name('sitemap');
    Route::get('/robots.txt', function () {
        return response(
            "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /account\nDisallow: /cart\nSitemap: " . url('/sitemap.xml') . "\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    })->withoutMiddleware('locale')->name('robots');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
        Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
        Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.store');
    });

    Route::get('/about', function () {
        return redirect()->route('home', [], 301);
    })->name('about.legacy');
    Route::get('/projects', function () {
        return redirect()->route('portfolio', [], 301);
    })->name('projects.legacy');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/account', [CustomerAuthController::class, 'account'])->name('customer.account');
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/items/update', [CartController::class, 'update'])->name('cart.items.update');
        Route::post('/cart/items/remove', [CartController::class, 'destroy'])->name('cart.items.destroy');
        Route::get('/checkout', [CustomerQuotationController::class, 'checkout'])->name('customer.checkout');
        Route::post('/quotations', [CustomerQuotationController::class, 'store'])->name('customer.quotations.store');
        Route::get('/account/quotations', [CustomerQuotationController::class, 'quotations'])->name('customer.quotations');
        Route::get('/account/quotations/{quotation}', [CustomerQuotationController::class, 'showQuotation'])->name('customer.quotations.show');
        Route::get('/account/orders', [CustomerQuotationController::class, 'orders'])->name('customer.orders');
        Route::get('/account/orders/{quotation}', [CustomerQuotationController::class, 'showOrder'])->name('customer.orders.show');

        Route::middleware('permission:admin.access')->prefix('admin')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('admin');
            Route::middleware('permission:categories.manage')->group(function () {
                Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories');
                Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
                Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
                Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
                Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
                Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
            });
            Route::middleware('permission:services.manage')->group(function () {
                Route::get('/services', [AdminServiceController::class, 'index'])->name('admin.services');
                Route::get('/services/create', [AdminServiceController::class, 'create'])->name('admin.services.create');
                Route::post('/services', [AdminServiceController::class, 'store'])->name('admin.services.store');
                Route::get('/services/{service}/edit', [AdminServiceController::class, 'edit'])->name('admin.services.edit');
                Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('admin.services.update');
                Route::delete('/services/{service}', [AdminServiceController::class, 'destroy'])->name('admin.services.destroy');
            });
            Route::middleware('permission:delivery.manage')->group(function () {
                Route::get('/delivery', [AdminDeliveryController::class, 'index'])->name('admin.delivery');
                Route::post('/delivery/vehicles', [AdminDeliveryController::class, 'storeVehicle'])->name('admin.delivery.vehicles.store');
                Route::put('/delivery/vehicles/{vehicle}', [AdminDeliveryController::class, 'updateVehicle'])->name('admin.delivery.vehicles.update');
                Route::delete('/delivery/vehicles/{vehicle}', [AdminDeliveryController::class, 'destroyVehicle'])->name('admin.delivery.vehicles.destroy');
                Route::post('/delivery/vehicles/{vehicle}/rates', [AdminDeliveryController::class, 'storeRate'])->name('admin.delivery.rates.store');
                Route::put('/delivery/vehicles/{vehicle}/rates/{rate}', [AdminDeliveryController::class, 'updateRate'])->name('admin.delivery.rates.update');
                Route::delete('/delivery/vehicles/{vehicle}/rates/{rate}', [AdminDeliveryController::class, 'destroyRate'])->name('admin.delivery.rates.destroy');
                Route::post('/delivery/coverage-areas', [AdminDeliveryController::class, 'storeCoverageArea'])->name('admin.delivery.coverage-areas.store');
                Route::put('/delivery/coverage-areas/{area}', [AdminDeliveryController::class, 'updateCoverageArea'])->name('admin.delivery.coverage-areas.update');
                Route::delete('/delivery/coverage-areas/{area}', [AdminDeliveryController::class, 'destroyCoverageArea'])->name('admin.delivery.coverage-areas.destroy');
            });
            Route::middleware('permission:projects.manage')->group(function () {
                Route::get('/portfolio', [AdminPortfolioController::class, 'index'])->name('admin.portfolio');
                Route::get('/portfolio/create', [AdminPortfolioController::class, 'create'])->name('admin.portfolio.create');
                Route::post('/portfolio', [AdminPortfolioController::class, 'store'])->name('admin.portfolio.store');
                Route::get('/portfolio/{project}/edit', [AdminPortfolioController::class, 'edit'])->name('admin.portfolio.edit');
                Route::put('/portfolio/{project}', [AdminPortfolioController::class, 'update'])->name('admin.portfolio.update');
                Route::delete('/portfolio/{project}', [AdminPortfolioController::class, 'destroy'])->name('admin.portfolio.destroy');
            });
            Route::middleware('permission:quotes.manage')->group(function () {
                Route::get('/quotations', [AdminQuotationController::class, 'index'])->name('admin.quotations');
                Route::get('/quotations/{quotation}', [AdminQuotationController::class, 'edit'])->name('admin.quotations.edit');
                Route::put('/quotations/{quotation}', [AdminQuotationController::class, 'update'])->name('admin.quotations.update');
            });
            Route::middleware('permission:rental.manage')->group(function () {
                Route::get('/rental-categories', [AdminCategoryController::class, 'rentalIndex'])->name('admin.rental-categories');
                Route::get('/rental-categories/create', [AdminCategoryController::class, 'rentalCreate'])->name('admin.rental-categories.create');
                Route::post('/rental-categories', [AdminCategoryController::class, 'rentalStore'])->name('admin.rental-categories.store');
                Route::get('/rental-categories/{category}/edit', [AdminCategoryController::class, 'rentalEdit'])->name('admin.rental-categories.edit');
                Route::put('/rental-categories/{category}', [AdminCategoryController::class, 'rentalUpdate'])->name('admin.rental-categories.update');
                Route::delete('/rental-categories/{category}', [AdminCategoryController::class, 'rentalDestroy'])->name('admin.rental-categories.destroy');
            });
            Route::middleware('permission:users.manage')->group(function () {
                Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
                Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
                Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
                Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
                Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
                Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
            });
            Route::get('/products', [AdminController::class, 'products'])->middleware('permission:catalog.view')->name('admin.products');
            Route::get('/products/create', [AdminController::class, 'createProduct'])->middleware('permission:catalog.manage')->name('admin.products.create');
            Route::post('/products', [AdminController::class, 'storeProduct'])->middleware('permission:catalog.manage')->name('admin.products.store');
            Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->middleware('permission:catalog.manage')->name('admin.products.edit');
            Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->middleware('permission:catalog.manage')->name('admin.products.update');
            Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->middleware('permission:catalog.manage')->name('admin.products.destroy');
            Route::middleware('permission:catalog.manage')->prefix('products/{product}')->group(function () {
                Route::put('/gallery', [AdminProductDetailController::class, 'saveGallery'])->name('admin.products.gallery.update');
                Route::put('/attributes', [AdminProductDetailController::class, 'saveAttributes'])->name('admin.products.attributes.update');
                Route::put('/variants', [AdminProductDetailController::class, 'saveVariants'])->name('admin.products.variants.update');
                Route::put('/recommendations', [AdminProductDetailController::class, 'saveRecommendations'])->name('admin.products.recommendations.update');
            });
        });
    });
});
