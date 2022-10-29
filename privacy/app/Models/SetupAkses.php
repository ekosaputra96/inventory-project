<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use DB;

class SetupAkses extends Model
{
    //
    use AuditableTrait;

    protected $table = 'setup_akses';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_user',
        'nama_user2',
        'nama_user3',
        'limit_dari',
        'limit_total',
        'kode_company',
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
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    public function getDestroyUrlAttribute()
    {
        return route('setupakses.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('setupakses.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('setupakses.update',$this->id);
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
