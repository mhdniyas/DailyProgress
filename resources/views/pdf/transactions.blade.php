<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report</h1>
        <p>{{ now()->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date }}</td>
                <td>{{ ucfirst($transaction->type) }}</td>
                <td>{{ $transaction->category->name }}</td>
                <td>₹{{ number_format($transaction->amount, 2) }}</td>
                <td>{{ $transaction->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
