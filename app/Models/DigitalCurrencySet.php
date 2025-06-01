<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalCurrencySet extends Model
{

    protected $table = "digital_currency_set";

    public $timestamps = false;

    public function userUsdtInfo()
    {
        return $this->hasMany(UserUsdtInfo::class);
    }

}
