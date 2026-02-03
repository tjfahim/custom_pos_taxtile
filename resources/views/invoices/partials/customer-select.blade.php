<!-- Customer Select Modal (update this section) -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Customer</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="customerSearch" class="form-control" placeholder="Search by name or phone...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            @foreach($customers as $customer)
                            <tr class="customer-row" 
                                data-customer-id="{{ $customer->id }}"
                                data-customer-name="{{ $customer->name }}"
                                data-customer-phone="{{ $customer->phone_number_1 }}"
                                data-customer-phone2="{{ $customer->phone_number_2 }}"
                                data-customer-address="{{ $customer->full_address }}"
                                data-customer-area="{{ $customer->delivery_area }}">
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone_number_1 }}</td>
                                <td>{{ Str::limit($customer->full_address, 30) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-customer">
                                        Select
                                    </button>
                                </td>
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