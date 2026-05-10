<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = [
        'tanggal',
        'produk',
        'kategori',
        'jumlah',
        'harga',
        'total',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
    ];
}
