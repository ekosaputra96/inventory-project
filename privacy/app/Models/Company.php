<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;
use App\Models\Permintaan;
use App\Models\Pemakaian;
use App\Models\Pembelian;
use App\Models\Penerimaan;
use App\Models\Memo;
use App\User;

class Company extends Model
{
    //
    use AuditableTrait;

    protected $connection = 'mysql2';
    
    protected $table = 'company';

	protected $primaryKey = 'kode_company';

	public $incrementing = false;

	protected $fillable = [
    	'kode_company',
        'nama_company',
        'alamat',
        'telp',
        'npwp',
        'status',
        'kode_lokasi',
    ];

    public function Produk()
    {
    return $this->hasMany(Produk::class,'kode_company');
    }

    public function Permintaan()
    {
    return $this->hasMany(Permintaan::class,'kode_company');
    }

    public function Pemakaian()
    {
        return $this->hasMany(Pemakaian::class,'kode_company');
    }

    public function Pembelian()
    {
        return $this->hasMany(Pembelian::class,'kode_company');
    }
    public function Penerimaan()
    {
        return $this->hasMany(Penerimaan::class,'kode_company');
    }
    public function Memo()
    {
        return $this->hasMany(Memo::class,'kode_company');
    }
    public function User()
    {
        return $this->hasOne(User::class,'kode_company');
    }

     public function getDestroyUrlAttribute()
    {
        return route('company.destroy', $this->kode_company);
    }

    public function getEditUrlAttribute()
    {
        return route('company.edit',$this->kode_company);
    }

    public function getUpdateUrlAttribute()
    {
        return route('company.update',$this->kode_company);
    }
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->kode_company = static::generateNumber(request()->nama_company);
            $query->kode_lokasi = auth()->user()->kode_lokasi;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateNumber($sumber_text)
    {
        $lastRecort = self::orderBy('kode_company', 'desc')->first();
        $prefix = strtoupper($sumber_text);
        $primary_key = (new self)->getKeyName();

        if ( $lastRecort == null )
            $number = 0;
        else {
            $field = $lastRecort->{$primary_key};

            if ($prefix[0] != $lastRecort->{$primary_key}[0]){
                $number = $field;
            }else {
                $number = 0;
            }
        }

        return sprintf('%02d', intval($number) + 1);
    }
}
