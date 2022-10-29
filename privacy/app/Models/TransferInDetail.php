<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Itembulanan;
use App\Models\Transfer;
use App\Models\TransferIn;

class TransferInDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'transfer_in_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_trf_in',
        'kode_produk',     
        'partnumber',    
        'no_mesin',
        'kode_satuan',      
        'qty',
        'hpp',
        'kode_company',     
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

    public function TransferIn()
    {
        return $this->belongsTo(TransferIn::class,'no_trf_in');
    }

    public function getDestroyUrlAttribute()
    {
        return route('transferindetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('transferindetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('transferindetail.update',$this->id);
    }

    public function getDetailUrlAttribute()
    {
        return route('transferindetail.detail',$this->id);
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
