<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multiple Invoices Print</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            padding: 0;
            color: #000; 
            background: #fff;
        }
        
        @page {
            size: A4;
            margin: 0.5cm;
        }
        
        .invoice-container { 
            width: 100%;
            max-width: 100%;
            margin: 0 auto 20px auto;
            background: white;
            border: 1px solid #eee;
            page-break-after: always;
        }
        
        .invoice-container:last-child {
            page-break-after: auto;
        }
        
        /* Copy all your existing print styles from print.blade.php here */
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
        
        /* Add all other styles from your print.blade.php */
        .invoice-body { padding: 12px 15px; width: 100%; }
        .recipient-section { margin-bottom: 10px; font-size: 12px; }
        .recipient-title { font-weight: bold; margin-bottom: 6px; font-size: 12px; color: #333; }
        .recipient-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; }
        .recipient-label { font-weight: 500; min-width: 60px; color: #555; }
        .phone-number { font-size: 14px; font-weight: 600; color: #333; }
        .items-table { width: 100%; border-collapse: collapse; font-size: 12px; margin: 10px 0; table-layout: fixed; }
        .items-table thead { background: #e6f7ff; }
        .items-table th { padding: 6px 4px; border: 1px solid #b3e0ff; font-size: 14px; }
        .items-table td { padding: 6px 4px; border: 1px solid #e5e5e5; font-size: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-wrapper { display: flex; justify-content: flex-end; margin-top: 12px; width: 100%; }
        .summary-section { width: 45%; border: 1px solid #e5e5e5; padding: 8px; font-size: 14px; background: #f9f9f9; }
        .summary-table { width: 100%; font-size: 14px; }
        .summary-table td { padding: 4px 0; }
        .summary-table .label { font-weight: 500; color: #555; }
        .summary-table .value { text-align: right; font-weight: 600; }
        .total-qty-row { font-weight: 700; font-size: 14px; color: #333; }
        .total-row { font-weight: 700; font-size: 14px; }
        .due-row { font-weight: 800; font-size: 16px; color: #d32f2f; border-top: 1px solid #e5e5e5; padding-top: 6px; margin-top: 4px; }
        
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-header, .items-table thead {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @foreach($invoices as $index => $invoice)
        <div class="invoice-container">
            <!-- Header -->
            <div class="invoice-header">
                <div>
                    <div class="shop-name">Faisal Textile</div>
                    <div class="shop-address">20, Balaka Vhaban, Chadni Chawk Market (Ground Floor)<br>Dhaka 1205 • Phone: 01923232543</div>
                </div>
                <div class="invoice-info">
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
                        @php $totalQuantity = 0; @endphp
                        @foreach($invoice->items as $item)
                            @php $totalQuantity += $item->quantity; @endphp
                            <tr>
                                <td>{{ $item->item_name }} {{ $item->description ? '(' . $item->description . ')' : '' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Summary -->
                <div class="summary-wrapper">
                    <div class="summary-section">
                        <div class="summary-title">PAYMENT SUMMARY</div>
                        <table class="summary-table">
                            <tr class="total-qty-row">
                                <td class="label">Total Qty:</td>
                                <td class="value">{{ $totalQuantity }}</td>
                            </tr>
                            <tr><td class="label">Subtotal:</td><td class="value">৳{{ number_format($invoice->subtotal, 2) }}</td></tr>
                            <tr><td class="label">Delivery:</td><td class="value">৳{{ number_format($invoice->delivery_charge, 2) }}</td></tr>
                            <tr class="total-row"><td class="label">Total:</td><td class="value">৳{{ number_format($invoice->total, 2) }}</td></tr>
                            <tr><td class="label">Advance:</td><td class="value">৳{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                            <tr class="due-row"><td class="label">DUE:</td><td class="value">৳{{ number_format($invoice->due_amount, 2) }}</td></tr>
                            @if($invoice->payment_method)
                                <tr><td class="label">Method:</td><td class="value">{{ ucfirst($invoice->payment_method) }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
      
    @endforeach
    
    <script>
        // Auto-print when loaded
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>