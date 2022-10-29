<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Mobil;
use App\Models\Pemakaian;


class JenisMobil extends Model
{
    //

    use AuditableTrait;

    protected $table = 'jenis_mobils';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_jenis_mobil',
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
            $koneksi = 'mysqlemkl';
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

    public function Mobil()
    {
    return $this->hasMany(Mobil::class,'id');
    }

    public function Pemakaian()
    {
    return $this->hasMany(Pemakaian::class,'kode_mobil');
    }

     public function getDestroyUrlAttribute()
    {
        return route('jenismobil.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('jenismobil.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('jenismobil.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
