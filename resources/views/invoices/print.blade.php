<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .shop-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .shop-address {
            font-size: 12px;
            opacity: 0.9;
            line-height: 1.4;
        }
        
        .invoice-title {
            margin-top: 15px;
            font-size: 18px;
            font-weight: 500;
        }
        
        .invoice-no {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
            font-size: 14px;
        }
        
        .invoice-body {
            padding: 20px;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
            min-width: 120px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        
        .items-table th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            font-weight: 500;
            color: #555;
            border-bottom: 1px solid #dee2e6;
        }
        
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .grand-total {
            font-size: 16px;
            font-weight: 600;
            color: #667eea;
            border-top: 2px solid #dee2e6;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .payment-info {
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .payment-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-partial {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        
        .thank-you {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
            font-size: 13px;
            border-top: 1px dashed #dee2e6;
            margin-top: 20px;
        }
        
        .print-controls {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
                border-radius: 0;
            }
            
            .print-controls {
                display: none;
            }
            
            .thank-you {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="shop-name">Faisal Textile</div>
            <div class="shop-address">
                12, Balaka Vhaban, Chadni Chawk Market (Ground Floor), Dhaka 1205<br>
                Phone: 01883024878
            </div>
            <div class="invoice-title">DELIVERY INVOICE</div>
            <div class="invoice-no">#{{ $invoice->invoice_number }}</div>
            <div style="margin-top: 8px; font-size: 13px;">
                {{ $invoice->invoice_date->format('d/m/Y h:i A') }}
            </div>
        </div>
        
        <!-- Body Section -->
        <div class="invoice-body">
            <!-- Recipient Information -->
            <div class="section">
                <div class="section-title">Recipient Details</div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $invoice->recipient_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $invoice->recipient_phone }}</span>
                </div>
                @if($invoice->recipient_secondary_phone)
                <div class="info-row">
                    <span class="info-label">Alt Phone:</span>
                    <span class="info-value">{{ $invoice->recipient_secondary_phone }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $invoice->recipient_address }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Area:</span>
                    <span class="info-value">{{ $invoice->delivery_area }}</span>
                </div>
            </div>
            
            <!-- Delivery Information -->
            <div class="section">
                <div class="section-title">Delivery Information</div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">{{ $invoice->delivery_type }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Weight:</span>
                    <span class="info-value">{{ number_format($invoice->total_weight) }} g</span>
                </div>
                @if($invoice->special_instructions)
                <div class="info-row">
                    <span class="info-label">Instructions:</span>
                    <span class="info-value">{{ $invoice->special_instructions }}</span>
                </div>
                @endif
            </div>
            
            <!-- Items Table -->
            <div class="section">
                <div class="section-title">Items</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>৳{{ number_format($item->unit_price, 2) }}</td>
                            <td>৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Totals Section -->
            <div class="total-section">
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
                    <span>৳{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="payment-info">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div class="info-row">
                            <span class="info-label" style="min-width: 80px;">Advance:</span>
                            <span class="info-value">৳{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label" style="min-width: 80px;">Due:</span>
                            <span class="info-value" style="font-weight: 600;">৳{{ number_format($invoice->due_amount, 2) }}</span>
                        </div>
                        @if($invoice->payment_method)
                        <div class="info-row">
                            <span class="info-label" style="min-width: 80px;">Method:</span>
                            <span class="info-value">{{ ucfirst($invoice->payment_method) }}</span>
                        </div>
                        @endif
                        @if($invoice->payment_details)
                        <div class="info-row">
                            <span class="info-label" style="min-width: 80px;">Details:</span>
                            <span class="info-value">{{ $invoice->payment_details }}</span>
                        </div>
                        @endif
                    </div>
                    <div>
                        <span class="payment-status status-{{ $invoice->payment_status }}">
                            {{ strtoupper($invoice->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Thank You Message -->
            <div class="thank-you">
                <div>Thank you for your business!</div>
                <div style="margin-top: 5px; font-size: 11px;">
                    * Please keep this invoice for any queries
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Controls -->
    <div class="print-controls">
        <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
        <a href="{{ route('invoices.pos') }}" class="btn btn-secondary">New Invoice</a>
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Invoice List</a>
    </div>
    
    <script>
        // Auto print after 1 second
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>