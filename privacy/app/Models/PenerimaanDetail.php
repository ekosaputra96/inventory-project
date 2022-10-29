<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Itembulanan;
use App\Mdoels\Penerimaan;

class PenerimaanDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'penerimaan_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_penerimaan',
        'kode_produk',
        'kode_satuan',
        'partnumber',
        'no_mesin',
        'qty',
        'qty_retur',
        'harga',
        'landedcost',
    ];

    protected $appends = ['destroy_url','edit_url','detail_url'];

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class,'no_penerimaan');
    }

    public function getDestroyUrlAttribute()
    {
        return route('penerimaandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('penerimaandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('penerimaandetail.update',$this->id);
    }

    public function getDetailUrlAttribute()
    {
        return route('penerimaandetail.detail',$this->id);
    }


    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->qty_retur = 0;
            $query->kode_company = Auth()->user()->kode_company;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

}
