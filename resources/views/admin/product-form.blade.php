@extends('admin.layout')

@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('page_title', $product->exists ? 'Edit product' : 'Add product')
@section('page_description', 'Atur informasi katalog dan pilihan jual atau rental produk.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.products') }}">Back to products</a>
@endsection

@section('content')
@if ($errors->any())
<div class="status-error" role="alert">
    <strong>Please review the form errors:</strong>
    <ul class="error-list">
        @foreach ($errors->all() as $message)
        <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>
@endif
<form class="panel form-panel" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="form-grid">
        <div class="field field-wide">
            <label for="name">Product name</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="subtitle">Subtitle (optional)</label>
            <input id="subtitle" name="subtitle" value="{{ old('subtitle', $product->subtitle) }}" maxlength="255">
            @error('subtitle') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ (string) old('category_id', $product->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="sku">SKU</label>
            <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="255">
            @error('sku') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="description">Description</label>
            <input id="description" type="hidden" name="description" value="{{ old('description', $product->description) }}">
            <trix-editor input="description" aria-label="Product description"></trix-editor>
            @error('description') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="specifications">Specifications</label>
            <textarea id="specifications" name="specifications" rows="6" maxlength="20000" placeholder="Engine displacement | 38.6 cm³&#10;Power output | 1.8 kW (2.4 hp)">{{ old('specifications', $product->specifications) }}</textarea>
            <small>Enter one specification per line using “name | details”.</small>
            @error('specifications') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="image">Product image</label>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <small>JPG, PNG, or WebP; maximum 5 MB.</small>
            @if ($product->image_path)
            <div class="mt-3">
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" style="width: 180px; height: 140px; object-fit: contain; border-radius: 8px;">
                <label class="check mt-2">
                    <input type="checkbox" name="remove_image" value="1">
                    Remove current image
                </label>
            </div>
            @endif
            @error('image') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        @if (!$product->exists)
        <div class="field field-wide">
            <label for="initial_images">Additional product images</label>
            <input id="initial_images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
            <small>Upload up to 10 JPG, PNG, or WebP images; maximum 5 MB each. Their order can be adjusted after saving.</small>
            @error('images') <div class="field-error">{{ $message }}</div> @enderror
            @error('images.*') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        @endif

        <div class="field field-wide">
            <label>Availability</label>
            <div class="check-row">
                <label class="check">
                    <input type="hidden" name="for_sale" value="0">
                    <input type="checkbox" name="for_sale" value="1" {{ old('for_sale', $product->for_sale) ? 'checked' : '' }}>
                    Available for sale
                </label>
                <label class="check">
                    <input type="hidden" name="for_rental" value="0">
                    <input type="checkbox" name="for_rental" value="1" {{ old('for_rental', $product->for_rental) ? 'checked' : '' }}>
                    Available for rental
                </label>
                <label class="check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->exists ? $product->is_active : true) ? 'checked' : '' }}>
                    Active in catalog
                </label>
            </div>
            @error('for_sale') <div class="field-error">{{ $message }}</div> @enderror
            @error('for_rental') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="sale_price">Sale price (Rp)</label>
            <input id="sale_price" name="sale_price" type="number" min="0" step="0.01" value="{{ old('sale_price', $product->sale_price) }}">
            @error('sale_price') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="rental_price">Daily rental price (Rp)</label>
            <input id="rental_price" name="rental_price" type="number" min="0" step="0.01" value="{{ old('rental_price', $product->rental_price) }}">
            @error('rental_price') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="weekly_rental_price">Weekly rental price (Rp)</label>
            <input id="weekly_rental_price" name="weekly_rental_price" type="number" min="0" step="0.01" value="{{ old('weekly_rental_price', $product->weekly_rental_price) }}">
            @error('weekly_rental_price') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="monthly_rental_price">Monthly rental price (Rp)</label>
            <input id="monthly_rental_price" name="monthly_rental_price" type="number" min="0" step="0.01" value="{{ old('monthly_rental_price', $product->monthly_rental_price) }}">
            @error('monthly_rental_price') <div class="field-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.products') }}">Cancel</a>
        <button class="btn" type="submit">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
    </div>
</form>

@if ($product->exists)
<section class="panel detail-panel">
    <div class="panel-heading">
        <div>
            <h2>Product gallery</h2>
            <p class="muted">Atur urutan gambar, teks alternatif, dan gambar tambahan.</p>
        </div>
    </div>
    <form class="detail-form" action="{{ route('admin.products.gallery.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if ($product->images->isNotEmpty())
        <div class="gallery-grid">
            @foreach ($product->images as $image)
            <article class="detail-card gallery-card">
                <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text ?: $product->name }}">
                <div class="field">
                    <label for="gallery-{{ $image->id }}-alt">Alt text</label>
                    <input id="gallery-{{ $image->id }}-alt" name="gallery[{{ $image->id }}][alt_text]" value="{{ old('gallery.' . $image->id . '.alt_text', $image->alt_text) }}" maxlength="255">
                </div>
                <div class="field">
                    <label for="gallery-{{ $image->id }}-order">Display order</label>
                    <input id="gallery-{{ $image->id }}-order" name="gallery[{{ $image->id }}][sort_order]" type="number" min="0" value="{{ old('gallery.' . $image->id . '.sort_order', $image->sort_order) }}" required>
                </div>
                <label class="check gallery-delete">
                    <input type="checkbox" name="delete_images[]" value="{{ $image->id }}">
                    Remove image
                </label>
                @if ($image->image_path === $product->image_path)
                <span class="badge">Current product image</span>
                @endif
            </article>
            @endforeach
        </div>
        @else
        <p class="muted">No gallery images yet.</p>
        @endif
        <div class="field detail-upload">
            <label for="gallery_uploads">Add gallery images</label>
            <input id="gallery_uploads" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
            <small>Up to 10 JPG, PNG, or WebP files; maximum 5 MB each.</small>
            @error('images') <div class="field-error">{{ $message }}</div> @enderror
            @error('images.*') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-actions">
            <button class="btn" type="submit">Save gallery</button>
        </div>
    </form>
</section>

<section class="panel detail-panel">
    <div class="panel-heading">
        <div>
            <h2>Attributes and options</h2>
            <p class="muted">Contoh: Color dengan opsi Black dan White; masukkan setiap opsi pada baris baru.</p>
        </div>
    </div>
    <form class="detail-form" action="{{ route('admin.products.attributes.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="has_attributes" value="1">
        <div class="detail-list" data-attribute-list>
            @foreach ($attributeRows as $index => $attribute)
            <fieldset class="detail-card" data-attribute-row>
                @if (!empty($attribute['id']))
                <input type="hidden" name="attributes[{{ $index }}][id]" value="{{ $attribute['id'] }}">
                @endif
                <div class="form-grid">
                    <div class="field">
                        <label>Attribute name</label>
                        <input name="attributes[{{ $index }}][name]" value="{{ $attribute['name'] ?? '' }}" maxlength="100" required>
                    </div>
                    <div class="field">
                        <label>Input style</label>
                        <select name="attributes[{{ $index }}][input_type]" required>
                            <option value="radio" {{ ($attribute['input_type'] ?? 'radio') === 'radio' ? 'selected' : '' }}>Radio buttons</option>
                            <option value="select" {{ ($attribute['input_type'] ?? 'radio') === 'select' ? 'selected' : '' }}>Dropdown</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Display order</label>
                        <input name="attributes[{{ $index }}][sort_order]" type="number" min="0" value="{{ $attribute['sort_order'] ?? $index }}" required>
                    </div>
                    <div class="field">
                        <label class="check">
                            <input type="hidden" name="attributes[{{ $index }}][is_active]" value="0">
                            <input type="checkbox" name="attributes[{{ $index }}][is_active]" value="1" {{ !empty($attribute['is_active']) ? 'checked' : '' }}>
                            Active on product page
                        </label>
                    </div>
                    <div class="field field-wide">
                        <label>Options (one per line)</label>
                        <textarea name="attributes[{{ $index }}][values]" maxlength="5000" required>{{ $attribute['values'] ?? '' }}</textarea>
                    </div>
                </div>
                <button class="btn btn-light" type="button" data-remove-row>Remove attribute</button>
            </fieldset>
            @endforeach
        </div>
        <template id="attribute-template">
            <fieldset class="detail-card" data-attribute-row>
                <div class="form-grid">
                    <div class="field">
                        <label>Attribute name</label>
                        <input name="attributes[__INDEX__][name]" maxlength="100" required>
                    </div>
                    <div class="field">
                        <label>Input style</label>
                        <select name="attributes[__INDEX__][input_type]" required>
                            <option value="radio">Radio buttons</option>
                            <option value="select">Dropdown</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Display order</label>
                        <input name="attributes[__INDEX__][sort_order]" type="number" min="0" value="0" required>
                    </div>
                    <div class="field">
                        <label class="check">
                            <input type="hidden" name="attributes[__INDEX__][is_active]" value="0">
                            <input type="checkbox" name="attributes[__INDEX__][is_active]" value="1" checked>
                            Active on product page
                        </label>
                    </div>
                    <div class="field field-wide">
                        <label>Options (one per line)</label>
                        <textarea name="attributes[__INDEX__][values]" maxlength="5000" required></textarea>
                    </div>
                </div>
                <button class="btn btn-light" type="button" data-remove-row>Remove attribute</button>
            </fieldset>
        </template>
        <div class="detail-actions">
            <button class="btn btn-light" type="button" data-add-attribute>Add attribute</button>
            <button class="btn" type="submit">Save attributes</button>
        </div>
    </form>
</section>

<section class="panel detail-panel">
    <div class="panel-heading">
        <div>
            <h2>Variants, stock, and quantity pricing</h2>
            <p class="muted">Setiap SKU aktif wajib mempunyai satu opsi untuk setiap atribut aktif. Harga tier berlaku mulai dari jumlah minimum tersebut.</p>
        </div>
    </div>
    <form class="detail-form" action="{{ route('admin.products.variants.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="has_variants" value="1">
        <div class="detail-list" data-variant-list>
            @foreach ($variantRows as $index => $variant)
            <fieldset class="detail-card" data-variant-row data-next-tier-index="{{ count($variant['price_tiers'] ?? []) }}">
                @if (!empty($variant['id']))
                <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">
                @endif
                <div class="form-grid">
                    <div class="field">
                        <label>Variant SKU</label>
                        <input name="variants[{{ $index }}][sku]" value="{{ $variant['sku'] ?? '' }}" maxlength="100" required>
                    </div>
                    <div class="field">
                        <label>Base unit price (Rp)</label>
                        <input name="variants[{{ $index }}][price]" type="number" min="0" step="0.01" value="{{ $variant['price'] ?? '' }}" required>
                    </div>
                    <div class="field">
                        <label>Stock quantity</label>
                        <input name="variants[{{ $index }}][stock_quantity]" type="number" min="0" value="{{ $variant['stock_quantity'] ?? 0 }}" required>
                    </div>
                    <div class="field">
                        <label class="check">
                            <input type="hidden" name="variants[{{ $index }}][is_active]" value="0">
                            <input type="checkbox" name="variants[{{ $index }}][is_active]" value="1" {{ !empty($variant['is_active']) ? 'checked' : '' }}>
                            Active / available for sale
                        </label>
                    </div>
                    @foreach ($attributes->where('is_active', true) as $attribute)
                    <div class="field">
                        <label>{{ $attribute->name }}</label>
                        <select name="variants[{{ $index }}][values][{{ $attribute->id }}]" {{ !empty($variant['is_active']) ? 'required' : '' }}>
                            <option value="">Select {{ $attribute->name }}</option>
                            @foreach ($attribute->values as $value)
                            <option value="{{ $value->id }}" {{ (string) ($variant['values'][$attribute->id] ?? '') === (string) $value->id ? 'selected' : '' }}>{{ $value->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                    <div class="field field-wide">
                        <label>Quantity price tiers</label>
                        <div class="tier-list">
                            @foreach (($variant['price_tiers'] ?? []) as $tierIndex => $tier)
                            <div class="tier-row" data-tier-row>
                                <input name="variants[{{ $index }}][price_tiers][{{ $tierIndex }}][min_quantity]" type="number" min="1" value="{{ $tier['min_quantity'] ?? '' }}" placeholder="Minimum quantity" aria-label="Minimum quantity">
                                <input name="variants[{{ $index }}][price_tiers][{{ $tierIndex }}][unit_price]" type="number" min="0" step="0.01" value="{{ $tier['unit_price'] ?? '' }}" placeholder="Unit price (Rp)" aria-label="Unit price">
                                <button class="btn btn-light" type="button" data-remove-row aria-label="Remove price tier">Remove</button>
                            </div>
                            @endforeach
                        </div>
                        <button class="btn btn-light" type="button" data-add-tier>Add price tier</button>
                    </div>
                </div>
                <button class="btn btn-light" type="button" data-remove-row>Remove variant</button>
            </fieldset>
            @endforeach
        </div>
        <template id="variant-template">
            <fieldset class="detail-card" data-variant-row data-next-tier-index="0">
                <div class="form-grid">
                    <div class="field">
                        <label>Variant SKU</label>
                        <input name="variants[__INDEX__][sku]" maxlength="100" required>
                    </div>
                    <div class="field">
                        <label>Base unit price (Rp)</label>
                        <input name="variants[__INDEX__][price]" type="number" min="0" step="0.01" required>
                    </div>
                    <div class="field">
                        <label>Stock quantity</label>
                        <input name="variants[__INDEX__][stock_quantity]" type="number" min="0" value="0" required>
                    </div>
                    <div class="field">
                        <label class="check">
                            <input type="hidden" name="variants[__INDEX__][is_active]" value="0">
                            <input type="checkbox" name="variants[__INDEX__][is_active]" value="1" checked>
                            Active / available for sale
                        </label>
                    </div>
                    @foreach ($attributes->where('is_active', true) as $attribute)
                    <div class="field">
                        <label>{{ $attribute->name }}</label>
                        <select name="variants[__INDEX__][values][{{ $attribute->id }}]" required>
                            <option value="">Select {{ $attribute->name }}</option>
                            @foreach ($attribute->values as $value)
                            <option value="{{ $value->id }}">{{ $value->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                    <div class="field field-wide">
                        <label>Quantity price tiers</label>
                        <div class="tier-list"></div>
                        <button class="btn btn-light" type="button" data-add-tier>Add price tier</button>
                    </div>
                </div>
                <button class="btn btn-light" type="button" data-remove-row>Remove variant</button>
            </fieldset>
        </template>
        <template id="tier-template">
            <div class="tier-row" data-tier-row>
                <input name="variants[__VARIANT__][price_tiers][__TIER__][min_quantity]" type="number" min="1" placeholder="Minimum quantity" aria-label="Minimum quantity">
                <input name="variants[__VARIANT__][price_tiers][__TIER__][unit_price]" type="number" min="0" step="0.01" placeholder="Unit price (Rp)" aria-label="Unit price">
                <button class="btn btn-light" type="button" data-remove-row aria-label="Remove price tier">Remove</button>
            </div>
        </template>
        <div class="detail-actions">
            <button class="btn btn-light" type="button" data-add-variant>Add variant</button>
            <button class="btn" type="submit">Save variants and prices</button>
        </div>
    </form>
</section>

<section class="panel detail-panel">
    <div class="panel-heading">
        <div>
            <h2>Frequently bought together</h2>
            <p class="muted">Pilih produk yang akan direkomendasikan dan atur urutan tampilnya.</p>
        </div>
    </div>
    <form class="detail-form" action="{{ route('admin.products.recommendations.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="recommendation_form_submitted" value="1">
        <div class="recommendation-list">
            @forelse ($otherProducts as $otherProduct)
            @php
                $recommendation = $recommendationRows[$otherProduct->id] ?? null;
                $isRecommended = $recommendation ? (bool) ($recommendation['is_active'] ?? false) : false;
            @endphp
            <label class="recommendation-row">
                <span class="check">
                    <input type="checkbox" name="related_products[]" value="{{ $otherProduct->id }}" {{ $isRecommended ? 'checked' : '' }}>
                    <span><strong>{{ $otherProduct->name }}</strong><small>{{ $otherProduct->sku ?: 'No SKU' }}</small></span>
                </span>
                <span class="field recommendation-order">
                    <span>Display order</span>
                    <input name="sort_order[{{ $otherProduct->id }}]" type="number" min="0" value="{{ $recommendation['sort_order'] ?? $loop->index }}">
                </span>
            </label>
            @empty
            <p class="muted">Create another product to add a recommendation.</p>
            @endforelse
        </div>
        <div class="detail-actions">
            <button class="btn" type="submit">Save recommendations</button>
        </div>
    </form>
</section>
@else
<section class="panel detail-panel">
    <div class="panel-body muted">Simpan produk terlebih dahulu untuk mengelola galeri, atribut, SKU varian, harga bertingkat, dan rekomendasi.</div>
</section>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        function addTemplateRow(templateId, container, replacements) {
            var template = document.getElementById(templateId);
            if (!template || !container) return null;
            var html = template.innerHTML;
            Object.keys(replacements).forEach(function (key) {
                html = html.split(key).join(String(replacements[key]));
            });
            var holder = document.createElement('div');
            holder.innerHTML = html.trim();
            var row = holder.firstElementChild;
            container.appendChild(row);
            return row;
        }

        document.addEventListener('click', function (event) {
            var addAttribute = event.target.closest('[data-add-attribute]');
            if (addAttribute) {
                var attributeList = document.querySelector('[data-attribute-list]');
                var attributeIndex = Number(attributeList.dataset.nextIndex || attributeList.children.length);
                attributeList.dataset.nextIndex = attributeIndex + 1;
                addTemplateRow('attribute-template', attributeList, {'__INDEX__': attributeIndex});
                return;
            }

            var addVariant = event.target.closest('[data-add-variant]');
            if (addVariant) {
                var variantList = document.querySelector('[data-variant-list]');
                var variantIndex = Number(variantList.dataset.nextIndex || variantList.children.length);
                variantList.dataset.nextIndex = variantIndex + 1;
                addTemplateRow('variant-template', variantList, {'__INDEX__': variantIndex});
                return;
            }

            var addTier = event.target.closest('[data-add-tier]');
            if (addTier) {
                var variant = addTier.closest('[data-variant-row]');
                var tierIndex = Number(variant.dataset.nextTierIndex || variant.querySelectorAll('[data-tier-row]').length);
                variant.dataset.nextTierIndex = tierIndex + 1;
                var variantInput = variant.querySelector('[name^="variants["][name$="[sku]"]');
                var variantMatch = variantInput.name.match(/^variants\[([^\]]+)\]/);
                if (variantMatch) {
                    addTemplateRow('tier-template', variant.querySelector('.tier-list'), {
                        '__VARIANT__': variantMatch[1],
                        '__TIER__': tierIndex
                    });
                }
                return;
            }

            var removeRow = event.target.closest('[data-remove-row]');
            if (removeRow) {
                removeRow.closest('[data-attribute-row], [data-variant-row], [data-tier-row]').remove();
            }
        });

        document.addEventListener('change', function (event) {
            var input = event.target;
            if (input.matches('[data-variant-row] input[type="checkbox"][name$="[is_active]"]')) {
                input.closest('[data-variant-row]').querySelectorAll('select[name*="[values]"]').forEach(function (select) {
                    select.required = input.checked;
                });
            }
        });

        var attributeList = document.querySelector('[data-attribute-list]');
        var variantList = document.querySelector('[data-variant-list]');
        if (attributeList) attributeList.dataset.nextIndex = attributeList.children.length;
        if (variantList) variantList.dataset.nextIndex = variantList.children.length;
    })();
</script>
@endpush