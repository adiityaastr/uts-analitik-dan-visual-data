-- ============================================================
-- QUERY ANALISIS DATA PENJUALAN
-- 411231139 - Muhamad Aditya Saputra
-- UTS Analitik dan Visual Data
-- ============================================================
-- Bagian 1: Data Cleaning
-- Bagian 2: Data Transformation (Agregasi)
-- Bagian 3: Data Analysis
-- ============================================================

-- ============================================================
-- BAGIAN 1: DATA CLEANING
-- ============================================================
-- Membersihkan data dengan ketentuan:
-- 1. Menangani data NULL (hapus atau isi dengan nilai yang sesuai)
-- 2. Memperbaiki format tanggal yang tidak valid
-- 3. Menyeragamkan penulisan teks (produk & kategori)
-- 4. Memastikan nilai total sesuai (jumlah x harga)
-- ============================================================

-- ------------------------------------------------
-- STEP 1: Handle NULL Values
-- ------------------------------------------------

-- 1a. Hapus baris dengan Produk NULL/kosong/string 'NULL'
DELETE FROM sales
WHERE produk IS NULL
   OR TRIM(produk) = ''
   OR UPPER(TRIM(produk)) = 'NULL';

-- 1b. Hapus baris dengan Kategori NULL/kosong/string 'NULL'
DELETE FROM sales
WHERE kategori IS NULL
   OR TRIM(kategori) = ''
   OR UPPER(TRIM(kategori)) = 'NULL';

-- 1c. Isi jumlah NULL dengan default 1
UPDATE sales SET jumlah = 1 WHERE jumlah IS NULL;

-- 1d. Isi harga NULL dengan default 0
UPDATE sales SET harga = 0 WHERE harga IS NULL;

-- 1e. Hitung ulang total jika NULL
UPDATE sales
SET total = jumlah * harga
WHERE total IS NULL;

-- ------------------------------------------------
-- STEP 2: Fix Invalid Dates
-- ------------------------------------------------

-- 2a. Cari tanggal valid pertama sebagai fallback
SET @fallback_date = (
    SELECT tanggal FROM sales
    WHERE tanggal IS NOT NULL
      AND STR_TO_DATE(tanggal, '%Y-%m-%d') IS NOT NULL
    LIMIT 1
);

-- 2b. Update tanggal kosong / 'not_a_date' dengan fallback
UPDATE sales
SET tanggal = COALESCE(@fallback_date, '2024-01-01')
WHERE tanggal IS NULL
   OR LOWER(TRIM(tanggal)) = 'not_a_date';

-- 2c. Normalisasi format tanggal ke Y-m-d
UPDATE sales
SET tanggal = STR_TO_DATE(tanggal, '%m/%d/%Y')
WHERE tanggal REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$';

UPDATE sales
SET tanggal = STR_TO_DATE(tanggal, '%d-%m-%Y')
WHERE tanggal REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$';

-- ------------------------------------------------
-- STEP 3: Standardize Text (Produk & Kategori)
-- ------------------------------------------------

-- 3a. Trim whitespace + Capitalize huruf pertama
UPDATE sales
SET produk = CONCAT(
    UPPER(SUBSTRING(TRIM(produk), 1, 1)),
    LOWER(SUBSTRING(TRIM(produk), 2))
);

UPDATE sales
SET kategori = CONCAT(
    UPPER(SUBSTRING(TRIM(kategori), 1, 1)),
    LOWER(SUBSTRING(TRIM(kategori), 2))
);

-- ------------------------------------------------
-- STEP 4: Recalculate Totals (Jumlah x Harga)
-- ------------------------------------------------

-- Koreksi total jika tidak sesuai dengan jumlah x harga
UPDATE sales
SET total = jumlah * harga
WHERE ABS(total - (jumlah * harga)) > 0.01;

-- ============================================================
-- VERIFIKASI DATA CLEANING
-- ============================================================

-- Cek NULL values setelah cleaning
SELECT 'NULL Check' AS step,
       COUNT(*) AS total_rows,
       SUM(CASE WHEN produk IS NULL OR TRIM(produk) = '' THEN 1 ELSE 0 END) AS null_produk,
       SUM(CASE WHEN kategori IS NULL OR TRIM(kategori) = '' THEN 1 ELSE 0 END) AS null_kategori,
       SUM(CASE WHEN tanggal IS NULL THEN 1 ELSE 0 END) AS null_tanggal
FROM sales;

-- Cek total mismatch
SELECT 'Total Check' AS step, COUNT(*) AS mismatch_count
FROM sales
WHERE ABS(total - (jumlah * harga)) > 0.01;

-- Sample data setelah cleaning
SELECT id, tanggal, produk, kategori, jumlah, harga, total
FROM sales
ORDER BY id
LIMIT 10;

-- ============================================================
-- BAGIAN 2: DATA TRANSFORMATION (Agregasi)
-- ============================================================
-- Mengolah data menggunakan agregasi:
-- - SUM (total penjualan)
-- - GROUP BY (produk & kategori)
-- - COUNT (jumlah transaksi)
-- ============================================================

-- --------------------------------
-- 2a. Total Penjualan Per Produk
-- --------------------------------
SELECT produk,
       SUM(total)   AS total_penjualan,
       COUNT(*)     AS jumlah_transaksi,
       AVG(total)   AS rata_rata
FROM sales
GROUP BY produk
ORDER BY total_penjualan DESC;

-- --------------------------------
-- 2b. Total Penjualan Per Kategori
-- --------------------------------
SELECT kategori,
       SUM(total)   AS total_penjualan,
       COUNT(*)     AS jumlah_transaksi,
       AVG(total)   AS rata_rata
FROM sales
GROUP BY kategori
ORDER BY total_penjualan DESC;

-- --------------------------------
-- 2c. KPI Metrics Ringkasan
-- --------------------------------
SELECT
    SUM(total)                    AS total_revenue,
    COUNT(*)                      AS total_transactions,
    AVG(total)                    AS average_transaction,
    COUNT(DISTINCT produk)        AS total_products,
    COUNT(DISTINCT kategori)      AS total_categories
FROM sales;

-- --------------------------------
-- 2d. Tren Penjualan Harian
-- --------------------------------
SELECT DATE(tanggal)  AS tanggal,
       SUM(total)     AS total_penjualan,
       COUNT(*)       AS jumlah_transaksi
FROM sales
GROUP BY DATE(tanggal)
ORDER BY DATE(tanggal);

-- --------------------------------
-- 2e. Penjualan Per Kategori Per Bulan
-- --------------------------------
SELECT kategori,
       YEAR(tanggal)  AS tahun,
       MONTH(tanggal) AS bulan,
       SUM(total)     AS total_penjualan,
       COUNT(*)       AS jumlah_transaksi
FROM sales
GROUP BY kategori, YEAR(tanggal), MONTH(tanggal)
ORDER BY tahun, bulan, kategori;

-- --------------------------------
-- 2f. Penjualan Per Produk Per Minggu
-- --------------------------------
SELECT produk,
       YEAR(tanggal)  AS tahun,
       WEEK(tanggal)  AS minggu,
       SUM(total)     AS total_penjualan,
       COUNT(*)       AS jumlah_transaksi
FROM sales
GROUP BY produk, YEAR(tanggal), WEEK(tanggal)
ORDER BY tahun, minggu, produk;

-- ============================================================
-- BAGIAN 3: DATA ANALYSIS
-- ============================================================
-- Melakukan analisis:
-- - Total penjualan keseluruhan per produk
-- - Penjualan per produk berdasarkan tanggal waktu per minggu
-- - Penjualan per kategori per bulannya
-- - Tren penjualan berdasarkan waktu
-- ============================================================

-- --------------------------------
-- 3a. Top 10 Produk Berdasarkan Revenue
-- --------------------------------
SELECT produk,
       SUM(total)    AS total_penjualan,
       COUNT(*)      AS jumlah_transaksi,
       AVG(total)    AS rata_rata,
       ROUND(SUM(total) / (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen
FROM sales
GROUP BY produk
ORDER BY total_penjualan DESC
LIMIT 10;

-- --------------------------------
-- 3b. Ranking Kategori Berdasarkan Revenue
-- --------------------------------
SELECT kategori,
       SUM(total)    AS total_penjualan,
       COUNT(*)      AS jumlah_transaksi,
       ROUND(SUM(total) / (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen
FROM sales
GROUP BY kategori
ORDER BY total_penjualan DESC;

-- --------------------------------
-- 3c. Penjualan Per Produk Per Minggu (Lengkap)
-- --------------------------------
SELECT produk,
       YEAR(tanggal)                AS tahun,
       WEEK(tanggal)                AS minggu,
       CONCAT(YEAR(tanggal), '-W', LPAD(WEEK(tanggal), 2, '0')) AS periode,
       SUM(total)                   AS total_penjualan,
       COUNT(*)                     AS jumlah_transaksi,
       AVG(total)                   AS rata_rata
FROM sales
GROUP BY produk, YEAR(tanggal), WEEK(tanggal)
ORDER BY tahun, minggu, total_penjualan DESC;

-- --------------------------------
-- 3d. Penjualan Per Kategori Per Bulan (Lengkap)
-- --------------------------------
SELECT kategori,
       YEAR(tanggal)                AS tahun,
       MONTH(tanggal)               AS bulan,
       CONCAT(YEAR(tanggal), '-', LPAD(MONTH(tanggal), 2, '0')) AS periode,
       SUM(total)                   AS total_penjualan,
       COUNT(*)                     AS jumlah_transaksi
FROM sales
GROUP BY kategori, YEAR(tanggal), MONTH(tanggal)
ORDER BY tahun, bulan, kategori;

-- --------------------------------
-- 3e. Tren Penjualan Harian + Rata-rata
-- --------------------------------
SELECT DATE(tanggal)    AS tanggal,
       DAYNAME(tanggal) AS hari,
       SUM(total)       AS total_penjualan,
       COUNT(*)         AS jumlah_transaksi,
       AVG(total)       AS rata_rata_transaksi
FROM sales
GROUP BY DATE(tanggal), DAYNAME(tanggal)
ORDER BY DATE(tanggal);

-- --------------------------------
-- 3f. Distribusi Revenue Per Kategori
-- --------------------------------
SELECT kategori,
       SUM(total)                                                     AS total_penjualan,
       ROUND(SUM(total) / (SELECT SUM(total) FROM sales) * 100, 1)   AS persentase
FROM sales
GROUP BY kategori
ORDER BY total_penjualan DESC;

-- --------------------------------
-- 3g. Insight: Produk Terlaris
-- --------------------------------
SELECT produk                          AS produk_terlaris,
       SUM(total)                      AS total_revenue,
       ROUND(SUM(total) /
            (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen,
       COUNT(*)                        AS jumlah_transaksi
FROM sales
GROUP BY produk
ORDER BY total_revenue DESC
LIMIT 1;

-- --------------------------------
-- 3h. Insight: Kategori Dominan
-- --------------------------------
SELECT kategori                        AS kategori_dominan,
       SUM(total)                      AS total_revenue,
       ROUND(SUM(total) /
            (SELECT SUM(total) FROM sales) * 100, 1) AS kontribusi_persen,
       COUNT(*)                        AS jumlah_transaksi
FROM sales
GROUP BY kategori
ORDER BY total_revenue DESC
LIMIT 1;

-- --------------------------------
-- 3i. Insight: Transaksi Tertinggi
-- --------------------------------
SELECT id, tanggal, produk, kategori, jumlah, harga, total
FROM sales
ORDER BY total DESC
LIMIT 1;

-- --------------------------------
-- 3j. Insight: Performa Bisnis Summary
-- --------------------------------
SELECT
    COUNT(*)                                     AS total_transaksi,
    COUNT(DISTINCT produk)                       AS total_produk_unik,
    COUNT(DISTINCT kategori)                     AS total_kategori,
    COUNT(DISTINCT DATE(tanggal))                AS total_hari_transaksi,
    ROUND(SUM(total), 0)                         AS total_revenue,
    ROUND(AVG(total), 0)                         AS rata_rata_transaksi,
    MIN(tanggal)                                 AS transaksi_pertama,
    MAX(tanggal)                                 AS transaksi_terakhir
FROM sales;

-- --------------------------------
-- 3k. Data Cleaning Summary
-- --------------------------------
SELECT
    COUNT(*)                                     AS total_data_bersih,
    COUNT(DISTINCT produk)                       AS total_produk,
    COUNT(DISTINCT kategori)                     AS total_kategori,
    COUNT(DISTINCT DATE(tanggal))                AS total_hari_transaksi,
    ROUND(SUM(total), 0)                         AS total_revenue
FROM sales;

-- ============================================================
-- END OF QUERY
-- 411231139 - Muhamad Aditya Saputra
-- ============================================================
