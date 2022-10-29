<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use Carbon;

class TypeCargo extends Model
{
    //

    use AuditableTrait;

    protected $connection = 'mysql_front_pbm';
    
    protected $table = 'type_cargo';

	protected $primaryKey = 'id';

	public $incrementing = false;
}
