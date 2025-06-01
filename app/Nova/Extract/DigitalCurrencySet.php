<?php

namespace App\Nova\Extract;

use App\Nova\Actions\ApplyFilling;
use App\Nova\Actions\ApplyTurnDown;
use App\Nova\Resource;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class DigitalCurrencySet extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ChargeReq>
     */
    public static $model = \App\Models\DigitalCurrencySet::class;

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
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('Wallet');
    }


    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 2;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user.account_number',
    ];

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
            Text::make(__('CurrencyName'), 'name'),
            Text::make(__('Protocol'), 'agreement'),
            Number::make(__('ExchangeRate'), 'exchange_rate')->step('any'),
            Number::make(__('Rate'), 'rate')->step('any'),
            Select::make(__('ServiceChargeType'),'service_charge_type')->options([
                1 => '百分比',
                2 => '固定金额',
            ])->onlyOnForms()->displayUsingLabels(),
            Number::make(__('UsdtNumber'), 'number'),
            Number::make(__('MinNumber'), 'min_number')->step('any'),
            Number::make(__('MaxNumber'), 'max_number')->step('any'),
            Boolean::make(__('IsDisplay'),'is_display')
                ->trueValue(1)
                ->falseValue(0),

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
        ];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('DigitalCurrencySet');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('DigitalCurrencySet');
    }
}
