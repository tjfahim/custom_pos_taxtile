<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            padding: 10px; 
            color: #000; 
            background: #f5f5f5;
        }
        
        @page {
            size: A4;
            margin: 0;
        }
        
        .invoice-container { 
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header { 
            background: #e6f7ff; 
            padding: 12px 15px; 
            display: flex; 
            justify-content: space-between;
            width: 100%;
            border-bottom: 2px solid #b3e0ff;
        }
        .shop-name { 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 4px;
        }
        .shop-address { 
            font-size: 9px; 
            line-height: 1.2; 
            color: #333;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-no { 
            font-size: 14px; 
            font-weight: bold; 
            margin-bottom: 3px;
        }
        .invoice-date { 
            font-size: 11px; 
            color: #333;
        }
        
        .invoice-body { padding: 12px 15px; width: 100%; }
        
        .recipient-section { margin-bottom: 10px; font-size: 12px; }
        .recipient-title { 
            font-weight: bold; 
            margin-bottom: 6px; 
            font-size: 12px;
            color: #333;
        }
        .recipient-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 6px; 
        }
        .recipient-label { 
            font-weight: 500; 
            min-width: 60px;
            color: #555;
        }
        .phone-number {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        
        /* Items Table - Fixed widths */
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 12px; 
            margin: 10px 0;
            table-layout: fixed;
        }
        .items-table thead { background: #e6f7ff; }
        .items-table th { 
            padding: 6px 4px; 
            border: 1px solid #b3e0ff; 
            font-size: 14px;
        }
        .items-table td { 
            padding: 6px 4px; 
            border: 1px solid #e5e5e5; 
            font-size: 12px;
        }
        .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 40%; }
        .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 10%; }
        .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 20%; }
        .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 20%; }
        
        /* Return Items Table - Same widths as items table */
        .return-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 5px 0 10px 0;
            table-layout: fixed;
        }
        .return-table thead { background: #ffe6e6; }
        .return-table th {
            padding: 6px 4px;
            border: 1px solid #ffb3b3;
            font-size: 13px;
            color: #d32f2f;
        }
        .return-table td {
            padding: 6px 4px;
            border: 1px solid #ffe6e6;
            font-size: 12px;
        }
        /* Match exactly the same widths as items table */
        .return-table th:nth-child(1), .return-table td:nth-child(1) { width: 40%; }
        .return-table th:nth-child(2), .return-table td:nth-child(2) { width: 10%; }
        .return-table th:nth-child(3), .return-table td:nth-child(3) { width: 20%; }
        .return-table th:nth-child(4), .return-table td:nth-child(4) { width: 20%; }
        /* Extra column for Reason - adjust other columns */
        .return-table th:nth-child(5), .return-table td:nth-child(5) { width: 10%; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .summary-wrapper { 
            display: flex; 
            justify-content: flex-end; 
            margin-top: 12px;
            width: 100%;
        }
        .summary-section { 
            width: 45%; 
            border: 1px solid #e5e5e5; 
            padding: 8px;
            font-size: 14px;
            background: #f9f9f9;
        }
        .summary-title { 
            font-size: 12px; 
            font-weight: bold; 
            margin-bottom: 6px;
            color: #333;
        }
        .summary-table { width: 100%; font-size: 14px; }
        .summary-table td { padding: 4px 0; }
        .summary-table .label { font-weight: 500; color: #555; }
        .summary-table .value { text-align: right; font-weight: 600; }
        .total-qty-row { 
            font-weight: 700; 
            font-size: 14px;
            color: #333;
        }
        .total-row { 
            font-weight: 700; 
            font-size: 14px;
        }
        .return-row-summary {
            color: #d32f2f;
            font-weight: 600;
        }
        .due-row { 
            font-weight: 800;
            font-size: 16px;
            color: #d32f2f;
            border-top: 1px solid #e5e5e5;
            padding-top: 6px;
            margin-top: 4px;
        }
        
        .print-controls { 
            text-align: center; 
            margin-top: 20px; 
            padding: 10px;
        }
        .print-btn {
            padding: 10px 25px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        .print-btn:hover {
            background: #218838;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0;
                margin-top: 0;
                margin-bottom: 0;
            }
            
            html, body {
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
                width: 100%;
                background: white !important;
            }
            
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .invoice-container {
                max-height: 100%;
                page-break-inside: avoid;
                page-break-after: avoid;
                page-break-before: avoid;
                box-shadow: none !important;
            }
            
            .invoice-header, .items-table thead, .return-table thead {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .print-controls { display: none !important; }
            
            @page :footer { display: none; }
            @page :header { display: none; }
            
            .due-row {
                font-size: 16px !important;
                font-weight: 800 !important;
                color: #d32f2f !important;
            }
        }
        
        @media (max-width: 400px) {
            body { padding: 5px; }
            .recipient-grid { grid-template-columns: 1fr; }
            .summary-section { width: 60%; }
            .invoice-header { padding: 10px 12px; }
            .invoice-body { padding: 10px 12px; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
       <div class="invoice-header">
    <div>
       <div class="invoice-header">
    <div style="flex: 0 0 auto;">
        <div class="shop-name">Faisal Textile</div>
        <div class="shop-address">20, Balaka Vhaban, Chadni Chawk Market (Ground Floor)<br>Dhaka 1205 • Phone: 01923232543</div>
    </div>
    
    <!-- Sale Type - Center -->
    <div style="flex: 1; text-align: center; display: flex; align-items: center; justify-content: center;">
        <div class="sale-type" style="font-size: 16px; font-weight: 500; color: #000000;">
            @if($invoice->is_wholesale)
                <span style="font-weight: bold; color: #000000;">Whole Sale</span>
                @if($invoice->is_inhouse_sale)
                    <span style="color: #000000;"> (In House)</span>
                @else
                    <span style="color: #000000;"> ({{ $invoice->courier_name ?? 'Pathao' }})</span>
                @endif
            @else
                @if($invoice->is_inhouse_sale)
                    <span style="font-weight: bold; color: #000000;">In House</span>
                @else
                    <span style="color: #000000;">{{ $invoice->courier_name ?? 'Pathao' }}</span>
                @endif
            @endif
        </div>
    </div>
    
    <div class="invoice-info" style="flex: 0 0 auto; text-align: right;">
        <div class="invoice-no">#{{ $invoice->invoice_number }}</div>
        <div class="invoice-date">{{ now()->format('d/m/Y h:i A') }}</div>
    </div>
</div>
        
        <!-- Body -->
        <div class="invoice-body">
            <!-- Recipient Details -->
            <div class="recipient-section">
                <div class="recipient-title">RECIPIENT DETAILS</div>
                <div class="recipient-grid">
                    <div><span class="recipient-label">Name:</span> {{ $invoice->recipient_name }}</div>
                    <div><span class="recipient-label">Phone:</span> <span class="phone-number">{{ $invoice->recipient_phone }}</span></div>
                    <div><span class="recipient-label">Address:</span> {{ $invoice->recipient_address }}</div>
                    <div><span class="recipient-label">Merchant ID:</span> {{ $invoice->merchant_order_id ?: 'N/A' }}</div>
                    <div><span class="recipient-label">Store:</span> {{ $invoice->store_location }}</div>
                    <div><span class="recipient-label">Area:</span> {{ $invoice->delivery_area }}</div>
                </div>
            </div>
            
            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalQuantity = 0;
                        $returnQuantity = 0;
                        $returnSubtotal = 0;
                    @endphp
                    @foreach($invoice->items as $item)
                    @php
                        $totalQuantity += $item->quantity;
                    @endphp
                    <tr>
                        <td>{{ $item->item_name }} {{ $item->description ? '(' . $item->description . ')' : '' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Return Items Table (if exists) -->
            @if($invoice->has_return_items && $invoice->returnItems->count() > 0)
            <div style="margin-top: 5px;">
                <div style="font-weight: bold; color: #d32f2f; margin-bottom: 5px; font-size: 13px;">
                    <i class="fa fa-undo"></i> RETURN ITEMS
                </div>
                <table class="return-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->returnItems as $returnItem)
                        @php
                            $returnQuantity += $returnItem->quantity;
                            $returnSubtotal += $returnItem->total_price;
                        @endphp
                        <tr>
                            <td>{{ $returnItem->item_name }} {{ $returnItem->description ? '(' . $returnItem->description . ')' : '' }}</td>
                            <td class="text-center">{{ $returnItem->quantity }}</td>
                            <td class="text-right">৳{{ number_format($returnItem->unit_price, 2) }}</td>
                            <td class="text-right">৳{{ number_format($returnItem->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Summary Table -->
            <div class="summary-wrapper">
                <div class="summary-section">
                    <div class="summary-title">PAYMENT SUMMARY</div>
                    <table class="summary-table">
                        <tr class="total-qty-row">
                            <td class="label">Total Qty:</td>
                            <td class="value">{{ $totalQuantity }}</td>
                        </tr>
                        @if($invoice->has_return_items && $invoice->returnItems->count() > 0)
                        <tr class="return-row-summary">
                            <td class="label">Return Qty:</td>
                            <td class="value">{{ $returnQuantity }}</td>
                        </tr>
                        @endif
                        <tr><td class="label">Subtotal:</td><td class="value">৳{{ number_format($invoice->items->sum('total_price'), 2) }}</td></tr>
                        @if($invoice->has_return_items && $invoice->returnItems->count() > 0)
                        <tr class="return-row-summary">
                            <td class="label">Less Returns:</td>
                            <td class="value">-৳{{ number_format($returnSubtotal, 2) }}</td>
                        </tr>
                        @endif
                        <tr><td class="label">Net Subtotal:</td><td class="value">৳{{ number_format($invoice->subtotal, 2) }}</td></tr>
                        <tr><td class="label">Delivery:</td><td class="value">৳{{ number_format($invoice->delivery_charge, 2) }}</td></tr>
                        <tr class="total-row"><td class="label">Total:</td><td class="value">৳{{ number_format($invoice->total, 2) }}</td></tr>
                        <tr><td class="label">Advance:</td><td class="value">৳{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                        <tr class="due-row"><td class="label">DUE:</td><td class="value">৳{{ number_format($invoice->due_amount, 2) }}</td></tr>
                        @if($invoice->payment_method)
                        <tr><td class="label">Method:</td><td class="value">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</td></tr>
                        @endif
                        @if($invoice->payment_details)
                        <tr><td class="label">Txn ID:</td><td class="value">{{ $invoice->payment_details }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Button -->
    <div class="print-controls">
        <button onclick="printInvoice()" class="print-btn">
            🖨️ Print Invoice
        </button>
        <div style="margin-top: 10px; font-size: 12px; color: #666;">
            Tip: For best results, use Chrome/Firefox and ensure "Headers and footers" is unchecked in print dialog
        </div>
    </div>
    
    <script>
        function printInvoice() {
            window.print();
        }
        
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        window.addEventListener('beforeprint', function() {
            document.body.classList.add('printing');
            document.querySelector('.print-controls').style.display = 'none';
        });
        
        window.addEventListener('afterprint', function() {
            document.body.classList.remove('printing');
            document.querySelector('.print-controls').style.display = 'block';
        });
    </script>
</body>
</html>