<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\PemakaianDetail;
use App\Models\TransaksiSetup;
use App\Models\Mobil;
use App\Models\JenisMobil;
use App\Models\Alat;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Penerimaan;
use App\Models\Masterlokasi;

class tb_bulanan_auto extends Model
{
    //

    protected $table = 'tb_item_bulanan';

    protected $primaryKey = 'kode_produk';

    public $incrementing = false;

    protected $fillable = [
        'kode_company',
        'periode',
        'kode_produk',
        'partnumber',
        'kode_lokasi',
        'no_mesin',
        'kode_satuan',
        'begin_stock',
        'begin_amount',
        'in_stock',
        'in_amount',
        'out_stock',
        'out_amount',
        'sale_stock',
        'sale_amount',
        'trf_in',
        'trf_in_amount',
        'trf_out',
        'trf_out_amount',
        'adjustment_stock',
        'adjustment_amount',
        'stock_opname',
        'amount_opname',
        'retur_beli_stock',
        'retur_beli_amount',
        'retur_pakai_stock',
        'retur_pakai_amount',
        'retur_jual_stock',
        'retur_jual_amount',
        'disassembling_stock',
        'disassembling_amount',
        'assembling_stock',
        'assembling_amount',
        'ending_stock',
        'ending_amount',
        'hpp',
    ];

    public function Penerimaan()
    {
    return $this->hasMany(Penerimaan::class,'periode');
    }

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Masterlokasi()
    {
        return $this->belongsTo(Masterlokasi::class,'kode_lokasi');
    }

    public function Mobil()
    {
        return $this->belongsTo(Mobil::class,'kode_mobil');
    }

    public function JenisMobil()
    {
        return $this->belongsTo(JenisMobil::class,'kode_jenis_mobil');
    }

    public function Alat()
    {
        return $this->belongsTo(Alat::class,'kode_alat');
    }

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function PemakaianDetail()
    {
        return $this->hasMany(PemakaianDetail::class,'no_pemakaian');
    }

    public function getUpdateUrlAttribute()
    {
        return route('tb_item_bulanan.update',$this->kode_produk);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->created_by = 'AutoEOM';
            $query->updated_by = 'AutoEOM';
        });

        static::updating(function ($query){
           $query->updated_by = 'AutoEOM';
        });
    }
}
