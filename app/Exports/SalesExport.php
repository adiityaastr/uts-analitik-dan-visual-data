<?php

namespace App\Exports;

use App\Models\Sales;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Sales::orderBy('tanggal')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Tanggal', 'Produk', 'Kategori', 'Jumlah', 'Harga', 'Total'];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->tanggal->format('Y-m-d'),
            $sale->produk,
            $sale->kategori,
            $sale->jumlah,
            $sale->harga,
            $sale->total,
        ];
    }
}
