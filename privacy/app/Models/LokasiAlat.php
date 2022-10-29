<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\MasterLokasi;
use App\Models\Produk;
use App\Models\Pemakaian;

class LokasiAlat extends Model
{
    //
    use AuditableTrait;

    protected $table = 'lokasi_alat';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'kode_alat',
        'kode_operator',
        'kode_lokasi',
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

    public function MasterLokasi()
    {
        return $this->belongsTo(MasterLokasi::class,'kode_lokasi');
    }

    public function Operator()
    {
        return $this->belongsTo(Operator::class,'kode_operator');
    }

    public function getDestroyUrlAttribute()
    {
        return route('alat.destroy', $this->kode_alat);
    }

    public function getEditUrlAttribute()
    {
        return route('alat.edit',$this->kode_alat);
    }

    public function getUpdateUrlAttribute()
    {
        return route('alat.update',$this->kode_alat);
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
