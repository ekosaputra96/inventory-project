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

class ReturpembelianDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'returpembelian_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_returpembelian',
        'no_penerimaan',
        'kode_produk',
        'kode_satuan',
        'partnumber',
        'no_mesin',
        'qty',
        'harga',
        'landedcost',
        'id',
    ];

    protected $appends = ['destroy_url','edit_url','detail_url'];
    
    public function Vendor()
    {
        return $this->belongsTo(Vendor::class,'kode_vendor');
    }

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

    public function Returpembelian()
    {
        return $this->belongsTo(Returpembelian::class,'no_returpembelian');
    }

    public function getDestroyUrlAttribute()
    {
        return route('returpembeliandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('returpembeliandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('returpembeliandetail.update',$this->id);
    }

    public function getDetailUrlAttribute()
    {
        return route('returpembeliandetail.detail',$this->id);
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
