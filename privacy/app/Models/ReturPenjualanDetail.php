<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\Customer;

class ReturPenjualanDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'retur_jual_detail';

    public $incrementing = false;

    protected $fillable = [
        'no_retur_jual',
        'no_penjualan',
        'kode_produk',
        'partnumber',
        'kode_satuan',
        'qty_retur',
        'harga',
        'harga_jual',
    ];

    protected $appends = ['destroy_url','edit_url'];
    
    public function Customer()
    {
        return $this->belongsTo(Customer::class,'kode_customer');
    }

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class,'no_penjualan');
    }   

    public function returjual()
    {
        return $this->belongsTo(ReturPenjualan::class,'no_retur_jual');
    }   

     public function getDestroyUrlAttribute()
    {
        return route('returjualdetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('returjualdetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('returjualdetail.update',$this->id);
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
