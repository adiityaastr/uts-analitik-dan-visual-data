@extends('layouts.app')
@section('title', 'Dashboard Analitik Penjualan')

@section('content')
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value mt-1">Rp {{ number_format($kpi['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon bg-primary-light">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value mt-1">{{ number_format($kpi['total_transactions'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon bg-success-light">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Rata-rata Transaksi</div>
                        <div class="stat-value mt-1">Rp {{ number_format($kpi['average_transaction'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon bg-warning-light">
                        <i class="bi bi-calculator"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-value mt-1">{{ $kpi['total_products'] ?? 0 }}</div>
                        <small class="text-muted">{{ $kpi['total_categories'] ?? 0 }} kategori</small>
                    </div>
                    <div class="stat-icon bg-danger-light">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i> Tren Penjualan (Line Chart)
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i> Distribusi Kategori (Pie Chart)
            </div>
            <div class="card-body">
                <canvas id="pieChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i> Total Penjualan Per Produk (Bar Chart)
            </div>
            <div class="card-body">
                <canvas id="productChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-bar-chart-steps me-2"></i> Penjualan Per Kategori Per Bulan (Bar Chart)
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const chartColors = [
        '#624bff', '#19cb98', '#ffaa46', '#e53f3c', '#00b8d4',
        '#36b37e', '#6554c0', '#ff8f73', '#2684ff', '#ff5630'
    ];

    // === Line Chart: Tren Penjualan ===
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($trend['labels']) !!},
            datasets: [{
                label: 'Total Penjualan (Rp)',
                data: {!! json_encode($trend['values']) !!},
                borderColor: '#624bff',
                backgroundColor: 'rgba(98, 75, 255, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        maxTicksLimit: 12,
                        maxRotation: 0,
                        autoSkip: true,
                        autoSkipPadding: 20,
                        font: { size: 11 }
                    }
                },
                y: {
                    ticks: {
                        callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M',
                        font: { size: 11 }
                    },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });

    // === Pie Chart: Distribusi Kategori ===
    new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($category_distribution['labels']) !!},
            datasets: [{
                data: {!! json_encode($category_distribution['values']) !!},
                backgroundColor: chartColors,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, usePointStyle: true, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.parsed / total) * 100).toFixed(1);
                            return ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID') + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });

    // === Bar Chart: Total Penjualan Per Produk ===
    new Chart(document.getElementById('productChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($by_product['labels']) !!},
            datasets: [{
                label: 'Total Penjualan (Rp)',
                data: {!! json_encode($by_product['values']) !!},
                backgroundColor: chartColors,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M' },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });

    // === Bar Chart: Penjualan Per Kategori Per Bulan ===
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($by_category_monthly['labels']) !!},
            datasets: {!! json_encode($by_category_monthly['datasets']) !!}.map((ds, i) => ({
                ...ds,
                backgroundColor: chartColors[i % chartColors.length],
                borderRadius: 4,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 12, usePointStyle: true, font: { size: 11 } }
                },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } },
                y: {
                    ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'M' },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
</script>
@endsection
