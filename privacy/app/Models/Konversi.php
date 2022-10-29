<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\TransaksiSetup;

class Konversi extends Model
{
    //
    use AuditableTrait;

    protected $table = 'konversi_satuan';

    protected $primaryKey = 'kode_konversi';

    public $incrementing = false;

    protected $fillable = [
        'kode_konversi',
        'kode_produk',
        'kode_satuan',
        'satuan_terbesar',
        'nilai_konversi',
        'kode_satuanterkecil',
        'satuan_terkecil',
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

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function PembelianDetail()
    {
    return $this->belongsTo(PembelianDetail::class,'kode_satuan');
    }

    public function getDestroyUrlAttribute()
    {
        return route('konversi.destroy', $this->kode_konversi);
    }

    public function getEditUrlAttribute()
    {
        return route('konversi.edit',$this->kode_konversi);
    }

    public function getUpdateUrlAttribute()
    {
        return route('konversi.update',$this->kode_konversi);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_konversi = static::generateKode(Auth()->user()->kode_konversi);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateKode($sumber_text)
    {
        $konek = static::konek();
        $comp = auth()->user()->kode_company;
        $primary_key = (new self)->getKeyName();
        $get_prefix_2 = strtoupper($sumber_text);
        $prefix_result = $get_prefix_2;
        $prefix_result_length = strlen($prefix_result);

        $lastRecort = Konversi::on($konek)->where('kode_konversi','like',$prefix_result.'%')->orderBy('created_at', 'desc')->first();

        if ( ! $lastRecort )
            $number = 0;
        else {
            $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
            if ($get_record_prefix === $prefix_result){
                $number = substr($lastRecort->{$primary_key},$prefix_result_length);
            }else {
                $number = 0;
            }

        }
        
        if ($comp == '02' || $comp == '03' || $comp == '01') {
            $result_number = $prefix_result . sprintf('%04d', intval($number) + 1);
        }else {
            $result_number = $prefix_result . sprintf('%03d', intval($number) + 1);
        }
        
        return $result_number ;
    }
}
