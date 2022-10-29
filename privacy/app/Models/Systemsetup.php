<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Systemsetup extends Model
{
    //
    use AuditableTrait;

    protected $connection = 'mysql4';
    
    protected $table = 'system_setup';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_setup',
        'type',
        'kode_setup',
    ];

    public static function konek()
    {
        $compa2 = auth()->user()->kode_company;
        $compa = substr($compa2,0,2);
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '03'){
            $koneksi = 'mysql';
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql';
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'kode_setup');
    }

    public function getDestroyUrlAttribute()
    {
        return route('systemsetup.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('systemsetup.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('systemsetup.update',$this->id);
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
