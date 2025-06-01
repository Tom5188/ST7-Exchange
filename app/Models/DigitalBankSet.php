<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalBankSet extends Model
{

    protected $table = "digital_bank_set";

    public $timestamps = false;


    public function userCashInfo()
    {
        return $this->hasMany(UserCashInfo::class);
    }


}
