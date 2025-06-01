<?php

namespace App\Services;

use App\Models\UserWalletLog;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogService
{
    /**
     * 记录用户钱包日志
     * @param int $uid 用户 ID
     * @param int $wallet_type_id 钱包类型 ID
     * @param float|int $add 更改金额 支持加减+ -
     * @param int $from_uid 来自用户 ID
     * @param string $day 所属日期
     * @param int $from 来源
     * @param string $remark 备注
     * @param int $order_id 订单 ID
     * @throws BindingResolutionException
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    public function userWalletLog(int $uid, int $wallet_type_id, float|int $add, int $from_uid = 0, string $day = '2023-01-01', int $from = 0, string $remark = '', int $order_id = 0): void
    {
        $key = 'lock_' . $wallet_type_id . '_' . $uid; // 钱包缓存 key
        $check = $this->checkLock($key); // 检测钱包缓存
        if ($check) {
            \Cache::put($key, $key, 10); // 写入缓存 有效期 10 秒

            $UserWalletService = app()->make(UserWalletService::class); // 钱包服务初始化
            $old = $UserWalletService->checkBalance($uid, $wallet_type_id); // 获取刷新用户钱包余额

            if ($add >= 0) {
                $new = @bcadd($old, $add, 2); // 增加
            } elseif ($add < 0) {
                $new = @bcsub($old, abs($add), 2); // 减少
            }

            DB::beginTransaction(); // 开启事务
            try {
                // 写入数据到钱包和日志
                if ($UserWalletService->store($uid, $wallet_type_id, $add)) {
                    UserWalletLog::create([
                        'user_id' => $uid, // 用户 ID
                        'wallet_type_id' => $wallet_type_id, // 钱包类型 ID
                        'day' => $day, // 日期
                        'old' => $old, // 原数值
                        'add' => $add, // 新增
                        'new' => $new, // 新数值
                        'from' => $from, // 来源
                        'from_user_id' => $from_uid, // 来自用户 ID
                        'order_id' => $order_id, // 订单 ID
                        'remark' => $remark, // 备注
                    ]); // 记录钱包日志
                    DB::commit(); // 提交事务
                }
            } catch (\Exception $e) {
                DB::rollBack(); // 回滚事务
                // 异常处理
                Log::error(__METHOD__ . '|' . __METHOD__ . '-执行失败', ['error' => $e]);
            }
        }
    }

    /**
     * 检测缓存 key
     * @param string $key 缓存名称
     * @return true|void
     */
    public function checkLock(string $key)
    {
        $verifyData = \Cache::get($key);
        if (!$verifyData) {
            return true;
        } else {
            sleep(rand(1, 5)); // 随机延迟 N 秒
            $this->checkLock($key);
        }
    }
}
