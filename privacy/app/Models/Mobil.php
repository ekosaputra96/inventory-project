<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\MasterLokasi;
use App\Models\JenisMobil;
use App\Models\Pemakaian;

class Mobil extends Model
{
    //
    use AuditableTrait;

    protected $table = 'mobils';

    protected $primaryKey = 'kode_mobil';

    public $incrementing = false;

    protected $fillable = [
        'kode_mobil',
        'nopol',
        'kode_jenis_mobil',
        'tahun',
        'no_asset_mobil',
        'kode_lokasi',
        'status_mobil',
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

    public function JenisMobil()
    {
        return $this->belongsTo(JenisMobil::class,'kode_jenis_mobil');
    }

    public function Pemakaian()
    {
        return $this->hasMany(Pemakaian::class,'kode_mobil');
    }

     public function getDestroyUrlAttribute()
    {
        return route('mobil.destroy', $this->kode_mobil);
    }

    public function getEditUrlAttribute()
    {
        return route('mobil.edit',$this->kode_mobil);
    }

    public function getUpdateUrlAttribute()
    {
        return route('mobil.update',$this->kode_mobil);
    }
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->kode_lokasi = 'HO';
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_mobil = static::generateNumber(request()->nopol);
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
        $lastRecort = self::on($konek)->orderBy('kode_mobil', 'desc')->first();
        $prefix = strtoupper($sumber_text) ;
        $primary_key = (new self)->getKeyName();

        // dd($primary_key);

        if ( ! $lastRecort )
            $number = 0;
        else {
            $field = $lastRecort->{$primary_key} ;
            if ($prefix[0] != $lastRecort->{$primary_key}[0]){
                $number = $field;
            }else {
                $number = 0;
            }
        }

        return  sprintf('%03d', intval($number) + 1);
    }
}
