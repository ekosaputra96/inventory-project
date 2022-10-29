<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use DB;

class Jurnal extends Model
{
    //
    use AuditableTrait;

    protected $connection = 'mysql2';

    protected $table = 'jurnal';

    protected $primaryKey = 'kode_jurnal';

    public $incrementing = false;
}
