<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailMessageUser extends Model
{
    protected $table = 'mail_message_user';
    
    protected $fillable = ['user_id', 'mail_message_id'];
    
    public $timestamps = false;
    
   
}