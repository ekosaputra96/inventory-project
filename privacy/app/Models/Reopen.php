<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;

class Reopen extends Model
{
    //
    use AuditableTrait;

    protected $table = 'reopen';

    public $incrementing = false;

    protected $fillable = [
        'tanggal_reopen',
    ];
}
