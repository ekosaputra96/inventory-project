<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Itembulanan;
use App\Models\Transfer;

class TransferDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'transfer_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_transfer',
        'kode_produk',
        'kode_satuan',
        'partnumber',
        'no_mesin',
        'qty',
        'hpp',
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

    public function Transfer()
    {
        return $this->belongsTo(Transfer::class,'no_transfer');
    }

    public function getDestroyUrlAttribute()
    {
        return route('transferdetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('transferdetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('transferdetail.update',$this->id);
    }

    public function getDetailUrlAttribute()
    {
        return route('transferdetail.detail',$this->id);
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
