<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class DisassemblingDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'disassembling_detail';

    protected $primaryKey = 'id_detail';

    public $incrementing = false;

    protected $fillable = [
        'id_detail',
        'id',
        'kode_produk',
        'partnumber',
        'qty',
        'hpp',
    ];

    protected $appends = ['destroy_url','edit_url'];

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function getDestroyUrlAttribute()
    {
        return route('disassemblingdetail.destroy', $this->id_detail);
    }

    public function getEditUrlAttribute()
    {
        return route('disassemblingdetail.edit',$this->id_detail);
    }

    public function getUpdateUrlAttribute()
    {
        return route('disassemblingdetail.update',$this->id_detail);
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
