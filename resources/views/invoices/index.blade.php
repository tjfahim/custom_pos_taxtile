@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-file-invoice"></i> Invoices
                </h5>
                 <a href="{{ route('invoices.download-today-csv') }}" class="btn btn-info">
            <i class="fa fa-download"></i> Download Today's Invoices (CSV)
        </a>
                <a href="{{ route('invoices.pos') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Create Invoice
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="invoicesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Recipient</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->recipient_name }}</td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>৳{{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ 
                                        $invoice->payment_status == 'paid' ? 'success' : 
                                        ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                                    }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('invoices.print', $invoice->id) }}" 
                                           class="btn btn-sm btn-info mr-1" title="Print">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" 
                                           class="btn btn-sm btn-secondary mr-1" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        {{-- <form action="{{ route('invoices.destroy', $invoice->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Delete this invoice?')"
                                                    title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form> --}}
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fa fa-file-invoice fa-2x text-muted mb-2"></i>
                                    <p>No invoices found</p>
                                    <a href="{{ route('invoices.pos') }}" class="btn btn-primary btn-sm">
                                        Create First Invoice
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Remove the pagination links since DataTable handles pagination -->
                {{-- <div class="mt-3">
                    {{ $invoices->links() }}
                </div> --}}
            </div>
        </div>
    </div>
</div>

<style>
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    .badge-success {
        background-color: #28a745;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-danger {
        background-color: #dc3545;
    }
</style>

<script>
$(document).ready(function() {
    $('#invoicesTable').DataTable({
        "order": [[0, "desc"]], // sort by Invoice # descending
        "columnDefs": [
            { 
                "orderable": false, 
                "targets": [6] // Actions column not sortable
            },
            {
                "searchable": false,
                "targets": [6] // Actions column not searchable
            },
            {
                "type": "date",
                "targets": [3] // Date column for proper sorting
            },
            {
                "type": "num-fmt", // For currency sorting
                "targets": [4] // Total column
            }
        ],
        "pageLength": 10, // rows per page
        "lengthMenu": [5, 10, 25, 50, 100],
        "responsive": true,
        "language": {
            "emptyTable": "No invoices found",
            "info": "Showing _START_ to _END_ of _TOTAL_ invoices",
            "infoEmpty": "Showing 0 to 0 of 0 invoices",
            "infoFiltered": "(filtered from _MAX_ total invoices)",
            "lengthMenu": "Show _MENU_ invoices",
            "search": "Search invoices:",
            "zeroRecords": "No matching invoices found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        "initComplete": function(settings, json) {
            // Add custom CSS classes after initialization
            $('.dataTables_length select').addClass('form-control-sm');
            $('.dataTables_filter input').addClass('form-control-sm');
        }
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});
</script>
@endsection