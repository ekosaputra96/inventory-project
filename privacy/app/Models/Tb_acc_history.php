<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Tb_acc_history extends Model
{
    //
    use AuditableTrait;

    protected $table = 'acc_history';

	public $incrementing = false;

	protected $fillable = [
        'transaction_type',
    	'account',
        'no_transaksi',
        'tanggal_transaksi',
        'no_reff',
        'payment_type',
        'other_recipient',
        'no_cekgiro',
        'due_date',
        'dbkr_type',
        'bulan',
        'tahun',
        'total',
        'no_journal',
        'status',
        'jam_transaksi',
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
