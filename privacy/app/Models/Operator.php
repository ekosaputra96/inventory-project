<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class Operator extends Model
{
    //
    use AuditableTrait;
    protected $connection = 'mysql_front_pbm';

    protected $table = 'operator';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public function Alat()
    {
        return $this->hasMany(Alat::class,'id');
    }

    public function LokasiAlat()
    {
        return $this->hasMany(LokasiAlat::class,'id');
    }

}

