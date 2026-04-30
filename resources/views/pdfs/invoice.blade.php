<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            background: #fff;
            padding: 12mm;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10mm;
            padding-bottom: 6mm;
            border-bottom: 2px solid #1a1a2e;
        }

        .brand-name {
            font-size: 22pt;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 1px;
        }

        .brand-tagline {
            font-size: 8pt;
            color: #888;
            margin-top: 1mm;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .invoice-number {
            font-size: 10pt;
            color: #555;
            margin-top: 2mm;
        }

        .invoice-date {
            font-size: 9pt;
            color: #777;
            margin-top: 1mm;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 2mm 4mm;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2mm;
        }

        .status-paid { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #d1ecf1; color: #0c5460; }

        /* Bill to section */
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8mm;
        }

        .billing-block h3 {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #888;
            margin-bottom: 2mm;
        }

        .billing-block p {
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }

        /* Items table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
        }

        thead tr {
            background: #1a1a2e;
            color: #fff;
        }

        thead th {
            padding: 3mm 4mm;
            text-align: left;
            font-size: 9pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        thead th:last-child { text-align: right; }

        tbody tr {
            border-bottom: 1px solid #eee;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        tbody td {
            padding: 3mm 4mm;
            font-size: 10pt;
            vertical-align: top;
        }

        tbody td:last-child { text-align: right; }

        .course-title { font-weight: 600; color: #1a1a2e; }
        .course-level { font-size: 8pt; color: #888; margin-top: 1mm; }

        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8mm;
        }

        .totals-table {
            width: 70mm;
        }

        .totals-table tr td {
            padding: 1.5mm 0;
            font-size: 10pt;
        }

        .totals-table tr td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .totals-table .total-row {
            border-top: 2px solid #1a1a2e;
            font-size: 12pt;
            font-weight: bold;
            color: #1a1a2e;
        }

        .totals-table .total-row td {
            padding-top: 3mm;
        }

        .discount-row { color: #28a745; }

        /* Payment info */
        .payment-info {
            background: #f8f9fa;
            border-left: 3px solid #1a1a2e;
            padding: 4mm;
            margin-bottom: 6mm;
            font-size: 9pt;
        }

        .payment-info h4 {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
            margin-bottom: 2mm;
        }

        .payment-info p { color: #333; line-height: 1.6; }

        /* Footer */
        .footer {
            border-top: 1px solid #eee;
            padding-top: 4mm;
            text-align: center;
            font-size: 8pt;
            color: #aaa;
        }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div>
        <div class="brand-name">{{ config('app.name', 'CoursePalette') }}</div>
        <div class="brand-tagline">Online Learning Platform</div>
    </div>
    <div class="invoice-meta">
        <div class="invoice-title">Invoice</div>
        <div class="invoice-number"># {{ $invoice->invoice_number }}</div>
        <div class="invoice-date">{{ $invoice->created_at->format('F j, Y') }}</div>
        <div>
            <span class="status-badge status-{{ strtolower($invoice->status) }}">
                {{ $invoice->status }}
            </span>
        </div>
    </div>
</div>

{{-- Billing Info --}}
<div class="billing-section">
    <div class="billing-block">
        <h3>Bill To</h3>
        <p>
            <strong>{{ $invoice->user->name }}</strong><br>
            {{ $invoice->user->email }}<br>
            @if($invoice->user->phone){{ $invoice->user->phone }}<br>@endif
            @if($invoice->user->address){{ $invoice->user->address }}@endif
        </p>
    </div>
    <div class="billing-block" style="text-align:right">
        <h3>Payment Details</h3>
        <p>
            @if($invoice->paid_at)
                Paid on {{ $invoice->paid_at->format('F j, Y') }}<br>
            @endif
            @if($invoice->payment_method)
                Method: {{ ucfirst($invoice->payment_method) }}<br>
            @endif
            @if($invoice->transaction_id)
                Ref: {{ $invoice->transaction_id }}
            @endif
        </p>
    </div>
</div>

{{-- Items Table --}}
<table>
    <thead>
        <tr>
            <th style="width:60%">Course</th>
            <th style="width:20%">Level</th>
            <th style="width:20%">Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->courses as $course)
        <tr>
            <td>
                <div class="course-title">{{ $course->title }}</div>
            </td>
            <td>
                <div class="course-level">{{ $course->level }}</div>
            </td>
            <td>${{ number_format($course->pivot->price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div class="totals-section">
    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td>${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount > 0)
        <tr class="discount-row">
            <td>Discount</td>
            <td>-${{ number_format($invoice->discount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td>Tax (10%)</td>
            <td>${{ number_format($invoice->tax, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total</td>
            <td>${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
        </tr>
    </table>
</div>

{{-- Notes --}}
@if($invoice->notes)
<div class="payment-info">
    <h4>Notes</h4>
    <p>{{ $invoice->notes }}</p>
</div>
@endif

{{-- Footer --}}
<div class="footer">
    <p>{{ config('app.name') }} · {{ config('app.url') }}</p>
    <p>Thank you for learning with us!</p>
</div>

</body>
</html>
