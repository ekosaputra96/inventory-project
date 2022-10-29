<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;


class TransaksiSetup extends Model
{
    //

    use AuditableTrait;

    protected $connection = 'mysql2';

    protected $table = 'transaksi_setups';

	protected $primaryKey = 'kode_setup';

	public $incrementing = false;

	protected $fillable = [
    	'kode_setup',
    	'kode_transaksi',
    	'nama_transaksi',
    ];
    
     public function getDestroyUrlAttribute()
    {
        return route('transaksisetup.destroy', $this->kode_setup);
    }

    public function getEditUrlAttribute()
    {
        return route('transaksisetup.edit',$this->kode_setup);
    }

    public function getUpdateUrlAttribute()
    {
        return route('transaksisetup.update',$this->kode_setup);
    }
    
    public static function boot()
    {
        parent::boot();
       
        static::creating(function ($query){
           $query->kode_setup = static::generateKode(Auth()->user()->kode_setup);
           $query->kode_company = Auth()->user()->kode_company;
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

        $lastRecort = TransaksiSetup::where($primary_key,'like',$prefix_result.'%')->orderBy('kode_setup', 'desc')->first();

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
