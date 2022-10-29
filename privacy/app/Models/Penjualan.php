<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\PenjualanDetail;
use App\Models\TransaksiSetup;
use App\Models\MasterLokasi;
use Carbon;

class Penjualan extends Model
{
    //
    use AuditableTrait;

    protected $table = 'penjualan';

    protected $primaryKey = 'no_penjualan';

    public $incrementing = false;

    protected $fillable = [
        'no_penjualan',
        'tanggal_penjualan',
        'kode_customer',
        'no_josp',
        'no_spjb',
        'tgl_spjb',
        'no_bast',
        'tgl_bast',
        'seri_faktur',
        'top',
        'total_item',
        'due_date',
        'no_sertifikat',
        'tgl_sertifikat',
        'ppn',
        'diskon_persen',
        'diskon_rp',
        'grand_total',
        'status',
        'kode_company',
        'type_ar',       
    ];

    public function Lokasi()
    {
        return $this->belongsTo(MasterLokasi::class,'kode_lokasi');
    }
   
    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Customer()
    {
        return $this->belongsTo(Customer::class,'kode_customer');
    }

    public function PenjualanDetail()
    {
        return $this->hasMany(PenjualanDetail::class,'no_penjualan');
    }

     public function getDestroyUrlAttribute()
    {
        return route('penjualan.destroy', $this->no_penjualan);
    }

    public function getEditUrlAttribute()
    {
        return route('penjualan.edit',$this->no_penjualan);
    }

    public function getUpdateUrlAttribute()
    {
        return route('penjualan.update',$this->no_penjualan);
    }

    public function getDetailUrlAttribute()
    {
        return route('penjualan.detail',$this->no_penjualan);
    }

    public function getCetakUrlAttribute()
    {
        return route('penjualan.cetak',$this->no_penjualan);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->grand_total = 0;
            $query->status = 'OPEN';
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_penjualan = static::generateKode(request()->type_ar, request()->tanggal_penjualan);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

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

    public static function generateKode($data, $tanggal)
    {
        $konek = self::konek();
        if($data == 'Jasa'){
            $kode = 'ISC';
        
            $primary_key = (new self)->getKeyName();
            $get_prefix_1 = Auth()->user()->kode_company;
            $get_prefix_2 = strtoupper($kode);
            $get_prefix_3 = date('my');
            $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
            $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

            $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_penjualan', 'desc')->first();

            if ( ! $lastRecort )
                $number = 0;
            else {
                $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
                if ($get_record_prefix == $prefix_result){
                    $number = substr($lastRecort->{$primary_key},$prefix_result_length);
                }else {
                    $number = 0;
                }

            }

            $result_number = $prefix_result . sprintf('%06d', intval($number) + 1);

            return $result_number ;
        }
        else if($data == 'Sparepart'){
            $kode = 'ISP';
        
            $primary_key = (new self)->getKeyName();
            $period = Carbon\Carbon::parse($tanggal)->format('my');

            $get_prefix_1 = Auth()->user()->kode_company;
            $get_prefix_2 = strtoupper($kode);
            $get_prefix_3 = $period;
            $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
            $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

            $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_penjualan', 'desc')->first();

            if ( ! $lastRecort )
                $number = 0;
            else {
                $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
                if ($get_record_prefix == $prefix_result){
                    $number = substr($lastRecort->{$primary_key},$prefix_result_length);
                }else {
                    $number = 0;
                }

            }

            $result_number = $prefix_result . sprintf('%06d', intval($number) + 1);

            return $result_number ;
        }else{
            $kode = 'IHE';
        
            $primary_key = (new self)->getKeyName();
            $period = Carbon\Carbon::parse($tanggal)->format('my');

            $get_prefix_1 = Auth()->user()->kode_company;
            $get_prefix_2 = strtoupper($kode);
            $get_prefix_3 = $period;
            $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
            $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

            $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_penjualan', 'desc')->first();

            if ( ! $lastRecort )
                $number = 0;
            else {
                $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
                if ($get_record_prefix == $prefix_result){
                    $number = substr($lastRecort->{$primary_key},$prefix_result_length);
                }else {
                    $number = 0;
                }

            }

            $result_number = $prefix_result . sprintf('%06d', intval($number) + 1);

            return $result_number ;
        }
    }
}
