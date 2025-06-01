<?php

/**
 * Created by PhpStorm.
 * Users: swl
 * Date: 2018/7/3
 * Time: 10:23
 */
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ChargeReq extends Model
{
    protected $table = 'charge_req';
    public $timestamps = false;
    protected $appends = [
        'account',
        'currency',
        'status_name'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'id')->withDefault();
    }

    public function getAccountAttribute()
    {
        return $this->belongsTo(Users::class,  'uid', 'id')->value('email');
    }

    public function getCurrencyAttribute()
    {
        return $this->belongsTo(Currency::class,  'currency_id', 'id')->value('name');
    }

    public function getStatusNameAttribute(): string
    {
        $status='';
        if($this->attributes['status']==1){
            $status='充值申请';
        }else if($this->attributes['status']==2){
            $status='充值完成';
        }else if($this->attributes['status']==3){
            $status='驳回申请';
        }
        return $status;
    }
}
