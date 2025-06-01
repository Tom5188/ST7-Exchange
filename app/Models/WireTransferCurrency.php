<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WireTransferCurrency extends Model
{

    protected $table = "wire_transfer_currency";

    public $timestamps = false;


    public function wireTransferAccount(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WireTransferAccount::class);
    }

}
