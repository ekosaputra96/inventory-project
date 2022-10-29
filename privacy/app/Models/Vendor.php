<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Pembelian;
use App\Models\VendorCounter;
use DB;
use Carbon;

class Vendor extends Model
{
    //
    use AuditableTrait;
    protected $connection = 'mysql2';
    
    protected $table = 'vendor';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'nama_vendor',
        'nama_vendor_po',
        'alamat',
        'telp',
        'hp',
        'norek_vendor',
        'npwp',
        'nama_kontak',
        'kode_coa',
        'email',
        'status',
        'pkp',
    ];

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'kode_coa');
    }

    public function Pembelian()
    {
    return $this->hasMany(Pembelian::class,'id');
    }

     public function getDestroyUrlAttribute()
    {
        return route('vendor.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('vendor.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('vendor.update',$this->id);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
