<?php

namespace App\Nova\Shareoption;

use App\Nova\Filters\DealStatus;
use App\Nova\Filters\DealType;
use App\Nova\Filters\MicroResult;
use App\Nova\Filters\MicroType;
use App\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Http\Requests\NovaRequest;
use KirschbaumDevelopment\Nova\InlineSelect;
use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\Number;

class MicroOrder extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\MicroOrder>
     */
    public static $model = \App\Models\MicroOrder::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';
    
    /**
     * The click action to use when clicking on the resource in the table.
     *
     * Can be one of: 'detail' (default), 'edit', 'select', 'preview', or 'ignore'.
     *
     * @var string
     */
    public static $clickAction = 'ignore';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user.account_number','currency.name'
    ];
    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 1;
    
    public static $trafficCop = false;
    
    public static $polling = true;
    
    public static $pollingInterval = 10;
    
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('SecondContract');
    }
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('simulation', 0);

    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $fields = [
            ID::make()->sortable(),
            Text::make(__('AccountNumber'), 'account')->readonly(),
            Text::make(__('Agreement'), 'symbol_name')->readonly(),
            Text::make(__('Type'), 'type_name')->readonly(),
            Status::make(__('TransactionStatus'), 'status_name')->loadingWhen(['交易中'])->failedWhen(['平仓中'])->readonly(),
            Number::make(__('Amount'), 'number')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('ServiceCharge'), 'fee')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Text::make(__('Result'), 'profit_result_name')->readonly(),
            Number::make(__('Profit'), 'fact_profits')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('OpenPrice'), 'open_price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('EndPrice'), 'end_price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            DateTime::make(__('OrderTime'), 'created_at')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),
            Text::make(__('Seconds'), 'seconds')->readonly(),
            Text::make(__('CloseOutTime'), 'handled_at')->displayUsing(function ($handled_at) {
                $handled_at = Carbon::parse($handled_at);
                $now = Carbon::now();
                if ($now >= $handled_at) {
                    return 0;
                }
                return $handled_at->diffInSeconds($now) . ' 秒';
            })->readonly()
        ];
    
        // 判断 `status_name` 是否为 '交易中'
        if ($this->status_name == '交易中') {
            // 否则使用 InlineSelect 并启用内联编辑
            $fields[] = InlineSelect::make(__('PreProfitResult'), 'pre_profit_result')
                ->options([
                    '1' => '盈利',
                    '0' => '不控',
                    '-1' => '亏损',
                ])
                ->displayUsingLabels()
                ->inlineOnIndex()
                ->enableOneStepOnIndex();
        } else {
            // 如果 status_name 为 '交易中', 使用 Select 并设置为只读
            $fields[] = Select::make(__('PreProfitResult'), 'pre_profit_result')
                ->options([
                    '1' => '盈利',
                    '0' => '不控',
                    '-1' => '亏损',
                ])
                ->displayUsingLabels()
                ->readonly();
        }
    
        return $fields;
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new MicroType(),
            new DealStatus(),
            new MicroResult()
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('SecondDeal');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('SecondDeal');
    }
}
