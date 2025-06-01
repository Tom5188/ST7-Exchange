<?php

namespace App\Nova;

use App\Nova\Filters\NewsLanguage;
use App\Nova\Filters\NewsType;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use NormanHuth\NovaRadioField\Radio;

class News extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\News>
     */
    public static $model = \App\Models\News::class;

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
        'title','lang'
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('News');
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
            Text::make(__('Title'), 'title'),
            Select::make(__('Type'),'c_id')->options([
                1 => __('Announcement'),
                2 => __('Information'),
                3 => __('PrivacyPolicy'),
                4 => __('UserAgreement'),
                5 => __('WindowAnnouncement'),
            ])->displayUsingLabels(),

            Select::make(__('Lang'),'lang')->options([
                'zh' => __('zh'),
                'hk' => __('hk'),
                'en' => __('en'),
                'jp' => __('jp'),
                'kor' => __('kor'),
            ])->displayUsingLabels(),
            Number::make(__('Sort'), 'sorts'),
            Image::make(__('SurfacePlot'), 'thumbnail'),
            Radio::make(__('IsDisplay'), 'display')
                ->options([
                    '0' => __('Hide Content'),
                    '1' => __('Show Content'),
                ]) ->inline(),
            Text::make(__('Keyword'), 'keyword'),
            Textarea::make(__('Abstract'),'abstract')->onlyOnForms(),
            Trix::make(__('Content'),'content')->onlyOnForms()
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
        return [
            new NewsType(),
            new NewsLanguage()
        ];
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
        return __('News');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('News');
    }
}
