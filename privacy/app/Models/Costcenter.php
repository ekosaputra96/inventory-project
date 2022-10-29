<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use DB;

class Costcenter extends Model
{
    //

    protected $connection = 'mysql4';

    protected $table = 'master_costcenter';

    protected $primaryKey = 'cost_center';

    public $incrementing = false;
}
