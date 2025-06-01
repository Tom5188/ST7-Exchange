<?php
namespace App\Nova\Dashboards;

use App\Nova\Metrics\BankExtractCurrency;
use App\Nova\Metrics\BankRechargeCurrency;
use App\Nova\Metrics\ExtractCurrency;
use App\Nova\Metrics\RechargeCurrency;
use App\Nova\Metrics\NewUsers;
use App\Nova\Metrics\TodayLogin;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new NewUsers(),
            new TodayLogin(),
            new RechargeCurrency(),
            new ExtractCurrency(),
            new BankRechargeCurrency(),
            new BankExtractCurrency(),
        ];
    }

    public function label()
    {
        return __('Main');
    }

    public function singularLabel()
    {
        return __('Main');
    }
}