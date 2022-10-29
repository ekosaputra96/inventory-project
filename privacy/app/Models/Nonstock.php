<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Nonstock extends Model
{
    //
    use AuditableTrait;
    
    protected $connection = 'mysql2';
    
    protected $table = 'nonstock';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_item',
        'coa',    
        'cost_center',
    ];

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'coa');
    }

    public function getDestroyUrlAttribute()
    {
        return route('nonstock.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('nonstock.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('nonstock.update',$this->id);
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
