@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-edit"></i> Edit Customer: {{ $customer->name }}
                </h5>
                <span class="badge badge-{{ $customer->status == 'active' ? 'success' : 'danger' }}">
                    {{ ucfirst($customer->status) }}
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Personal Info -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $customer->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Primary Phone -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Primary Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number_1" 
                                       class="form-control @error('phone_number_1') is-invalid @enderror"
                                       value="{{ old('phone_number_1', $customer->phone_number_1) }}" required>
                                @error('phone_number_1')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Secondary Phone -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Secondary Phone</label>
                                <input type="text" name="phone_number_2" 
                                       class="form-control @error('phone_number_2') is-invalid @enderror"
                                       value="{{ old('phone_number_2', $customer->phone_number_2) }}">
                                @error('phone_number_2')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Delivery Area -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Delivery Area</label>
                                <input type="text" name="delivery_area" 
                                       class="form-control @error('delivery_area') is-invalid @enderror"
                                       value="{{ old('delivery_area', $customer->delivery_area) }}">
                                @error('delivery_area')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Full Address -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Full Address <span class="text-danger">*</span></label>
                                <textarea name="full_address" rows="3" 
                                          class="form-control @error('full_address') is-invalid @enderror" 
                                          required>{{ old('full_address', $customer->full_address) }}</textarea>
                                @error('full_address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="note" rows="2" 
                                          class="form-control @error('note') is-invalid @enderror">{{ old('note', $customer->note) }}</textarea>
                                @error('note')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-right mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Customer
                        </button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-danger" 
                                onclick="if(confirm('Are you sure?')) { document.getElementById('delete-form').submit(); }">
                            Delete
                        </button>
                    </div>
                </form>

                <form id="delete-form" action="{{ route('admin.customers.destroy', $customer) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection