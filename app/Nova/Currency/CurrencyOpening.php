<?php

namespace App\Nova\Currency;

use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CurrencyOpening extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\CurrencyOpening>
     */
    public static $model = \App\Models\CurrencyOpening::class;

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
    public static $priority = 4;
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
            BelongsTo::make(__('CurrencyName'),'Currency', 'App\Nova\Currency\Currency'),
            Text::make(__('MonBegin'),'mon_begin')->default('00:00:00'),
            Text::make(__('MonEnd'),'mon_end')->default('23:59:59'),
            Text::make(__('TueBegin'),'tue_begin')->default('00:00:00'),
            Text::make(__('TueEnd'),'tue_end')->default('23:59:59'),
            Text::make(__('WedBegin'),'wed_begin')->default('00:00:00'),
            Text::make(__('WedEnd'),'wed_end')->default('23:59:59'),
            Text::make(__('ThuBegin'),'thu_begin')->default('00:00:00'),
            Text::make(__('ThuEnd'),'thu_end')->default('23:59:59'),
            Text::make(__('FinBegin'),'fin_begin')->default('00:00:00'),
            Text::make(__('FinEnd'),'fin_end')->default('23:59:59'),
            Text::make(__('SatBegin'),'sat_begin')->default('00:00:00'),
            Text::make(__('SatEnd'),'sat_end')->default('23:59:59'),
            Text::make(__('SunBegin'),'sun_begin')->default('00:00:00'),
            Text::make(__('SunEnd'),'sun_end')->default('23:59:59'),
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
        return __('CurrencyOpening');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('CurrencyOpening');
    }
}
