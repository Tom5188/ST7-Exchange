<?php
namespace App\Nova\Settings;

use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;

class Site
{
    public string $name = "site";

    public function fields(): array
    {
        return [
            Text::make(__('SiteName'),'site_name'),//网站名称
            Image::make(__('SiteLogo'), 'site_logo'),//网站logo
            Image::make(__('UserDefaultAvatar'), 'user_default_avatar'),//用户默认头像
            Text::make(__('SupportUrl'),'support_url'),//在线客服地址
            Text::make(__('IosDownloadUrl'),'app_ios'),//
            Text::make(__('AndroidDownloadUrl'),'app_android'),//
            Text::make(__('WebSite'),'website'),//
            Text::make(__('Service'),'kf'),//在线客服地址
            Text::make(__('Version'),'version'),//版本号
            Text::make(__('CommissionRate'),'commissionRate'),//1级返佣
            Boolean::make(__('AuthenticationEmail'),'authentication_email')
                ->trueValue('1')
                ->falseValue('0'),
            Boolean::make(__('PromptTone'),'prompt_tone')
                ->trueValue('1')
                ->falseValue('0'),
            Boolean::make(__('DealReal'),'deal_real')
                ->trueValue('1')
                ->falseValue('0'),
            Boolean::make(__('WithdrawDepositReal'),'withdraw_deposit_real')
                ->trueValue('1')
                ->falseValue('0'),
            Boolean::make(__('IsRegCode'),'isregcode')
                ->trueValue('1')
                ->falseValue('0'),
        ];
    }

    public function casts(): array
    {
        return [

        ];
    }
}
