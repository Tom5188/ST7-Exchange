<?php
/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UserReal extends Model
{
    protected $table = 'user_real';
    public $timestamps = false;
    protected $hidden = [];
    protected $appends = ['account', 'status'];

    protected $casts = [
        'create_time' => 'datetime',
    ];
    
    protected static function booted()
    {
        static::deleting(function ($userReal) {
            $user = $userReal->user;
            $user->is_realname = 1;
            $user->save();
        });
    }

    public function getCreateTimeAttribute()
    {
        return  $this->attributes['create_time'];

    }
    public function getAccountAttribute()
    {
        return $this->belongsTo(Users::class,  'user_id', 'id')->value('account_number');

    }
    public function getStatusAttribute()
    {
        $review_status = $this->attributes['review_status'];
        if ($review_status == 1) {
            return '未审核';
        }elseif ($review_status == 2) {
            return '已审核';
        }elseif ($review_status == 3) {
            return '已拒绝';
        }
    }
    
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

}
