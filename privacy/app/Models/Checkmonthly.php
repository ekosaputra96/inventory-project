<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\tb_item_bulanan;

class Checkmonthly extends Model
{
    //
    use AuditableTrait;

    public $incrementing = false;

    protected $fillable = [
        'bulan_awal',
        'tahun_awal',
        'bulan_akhir',
        'tahun_akhir',
    ];
}
