# Dashboard Analitik Penjualan

### UTS Analitik dan Visual Data

---

**Muhamad Aditya Saputra**  
**411231139**

---

## Latar Belakang & Tujuan

---

### Masalah
- Data penjualan mentah tidak terstruktur
- NULL values, format tanggal tidak seragam
- Kesalahan penulisan, mismatch perhitungan

### Tujuan
1. Memahami struktur data
2. Membersihkan data (4 langkah pipeline)
3. Mentransformasi dengan agregasi SQL
4. Menganalisis untuk insight bisnis
5. Visualisasi dashboard interaktif

---

## Tech Stack

---

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.2) |
| Database | MySQL |
| Charting | Chart.js 4.5 |
| UI Framework | Bootstrap 5.3 |
| Excel | Maatwebsite/Excel |
| PDF | Barryvdh/laravel-dompdf |
| Build | Vite 5 |

---

## Arsitektur Sistem

---

```
Browser (Chart.js + Bootstrap 5)
          │
     Route (web.php)
          │
  ┌───────┴──────────┐
  │   Controllers    │
  │ Dashboard | Import | Export
  └───────┬──────────┘
          │
  ┌───────┴──────────┐
  │  Service Layer   │
  │ AnalyticsService │
  │ DataCleaningService
  └───────┬──────────┘
          │
     SalesRepository
          │
     Model (Sales)
          │
     MySQL Database
```

---

## Database Design

---

### Tabel `sales`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Auto Increment |
| tanggal | DATE | Indexed |
| produk | VARCHAR(255) | Indexed |
| kategori | VARCHAR(255) | Indexed |
| jumlah | INTEGER | CHECK > 0 |
| harga | DECIMAL(15,2) | CHECK >= 0 |
| total | DECIMAL(15,2) | CHECK = jumlah × harga |

### Constraints
- Semua kolom data **NOT NULL**
- `CHECK (jumlah > 0)`
- `CHECK (harga >= 0)`
- `CHECK (total = jumlah * harga)`

---

## Fase 1: Data Understanding

---

### Dataset: `Data Penjualan.xlsx`

| Kolom | Tipe | Makna |
|-------|------|-------|
| **Tanggal** | Date | Tanggal transaksi |
| **Produk** | Text | Nama produk |
| **Kategori** | Text | Kelompok produk |
| **Jumlah** | Numeric | Kuantitas terjual |
| **Harga** | Numeric | Harga satuan |
| **Total** | Numeric | Jumlah × Harga |

> Data mentah → dibersihkan → disimpan ke MySQL

---

## Fase 2: Data Cleaning (1)

---

### Step 1: Handle NULL Values

```sql
-- Hapus baris tanpa Produk/Kategori
DELETE FROM sales
WHERE produk IS NULL OR TRIM(produk) = ''
   OR UPPER(TRIM(produk)) = 'NULL';

-- Isi default untuk Jumlah & Harga
UPDATE sales SET jumlah = 1 WHERE jumlah IS NULL;
UPDATE sales SET harga = 0 WHERE harga IS NULL;

-- Hitung ulang Total
UPDATE sales SET total = jumlah * harga
WHERE total IS NULL;
```

---

### Step 2: Fix Invalid Dates

```sql
-- Fallback ke tanggal valid pertama
SET @fallback_date = (
    SELECT tanggal FROM sales
    WHERE tanggal IS NOT NULL
      AND STR_TO_DATE(tanggal, '%Y-%m-%d') IS NOT NULL
    LIMIT 1
);

-- Ganti tanggal kosong / 'not_a_date'
UPDATE sales
SET tanggal = COALESCE(@fallback_date, '2024-01-01')
WHERE tanggal IS NULL
   OR LOWER(TRIM(tanggal)) = 'not_a_date';

-- Normalisasi format ke Y-m-d
UPDATE sales
SET tanggal = STR_TO_DATE(tanggal, '%m/%d/%Y')
WHERE tanggal REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$';
```

---

## Fase 2: Data Cleaning (2)

---

### Step 3: Standardize Text

```sql
-- Kapitalisasi Produk
UPDATE sales
SET produk = CONCAT(
    UPPER(SUBSTRING(TRIM(produk), 1, 1)),
    LOWER(SUBSTRING(TRIM(produk), 2))
);

-- Kapitalisasi Kategori
UPDATE sales
SET kategori = CONCAT(
    UPPER(SUBSTRING(TRIM(kategori), 1, 1)),
    LOWER(SUBSTRING(TRIM(kategori), 2))
);
```

> `"laptop"` → `"Laptop"` | `" ELEKTRONIK "` → `"Elektronik"`

---

### Step 4: Recalculate Totals

```sql
UPDATE sales
SET total = jumlah * harga
WHERE ABS(total - (jumlah * harga)) > 0.01;
```

> Toleransi 0.01 untuk floating-point

---

## Fase 3: Data Transformation

---

### KPI Metrics

```sql
SELECT
    SUM(total)               AS total_revenue,
    COUNT(*)                 AS total_transactions,
    AVG(total)               AS average_transaction,
    COUNT(DISTINCT produk)   AS total_products,
    COUNT(DISTINCT kategori) AS total_categories
FROM sales;
```

---

### Agregasi Per Produk & Kategori

```sql
-- Per Produk
SELECT produk,
       SUM(total) AS total_penjualan,
       COUNT(*)   AS jumlah_transaksi,
       AVG(total) AS rata_rata
FROM sales
GROUP BY produk
ORDER BY total_penjualan DESC;
```

```sql
-- Per Kategori
SELECT kategori,
       SUM(total) AS total_penjualan,
       COUNT(*)   AS jumlah_transaksi,
       AVG(total) AS rata_rata
FROM sales
GROUP BY kategori
ORDER BY total_penjualan DESC;
```

---

## Fase 3: Data Transformation (2)

---

### Tren Penjualan Harian

```sql
SELECT DATE(tanggal) AS tanggal,
       SUM(total)    AS total_penjualan,
       COUNT(*)      AS jumlah_transaksi
FROM sales
GROUP BY DATE(tanggal)
ORDER BY DATE(tanggal);
```

---

### Per Kategori Per Bulan

```sql
SELECT kategori,
       YEAR(tanggal)  AS tahun,
       MONTH(tanggal) AS bulan,
       SUM(total)     AS total_penjualan,
       COUNT(*)       AS jumlah_transaksi
FROM sales
GROUP BY kategori, YEAR(tanggal), MONTH(tanggal)
ORDER BY tahun, bulan, kategori;
```

---

### Per Produk Per Minggu

```sql
SELECT produk,
       YEAR(tanggal) AS tahun,
       WEEK(tanggal) AS minggu,
       SUM(total)    AS total_penjualan,
       COUNT(*)      AS jumlah_transaksi
FROM sales
GROUP BY produk, YEAR(tanggal), WEEK(tanggal)
ORDER BY tahun, minggu, produk;
```

---

## Fase 4: Data Analysis (1)

---

### Top 10 Produk + Kontribusi %

```sql
SELECT produk,
       SUM(total) AS total_penjualan,
       COUNT(*)   AS jumlah_transaksi,
       AVG(total) AS rata_rata,
       ROUND(SUM(total) /
           (SELECT SUM(total) FROM sales) * 100, 1
       ) AS kontribusi_persen
FROM sales
GROUP BY produk
ORDER BY total_penjualan DESC
LIMIT 10;
```

---

### Ranking Kategori + Kontribusi %

```sql
SELECT kategori,
       SUM(total) AS total_penjualan,
       COUNT(*)   AS jumlah_transaksi,
       ROUND(SUM(total) /
           (SELECT SUM(total) FROM sales) * 100, 1
       ) AS kontribusi_persen
FROM sales
GROUP BY kategori
ORDER BY total_penjualan DESC;
```

---

## Fase 4: Data Analysis (2)

---

### Penjualan Per Produk Per Minggu (lengkap)

```sql
SELECT produk,
       YEAR(tanggal)  AS tahun,
       WEEK(tanggal)  AS minggu,
       CONCAT(YEAR(tanggal), '-W',
              LPAD(WEEK(tanggal), 2, '0')) AS periode,
       SUM(total)     AS total_penjualan,
       COUNT(*)       AS jumlah_transaksi,
       AVG(total)     AS rata_rata
FROM sales
GROUP BY produk, YEAR(tanggal), WEEK(tanggal)
ORDER BY tahun, minggu, total_penjualan DESC;
```

---

### Penjualan Per Kategori Per Bulan (lengkap)

```sql
SELECT kategori,
       YEAR(tanggal)  AS tahun,
       MONTH(tanggal) AS bulan,
       CONCAT(YEAR(tanggal), '-',
              LPAD(MONTH(tanggal), 2, '0')) AS periode,
       SUM(total)     AS total_penjualan,
       COUNT(*)       AS jumlah_transaksi
FROM sales
GROUP BY kategori, YEAR(tanggal), MONTH(tanggal)
ORDER BY tahun, bulan, kategori;
```

---

## Fase 4: Data Analysis (3)

---

### Tren Harian + Nama Hari

```sql
SELECT DATE(tanggal)    AS tanggal,
       DAYNAME(tanggal) AS hari,
       SUM(total)       AS total_penjualan,
       COUNT(*)         AS jumlah_transaksi,
       AVG(total)       AS rata_rata_transaksi
FROM sales
GROUP BY DATE(tanggal), DAYNAME(tanggal)
ORDER BY DATE(tanggal);
```

---

### Distribusi Revenue Per Kategori

```sql
SELECT kategori,
       SUM(total)   AS total_penjualan,
       ROUND(SUM(total) /
           (SELECT SUM(total) FROM sales) * 100, 1
       ) AS persentase
FROM sales
GROUP BY kategori
ORDER BY total_penjualan DESC;
```

---

## Insight Bisnis

---

### Produk Terlaris

```sql
SELECT produk, SUM(total) AS total_revenue,
       ROUND(SUM(total) /
           (SELECT SUM(total) FROM sales) * 100, 1
       ) AS kontribusi_persen, COUNT(*) AS jumlah_transaksi
FROM sales GROUP BY produk
ORDER BY total_revenue DESC LIMIT 1;
```

### Kategori Dominan

```sql
SELECT kategori, SUM(total) AS total_revenue,
       ROUND(SUM(total) /
           (SELECT SUM(total) FROM sales) * 100, 1
       ) AS kontribusi_persen, COUNT(*) AS jumlah_transaksi
FROM sales GROUP BY kategori
ORDER BY total_revenue DESC LIMIT 1;
```

### Transaksi Tertinggi

```sql
SELECT id, tanggal, produk, kategori, jumlah, harga, total
FROM sales ORDER BY total DESC LIMIT 1;
```

---

## Insight: Performa & Cleaning Summary

---

### Performa Bisnis

```sql
SELECT
    COUNT(*)                  AS total_transaksi,
    COUNT(DISTINCT produk)    AS total_produk_unik,
    COUNT(DISTINCT kategori)  AS total_kategori,
    COUNT(DISTINCT DATE(tanggal)) AS total_hari_transaksi,
    ROUND(SUM(total), 0)      AS total_revenue,
    ROUND(AVG(total), 0)      AS rata_rata_transaksi,
    MIN(tanggal)              AS transaksi_pertama,
    MAX(tanggal)              AS transaksi_terakhir
FROM sales;
```

---

### Data Cleaning Summary

```sql
SELECT
    COUNT(*)                  AS total_data_bersih,
    COUNT(DISTINCT produk)    AS total_produk,
    COUNT(DISTINCT kategori)  AS total_kategori,
    COUNT(DISTINCT DATE(tanggal)) AS total_hari_transaksi,
    ROUND(SUM(total), 0)      AS total_revenue
FROM sales;
```

---

## Fitur Dashboard

---

### 4 KPI Stat Cards
- **Total Revenue** — `SUM(total)` format Rupiah
- **Total Transaksi** — `COUNT(*)` jumlah data
- **Rata-rata Transaksi** — `AVG(total)`
- **Total Produk** — `COUNT(DISTINCT produk)`

### 4 Chart.js Visualizations
- **Line Chart** — Tren penjualan harian
- **Doughnut Chart** — Distribusi kategori
- **Bar Chart** — Total penjualan per produk
- **Grouped Bar Chart** — Per kategori per bulan

> Route: `GET /` → `DashboardController@index`

---

## Fitur Analysis & Insights

---

### Halaman Analysis (`/analysis`)
- Stacked Bar Chart — Penjualan per produk per minggu
- Tabel Top 10 Produk (revenue tertinggi)
- Tabel Top Kategori (revenue + kontribusi %)

### Halaman Insights (`/insights`)
- Produk Terlaris — #1 revenue
- Kategori Dominan — pangsa pasar terbesar
- Performa Bisnis — statistik lengkap
- Diversifikasi Produk — konsentrasi penjualan
- Transaksi Tertinggi — outlier detection
- Data Cleaning Summary — metrik data bersih

---

## Fitur Import Data

---

### Flow Import

```
Upload Excel (.xlsx / .xls / .csv)
          ↓
    Validasi File
          ↓
    Baca via PhpSpreadsheet
          ↓
  ┌───────────────────────┐
  │ Data Cleaning Pipeline │
  ├───────────────────────┤
  │ 1. Handle NULL        │
  │ 2. Fix Invalid Dates  │
  │ 3. Standardize Text   │
  │ 4. Recalculate Totals │
  └───────────────────────┘
          ↓
  Preview + Cleaning Log
          ↓
  Konfirmasi → Truncate + Insert
```

### File Kunci
- `ImportController` — 3 method (index, preview, confirm)
- `SalesImport` — Excel reader + cleaning
- `import.blade.php` — Upload + Preview UI

---

## Fitur Export

---

### Export Excel (3 jenis)

| Jenis | Data | Query |
|-------|------|-------|
| **All Data** | Semua baris | `Sales::orderBy('tanggal')` |
| **By Product** | Agregasi produk | `SUM GROUP BY produk` |
| **By Category** | Agregasi kategori | `SUM GROUP BY kategori` |

### Export PDF
- Library: `barryvdh/laravel-dompdf`
- Template: `exports/sales-pdf.blade.php`
- Output: Laporan profesional

### Route
| Method | URI | Output |
|--------|-----|--------|
| GET | `/export/excel` | Excel all data |
| GET | `/export/product-excel` | Excel by product |
| GET | `/export/category-excel` | Excel by category |
| GET | `/export/pdf` | PDF report |

---

## Struktur Project (Kunci)

---

```
app/
├── Controllers/
│   ├── DashboardController.php   # Dashboard, Analysis, Insights
│   ├── ExportController.php      # Excel + PDF export
│   └── ImportController.php      # Upload + cleaning + import
├── Services/
│   ├── AnalyticsService.php      # Query → Chart.js format
│   └── DataCleaningService.php   # 4-step cleaning pipeline
├── Repositories/
│   └── SalesRepository.php       # Semua query agregasi SQL
├── Models/
│   └── Sales.php                 # Eloquent model
├── Imports/
│   └── SalesImport.php           # Excel reader + cleaning
└── Exports/
    ├── SalesExport.php
    ├── SalesByProductExport.php
    └── SalesByCategoryExport.php

database/queries/
└── 411231139_muhamad_aditya_saputra.sql  # Semua SQL query
```

---

## Demo Langkah

---

1. `php artisan serve` → `http://127.0.0.1:8000`
2. **Dashboard** — KPI cards + 4 chart
3. **Analysis** — Weekly chart + ranking tabel
4. **Insights** — Insight bisnis naratif
5. **Import** — Upload Excel → Preview → Konfirmasi
6. **Export** — Download Excel / PDF
7. **SQL** — Jalankan di DBeaver/CLI MySQL

---

## Kesimpulan

---

- Siklus analisis data **lengkap** terimplementasi:
  Data Understanding → Cleaning → Transformation → Analysis

- **Data Cleaning Pipeline** 4 langkah otomatis memastikan data siap analisis

- **Arsitektur berlapis** (Controller → Service → Repository → Model) — clean & maintainable

- **Query SQL** menjadi tulang punggung: SUM, COUNT, AVG, GROUP BY, YEAR, MONTH, WEEK, DAYNAME

- **11 query analisis** menghasilkan insight bisnis yang actionable

- **Dashboard interaktif** dengan Chart.js memudahkan visualisasi data

- **Import + Export** melengkapi dashboard sebagai tool analisis praktis

---

## Q & A

---

### Terima Kasih

**Muhamad Aditya Saputra — 411231139**

GitHub: `adiityaastr/uts-analitik-dan-visual-data`
