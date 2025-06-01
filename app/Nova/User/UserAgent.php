<?php

namespace App\Nova\User;

use App\Models\Users;
use App\Nova\Resource;
use App\Nova\Actions\UserRecharge;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Fields\Boolean;

class UserAgent extends Resource
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
        'account_number',
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
        return $query->where('simulation', 0)->where('agent', 1);

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
            Text::make(__('AccountNumber'), 'account_number')
                ->sortable()->readonly(),

            # Select字段/枚举字段
            Select::make(__('Status'),'status')->options([
                0 => '正常',
                1 => '禁用',
            ])->displayUsingLabels(),
            
            Select::make(__('UserType'), 'agent')
                ->options([
                    0 => '会员',
                    1 => '代理',
                ])
                ->displayUsingLabels(),
            
            Text::make(__('ExtensionCode'), 'extension_code')->copyable(),
            
            Number::make(__('Balance'),'usdt')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            
            Boolean::make(__('IsRealsName'), 'is_realname')->trueValue(2)->falseValue(1)->exceptOnForms(),

            Password::make(__('Password'), 'password')
                ->onlyOnForms()
                ->placeholder('修改登录密码')
                ->creationRules(
                    'required',
                    'min:8',
                    'max:16',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,16}$/'
                )
                ->updateRules(
                    'nullable',
                    'min:8',
                    'max:16',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,16}$/'
                ),
                
            Password::make(__('Secondary Password'), 'pay_password')
                ->onlyOnForms()
                ->placeholder('修改交易密码')
                ->creationRules(
                    'required',
                    'regex:/^\d{6}$/'
                )
                ->updateRules(
                    'nullable',
                    'regex:/^\d{6}$/'
                ),
                
            new Panel('代理统计信息', $this->agentStatsFields()),

            DateTime::make(__('Created At'),'created_at')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),	# 只读字段

            DateTime::make(__('Last Login Time'),'last_login_time')->displayUsing(function ($value) {
                return $value ? $value->format('Y-m-d H:i:s') : '';
            })->readonly(),	# 只读字段
        ];
    }
    
    protected function agentStatsFields()
    {
        $stats = $this->resource->getChildrenStats(); // 调用模型中的方法
    
        return [
            Text::make('一级会员', fn() => $stats['L1'])->readonly(),
            Text::make('二级会员', fn() => $stats['L2'])->readonly(),
            Text::make('三级会员', fn() => $stats['L3'])->readonly(),
            Text::make('总充值', fn() => number_format($stats['recharge'], 2))->readonly(),
            Text::make('总提现', fn() => number_format($stats['withdraw'], 2))->readonly(),
            Text::make('总盈利', fn() => number_format($stats['recharge'] - $stats['withdraw'], 2))->readonly(),
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
            (new UserRecharge())->showInline(),
        ];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('UserAgent');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('UserAgent');
    }
}
