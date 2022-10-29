<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use APP\Models\Produk;


class MasterLokasi extends Model
{
    //
    use AuditableTrait;
    
    protected $connection = 'mysql2';

    protected $table = 'master_lokasi';

    protected $primaryKey = 'kode_lokasi';
    
    public $incrementing = false;

    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'alamat',
        'status',
        'level_lokasi',
    ];

    public function Produk()
    {
    return $this->hasMany(Produk::class,'kode_lokasi');
    }

    public function getDestroyUrlAttribute()
    {
        return route('masterlokasi.destroy', $this->kode_lokasi);
    }

    public function getEditUrlAttribute()
    {
        return route('masterlokasi.edit',$this->kode_lokasi);
    }

    public function getUpdateUrlAttribute()
    {
        return route('masterlokasi.update',$this->kode_lokasi);
    }

    public function getDetailUrlAttribute()
    {
        return route('masterlokasi.detail',$this->kode_lokasi);
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
