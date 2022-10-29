<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Itembulanan;
use App\Mdoels\Returpembelian;
use App\Mdoels\Penerimaan;
use App\Models\Vendor;

class ReturpemakaianDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'retur_pemakaian_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_retur_pemakaian',
        'no_pemakaian',
        'kode_produk',
        'kode_satuan',
        'partnumber',
        'qty',
        'harga',
        'id',
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

    public function pemakaian()
    {
        return $this->belongsTo(Pemakaian::class,'no_pemakaian');
    }

    public function Returpemakaian()
    {
        return $this->belongsTo(ReturPemakaian::class,'no_retur_pemakaian');
    }

    public function getDestroyUrlAttribute()
    {
        return route('returpemakaiandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('returpemakaiandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('returpemakaiandetail.update',$this->id);
    }

    public function getDetailUrlAttribute()
    {
        return route('returpemakaiandetail.detail',$this->id);
    }


    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

}
