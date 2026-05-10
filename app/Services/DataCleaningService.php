<?php

namespace App\Services;

use Carbon\Carbon;

class DataCleaningService
{
    private array $cleaningLog = [];

    public function clean(array $data): array
    {
        $this->cleaningLog = [];
        $data = $this->handleNullValues($data);
        $data = $this->fixInvalidDates($data);
        $data = $this->standardizeText($data);
        $data = $this->recalculateTotals($data);
        return $data;
    }

    private function handleNullValues(array $data): array
    {
        foreach ($data as $index => &$row) {
            if (!isset($row['produk']) || empty($row['produk']) || strtoupper((string)$row['produk']) === 'NULL') {
                $this->cleaningLog[] = "Row {$index}: Produk NULL - removed";
                unset($data[$index]);
                continue;
            }
            if (!isset($row['kategori']) || empty($row['kategori']) || strtoupper((string)$row['kategori']) === 'NULL') {
                $this->cleaningLog[] = "Row {$index}: Kategori NULL - removed";
                unset($data[$index]);
                continue;
            }
            if (!isset($row['jumlah']) || $row['jumlah'] === null) {
                $this->cleaningLog[] = "Row {$index}: Jumlah NULL - set to 1";
                $row['jumlah'] = 1;
            }
            if (!isset($row['harga']) || $row['harga'] === null) {
                $this->cleaningLog[] = "Row {$index}: Harga NULL - set to 0";
                $row['harga'] = 0;
            }
            if (!isset($row['total']) || $row['total'] === null) {
                $this->cleaningLog[] = "Row {$index}: Total NULL - recalculated";
                $row['total'] = (int)$row['jumlah'] * (float)$row['harga'];
            }
        }
        return array_values($data);
    }

    private function fixInvalidDates(array $data): array
    {
        foreach ($data as $index => &$row) {
            $tanggal = $row['tanggal'] ?? '';
            if (empty($tanggal) || strtolower((string)$tanggal) === 'not_a_date') {
                if (!empty($data)) {
                    $validDates = array_filter(array_column($data, 'tanggal'), function ($d) {
                        try {
                            Carbon::parse($d);
                            return true;
                        } catch (\Exception $e) {
                            return false;
                        }
                    });
                    $fallbackDate = !empty($validDates) ? Carbon::parse(reset($validDates))->format('Y-m-d') : '2024-01-01';
                } else {
                    $fallbackDate = '2024-01-01';
                }
                $this->cleaningLog[] = "Row {$index}: Invalid date '{$tanggal}' - set to {$fallbackDate}";
                $row['tanggal'] = $fallbackDate;
            } else {
                try {
                    $row['tanggal'] = Carbon::parse($tanggal)->format('Y-m-d');
                } catch (\Exception $e) {
                    $row['tanggal'] = '2024-01-01';
                    $this->cleaningLog[] = "Row {$index}: Unparseable date - set to 2024-01-01";
                }
            }
        }
        return $data;
    }

    private function standardizeText(array $data): array
    {
        foreach ($data as $index => &$row) {
            $oldProduk = $row['produk'];
            $oldKategori = $row['kategori'];
            $row['produk'] = ucwords(trim((string)$row['produk']));
            $row['kategori'] = ucwords(trim((string)$row['kategori']));
            if ($oldProduk !== $row['produk']) {
                $this->cleaningLog[] = "Row {$index}: Produk standardized '{$oldProduk}' -> '{$row['produk']}'";
            }
            if ($oldKategori !== $row['kategori']) {
                $this->cleaningLog[] = "Row {$index}: Kategori standardized '{$oldKategori}' -> '{$row['kategori']}'";
            }
        }
        return $data;
    }

    private function recalculateTotals(array $data): array
    {
        foreach ($data as $index => &$row) {
            $expectedTotal = (int)$row['jumlah'] * (float)$row['harga'];
            $currentTotal = (float)$row['total'];
            if (abs($currentTotal - $expectedTotal) > 0.01) {
                $this->cleaningLog[] = "Row {$index}: Total mismatch - {$row['jumlah']} x {$row['harga']} = {$expectedTotal} (was {$currentTotal})";
                $row['total'] = $expectedTotal;
            }
        }
        return $data;
    }

    public function getCleaningLog(): array
    {
        return $this->cleaningLog;
    }
}
