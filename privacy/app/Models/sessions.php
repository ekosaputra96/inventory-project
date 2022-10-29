<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\users;

class sessions extends Model
{
    //
    use AuditableTrait;

    protected $table = 'sessions';

    protected $fillable = [
        'user_id',
        'kode_company',
    ];

}