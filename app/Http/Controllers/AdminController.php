<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'categories' => Category::withCount('products')->get(),
            'products' => Product::with('category')->latest()->take(8)->get(),
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'sales' => Product::where('for_sale', true)->count(),
                'rentals' => Product::where('for_rental', true)->count(),
            ],
        ]);
    }

    public function products()
    {
        return view('admin.products', [
            'products' => Product::with('category')->latest()->get(),
        ]);
    }

    public function createProduct()
    {
        return view('admin.product-form', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'product' => new Product(),
            'formAction' => route('admin.products.store'),
            'formMethod' => 'POST',
        ]);
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image'], $data['remove_image'], $data['images']);

        $product = Product::create($data);
        if ($product->image_path) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $product->image_path,
                'sort_order' => 0,
            ]);
        }
        $this->storeAdditionalImages($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created. You can now manage its gallery, variants, and recommendations.');
    }

    public function editProduct(Product $product)
    {
        $product->load('images');
        $attributes = ProductAttribute::with('values')
            ->where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $variants = ProductVariant::with(['values', 'priceTiers'])
            ->where('product_id', $product->id)
            ->orderBy('id')
            ->get();
        $storedRecommendations = DB::table('frequently_bought_together')
            ->where('product_id', $product->id)
            ->get()
            ->keyBy('related_product_id');

        $attributeRows = old('attributes');
        if (!is_array($attributeRows)) {
            $attributeRows = $attributes->map(function (ProductAttribute $attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'input_type' => $attribute->input_type,
                    'sort_order' => $attribute->sort_order,
                    'is_active' => $attribute->is_active,
                    'values' => $attribute->values->pluck('value')->implode("\n"),
                ];
            })->all();
        }

        $variantRows = old('variants');
        if (!is_array($variantRows)) {
            $variantRows = $variants->map(function (ProductVariant $variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock_quantity' => $variant->stock_quantity,
                    'is_active' => $variant->is_active,
                    'values' => $variant->values->mapWithKeys(function ($value) {
                        return [$value->pivot->product_attribute_id => $value->id];
                    })->all(),
                    'price_tiers' => $variant->priceTiers->map(function ($tier) {
                        return [
                            'min_quantity' => $tier->min_quantity,
                            'unit_price' => $tier->unit_price,
                        ];
                    })->all(),
                ];
            })->all();
        }

        $recommendationRows = null;
        if (old('recommendation_form_submitted')) {
            $recommendationRows = [];
            $selectedIds = array_map('intval', old('related_products', []));
            $orders = old('sort_order', []);
            foreach ($selectedIds as $relatedProductId) {
                $recommendationRows[$relatedProductId] = [
                    'is_active' => true,
                    'sort_order' => $orders[$relatedProductId] ?? 0,
                ];
            }
        } else {
            $recommendationRows = $storedRecommendations->mapWithKeys(function ($recommendation) {
                return [
                    $recommendation->related_product_id => [
                        'is_active' => $recommendation->is_active,
                        'sort_order' => $recommendation->sort_order,
                    ],
                ];
            })->all();
        }

        return view('admin.product-form', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'product' => $product,
            'formAction' => route('admin.products.update', $product),
            'formMethod' => 'PUT',
            'attributes' => $attributes,
            'attributeRows' => $attributeRows,
            'variantRows' => $variantRows,
            'recommendationRows' => $recommendationRows,
            'otherProducts' => Product::where('id', '!=', $product->id)
                ->where('is_active', true)
                ->where('for_sale', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product);
        $data['slug'] = $this->uniqueSlug($data['name'], $product);
        $oldImagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }
        unset($data['image'], $data['remove_image'], $data['images']);

        $newImagePath = array_key_exists('image_path', $data) ? $data['image_path'] : $oldImagePath;
        $product->update($data);
        if ($oldImagePath !== $newImagePath) {
            $this->syncPrimaryImage($product, $oldImagePath, $newImagePath);
        }
        $this->storeAdditionalImages($request, $product);
        if ($oldImagePath && array_key_exists('image_path', $data) && $oldImagePath !== $data['image_path']) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route('admin.products')->with('status', 'Product updated.');
    }

    public function deleteProduct(Product $product)
    {
        $imagePaths = $product->images()->pluck('image_path')->all();
        if ($product->image_path) {
            $imagePaths[] = $product->image_path;
        }
        $product->delete();
        Storage::disk('public')->delete(array_unique($imagePaths));

        return redirect()->route('admin.products')->with('status', 'Product deleted.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'sale_price' => ['nullable', 'required_if:for_sale,1', 'numeric', 'min:0'],
            'rental_price' => ['nullable', 'required_if:for_rental,1', 'numeric', 'min:0'],
            'weekly_rental_price' => ['nullable', 'numeric', 'min:0'],
            'monthly_rental_price' => ['nullable', 'numeric', 'min:0'],
            'for_sale' => ['required', 'boolean'],
            'for_rental' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!$data['for_sale'] && !$data['for_rental']) {
            throw ValidationException::withMessages([
                'for_sale' => 'Enable sale or rental for this product.',
            ]);
        }

        $data['description'] = HtmlSanitizer::sanitize($data['description'] ?? null);

        return $data;
    }

    private function syncPrimaryImage(Product $product, ?string $oldImagePath, ?string $newImagePath): void
    {
        $primaryImage = $oldImagePath
            ? $product->images()->where('image_path', $oldImagePath)->first()
            : null;

        if (!$newImagePath) {
            if ($primaryImage) {
                $primaryImage->delete();
            }
            return;
        }

        if ($primaryImage) {
            $primaryImage->update(['image_path' => $newImagePath]);
            return;
        }

        $product->images()->increment('sort_order');
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $newImagePath,
            'sort_order' => 0,
        ]);
    }

    private function storeAdditionalImages(Request $request, Product $product): void
    {
        $sortOrder = (int) $product->images()->max('sort_order') + 1;
        foreach ($request->file('images', []) as $image) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $image->store('products', 'public'),
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function uniqueSlug(string $name, ?Product $product = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::where('slug', $slug)
            ->when($product, function ($query) use ($product) {
                $query->where('id', '!=', $product->id);
            })
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
