<?php

namespace App\Models;

use App\Scopes\SiteScope;
use Illuminate\Database\Eloquent\Model;


class AgentRole extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'agent_role';
    public $timestamps = false;

}
