<?php

namespace App\Http\Controllers\Api;

use App\Models\Agent;
use App\Models\ChargeReq;
use App\Models\DigitalCurrencyAddress;
use App\Models\LhBankAccount;
use App\Models\UserAlgebra;
use App\Models\UserLevelModel;
use App\Models\UserProfile;
use App\Models\UsersWalletOut;
use App\Models\UsersWalletWithdraw;
use App\Models\UserUsdtInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use App\Models\UserCashInfo;
use App\Models\UserCashInfoInternational;
use Illuminate\Http\Request;
use App\Models\UserChat;
use App\Models\Users;
use App\Models\UserReal;
use App\Models\Token;
use App\Models\AccountLog;
use App\Models\UsersWallet;
use App\Models\UsersWalletcopy;
use App\Models\Bank;
use App\Models\IdCardIdentity;
use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\Models\Seller;
use App\Models\CurrencyQuotation;
use App\Services\RedisService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;


//use App\{Users, AccountLog};

class UserController extends Controller
{
    //实名认证状态
    public function realState(Request $request)
    {
        $user_id = $request->user()->id;
        $real_status = 0;

        $real_data = DB::table('user_real')->where('user_id', $user_id)
            ->first();

        if (!empty($real_data)) {
            if ($real_data->review_status == 1) {
                $real_status = 1;
            }
            if ($real_data->review_status == 2) {
                $real_status = 2;
            }
            if ($real_data->review_status == 3) {
                $real_status = 3;
            }
        }

        $result = compact('real_status', "real_data");
        return $this->success('', 0, $result);
    }

    // 实名认证
    public function saveUserReal(Request $request)
    {
        $user_id = $request->user()->id;

        $id_type = $request->post('id_type', 0); // 0身份证 1护照 2驾驶证

        // 接受参数
        $real_type = $request->post('real_type'); // 1 初级认证  2 高级认证
        if (!in_array($real_type, [1, 2])) {
            return $this->error('认证类型错误');
        }
        $name = $request->post('name');
        $card_id = $request->post('card_id');
        $front_pic = $request->post('front_pic');
        $reverse_pic = $request->post('reverse_pic');

        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        if ($real_type == 1) {
            if (empty($name) || empty($card_id)) {
                return $this->error('请填写完整信息');
            }
            $userreal_number = UserReal::where("card_id", $card_id)->where('user_id', '!=', $user_id)->count();
            if ($userreal_number > 0) {
                return $this->error("该身份证号已实名认证过!");
            }
            $real = UserReal::where('user_id', $user_id)->first();

            if ($real && in_array($real->review_status, [1, 2])) {
                return $this->error('已经审核过认证~');
            }
            if($real){
                $real->id_type = $id_type;
                $real->name = $name;
                $real->card_id = $card_id;
                $real->front_pic = $front_pic;
                $real->reverse_pic = $reverse_pic;
                $real->create_time = date('Y-m-d H:i:s', time());
                $real->review_status = 1;
            }else{
                $real = new UserReal;
                $real->id_type = $id_type;
                $real->name = $name;
                $real->user_id = $user_id;
                $real->card_id = $card_id;
                $real->front_pic = $front_pic;
                $real->reverse_pic = $reverse_pic;
                $real->create_time = date('Y-m-d H:i:s', time());
            }
            $real->save();
        } else {
            if (empty($front_pic) || empty($reverse_pic)) {
                return $this->error('请填写完整信息');
            }
            $real = UserReal::where('user_id', $user_id)->first();
            if (empty($real)) {
                return $this->error('请先完成初级认证');
            }
            if ($real->review_status != 2) {
                return $this->error('初级认证审核中，请耐心等待');
            }
            $real->front_pic = $front_pic;
            $real->reverse_pic = $reverse_pic;
            $real->advanced_user = 1;
            $real->save();
        }
        return $this->success('认证成功，请等待审核');
    }

    //新增钱包地址
    public function userWalletSave(Request $request)
    {
        // 两种模式    给我传id  就是  修改     不给我传id  就是  新增
        $user_id = $request->user()->id;
        // 接受参数
        $wallet_id = $request->post('id');
        $currency = $request->post('currency');
        $address = $request->post('address');
        $qrcode = $request->post('qrcode');
        if (empty($currency) || empty($address) || empty($qrcode)) {
            return $this->error('请完善钱包信息');
        }
        if ($wallet_id) {
            $wallet = UsersWalletWithdraw::where('id', $wallet_id)->first();
        } else {
            $wallet = new UsersWalletWithdraw();
        }
        $wallet->user_id = $user_id;
        $wallet->currency = $currency;
        $wallet->address = $address;
        $wallet->qrcode = $qrcode;
        $wallet->save();
        $msg = $wallet_id ? '保存成功' : '添加成功';
        return $this->success($msg);
    }

    //钱包列表
    public function userWalletList(Request $request)
    {
        $user_id = $request->user()->id;
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $currency = $request->get('currency', 0);
        $lists = UsersWalletWithdraw::with([])
            ->join('currency', 'currency.id', '=', 'users_wallet_withdraw.currency')
            ->where('users_wallet_withdraw.user_id', $user_id)
            ->where(function ($query) use ($currency) {
                if ($currency > 0) {
                    $query->where('currency.id', $currency);
                }
            })
            ->select('users_wallet_withdraw.*', 'currency.name')
            ->orderBy('users_wallet_withdraw.id', 'desc')
            ->paginate($limit);

        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('', 0, $result);
    }

    //删除钱包
    public function userWalletDelete(Request $request)
    {

        $wallet_id = $request->post('id');

        UsersWalletWithdraw::with([])->where('id', $wallet_id)->delete();

        return $this->success('删除成功');

    }





    //添加/修改数字货币
    public function saveUsdtInfo(Request $request)
    {

        $digital_currency_id = $request->get('digital_currency_id', '');
        $account = $request->get('account', '');
        $id = $request->get('id', '');
        $user_id = $request->user()->id;
        if (empty($id)) {
            $cash_info = new UserUsdtInfo();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        } else {
            $cash_info = UserUsdtInfo::where('id', $id)->first();
        }
        $cash_info->digital_currency_id = $digital_currency_id;
        $cash_info->account = $account;
        $cash_info->save();
        //更新申请商家收付款方式
        return $this->success('保存成功');

    }

    //数字货币列表
    public function usdtInfo(Request $request)
    {
        $user_id = $request->user()->id;

        $digital_currency_id = $request->get('digital_currency_id');
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $result = UserUsdtInfo::with(['digitalCurrency'])->where('user_id', $user_id)
            ->where(function ($query) use ($digital_currency_id){
                if($digital_currency_id){
                    $query->where('digital_currency_id',$digital_currency_id);
                }
            })
            ->get();
        return $this->success('',0,$result);
    }

    //删除数字货币
    public function usdtDelete(Request $request)
    {
        $id = $request->get('id', '');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UserUsdtInfo::where('id', $id)->delete();

        return $this->success('删除成功');
    }


    //添加/修改银行卡
    public function saveCashInfo(Request $request)
    {
        $bank_name = $request->get('bank_name', '');
        $digital_bank_id = $request->get('digital_bank_id', 1);
        $bank_dizhi = $request->get('bank_address', '');
        $bank_account = $request->get('bank_account', '');
        $real_name = $request->get('real_name', '');
        $swift = $request->get('swift', '');
        $id = $request->get('id', '');
        $user_id = $request->user()->id;
        if (empty($id)) {
            $cash_info = new UserCashInfo();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        } else {
            $cash_info = UserCashInfo::where('id', $id)->first();
        }
        $cash_info->bank_name = $bank_name;
        $cash_info->digital_bank_id = $digital_bank_id;
        $cash_info->bank_dizhi = $bank_dizhi;
        $cash_info->real_name = $real_name;
        $cash_info->bank_account = $bank_account;
        $cash_info->swift = $swift;
        $cash_info->save();
        //更新申请商家收付款方式
        return $this->success('保存成功');

    }

    //银行卡列表
    public function cashInfo(Request $request)
    {
        $user_id = $request->user()->id;

        $money = $request->get('money');
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $result = UserCashInfo::with(['digitalBankSet'])->where('user_id', $user_id)
            ->where(function ($query) use ($money){
                if($money){
                    $query->where('money',$money);
                }
            })
            ->get();
        return $this->success('',0,$result);
    }



    //删除银行卡
    public function cashDelete(Request $request)
    {
        $id = $request->get('id', '');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UserCashInfo::where('id', $id)->delete();

        return $this->success('删除成功');
    }



    // 当前登录用户信息
    public function info(Request $request)
    {
        $request_user_id = $request->get('user_id', 0);
        $user_id = $request->user()->id;
        if ($request_user_id) {
            $user_id = $request_user_id;
        }

        $currency_usdt_id = Currency::where('name', 'USDT')->select(['id', 'name'])->first();
        //$user = Users::where("id",$user_id)->first(['id','phone','email','head_portrait','status']);
        $user = Users::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $user['is_open_transfer_candy'] = Setting::getValueByKey("is_open_transfer_candy");
        //用户认证状况
        $res = UserReal::where('user_id', $user_id)->first();
        if (empty($res)) {
            $user['review_status'] = 0;
            $user['name'] = '';
        } else {
            $user['review_status'] = $res['review_status'];
            $user['name'] = $res['name'];
        }
        $seller = Seller::where('user_id', $user_id)->get()->toArray();
        if (!empty($seller)) {
            $user['seller'] = $seller;
        }
        $user['tobe_seller_lockusdt'] = Setting::getValueByKey("tobe_seller_lockusdt");
        $user['currency_usdt_id'] = $currency_usdt_id->id;
        $user['currency_usdt_name'] = $currency_usdt_id->name;


        $currency_name = $request->input('currency_name', '');
        
        $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
            })->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();
            
        $lever_wallet['totle'] = 0;
        foreach ($lever_wallet['balance'] as $k => $v) {
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            $lever_wallet['totle'] += $num * $v['cny_price'];
        }
        
        $user["lever_wallet"] = $lever_wallet;

        $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
            })->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();
        $legal_wallet['totle'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            $legal_wallet['totle'] += $num * $v['cny_price'];
        }
        $user["legal_wallet"] = $legal_wallet;
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) {
                $query->where('is_micro', 1);
            })->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])->toArray();
        $user["micro_wallet"] = $micro_wallet;
        //幣幣钱包
        $change_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) {
                $query->where('is_match', 1);
            })->get(['id', 'currency', 'change_balance', 'lock_change_balance'])->toArray();
        $user["change_wallet"] = $change_wallet;
        //代理线获取3级
        $level1 = Users::where('parent_id', $user_id)->get();
        $level2 = Users::whereIn('parent_id', $level1->pluck('id'))->get();
        $level3 = Users::whereIn('parent_id', $level2->pluck('id'))->get();
        $stat = function ($collection) {
            $verified = $collection->filter(function ($item) {
                return $item->is_realname == 2 && $item->recharge > 1;
            })->count();
    
            return "$verified/".$collection->count();
        };
        // 总充值 / 提现 / 充值人数（去重）
        $allUsers = $level1->merge($level2)->merge($level3);

        $user["children"] = ['L1' => $stat($level1),'L2' => $stat($level2),'L3' => $stat($level3), 'recharge' => $allUsers->sum('recharge'), 'withdraw' => $allUsers->sum('withdraw'), 'recharge_person' => $allUsers->filter(fn($u) => $u->recharge > 0)->count()];
        return $this->success('', 0, $user);
    }

    public function generateAccount()
    {
        $user_string = makeCardPassword(8) . '@email.com';
        $password = makeCardPassword(6);
        $users = new Users();
        $users->password = $password;
        $users->parent_id = 0;
        $users->account_number = $user_string;
        $users->area_code_id = 0;
        $users->area_code = 0;
        $users->email = $user_string;
        $users->phone = null;
        // 后台设置用户默认头像
        $user_default_avatar = DB::table('settings')->where('key', 'user_default_avatar')->first();
        $users->head_portrait = URL($user_default_avatar->value);
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        $users->simulation = 1;
        DB::beginTransaction();
        try {
            $users->save(); // 保存到user表中
            UsersWallet::makeWalletSimulation($users->id);
            $real = new UserReal;
            $real->id_type = 0;
            $real->name = '模拟';
            $real->user_id = $users->id;
            $real->card_id = '123456789';
            $real->front_pic = '';
            $real->reverse_pic = '';
            $real->create_time = date('Y-m-d H:i:s', time());
            $real->simulation = 1;
            $real->save();
            DB::commit();
            $info = array(
                'username' => $user_string,
                'password' => $password
            );
            return $this->success("", 0, $info);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }
}

?>
