<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class Unit extends Model
{
    //
    use AuditableTrait;
    
    protected $connection = 'mysql2';

    protected $table = 'unit';

    protected $primaryKey = 'kode_unit';

    public $incrementing = false;

    protected $fillable = [
        'kode_unit',
        'nama_unit',
    ];

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
