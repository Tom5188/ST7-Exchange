<?php

namespace App\Nova\Actions;

use App\Models\BorrowOrder;
use App\Models\AccountLog;
use App\Models\UsersWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Cache\RedisLock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\ChargeReq;


class ApplyFillingBorrow extends Action
{
    use InteractsWithQueue, Queueable;

    public $name='同意';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            if($model->status<>1){
                return Action::danger('无法操作');
            }
            $legal = UsersWallet::where("user_id", $model->user->id)
                ->where("currency", 1)
                ->lockForUpdate()
                ->first();
            if(!$legal){
                return Action::danger('找不到用户钱包');
            }
            
            $redis = Redis::connection();
            $lock = new RedisLock($redis,'borrow_charge'.$model->id,10);
            DB::beginTransaction();
            try{
                $money = $model->amount;
                BorrowOrder::with([])->where('id',$model->id)->update(['status'=>2,'updated_at'=>date('Y-m-d H:i:s'),'sub_time'=>date('Y-m-d H:i:s')]);
                change_wallet_balance(
                    $legal,
                    2,
                    $money,
                    AccountLog::USER_BORROW_ORDER_BUY,
                    '借贷申请通过,到账'.$money,
                    false,
                    0,
                    0,
                    serialize([
                    ]),
                    false,
                    true
                );
                $lock->release();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                return Action::danger($e->getMessage());
            }
        }
        return Action::message('操作成功');
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}