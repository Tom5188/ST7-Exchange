<?php

namespace App\Nova\Shareoption;

use App\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class MicroSecond extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\MicroSecond>
     */
    public static $model = \App\Models\MicroSecond::class;

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
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 3;
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('SecondContract');
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
            Number::make(__('Seconds'), 'seconds'),
            Select::make(__('Status'),'status')->options([
                0 => '禁用',
                1 => '正常',
            ])->displayUsingLabels(),
            Number::make(__('ProfitRatio'), 'profit_ratio')->step('any'),
            Number::make(__('MaxAmount'), 'max_amount')->step('any'),
            Number::make(__('MinAmount'), 'min_amount')->step('any'),
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
    public static function label()
    {
        return __('MicroSecond');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('MicroSecond');
    }
}
