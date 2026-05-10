<?php

namespace App\Imports;

use App\Models\Sales;
use App\Services\DataCleaningService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SalesImport implements ToCollection, WithHeadingRow
{
    private DataCleaningService $cleaner;
    private array $cleaningLog = [];
    private array $cleanedData = [];
    private int $originalCount = 0;

    public function __construct()
    {
        $this->cleaner = new DataCleaningService();
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows): void
    {
        $this->originalCount = $rows->count();
        $data = $rows->toArray();
        $cleaned = $this->cleaner->clean($data);
        $this->cleaningLog = $this->cleaner->getCleaningLog();
        $this->cleanedData = $cleaned;
    }

    public function importAndSave(): int
    {
        $inserted = 0;
        foreach ($this->cleanedData as $row) {
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
        return $inserted;
    }

    public function getOriginalCount(): int
    {
        return $this->originalCount;
    }

    public function getCleanedCount(): int
    {
        return count($this->cleanedData);
    }

    public function getCleaningLog(): array
    {
        return $this->cleaningLog;
    }

    public function getCleanedData(): array
    {
        return $this->cleanedData;
    }

    public static function previewFromFile(string $filePath): self
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $header = array_shift($rows);
        $header = array_map('strtolower', $header);

        $data = [];
        foreach ($rows as $row) {
            $data[] = array_combine($header, array_pad($row, count($header), null));
        }

        $instance = new self();
        $instance->originalCount = count($data);
        $cleaned = $instance->cleaner->clean($data);
        $instance->cleaningLog = $instance->cleaner->getCleaningLog();
        $instance->cleanedData = $cleaned;

        return $instance;
    }
}
