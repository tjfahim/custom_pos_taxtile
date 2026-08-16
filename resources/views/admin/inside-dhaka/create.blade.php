@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-plus"></i> Add Zone to Inside Dhaka
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.inside-dhaka.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city_id">City <span class="text-danger">*</span></label>
                                <select class="form-control @error('city_id') is-invalid @enderror" 
                                        id="city_id" name="city_id" required>
                                    <option value="">-- Select City --</option>
                                    @foreach($cities as $city)
                                    <option value="{{ $city->city_id }}" 
                                        {{ old('city_id', $selectedCityId ?? '1') == $city->city_id ? 'selected' : '' }}>
                                        {{ $city->city_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('city_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_id">Zone <span class="text-danger">*</span></label>
                                <select class="form-control @error('zone_id') is-invalid @enderror" 
                                        id="zone_id" name="zone_id">
                                    <option value="">-- Select Zone --</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->zone_id }}" 
                                            {{ old('zone_id') == $zone->zone_id ? 'selected' : '' }}
                                            @if(in_array($zone->zone_id, $addedZones ?? [])) disabled style="color:#999;" @endif>
                                            {{ $zone->zone_name }}
                                            @if(in_array($zone->zone_id, $addedZones ?? [])) (Already Added) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('zone_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-control @error('is_active') is-invalid @enderror" 
                                        id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Add to Inside Dhaka
                        </button>
                        <a href="{{ route('admin.inside-dhaka.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // When city changes, reload page with selected city to load zones
        $('#city_id').on('change', function() {
            const cityId = $(this).val();
            if (cityId) {
                window.location.href = '{{ route("admin.inside-dhaka.create") }}?city_id=' + cityId;
            } else {
                window.location.href = '{{ route("admin.inside-dhaka.create") }}';
            }
        });
    });
</script>
@endsection