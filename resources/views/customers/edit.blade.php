@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Customer</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('customers.index') }}">Customers</a></li>
                    <li class="active">Edit Customer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Edit Customer: {{ $customer->name }}</strong>
                        <span class="badge badge-{{ $customer->status == 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                    <div class="card-body card-block">
                        <form action="{{ route('customers.update', $customer) }}" method="POST" class="form-horizontal">
                            @csrf
                            @method('PUT')
                            
                            <!-- Customer Info -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> 
                                        Last updated: {{ $customer->updated_at->format('F d, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Card -->
                            <div class="card">
                                <div class="card-header">
                                    <i class="fa fa-user"></i> Personal Information
                                </div>
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="name" class="form-control-label">
                                                Full Name <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" id="name" name="name" 
                                                   placeholder="Enter customer full name"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name', $customer->name) }}" required>
                                            @error('name')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Card -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <i class="fa fa-phone"></i> Contact Information
                                </div>
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="phone_number_1" class="form-control-label">
                                                Primary Phone <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                                </div>
                                                <input type="text" id="phone_number_1" name="phone_number_1"
                                                       placeholder="Enter primary phone number"
                                                       class="form-control @error('phone_number_1') is-invalid @enderror"
                                                       value="{{ old('phone_number_1', $customer->phone_number_1) }}" required>
                                            </div>
                                            @error('phone_number_1')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="phone_number_2" class="form-control-label">
                                                Secondary Phone
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                                </div>
                                                <input type="text" id="phone_number_2" name="phone_number_2"
                                                       placeholder="Enter secondary phone number (optional)"
                                                       class="form-control @error('phone_number_2') is-invalid @enderror"
                                                       value="{{ old('phone_number_2', $customer->phone_number_2) }}">
                                            </div>
                                            @error('phone_number_2')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information Card -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <i class="fa fa-map-marker"></i> Address Information
                                </div>
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="full_address" class="form-control-label">
                                                Full Address <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <textarea id="full_address" name="full_address" 
                                                      rows="4" placeholder="Enter complete address"
                                                      class="form-control @error('full_address') is-invalid @enderror"
                                                      required>{{ old('full_address', $customer->full_address) }}</textarea>
                                            @error('full_address')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information Card -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <i class="fa fa-info-circle"></i> Additional Information
                                </div>
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="status" class="form-control-label">
                                                Status <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-outline-success @if(old('status', $customer->status) == 'active') active @endif">
                                                    <input type="radio" name="status" value="active" 
                                                           @if(old('status', $customer->status) == 'active') checked @endif> Active
                                                </label>
                                                <label class="btn btn-outline-secondary @if(old('status', $customer->status) == 'inactive') active @endif">
                                                    <input type="radio" name="status" value="inactive"
                                                           @if(old('status', $customer->status) == 'inactive') checked @endif> Inactive
                                                </label>
                                            </div>
                                            @error('status')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="note" class="form-control-label">
                                                Note
                                            </label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <textarea id="note" name="note" 
                                                      rows="3" placeholder="Any additional notes (optional)"
                                                      class="form-control @error('note') is-invalid @enderror">{{ old('note', $customer->note) }}</textarea>
                                            @error('note')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                            <small class="form-text text-muted">
                                                You can add important notes about this customer.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <i class="fa fa-bolt"></i> Quick Actions
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-info" onclick="copyAddress()">
                                                    <i class="fa fa-copy"></i> Copy Address
                                                </button>
                                                <button type="button" class="btn btn-outline-info" onclick="formatPhoneNumbers()">
                                                    <i class="fa fa-phone"></i> Format Phones
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="resetForm()">
                                                    <i class="fa fa-refresh"></i> Reset Form
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="send_update" name="send_update">
                                                <label class="form-check-label" for="send_update">
                                                    Send update notification (if applicable)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fa fa-check"></i> Update Customer
                                            </button>
                                            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-lg">
                                                <i class="fa fa-times"></i> Cancel
                                            </a>
                                            <a href="#" class="btn btn-danger btn-lg" 
                                               onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this customer?')) { document.getElementById('delete-form').submit(); }">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Delete Form -->
                        <form id="delete-form" action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .animated -->
</div><!-- .content -->

@push('scripts')
<script>
    // Phone number formatting
    $('#phone_number_1, #phone_number_2').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 3 && value.length <= 6) {
            value = value.replace(/(\d{3})(\d{1,3})/, '($1) $2');
        } else if (value.length > 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{1,4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // Quick action functions
    function copyAddress() {
        var address = $('#full_address').val();
        if (address) {
            navigator.clipboard.writeText(address).then(function() {
                alert('Address copied to clipboard!');
            });
        }
    }

    function formatPhoneNumbers() {
        $('#phone_number_1, #phone_number_2').trigger('input');
        alert('Phone numbers formatted!');
    }

    function resetForm() {
        if (confirm('Are you sure you want to reset all changes?')) {
            location.reload();
        }
    }

    // Auto-save draft (optional)
    let autoSaveTimeout;
    $('input, textarea, select').on('input change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            console.log('Auto-save would trigger here...');
        }, 3000);
    });
</script>
@endpush

@endsection