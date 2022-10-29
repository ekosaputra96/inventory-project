<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;

class Kartustok extends Model
{
    //
    use AuditableTrait;

    public $incrementing = false;

    protected $fillable = [
        'kode_produk',
        'tanggal_awal',
        'tanggal_akhir',
    ];
}
