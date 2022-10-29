<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\satuan;
use App\Models\Penjualan;

class PenjualanDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'penjualan_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_penjualan',
        'kode_produk',
        'partnumber',
        'no_mesin',
        'kode_satuan',
        'qty',
        'qty_retur',
        'harga_jual',
        'harga',
        'total',
    ];

    protected $appends = ['destroy_url','edit_url'];

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function Jasa()
    {
        return $this->belongsTo(Jasa::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function Penjualan()
    {
        return $this->belongsTo(Penjualan::class,'no_penjualan');
    }   

     public function getDestroyUrlAttribute()
    {
        return route('penjualandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('penjualandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('penjualandetail.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->qty_retur = 0;
            $query->kode_company = Auth()->user()->kode_company;;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
