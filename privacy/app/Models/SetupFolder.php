<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use DB;
use Carbon;

class SetupFolder extends Model
{
    use AuditableTrait;
    
    protected $connection = 'mysql2';
    protected $table = 'setup_folder';
    protected $primaryKey = 'id';

    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'keterangan',
        'folder',
        'subfolder',
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
