<?php

namespace App\Nova\Extract;

use App\Nova\Actions\ExtractPass;
use App\Nova\Actions\ExtractPassBank;
use App\Nova\Actions\ExtractRefuse;
use App\Nova\Resource;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class UsersWalletOutBank extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\UsersWalletOut>
     */
    public static $model = \App\Models\UsersWalletOutBank::class;

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
    public static $priority = 3;


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
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make(__('AccountNumber'), 'account')->readonly(),
            Text::make(__('CurrencyName'), 'currency')->readonly(),
            Text::make(__('Extract Address'), 'address')->readonly(),
            Text::make(__('BankName'), 'bank_name')->readonly(),
            Text::make(__('Payee'), 'real_name')->readonly(),
            Text::make(__('Swift'), 'swift')->readonly(),
            Number::make(__('Extract Number'), 'number')->readonly(),
            Number::make(__('ServiceCharge'), 'rate')->readonly(),
            Number::make(__('ReceivedNumber'), 'real_number')->readonly(),
            Badge::make(__('Status'), 'status_name')->map([//danger
                'Apply for currency withdrawal' => 'info',
                '成功' => 'success',
                'fail' => 'danger'
            ]),
            Text::make(__('Time Of Application'), 'create_time')->readonly()->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            ( new ExtractPassBank())->showInline(),
            (new ExtractRefuse())->showInline()
        ];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('UsersWalletOutBank');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('UsersWalletOutBank');
    }
}
