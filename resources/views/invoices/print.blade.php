<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        /* Modern, clean styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            background: white;
            border: 1px solid #e5e5e5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        /* Header */
        .invoice-header {
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .shop-name {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        
        .shop-address {
            font-size: 11px;
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .invoice-title {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .invoice-no {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            font-size: 12px;
            color: #888;
        }
        
        /* Body */
        .invoice-body {
            padding: 20px;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 8px;
            font-size: 12px;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
        }
        
        .info-value {
            color: #1a1a1a;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 11px;
        }
        
        .items-table thead {
            background: #f8f9fa;
        }
        
        .items-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 500;
            color: #555;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .items-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .items-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        /* Totals Section */
        .totals-container {
            background: #fafafa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #f0f0f0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        .total-row:last-child {
            margin-bottom: 0;
        }
        
        .grand-total {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            padding-top: 8px;
            margin-top: 8px;
            border-top: 1px solid #e5e5e5;
        }
        
        /* Payment Section */
        .payment-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e5e5e5;
            margin-top: 20px;
        }
        
        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .payment-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-paid {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .status-partial {
            background: #fff8e1;
            color: #f57c00;
            border: 1px solid #ffe0b2;
        }
        
        .status-unpaid {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        /* Footer */
        .invoice-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px dashed #e5e5e5;
            margin-top: 20px;
        }
        
        .thank-you {
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .footer-note {
            font-size: 10px;
            color: #888;
            line-height: 1.4;
        }
        
        /* Print Controls - Only visible on screen */
        .print-controls {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            background: #f8f9fa;
            color: #333;
        }
        
        .btn:hover {
            background: #e9ecef;
            border-color: #ccc;
        }
        
        /* Print-specific styles */
        @media print {
            @page {
                margin: 5mm;
                size: auto;
            }
            
            body {
                padding: 0;
                background: white;
                font-size: 11pt;
            }
            
            .invoice-container {
                max-width: 100%;
                border: none;
                box-shadow: none;
                margin: 0;
            }
            
            .print-controls {
                display: none !important;
            }
            
            /* Hide browser headers/footers */
            @page {
                margin-top: 0;
                margin-bottom: 0;
            }
            
            body::after,
            body::before {
                display: none !important;
            }
        }
        
        /* Mobile responsiveness */
        @media screen and (max-width: 400px) {
            body {
                padding: 10px;
            }
            
            .invoice-container {
                max-width: 100%;
            }
            
            .invoice-header,
            .invoice-body {
                padding: 15px;
            }
            
            .info-grid {
                grid-template-columns: 80px 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="shop-name">Faisal Textile</div>
            <div class="shop-address">
                20, Balaka Vhaban, Chadni Chawk Market (Ground Floor)<br>
                Dhaka 1205 • Phone: 01923232543
            </div>
            <div class="invoice-title">Delivery Invoice</div>
            <div class="invoice-no">#{{ $invoice->invoice_number }}</div>
        </div>
        
        <!-- Body -->
        <div class="invoice-body">
            <!-- Recipient Information -->
            <div class="section">
                <div class="section-title">Recipient Details</div>
                <div class="info-grid">
                    <div class="info-label">Name:</div>
                    <div class="info-value">{{ $invoice->recipient_name }}</div>
                    
                    <div class="info-label">Phone:</div>
                    <div class="info-value">{{ $invoice->recipient_phone }}</div>
                    
                    @if($invoice->recipient_secondary_phone)
                    <div class="info-label">Alt Phone:</div>
                    <div class="info-value">{{ $invoice->recipient_secondary_phone }}</div>
                    @endif
                    
                    <div class="info-label">Address:</div>
                    <div class="info-value">{{ $invoice->recipient_address }}</div>
                    
                    <div class="info-label">Merchant Order Id:</div>
                    <div class="info-value">{{ $invoice->merchant_order_id }}</div>
                    
                    <div class="info-label">Area:</div>
                    <div class="info-value">{{ $invoice->delivery_area }}</div>
                    
                    <div class="info-label">Store:</div>
                    <div class="info-value">{{ $invoice->store_location }}</div>
                    
                    <div class="info-label">Delivery:</div>
                    <div class="info-value">{{ $invoice->delivery_type }}</div>
                </div>
            </div>
            
            <!-- Items Table -->
            <div class="section">
                <div class="section-title">Items</div>
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
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Totals -->
            <div class="totals-container">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>৳{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Delivery Charge:</span>
                    <span>৳{{ number_format($invoice->delivery_charge, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Amount:</span>
                    <span><strong>৳{{ number_format($invoice->total, 2) }}</strong></span>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="payment-container">
                <div class="payment-header">
                    <div style="font-size: 13px; font-weight: 600;">Payment Summary</div>
                    <div class="payment-status status-{{ $invoice->payment_status }}">
                        {{ strtoupper($invoice->payment_status) }}
                    </div>
                </div>
                
                <div class="info-grid" style="grid-template-columns: 90px 1fr;">
                    <div class="info-label">Advance:</div>
                    <div class="info-value">৳{{ number_format($invoice->paid_amount, 2) }}</div>
                    
                    <div class="info-label">Due:</div>
                    <div class="info-value" style="font-weight: 600;">৳{{ number_format($invoice->due_amount, 2) }}</div>
                    
                    @if($invoice->payment_method)
                    <div class="info-label">Method:</div>
                    <div class="info-value">{{ ucfirst($invoice->payment_method) }}</div>
                    @endif
                    
                    @if($invoice->payment_details)
                    <div class="info-label">Details:</div>
                    <div class="info-value" style="font-size: 11px;">{{ $invoice->payment_details }}</div>
                    @endif
                </div>
            </div>
            
            <!-- Footer -->
            <div class="invoice-footer">
                <div class="thank-you">Thank you for your business!</div>
                <div class="footer-note">
                    Please keep this invoice for any queries<br>
                    Invoice generated on {{ now()->format('d/m/Y h:i A') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Controls - Hidden in print -->
    <div class="print-controls">
        <button onclick="printInvoice()" class="btn">Print Invoice</button>
        <a href="{{ route('invoices.pos') }}" class="btn">New Invoice</a>
        <a href="{{ route('invoices.index') }}" class="btn">Invoice List</a>
    </div>
    
    <script>
        // Custom print function for better control
        function printInvoice() {
            // Hide print controls before printing
            document.querySelector('.print-controls').style.display = 'none';
            
            // Print the document
            window.print();
            
            // Restore print controls after a delay
            setTimeout(() => {
                document.querySelector('.print-controls').style.display = 'block';
            }, 500);
        }
        
        // Auto-print if URL has autoprint parameter
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const autoPrint = urlParams.get('autoprint');
            
            if (autoPrint === '1') {
                setTimeout(() => {
                    printInvoice();
                }, 500);
            }
        };
        
        window.addEventListener('beforeprint', function() {
            document.title = 'Invoice #{{ $invoice->invoice_number }}';
        });
    </script>
</body>
</html>