<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalCurrencyAddress extends Model
{

    protected $table = "digital_currency_address";

    public $timestamps = false;

    protected $appends = ['name'];

    public function getNameAttribute(){
        return $this->currency()->value('name');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }


}
