<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Report Summary - {{ $fromDate->format('d/m/Y') }} to {{ $toDate->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background-color: #fff;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .subtitle {
            font-size: 14px;
            color: #7f8c8d;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #3498db;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .card .amount {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }
        .card .small-text {
            font-size: 11px;
            color: #777;
            margin-top: 5px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 13px;
        }
        td {
            border: 1px solid #ddd;
            padding: 10px 8px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 11px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .print-button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 10px;
        }
        .close-button {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            th {
                background-color: #3498db !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .card {
                background-color: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">Faisal Textile</div>
            <div class="title">INVOICE REPORT SUMMARY</div>
            <div class="subtitle">
                Period: {{ $fromDate->format('d F Y') }} - {{ $toDate->format('d F Y') }}
                <br>
                Generated on: {{ now()->format('d F Y h:i A') }}
                <br>
                Generated by: {{ auth()->user()->name ?? 'System' }}
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <h3>Total Invoices</h3>
                <div class="amount">{{ number_format($summary['total_invoices']) }}</div>
                <div class="small-text">Period total</div>
            </div>
            <div class="card">
                <h3>Total Quantity</h3>
                <div class="amount">{{ number_format($summary['total_quantity'] ?? 0) }}</div>
                <div class="small-text">Items sold</div>
            </div>
            <div class="card">
                <h3>Total Amount</h3>
                <div class="amount">৳{{ number_format($summary['total_amount'], 2) }}</div>
                <div class="small-text">Invoice value</div>
            </div>
            <div class="card">
                <h3>Total Paid</h3>
                <div class="amount">৳{{ number_format($summary['total_paid'], 2) }}</div>
                <div class="small-text">Collected</div>
            </div>
            <div class="card">
                <h3>Total Due</h3>
                <div class="amount">৳{{ number_format($summary['total_due'], 2) }}</div>
                <div class="small-text">Receivable</div>
            </div>
            <div class="card">
                <h3>Subtotal</h3>
                <div class="amount">৳{{ number_format($summary['total_subtotal'], 2) }}</div>
                <div class="small-text">Product value</div>
            </div>
            <div class="card">
                <h3>Delivery Charge</h3>
                <div class="amount">৳{{ number_format($summary['total_delivery_charge'], 2) }}</div>
                <div class="small-text">Shipping cost</div>
            </div>
            <div class="card">
                <h3>Avg Order Value</h3>
                <div class="amount">৳{{ number_format($summary['total_amount'] / max($summary['total_invoices'], 1), 2) }}</div>
                <div class="small-text">Per invoice</div>
            </div>
        </div>

        <!-- Financial Summary Table -->
        <div class="section-title">FINANCIAL SUMMARY</div>
        <table>
            <thead>
                <tr>
                    <th width="50%">Description</th>
                    <th width="25%" class="text-center">Count/Quantity</th>
                    <th width="25%" class="text-end">Amount (৳)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total Invoices</strong></td>
                    <td class="text-center">{{ number_format($summary['total_invoices']) }}</td>
                    <td class="text-end">—</td>
                </tr>
                <tr>
                    <td><strong>Total Quantity (Items)</strong></td>
                    <td class="text-center">{{ number_format($summary['total_quantity'] ?? 0) }}</td>
                    <td class="text-end">—</td>
                </tr>
                <tr>
                    <td><strong>Subtotal (Product Value)</strong></td>
                    <td class="text-center">—</td>
                    <td class="text-end">৳{{ number_format($summary['total_subtotal'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Delivery Charge</strong></td>
                    <td class="text-center">—</td>
                    <td class="text-end">৳{{ number_format($summary['total_delivery_charge'], 2) }}</td>
                </tr>
                <tr style="background-color: #e8f4f8; font-weight: bold;">
                    <td><strong>TOTAL INVOICE AMOUNT</strong></td>
                    <td class="text-center">—</td>
                    <td class="text-end"><strong>৳{{ number_format($summary['total_amount'], 2) }}</strong></td>
                </tr>
                <tr style="background-color: #d4edda;">
                    <td><strong>TOTAL PAID AMOUNT (COLLECTED)</strong></td>
                    <td class="text-center">—</td>
                    <td class="text-end"><strong style="color: #28a745;">৳{{ number_format($summary['total_paid'], 2) }}</strong></td>
                </tr>
                <tr style="background-color: #f8d7da;">
                    <td><strong>TOTAL DUE AMOUNT (RECEIVABLE)</strong></td>
                    <td class="text-center">—</td>
                    <td class="text-end"><strong style="color: #dc3545;">৳{{ number_format($summary['total_due'], 2) }}</strong></td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background-color: #e9ecef;">
                    <td colspan="2" class="text-end"><strong>Collection Rate:</strong></td>
                    <td class="text-end">
                        <strong>
                            @if($summary['total_amount'] > 0)
                                {{ number_format(($summary['total_paid'] / $summary['total_amount']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- User Performance Summary -->
        @if(isset($createdByStats) && $createdByStats->count() > 0)
        <div class="section-title">CREATOR PERFORMANCE SUMMARY</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Creator Name</th>
                    <th class="text-center">Orders</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Total (৳)</th>
                    <th class="text-end">Paid (৳)</th>
                    <th class="text-end">Due (৳)</th>
                    <th class="text-center">Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($createdByStats as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $user['name'] }}</strong></td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $user['count'] }}</span>
                    </td>
                    <td class="text-center">{{ number_format($user['quantity']) }}</td>
                    <td class="text-end">৳{{ number_format($user['total'], 2) }}</td>
                    <td class="text-end">৳{{ number_format($user['paid'], 2) }}</td>
                    <td class="text-end">৳{{ number_format($user['due'], 2) }}</td>
                    <td class="text-center">
                        @php
                            $collectionRate = $user['total'] > 0 ? ($user['paid'] / $user['total']) * 100 : 0;
                        @endphp
                        <span class="badge {{ $collectionRate >= 80 ? 'badge-success' : ($collectionRate >= 50 ? 'badge-warning' : 'badge-danger') }}">
                            {{ number_format($collectionRate, 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #e9ecef; font-weight: bold;">
                    <td colspan="2" class="text-end">TOTAL:</td>
                    <td class="text-center">{{ number_format($createdByStats->sum('count')) }}</td>
                    <td class="text-center">{{ number_format($createdByStats->sum('quantity')) }}</td>
                    <td class="text-end">৳{{ number_format($createdByStats->sum('total'), 2) }}</td>
                    <td class="text-end">৳{{ number_format($createdByStats->sum('paid'), 2) }}</td>
                    <td class="text-end">৳{{ number_format($createdByStats->sum('due'), 2) }}</td>
                    <td class="text-center">—</td>
                </tr>
            </tfoot>
        </table>
        @endif

        <!-- Summary Statistics -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 30px;">
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">📊 Period Statistics</h4>
                <table style="width: 100%; border: none; margin-bottom: 0;">
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Daily Average Orders:</strong></td>
                        <td style="border: none; text-align: right;">{{ number_format($summary['total_invoices'] / max($fromDate->diffInDays($toDate) + 1, 1), 1) }}</td>
                    </tr>
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Daily Average Amount:</strong></td>
                        <td style="border: none; text-align: right;">৳{{ number_format($summary['total_amount'] / max($fromDate->diffInDays($toDate) + 1, 1), 2) }}</td>
                    </tr>
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Average Items/Order:</strong></td>
                        <td style="border: none; text-align: right;">{{ number_format(($summary['total_quantity'] ?? 0) / max($summary['total_invoices'], 1), 1) }}</td>
                    </tr>
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Average Order Value:</strong></td>
                        <td style="border: none; text-align: right;">৳{{ number_format($summary['total_amount'] / max($summary['total_invoices'], 1), 2) }}</td>
                    </tr>
                </table>
            </div>
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">💰 Payment Summary</h4>
                <table style="width: 100%; border: none; margin-bottom: 0;">
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Collection Rate:</strong></td>
                        <td style="border: none; text-align: right;">
                            @if($summary['total_amount'] > 0)
                                {{ number_format(($summary['total_paid'] / $summary['total_amount']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Outstanding Ratio:</strong></td>
                        <td style="border: none; text-align: right;">
                            @if($summary['total_amount'] > 0)
                                {{ number_format(($summary['total_due'] / $summary['total_amount']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    <tr style="background: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Delivery Charge %:</strong></td>
                        <td style="border: none; text-align: right;">
                            @if($summary['total_amount'] > 0)
                                {{ number_format(($summary['total_delivery_charge'] / $summary['total_amount']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated summary report. No signature is required.</p>
            <p>© {{ date('Y') }} Your Company Name. All rights reserved.</p>
            <p style="font-size: 10px; margin-top: 10px;">Report ID: RPT-{{ date('Ymd') }}-{{ rand(1000, 9999) }}</p>
        </div>

        <!-- Print Controls -->
        <div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px;">
            <button onclick="window.print();" class="print-button">
                🖨️ Print Summary Report
            </button>
            <button onclick="window.close();" class="close-button">
                ✖️ Close
            </button>
        </div>
    </div>

    <script>
        // Auto-print dialog (uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>