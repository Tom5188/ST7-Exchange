<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WireTransferAccount extends Model
{

    protected $table = "wire_transfer_account";

    public $timestamps = false;



    public function wireTransferCurrency()
    {
        return $this->belongsTo(WireTransferCurrency::class, 'wire_transfer_id', 'id');
    }
}
