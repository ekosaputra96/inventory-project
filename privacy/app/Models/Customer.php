<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Penjualan;
use App\Models\CustomerCounter;

class customer extends Model
{
    //
    use AuditableTrait;
  
    protected $table = 'customer';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_customer',
        'nama_customer_po',      
        'alamat',
        'kota',
        'kode_pos',
        'telp',
        'fax',
        'hp',
        'npwp',
        'nama_kontak',
        'no_kode_pajak',
        'kode_coa',
        'email',
        'status',
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

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'kode_coa');
    }

     public function getDestroyUrlAttribute()
    {
        return route('customer.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('customer.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('customer.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            //Coa Piutang Usaha
            $get_setup = Systemsetup::find('19');
            $get_coa = $get_setup->kode_setup;

            $query->kode_coa = $get_coa;

            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
