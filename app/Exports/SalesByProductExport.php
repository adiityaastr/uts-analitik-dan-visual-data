<?php

namespace App\Exports;

use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesByProductExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Sales::select('produk', DB::raw('SUM(total) as total_penjualan'), DB::raw('COUNT(*) as jumlah_transaksi'), DB::raw('AVG(total) as rata_rata'))
            ->groupBy('produk')
            ->orderByDesc('total_penjualan')
            ->get();
    }

    public function headings(): array
    {
        return ['Produk', 'Total Penjualan', 'Jumlah Transaksi', 'Rata-rata'];
    }
}
