<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->reference_id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        .header {
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .header td {
            vertical-align: top;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .invoice-details {
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
        }
        .status-paid { background-color: #2ecc71; }
        .status-pending { background-color: #f39c12; }
        .status-failed { background-color: #e74c3c; }
        .status-expired { background-color: #95a5a6; }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th {
            text-align: left;
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #ddd;
            border-bottom: none;
        }
        .amount-column {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="company-name">IndoPay</div>
                        <div>Payment Orchestration</div>
                    </td>
                    <td class="invoice-details">
                        <h1>INVOICE</h1>
                        <div>Reference: <strong>#{{ $transaction->reference_id }}</strong></div>
                        <div>Date: {{ $transaction->created_at->format('d M Y') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 20px;">
            <span class="status-badge status-{{ $transaction->status->value }}">
                {{ $transaction->status->value }}
            </span>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount-column">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($transaction->payload['itemDetails']) && is_array($transaction->payload['itemDetails']) && count($transaction->payload['itemDetails']) > 0)
                    @foreach($transaction->payload['itemDetails'] as $item)
                        <tr>
                            <td>
                                {{ $item['name'] ?? 'Item' }} 
                                <br>
                                <small style="color: #666;">x{{ $item['quantity'] ?? 1 }}</small>
                            </td>
                            <td class="amount-column">
                                {{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>Payment Reference #{{ $transaction->reference_id }}</td>
                        <td class="amount-column">{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                
                <tr class="total-row">
                    <td>Total</td>
                    <td class="amount-column">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        @if($transaction->status === \IndoPay\Enums\PaymentStatus::PAID)
            <div style="margin-top: 30px; text-align: center; color: #2ecc71; font-weight: bold;">
                PAID ON {{ $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i') : '-' }}
            </div>
        @endif
    </div>
</body>
</html>
