<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\TransferDetail;
use App\Models\TransaksiSetup;
use App\Models\tb_akhir_bulan;
use App\Models\TransferInDetail;
use App\Models\MasterLokasi;
use Carbon;
use DB;

class TransferIn extends Model
{
    //
    use AuditableTrait;

    protected $table = 'transfer_in';

    protected $primaryKey = 'no_trf_in';

    public $incrementing = false;

    protected $fillable = [
        'no_trf_in',
        'no_transfer',    
        'tanggal_transfer',
        'kode_lokasi',
        'total_item',
        'kode_dari',
        'transfer_dari',
        'keterangan',
        'status',
        'kode_company',
        'kode_jurnal',
        'no_journal',
    ];
    
    public function Lokasi()
    {
        return $this->belongsTo(MasterLokasi::class,'kode_lokasi');
    }
    
    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Transfer()
    {
        return $this->belongsTo(Transfer::class,'no_transfer');
    }

    public function TransferInDetail()
    {
        return $this->hasMany(TransferInDetail::class,'no_trf_in');
    }
    
    public function getDestroyUrlAttribute()
    {
        return route('transferin.destroy', $this->no_trf_in);
    }

    public function getEditUrlAttribute()
    {
        return route('transferin.edit',$this->no_trf_in);
    }

    public function getUpdateUrlAttribute()
    {
        return route('transferin.update',$this->no_trf_in);
    }

    public function getDetailUrlAttribute()
    {
        return route('transferin.detail',$this->no_trf_in);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->total_item = 0;
            $query->kode_jurnal = '190';
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_trf_in = static::generateKode(request());
            $query->no_journal = static::getjurnal(request()->tanggal_transfer);
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

        $jurnal1 = '190'.'.'.$kode_company.$tahun.'.'.$bulan.'.';
        
        if($kode_company != '0401'){
            $cek_jurnal = self::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
            if ($cek_jurnal != null){
                $leng = substr($cek_jurnal->no_journal,12,4);
            }else {
                $leng = 0;
            }
        }else{
            $cek_jurnal = self::on($konek)->where(DB::raw('LEFT(no_journal,14)'),$jurnal1)->orderBy('created_at','desc')->first();
            if ($cek_jurnal != null){
                $leng = substr($cek_jurnal->no_journal,14,4);
            }else {
                $leng = 0;
            }
        }

        $lenger = $leng;

        $hasil = $jurnal1.sprintf('%04d', intval($lenger) + 1);
          
        return $hasil;
    }



    public static function generateKode($data)
    {
        $konek = self::konek();
        $kode = TransaksiSetup::where('kode_setup','012')->first();
        
        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_2 = strtoupper($kode->kode_transaksi);
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('my');
        $get_prefix_3 = $period;
        $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_trf_in', 'desc')->first();
            
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
