<?php

namespace App\Nova\Actions;

use App\Models\AccountLog;
use App\Models\UserLevelModel;
use App\Models\UsersWallet;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Illuminate\Cache\RedisLock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;
use App\Models\UsersWalletOut;
use App\Models\ChargeReq;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserRecharge extends Action
{
    use InteractsWithQueue, Queueable;

    public function name(){
        return __('Recharge');
    }

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
            $amount = $fields->number;
            $remark = "";
            if($fields->type == 2){
                $amount = -$fields->number;
                $remark = "平台提现";
            }
            if($fields->type == 1){
                $remark = "平台彩金";
            }
            if($fields->type == 0){
                $remark = "平台充值";
            }
            $legal = UsersWallet::where("user_id", $model->id)
                ->where("currency", 1)
                ->lockForUpdate()
                ->first();
            if(!$legal){
                return Action::danger('找不到用户钱包');
            }
            DB::beginTransaction();
            try{
                
               
            if($fields->type == 1 || $fields->type == 0){
                ChargeReq::unguard();
                ChargeReq::create([
                    'uid' => $model->id,
                    'amount' => $fields->number,
                    'user_account' => '',
                    'status' => 2,
                    'currency_id' => 1,
                    'remark' => $remark,
                    'type' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'give' => $fields->number
                ]);
                change_wallet_balance(
                    $legal,
                    2,
                    $fields->number,
                    AccountLog::WALLET_CURRENCY_IN,
                    $remark,
                    false,
                    0,
                    0,
                    serialize([
                    ]),
                    false,
                    true
                );
            }
            if($fields->type == 2){
                UsersWalletOut::unguard();
                UsersWalletOut::create([
                    'user_id' => $model->id,
                    'currency' => 'USDT',
                    'address' => '****',
                    'number' => $fields->number,
                    'rate' => 0.00,
                    'status' => 2,
                    'real_number' => 0.00,
                    'remark' => $remark,
                    'txid' => '1',
                    'created_at' =>date('Y-m-d H:i:s'),
                    'create_time' => time()
                ]);
                change_wallet_balance(
                    $legal,
                    2,
                    -$fields->number,
                    AccountLog::WALLET_CURRENCY_IN,
                    $remark,
                    false,
                    0,
                    0,
                    serialize([
                    ]),
                    false,
                    true
                );
            }
            
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
        return [
            Number::make(__('Amount'), 'number'),
            Select::make(__('Type'),'type')->options([
                0 => '充值',
                // 1 => '彩金',
                2 => '提现',
            ])
        ];
    }
}
