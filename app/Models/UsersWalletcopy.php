<?php

/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Utils\RPC;
use Illuminate\Support\Facades\Config;
use App\Models\Currency;

class UsersWalletcopy extends Model
{
    protected $table = 'users_wallet';
    public $timestamps = false;
    /*const CREATED_AT = 'create_time';*/

}
