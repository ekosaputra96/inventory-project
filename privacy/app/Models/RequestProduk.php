<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Nonstock;
use App\Models\satuan;
use App\Models\Pembelian;
use App\Models\Konversi;
use App\Models\Vendor;

class RequestProduk extends Model
{
    //
    use AuditableTrait;

    protected $table = 'request_produk';

	public $incrementing = true;

	protected $fillable = [
        'no_request',
        'kode_produk',
    	'qty',
    ];

    protected $appends = ['destroy_url','edit_url'];
    
    public function Vendor()
    {
        return $this->belongsTo(Vendor::class,'kode_vendor');
    }

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

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function Konversi()
    {
        return $this->belongsTo(Produk::class,'kode_satuan');
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class,'no_pembelian');
    }  

     public function getDestroyUrlAttribute()
    {
        return route('pembeliandetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('pembeliandetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('pembeliandetail.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
            $query->qty_po = 0;
            $query->status_produk = "OFF";
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
