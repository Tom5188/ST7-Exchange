<?php

namespace App\Nova\Actions;


use App\Models\AccountLog;
use App\Models\Currency;
use App\Models\UsersWallet;
use App\Models\UserLevelModel;
use App\Models\UsersWalletOut;
use App\Models\UsersWalletOutBank;
use Illuminate\Bus\Queueable;
use Illuminate\Cache\RedisLock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\ChargeReq;


class ExtractRefuseBank extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = '驳回';

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            if ($model->status <> 1) {
                return Action::danger('无法操作');
            }
            $id = $model->id;
            $notes=$fields->notes;
            if (!$id) {
                return Action::danger('参数错误');
            }

            try {
                DB::beginTransaction();
                $wallet_out = UsersWalletOutBank::where('status', '<=', 1)->lockForUpdate()->findOrFail($id);
                $number = $wallet_out->number;
                $user_id = $wallet_out->user_id;
                $user_wallet = UsersWallet::where('user_id', $user_id)->where('currency', 1)->lockForUpdate()->first();
                $wallet_out->status = 3;//提币失败状态
                $wallet_out->notes = $notes;//反馈的信息

                $wallet_out->update_time = time();
                $wallet_out->save();
                $change_result = change_wallet_balance($user_wallet, 2, -$number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额减少', true);
                if ($change_result !== true) {
                    return Action::danger($change_result);
                }
                $change_result = change_wallet_balance($user_wallet, 2, $number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额撤回');
                if ($change_result !== true) {
                    return Action::danger($change_result);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return Action::danger($e->getMessage());
            }
        }
        return Action::message('操作成功');
    }

    /**
     * Get the fields available on the action.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Notes'), 'notes'),
        ];
    }


}
