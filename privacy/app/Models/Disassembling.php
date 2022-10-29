<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use Carbon;
use DB;

class Disassembling extends Model
{
    //
    use AuditableTrait;

    protected $table = 'disassembling';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tanggal',
        'kode_produk',
        'partnumber',
        'hpp',
        'qty_disassembling',
        'keterangan',
        'total_item',
        'status',
        'kode_jurnal',
        'no_journal',
    ];

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function getDestroyUrlAttribute()
    {
        return route('disassembling.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('disassembling.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('disassembling.update',$this->id);
    }

    public function getDetailUrlAttribute()
    {
        return route('disassembling.detail',$this->id);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->kode_jurnal = '200';
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_journal = static::getjurnal(request()->tanggal);
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

        $jurnal1 = '200'.'.'.$kode_company.$tahun.'.'.$bulan.'.';
        
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
}
