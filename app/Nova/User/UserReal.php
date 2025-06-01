<?php

namespace App\Nova\User;

use App\Nova\Resource;
use Davidpiesse\NovaToggle\Toggle;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Actions\ApplyRealName;
use App\Nova\Actions\NoApplyRealName;

class UserReal extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\UserReal>
     */
    public static $model = \App\Models\UserReal::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user.account_number','name','card_id'
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
     * The visual style used for the table. Available options are 'tight' and 'default'.
     *
     * @var string
     */
    public static $tableStyle = 'tight';
    
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
    public static $priority =9999;

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
        return [
            ID::make(),

            Text::make(__('AccountNumber'), 'account')->readonly(),

            Text::make(__('RealName'), 'name')->readonly(),

            Text::make(__('ID Number'), 'card_id')->readonly(),

            Text::make(__('Time Of Application'),'create_time')->readonly(),

            Badge::make(__('Status'),'status')->map([
                '已审核' => 'success',
                '未审核' => 'info',
                '已拒绝' => 'danger',
            ]),
            
            Image::make(__('FrontPic'), 'front_pic') ->detailWidth(300), // onlyOnDetail

            Image::make(__('ReversePic'), 'reverse_pic') ->detailWidth(300),
            
            Text::make(__('Notes'), 'notes')->hideFromIndex()->readonly()->displayUsing(function ($value) {
                return '<span style="color:red">' . $value . '</span>';
            })->asHtml(),
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
            (new ApplyRealName)->showInline(),
            (new NoApplyRealName)->showInline()
        ];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Users Reals');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Users Reals');
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->name;
    }
}
