@extends('admin.layout')

@section('title', $category->exists ? 'Edit ' . strtolower($categoryLabel) . ' category' : 'Add ' . strtolower($categoryLabel) . ' category')
@section('page_title', $category->exists ? 'Edit ' . strtolower($categoryLabel) . ' category' : 'Add ' . strtolower($categoryLabel) . ' category')
@section('page_description', 'Atur nama, gambar, deskripsi, dan status kategori ' . $categoryLabel . '.')

@section('page_actions')
<a class="btn btn-light" href="{{ route($categoryRoute) }}">Back to categories</a>
@endsection

@section('content')
<form class="panel form-panel" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="form-grid">
        <div class="field field-wide">
            <label for="name">Category name</label>
            <input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="type">Category type</label>
            <input type="hidden" name="type" value="{{ $categoryType }}">
            <input id="type" value="{{ $categoryLabel }}" disabled>
            @error('type') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description', $category->description) }}</textarea>
            @error('description') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="image">Category image</label>
            @if ($category->image_path)
            <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" style="width: 180px; height: 110px; object-fit: cover; border-radius: 6px;">
            <label class="check">
                <input type="hidden" name="remove_image" value="0">
                <input type="checkbox" name="remove_image" value="1">
                Remove current image
            </label>
            @endif
            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <span class="muted">JPG, PNG, or WebP up to 2 MB.</span>
            @error('image') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label class="check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->exists ? $category->is_active : true) ? 'checked' : '' }}>
                Active category
            </label>
            @error('is_active') <div class="field-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route($categoryRoute) }}">Cancel</a>
        <button class="btn" type="submit">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
    </div>
</form>
@endsection
