<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class WorkorderDetail extends Model
{

    protected $table = 'workorder_detail';

	public $incrementing = true;

	protected $fillable = [
    	'no_wo',
        'kode_produk',
        'nama_produk',
        'partnumber',
        'type',
        'qty',
        'qty_pakai',
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
    
    public function Nonstock()
    {
        return $this->belongsTo(Nonstock::class,'kode_produk');
    }

     public function getDestroyUrlAttribute()
    {
        return route('workorderdetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('workorderdetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('workorderdetail.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($query){
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
            $query->qty_pakai = 0;
            $query->status_produk = "OFF";
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
