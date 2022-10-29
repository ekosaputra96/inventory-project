<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\PemakaianDetail;
use App\Models\TransaksiSetup;
use App\Models\Mobil;
use App\Models\JenisMobil;
use App\Models\Alat;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Penerimaan;

class tb_produk_history extends Model
{
    //
    use AuditableTrait;

    protected $table = 'tb_produk_history';

	protected $primaryKey = 'no_transaksi';

	public $incrementing = false;

	protected $fillable = [
    	'kode_produk',
        'no_transaksi',
        'tanggal_transaksi',
        'jam_transaksi',
        'qty_transaksi',
        'harga_transaksi',
        'total_transaksi',
        'kode_lokasi',
    ];

    public function Penerimaan()
    {
        return $this->hasMany(Penerimaan::class,'no_transaksi');
    }

    public function Mobil()
    {
        return $this->belongsTo(Mobil::class,'kode_mobil');
    }

    public function JenisMobil()
    {
        return $this->belongsTo(JenisMobil::class,'kode_jenis_mobil');
    }

    public function Alat()
    {
        return $this->belongsTo(Alat::class,'kode_alat');
    }

    public function Produk()
    {
        return $this->belongsTo(Produk::class,'kode_produk');
    }

    public function satuan()
    {
        return $this->belongsTo(satuan::class,'kode_satuan');
    }

    public function PemakaianDetail()
    {
        return $this->hasMany(PemakaianDetail::class,'no_pemakaian');
    }

    public function getUpdateUrlAttribute()
    {
        return route('tb_produk_history.update',$this->no_transaksi);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
