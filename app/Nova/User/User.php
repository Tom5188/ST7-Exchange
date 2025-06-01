<?php

namespace App\Nova\User;

use Acme\Analytics\Analytics;
use App\Nova\Actions\UserRecharge;
use App\Nova\Resource;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use KirschbaumDevelopment\Nova\InlineSelect;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Users>
     */
    public static $model = \App\Models\Users::class;

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
        'account_number','id','parent_id'
    ];

    /**
     * Whether to show borders for each column on the X-axis.
     *
     * @var bool
     */
    public static $showColumnBorders = false;

    /**
     * The visual style used for the table. Available options are 'tight' and 'default'.
     *
     * @var string
     */
    public static $tableStyle = 'default';
    
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
        return __('User');
    }

    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 9999;

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('simulation', 0)->where('agent', 0);

    }
    
    public static function withoutDetail()
    {
        return true;  // 禁止跳转到详情页面
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
                ->help('编辑时忽略其数据改变,保护账号安全!')
                ->creationRules('required', 'unique:users')
                ->updateRules('required', 'unique:users')
                ->readonly(function ($request) {
                    // 判断是否为更新操作，如果是则使字段只读
                    return $request->isMethod('put');
                }),

            Text::make(__('Superior'),'parent_id')->help('编辑时,请填写上级的ID!'),

            Text::make(__('ExtensionCode'), 'extension_code')->copyable()->readonly(),

            // Text::make(__('UserType'),'my_agent_level')->readonly(),
            
            Select::make(__('UserType'), 'agent')
                ->options([
                    0 => '会员',
                    1 => '代理',
                ])
                ->default(0)
                ->displayUsingLabels(),

            // Text::make(__('UserLevel'), 'level')->readonly(),

            // # Select字段/枚举字段
            Select::make(__('Status'), 'status')
                ->options([
                    0 => '正常',
                    1 => '冻结',
                ])
                ->default(0)
                ->displayUsingLabels(),

            Number::make(__('Balance'),'usdt')->step('any')->displayUsing(function ($value) {
                return number_format((float)$value, 3); // 显示保留 3 位小数
            })->readonly(),
            
            Boolean::make(__('IsRealsName'), 'is_realname')->trueValue(2)->falseValue(1)->readonly(),
            
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

            // 添加备注字段
            Text::make(__('Label'), 'label')
                ->sortable()
                ->rules('nullable', 'max:30')
                ->hideFromIndex(), # 可以选择隐藏于索引视图中

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
        return [
        ];
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
        return __('Users');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Users');
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

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return $this->name . '/' . $this->email;
    }
}
