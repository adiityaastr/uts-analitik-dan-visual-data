<?php

namespace Database\Seeders;

use App\Models\Sales;
use App\Services\DataCleaningService;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/public/Data Penjualan.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $header = array_shift($rows);
        $header = array_map('strtolower', $header);

        $data = [];
        foreach ($rows as $index => $row) {
            $data[] = array_combine($header, array_pad($row, count($header), null));
        }

        $this->command->info("Raw data loaded: " . count($data) . " rows");
        $this->command->info("Starting data cleaning...");

        $cleaner = new DataCleaningService();
        $cleanedData = $cleaner->clean($data);

        foreach ($cleaner->getCleaningLog() as $log) {
            $this->command->line("  - {$log}");
        }

        $this->command->info("Clean data: " . count($cleanedData) . " rows");

        Sales::truncate();

        $inserted = 0;
        foreach ($cleanedData as $row) {
            Sales::create([
                'tanggal' => $row['tanggal'],
                'produk' => $row['produk'],
                'kategori' => $row['kategori'],
                'jumlah' => (int) $row['jumlah'],
                'harga' => (float) $row['harga'],
                'total' => (float) $row['total'],
            ]);
            $inserted++;
        }

        $this->command->info("Data imported: {$inserted} records inserted.");
    }
}
