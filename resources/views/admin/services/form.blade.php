@extends('admin.layout')

@section('title', $service->exists ? 'Edit service' : 'Add service')
@section('page_title', $service->exists ? 'Edit service' : 'Add service')
@section('page_description', 'Atur layanan dan pilih area layanan utamanya.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.services') }}">Back to services</a>
@endsection

@section('content')
<form class="panel form-panel" action="{{ $formAction }}" method="POST">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="form-grid">
        <div class="field field-wide">
            <label for="name">Service name</label>
            <input id="name" name="name" value="{{ old('name', $service->name) }}" required maxlength="255">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="service_area">Service area</label>
            <select id="service_area" name="service_area" required>
                @foreach ($serviceAreas as $serviceArea)
                <option value="{{ $serviceArea }}" {{ old('service_area', $service->service_area ?: 'service_maintenance') === $serviceArea ? 'selected' : '' }}>{{ __('site.services.areas.' . $serviceArea) }}</option>
                @endforeach
            </select>
            @error('service_area') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label class="check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->exists ? $service->is_active : true) ? 'checked' : '' }}>
                Active and visible to customers
            </label>
            @error('is_active') <div class="field-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.services') }}">Cancel</a>
        <button class="btn" type="submit">{{ $service->exists ? 'Save changes' : 'Create service' }}</button>
    </div>
</form>
@endsection
