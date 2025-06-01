<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;
use Laravel\Nova\Fields\BelongsToMany;
use Illuminate\Support\Facades\Hash;
use App\Nova\Actions\EnableTwoFactorAuth;
use App\Nova\Actions\DisbleTwoFactorAuth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Laravel\Nova\Fields\Boolean;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Fields\Line;

class AdminUser extends Resource
{
    /**
     * 后台管理员
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Users>
     */
    public static $model = \App\Models\AdminUser::class;

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
        'id', 'name', 'email',
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
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static function group(){
        return __('Admin');
    }


    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 2;
    
    protected function generateGoogleQRCode(string $company, string $email, string $secret): string
    {
        $google2fa = new Google2FA();
    
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $company,
            $email,
            $secret
        );
    
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
    
        $writer = new Writer($renderer);
        $qrImage = $writer->writeString($qrCodeUrl);
    
        return 'data:image/svg+xml;base64,' . base64_encode($qrImage);
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

            Gravatar::make(__('Avatar'))->disk('public/uploads/')->maxWidth(50),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make(__('Email'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make(__('Password'), 'password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),
                
            Text::make('Google二维码')
                ->asHtml()
                ->exceptOnForms()
                ->resolveUsing(function ($value, $model) {
                    if (empty($model->google2fa_secret)) {
                        // 动态生成 secret 并保存
                        $google2fa = new Google2FA();
                        $secret = $google2fa->generateSecretKey();
                        Cache::put($model->id, $secret, Carbon::now()->addSeconds(300));
                        $qrCode = $this->generateGoogleQRCode(
                            config('app.name'),
                            $model->email,
                            $secret
                        );
                        return "<img id=\"qrCode{$model->id}\" onclick=\"\" src=\"{$qrCode}\" width=\"100\">";
                    } else {
                        $secret = $model->google2fa_secret;
                        Cache::put($model->id, $secret);
                        return "<span style=\"color: green;display: block;padding-left: 20px;\">已绑定</span>";
                    }
                }),
            
            Boolean::make(__('启用 Google 验证'), 'google2fa')
                ->trueValue(1)  // 数据库中为 1 表示开启
                ->falseValue(0) // 数据库中为 0 表示关闭
                ->displayUsing(function ($value) {
                    return $value ? '已开启' : '未开启';
                })
                ->readonly(),
                
            BelongsToMany::make('Roles', 'roles', \Pktharindu\NovaPermissions\Nova\Role::class),
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
            (new DownloadExcel)->withHeadings()->showInline(),
            (new EnableTwoFactorAuth)->showInline(),
            (new DisbleTwoFactorAuth)->showInline(),
        ];
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Admin');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Admin');
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
