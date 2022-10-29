<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class Merek extends Model
{
    //
    use AuditableTrait;

    protected $table = 'merek';

    protected $primaryKey = 'kode_merek';

    public $incrementing = false;

    protected $fillable = [
        'kode_merek',
        'nama_merek',
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

     public function getDestroyUrlAttribute()
    {
        return route('merek.destroy', $this->kode_merek);
    }

    public function getEditUrlAttribute()
    {
        return route('merek.edit',$this->kode_merek);
    }

    public function getUpdateUrlAttribute()
    {
        return route('merek.update',$this->kode_merek);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_merek = static::generateNumber(request()->nama_merek);
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
        $lastRecort = self::on($konek)->orderBy('kode_merek', 'desc')->first();
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
