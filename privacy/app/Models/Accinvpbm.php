<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Accinvpbm extends Model
{
    //
    use AuditableTrait;

    protected $table = 'accinv_pbm';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'type_inv',
        'coa_pendapatan',     
    ];

    public static function konek()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '0401'){
            $koneksi = 'mysqlgutjkt';
        }else if ($compa == '03'){
            $koneksi = 'mysql';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysqlsub';
        }
        return $koneksi;
    }

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'coa_pendapatan');
    }

     public function getDestroyUrlAttribute()
    {
        return route('accinvpbm.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('accinvpbm.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('accinvpbm.update',$this->id);
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
