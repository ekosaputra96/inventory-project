<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Itembulanan;
use App\Mdoels\Adjustment;

class AdjustmentDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'adjustments_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_penyesuaian',
        'kode_produk',
        'kode_satuan',
        'partnumber',
        'qty',
        'harga',
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

    public function penerimaan()
    {
        return $this->belongsTo(Adjustment::class,'no_penyesuaian');
    }

    public function getDestroyUrlAttribute()
    {
        return route('adjustmentdetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('adjustmentdetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('adjustmentdetail.update',$this->id);
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
