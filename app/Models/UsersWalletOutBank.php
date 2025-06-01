<?php

/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UsersWalletOutBank extends Model
{
    protected $table = 'users_wallet_out_bank';
    public $timestamps = false;
    protected $appends = [ 'account','account_number', 'real_name','bank_account','bank_name','status_name'];
    //节点等级
    const TO_BE_AUDITED = 1;
    const ToO_EXAMINE_ADOPT = 2;
    const ToO_EXAMINE_FAIL = 3;


    public function getAccountAttribute()
    {
        return $this->belongsTo(Users::class,  'user_id', 'id')->value('email');
    }
    public function getBankNameAttribute()
    {
        return $this->hasOne(UserCashInfo::class, 'user_id', 'user_id')->value('bank_name');
    }
    public function getBankAccountAttribute()
    {
        return $this->hasOne(UserCashInfo::class, 'user_id', 'user_id')->value('bank_account');
    }
    public function getRealNameAttribute()
    {
        return $this->hasOne(UserCashInfo::class, 'user_id', 'user_id')->value('real_name');
    }
    public function getCurrencyNameAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency')->value('name');
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
            $status='Apply for currency withdrawal';
        }else if($this->attributes['status']==2){
            $status='成功';
        }else if($this->attributes['status']==3){
            $status='fail';
        }
        return $status;
    }
}
