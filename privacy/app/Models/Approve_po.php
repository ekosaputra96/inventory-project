<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Reopen;

class Approve_po extends Model
{
    //
    use AuditableTrait;

    protected $table = 'approve_po';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'no_pembelian',
        'approve_status',  
    ];

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