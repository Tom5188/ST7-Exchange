<?php

namespace App\Nova\Actions;

use App\Models\AccountLog;
use App\Models\UsersWallet;
use App\Models\UserLevelModel;
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
use App\Models\ChargeReq;
use App\Models\Setting;

class ApplyFilling extends Action
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
            $commissionRate = Setting::getValueByKey('commissionRate', 0);
            $user = Users::where('id',$model->user->id)->first();
            $redis = Redis::connection();
            $lock = new RedisLock($redis,'manual_charge'.$model->id,10);
            DB::beginTransaction();
            try{
                ChargeReq::where('id',$model->id)->update(['status'=>2,'updated_at'=>date('Y-m-d H:i:s')]);
                change_wallet_balance(
                    $legal,
                    2,
                    $model->amount,
                    AccountLog::WALLET_CURRENCY_IN,
                    '充值到账',
                    false,
                    0,
                    0,
                    serialize([
                    ]),
                    false,
                    true
                );
                
                $user->increment('recharge', $model->amount);
                if($user->superioragent > 0){
                    $wallet = UsersWallet::where('currency', 1)
                        ->where('user_id', $user->parent_id)
                        ->first();
                    $totle_price = $model->amount * $commissionRate;
                    $info = "1级会员{$user->account_number}充值返佣：" . $totle_price;
                    $result = change_wallet_balance($wallet, 2, $totle_price, AccountLog::AGENT_COMMISSION_MONEY, $info);
                    $user->increment('commission', $totle_price);
                }
                $user->save();
                // 计算用户升级
                UserLevelModel::checkUpgrade($model);
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
