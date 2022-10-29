<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\MasterLokasi;
use App\Models\Produk;
use App\Models\Pemakaian;

class Alat2 extends Model
{
    //
    use AuditableTrait;

    protected $connection = 'mysql_front_pbm';

    protected $table = 'alat';

    protected $primaryKey = 'kode_alat';

    public $incrementing = false;

    protected $fillable = [
        'kode_alat',
        'nama_alat',
        'merk',
        'type',
        'kapasitas',
        'kode_time_sheet',
        'no_asset_alat',
        'tahun',
        'kode_lokasi',
        'status',
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

    public function Pemakaian()
    {
        return $this->hasMany(Pemakaian::class,'kode_alat');
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
            $query->kode_lokasi = 'HO';
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_alat = static::generateNumber(request()->nama_alat);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateNumber($sumber_text)
    {
        $konek = static::konek();
        $lastRecort = self::on('mysql_front_pbm')->orderBy('kode_alat', 'desc')->first();
        $prefix = strtoupper($sumber_text) ;
        $primary_key = (new self)->getKeyName();


        if ( $lastRecort == null )
            $number = 0;
        else {
            $field = $lastRecort->{$primary_key} ;

            if ($prefix[0] != $lastRecort->{$primary_key}[0]){
                $number = $field;
            }else {
                $number = 0;
            }
        }

        return sprintf('%03d', intval($number) + 1);
    }
}
