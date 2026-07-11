@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-truck"></i> Delivery Charge Management
                </h5>
                <div>
                    <a href="{{ route('admin.delivery-charges.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Range (Pieces)</th>
                                <th width="25%">Inside Dhaka (৳)</th>
                                <th width="25%">Outside Dhaka (৳)</th>
                                <th width="25%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charges as $charge)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $charge->range_display }}</td>
                                <td>{{ (int) $charge->inside_dhaka_price }}</td>
                                <td>{{ (int) $charge->outside_dhaka_price }}</td>
                                <td>
                                    <a href="{{ route('admin.delivery-charges.edit', $charge->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.delivery-charges.destroy', $charge->id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No delivery charges found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection