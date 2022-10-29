<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\satuan;
use App\Models\Opname;

class OpnameDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'opname_detail';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'no_opname',
        'kode_produk',
        'partnumber',
        'no_mesin',
        'kode_satuan',
        'stok',
        'hpp',
        'qty_checker1',
        'qty_checker2',
        'qty_checker3',
        'hpp',
        'stock_opname',
        'amount_opname',
    ];

    protected $appends = ['destroy_url','edit_url'];

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

     public function Merek()
    {
        return $this->belongsTo(Merek::class,'kode_merek');
    }

    public function Ukuran()
    {
        return $this->belongsTo(Ukuran::class,'kode_ukuran');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function opname()
    {
        return $this->belongsTo(Opname::class,'no_opname');
    }  

     public function getDestroyUrlAttribute()
    {
        return route('opnamedetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('opnamedetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('opnamedetail.update',$this->id);
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
