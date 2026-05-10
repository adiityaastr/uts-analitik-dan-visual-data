@extends('layouts.app')
@section('title', 'Insight Analisis')

@section('content')
@php
    $sales = \App\Models\Sales::all();
    $topProduct = $sales->groupBy('produk')->map(fn($g) => $g->sum('total'))->sortDesc()->keys()->first();
    $topCategory = $sales->groupBy('kategori')->map(fn($g) => $g->sum('total'))->sortDesc()->keys()->first();
    $totalRevenue = $sales->sum('total');
    $topProductRevenue = $sales->where('produk', $topProduct)->sum('total');
    $topCategoryRevenue = $sales->where('kategori', $topCategory)->sum('total');
    $avgTransaction = $sales->avg('total');
    $products = $sales->unique('produk')->count();
    $categories = $sales->unique('kategori')->count();
    $highestTransaction = $sales->sortByDesc('total')->first();
@endphp

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold"><i class="bi bi-lightbulb-fill me-2 text-warning"></i> Insight Hasil Analisis Data Penjualan</h4>
        <p class="text-muted">Berikut adalah hasil analisis dari {{ number_format($sales->count()) }} data penjualan yang telah dibersihkan dan dianalisis.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="insight-box">
            <h5 class="fw-bold"><i class="bi bi-trophy-fill me-2 text-warning"></i> Produk Terlaris</h5>
            <p>
                <strong>{{ $topProduct }}</strong> merupakan produk dengan penjualan tertinggi dengan total revenue
                <strong>Rp {{ number_format($topProductRevenue, 0, ',', '.') }}</strong>,
                berkontribusi sebesar <strong>{{ number_format(($topProductRevenue / $totalRevenue) * 100, 1) }}%</strong> dari total revenue.
                Fokus pemasaran dan stok pada produk ini perlu diprioritaskan.
            </p>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="insight-box">
            <h5 class="fw-bold"><i class="bi bi-folder-fill me-2 text-primary"></i> Kategori Dominan</h5>
            <p>
                Kategori <strong>{{ $topCategory }}</strong> mendominasi penjualan dengan revenue
                <strong>Rp {{ number_format($topCategoryRevenue, 0, ',', '.') }}</strong>,
                mencakup <strong>{{ number_format(($topCategoryRevenue / $totalRevenue) * 100, 1) }}%</strong> dari total revenue.
                Ekspansi produk dalam kategori ini dapat meningkatkan penjualan lebih lanjut.
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="insight-box">
            <h5 class="fw-bold"><i class="bi bi-graph-up-arrow me-2 text-success"></i> Performa Bisnis</h5>
            <p>
                Total revenue mencapai <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
                dengan rata-rata transaksi <strong>Rp {{ number_format($avgTransaction, 0, ',', '.') }}</strong>.
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="insight-box">
            <h5 class="fw-bold"><i class="bi bi-box-seam me-2 text-info"></i> Diversifikasi Produk</h5>
            <p>
                Terdapat <strong>{{ $products }} produk</strong> dalam <strong>{{ $categories }} kategori</strong>.
                Diversifikasi yang baik membantu mengurangi risiko bisnis dan menjangkau pasar lebih luas.
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="insight-box">
            <h5 class="fw-bold"><i class="bi bi-star-fill me-2 text-danger"></i> Transaksi Tertinggi</h5>
            <p>
                Transaksi terbesar adalah <strong>{{ $highestTransaction->produk }}</strong> ({{ $highestTransaction->kategori }})
                senilai <strong>Rp {{ number_format($highestTransaction->total, 0, ',', '.') }}</strong>
                pada tanggal {{ $highestTransaction->tanggal->format('d/m/Y') }}.
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-check2-square me-2 text-success"></i> Data Cleaning Summary</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3">
                            <div class="fs-3 fw-bold text-primary">{{ number_format($sales->count()) }}</div>
                            <small class="text-muted">Data Bersih Tersimpan</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3">
                            <div class="fs-3 fw-bold text-success">{{ $products }}</div>
                            <small class="text-muted">Produk Unik</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3">
                            <div class="fs-3 fw-bold text-warning">{{ $categories }}</div>
                            <small class="text-muted">Kategori</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3">
                            <div class="fs-3 fw-bold text-info">{{ $sales->groupBy('tanggal')->count() }}</div>
                            <small class="text-muted">Hari Transaksi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
