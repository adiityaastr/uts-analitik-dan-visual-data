<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\SalesByProductExport;
use App\Exports\SalesByCategoryExport;
use App\Models\Sales;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new SalesExport, 'sales_data.xlsx');
    }

    public function exportProductExcel()
    {
        return Excel::download(new SalesByProductExport, 'sales_by_product.xlsx');
    }

    public function exportCategoryExcel()
    {
        return Excel::download(new SalesByCategoryExport, 'sales_by_category.xlsx');
    }

    public function exportPDF()
    {
        $sales = Sales::orderBy('tanggal')->get();
        $pdf = Pdf::loadView('exports.sales-pdf', compact('sales'));
        return $pdf->download('sales_report.pdf');
    }
}
