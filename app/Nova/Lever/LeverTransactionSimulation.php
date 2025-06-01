<?php

namespace App\Nova\Lever;

use App\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class LeverTransactionSimulation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\LeverTransaction>
     */
    public static $model = \App\Models\LeverTransaction::class;

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

    public static $priority = 10;
    
    public static $polling = true;
    
    public static $pollingInterval = 10;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('Contract');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('simulation', 1);

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
            Text::make(__('AccountNumber'), 'account_number')->readonly(),
            Text::make(__('Currency Matches'), 'symbol')->readonly(),
            Text::make(__('TradeType'), 'type')->resolveUsing(function ($name) {
                if($name==1){
                    return '买入';
                }else{
                    return '卖出';
                }

            }),
             Text::make(__('Status'), 'status')->resolveUsing(function ($name) {
                 if($name==1){
                     return '交易中';
                 }else if($name==2){
                     return '平仓中';
                 }else if($name==3){
                     return '已平仓';
                 }else if($name==4){
                     return '已撤单';
                 }else if($name==0){
                     return '挂单中';
                 }else{
                     return '未知';
                 }

             }),
            Number::make(__('InitialPrice'), 'origin_price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('OpenPrice'), 'price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('PresentPrice'), 'update_price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('TargetProfitPrice'), 'target_profit_price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('StopLossPrice'), 'stop_loss_price')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('NumberOfHands'), 'share')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('Multiple'), 'multiple')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('InitialMargin'), 'origin_caution_money')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('CautionMoney'), 'caution_money')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('TradeFee'), 'trade_fee')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('Profits'), 'profits')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Text::make(__('CreateTime'), 'transaction_time')->readonly(),
            Text::make(__('CloseOutTime'), 'handle_time')->readonly(),
            Text::make(__('CompleteTime'), 'complete_time')->readonly(),
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
        return [ ];
    }
    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('LeverTransactionSimulation');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('LeverTransactionSimulation');
    }
}
