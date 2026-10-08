@extends('admin.layout')

@section('title', 'Delivery management')
@section('page_title', 'Delivery')
@section('page_description', 'Manage delivery vehicles, distance-based rates and map coverage origins.')

@section('content')
@if ($errors->any())
<div class="status-error" role="alert">
    <strong>Please correct the following:</strong>
    <ul style="margin: 7px 0 0; padding-left: 20px;">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<section class="panel form-panel" style="max-width:none;">
    <div class="panel-heading" style="margin:-22px -22px 20px;">
        <div>
            <h2>Add delivery vehicle</h2>
            <span class="subtext">Vehicles are shown publicly in their display order.</span>
        </div>
    </div>
    <form action="{{ route('admin.delivery.vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="new_vehicle_name">Vehicle name</label>
                <input id="new_vehicle_name" name="name" value="{{ old('name') }}" required maxlength="255">
            </div>
            <div class="field">
                <label for="new_vehicle_order">Display order</label>
                <input id="new_vehicle_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" required>
            </div>
            <div class="field field-wide">
                <label for="new_vehicle_weight">Load capacity label</label>
                <textarea id="new_vehicle_weight" name="max_weight_label" maxlength="500" required>{{ old('max_weight_label') }}</textarea>
            </div>
            <div class="field">
                <label for="new_vehicle_image">Vehicle image (optional)</label>
                <input id="new_vehicle_image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <div class="field">
                <label class="check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active and visible publicly
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit">Create vehicle</button>
        </div>
    </form>
</section>

<div class="detail-list" style="margin-top:20px;">
    @forelse ($vehicles as $vehicle)
    <section class="panel detail-panel">
        <div class="panel-heading">
            <div>
                <h2>{{ $vehicle->name }}</h2>
                <p>{{ $vehicle->rates->count() }} distance rate(s) · {{ $vehicle->is_active ? 'Active' : 'Inactive' }}</p>
            </div>
            @if ($vehicle->image_path)
            <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="{{ $vehicle->name }}" style="width:84px;height:60px;object-fit:contain;border-radius:6px;background:#f3f6f5;">
            @endif
        </div>
        <div class="detail-form">
            <form action="{{ route('admin.delivery.vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="field">
                        <label for="vehicle_name_{{ $vehicle->id }}">Vehicle name</label>
                        <input id="vehicle_name_{{ $vehicle->id }}" name="name" value="{{ $vehicle->name }}" required maxlength="255">
                    </div>
                    <div class="field">
                        <label for="vehicle_order_{{ $vehicle->id }}">Display order</label>
                        <input id="vehicle_order_{{ $vehicle->id }}" name="sort_order" type="number" min="0" value="{{ $vehicle->sort_order }}" required>
                    </div>
                    <div class="field field-wide">
                        <label for="vehicle_weight_{{ $vehicle->id }}">Load capacity label</label>
                        <textarea id="vehicle_weight_{{ $vehicle->id }}" name="max_weight_label" maxlength="500" required>{{ $vehicle->max_weight_label }}</textarea>
                    </div>
                    <div class="field">
                        <label for="vehicle_image_{{ $vehicle->id }}">Replace image</label>
                        <input id="vehicle_image_{{ $vehicle->id }}" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="field">
                        <label class="check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $vehicle->is_active ? 'checked' : '' }}>
                            Active and visible publicly
                        </label>
                        @if ($vehicle->image_path)
                        <label class="check">
                            <input type="checkbox" name="remove_image" value="1">
                            Remove current image
                        </label>
                        @endif
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="btn" type="submit">Save vehicle</button>
                </div>
            </form>
            <form action="{{ route('admin.delivery.vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Delete this vehicle and all its rates?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Delete vehicle</button>
            </form>
        </div>

        <div class="panel detail-panel" style="margin:0 20px 20px;">
            <div class="panel-heading">
                <div>
                    <h2>Distance rates</h2>
                    <p>Distance ranges for the same vehicle cannot overlap.</p>
                </div>
            </div>
            <div class="detail-form">
                <form action="{{ route('admin.delivery.rates.store', $vehicle) }}" method="POST" class="detail-card">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="new_min_{{ $vehicle->id }}">From (km)</label>
                            <input id="new_min_{{ $vehicle->id }}" name="distance_min_km" type="number" step="0.01" min="0" required>
                        </div>
                        <div class="field">
                            <label for="new_max_{{ $vehicle->id }}">To (km)</label>
                            <input id="new_max_{{ $vehicle->id }}" name="distance_max_km" type="number" step="0.01" min="0.01" required>
                        </div>
                        <div class="field">
                            <label for="new_fee_{{ $vehicle->id }}">Fee (IDR)</label>
                            <input id="new_fee_{{ $vehicle->id }}" name="fee" type="number" step="0.01" min="0">
                        </div>
                        <div class="field">
                            <label for="new_quote_{{ $vehicle->id }}">Pricing</label>
                            <select id="new_quote_{{ $vehicle->id }}" name="is_price_on_application" required>
                                <option value="0">Fixed fee</option>
                                <option value="1">Price on application</option>
                            </select>
                        </div>
                    </div>
                    <div class="detail-actions">
                        <button class="btn btn-light" type="submit">Add rate</button>
                    </div>
                </form>

                @forelse ($vehicle->rates as $rate)
                <div class="detail-card" style="margin-top:12px;">
                    <form action="{{ route('admin.delivery.rates.update', [$vehicle, $rate]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <div class="field">
                                <label for="rate_min_{{ $rate->id }}">From (km)</label>
                                <input id="rate_min_{{ $rate->id }}" name="distance_min_km" type="number" step="0.01" min="0" value="{{ $rate->distance_min_km }}" required>
                            </div>
                            <div class="field">
                                <label for="rate_max_{{ $rate->id }}">To (km)</label>
                                <input id="rate_max_{{ $rate->id }}" name="distance_max_km" type="number" step="0.01" min="0.01" value="{{ $rate->distance_max_km }}" required>
                            </div>
                            <div class="field">
                                <label for="rate_fee_{{ $rate->id }}">Fee (IDR)</label>
                                <input id="rate_fee_{{ $rate->id }}" name="fee" type="number" step="0.01" min="0" value="{{ $rate->fee }}">
                            </div>
                            <div class="field">
                                <label for="rate_quote_{{ $rate->id }}">Pricing</label>
                                <select id="rate_quote_{{ $rate->id }}" name="is_price_on_application" required>
                                    <option value="0" {{ !$rate->is_price_on_application ? 'selected' : '' }}>Fixed fee</option>
                                    <option value="1" {{ $rate->is_price_on_application ? 'selected' : '' }}>Price on application</option>
                                </select>
                            </div>
                        </div>
                        <div class="detail-actions">
                            <button class="btn btn-light" type="submit">Save rate</button>
                        </div>
                    </form>
                    <form action="{{ route('admin.delivery.rates.destroy', [$vehicle, $rate]) }}" method="POST" onsubmit="return confirm('Delete this rate?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete rate</button>
                    </form>
                </div>
                @empty
                <p class="muted" style="margin:16px 0 0;">No distance rates have been added.</p>
                @endforelse
            </div>
        </div>
    </section>
    @empty
    <section class="panel panel-body muted">No delivery vehicles have been added yet.</section>
    @endforelse
</div>

<section class="panel detail-panel">
    <div class="panel-heading">
        <div>
            <h2>Map coverage origins</h2>
            <p>Each active origin appears on the public map with concentric distance rings.</p>
        </div>
    </div>
    <div class="detail-form">
        <form action="{{ route('admin.delivery.coverage-areas.store') }}" method="POST" class="detail-card">
            @csrf
            <h3 style="margin:0 0 14px;font-weight:700;">Add coverage origin</h3>
            <div class="form-grid">
                <div class="field field-wide">
                    <label for="new_area_name">Origin name</label>
                    <input id="new_area_name" name="name" maxlength="255" required>
                </div>
                <div class="field">
                    <label for="new_area_latitude">Latitude</label>
                    <input id="new_area_latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" required>
                </div>
                <div class="field">
                    <label for="new_area_longitude">Longitude</label>
                    <input id="new_area_longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" required>
                </div>
                <div class="field">
                    <label for="new_area_radius">Radius (km)</label>
                    <input id="new_area_radius" name="radius_km" type="number" min="1" max="500" value="45" required>
                </div>
                <div class="field">
                    <label for="new_area_order">Display order</label>
                    <input id="new_area_order" name="sort_order" type="number" min="0" value="0" required>
                </div>
                <div class="field field-wide">
                    <label class="check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Active on public map
                    </label>
                </div>
            </div>
            <div class="detail-actions">
                <button class="btn" type="submit">Add origin</button>
            </div>
        </form>

        @forelse ($coverageAreas as $area)
        <div class="detail-card" style="margin-top:12px;">
            <form action="{{ route('admin.delivery.coverage-areas.update', $area) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="field field-wide">
                        <label for="area_name_{{ $area->id }}">Origin name</label>
                        <input id="area_name_{{ $area->id }}" name="name" value="{{ $area->name }}" maxlength="255" required>
                    </div>
                    <div class="field">
                        <label for="area_latitude_{{ $area->id }}">Latitude</label>
                        <input id="area_latitude_{{ $area->id }}" name="latitude" type="number" step="0.0000001" min="-90" max="90" value="{{ $area->latitude }}" required>
                    </div>
                    <div class="field">
                        <label for="area_longitude_{{ $area->id }}">Longitude</label>
                        <input id="area_longitude_{{ $area->id }}" name="longitude" type="number" step="0.0000001" min="-180" max="180" value="{{ $area->longitude }}" required>
                    </div>
                    <div class="field">
                        <label for="area_radius_{{ $area->id }}">Radius (km)</label>
                        <input id="area_radius_{{ $area->id }}" name="radius_km" type="number" min="1" max="500" value="{{ $area->radius_km }}" required>
                    </div>
                    <div class="field">
                        <label for="area_order_{{ $area->id }}">Display order</label>
                        <input id="area_order_{{ $area->id }}" name="sort_order" type="number" min="0" value="{{ $area->sort_order }}" required>
                    </div>
                    <div class="field field-wide">
                        <label class="check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $area->is_active ? 'checked' : '' }}>
                            Active on public map
                        </label>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="btn btn-light" type="submit">Save origin</button>
                </div>
            </form>
            <form action="{{ route('admin.delivery.coverage-areas.destroy', $area) }}" method="POST" onsubmit="return confirm('Delete this map coverage origin?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Delete origin</button>
            </form>
        </div>
        @empty
        <p class="muted" style="margin:16px 0 0;">No coverage origins have been added.</p>
        @endforelse
    </div>
</section>
@endsection
