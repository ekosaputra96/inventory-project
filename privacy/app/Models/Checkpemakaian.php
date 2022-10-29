<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\tb_item_bulanan;

class Checkpemakaian extends Model
{
    //
    use AuditableTrait;

    public $incrementing = false;

    protected $fillable = [
        'periode',
    ];
}
