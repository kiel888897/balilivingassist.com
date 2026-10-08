<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DeliveryCoverageArea;
use App\Models\DeliveryVehicle;
use App\Models\Product;
use App\Models\PortfolioProject;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'categories' => Category::where('is_active', true)
                ->where('type', 'shop')
                ->orderBy('name')
                ->take(6)
                ->get(),
            'serviceGroups' => $this->activeServiceGroups(),
            'rentalCategories' => Category::where('is_active', true)
                ->where('type', 'rental')
                ->orderBy('name')
                ->take(8)
                ->get(),
            'products' => Product::with(['category', 'images'])->where('is_active', true)->take(6)->get(),
            'activeSection' => 'home',
        ]);
    }

    public function shop(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['name-asc', 'name-desc', 'price-asc', 'price-desc'])],
        ]);

        $sort = $filters['sort'] ?? 'name-asc';
        $direction = substr($sort, -3) === 'asc' ? 'asc' : 'desc';
        $sortColumn = strpos($sort, 'price-') === 0 ? 'price' : 'name';
        return view('shop', [
            'categories' => Category::where('is_active', true)->where('type', 'shop')->orderBy('name')->get(),
            'products' => Product::with(['category', 'images'])
                ->withCount(['variants', 'attributes'])
                ->where('is_active', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true)->where('type', 'shop');
                })
                ->when(!empty($filters['category']), function ($query) use ($filters) {
                    $query->whereHas('category', function ($categoryQuery) use ($filters) {
                        $categoryQuery->where('slug', $filters['category']);
                    });
                })
                ->when(!empty($filters['q']), function ($query) use ($filters) {
                    $search = trim($filters['q']);
                    $query->where(function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
                })
                ->when($sortColumn === 'price', function ($query) use ($direction) {
                    $query->orderByRaw('CASE WHEN for_sale = 1 THEN sale_price ELSE rental_price END ' . $direction);
                }, function ($query) use ($direction) {
                    $query->orderBy('name', $direction);
                })
                ->orderBy('id')
                ->paginate(12)
                ->withQueryString(),
            'selectedCategory' => $filters['category'] ?? null,
            'search' => $filters['q'] ?? '',
            'sort' => $sort,
        ]);
    }

    public function product(Request $request, $slug)
    {
        $product = Product::with([
            'category',
            'images',
            'attributes.values',
            'variants.values.attribute',
            'variants.priceTiers',
            'frequentlyBoughtTogether.category',
            'frequentlyBoughtTogether.images',
            'frequentlyBoughtTogether.variants',
            'frequentlyBoughtTogether.attributes',
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $isRentalOrigin = $request->query('from') === 'rental';
        if ($isRentalOrigin) {
            abort_unless($product->for_rental, 404);
        }

        $relatedProducts = $product->frequentlyBoughtTogether;
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::with(['category', 'images', 'variants', 'attributes'])
                ->where('is_active', true)
                ->when($isRentalOrigin, function ($query) {
                    $query->where('for_sale', true);
                })
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->take(3)
                ->get();
        }
        if ($isRentalOrigin) {
            $relatedProducts = $relatedProducts->filter(function ($relatedProduct) {
                return $relatedProduct->for_sale;
            })->values();
        }

        return view('product.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function services()
    {
        return view('services.index', [
            'serviceGroups' => $this->activeServiceGroups(),
        ]);
    }

    public function service(string $slug)
    {
        $service = Service::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('services.show', ['service' => $service]);
    }

    public function rental(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['name-asc', 'name-desc', 'price-asc', 'price-desc'])],
        ]);

        $sort = $filters['sort'] ?? 'name-asc';
        $direction = substr($sort, -3) === 'asc' ? 'asc' : 'desc';
        $sortColumn = strpos($sort, 'price-') === 0 ? 'price' : 'name';

        return view('rental.index', [
            'categories' => Category::where('is_active', true)->where('type', 'rental')->orderBy('name')->get(),
            'products' => Product::with(['category', 'images'])
                ->where('is_active', true)
                ->where('for_rental', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true)->where('type', 'rental');
                })
                ->when(!empty($filters['category']), function ($query) use ($filters) {
                    $query->whereHas('category', function ($categoryQuery) use ($filters) {
                        $categoryQuery->where('slug', $filters['category']);
                    });
                })
                ->when(!empty($filters['q']), function ($query) use ($filters) {
                    $search = trim($filters['q']);
                    $query->where(function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
                })
                ->when($sortColumn === 'price', function ($query) use ($direction) {
                    $query->orderBy('rental_price', $direction);
                }, function ($query) use ($direction) {
                    $query->orderBy('name', $direction);
                })
                ->orderBy('id')
                ->paginate(12)
                ->withQueryString(),
            'selectedCategory' => $filters['category'] ?? null,
            'search' => $filters['q'] ?? '',
            'sort' => $sort,
        ]);
    }

    public function delivery()
    {
        return view('delivery.index', [
            'vehicles' => DeliveryVehicle::with('rates')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'coverageAreas' => DeliveryCoverageArea::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function projects()
    {
        return view('home', [
            'categories' => Category::where('is_active', true)->get(),
            'serviceGroups' => $this->activeServiceGroups(),
            'rentalCategories' => Category::where('is_active', true)->where('type', 'rental')->orderBy('name')->take(8)->get(),
            'products' => Product::where('is_active', true)->take(4)->get(),
            'activeSection' => 'projects',
        ]);
    }

    public function portfolio()
    {
        return view('portfolio', [
            'projects' => PortfolioProject::with('images')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function about()
    {
        return view('home', [
            'categories' => Category::where('is_active', true)->get(),
            'serviceGroups' => $this->activeServiceGroups(),
            'rentalCategories' => Category::where('is_active', true)->where('type', 'rental')->orderBy('name')->take(8)->get(),
            'products' => Product::where('is_active', true)->take(4)->get(),
            'activeSection' => 'about',
        ]);
    }

    public function contact()
    {
        return view('contact');
    }

    private function activeServiceGroups()
    {
        return Service::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->groupBy('service_area');
    }
}
