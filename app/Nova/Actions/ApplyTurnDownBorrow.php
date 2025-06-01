<?php

namespace App\Nova\Actions;

use App\Models\BorrowOrder;
use App\Models\UsersWallet;
use App\Models\AccountLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ApplyTurnDownBorrow extends Action
{
    use InteractsWithQueue, Queueable;

    public $name='拒绝';

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
            $borrow_desc=$fields->borrow_desc;
            $borroworder = BorrowOrder::where('id', $model->id)->first();
            
            $wallet = UsersWallet::where('currency', 1)
                ->where('user_id', $borroworder->user_id)
                ->first();
            $margin = $borroworder->amount * $borroworder->margin;
            $request = change_wallet_balance(
                $wallet,
                2,
                $margin,
                AccountLog::USER_BORROW_ORDER_BUY_MARGIN,
                '借贷保证金退回',
                false,
                0,
                0,
                serialize([
                ]),
                false,
                true
            );
            $borroworder->status = 4;
            $borroworder->borrow_desc = $borrow_desc;
            $borroworder->sub_time = date('Y-m-d H:i:s');
            $borroworder->save();
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
            Text::make(__('BorrowDesc'), 'borrow_desc'),
        ];
    }
}
