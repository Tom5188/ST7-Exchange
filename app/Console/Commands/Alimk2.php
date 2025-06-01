<?php

namespace App\Console\Commands;

use App\Currency;
use App\CurrencyQuotation;
use App\Http\Controllers\Api\AliMarketController;
use App\MarketHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Alimk2 extends Command
{
    protected $signature = 'call:huobikline';

    protected $description = '获取K线图数据';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // app() 是 Laravel 的服务容器，它会自动解析 AliMarketController 并注入其依赖项
        $controller = app(AliMarketController::class);  // 通过 app() 解析控制器类

        // 调用 kline_history 方法
        $response = $controller->kline_history_huobi();

        // 输出返回的内容（假设 kline_history 返回的是字符串或数组）
        $this->info($response); // 使用 info() 输出命令行信息
    }
}