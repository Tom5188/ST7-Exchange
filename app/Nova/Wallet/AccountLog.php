<?php

namespace App\Nova\Wallet;

use App\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class AccountLog extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\AccountLog>
     */
    public static $model = \App\Models\AccountLog::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user.account_number',
    ];
    
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
            Number::make(__('Operation'), 'value')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('Before'), 'before')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Number::make(__('After'), 'after')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            Text::make(__('Source'), 'transaction_info')->readonly(),
            Text::make(__('Remark'), 'info')->readonly(),
            Text::make(__('CreateTime'), 'created_time')->readonly(),
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
        return __('AccountLog');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('AccountLog');
    }
}
