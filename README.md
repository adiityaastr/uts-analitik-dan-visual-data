# Dashboard Analitik Penjualan

**UTS Analitik dan Visual Data**
- **Nama:** Muhamad Aditya Saputra
- **NIM:** 411231139
- **Teknologi:** Laravel 11, MySQL, Chart.js, Bootstrap 5

---

## Fitur

| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | KPI cards, Line chart tren harian, Doughnut distribusi kategori, Bar chart per produk & per kategori per bulan |
| Analysis | Bar chart penjualan per produk per minggu, Tabel Top Produk & Top Kategori |
| Insights | Insight bisnis (produk terlaris, kategori dominan, performa, transaksi tertinggi), Data Cleaning Summary |
| Import Data | Upload Excel, data cleaning otomatis 4 langkah, preview cleaning log, konfirmasi import ke MySQL |
| Export Data | Export Excel (all data, by product, by category), Export PDF |

---

## Fase Analisis Data

### 1. Data Understanding
Dataset penjualan dari file `Data Penjualan.xlsx` dengan struktur 6 kolom: **Tanggal, Produk, Kategori, Jumlah, Harga, Total** — disimpan ke tabel MySQL `sales`.

### 2. Data Cleaning
Pipeline 4 langkah via `DataCleaningService` sebelum data masuk database:
- **Handle NULL** — hapus baris tanpa produk/kategori, isi default jumlah=1, harga=0, total dihitung ulang
- **Fix Invalid Dates** — normalisasi format tanggal, fallback ke tanggal valid pertama
- **Standardize Text** — capitalize & trim nama produk dan kategori
- **Recalculate Totals** — koreksi `total = jumlah × harga` jika mismatch

### 3. Data Transformation
Agregasi SQL: `SUM(total)`, `COUNT(*)`, `AVG(total)`, `COUNT(DISTINCT)` dengan `GROUP BY` produk, kategori, tanggal, minggu, bulan.

### 4. Data Analysis
Top produk/kategori berdasarkan revenue, tren penjualan harian, penjualan per produk per minggu, penjualan per kategori per bulan, distribusi revenue, insight bisnis.

---

## Instalasi

```bash
git clone https://github.com/adiityaastr/uts-analitik-dan-visual-data.git
cd uts-analitik-dan-visual-data/411231139_muhamad_aditya_saputra

# Install dependencies
composer install
npm install

# Konfigurasi database
cp .env.example .env
# Edit .env: DB_CONNECTION=mysql, DB_DATABASE=411231139_muhamad_aditya_saputra

# Migrasi & seed data
php artisan migrate
php artisan db:seed --class=SalesSeeder

# Build Vite assets
npm run build

# Jalankan server
php artisan serve
```

Akses di `http://127.0.0.1:8000`

---

## Struktur Project

```
app/
├── Controllers/
│   ├── DashboardController       # Halaman dashboard, analysis, insights
│   ├── ExportController          # Export Excel & PDF
│   └── ImportController          # Upload & import data dengan auto-cleaning
├── Exports/                      # 3 class export Excel (all, by product, by category)
├── Imports/
│   └── SalesImport.php           # Import Excel dengan integrasi DataCleaningService
├── Models/
│   └── Sales.php                 # Model dengan guard boot() + casting
├── Repositories/
│   └── SalesRepository.php       # Query agregasi SUM, COUNT, GROUP BY, YEAR, MONTH, WEEK
└── Services/
    ├── AnalyticsService.php      # Mengolah data repository menjadi format chart
    └── DataCleaningService.php   # Pipeline 4 langkah data cleaning

database/
├── migrations/
│   ├── ..._create_sales_table.php
│   └── ..._add_constraints_to_sales_table.php  # NOT NULL + CHECK constraints
├── queries/
│   └── 411231139_muhamad_aditya_saputra.sql    # Semua query SQL (cleaning + transformation + analysis)
└── seeders/
    └── SalesSeeder.php           # Import dari Excel + data cleaning + insert

resources/views/
├── dashboard/
│   ├── index.blade.php           # Main dashboard (KPI cards + 4 chart)
│   ├── analysis.blade.php        # Penjualan per produk per minggu + tabel ranking
│   ├── import.blade.php          # Upload form + preview data bersih + cleaning log
│   └── insights.blade.php        # Narrative insights + summary data cleaning
├── exports/
│   └── sales-pdf.blade.php       # Template PDF laporan penjualan
├── layouts/
│   └── app.blade.php             # Layout utama (sidebar, topbar, export dropdown)
└── partials/
    └── sidebar.blade.php         # Navigasi sidebar

routes/
└── web.php                       # Route: dashboard, analysis, insights, import, export
```

---

## Query SQL Lengkap

Semua query tersedia di `database/queries/411231139_muhamad_aditya_saputra.sql`:
- **Part 1:** Data Cleaning (DELETE/UPDATE untuk NULL, tanggal, teks, total)
- **Part 2:** Data Transformation (SUM, GROUP BY, COUNT, AVG)
- **Part 3:** Data Analysis (Top produk, tren mingguan/bulanan, insight)

Jalankan langsung di DBeaver / phpMyAdmin / MySQL CLI.

---

## License

MIT — Muhamad Aditya Saputra (411231139)
