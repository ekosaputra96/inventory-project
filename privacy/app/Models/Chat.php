<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\User;
use App\Models\Company;


class Chat extends Model
{
    //
    use AuditableTrait;

    protected $table = 'users_chat';

    protected $fillable = [
    	'id_chat',
    	'from_id',
    	'to_id',
    	'chat',
    	'status',
    	'created_at',
    	'updated_at',
    	'created_by',
    	'updated_by',
    	'kode_company',
    	'kode_lokasi',
    ];

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function User()
    {
        return $this->belongsTo(User::class,'id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'SEND';
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->kode_company = Auth()->user()->kode_company;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
