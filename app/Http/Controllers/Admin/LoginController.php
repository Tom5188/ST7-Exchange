<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;

class LoginController extends Controller
{
    public function login()
    {
        return view('gglogin');
    }

    public function gglogin(Request $request)
    {
        $EmailAndPassWord = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::guard('admins')->attempt($EmailAndPassWord)) {
            $request->session()->regenerate();
            $user = Auth::guard('admins')->user();
            if ($user->google2fa) {
                $google2fa = new Google2FA();
                if ($google2fa->verifyKey($user->google2fa_secret, $request->google2fa_code)) {
                    return redirect()->intended('/admin');
                } else {
                    return back()->withErrors([
                        'Google2fa' => '❌：Google 验证码出现错误！'
                    ]);
                }
            } else {
                return redirect()->intended('/admin');
            }
        } else {
            return back()->withErrors([
                'Email' => '❌：账号不存在或密码错误！'
            ]);
        }
    }
}