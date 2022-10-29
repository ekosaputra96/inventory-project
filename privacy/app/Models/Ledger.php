<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Ledger extends Model
{
    //
    use AuditableTrait;

    protected $table = 'ledger';

    public $incrementing = false;

    protected $fillable = [
        'kode_company',
        'tahun',
        'periode',
        'account',
        'cost_center',
        'no_journal',
        'journal_date',
        'db_cr',
        'reference',
        'no_jo',
        'keterangan',
        'debit',
        'kredit',
        'kode_lokasi',
    ];

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'account');
    }
    
    public function Costcenter()
    {
        return $this->belongsTo(Costcenter::class,'cost_center');
    }

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
