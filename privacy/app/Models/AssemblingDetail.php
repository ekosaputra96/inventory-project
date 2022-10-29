<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class AssemblingDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'assembling_detail';

    protected $primaryKey = 'id_detail';

    public $incrementing = false;

    protected $fillable = [
        'id_detail',
        'no_ass',
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
        return route('assemblingdetail.destroy', $this->id_detail);
    }

    public function getEditUrlAttribute()
    {
        return route('assemblingdetail.edit',$this->id_detail);
    }

    public function getUpdateUrlAttribute()
    {
        return route('assemblingdetail.update',$this->id_detail);
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
