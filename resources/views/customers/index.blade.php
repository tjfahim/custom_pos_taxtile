@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Customer Management</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Customers</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    <!-- Success Message -->
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-success">Success</span>
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
                <span class="badge badge-pill badge-danger">Error</span>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                <strong class="card-title m-0">
                    <i class="fa fa-users mr-2 text-primary"></i>
                    Customer List
                </strong>
                <div>
                   
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus-circle"></i> Add Customer
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="customerTable" class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 text-center" style="width: 70px;">#ID</th>
                                <th class="border-0">Customer</th>
                                <th class="border-0">Contact</th>
                                <th class="border-0">Address</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Notes</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($customers as $c)
                            <tr class="border-bottom">
                                <!-- ID Column -->
                                <td class="text-center align-middle">
                                    <span class="badge badge-dark border rounded-pill px-3 py-1 font-weight-normal">
                                        #{{ $c->id }}
                                    </span>
                                </td>

                                <!-- Customer Name Column -->
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white mr-3">
                                            {{ substr($c->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-semibold">{{ $c->name }}</h6>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar-alt mr-1"></i>
                                                {{ $c->created_at->format('M d, Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Column -->
                                <td class="align-middle">
                                    <div class="contact-info">
                                        @if($c->phone_number_1)
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa fa-phone fa-fw text-primary mr-2"></i>
                                            <span class="text-dark">{{ $c->phone_number_1 }}</span>
                                        </div>
                                        @endif
                                        @if($c->phone_number_2)
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-phone fa-fw text-secondary mr-2"></i>
                                            <span class="text-muted">{{ $c->phone_number_2 }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Address Column -->
                                <td class="align-middle">
                                    <div class="address-info">
                                        <div class="d-flex">
                                            <i class="fa fa-map-marker-alt fa-fw text-danger mr-2 mt-1"></i>
                                            <span class="text-truncate" style="max-width: 200px;" 
                                                  data-toggle="tooltip" title="{{ $c->full_address }}">
                                                {{ Str::limit($c->full_address, 40) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Column -->
                                <td class="align-middle">
                                    <span class="badge badge-pill {{ $c->status == 'active' ? 'badge-success-light' : 'badge-danger-light' }}">
                                        <i class="fa fa-circle {{ $c->status == 'active' ? 'text-success' : 'text-danger' }} mr-1"></i>
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>

                                <!-- Notes Column -->
                                <td class="align-middle">
                                    @if($c->note)
                                    <div class="note-preview">
                                        <span class="text-truncate d-block" style="max-width: 150px;" 
                                              data-toggle="tooltip" title="{{ $c->note }}">
                                            <i class="fa fa-sticky-note text-warning mr-1"></i>
                                            {{ Str::limit($c->note, 25) }}
                                        </span>
                                    </div>
                                    @else
                                    <span class="text-muted">No notes</span>
                                    @endif
                                </td>

                                <!-- Actions Column -->
                                <td class="text-center align-middle">
                                    <div class="btn-group" role="group">
                                        <!-- View Button -->
                                        <button type="button" class="btn btn-sm btn-outline-info border-0 rounded-circle mr-1" 
                                                data-toggle="modal" data-target="#viewModal{{ $c->id }}"
                                                title="Quick View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        
                                        <!-- Edit Button -->
                                        <a href="{{ route('customers.edit', $c->id) }}" 
                                           class="btn btn-sm btn-outline-warning border-0 rounded-circle mr-1"
                                           title="Edit Customer">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <!-- Delete Button -->
                                        <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger border-0 rounded-circle"
                                                    title="Delete Customer"
                                                    onclick="return confirm('Are you sure you want to delete this customer?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal{{ $c->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-gradient-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fa fa-user-circle"></i> Customer Details
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="customer-avatar text-center mb-4">
                                                <div class="avatar-lg mx-auto bg-primary text-white d-flex align-items-center justify-content-center" 
                                                     style="width: 80px; height: 80px; border-radius: 50%; font-size: 32px; font-weight: bold;">
                                                    {{ substr($c->name, 0, 1) }}
                                                </div>
                                                <h5 class="mt-3 mb-0">{{ $c->name }}</h5>
                                                <span class="badge {{ $c->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                                    {{ ucfirst($c->status) }}
                                                </span>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-0 bg-light">
                                                        <div class="card-body">
                                                            <h6 class="card-title text-muted mb-3">
                                                <i class="fa fa-phone text-primary mr-2"></i>Contact Info
                                                            </h6>
                                                            <p class="mb-2">
                                                                <strong>Primary:</strong><br>
                                                                {{ $c->phone_number_1 }}
                                                            </p>
                                                            @if($c->phone_number_2)
                                                            <p class="mb-0">
                                                                <strong>Secondary:</strong><br>
                                                                {{ $c->phone_number_2 }}
                                                            </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-0 bg-light">
                                                        <div class="card-body">
                                                            <h6 class="card-title text-muted mb-3">
                                                <i class="fa fa-calendar text-info mr-2"></i>Date Info
                                                            </h6>
                                                            <p class="mb-2">
                                                                <strong>Created:</strong><br>
                                                                {{ $c->created_at->format('F d, Y') }}
                                                            </p>
                                                            <p class="mb-0">
                                                                <strong>Updated:</strong><br>
                                                                {{ $c->updated_at->format('F d, Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card border-0 bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title text-muted mb-3">
                                                <i class="fa fa-map-marker-alt text-danger mr-2"></i>Address
                                                    </h6>
                                                    <p class="mb-0">{{ $c->full_address }}</p>
                                                </div>
                                            </div>
                                            
                                            @if($c->note)
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-muted mb-3">
                                                <i class="fa fa-sticky-note text-warning mr-2"></i>Notes
                                                    </h6>
                                                    <p class="mb-0">{{ $c->note }}</p>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-primary">
                                                <i class="fa fa-edit mr-1"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No customers found</h5>
                                        <p class="text-muted">Add your first customer to get started</p>
                                        <a href="{{ route('customers.create') }}" class="btn btn-primary mt-2">
                                            <i class="fa fa-plus"></i> Add First Customer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted">
                    <i class="fa fa-info-circle mr-1"></i>
                    Showing {{ $customers->count() }} customer(s)
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button class="btn btn-sm btn-outline-success ml-2" id="exportBtn">
                        <i class="fa fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modern Styling */
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        border-top: none;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        padding: 1rem 0.75rem;
        background-color: #f8f9fa;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9ff;
        border-left: 3px solid #007bff;
        transform: translateX(2px);
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #f0f0f0;
    }
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }
    
    .badge-success-light {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.2);
    }
    
    .badge-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }
    
    .badge-primary-light {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border: 1px solid rgba(0, 123, 255, 0.2);
    }
    
    .btn-group .btn {
        transition: all 0.2s ease;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .empty-state i {
        opacity: 0.5;
    }
    
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
        padding: 1.2rem 1.5rem;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .avatar-lg {
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    
    .note-preview, .address-info {
        max-width: 200px;
    }
    
    .contact-info div {
        line-height: 1.4;
    }
    
    /* Print styles */
    @media print {
        .card-header, .card-footer, .modal, .btn {
            display: none !important;
        }
        
        .table {
            border: 1px solid #dee2e6;
        }
        
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
        }
    }
</style>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#customerTable').DataTable({
            "order": [], // Remove default ordering
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [0, 1, 2, 3, 4, 5, 6] // All columns except ID for sorting
                },
                { "searchable": false, "targets": [0, 6] }
            ],
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search customers...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ customers",
                "infoEmpty": "Showing 0 to 0 of 0 customers",
                "infoFiltered": "(filtered from _MAX_ total customers)",
                "zeroRecords": "No matching customers found",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "initComplete": function() {
                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();
                
                // Export button functionality
                $('#exportBtn').click(function() {
                    alert('Export feature would be implemented here');
                });
                
                // Make table rows clickable for view
                $('#customerTable tbody').on('click', 'tr', function(e) {
                    // Don't trigger if clicking on buttons or links
                    if (!$(e.target).is('button, a, i, input, select, textarea, form')) {
                        var customerId = $(this).find('.view-customer').data('customer-id');
                        if (customerId) {
                            $('#viewModal' + customerId).modal('show');
                        }
                    }
                });
            }
        });
        
        // Custom hover effect
        $('#customerTable tbody').on('mouseenter', 'tr', function() {
            $(this).addClass('table-row-hover');
        }).on('mouseleave', 'tr', function() {
            $(this).removeClass('table-row-hover');
        });
    });
    </script>
@endsection