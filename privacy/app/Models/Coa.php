<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Coa extends Model
{
    //
    use AuditableTrait;
    protected $connection = 'mysql7';

    protected $table = 'coa';

    protected $primaryKey = 'kode_coa';

    public $incrementing = false;

    protected $fillable = [
        'kode_coa',
        'cost_center',
        'account',
        'ac_description',
        'cc_accum',
        'ac_accum',
        'level',
        'position',
        'normal_balance',
        'account_type',
        'sub_account',
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

    public function getDestroyUrlAttribute()
    {
        return route('coa.destroy', $this->kode_coa);
    }

    public function getEditUrlAttribute()
    {
        return route('coa.edit',$this->kode_coa);
    }

    public function getUpdateUrlAttribute()
    {
        return route('coa.update',$this->kode_coa);
    }
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_coa = static::generateKode(request()->kode_coa);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateKode($sumber_text)
    {
        $konek = static::konek();
        $primary_key = (new self)->getKeyName();
        $get_prefix_2 = strtoupper($sumber_text);
        $prefix_result = $get_prefix_2;
        $prefix_result_length = strlen($prefix_result);

        $lastRecort = Coa::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('kode_coa', 'desc')->first();

        if ( ! $lastRecort )
            $number = 0;
        else {
            $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
            if ($get_record_prefix === $prefix_result){
                $number = substr($lastRecort->{$primary_key},$prefix_result_length);
            }else {
                $number = 0;
            }

        }

        $result_number = $prefix_result . sprintf('%03d', intval($number) + 1);

        return $result_number ;
    }
}
