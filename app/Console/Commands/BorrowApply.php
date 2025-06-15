<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BorrowOrder;
use App\Models\UsersWallet;
use App\Models\AccountLog;
use Illuminate\Support\Facades\DB;

class BorrowApply extends Command
{
    protected $signature = 'borrow:apply';
    protected $description = '借款强制还款';

    public function handle()
    {
        $today = now();

        $borrowOrders = BorrowOrder::where('status', 2)->get(); // 进行中的借款

        foreach ($borrowOrders as $order) {
            $dueDate = \Carbon\Carbon::parse($order->sub_time)->addDays($order->lock_dividend_days)->addDay(); // 到期后第二天

            if ($today->lt($dueDate)) {
                continue; // 还没到强制还款时间
            }

            DB::beginTransaction();
            try {
                $wallet = UsersWallet::where('currency', 1)
                    ->where('user_id', $order->user_id)
                    ->lockForUpdate()
                    ->first();

                throw_unless($wallet, new \Exception('用户钱包不存在'));

                $amount = $order->amount + $order->sum_profit;

                // 扣除借款本金 + 利息
                change_wallet_balance(
                    $wallet,
                    2,
                    -$amount,
                    AccountLog::USER_BORROW_ORDER_RETURN,
                    '借贷还款扣除借贷金额'.$order->amount.'和利息'.$order->sum_profit
                );

                // 退还保证金
                $margin = $order->amount * $order->margin;
                change_wallet_balance(
                    $wallet,
                    2,
                    $margin,
                    AccountLog::USER_BORROW_ORDER_BUY_MARGIN,
                    '借贷保证金退回'
                );

                // 更新订单状态
                $order->update([
                    'is_return' => 1,
                    'status' => 3,
                    'updated_at' => now(),
                ]);

                DB::commit();

                $this->info("用户{$order->user_id}借款ID {$order->id} 强制还款完成");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("处理借款ID {$order->id} 失败：".$e->getMessage());
            }
        }
    }
}
