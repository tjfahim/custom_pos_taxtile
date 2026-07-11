@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-edit"></i> Edit Delivery Charge
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.delivery-charges.update', $charge->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from_range">From Range (pieces) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('from_range') is-invalid @enderror" 
                                       id="from_range" name="from_range" value="{{ intval(old('from_range', $charge->from_range)) }}" min="0" required>
                                <small class="text-muted">Starting number of pieces (e.g., 1, 5, 10)</small>
                                @error('from_range')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to_range">To Range (pieces) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('to_range') is-invalid @enderror" 
                                       id="to_range" name="to_range" value="{{ intval(old('to_range', $charge->to_range)) }}" min="0" required>
                                <small class="text-muted">Ending number of pieces (e.g., 5, 10, 20)</small>
                                @error('to_range')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inside_dhaka_price">Inside Dhaka Price (৳) <span class="text-danger">*</span></label>
                                <input type="number" step="1" class="form-control @error('inside_dhaka_price') is-invalid @enderror" 
                                       id="inside_dhaka_price" name="inside_dhaka_price" value="{{ intval(old('inside_dhaka_price', $charge->inside_dhaka_price)) }}" min="0" required>
                                <small class="text-muted">Delivery charge for inside Dhaka (e.g., 60, 80, 100)</small>
                                @error('inside_dhaka_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="outside_dhaka_price">Outside Dhaka Price (৳) <span class="text-danger">*</span></label>
                                <input type="number" step="1" class="form-control @error('outside_dhaka_price') is-invalid @enderror" 
                                       id="outside_dhaka_price" name="outside_dhaka_price" value="{{ intval(old('outside_dhaka_price', $charge->outside_dhaka_price)) }}" min="0" required>
                                <small class="text-muted">Delivery charge for outside Dhaka (e.g., 120, 160, 200)</small>
                                @error('outside_dhaka_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Delivery Charge
                        </button>
                        <a href="{{ route('admin.delivery-charges.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection