<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\AliMarketController;

class CallAliMarketTest extends Command
{
    // 命令名称
    protected $signature = 'call:alimarket';

    // 命令描述
    protected $description = 'Call the getTest method of AliMarketController';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 使用依赖注入解析控制器
        // app() 是 Laravel 的服务容器，它会自动解析 AliMarketController 并注入其依赖项
        $controller = app(AliMarketController::class);  // 通过 app() 解析控制器类

        // 调用 getTest 方法
        $response = $controller->getTest();

        // 输出返回的内容（假设 getTest 返回的是字符串或数组）
        $this->info($response); // 使用 info() 输出命令行信息
    }
}

