<?php

namespace App\Nova\Currency;

use App\Nova\Resource;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency as Curr;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Query\Search\SearchableText;
use NormanHuth\NovaRadioField\Radio;
use OwenMelbz\RadioField\RadioButton;

class Currency extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Currency>
     */
    public static $model = \App\Models\Currency::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public static $showColumnBorders = true;
    
    /**
     * The click action to use when clicking on the resource in the table.
     *
     * Can be one of: 'detail' (default), 'edit', 'select', 'preview', or 'ignore'.
     *
     * @var string
     */
    public static $clickAction = 'ignore';


    /**
     * The visual style used for the table. Available options are 'tight' and 'default'.
     *
     * @var string
     */
    public static $tableStyle = 'default';

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
    public static $priority = 1;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name','CurrencyType.name'
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

            Text::make(__('Name'), 'name'),

            Text::make(__('Alias'), 'alias'),

            Select::make(__('Platform'),'platform')->options([
                0 => '火币',
                1 => 'MT5',
            ])->onlyOnForms()
              ->displayUsingLabels(),

            Image::make(__('Logo'), 'logo'),

            BelongsTo::make(__('CurrencyType'),'CurrencyType', 'App\Nova\Currency\CurrencyType'),

            Number::make(__('Sort'), 'sort'),

            Select::make(__('IsDisplay'), 'is_display')
                ->options([
                    '0' => __('Hide Content'),
                    '1' => __('Show Content'),
                ]) ->displayUsingLabels(),
//            Number::make(__('MinNumber'), 'min_number')->step('any'),
//
//            Number::make(__('MaxNumber'), 'max_number')->step('any'),

//            Number::make(__('Rate'), 'rate')->placeholder('百分比')->step('any'),

            Number::make(__('MicroShareFee'), 'micro_trade_fee')->placeholder('百分比')->step('any'),

            Number::make(__('EachPiece'), 'each_piece'),

            Text::make('erc20', 'address_erc')->onlyOnForms(),

            Text::make('trc20', 'address_omni')->onlyOnForms(),

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
        return __('Currency');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Currency');
    }
}
