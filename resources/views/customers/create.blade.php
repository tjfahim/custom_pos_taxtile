@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Add New Customer</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('customers.index') }}">Customers</a></li>
                    <li class="active">Add Customer</li>
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
                    <div class="card-header">
                        <strong>Customer Information</strong>
                    </div>
                    <div class="card-body card-block">
                        <form action="{{ route('customers.store') }}" method="POST" class="form-horizontal">
                            @csrf
                            
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
                                                   value="{{ old('name') }}" required>
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
                                                       value="{{ old('phone_number_1') }}" required>
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
                                                       value="{{ old('phone_number_2') }}">
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
                                                      required>{{ old('full_address') }}</textarea>
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
                                                <label class="btn btn-outline-success @if(old('status', 'active') == 'active') active @endif">
                                                    <input type="radio" name="status" value="active" 
                                                           @if(old('status', 'active') == 'active') checked @endif> Active
                                                </label>
                                                <label class="btn btn-outline-secondary @if(old('status') == 'inactive') active @endif">
                                                    <input type="radio" name="status" value="inactive"
                                                           @if(old('status') == 'inactive') checked @endif> Inactive
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
                                                      class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                                            @error('note')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fa fa-save"></i> Save Customer
                                            </button>
                                            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-lg">
                                                <i class="fa fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

    // Character counter for textareas
    $('#note, #full_address').on('input', function() {
        var maxLength = $(this).attr('maxlength');
        if (maxLength) {
            var length = $(this).val().length;
            var lengthText = length + ' / ' + maxLength + ' characters';
            if (!$(this).next('.char-count').length) {
                $(this).after('<small class="form-text text-muted char-count"></small>');
            }
            $(this).next('.char-count').text(lengthText);
        }
    });
</script>
@endpush

@endsection