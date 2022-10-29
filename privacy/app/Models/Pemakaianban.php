<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Mobil;
use App\Models\Alat;
use App\Models\Signature;
use App\Models\PemakaianbanDetail;
use App\Models\TransaksiSetup;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use Carbon;
use DB;

class Pemakaianban extends Model
{
    //
    use AuditableTrait;

    protected $table = 'pemakaianban';

    protected $primaryKey = 'no_pemakaianban';

    public $incrementing = false;

    protected $fillable = [
        'no_pemakaianban',
        'tanggal_pemakaianban',
        'kode_mobil',
        'no_asset_mobil',
        'kode_alat',
        'no_asset_alat',
        'total_item',
        'status',
        'kode_company',
        'type',
        'kode_jurnal',
        'no_journal',
        'cost_center',
    ];

    public function Lokasi()
    {
        return $this->belongsTo(MasterLokasi::class,'kode_lokasi');
    }

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Mobil()
    {
        return $this->belongsTo(Mobil::class,'kode_mobil');
    }

    public function Alat()
    {
        return $this->belongsTo(Alat::class,'kode_alat');
    }
    
    public function PemakaianbanDetail()
    {
        return $this->hasMany(PemakaianbanDetail::class,'no_pemakaianban');
    }

     public function getDestroyUrlAttribute()
    {
        return route('pemakaianban.destroy', $this->no_pemakaianban);
    }

    public function getEditUrlAttribute()
    {
        return route('pemakaianban.edit',$this->no_pemakaianban);
    }

    public function getUpdateUrlAttribute()
    {
        return route('pemakaianban.update',$this->no_pemakaianban);
    }

    public function getDetailUrlAttribute()
    {
        return route('pemakaianban.detail',$this->no_pemakaianban);
    }

    public function getShowUrlAttribute()
    {
        return route('pemakaianban.show',$this->no_pemakaianban);
    }

    public function getCetakUrlAttribute()
    {
        return route('pemakaianban.cetak',$this->no_pemakaianban);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->total_item = 0;
            $query->kode_jurnal = '120';
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_pemakaianban = static::generateKode(request()->tanggal_pemakaianban);
            $query->no_journal = static::getjurnal(request()->tanggal_pemakaianban);
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

        $jurnal1 = '120'.'.'.$kode_company.$tahun.'.'.$bulan.'.';
        
        $cek_jurnal = self::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal != null){
            $leng = substr($cek_jurnal->no_journal,12,4);
        }else {
            $leng = 0;
        }

        $cek_jurnal2 = Pemakaian::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal2 != null ) {
            $leng2 = substr($cek_jurnal2->no_journal,12,4);
        }else{
           $leng2 = 0;
        }

        $lenger = max($leng, $leng2);
        $hasil = $jurnal1.sprintf('%04d', intval($lenger) + 1);
          
        return $hasil;
    }

    public static function generateKode($data)
    {
        $konek = static::konek();
        $kode = TransaksiSetup::where('kode_setup','013')->first();

        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_2 = strtoupper($kode->kode_transaksi);

        $period = Carbon\Carbon::parse($data)->format('my');

        $get_prefix_3 = $period;
        $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_pemakaianban', 'desc')->first();

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
