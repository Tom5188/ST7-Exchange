<?php

namespace App\Providers;

use App\Nova\AdminUser;
use App\Nova\Currency\Currency;
use App\Nova\Currency\CurrencyFloating;
use App\Nova\Currency\CurrencyMatch;
use App\Nova\Currency\CurrencyOpening;
use App\Nova\Currency\CurrencyType;
use App\Nova\Currency\CurrencyKx;
use App\Nova\Project;
use App\Nova\ProjectOrder;
use App\Nova\ProjectOrderEnd;
use App\Nova\Borrow;
use App\Nova\BorrowOrder;
use App\Nova\Dashboards\Main;
use App\Nova\Extract\DigitalBankSet;
use App\Nova\Extract\DigitalCurrencySet;
use App\Nova\Extract\UsersWalletOut;
use App\Nova\Extract\UsersWalletOutBank;
use App\Nova\Lever\LeverMultiple;
use App\Nova\Lever\LeverTransaction;
use App\Nova\Lever\LeverTransactionSimulation;
use App\Nova\News;
use App\Nova\Message;
use App\Nova\Shareoption\MicroOrder;
use App\Nova\Shareoption\MicroOrderSimulation;
use App\Nova\Shareoption\MicroSecond;
use App\Nova\TopUp\ChargeReq;
use App\Nova\TopUp\ChargeReqBank;
use App\Nova\TopUp\DigitalCurrencyAddress;
use App\Nova\TopUp\WireTransferAccount;
use App\Nova\TopUp\WireTransferCurrency;
use App\Nova\User\User;
use App\Nova\User\UserAgent;
use App\Nova\User\UserReal;
use App\Nova\User\UserAddress;
use App\Nova\User\UserSimulation;
use App\Nova\Wallet\AccountLog;
use App\Nova\Wallet\UsersWallet;
use Blade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Pktharindu\NovaPermissions\Nova\Role;
use Laravel\Nova\Fields\DateTime;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        
        Nova::auth(function ($request) {
            return Auth::check();
        });
        
        Nova::style('nova', public_path('css/nova.css'));
        
        Nova::script('nova', public_path('js/nova.js'));
        
        Nova::withBreadcrumbs();

        Nova::initialPath('/resources/users'); // 默认控制台跳转
        // 网站配置项
        $settings = [
            new \App\Nova\Settings\General(), // 常规设置
            new \App\Nova\Settings\Site(), // 站点设置
        ];

        foreach ($settings as $setting) {
            Nova::serving(function () use ($setting) {
                \Outl1ne\NovaSettings\NovaSettings::addSettingsFields(
                    $setting->fields() ?? [],
                    $setting->casts() ?? [],
                    $setting->name ?? null
                );
            });
        }

        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::dashboard(Main::class)->icon('chart-bar'),

                MenuSection::make(__('Setting'), [
                    MenuItem::externalLink(__('General'), env('APP_URL') . '/admin/nova-settings/general')
                        ->canSee(function ($request) {
                            return $request->user() && $request->user()->can('update nova-settings');
                        }),
                    
                    MenuItem::externalLink(__('Site'), env('APP_URL') . '/admin/nova-settings/site')
                        ->canSee(function ($request) {
                            return $request->user() && $request->user()->can('update site-settings');
                        }),

                ])->icon('cog')->collapsable(),

                MenuSection::make(__('User'), [
                    MenuItem::resource(User::class),
                    MenuItem::resource(UserReal::class),
                    MenuItem::resource(UserSimulation::class),
                    MenuItem::resource(UserAgent::class),
                    MenuItem::resource(UserAddress::class),
                ])->icon('users')->collapsable(),

                MenuSection::make(__('BlockTrade'), [
                    MenuItem::resource(Currency::class),
                    MenuItem::resource(CurrencyType::class),
                    MenuItem::resource(CurrencyMatch::class),
                    MenuItem::resource(CurrencyOpening::class),
                    MenuItem::resource(CurrencyFloating::class),
                    // MenuItem::resource(CurrencyKx::class),
                ])->icon('adjustments')->collapsable(),

                MenuSection::make(__('News'), [
                    MenuItem::resource(News::class),
                    MenuItem::resource(Message::class),
                ])->icon('annotation')->collapsable(),

                MenuSection::make(__('Contract'), [
                    MenuItem::resource(LeverMultiple::class),
                    MenuItem::resource(LeverTransaction::class),
                    MenuItem::resource(LeverTransactionSimulation::class),
                ])->icon('presentation-chart-bar')->collapsable(),

                MenuSection::make(__('SecondContract'), [
                    MenuItem::resource(MicroOrder::class),
                    MenuItem::resource(MicroOrderSimulation::class),
                    MenuItem::resource(MicroSecond::class),
                ])->icon('presentation-chart-line')->collapsable(),

                MenuSection::make(__('Wallet'), [
                    MenuItem::resource(UsersWallet::class),
                    MenuItem::resource(AccountLog::class),
                    MenuItem::resource(Borrow::class),
                    MenuItem::resource(BorrowOrder::class),
                    MenuItem::resource(Project::class),
                    MenuItem::resource(ProjectOrder::class),
                    MenuItem::resource(ProjectOrderEnd::class),
                ])->icon('currency-dollar')->collapsable(),

                MenuSection::make(__('TopUpSet'), [
                    // MenuItem::resource(WireTransferCurrency::class),
                    // MenuItem::resource(WireTransferAccount::class),
                    MenuItem::resource(DigitalCurrencyAddress::class),
                    MenuItem::resource(ChargeReq::class),
                    // MenuItem::resource(ChargeReqBank::class),
                ])->icon('cog')->collapsable(),

                MenuSection::make(__('ExtractSet'), [
                    MenuItem::resource(DigitalCurrencySet::class),
                    // MenuItem::resource(DigitalBankSet::class),
                    MenuItem::resource(UsersWalletOut::class),
                    // MenuItem::resource(UsersWalletOutBank::class),
                ])->icon('cog')->collapsable(),
                
                MenuSection::make(__('Admin'), [
                    MenuItem::resource(AdminUser::class),
                    MenuItem::resource(Role::class),
                ])->icon('user')->collapsable(),
            ];
        });

        // 自定义版权
        Nova::footer(function ($request) {
            return Blade::render('
            @env(\'local\')

            @endenv
            ');
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new \Pktharindu\NovaPermissions\NovaPermissions(),
            new \Badinansoft\LanguageSwitch\LanguageSwitch(), // 语言切换
            new \Outl1ne\NovaSettings\NovaSettings(), // 网站设置
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
