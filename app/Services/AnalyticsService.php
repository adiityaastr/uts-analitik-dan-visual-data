<?php

namespace App\Services;

use App\Repositories\SalesRepository;

class AnalyticsService
{
    public function __construct(private SalesRepository $repository)
    {
    }

    public function getDashboardData(): array
    {
        return [
            'kpi' => $this->repository->getKPIMetrics(),
            'trend' => $this->getTrendChartData(),
            'by_product' => $this->getProductChartData(),
            'by_category_monthly' => $this->getCategoryMonthlyChartData(),
            'category_distribution' => $this->getCategoryDistributionChartData(),
        ];
    }

    public function getTrendChartData(): array
    {
        $data = $this->repository->getDailySalesTrend();
        return [
            'labels' => $data->pluck('tanggal')->map(fn($d) => $d->format('j M'))->toArray(),
            'values' => $data->pluck('total_penjualan')->toArray(),
            'transactions' => $data->pluck('jumlah_transaksi')->toArray(),
        ];
    }

    public function getProductChartData(): array
    {
        $data = $this->repository->getTotalByProduct();
        return [
            'labels' => $data->pluck('produk')->toArray(),
            'values' => $data->pluck('total_penjualan')->toArray(),
            'transactions' => $data->pluck('jumlah_transaksi')->toArray(),
        ];
    }

    public function getCategoryMonthlyChartData(): array
    {
        $raw = $this->repository->getSalesByCategoryPerMonth();
        $categories = $raw->pluck('kategori')->unique()->values();
        $labels = $raw->map(fn($r) => $r->tahun . '-' . str_pad($r->bulan, 2, '0', STR_PAD_LEFT))->unique()->values();

        $datasets = [];
        foreach ($categories as $cat) {
            $catData = $raw->where('kategori', $cat);
            $dataset = [];
            foreach ($labels as $label) {
                [$tahun, $bulan] = explode('-', $label);
                $match = $catData->where('tahun', (int)$tahun)->where('bulan', (int)$bulan)->first();
                $dataset[] = $match ? $match->total_penjualan : 0;
            }
            $datasets[] = [
                'label' => $cat,
                'data' => $dataset,
            ];
        }

        return [
            'labels' => $labels->toArray(),
            'datasets' => $datasets,
        ];
    }

    public function getCategoryDistributionChartData(): array
    {
        $data = $this->repository->getCategoryDistribution();
        return [
            'labels' => $data->pluck('kategori')->toArray(),
            'values' => $data->pluck('total_penjualan')->toArray(),
        ];
    }

    public function getWeeklySalesByProduct(): array
    {
        $raw = $this->repository->getSalesByWeek();
        $produk = $raw->pluck('produk')->unique()->values();
        $weeks = $raw->map(fn($r) => $r->tahun . '-W' . str_pad($r->minggu, 2, '0', STR_PAD_LEFT))->unique()->values();

        $datasets = [];
        foreach ($produk as $p) {
            $pData = $raw->where('produk', $p);
            $dataset = [];
            foreach ($weeks as $week) {
                [$tahun, $mingguStr] = explode('-W', $week);
                $match = $pData->where('tahun', (int)$tahun)->where('minggu', (int)$mingguStr)->first();
                $dataset[] = $match ? $match->total_penjualan : 0;
            }
            $datasets[] = [
                'label' => $p,
                'data' => $dataset,
            ];
        }

        return [
            'labels' => $weeks->toArray(),
            'datasets' => $datasets,
        ];
    }
}
