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

class Kasbon extends Model
{
    //
    use AuditableTrait;

    protected $table = 'permintaan_kasbon';

    protected $primaryKey = 'no_pkb';

    public $incrementing = false;

    // public $timestamps = false;

    protected $fillable = [
        'no_pkb',
        'nama_pemohon',
        'tanggal_permintaan',
        'nilai',
        'keterangan',
        'status',
    ];

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Vendor()
    {
        return $this->belongsTo(Vendor::class,'kode_vendor');
    }

     public function getDestroyUrlAttribute()
    {
        return route('kasbon.destroy', $this->no_pkb);
    }

    public function getEditUrlAttribute()
    {
        return route('kasbon.edit',$this->no_pkb);
    }

    public function getUpdateUrlAttribute()
    {
        return route('kasbon.update',$this->no_pkb);
    }

    public function getShowUrlAttribute()
    {
        return route('kasbon.show',$this->no_pkb);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->kode_company = Auth()->user()->kode_company;
            $query->no_pkb = static::generateKode(request()->tanggal_permintaan);
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

    public static function generateKode($data)
    {
        $user = Auth()->user()->level;
        $konek = static::konek();
        
        $kode = TransaksiSetup::where('kode_setup','028')->first();

        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_2 = strtoupper($kode->kode_transaksi);

        $period = Carbon\Carbon::parse($data)->format('ym');

        $get_prefix_3 = $period;
        $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('no_pkb', 'desc')->first();

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
