<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Adjustment;
use App\Models\users;


class user_history extends Model
{
    //
    use AuditableTrait;

    protected $table = 'user_history';

    protected $fillable = [
    	'nama',
    	'aksi',
    	'created_by',
    	'updated_by',
    ];

}
