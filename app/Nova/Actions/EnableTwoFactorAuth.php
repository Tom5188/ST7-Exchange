<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Place;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Gravatar;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class EnableTwoFactorAuth extends Action
{
    use InteractsWithQueue, Queueable;
    
    public $name='验证并启用Google';
    
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $user = $models->first();
        
        if ($user->google2fa_secret) {
            return Action::danger('Google 验证已绑定。');
        }

        $secret = Cache::get($user->id);
    
        if (!$secret) {
            return Action::danger('临时密钥已过期，请刷新页面重新绑定。');
        }
    
        $google2fa = new Google2FA();
    
        if (!$google2fa->verifyKey($secret, $fields->verify_code)) {
            return Action::danger('验证码不正确，请确认手机中的 Google 验证器应用。');
        }
    
        $user->google2fa = 1;
        $user->google2fa_secret = $secret;
        $user->save();
    
        return Action::message('Google 验证绑定成功！');
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('验证码', 'verify_code')
                ->rules('required', 'digits:6')
                ->help('请输入 Google Authenticator 中显示的 6 位验证码'),
        ];
    }
}
