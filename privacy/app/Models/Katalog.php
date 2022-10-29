<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class Katalog extends Model
{
    //
    use AuditableTrait;

    protected $table = 'katalog';

    protected $primaryKey = 'kode_item';

    public $incrementing = false;

    protected $fillable = [
        'kode_item',
        'partnumber',
        'nama_item',
        'nama_item_en',
        'tipe',
        'ic',
        // 'id',
        'kode_company',
        

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
        }
        return $koneksi;
    }

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function getDestroyUrlAttribute()
    {
        return route('katalog.destroy', $this->kode_item);
    }

    public function getEditUrlAttribute()
    {
        return route('katalog.edit',$this->kode_item);
    }

    public function getUpdateUrlAttribute()
    {
        return route('katalog.update',$this->kode_item);
    }
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_item = static::generateKode(request());
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateKode($data)
    {
        $konek = static::konek();
        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_3 = date('my');
        $prefix_result = $get_prefix_1.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('kode_item', 'desc')->first();

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
        
        $result_number = $prefix_result . sprintf('%05d', intval($number) + 1);

        return $result_number ;
    }
}
