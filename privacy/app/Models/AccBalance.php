<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class AccBalance extends Model
{
    //
    use AuditableTrait;

    protected $table = 'account_balance';

    public $incrementing = false;

    protected $fillable = [
        'kode_company',
        'periode',
        'fiscalyear',
        'account',
        'cost_center',
        'beginning_balance',
        'debet',
        'kredit',
        'ending_balance',
        'kode_lokasi',
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
