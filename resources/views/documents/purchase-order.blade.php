<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Purchase Order {{ $purchaseOrder->po_number }}</h1>
    <p>Status: {{ $purchaseOrder->status?->value ?? $purchaseOrder->status }}</p>
    <p>Supplier: {{ $purchaseOrder->relationship?->supplierOrganization?->name }}</p>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrder->lines as $line)
                <tr>
                    <td>{{ $line->product?->sku }}</td>
                    <td>{{ $line->product?->name }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>{{ $line->unit_price }}</td>
                    <td>{{ $line->line_total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Grand Total:</strong> {{ $purchaseOrder->grand_total }} {{ $purchaseOrder->currency_code }}</p>
</body>
</html>
