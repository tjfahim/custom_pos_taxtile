@extends('admin.layouts.master')

@section('main_content')

<div class="content mt-3">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-users mr-2"></i>
                    Customer List
                </h5>
                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Customer
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="customerTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th>Area</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($customers as $c)
                            <tr>
                                <!-- ID Column -->
                                <td>{{ $c->id }}</td>

                                <!-- Customer Name Column -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white mr-3">
                                            {{ substr($c->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $c->name }}</strong>
                                            <div class="text-muted small">
                                                {{ $c->created_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Column -->
                                <td>
                                    @if($c->phone_number_1)
                                    <div class="mb-1">
                                        <i class="fa fa-phone text-primary mr-2"></i>
                                        {{ $c->phone_number_1 }}
                                    </div>
                                    @endif
                                    @if($c->phone_number_2)
                                    <div class="text-muted">
                                        <i class="fa fa-phone mr-2"></i>
                                        {{ $c->phone_number_2 }}
                                    </div>
                                    @endif
                                </td>

                                <!-- Address Column -->
                                <td>
                                    <i class="fa fa-map-marker-alt text-danger mr-2"></i>
                                    {{ Str::limit($c->full_address) }}
                                </td>
                                <td>
                                     @if($c->delivery_area)
                                    <i class="fa fa-map-marker-alt text-danger mr-2"></i>
                                    {{ Str::limit($c->delivery_area) }}
                                    @else
                                    <span class="text-muted">No area</span>
                                    @endif
                                    
                                </td>

                                <!-- Status Column -->
                                <td>
                                    <span class="badge {{ $c->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>

                                <!-- Notes Column -->
                                <td>
                                    @if($c->note)
                                    <i class="fa fa-sticky-note text-warning mr-1"></i>
                                    {{ Str::limit($c->note) }}
                                    @else
                                    <span class="text-muted">No notes</span>
                                    @endif
                                </td>

                                <!-- Actions Column -->
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- View Button -->
                                        <button type="button" class="btn btn-sm btn-info mr-1" 
                                                data-toggle="modal" data-target="#viewModal{{ $c->id }}"
                                                title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        
                                        <!-- Edit Button -->
                                        <a href="{{ route('customers.edit', $c->id) }}" 
                                           class="btn btn-sm btn-warning mr-1"
                                           title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <!-- Delete Button -->
                                        <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal{{ $c->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Customer Details</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="text-center mb-4">
                                                <div class="avatar-circle bg-primary text-white mx-auto" style="width: 60px; height: 60px; font-size: 24px;">
                                                    {{ substr($c->name, 0, 1) }}
                                                </div>
                                                <h5 class="mt-2">{{ $c->name }}</h5>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Primary Phone:</strong><br>{{ $c->phone_number_1 }}</p>
                                                    @if($c->phone_number_2)
                                                    <p><strong>Secondary Phone:</strong><br>{{ $c->phone_number_2 }}</p>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Created:</strong><br>{{ $c->created_at->format('M d, Y') }}</p>
                                                    <p><strong>Updated:</strong><br>{{ $c->updated_at->format('M d, Y') }}</p>
                                                </div>
                                            </div>
                                            
                                            <p><strong>Address:</strong><br>{{ $c->full_address }}</p>
                                            
                                            @if($c->note)
                                            <p><strong>Notes:</strong><br>{{ $c->note }}</p>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-primary">
                                                Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fa fa-users fa-2x text-muted mb-2"></i>
                                    <p>No customers found</p>
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                                        Add First Customer
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $customers->count() }} customer(s)
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>

<script>
$(document).ready(function() {
    $('#customerTable').DataTable({
        "order": [[0, "desc"]], // sort by ID descending
        "columnDefs": [
            { 
                "orderable": false, 
                "targets": [2, 3, 4, 5, 6, 7] // Making non-ID columns non-sortable
            },
            {
                "searchable": false,
                "targets": [7] // Action column not searchable
            }
        ],
        "pageLength": 10, // rows per page
        "lengthMenu": [5, 10, 25, 50, 100],
        "responsive": true,
        "language": {
            "emptyTable": "No customers found",
            "info": "Showing _START_ to _END_ of _TOTAL_ customers",
            "infoEmpty": "Showing 0 to 0 of 0 customers",
            "infoFiltered": "(filtered from _MAX_ total customers)",
            "lengthMenu": "Show _MENU_ customers",
            "search": "Search:",
            "zeroRecords": "No matching customers found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection