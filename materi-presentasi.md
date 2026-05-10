# MATERI PRESENTASI (Speaker Notes)

## Dashboard Analitik Penjualan
**Muhamad Aditya Saputra — 411231139**  
**UTS Analitik dan Visual Data**

---

## Slide 1: Cover

> **Yang disampaikan:**  
> Selamat pagi/siang, perkenalkan saya Muhamad Aditya Saputra dengan NIM 411231139. Hari ini saya akan mempresentasikan project UTS mata kuliah Analitik dan Visual Data, yaitu **Dashboard Analitik Penjualan**. Project ini adalah sebuah aplikasi web yang menerapkan seluruh siklus analisis data — mulai dari memahami data mentah, membersihkannya, mentransformasi, hingga menyajikan hasil analisis dalam bentuk dashboard interaktif.

---

## Slide 2: Latar Belakang & Tujuan

> **Yang disampaikan:**  
> Dalam dunia bisnis, data penjualan seringkali masih mentah dan tidak terstruktur. Masalah yang sering muncul:
> - Data kosong (NULL) yang mengganggu perhitungan
> - Format tanggal tidak seragam
> - Kesalahan penulisan nama produk dan kategori
> - Nilai total yang tidak sesuai dengan jumlah × harga
>
> Maka project ini bertujuan untuk:
> 1. **Memahami struktur data penjualan** yang tersedia
> 2. **Membersihkan data** secara otomatis melalui pipeline 4 langkah
> 3. **Mentransformasi data** dengan agregasi SQL (SUM, COUNT, AVG, GROUP BY)
> 4. **Menganalisis data** untuk menghasilkan insight bisnis
> 5. **Memvisualisasikan hasil analisis** dalam dashboard web interaktif

---

## Slide 3: Tech Stack

> **Yang disampaikan:**  
> Teknologi yang digunakan dalam project ini:
> - **Laravel 11** (PHP 8.2) — framework backend dengan arsitektur MVC
> - **MySQL** — database relasional untuk menyimpan data penjualan
> - **Chart.js 4.5** — library JavaScript untuk visualisasi chart (Line, Bar, Doughnut/Pie)
> - **Bootstrap 5.3** — CSS framework untuk UI yang responsif dan profesional
> - **Maatwebsite/Excel** — package Laravel untuk import/export file Excel
> - **Barryvdh/laravel-dompdf** — package untuk generate laporan PDF
> - **Vite 5** — build tool untuk kompilasi asset frontend
>
> MySQL digunakan sebagai database wajib (dicek di `AppServiceProvider`), bukan SQLite.

---

## Slide 4: Arsitektur Sistem

> **Yang disampaikan:**  
> Project ini menggunakan arsitektur berlapis (layered architecture) di atas Laravel:
>
> ```
> Browser (Chart.js + Bootstrap)
>           ↓
>      Route (web.php)
>           ↓
>      Controller (DashboardController, ImportController, ExportController)
>           ↓
>      Service Layer
>      ├── AnalyticsService      → Mengubah data query jadi format chart
>      └── DataCleaningService   → Pipeline 4 langkah pembersihan data
>           ↓
>      Repository Layer
>      └── SalesRepository       → Semua query agregasi SQL
>           ↓
>      Model (Sales)             → Eloquent ORM ke tabel 'sales'
>           ↓
>      MySQL Database
> ```
>
> **Keunggulan arsitektur ini:**
> - Pemisahan tanggung jawab (separation of concerns) — Repository khusus query, Service khusus logika bisnis
> - Query SQL tidak bercampur dengan logika controller
> - Mudah di-maintenance dan di-test
>
> **Detail file kunci:**
> - `app/Repositories/SalesRepository.php` — 8 method query agregasi
> - `app/Services/AnalyticsService.php` — 5 method untuk format data chart
> - `app/Services/DataCleaningService.php` — 4 method pipeline cleaning
> - `app/Imports/SalesImport.php` — membaca Excel + cleaning otomatis

---

## Slide 5: Database Design

> **Yang disampaikan:**  
> Dataset disimpan dalam satu tabel MySQL bernama `sales` dengan struktur:
>
> | Kolom | Tipe | Keterangan |
> |-------|------|------------|
> | id | BIGINT (PK, AI) | Primary key |
> | tanggal | DATE | Tanggal transaksi (di-index) |
> | produk | VARCHAR(255) | Nama produk (di-index) |
> | kategori | VARCHAR(255) | Kategori produk (di-index) |
> | jumlah | INTEGER | Jumlah barang terjual (> 0) |
> | harga | DECIMAL(15,2) | Harga satuan (>= 0) |
> | total | DECIMAL(15,2) | Total = jumlah × harga |
> | created_at, updated_at | TIMESTAMP | Timestamp Laravel |
>
> **Constraints (dari migrasi ke-2):**
> - Semua kolom data (tanggal, produk, kategori, jumlah, harga, total) **NOT NULL**
> - **CHECK** `jumlah > 0`
> - **CHECK** `harga >= 0`
> - **CHECK** `total = jumlah * harga` — memastikan integritas data di level database
>
> **Indeks:** pada `tanggal`, `produk`, dan `kategori` untuk mempercepat query agregasi.
>
> **File migrasi:**
> - `database/migrations/..._create_sales_table.php`
> - `database/migrations/..._add_constraints_to_sales_table.php`

---

## Slide 6: Fase 1 — Data Understanding

> **Yang disampaikan:**  
> **Fase pertama** dari metodologi analisis data adalah memahami struktur data.
>
> Dataset berasal dari file Excel: **`Data Penjualan.xlsx`**
>
> **6 Kolom Dataset:**
> | Kolom | Tipe Data | Makna |
> |-------|-----------|-------|
> | Tanggal | Date | Tanggal transaksi penjualan |
> | Produk | Text | Nama produk yang terjual |
> | Kategori | Text | Kelompok/kategori produk |
> | Jumlah | Numeric | Kuantitas barang terjual |
> | Harga | Numeric | Harga satuan per produk |
> | Total | Numeric | Total nilai transaksi (jumlah × harga) |
>
> **Yang perlu diperhatikan:**
> - Data mentah mengandung masalah: NULL, format tanggal tidak konsisten, teks tidak seragam, mismatch total
> - Sebelum bisa dianalisis, data harus melalui tahap cleaning terlebih dahulu
> - Data akan disimpan ke tabel MySQL `sales` dengan 7 baris data awal (setelah proses import dan seed)

---

## Slide 7: Fase 2 — Data Cleaning (Step 1 & 2)

> **Yang disampaikan:**  
> **Fase kedua: Data Cleaning** — pipeline 4 langkah otomatis via `DataCleaningService`.
>
> **Step 1: Handle NULL Values**
>
> Menangani nilai NULL/kosong di semua kolom:
>
> ```sql
> -- Hapus baris dengan Produk NULL, kosong, atau string 'NULL'
> DELETE FROM sales
> WHERE produk IS NULL OR TRIM(produk) = '' OR UPPER(TRIM(produk)) = 'NULL';
>
> -- Hapus baris dengan Kategori NULL, kosong, atau string 'NULL'
> DELETE FROM sales
> WHERE kategori IS NULL OR TRIM(kategori) = '' OR UPPER(TRIM(kategori)) = 'NULL';
>
> -- Isi jumlah NULL dengan default 1
> UPDATE sales SET jumlah = 1 WHERE jumlah IS NULL;
>
> -- Isi harga NULL dengan default 0
> UPDATE sales SET harga = 0 WHERE harga IS NULL;
>
> -- Hitung ulang total jika NULL
> UPDATE sales SET total = jumlah * harga WHERE total IS NULL;
> ```
>
> **Kebijakan:** Baris tanpa nama produk/kategori **dihapus** (tidak bisa diidentifikasi), sedangkan jumlah & harga yang NULL **diisi nilai default**.
>
> **Step 2: Fix Invalid Dates**
>
> Memperbaiki format tanggal yang tidak valid:
>
> ```sql
> -- Cari tanggal valid pertama sebagai fallback
> SET @fallback_date = (
>     SELECT tanggal FROM sales
>     WHERE tanggal IS NOT NULL
>       AND STR_TO_DATE(tanggal, '%Y-%m-%d') IS NOT NULL
>     LIMIT 1
> );
>
> -- Update tanggal kosong / 'not_a_date' dengan fallback
> UPDATE sales
> SET tanggal = COALESCE(@fallback_date, '2024-01-01')
> WHERE tanggal IS NULL OR LOWER(TRIM(tanggal)) = 'not_a_date';
>
> -- Normalisasi format tanggal ke Y-m-d (dari m/d/Y dan d-m-Y)
> UPDATE sales
> SET tanggal = STR_TO_DATE(tanggal, '%m/%d/%Y')
> WHERE tanggal REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$';
> ```
>
> **Kebijakan:** Tanggal tidak valid diganti dengan tanggal valid pertama yang ditemukan. Jika tidak ada, fallback ke `2024-01-01`.

---

## Slide 8: Fase 2 — Data Cleaning (Step 3 & 4)

> **Yang disampaikan:**  
> **Step 3: Standardize Text**
>
> Menyeragamkan penulisan nama produk dan kategori — trim spasi + kapitalisasi huruf pertama:
>
> ```sql
> -- Standardize Produk
> UPDATE sales
> SET produk = CONCAT(
>     UPPER(SUBSTRING(TRIM(produk), 1, 1)),
>     LOWER(SUBSTRING(TRIM(produk), 2))
> );
>
> -- Standardize Kategori
> UPDATE sales
> SET kategori = CONCAT(
>     UPPER(SUBSTRING(TRIM(kategori), 1, 1)),
>     LOWER(SUBSTRING(TRIM(kategori), 2))
> );
> ```
>
> **Hasil:** Semua nama produk dan kategori menjadi format "Capitalized" — huruf pertama besar, sisanya kecil. Contoh: `"laptop"` → `"Laptop"`, `" ELEKTRONIK "` → `"Elektronik"`.
>
> **Step 4: Recalculate Totals**
>
> Memastikan nilai `total = jumlah × harga`:
>
> ```sql
> UPDATE sales
> SET total = jumlah * harga
> WHERE ABS(total - (jumlah * harga)) > 0.01;
> ```
>
> **Kebijakan:** Koreksi dilakukan jika selisih lebih dari 0.01 (toleransi floating-point). Menggunakan `ABS()` untuk menangani selisih positif maupun negatif.
>
> **Implementasi di Laravel:**
> Pipeline ini diterapkan di dua tempat:
> 1. Saat **import data** — `DataCleaningService` dipanggil oleh `SalesImport` sebelum data masuk database
> 2. Saat **seed database** — `SalesSeeder` juga membaca Excel lalu membersihkan via `DataCleaningService`
>
> Cleaning log disimpan dan bisa dilihat di halaman Import → Preview.

---

## Slide 9: Fase 3 — Data Transformation

> **Yang disampaikan:**  
> **Fase ketiga: Data Transformation** — mengolah data menggunakan agregasi SQL.
>
> **6 jenis agregasi yang digunakan:**
>
> **1. KPI Metrics Ringkasan** (`SalesRepository::getKPIMetrics`):
> ```sql
> SELECT
>     SUM(total)                    AS total_revenue,
>     COUNT(*)                      AS total_transactions,
>     AVG(total)                    AS average_transaction,
>     COUNT(DISTINCT produk)        AS total_products,
>     COUNT(DISTINCT kategori)      AS total_categories
> FROM sales;
> ```
> Hasil: 5 metrik utama untuk KPI cards di dashboard.
>
> **2. Total Penjualan Per Produk** (`SalesRepository::getTotalByProduct`):
> ```sql
> SELECT produk,
>        SUM(total)   AS total_penjualan,
>        COUNT(*)     AS jumlah_transaksi,
>        AVG(total)   AS rata_rata
> FROM sales
> GROUP BY produk
> ORDER BY total_penjualan DESC;
> ```
> Hasil: ranking produk berdasarkan revenue tertinggi.
>
> **3. Total Penjualan Per Kategori** (`SalesRepository::getCategoryDistribution`):
> ```sql
> SELECT kategori,
>        SUM(total)   AS total_penjualan,
>        COUNT(*)     AS jumlah_transaksi,
>        AVG(total)   AS rata_rata
> FROM sales
> GROUP BY kategori
> ORDER BY total_penjualan DESC;
> ```
> Hasil: distribusi revenue per kategori untuk Doughnut chart.

---

## Slide 10: Fase 3 — Data Transformation (lanjutan)

> **Yang disampaikan:**  
> **4. Tren Penjualan Harian** (`SalesRepository::getDailySalesTrend`):
> ```sql
> SELECT DATE(tanggal)  AS tanggal,
>        SUM(total)     AS total_penjualan,
>        COUNT(*)       AS jumlah_transaksi
> FROM sales
> GROUP BY DATE(tanggal)
> ORDER BY DATE(tanggal);
> ```
> Hasil: data time-series untuk Line Chart di dashboard.
>
> **5. Penjualan Per Kategori Per Bulan** (`SalesRepository::getSalesByCategoryPerMonth`):
> ```sql
> SELECT kategori,
>        YEAR(tanggal)  AS tahun,
>        MONTH(tanggal) AS bulan,
>        SUM(total)     AS total_penjualan,
>        COUNT(*)       AS jumlah_transaksi
> FROM sales
> GROUP BY kategori, YEAR(tanggal), MONTH(tanggal)
> ORDER BY tahun, bulan, kategori;
> ```
> Hasil: data untuk Grouped Bar Chart — perbandingan kategori setiap bulan.
>
> **6. Penjualan Per Produk Per Minggu** (`SalesRepository::getSalesByWeek`):
> ```sql
> SELECT produk,
>        YEAR(tanggal)  AS tahun,
>        WEEK(tanggal)  AS minggu,
>        SUM(total)     AS total_penjualan,
>        COUNT(*)       AS jumlah_transaksi
> FROM sales
> GROUP BY produk, YEAR(tanggal), WEEK(tanggal)
> ORDER BY tahun, minggu, produk;
> ```
> Hasil: data untuk Stacked Bar Chart di halaman Analysis.
>
> **Implementasi Repository:**
> Semua query di atas diimplementasikan di `app/Repositories/SalesRepository.php` menggunakan Eloquent Query Builder (`DB::raw()`). Hasil query kemudian diolah oleh `AnalyticsService` menjadi format yang kompatibel dengan Chart.js (labels + values).

---

## Slide 11: Fase 4 — Data Analysis (Top Produk & Kategori)

> **Yang disampaikan:**  
> **Fase keempat: Data Analysis** — menghasilkan insight bisnis dari data yang sudah bersih.
>
> **Top 10 Produk Berdasarkan Revenue**:
> ```sql
> SELECT produk,
>        SUM(total)    AS total_penjualan,
>        COUNT(*)      AS jumlah_transaksi,
>        AVG(total)    AS rata_rata,
>        ROUND(SUM(total) / (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen
> FROM sales
> GROUP BY produk
> ORDER BY total_penjualan DESC
> LIMIT 10;
> ```
>
> **Ranking Kategori Berdasarkan Revenue**:
> ```sql
> SELECT kategori,
>        SUM(total)    AS total_penjualan,
>        COUNT(*)      AS jumlah_transaksi,
>        ROUND(SUM(total) / (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen
> FROM sales
> GROUP BY kategori
> ORDER BY total_penjualan DESC;
> ```
>
> **Distribusi Revenue Per Kategori (untuk Doughnut chart)**:
> ```sql
> SELECT kategori,
>        SUM(total)                                                     AS total_penjualan,
>        ROUND(SUM(total) / (SELECT SUM(total) FROM sales) * 100, 1)   AS persentase
> FROM sales
> GROUP BY kategori
> ORDER BY total_penjualan DESC;
> ```
>
> **Poin penting:** Setiap query menghitung kontribusi persentase terhadap total revenue menggunakan subquery `SELECT SUM(total) FROM sales` — sehingga kita tahu produk/kategori mana yang paling dominan.

---

## Slide 12: Fase 4 — Data Analysis (Time-Based & Insights)

> **Yang disampaikan:**  
> **Penjualan Per Produk Per Minggu (lengkap dengan periode)**:
> ```sql
> SELECT produk,
>        YEAR(tanggal)                AS tahun,
>        WEEK(tanggal)                AS minggu,
>        CONCAT(YEAR(tanggal), '-W', LPAD(WEEK(tanggal), 2, '0')) AS periode,
>        SUM(total)                   AS total_penjualan,
>        COUNT(*)                     AS jumlah_transaksi,
>        AVG(total)                   AS rata_rata
> FROM sales
> GROUP BY produk, YEAR(tanggal), WEEK(tanggal)
> ORDER BY tahun, minggu, total_penjualan DESC;
> ```
> **Fungsi:** `WEEK()` menghasilkan nomor minggu, `LPAD()` membuat format "2024-W01", `CONCAT()` menggabungkan tahun dan minggu jadi label periode.
>
> **Penjualan Per Kategori Per Bulan (lengkap dengan periode)**:
> ```sql
> SELECT kategori,
>        YEAR(tanggal)                AS tahun,
>        MONTH(tanggal)               AS bulan,
>        CONCAT(YEAR(tanggal), '-', LPAD(MONTH(tanggal), 2, '0')) AS periode,
>        SUM(total)                   AS total_penjualan,
>        COUNT(*)                     AS jumlah_transaksi
> FROM sales
> GROUP BY kategori, YEAR(tanggal), MONTH(tanggal)
> ORDER BY tahun, bulan, kategori;
> ```
>
> **Tren Penjualan Harian + Rata-rata**:
> ```sql
> SELECT DATE(tanggal)    AS tanggal,
>        DAYNAME(tanggal) AS hari,
>        SUM(total)       AS total_penjualan,
>        COUNT(*)         AS jumlah_transaksi,
>        AVG(total)       AS rata_rata_transaksi
> FROM sales
> GROUP BY DATE(tanggal), DAYNAME(tanggal)
> ORDER BY DATE(tanggal);
> ```
> **Fungsi:** `DAYNAME()` menampilkan nama hari (Senin, Selasa, dll) untuk analisis pola harian.

---

## Slide 13: Fase 4 — Insight Bisnis

> **Yang disampaikan:**  
> **Insight yang dihasilkan dari query analisis:**
>
> **Produk Terlaris:**
> ```sql
> SELECT produk                          AS produk_terlaris,
>        SUM(total)                      AS total_revenue,
>        ROUND(SUM(total) /
>             (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen,
>        COUNT(*)                        AS jumlah_transaksi
> FROM sales
> GROUP BY produk
> ORDER BY total_revenue DESC
> LIMIT 1;
> ```
> → Menampilkan satu produk dengan revenue tertinggi dan kontribusinya.
>
> **Kategori Dominan:**
> ```sql
> SELECT kategori                        AS kategori_dominan,
>        SUM(total)                      AS total_revenue,
>        ROUND(SUM(total) /
>             (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen,
>        COUNT(*)                        AS jumlah_transaksi
> FROM sales
> GROUP BY kategori
> ORDER BY total_revenue DESC
> LIMIT 1;
> ```
> → Menampilkan kategori dengan pangsa pasar terbesar.
>
> **Transaksi Tertinggi:**
> ```sql
> SELECT id, tanggal, produk, kategori, jumlah, harga, total
> FROM sales
> ORDER BY total DESC
> LIMIT 1;
> ```
> → Satu transaksi dengan nilai tertinggi — berguna untuk analisis outlier.

---

## Slide 14: Fase 4 — Performa & Cleaning Summary

> **Yang disampaikan:**  
> **Performa Bisnis Summary:**
> ```sql
> SELECT
>     COUNT(*)                                     AS total_transaksi,
>     COUNT(DISTINCT produk)                       AS total_produk_unik,
>     COUNT(DISTINCT kategori)                     AS total_kategori,
>     COUNT(DISTINCT DATE(tanggal))                AS total_hari_transaksi,
>     ROUND(SUM(total), 0)                         AS total_revenue,
>     ROUND(AVG(total), 0)                         AS rata_rata_transaksi,
>     MIN(tanggal)                                 AS transaksi_pertama,
>     MAX(tanggal)                                 AS transaksi_terakhir
> FROM sales;
> ```
> → Ringkasan lengkap: total transaksi, produk unik, kategori, hari aktif, revenue, rata-rata, periode data.
>
> **Data Cleaning Summary:**
> ```sql
> SELECT
>     COUNT(*)                                     AS total_data_bersih,
>     COUNT(DISTINCT produk)                       AS total_produk,
>     COUNT(DISTINCT kategori)                     AS total_kategori,
>     COUNT(DISTINCT DATE(tanggal))                AS total_hari_transaksi,
>     ROUND(SUM(total), 0)                         AS total_revenue
> FROM sales;
> ```
> → Ringkasan setelah cleaning: berapa banyak data bersih yang siap dianalisis.
>
> **Verifikasi cleaning (NULL check):**
> ```sql
> SELECT 'NULL Check' AS step,
>        COUNT(*) AS total_rows,
>        SUM(CASE WHEN produk IS NULL OR TRIM(produk) = '' THEN 1 ELSE 0 END) AS null_produk,
>        SUM(CASE WHEN kategori IS NULL OR TRIM(kategori) = '' THEN 1 ELSE 0 END) AS null_kategori,
>        SUM(CASE WHEN tanggal IS NULL THEN 1 ELSE 0 END) AS null_tanggal
> FROM sales;
> ```
> → Memastikan tidak ada NULL tersisa setelah cleaning.

---

## Slide 15: Fitur Dashboard

> **Yang disampaikan:**  
> **Halaman Dashboard** adalah halaman utama (`/`) yang menampilkan:
>
> **4 KPI Stat Cards** (sumber data: `getKPIMetrics()`):
> - **Total Revenue** — `SUM(total)` dari seluruh transaksi, format Rupiah
> - **Total Transaksi** — `COUNT(*)` jumlah seluruh baris di tabel sales
> - **Rata-rata Transaksi** — `AVG(total)` nilai rata-rata per transaksi
> - **Total Produk** — `COUNT(DISTINCT produk)` jumlah produk unik + jumlah kategori
>
> **4 Chart.js Visualizations** (sumber data: `AnalyticsService`):
> 1. **Line Chart** — Tren penjualan harian (dari `getTrendChartData()`)
> 2. **Doughnut Chart** — Distribusi revenue per kategori (dari `getCategoryDistributionChartData()`)
> 3. **Bar Chart** — Total penjualan per produk (dari `getProductChartData()`)
> 4. **Grouped Bar Chart** — Penjualan per kategori per bulan (dari `getCategoryMonthlyChartData()`)
>
> **Teknis:** Data dikirim dari controller sebagai JSON (via `json_encode()` Blade directive) dan langsung digunakan oleh Chart.js di client side. Semua chart responsif, memiliki tooltip format Rupiah, dan tampilan profesional.

---

## Slide 16: Fitur Analysis & Insights

> **Yang disampaikan:**  
> **Halaman Analysis** (`/analysis`):
> - **Stacked Bar Chart** — Penjualan per produk per minggu. Menampilkan bagaimana performa setiap produk dari minggu ke minggu. Data dari `AnalyticsService::getWeeklySalesByProduct()`.
> - **Tabel Top Produk** — 10 produk dengan penjualan tertinggi, lengkap dengan jumlah transaksi dan rata-rata.
> - **Tabel Top Kategori** — Kategori diurutkan berdasarkan revenue, dengan kontribusi persentase.
>
> **Halaman Insights** (`/insights`):
> - **5 Insight Cards** naratif:
>   1. **Produk Terlaris** — nama produk + revenue + kontribusi %
>   2. **Kategori Dominan** — nama kategori + revenue + kontribusi %
>   3. **Performa Bisnis** — total transaksi, produk, kategori, hari aktif, periode
>   4. **Diversifikasi Produk** — jumlah produk unik vs total transaksi (analisis konsentrasi)
>   5. **Transaksi Tertinggi** — detail transaksi dengan nilai terbesar
> - **Data Cleaning Summary** — metrik data bersih setelah pipeline cleaning
>
> Semua data di halaman ini dihitung dengan query yang sama seperti di file SQL.

---

## Slide 17: Fitur Import Data

> **Yang disampaikan:**  
> **Halaman Import** (`/import`):
>
> **Flow Import Data:**
> ```
> Upload Excel (.xlsx/.xls/.csv)
>        ↓
> Validasi file (format, ukuran)
>        ↓
> Baca data via PhpSpreadsheet (Maatwebsite/Excel)
>        ↓
> Data Cleaning Pipeline (4 langkah via DataCleaningService)
>    ├── Handle NULL (hapus baris tanpa produk/kategori)
>    ├── Fix Invalid Dates (normalisasi format)
>    ├── Standardize Text (ucwords + trim)
>    └── Recalculate Totals (jumlah × harga)
>        ↓
> Preview Data Bersih (20 baris pertama)
>        ↓
> Tampilkan Cleaning Log (setiap perubahan dicatat)
>        ↓
> Konfirmasi Import → Truncate + Insert ke MySQL
> ```
>
> **File terkait:**
> - `app/Http/Controllers/ImportController.php` — 3 method: `index`, `preview`, `confirm`
> - `app/Imports/SalesImport.php` — membaca Excel + integrasi `DataCleaningService`
> - `resources/views/dashboard/import.blade.php` — tampilan dengan 2 state (upload & preview)
>
> **Poin penting:** Import selalu truncate data lama (replace), bukan append. Jadi setiap import adalah dataset baru.

---

## Slide 18: Fitur Export

> **Yang disampaikan:**  
> **Fitur Export** dapat diakses dari dropdown di topbar dan halaman Export.
>
> **Export Excel — 3 jenis:**
> | Jenis | File | Query |
> |-------|------|-------|
> | All Data | `SalesExport` | `Sales::orderBy('tanggal')->get()` |
> | By Product | `SalesByProductExport` | `SUM(total) GROUP BY produk` |
> | By Category | `SalesByCategoryExport` | `SUM(total) GROUP BY kategori` |
>
> **Export PDF** (`ExportController@exportPDF`):
> - Menggunakan **Barryvdh/laravel-dompdf**
> - Template Blade: `resources/views/exports/sales-pdf.blade.php`
> - Output: laporan profesional dengan header (judul, identitas, tanggal), tabel data lengkap (ID, Tanggal, Produk, Kategori, Jumlah, Harga, Total), footer
>
> **Route terkait:**
> - `GET /export/excel` — Excel all data
> - `GET /export/product-excel` — Excel by product
> - `GET /export/category-excel` — Excel by category
> - `GET /export/pdf` — PDF report

---

## Slide 19: Demo Live (Panduan Presentasi)

> **Yang disampaikan (saat live demo):**  
> Berikut langkah-langkah demo yang bisa ditunjukkan:
>
> 1. **Buka terminal**, jalankan:
>    ```bash
>    php artisan serve
>    ```
> 2. **Buka browser** ke `http://127.0.0.1:8000`
> 3. **Tunjukkan halaman Dashboard** — jelaskan KPI cards dan 4 chart yang muncul
> 4. **Klik Analysis** di sidebar — tunjukkan weekly chart dan tabel ranking
> 5. **Klik Insights** — baca insight bisnis yang dihasilkan
> 6. **Demo Import:**
>    - Klik Import di sidebar
>    - Upload file Excel baru
>    - Tunjukkan preview data bersih dan cleaning log
>    - Klik "Konfirmasi Import"
>    - Kembali ke Dashboard, tunjukkan data sudah berubah
> 7. **Demo Export:**
>    - Klik dropdown Export di topbar
>    - Download Excel (by product / by category)
>    - Download PDF, buka filenya
>
> **Tips teknis:**
> - Pastikan MySQL sudah running sebelum demo
> - Jika error muncul, cek `storage/logs/laravel.log`
> - Data awal berasal dari `Data Penjualan.xlsx` via `php artisan db:seed --class=SalesSeeder`
> - Query SQL mandiri bisa dijalankan langsung di DBeaver/MySQL CLI via file `database/queries/411231139_muhamad_aditya_saputra.sql`

---

## Slide 20: Kesimpulan

> **Yang disampaikan:**  
> **Kesimpulan dari project ini:**
>
> 1. **Siklus analisis data lengkap** berhasil diimplementasikan — dari data mentah hingga visualisasi interaktif:
>    - Data Understanding → Data Cleaning (4 langkah) → Data Transformation (6 agregasi) → Data Analysis (11 query insight)
>
> 2. **Data Cleaning Pipeline** adalah fondasi penting — tanpa data bersih, hasil analisis tidak bisa dipercaya. Pipeline 4 langkah menangani NULL, tanggal invalid, teks tidak seragam, dan mismatch perhitungan.
>
> 3. **Arsitektur berlapis** (Controller → Service → Repository → Model) memberikan pemisahan tanggung jawab yang jelas dan kode yang mudah di-maintain.
>
> 4. **Query SQL** menjadi tulang punggung analisis — semua agregasi (SUM, COUNT, AVG, GROUP BY dengan YEAR/MONTH/WEEK) ditulis baik dalam Eloquent Query Builder maupun SQL murni.
>
> 5. **Visualisasi interaktif** dengan Chart.js memudahkan stakeholder memahami data penjualan tanpa perlu melihat angka mentah.
>
> 6. **Fitur Import & Export** melengkapi dashboard menjadi tool yang praktis — bisa menerima data baru dan mengekspor hasil analisis ke Excel/PDF.

---

## Slide 21: Q&A

> **Yang disampaikan:**  
> Demikian presentasi saya tentang Dashboard Analitik Penjualan. Saya membuka sesi tanya jawab.
>
> **Pertanyaan yang mungkin muncul dan jawabannya:**
>
> **Q: Kenapa data cleaning dilakukan di dua tempat (SQL dan PHP)?**
> A: SQL queries di file `.sql` adalah untuk referensi dan bisa dijalankan langsung di database tanpa aplikasi Laravel. Sedangkan DataCleaningService di PHP adalah yang benar-benar berjalan saat import data via web. Keduanya menghasilkan output yang sama.
>
> **Q: Bagaimana cara mengganti dataset?**
> A: Upload file Excel baru melalui halaman Import. Data lama akan di-truncate dan diganti dengan data baru yang sudah dibersihkan.
>
> **Q: Apakah bisa pakai database selain MySQL?**
> A: Tidak. `AppServiceProvider` secara eksplisit mengecek dan melempar exception jika bukan MySQL. Ini karena query menggunakan fungsi MySQL-specific seperti `WEEK()`, `YEAR()`, `MONTH()`, `DAYNAME()`.
>
> **Q: Apakah data cleaning bisa dikustomisasi?**
> A: Bisa. Semua logika cleaning ada di `app/Services/DataCleaningService.php`. 4 method private bisa dimodifikasi sesuai kebutuhan (misal: ganti default jumlah dari 1 ke 0).
