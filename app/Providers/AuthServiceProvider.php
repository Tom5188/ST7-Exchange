<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use App\Models\AdminUser as User;
use Illuminate\Support\Facades\Route;
use Pktharindu\NovaPermissions\Traits\ValidatesPermissions;

class AuthServiceProvider extends ServiceProvider
{
    use ValidatesPermissions;

    protected $policies = [
        \Pktharindu\NovaPermissions\Role::class => \App\Policies\RolePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        
        // token 认证有效期 365 天
        Passport::tokensExpireIn(now()->addDays(365));
        // 刷新 token 认证有效期 400 天
        Passport::refreshTokensExpireIn(now()->addDays(400));
        // 设置令牌过期时间 1 年
        Passport::personalAccessTokensExpireIn(now()->addMonths(12));
        
        Gate::define('view nova-settings', function (User $user) {
            return $user->hasPermissionTo('view nova-settings');
        });
        Gate::define('view site-settings', function (User $user) {
            return $user->hasPermissionTo('view site-settings');
        });
        Gate::define('update nova-settings', function (User $user) {
            return $user->hasPermissionTo('update nova-settings');
        });
        Gate::define('update site-settings', function (User $user) {
            return $user->hasPermissionTo('update site-settings');
        });
    
        foreach (config('nova-permissions.permissions') as $key => $permissions) {
            Gate::define($key, function (User $user) use ($key) {
                if ($this->nobodyHasAccess($key)) {
                    return true;
                }
                return $user->hasPermissionTo($key);
            });
        }
    }
}
