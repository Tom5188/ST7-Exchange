<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use App\Nova\Actions\ProjectReturn;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Carbon\Carbon;

class ProjectOrder extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ProjectOrder>
     */
    public static $model = \App\Models\ProjectOrder::class;

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
        'user.account_number'
    ];
    
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make(__('AccountNumber'), 'account')->readonly(),
            Text::make(__('ProjectName'), 'project_name')->readonly(),
            Number::make(__('Amount'), 'amount')->step('any')->readonly(),
            Number::make(__('lockDividendDays'), 'lock_dividend_days')->step('any')->readonly(),
            Number::make(__('dayProfit'), 'day_profit')->step('any')->readonly(),
            Number::make(__('dayProfitAndsumProfit'), 'sum_profit')->step('any')->displayUsing(function ($value) {
                $already_ettled_day = $this->already_ettled_day;
                $day_profit = $this->day_profit;
                return $already_ettled_day * $day_profit . ' / ' . $value * 1;
            })->readonly(),
            Badge::make(__('Status'),'status_name')->map([//danger
                '交易中' => 'info',
                '申请退款' => 'warning',
                '交易完成' => 'success',
                '提前赎回' => 'danger'
            ]),
            DateTime::make(__('subTime'), 'sub_time')->readonly()->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d') : '';
            }),	# 只读字段
            
            Number::make(__('AlreadyEttledDay'), 'already_ettled_day')->step('any')->readonly(),
            // DateTime::make(__('DoTime'), 'interest_gen_next_time')->readonly()->displayUsing(function ($value) {
            //     return $value ? $value->format('Y-m-d H:i:s') : '';
            // }),	# 只读字段
            DateTime::make(__('CompleteTime'), 'end_time')->readonly()->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d') : '';
            })
        ];
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
        return [];
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
        return [
            (new ProjectReturn)->showInline(),
        ];
    }
    
    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Project Orders');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Project Orders');
    }
}
