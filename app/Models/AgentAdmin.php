<?php

namespace App\Models;

use App\Scopes\SiteScope;
use Illuminate\Database\Eloquent\Model;


class AgentAdmin extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'agent_admin';
    public $timestamps = false;

}
