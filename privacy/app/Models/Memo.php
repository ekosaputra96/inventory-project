<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Memo;
use App\Models\Vendor;
use App\Models\Signature;
use App\Models\PembelianDetail;
use App\Models\TransaksiSetup;
use App\Models\tb_akhir_bulan;
use Carbon;

class Memo extends Model
{
    //
    use AuditableTrait;

    protected $table = 'memo';

    protected $primaryKey = 'no_memo';

    public $incrementing = false;

    // public $timestamps = false;

    protected $fillable = [
        'no_memo',
        'tgl_memo',
        'keterangan',

    ];

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }
    public function Memo()
    {
        return $this->belongsTo(Memo::class,'no_memo');
    }

    public function Vendor()
    {
        return $this->belongsTo(Vendor::class,'kode_vendor');
    }

    public function PembelianDetail()
    {
        return $this->hasMany(PembelianDetail::class,'no_pembelian');
    }

     public function getDestroyUrlAttribute()
    {
        return route('pembelian.destroy', $this->no_pembelian);
    }

    public function getEditUrlAttribute()
    {
        return route('pembelian.edit',$this->no_pembelian);
    }

    public function getUpdateUrlAttribute()
    {
        return route('pembelian.update',$this->no_pembelian);
    }

    // public function getDetailUrlAttribute()
    // {
    //     return route('pembelian.detail',$this->no_pembelian);
    // }

    public function getShowUrlAttribute()
    {
        return route('pembelian.show',$this->no_pembelian);
    }

    public function getCetakUrlAttribute()
    {
        return route('pembelian.cetak',$this->no_pembelian);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->total_item = 0;
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_memo = static::generateKode(request()->tanggal_pembelian, request()->jenis_po);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        // static::updating(function ($query){
        //   $query->updated_by = Auth()->user()->name;
        // });
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
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    public static function generateKode($data)
    {
        $konek = static::konek();
        $kode = TransaksiSetup::where('kode_setup','033')->first();
        
        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_2 = strtoupper($kode->kode_transaksi);
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('ym');
        $get_prefix_3 = $period;
        $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_memo', 'desc')->first();

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
