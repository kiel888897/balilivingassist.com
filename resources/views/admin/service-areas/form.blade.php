@extends('admin.layout')

@section('title', $area->exists ? 'Edit service area' : 'Add service area')
@section('page_title', $area->exists ? 'Edit service area' : 'Add service area')
@section('page_description', 'Area layanan tampil sebagai kartu pengelompokan di halaman Services dan home.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.service-areas') }}">Back to service areas</a>
@endsection

@section('content')
<form class="panel form-panel" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="name">Area name (English)</label>
            <input id="name" name="name" value="{{ old('name', $area->name) }}" required maxlength="255">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="name_id">Area name (Indonesian, optional)</label>
            <input id="name_id" name="name_id" value="{{ old('name_id', $area->name_id) }}" maxlength="255">
            @error('name_id') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="sort_order">Display order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', $area->sort_order) }}" required>
            @error('sort_order') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="image">Background image (optional)</label>
            @if ($area->image_path)
            <img src="{{ asset('storage/' . $area->image_path) }}" alt="{{ $area->name }}" style="width: 180px; height: 110px; object-fit: cover; border-radius: 6px;">
            <label class="check">
                <input type="hidden" name="remove_image" value="0">
                <input type="checkbox" name="remove_image" value="1">
                Remove current image
            </label>
            @endif
            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <span class="muted">JPG, PNG, or WebP up to 5 MB. Shown behind the area card on the home page.</span>
            @error('image') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label class="check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $area->exists ? $area->is_active : true) ? 'checked' : '' }}>
                Active and visible to customers
            </label>
            @error('is_active') <div class="field-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.service-areas') }}">Cancel</a>
        <button class="btn" type="submit">{{ $area->exists ? 'Save changes' : 'Create service area' }}</button>
    </div>
</form>
@endsection
