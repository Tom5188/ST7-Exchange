<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProjectOrderProfit extends BaseModel
{
    protected $table = "project_order_profit";
    use HasFactory;
    public $timestamps = false;
    protected $appends = [];
    protected $fillable = [
        'order_id',
        'user_id',
        'value',
        'created_time'
    ];
}
