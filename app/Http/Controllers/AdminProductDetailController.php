<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPriceTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminProductDetailController extends Controller
{
    public function saveGallery(Request $request, Product $product)
    {
        $data = $request->validate([
            'gallery' => ['nullable', 'array'],
            'gallery.*.sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'gallery.*.alt_text' => ['nullable', 'string', 'max:255'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['required', 'integer', 'distinct'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $images = $product->images()->get()->keyBy('id');
        $galleryRows = $data['gallery'] ?? [];
        $deleteIds = array_map('intval', $data['delete_images'] ?? []);
        foreach (array_merge(array_keys($galleryRows), $deleteIds) as $imageId) {
            if (!$images->has((int) $imageId)) {
                throw ValidationException::withMessages([
                    'gallery' => 'The selected image does not belong to this product.',
                ]);
            }
        }

        $uploadedPaths = [];
        $deletedPaths = [];

        try {
            DB::transaction(function () use ($request, $product, $galleryRows, $deleteIds, &$uploadedPaths, &$deletedPaths) {
                foreach ($galleryRows as $imageId => $row) {
                    if (in_array((int) $imageId, $deleteIds, true)) {
                        continue;
                    }

                    $product->images()
                        ->whereKey($imageId)
                        ->update([
                            'sort_order' => $row['sort_order'],
                            'alt_text' => $row['alt_text'] ?? null,
                        ]);
                }

                foreach ($deleteIds as $imageId) {
                    $image = $product->images()->whereKey($imageId)->firstOrFail();
                    $deletedPaths[] = $image->image_path;
                    if ($product->image_path === $image->image_path) {
                        $product->update(['image_path' => null]);
                    }
                    $image->delete();
                }

                $sortOrder = (int) $product->images()->max('sort_order') + 1;
                foreach ($request->file('images', []) as $file) {
                    $path = $file->store('products', 'public');
                    if (!$path) {
                        throw new \RuntimeException('The product image could not be saved.');
                    }

                    $uploadedPaths[] = $path;
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'alt_text' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'sort_order' => $sortOrder++,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);
            throw $exception;
        }

        Storage::disk('public')->delete($deletedPaths);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product gallery updated.');
    }

    public function saveAttributes(Request $request, Product $product)
    {
        $data = $request->validate([
            'has_attributes' => ['required', 'in:1'],
            'attributes' => ['nullable', 'array', 'max:10'],
            'attributes.*.id' => ['nullable', 'integer'],
            'attributes.*.name' => ['required', 'string', 'max:100'],
            'attributes.*.input_type' => ['required', 'in:radio,select'],
            'attributes.*.sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'attributes.*.is_active' => ['required', 'boolean'],
            'attributes.*.values' => ['required', 'string', 'max:5000'],
        ]);

        $rows = $data['attributes'] ?? [];
        $existingAttributes = ProductAttribute::where('product_id', $product->id)->get()->keyBy('id');
        $submittedIds = [];
        $normalizedRows = [];

        foreach ($rows as $index => $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            if ($id && (!$existingAttributes->has($id) || in_array($id, $submittedIds, true))) {
                throw ValidationException::withMessages([
                    'attributes' => 'An attribute is invalid or appears more than once.',
                ]);
            }
            if ($id) {
                $submittedIds[] = $id;
            }

            $values = [];
            foreach (preg_split('/\r\n|\r|\n/', $row['values']) as $value) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
                if (mb_strlen($value) > 120) {
                    throw ValidationException::withMessages([
                        'attributes' => 'Each option must be 120 characters or fewer.',
                    ]);
                }
                if (in_array(mb_strtolower($value), array_map('mb_strtolower', $values), true)) {
                    throw ValidationException::withMessages([
                        'attributes' => 'Each option must be unique within its attribute.',
                    ]);
                }
                $values[] = $value;
            }
            if (!$values) {
                throw ValidationException::withMessages([
                    'attributes' => 'Each attribute must have at least one option.',
                ]);
            }

            $normalizedRows[$index] = [
                'id' => $id,
                'name' => $row['name'],
                'input_type' => $row['input_type'],
                'sort_order' => $row['sort_order'],
                'is_active' => (bool) $row['is_active'],
                'values' => $values,
            ];
        }

        DB::transaction(function () use ($product, $existingAttributes, $submittedIds, $normalizedRows) {
            foreach ($existingAttributes as $attribute) {
                if (!in_array($attribute->id, $submittedIds, true)) {
                    if (DB::table('product_variant_values')
                        ->where('product_attribute_id', $attribute->id)
                        ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'attributes' => "Remove the attribute's variants before deleting the attribute.",
                        ]);
                    }
                    $attribute->delete();
                    continue;
                }

                $row = collect($normalizedRows)->firstWhere('id', $attribute->id);
                if ($row && !$row['is_active'] && $attribute->is_active
                    && DB::table('product_variant_values')
                        ->join('product_variants', 'product_variants.id', '=', 'product_variant_values.product_variant_id')
                        ->where('product_variant_values.product_attribute_id', $attribute->id)
                        ->where('product_variants.is_active', true)
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'attributes' => 'Disable variants using this attribute before deactivating it.',
                    ]);
                }
            }

            foreach ($normalizedRows as $row) {
                $attribute = $row['id']
                    ? $existingAttributes->get($row['id'])
                    : new ProductAttribute(['product_id' => $product->id]);
                $attribute->fill([
                    'name' => $row['name'],
                    'slug' => $this->uniqueAttributeSlug($product, $row['name'], $attribute->exists ? $attribute->id : null),
                    'input_type' => $row['input_type'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => $row['is_active'],
                ]);
                $attribute->product_id = $product->id;
                $attribute->save();

                $existingValues = $attribute->values()->get()->keyBy('value');
                $keepValues = [];
                foreach ($row['values'] as $sortOrder => $value) {
                    $attributeValue = $existingValues->get($value)
                        ?? new ProductAttributeValue([
                            'product_id' => $product->id,
                            'product_attribute_id' => $attribute->id,
                            'value' => $value,
                        ]);
                    $attributeValue->sort_order = $sortOrder;
                    $attributeValue->save();
                    $keepValues[] = $value;
                }

                $removedValues = $existingValues->except($keepValues);
                if ($removedValues->isNotEmpty()) {
                    $removedIds = $removedValues->pluck('id')->all();
                    if (DB::table('product_variant_values')->whereIn('product_attribute_value_id', $removedIds)->exists()) {
                        throw ValidationException::withMessages([
                            'attributes' => 'Remove variants using deleted options before removing those options.',
                        ]);
                    }
                    ProductAttributeValue::whereIn('id', $removedIds)->delete();
                }
            }
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product attributes and options updated.');
    }

    public function saveVariants(Request $request, Product $product)
    {
        $data = $request->validate([
            'has_variants' => ['required', 'in:1'],
            'variants' => ['nullable', 'array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:100'],
            'variants.*.price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'variants.*.is_active' => ['required', 'boolean'],
            'variants.*.values' => ['nullable', 'array'],
            'variants.*.values.*' => ['nullable', 'integer', 'exists:product_attribute_values,id'],
            'variants.*.price_tiers' => ['nullable', 'array', 'max:20'],
            'variants.*.price_tiers.*.min_quantity' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'variants.*.price_tiers.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $rows = $data['variants'] ?? [];
        $existingVariants = ProductVariant::where('product_id', $product->id)->get()->keyBy('id');
        $activeAttributes = ProductAttribute::with('values')
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $allAttributes = ProductAttribute::where('product_id', $product->id)->get()->keyBy('id');
        $productValues = ProductAttributeValue::where('product_id', $product->id)->get()->keyBy('id');
        $submittedIds = [];
        $normalizedRows = [];
        $activeCombinations = [];
        $skus = [];

        foreach ($rows as $index => $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            if ($id && (!$existingVariants->has($id) || in_array($id, $submittedIds, true))) {
                throw ValidationException::withMessages([
                    'variants' => 'A variant is invalid or appears more than once.',
                ]);
            }
            if ($id) {
                $submittedIds[] = $id;
            }

            $skuKey = mb_strtolower(trim($row['sku']));
            if (in_array($skuKey, $skus, true)) {
                throw ValidationException::withMessages([
                    'variants' => 'Variant SKUs must be unique.',
                ]);
            }
            $skus[] = $skuKey;

            $selectedValues = [];
            foreach ($row['values'] ?? [] as $attributeId => $valueId) {
                if ($valueId === '' || $valueId === null) {
                    continue;
                }
                $attributeId = (int) $attributeId;
                $value = $productValues->get((int) $valueId);
                if (!$allAttributes->has($attributeId)
                    || !$value
                    || (int) $value->product_attribute_id !== $attributeId
                ) {
                    throw ValidationException::withMessages([
                        'variants' => 'A variant option does not belong to this product and attribute.',
                    ]);
                }
                $selectedValues[$attributeId] = $value->id;
            }

            $isActive = (bool) $row['is_active'];
            if ($isActive) {
                foreach ($activeAttributes as $attribute) {
                    if (!isset($selectedValues[$attribute->id])) {
                        throw ValidationException::withMessages([
                            'variants' => 'Every active variant must select one option for each active attribute.',
                        ]);
                    }
                }
                $activeAttributeIds = array_flip($activeAttributes->pluck('id')->all());
                $combination = array_values(array_intersect_key($selectedValues, $activeAttributeIds));
                sort($combination);
                $signature = implode('-', $combination);
                if (in_array($signature, $activeCombinations, true)) {
                    throw ValidationException::withMessages([
                        'variants' => 'Active variants must have different option combinations.',
                    ]);
                }
                $activeCombinations[] = $signature;
            }

            $tiers = [];
            $tierQuantities = [];
            foreach ($row['price_tiers'] ?? [] as $tier) {
                $minQuantity = $tier['min_quantity'] ?? null;
                $unitPrice = $tier['unit_price'] ?? null;
                if (($minQuantity === null || $minQuantity === '') && ($unitPrice === null || $unitPrice === '')) {
                    continue;
                }
                if ($minQuantity === null || $minQuantity === '' || $unitPrice === null || $unitPrice === '') {
                    throw ValidationException::withMessages([
                        'variants' => 'Each price tier needs both a minimum quantity and a unit price.',
                    ]);
                }
                if (in_array((int) $minQuantity, $tierQuantities, true)) {
                    throw ValidationException::withMessages([
                        'variants' => 'A variant cannot have duplicate minimum quantities for price tiers.',
                    ]);
                }
                $tierQuantities[] = (int) $minQuantity;
                $tiers[] = [
                    'min_quantity' => (int) $minQuantity,
                    'unit_price' => $unitPrice,
                ];
            }

            $normalizedRows[$index] = [
                'id' => $id,
                'sku' => trim($row['sku']),
                'price' => $row['price'],
                'stock_quantity' => $row['stock_quantity'],
                'is_active' => $isActive,
                'values' => $selectedValues,
                'price_tiers' => $tiers,
            ];
        }

        $conflictingSku = ProductVariant::where('product_id', '!=', $product->id)
            ->whereIn('sku', array_column($normalizedRows, 'sku'))
            ->exists();
        if ($conflictingSku) {
            throw ValidationException::withMessages([
                'variants' => 'A variant SKU is already used by another product.',
            ]);
        }

        DB::transaction(function () use ($product, $existingVariants, $submittedIds, $normalizedRows) {
            $removedIds = $existingVariants->keys()->diff($submittedIds);
            if ($removedIds->isNotEmpty()) {
                ProductVariant::where('product_id', $product->id)->whereIn('id', $removedIds)->delete();
            }

            foreach ($existingVariants->only($submittedIds) as $variant) {
                $variant->update(['sku' => 'admin-tmp-' . $variant->id . '-' . Str::random(12)]);
            }

            foreach ($normalizedRows as $row) {
                $variant = $row['id']
                    ? $existingVariants->get($row['id'])
                    : new ProductVariant(['product_id' => $product->id]);
                $variant->fill([
                    'sku' => $row['sku'],
                    'price' => $row['price'],
                    'stock_quantity' => $row['stock_quantity'],
                    'is_active' => $row['is_active'],
                ]);
                $variant->product_id = $product->id;
                $variant->save();

                $pivotValues = [];
                foreach ($row['values'] as $attributeId => $valueId) {
                    $pivotValues[$valueId] = [
                        'product_id' => $product->id,
                        'product_attribute_id' => $attributeId,
                    ];
                }
                $variant->values()->sync($pivotValues);

                $variant->priceTiers()->delete();
                foreach ($row['price_tiers'] as $tier) {
                    ProductVariantPriceTier::create(array_merge(
                        ['product_variant_id' => $variant->id],
                        $tier
                    ));
                }
            }
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product variants and price tiers updated.');
    }

    public function saveRecommendations(Request $request, Product $product)
    {
        $data = $request->validate([
            'recommendation_form_submitted' => ['required', 'in:1'],
            'related_products' => ['nullable', 'array', 'max:100'],
            'related_products.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'sort_order' => ['nullable', 'array'],
            'sort_order.*' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        $relatedIds = array_map('intval', $data['related_products'] ?? []);
        if (in_array($product->id, $relatedIds, true)) {
            throw ValidationException::withMessages([
                'related_products' => 'A product cannot recommend itself.',
            ]);
        }
        $eligibleIds = Product::whereIn('id', $relatedIds)
            ->where('is_active', true)
            ->where('for_sale', true)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
        sort($eligibleIds);
        $sortedRelatedIds = $relatedIds;
        sort($sortedRelatedIds);
        if ($eligibleIds !== $sortedRelatedIds) {
            throw ValidationException::withMessages([
                'related_products' => 'Frequently bought together items must be active products available for sale.',
            ]);
        }

        DB::transaction(function () use ($product, $relatedIds, $data) {
            DB::table('frequently_bought_together')
                ->where('product_id', $product->id)
                ->whereNotIn('related_product_id', $relatedIds ?: [0])
                ->delete();

            foreach ($relatedIds as $relatedProductId) {
                $keys = [
                    'product_id' => $product->id,
                    'related_product_id' => $relatedProductId,
                ];
                $values = [
                    'sort_order' => $data['sort_order'][$relatedProductId] ?? 0,
                    'is_active' => true,
                    'updated_at' => now(),
                ];
                if (DB::table('frequently_bought_together')->where($keys)->exists()) {
                    DB::table('frequently_bought_together')->where($keys)->update($values);
                } else {
                    DB::table('frequently_bought_together')->insert($keys + $values + ['created_at' => now()]);
                }
            }
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Frequently bought together products updated.');
    }

    private function uniqueAttributeSlug(Product $product, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'attribute';
        $slug = $baseSlug;
        $suffix = 2;

        while (ProductAttribute::where('product_id', $product->id)
            ->where('slug', $slug)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
