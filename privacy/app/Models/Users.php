<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use Carbon;

class Users extends Model
{
    //

    use AuditableTrait;

    protected $connection = 'mysql';
    
    protected $table = 'users';

	protected $primaryKey = 'id';

	public $incrementing = false;
}
