<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                color: #000;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .company-info h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info h2 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }
        
        .customer-info {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .totals-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .totals-table .label {
            font-weight: bold;
            background: #f8f9fa;
            text-align: right;
            width: 40%;
        }
        
        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .paid {
            background: #d4edda;
            color: #155724;
        }
        
        .partial {
            background: #fff3cd;
            color: #856404;
        }
        
        .unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-print:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print Invoice
        </button>
        <button class="btn-print" onclick="window.close()" style="background: #6c757d; margin-left: 10px;">
            ✕ Close Window
        </button>
    </div>
    
    <div class="invoice-container">
        <div class="header">
            <div class="company-info">
                <h1>YOUR COMPANY NAME</h1>
                <p>Company Address Line 2</p>
                <p>Phone: +880 XXXX-XXXXXX | Email: info@company.com</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE #{{ $invoice->invoice_number }}</h2>
                <p><strong>Date:</strong> {{ $invoice->invoice_date->format('F d, Y') }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge {{ $invoice->payment_status }}">
                        {{ strtoupper($invoice->payment_status) }}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="customer-info">
            <h3>BILL TO:</h3>
            <p><strong>{{ $invoice->customer->name }}</strong></p>
            <p>Phone: {{ $invoice->customer->phone_number_1 }}</p>
            <p>Address: {{ $invoice->customer->full_address }}</p>
            @if($invoice->customer->phone_number_2)
            <p>Secondary Phone: {{ $invoice->customer->phone_number_2 }}</p>
            @endif
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="50%">Description</th>
                    <th width="15%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Unit Price</th>
                    <th width="15%" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if($item->description)
                        <br><small>{{ $item->description }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="amount">৳{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Amount:</td>
                <td class="amount">৳{{ number_format($invoice->total, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Paid Amount:</td>
                <td class="amount">৳{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td class="label"><strong>Due Amount:</strong></td>
                <td class="amount"><strong>৳{{ number_format($invoice->due_amount, 2) }}</strong></td>
            </tr>
        </table>
        
        @if($invoice->notes)
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-left: 4px solid #6c757d;">
            <h4 style="margin-top: 0;">Notes:</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer generated invoice. No signature required.</p>
            <p>Printed on: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
    
    <script>
        // Auto-print option
        @if(request('autoprint'))
        window.onload = function() {
            window.print();
        }
        @endif
        
        // Close window after print
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        };
    </script>
</body>
</html>