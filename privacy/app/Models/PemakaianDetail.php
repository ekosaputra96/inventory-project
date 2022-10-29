<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Pemakaian;

class PemakaianDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'pemakaian_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_pemakaian',
        'kode_produk',
        'partnumber',
        'kode_satuan',
        'qty',
        'qty_retur',
        'harga',
        'keterangan',
    ];

    protected $appends = ['destroy_url','edit_url'];

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function pemakaian()
    {
        return $this->belongsTo(Pemakaian::class,'no_pemakaian');
    }   

     public function getDestroyUrlAttribute()
    {
        return route('pemakaiandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('pemakaiandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('pemakaiandetail.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
