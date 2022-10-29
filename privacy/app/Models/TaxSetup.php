<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;


class TaxSetup extends Model
{
    //

    use AuditableTrait;

    protected $connection = 'mysql2';
    
    protected $table = 'tax_setups';

    protected $primaryKey = 'id_pajak';

    public $incrementing = false;

    protected $fillable = [
        'id_pajak',
        'kode_pajak',
        'nama_pajak',
        'nilai_pajak',
        'tgl_berlaku',
    ];

    

    public function getDestroyUrlAttribute()
    {
        return route('taxsetup.destroy', $this->id_pajak);
    }

    public function getEditUrlAttribute()
    {
        return route('taxsetup.edit',$this->id_pajak);
    }

    public function getUpdateUrlAttribute()
    {
        return route('taxsetup.update',$this->id_pajak);
    }

    public static function boot()
    {
        parent::boot();
       
        static::creating(function ($query){
           $query->kode_company = Auth()->user()->kode_company;
           $query->id_pajak = static::generateKode(Auth()->user()->id_pajak);
           $query->created_by = Auth()->user()->name;
           $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateKode($sumber_text)
    {
        $primary_key = (new self)->getKeyName();
        $get_prefix_2 = strtoupper($sumber_text);
        $prefix_result = $get_prefix_2;
        $prefix_result_length = strlen($prefix_result);

        $lastRecort = TaxSetup::where($primary_key,'like',$prefix_result.'%')->orderBy('id_pajak', 'desc')->first();

        if ( ! $lastRecort )
            $number = 0;
        else {
            $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
            // dd($get_record_prefix, $prefix_result);
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
