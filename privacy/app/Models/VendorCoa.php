<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use DB;
use Carbon;

class VendorCoa extends Model
{
    use AuditableTrait;
    
    protected $connection = 'mysql2';
    
    protected $table = 'vendor_coa';

    protected $primaryKey = 'id';

    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'kode_vendor',
        'kode_coa',
        'kode_company',
    ];

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'kode_coa');
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
