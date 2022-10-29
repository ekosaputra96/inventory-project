<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\KategoriProduk;
use App\Models\Alat;
use App\Models\Merek;
use App\Models\Jenis;
use App\Models\Jasa;
use App\Models\Signature;
use App\Models\JenisMobil;
use App\Models\Catatanpo;
use App\Models\Opname;
use App\Models\ProdukCounter;
use App\Models\satuan;
use App\Models\Company;
use App\Models\PermintaanDetail;
use App\Models\PembelianDetail;
use App\Models\MasterLokasi;
use App\Models\Konversi;
use DB;
use Carbon;

class Produk extends Model
{
    //
    use AuditableTrait;

    protected $table = 'produk';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'kode_produk',
        'nama_produk',
        'tipe_produk',
        'kode_kategori',
        'kode_unit',
        'kode_merek',
        'kode_ukuran',
        'kode_alat',
        'kode_jasa',
        'kode_tipe',
        'no_opname',
        'kode_signature',
        'kode_jenis_mobil',
        'nomor',
        'kode_satuan',
        'partnumber',
        'harga_beli',
        'harga_jual',
        'hpp',
        'stok',
        'stat',
        'min_qty',
        'max_qty',
        'kode_company',
        'kode_lokasi',
        'kode_konversi',
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

    public function PermintaanDetail()
    {
    return $this->hasMany(PermintaanDetail::class,'kode_satuan');
    }

    public function PemakaianDetail()
    {
    return $this->hasMany(PemakaianDetail::class,'kode_satuan');
    }

    public function PenerimaanDetail()
    {
    return $this->hasMany(PenerimaanDetail::class,'kode_satuan');
    }

    public function OpnameDetail()
    {
    return $this->hasMany(OpnameDetail::class,'kode_satuan');
    }
    
    public function KategoriProduk()
    {
        return $this->belongsTo(KategoriProduk::class,'kode_kategori');
    }

    public function Unit()
    {
        return $this->belongsTo(Unit::class,'kode_unit');
    }

    public function Merek()
    {
        return $this->belongsTo(Merek::class,'kode_merek');
    }

    public function Konversi()
    {
    return $this->hasMany(Konversi::class,'kode_satuan');
    }

    public function Alat()
    {
        return $this->belongsTo(Alat::class,'kode_alat');
    }

    public function Jenis()
    {
        return $this->belongsTo(Alat::class,'kode_tipe');
    }

    public function Jasa()
    {
        return $this->belongsTo(Jasa::class,'kode_jasa');
    }

    public function Opname()
    {
        return $this->belongsTo(Opname::class,'no_opname');
    }

    public function Signature()
    {
        return $this->belongsTo(Signature::class,'kode_signature');
    }

    public function JenisMobil()
    {
        return $this->belongsTo(Signature::class,'kode_jenis_mobil');
    }

    public function Catatanpo()
    {
        return $this->belongsTo(Catatanpo::class,'nomor');
    }

    public function Ukuran()
    {
        return $this->belongsTo(Ukuran::class,'kode_ukuran');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function pembeliandetail()
    {
        return $this->belongsTo(PembelianDetail::class,'qty');
    }

    public function company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function MasterLokasi()
    {
        return $this->belongsTo(MasterLokasi::class,'kode_lokasi');
    }

     public function getDestroyUrlAttribute()
    {
        return route('produk.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('produk.edit',$this->id);
    }

    public function getShowUrlAttribute()
    {
        return route('produk.show',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('produk.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
       
        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_produk = static::generateNumber(request()->nama_produk);
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
        $lastRecort = self::on($konek)->orderBy('kode_produk', 'desc')->first();
        $prefix = strtoupper($sumber_text[0]) ;
        $primary_key = (new self)->getKeyName();


        if ( ! $lastRecort )
            $number = 0;
        else {
            $field = $lastRecort->{$primary_key} ;
            if (is_string($prefix) && $prefix[0] == is_string($field) && $field[0]){
                $number = substr($field, 2);
            }else {
                $number = 0;
            }
        }

        if($prefix != null){
            $produk_index = ProdukCounter::on($konek)->where('index', $prefix)->first();
            if($produk_index != null){
                $jumlah_final = $produk_index->jumlah + 1;

                $tabel_baru2 = [
                            'index'=>$prefix,
                            'jumlah'=>$jumlah_final,
                            ];

                $update = ProdukCounter::on($konek)->where('index', $prefix)->update($tabel_baru2);

                return  $prefix . sprintf('%05d', intval($jumlah_final));

            }
            else{
                $tabel_baru2 = [
                            'index'=>$prefix,
                            'jumlah'=>1,
                            ];

                $update = ProdukCounter::on($konek)->create($tabel_baru2);

                return  $prefix . sprintf('%05d', intval($number) + 1);

            }
            
        }
    }
}
