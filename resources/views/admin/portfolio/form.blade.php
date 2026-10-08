@extends('admin.layout')

@section('title', $project->exists ? 'Edit portfolio project' : 'Add portfolio project')
@section('page_title', $project->exists ? 'Edit portfolio project' : 'Add portfolio project')
@section('page_description', 'Atur judul project dan beberapa foto untuk slider di website.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.portfolio') }}">Back to portfolio</a>
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
            <label for="title">Project title</label>
            <input id="title" name="title" value="{{ old('title', $project->title) }}" required maxlength="255">
        </div>

        <div class="field field-wide">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" maxlength="5000">{{ old('description', $project->description) }}</textarea>
        </div>

        <div class="field">
            <label for="sort_order">Display order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="4294967295" value="{{ old('sort_order', $project->sort_order ?? 0) }}" required>
        </div>

        <div class="field field-wide">
            <label class="check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $project->exists ? $project->is_active : true) ? 'checked' : '' }}>
                Active and visible on the public portfolio page
            </label>
            <label class="check">
                <input type="hidden" name="is_sample" value="0">
                <input type="checkbox" name="is_sample" value="1" {{ old('is_sample', $project->exists ? $project->is_sample : false) ? 'checked' : '' }}>
                Illustrative sample — show a sample label to visitors
            </label>
        </div>

        @if ($project->exists && $project->images->isNotEmpty())
        <div class="field field-wide">
            <label>Current photos</label>
            <div class="gallery-grid">
                @foreach ($project->images as $image)
                <article class="detail-card gallery-card">
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text ?: $project->title }}">
                    <div class="field">
                        <label for="gallery-{{ $image->id }}-alt">Alternative text</label>
                        <input id="gallery-{{ $image->id }}-alt" name="gallery[{{ $image->id }}][alt_text]" value="{{ old('gallery.' . $image->id . '.alt_text', $image->alt_text) }}" maxlength="255">
                    </div>
                    <div class="field">
                        <label for="gallery-{{ $image->id }}-order">Display order</label>
                        <input id="gallery-{{ $image->id }}-order" name="gallery[{{ $image->id }}][sort_order]" type="number" min="0" max="4294967295" value="{{ old('gallery.' . $image->id . '.sort_order', $image->sort_order) }}" required>
                    </div>
                    <label class="check">
                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}">
                        Remove this photo
                    </label>
                </article>
                @endforeach
            </div>
        </div>
        @endif

        <div class="field field-wide">
            <label for="images">{{ $project->exists ? 'Add photos to slider' : 'Project photos' }}</label>
            <input id="images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple {{ $project->exists ? '' : 'required' }}>
            <small>JPG, PNG, or WebP. Upload up to 10 photos at a time; maximum 5 MB each. The first photo is shown first in the slider.</small>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.portfolio') }}">Cancel</a>
        <button class="btn" type="submit">{{ $project->exists ? 'Save changes' : 'Create project' }}</button>
    </div>
</form>
@endsection
