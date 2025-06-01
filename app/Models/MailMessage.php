<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class MailMessage extends Model
{
    protected $table = 'mail_message';
    protected $casts = [
        'user_id' => 'array', // 让 Laravel 自动转换 JSON
    ];
    
    // public function user()
    // {
    //     return $this->belongsTo(\App\Models\Users::class, 'user_id', 'id');
    // }
}