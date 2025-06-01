<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyOpening extends Model
{
    use HasFactory;

    protected $table = "currency_openings";

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
