<?php
namespace App\Nova\Settings;


use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class General
{
    public string $name = "general";


    public function fields(): array
    {
        return [
            Text::make(__('PhpMailerUsername'),'phpMailer_username'),//邮箱账号
            Text::make(__('PhpMailerPassword'),'phpMailer_password'),//token密码
            Text::make(__('PhpMailerPort'),'phpMailer_port'),//端口
            Text::make(__('Host'),'phpMailer_host'),//host
            Text::make(__('FromName'),'submail_from_name'),//发件人
        ];
    }

    public function casts() : array
    {
        return [

        ];
    }

}
