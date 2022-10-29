<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\ReturpembelianDetail;
use App\Models\Vendor;
use App\Models\Penerimaan;
use App\Models\TransaksiSetup;
use App\Models\tb_akhir_bulan;
use Carbon;
use DB;

class Returpembelian extends Model
{
    //
    use AuditableTrait;

    protected $table = 'retur_pembelian';

    protected $primaryKey = 'no_returpembelian';

    public $incrementing = false;

    protected $fillable = [
        'no_returpembelian',
        'tanggal_returpembelian',
        'no_penerimaan',
        'no_pembelian',
        'kode_vendor',
        'keterangan',
        'total_item',
        'status',
        'kode_lokasi',
        'kode_company',
        'kode_jurnal',
        'no_journal',
        'cost_center',
    ];

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Vendor()
    {
        return $this->belongsTo(Vendor::class,'kode_vendor');
    }

    public function Pembelian()
    {
        return $this->belongsTo(Pembelian::class,'no_pembelian');
    }

    public function Penerimaan()
    {
        return $this->belongsTo(Penerimaan::class,'no_penerimaan');
    }

    public function ReturpembelianDetail()
    {
        return $this->hasMany(ReturpembelianDetail::class,'no_returpembelian');
    }

    public function getDestroyUrlAttribute()
    {
        return route('returpembelian.destroy', $this->no_returpembelian);
    }

    public function getEditUrlAttribute()
    {
        return route('returpembelian.edit',$this->no_returpembelian);
    }

    public function getUpdateUrlAttribute()
    {
        return route('returpembelian.update',$this->no_returpembelian);
    }

    public function getDetailUrlAttribute()
    {
        return route('returpembelian.detail',$this->no_returpembelian);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->total_item = 0;
            $query->kode_jurnal = '160';
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_returpembelian = static::generateKode(request());
            $query->no_journal = static::getjurnal(request()->tanggal_returpembelian);
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
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }


    public static function getjurnal()
    {
        $konek = static::konek();
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;

        $kode_company = auth()->user()->kode_company;
        $tahun = Carbon\Carbon::parse($tgl_jalan2)->format('y');
        $bulan = Carbon\Carbon::parse($tgl_jalan2)->format('m');

        $jurnal1 = '160'.'.'.$kode_company.$tahun.'.'.$bulan.'.';
        
        $cek_jurnal = self::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal != null){
            $leng = substr($cek_jurnal->no_journal,12,4);
        }else {
            $leng = 0;
        }

        $lenger = $leng;

        $hasil = $jurnal1.sprintf('%04d', intval($lenger) + 1);
          
        return $hasil;
    }


    public static function generateKode($data)
    {
        $konek = static::konek();
        $user = Auth()->user()->name;

        $kode = TransaksiSetup::where('kode_setup','014')->first();
        
        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_2 = strtoupper($kode->kode_transaksi);
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('my');
        $get_prefix_3 = $period;
        $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_returpembelian', 'desc')->first();

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
