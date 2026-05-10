@extends('layouts.app')
@section('title', 'Analisis Penjualan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i> Penjualan Per Produk Berdasarkan Minggu
            </div>
            <div class="card-body">
                <canvas id="weeklyProductChart" height="350"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-lg-6">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-table me-2"></i> Top Produk Berdasarkan Revenue
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Total Penjualan</th>
                                <th>Jumlah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topProducts = \App\Models\Sales::select('produk', \Illuminate\Support\Facades\DB::raw('SUM(total) as total_penjualan'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as jumlah_transaksi'))
                                    ->groupBy('produk')
                                    ->orderByDesc('total_penjualan')
                                    ->get();
                            @endphp
                            @foreach($topProducts as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $p->produk }}</td>
                                <td>Rp {{ number_format($p->total_penjualan, 0, ',', '.') }}</td>
                                <td>{{ $p->jumlah_transaksi }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-table me-2"></i> Top Kategori Berdasarkan Revenue
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kategori</th>
                                <th>Total Penjualan</th>
                                <th>Jumlah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topCategories = \App\Models\Sales::select('kategori', \Illuminate\Support\Facades\DB::raw('SUM(total) as total_penjualan'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as jumlah_transaksi'))
                                    ->groupBy('kategori')
                                    ->orderByDesc('total_penjualan')
                                    ->get();
                            @endphp
                            @foreach($topCategories as $i => $c)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $c->kategori }}</td>
                                <td>Rp {{ number_format($c->total_penjualan, 0, ',', '.') }}</td>
                                <td>{{ $c->jumlah_transaksi }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const weeklyData = @json(app(\App\Services\AnalyticsService::class)->getWeeklySalesByProduct());
    const colors = ['#624bff', '#19cb98', '#ffaa46', '#e53f3c', '#00b8d4', '#36b37e', '#6554c0', '#ff8f73'];

    new Chart(document.getElementById('weeklyProductChart'), {
        type: 'bar',
        data: {
            labels: weeklyData.labels,
            datasets: weeklyData.datasets.map((ds, i) => ({
                ...ds,
                backgroundColor: colors[i % colors.length],
                borderRadius: 4,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
            },
            scales: {
                x: { grid: { display: false } },
                y: { ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M' }, grid: { color: '#f1f5f9' } }
            }
        }
    });
</script>
@endsection
