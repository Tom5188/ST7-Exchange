<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUsdtInfo extends Model
{
    protected $table = 'user_usdt_info';
    public $timestamps = false;
    protected $appends = ['account_number'];
    protected $casts = [
        'create_time' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
    
    public function digitalCurrency()
    {
        return $this->belongsTo(DigitalCurrencySet::class, 'digital_currency_id', 'id');
    }

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->attributes['create_time']));
    }
    
    public function getAccountNumberAttribute()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->value('account_number');
    }
}
