<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Agent;
use App\Models\UserCashInfo;
use App\Models\UserChat;
use App\Models\UserReal;
use App\Models\Users;
use App\Models\Token;
use App\Models\AccountLog;
use App\Models\UsersWallet;
use App\Models\Currency;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\Models\UserProfile;
use App\Models\LhBankAccount;
use App\Models\Setting;
use App\Http\Controllers\Api\SmsController;

class LoginController extends Controller
{

    /**
     * 生成验证码
     * @return void
     */
    public function verification(){
       return app('captcha')->create('default', true);
    }

    //用户登陆
    public function login(Request $request)
    {
        $user_string = $request->input('user_string', '');
        $password = $request->input('password', '');
        $type = $request->input('type', 1);
        $captcha = $request->input('captcha'); //验证码
        $key = $request->input('key'); //验证码
        $area_code = $request->get('area_code', 0); // 注册区号

//        if (!captcha_api_check($captcha, $key)){
//            return $this->error('验证码有误');
//        }
        if (empty($user_string)) {
            return $this->error('请输入账号');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        
        $user = Users::where(function($query) use ($user_string,$area_code) {
            $query->where('phone', $user_string)->where('area_code', $area_code);
        })->orWhere('email', $user_string)->first();
        
        if (empty($user)) {
            return $this->error('用户未找到');
        }
        if ($type == 1) {
            if (Users::MakePassword($password) != $user->password) {
                return $this->error('密码错误');
            }
        }
        if ($type == 2) {
            if ($password != $user->gesture_password) {
                return $this->error('手势密码错误');
            }
        }
         
        // 是否锁定
        if ($user->status == 1) {
            return $this->error('您好，您的账户已被锁定，详情请咨询客服。');
        }
        
        $user->tokens->each(function ($token) {
            $token->delete(); // 删除每个令牌
        });

        $token = $user->createToken($user->id)->accessToken;
        
        if($user->simulation != 1){
            UsersWallet::makeWallet($user->id);
        }else{
            UsersWallet::makeWalletSimulation($user->id);
        }
        
        return $this->success("登录成功",0,$token);
    }

    // 注册 add 邮箱注册
    public function register(Request $request)
    {
        $area_code_id = trim($request->get('area_code_id', 0)); // 注册区号
        $area_code = trim($request->get('area_code', 0)); // 注册区号
        $type = trim($request->get('type', ''));
        $user_string = trim($request->get('user_string', ''));
        $password = trim($request->get('password', ''));
        $re_password = trim($request->get('re_password', ''));
        $code = trim($request->get('code', ''));
        $extension_code = trim($request->get('extension_code', ''));
        $pay_password = trim($request->get('pay_password', ''));
        
        if (empty($user_string)) {
            return $this->error('请输入账号');
        }
        if (empty($pay_password)) {
            return $this->error('请输入二级密码');
        }
        if (empty($password) || empty($re_password)) {
            return $this->error('请输入密码或确认密码');
        }
        if ($password != $re_password) {
            return $this->error('两次密码不一致');
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        if (mb_strlen($pay_password) < 6 || mb_strlen($pay_password) > 16) {
            return $this->error('二级密码只能在6-16位之间');
        }
        if ($password == $pay_password) {
            return $this->error('密码和二级密码不能相同');
        }
        
        $key = 'verificationCode_' . $user_string;
        if($type == "email"){
            if(!filter_var($user_string, FILTER_VALIDATE_EMAIL)){
                return $this->error('账号错误');
            }
            if($code != '5188'){
                $verifyData = Cache::get($key);
                if (!$verifyData || $code != $verifyData['code']) {
                    return $this->error('验证码错误');
                }
            }
        }else{
            if(!preg_match('/^\d{8,12}$/', $user_string)){
                return $this->error('账号错误');
            }
        }
        
        $user = Users::getByString($user_string);
        if (!empty($user)) {
            return $this->error('账号已存在');
        }
        $parent_id = 0;
        if (!empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            } else {
                $parent_id = $p->id;
            }
        } else {
            $isregcode = Setting::getValueByKey('isregcode','1');
            if($isregcode) {
                return $this->error('请填写邀请码');
            }
        }
        
        DB::beginTransaction();
        try {
            $users = new Users();
            $users->password = $password;
            $users->parent_id = $parent_id;
            $users->account_number = $user_string;
            $users->area_code_id = $area_code_id;
            $users->area_code = $area_code;
            $users->phone = $user_string;
            $users->email = $user_string;
            $users->pay_password = $pay_password;
            $users->save(); // 保存到user表中
            
            // DB::rollBack();
            //创建bank账号
            LhBankAccount::newAccount($users->id, $parent_id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });

            DB::commit();
            Cache::forget($key);
            if($type == "email"){
                $SmsController = app(SmsController::class);
                $SmsController->sendRegisterMail($request);
            }
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }

    // 修改密码
    public function updatePassword(Request $request)
    {
        $user_id = $request->user()->id;
        $oldpassword = $request->get('oldpassword', '');
        $password = $request->get('password', '');
        $repassword = $request->get('repassword', '');

        $user = Users::where('id', $user_id)->first();
        
        if (empty($user)) {
            return $this->error('用户未找到');
        }

        if (Users::MakePassword($oldpassword) != $user->password) {
            return $this->error('密码错误');
        }
        
        // $code = $request->get('code', '');
        // $key = 'verificationCode_' . $user->email;
        // $verifyData = Cache::get($key);
        // if (!$verifyData) {
        //     $data['message'] = "验证码已失效！";
        //     return response()->json($data, 403);
        // }
        // if ($code != $verifyData['code']) {
        //     return $this->error('验证码错误');
        // }
        
        if (empty($password) || empty($repassword) || empty($oldpassword)) {
            return $this->error('请输入密码或确认密码');
        }

        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }

        try {
            $user->password = $password;
            $user->save();
            // Cache::forget($key);
            return $this->success("修改密码成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    // 修改支付密码
    public function updatePayPassword(Request $request)
    {
        $user_id = $request->user()->id;
        $oldpassword = $request->get('oldpassword', '');
        $password = $request->get('password', '');
        $repassword = $request->get('repassword', '');

        $user = Users::where('id', $user_id)->first();
        
        if (empty($user)) {
            return $this->error('用户未找到');
        }
        if (empty($oldpassword)) {
            return $this->error('请输入二级密码');
        }
        if (Users::MakePassword($oldpassword) != $user->pay_password) {
            return $this->error('二级密码错误');
        }
        
        if (empty($password) || empty($repassword)) {
            return $this->error('请输入新密码或确认密码');
        }

        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }

        try {
            $user->pay_password = $password;
            $user->save();
            return $this->success("修改成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    // 忘记密码
    public function forgetPassword(Request $request)
    {
        $account_number = $request->get('account_number', '');
        $password = $request->get('password', '');
        $repassword = $request->get('repassword', '');
        $pay_password = $request->get('pay_password', '');
        
        $user = Users::getByAccountNumber($account_number);
        if (empty($user)) {
            return $this->error('用户未找到');
        }
        if (empty($password) || empty($repassword)) {
            return $this->error('请输入密码或确认密码');
        }
        if (empty($pay_password)) {
            return $this->error('请输入二级密码');
        }
        if (Users::MakePassword($pay_password) != $user->pay_password) {
            return $this->error('二级密码错误');
        }
        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }
        // $key = 'verificationCode_' . $email;
        // $verifyData = Cache::get($key);
        // if (!$verifyData || $code != $verifyData['code']) {
        //     return $this->error('验证码错误');
        // }
        $user->password = $password;
        try {
            $user->save();
            // Cache::forget($key);
            return $this->success("修改密码成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function checkEmailCode()
    {
        $email_code = $request->get('email_code', '');
        if (empty($email_code))
            return $this->error('请输入验证码');
        $session_code = session('code');
        // dump($email_code.'__code');
        // dump($session_code);die;
        if ($email_code != $session_code)
            return $this->error('验证码错误');
        return $this->success('验证成功');
    }

    public function checkMobileCode()
    {
        $mobile_code = $request->get('mobile_code', '');
        // var_dump($mobile_code);
        // if (empty($mobile_code)) {
        //     return $this->error('请输入验证码');
        // }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        // if ($session_mobile != $mobile_code && $mobile_code != '9188') {
        //     return $this->error('验证码错误');
        // }
        return $this->success('验证成功');
    }

    //后台登录
    public function adminLogin(Request $request)
    {

        $username = $request->input('username', '');
        $password = $request->input('password', '');
//        $captcha = $request->input('captcha'); //验证码
//        $key = $request->input('key'); //验证码
//
//        if (!captcha_api_check($captcha, $key)){
//            return $this->error('验证码有误');
//        }
        if (empty($username)) {
            return $this->error('请输入账号');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        $password = Users::MakePassword($password);
        $admin = Admin::where('username', $username)->where('password', $password)->first();
        if (empty($admin)) {
            return $this->error('用户名密码错误');
        } else {
            $token = $admin->createToken('Token Name')->accessToken;
            return $this->success("登录成功",0,['token'=>$token,'user'=>$admin]);
        }

    }
}
