<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyKx extends BaseModel
{
protected $table = "currency_kx";
    public $timestamps = false;
    protected $casts = [
        'data_start' => 'datetime',
        'data_end' => 'datetime',
        'put_start' => 'datetime',
        'put_end' => 'datetime',
    ];
    
    public function currency()
	{
		return $this->belongsTo(Currency::class, "symbol_id", "id")->withDefault();
	}
}
