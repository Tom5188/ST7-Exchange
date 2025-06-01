<?php

namespace App\Nova\User;

use App\Models\Users;
use App\Nova\Resource;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class UserSimulation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Users>
     */
    public static $model = Users::class;

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
        'id',
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('User');
    }

    /**
     * The click action to use when clicking on the resource in the table.
     *
     * Can be one of: 'detail' (default), 'edit', 'select', 'preview', or 'ignore'.
     *
     * @var string
     */
    public static $clickAction = 'ignore';

    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 9999;

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
            Text::make(__('Email'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            # Select字段/枚举字段
            Select::make(__('Status'),'status')->options([
                0 => '正常',
                1 => '禁用',
            ])->displayUsingLabels(),

            Password::make(__('Password'), 'password')
                ->onlyOnForms()
                ->placeholder('修改登录密码')
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            DateTime::make(__('Created At'),'created_at')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),	# 只读字段

            DateTime::make(__('Last Login Time'),'last_login_time')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),	# 只读字段
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
        return __('UserSimulation');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('UserSimulation');
    }
}
