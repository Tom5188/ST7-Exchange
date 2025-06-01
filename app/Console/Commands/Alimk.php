<?php

namespace App\Console\Commands;

use App\Currency;
use App\CurrencyQuotation;
use App\Http\Controllers\Api\AliMarketController;
use App\MarketHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Alimk extends Command
{
    protected $signature = 'call:kline';

    protected $description = '获取K线图数据';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = app(AliMarketController::class);
        
        $response = $controller->kline_history_ali();
        
        $this->info($response);
    }
}