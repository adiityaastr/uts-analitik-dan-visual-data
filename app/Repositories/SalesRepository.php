<?php

namespace App\Repositories;

use App\Models\Sales;
use Illuminate\Support\Facades\DB;

class SalesRepository
{
    public function getTotalByProduct()
    {
        return Sales::select('produk', DB::raw('SUM(total) as total_penjualan'), DB::raw('COUNT(*) as jumlah_transaksi'))
            ->groupBy('produk')
            ->orderByDesc('total_penjualan')
            ->get();
    }

    public function getSalesByWeek()
    {
        return Sales::select(
                'produk',
                DB::raw('YEAR(tanggal) as tahun'),
                DB::raw('WEEK(tanggal) as minggu'),
                DB::raw('SUM(total) as total_penjualan'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->groupBy('produk', 'tahun', 'minggu')
            ->orderBy('tahun')
            ->orderBy('minggu')
            ->get();
    }

    public function getSalesByCategoryPerMonth()
    {
        return Sales::select(
                'kategori',
                DB::raw('YEAR(tanggal) as tahun'),
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(total) as total_penjualan'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->groupBy('kategori', 'tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();
    }

    public function getDailySalesTrend()
    {
        return Sales::select(
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('SUM(total) as total_penjualan'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
    }

    public function getKPIMetrics()
    {
        return [
            'total_revenue' => Sales::sum('total'),
            'total_transactions' => Sales::count(),
            'average_transaction' => Sales::avg('total'),
            'total_products' => Sales::distinct('produk')->count('produk'),
            'total_categories' => Sales::distinct('kategori')->count('kategori'),
            'top_product' => Sales::select('produk', DB::raw('SUM(total) as total'))
                ->groupBy('produk')
                ->orderByDesc('total')
                ->first(),
            'top_category' => Sales::select('kategori', DB::raw('SUM(total) as total'))
                ->groupBy('kategori')
                ->orderByDesc('total')
                ->first(),
        ];
    }

    public function getCategoryDistribution()
    {
        return Sales::select('kategori', DB::raw('SUM(total) as total_penjualan'))
            ->groupBy('kategori')
            ->orderByDesc('total_penjualan')
            ->get();
    }

    public function getAllSales()
    {
        return Sales::orderBy('tanggal')->get();
    }
}
