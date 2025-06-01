<?php

namespace App\Http\Controllers\Api;

use App\Enums\FromType;
use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    //
    public function test()
    {
        exit();
        $user = Users::find(1);
        $order_sn = '11111';

        $day = Carbon::now();
        $money = 10;
        $logService = app()->make(LogService::class); // 钱包服务初始化
        $remark = "奖励金额 " . $money . ' ,订单号 #' . $order_sn;
        $logService->userWalletLog($user->id, 1, $money, 0, $day, FromType::ORDER, $remark);

        exit();
        $link = nova_get_setting('link');
        $data['msg'] = $link;

        return response()->json($data, 200);
    }
}
