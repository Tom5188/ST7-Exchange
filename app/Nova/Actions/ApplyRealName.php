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
use App\Models\UserReal;

class ApplyRealName extends Action
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
            if($model->review_status<>1){
                return Action::danger('无法操作');
            }

            $user = Users::where('id',$model->user->id)->first();

            DB::beginTransaction();
            try{
                UserReal::where('id',$model->id)->update(['review_status'=>2,'review_time'=>date('Y-m-d H:i:s')]);
                $user->is_realname = 2;
                $user->save();
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
