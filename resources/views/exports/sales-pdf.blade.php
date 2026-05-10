<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #624bff; padding-bottom: 12px; margin-bottom: 16px; }
        .header h2 { color: #624bff; margin: 0 0 4px 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #624bff; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Penjualan</h2>
        <p>Dashboard Analitik Penjualan - 411231139 Muhamad Aditya Saputra</p>
        <p>Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->tanggal->format('d/m/Y') }}</td>
                <td>{{ $sale->produk }}</td>
                <td>{{ $sale->kategori }}</td>
                <td class="text-right">{{ $sale->jumlah }}</td>
                <td class="text-right">Rp {{ number_format($sale->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        &copy; {{ date('Y') }} Muhamad Aditya Saputra (411231139) - UTS Dashboard Analitik Penjualan
    </div>
</body>
</html>
