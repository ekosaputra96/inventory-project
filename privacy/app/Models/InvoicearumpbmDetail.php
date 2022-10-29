<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Cashbankin;
use App\Models\Coa;


class InvoicearumpbmDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'invoicearumpbm_detail';

	public $incrementing = true;

	protected $fillable = [
        'no_invoice',
    	'kode_coa',
        'keterangan',
        'qty',
        'harga_satuan',
        'sub_total',
        'id',
    ];

    protected $appends = ['destroy_url','edit_url'];

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'kode_coa');
    }

    public function getDestroyUrlAttribute()
    {
        return route('invoicearumpbmdetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('invoicearumpbmdetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('invoicearumpbmdetail.update',$this->id);
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
