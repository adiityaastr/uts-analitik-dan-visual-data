<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE sales MODIFY produk VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE sales MODIFY kategori VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE sales MODIFY jumlah INT NOT NULL');
        DB::statement('ALTER TABLE sales MODIFY harga DECIMAL(15,2) NOT NULL');
        DB::statement('ALTER TABLE sales MODIFY total DECIMAL(15,2) NOT NULL');
        DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_jumlah CHECK (jumlah > 0)');
        DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_harga CHECK (harga >= 0)');
        DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_total_akurat CHECK (total = jumlah * harga)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sales DROP CHECK chk_jumlah');
        DB::statement('ALTER TABLE sales DROP CHECK chk_harga');
        DB::statement('ALTER TABLE sales DROP CHECK chk_total_akurat');
    }
};
