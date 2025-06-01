<?php

/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UsersWalletOut extends Model
{
    protected $table = 'users_wallet_out';
    public $timestamps = false;
    protected $appends = [   'account', 'account_number','real_name','status_name'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
    //节点等级
    const TO_BE_AUDITED = 1;
    const ToO_EXAMINE_ADOPT = 2;
    const ToO_EXAMINE_FAIL = 3;

    public function currency()
    {
        return $this->belongsTo(Currency::class,  'currency', 'id');
    }

    public function getAccountAttribute()
    {
        return $this->belongsTo(Users::class,  'user_id', 'id')->value('email');
    }
    public function getRealNameAttribute()
    {
        return $this->hasOne(UserCashInfo::class, 'user_id', 'user_id')->value('real_name');
    }


    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }


    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }

    public function getStatusNameAttribute(): string
    {
        $status='';
        if($this->attributes['status']==1){
            $status='提款申请';
        }else if($this->attributes['status']==2){
            $status='提款成功';
        }else if($this->attributes['status']==3){
            $status='提款失败';
        }
        return $status;
    }
}
