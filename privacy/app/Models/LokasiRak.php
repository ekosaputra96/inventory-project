<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\MasterLokasi;

class LokasiRak extends Model
{
    //
    use AuditableTrait;
  
    protected $table = 'produk_lokasi_rak';

    protected $fillable = [
        'id',
        'kode_produk',
        'kode_lokasi',
        'partnumber',
        'lokasi_rak',
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
        }
        return $koneksi;
    }
    
    public function Lokasi()
    {
        return $this->belongsTo(MasterLokasi::class,'kode_lokasi');
    }

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

     public function getDestroyUrlAttribute()
    {
        return route('lokasirak.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('lokasirak.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('lokasirak.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($query){
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->id = static::generateNumber(request()->kode_produk);
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
        $lastRecort = self::on($konek)->orderBy('id', 'desc')->first();
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
        return $number + 1;
    }
}
