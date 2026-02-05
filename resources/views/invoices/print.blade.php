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
        
        /* Header - improved spacing */
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
        
        /* Body - compact spacing */
        .invoice-body { padding: 12px 15px; width: 100%; }
        
        /* Recipient Details - compact */
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
        /* Increased phone number font size */
        .phone-number {
            font-size: 14px; /* Increased from 12px to 14px */
            font-weight: 600;
            color: #333;
        }
        
        /* Items Table - compact */
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
            font-weight: ; 
            border: 1px solid #b3e0ff; 
            font-size: 14px;
        }
        .items-table td { 
            padding: 6px 4px; 
            border: 1px solid #e5e5e5; 
            font-size: 12px;
        }
        .items-table th:nth-child(1) { width: 50%; }
        .items-table th:nth-child(2) { width: 10%; }
        .items-table th:nth-child(3) { width: 20%; }
        .items-table th:nth-child(4) { width: 20%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Summary Table - right aligned, compact */
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
        
        /* Specific styles for summary items */
        .total-qty-row { 
            font-weight: 700; 
            font-size: 14px; /* Larger font for total quantity */
            color: #333;
        }
        .total-row { 
            font-weight: 700; 
            font-size: 14px; /* Larger font for total */
        }
        .due-row { 
            font-weight: 800; /* Bolder */
            font-size: 16px; /* Larger font for due amount */
            color: #d32f2f;
            border-top: 1px solid #e5e5e5;
            padding-top: 6px;
            margin-top: 4px;
        }
        
        /* Print button - visible on screen */
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
        
        /* Print styles - Remove browser headers/footers */
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
            
            .invoice-header, .items-table thead {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .print-controls { display: none !important; }
            
            /* Hide URL and page numbers */
            @page :footer { display: none; }
            @page :header { display: none; }
            
            /* Ensure due amount stands out in print */
            .due-row {
                font-size: 16px !important;
                font-weight: 800 !important;
                color: #d32f2f !important;
            }
        }
        
        /* Mobile */
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
        <!-- Header with improved spacing -->
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
                    <!-- Increased phone number size -->
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
                    @endphp
                    @foreach($invoice->items as $item)
                    @php
                        $totalQuantity += $item->quantity;
                    @endphp
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Summary Table - right side -->
            <div class="summary-wrapper">
                <div class="summary-section">
                    <div class="summary-title">PAYMENT SUMMARY</div>
                    <table class="summary-table">
                        <!-- Added total quantity row with larger font -->
                        <tr class="total-qty-row">
                            <td class="label">Total Qty:</td>
                            <td class="value">{{ $totalQuantity }}</td>
                        </tr>
                        <tr><td class="label">Subtotal:</td><td class="value">৳{{ number_format($invoice->subtotal, 2) }}</td></tr>
                        <tr><td class="label">Delivery:</td><td class="value">৳{{ number_format($invoice->delivery_charge, 2) }}</td></tr>
                        <tr class="total-row"><td class="label">Total:</td><td class="value">৳{{ number_format($invoice->total, 2) }}</td></tr>
                        <tr><td class="label">Advance:</td><td class="value">৳{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                        <!-- Due amount with larger and bolder font -->
                        <tr class="due-row"><td class="label">DUE:</td><td class="value">৳{{ number_format($invoice->due_amount, 2) }}</td></tr>
                        @if($invoice->payment_method)
                        <tr><td class="label">Method:</td><td class="value">{{ ucfirst($invoice->payment_method) }}</td></tr>
                        @endif
                        @if($invoice->payment_details)
                        <tr><td class="label">Txn ID:</td><td class="value">{{ $invoice->payment_details }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Button - Visible on screen -->
    <div class="print-controls">
        <button onclick="printInvoice()" class="print-btn">
            🖨️ Print Invoice
        </button>
        <div style="margin-top: 10px; font-size: 12px; color: #666;">
            Tip: For best results, use Chrome/Firefox and ensure "Headers and footers" is unchecked in print dialog
        </div>
    </div>
    
    <script>
        // Simple print function
        function printInvoice() {
            // Trigger browser print
            window.print();
        }
        
        // Auto-print if parameter exists
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        // Modern print handling with better header/footer removal
        window.addEventListener('beforeprint', function() {
            // Add print-specific class
            document.body.classList.add('printing');
            
            // Remove print button from print view
            document.querySelector('.print-controls').style.display = 'none';
        });
        
        window.addEventListener('afterprint', function() {
            // Remove print-specific class
            document.body.classList.remove('printing');
            
            document.querySelector('.print-controls').style.display = 'block';
        });
    </script>
</body>
</html>