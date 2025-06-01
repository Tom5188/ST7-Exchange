<?php

namespace App\Nova\Actions;

use App\Models\AccountLog;
use App\Models\UsersWallet;
use App\Models\ProjectOrder;
use App\Models\Users;
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
use App\Models\Setting;

class ProjectReturn extends Action
{
    use InteractsWithQueue, Queueable;

    public $name='赎回';

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
            $legal = UsersWallet::where("user_id", $model->user_id)
                ->where("currency", 1)
                ->lockForUpdate()
                ->first();
            if(!$legal){
                return Action::danger('找不到用户钱包');
            }
            $user = Users::where('id',$model->user_id)->first();
            DB::beginTransaction();
            try{
                ProjectOrder::where('id',$model->id)->update(['status'=>4,'updated_at'=>date('Y-m-d H:i:s')]);
                $profit = $model->day_profit * $model->already_ettled_day;
                change_wallet_balance(
                    $legal,
                    2,
                    $model->amount + $profit,
                    AccountLog::USER_PROJECT_ORDER_RETURN,
                    '订单退订，退还本金'.$model->amount.'，结算'.$model->already_ettled_day.'(天)利息'.$profit,
                    false,
                    0,
                    0,
                    serialize([
                    ]),
                    false,
                    true
                );
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
