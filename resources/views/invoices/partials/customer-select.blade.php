<!-- Customer Selection Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Select Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="customerSearch" placeholder="Search customers...">
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('customers.create') }}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add New Customer
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            @foreach($customers as $customer)
                            <tr class="customer-row" data-customer-id="{{ $customer->id }}" 
                                data-customer-name="{{ $customer->name }}"
                                data-customer-phone="{{ $customer->phone_number_1 }}"
                                data-customer-phone2="{{ $customer->phone_number_2 ?? '' }}"
                                data-customer-address="{{ $customer->full_address }}"
                                data-customer-area="{{ $customer->delivery_area ?? '' }}">
                                <td>
                                    <button type="button" class="btn btn-sm btn-success select-customer" 
                                            data-dismiss="modal">
                                        <i class="fa fa-check"></i> Select
                                    </button>
                                </td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone_number_1 }}</td>
                                <td>{{ Str::limit($customer->full_address, 30) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>