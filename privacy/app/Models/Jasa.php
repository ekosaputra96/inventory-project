<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Jasa extends Model
{
    //
    use AuditableTrait;
    
    protected $connection = 'mysql2';
    
    protected $table = 'jasa';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'kode_produk',
        'nama_item',
        'keterangan',    
    ];

     public function getDestroyUrlAttribute()
    {
        return route('jasa.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('jasa.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('jasa.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->kode_produk = static::generateKode(request()->nama_item);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function generateKode($sumber_text2)
    {
        $prefix = 'J';
        $prefix2 = 'J'. strtoupper($sumber_text2[0]);
        $primary_key = (new self)->getKeyName();

        $lastRecort = self::where('kode_produk', 'like' , $prefix2.'%')->orderBy('created_at', 'desc')->first();

        // dd($primary_key);

        if ( ! $lastRecort )
            $number = 0;
        else {
            $field = $lastRecort->{$primary_key} ;
            if ($prefix[0] == $lastRecort->{$primary_key}[0]){
                $number = substr($field, 2);
            }else {
                $number = 0;
            }
        }
        
        if($prefix != null){
            $produk_index = JasaCounter::where('index', $prefix)->first();
            if($produk_index != null){
                $jumlah_final = $produk_index->jumlah + 1;

                $tabel_baru2 = [
                            'index'=>$prefix,
                            'jumlah'=>$jumlah_final,
                            ];

                $update = JasaCounter::where('index', $prefix)->update($tabel_baru2);

                return  $prefix . $prefix2 . sprintf('%04d', intval($jumlah_final));

            }
            else{
                $tabel_baru2 = [
                            'index'=>$prefix,
                            'jumlah'=>1,
                            ];
                $update = JasaCounter::create($tabel_baru2);

                return  $prefix . $prefix2 . sprintf('%04d', intval($number) + 1);

            }
            
        }
    }
}
