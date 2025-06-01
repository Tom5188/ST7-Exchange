<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    protected $table = 'conversion';
    public $timestamps = false;
    protected $appends = ['mobile','form_currency','to_currency'];

    public function getMobileAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }
    public function getFormCurrencyAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'form_currency_id')->value('name');
    }
    public function getToCurrencyAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'to_currency_id')->value('name');
    }



    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }



}
