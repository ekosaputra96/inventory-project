<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Pemakaianban;

class PemakaianbanDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'pemakaianban_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_pemakaianban',
        'kode_produk',
        'partnumber',
        'partnumberbaru',
        'kode_satuan',
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

    public function pemakaianban()
    {
        return $this->belongsTo(Pemakaianban::class,'no_pemakaianban');
    }   

     public function getDestroyUrlAttribute()
    {
        return route('pemakaianbandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('pemakaianbandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('pemakaianbandetail.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
