<?php

namespace App\Nova\Currency;

use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use NormanHuth\NovaRadioField\Radio;

class CurrencyMatch extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CurrencyMatch>
     */
    public static $model = \App\Models\CurrencyMatch::class;

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
        'id',
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('BlockTrade');
    }


    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 3;


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
            Select::make(__('LegalId'),'legal_id')->options([
                1 => 'USDT',
            ])->default(1),
            BelongsTo::make(__('DealCurrency'),'currency', 'App\Nova\Currency\Currency'),
            Hidden::make('Category')->default(1),
            // Radio::make(__('IsDisplay'), 'is_display')
            //     ->options([
            //         '0' => __('Hide Content'),
            //         '1' => __('Show Content'),
            //     ]) ->inline(),
            Number::make(__('MicroMin'), 'lever_min_share'),
            Number::make(__('MicroMax'), 'lever_max_share'),
            Number::make(__('LeverShareFee'), 'lever_trade_fee')->step('any'),
            DateTime::make(__('CreateTime'),'created_at'),	# 只读字段
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
        return [];
    }
    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Counterparty');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Counterparty');
    }
}
