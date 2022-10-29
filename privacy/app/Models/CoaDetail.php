<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;

class CoaDetail extends Model
{
    //
    use AuditableTrait;
    protected $connection = 'mysql7';

    protected $table = 'coa_detail';

    protected $primaryKey = 'kode_coa';

    public $incrementing = false;

}
