<?php

namespace App\Http\Controllers\Api;

use App\Models\ChargeReq;
use App\Models\ChargeReqBank;
use App\Models\DigitalBankSet;
use App\Models\DigitalCurrencyAddress;
use App\Models\DigitalCurrencySet;
use App\Models\UserCashInfo;
use App\Models\UserLevelModel;
use App\Models\UsersWalletOutBank;
use App\Models\UserUsdtInfo;
use App\Models\WireTransferAccount;
use App\Models\WireTransferCurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use App\Models\Conversion;
use App\Models\FlashAgainst;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Http\Requests;
use App\Models\Currency;
use App\Models\Ltc;
use App\Models\LtcBuy;
use App\Models\TransactionComplete;
use App\Models\NewsCategory;
use App\Models\Address;
use App\Models\AccountLog;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UsersWallet;
use App\Models\UsersWalletOut;
use App\Models\WalletLog;
use App\Models\Levertolegal;
use App\Models\LeverTransaction;
use App\Jobs\UpdateBalance;
use App\Jobs\SendTelegramRechargeNotification;
use App\Notifications\ChargeReqOrderAlert;
use App\Notifications\WithdrawOrderAlert;

class WalletController extends Controller
{
    //我的资产
    public function walletList(Request $request)
    {
        $currency_name = $request->input('currency_name', '');
        $user_id = $request->user()->id;
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
                $query->where("is_display", 1);
            })->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();


        $legal_wallet['totle'] = 0;
        $legal_wallet['usdt_totle'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            if (in_array($v['currency'], [3])) {
                $legal_wallet['balance'][$k]['is_charge'] = true;
            } else {
                $legal_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            //$legal_wallet['totle'] += $num * $v['cny_price'];
            $legal_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        $legal_wallet['CNY'] = '';
        $change_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_display", 1);
            })->get(['id', 'currency', 'change_balance', 'lock_change_balance'])
            ->toArray();
        $change_wallet['totle'] = 0;
        $change_wallet['usdt_totle'] = 0;
        foreach ($change_wallet['balance'] as $k => $v) {
            if (in_array($v['currency'], [1, 2, 3])) {
                $change_wallet['balance'][$k]['is_charge'] = true;
            } else {
                $change_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['change_balance'] + $v['lock_change_balance'];
            // $change_wallet['totle'] += $num * $v['cny_price'];
            $change_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        $change_wallet['CNY'] = '';
        $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
                $query->where("is_display", 1);
            })->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();
        $lever_wallet['totle'] = 0;
        $lever_wallet['usdt_totle'] = 0;
        foreach ($lever_wallet['balance'] as $k => $v) {
            if (in_array($v['currency'], [])) {
                $lever_wallet['balance'][$k]['is_charge'] = true;
            } else {
                $lever_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            $lever_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }

        $lever_wallet['CNY'] = '';

        $micro_wallet['CNY'] = '';
        $micro_wallet['totle'] = 0;
        $micro_wallet['usdt_totle'] = 0;
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                // $query->where("is_micro", 1);
                $query->where("is_display", 1);
            })->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])
            ->toArray();
        foreach ($micro_wallet['balance'] as $k => $v) {
            if (in_array($v['currency'], [1, 2, 3, 6, 10, 29])) {
                $micro_wallet['balance'][$k]['is_charge'] = true;
            } else {
                $micro_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['micro_balance'] + $v['lock_micro_balance'];
            // $micro_wallet['totle'] += $num * $v['cny_price'];
            $micro_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);

        //读取是否开启充提币
        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;
        return $this->success('', 0, [
            'legal_wallet' => $legal_wallet,
            'change_wallet' => $change_wallet,
            'micro_wallet' => $micro_wallet,
            'lever_wallet' => $lever_wallet,
            'ExRate' => $ExRate,
            "is_open_CTbi" => $is_open_CTbi
        ]);
    }

    /**
     * 入金数字货币地址列表
     */
    public function coinTopUpList(): JsonResponse
    {
        $result=DigitalCurrencyAddress::with([])->get();
        return $this->success('',0,$result);
    }

    /**
     * 入金银行卡收款货币列表
     */
    public function coinTopUpBankCurrency(): JsonResponse
    {
        $result=WireTransferCurrency::with([])->get();
        return $this->success('',0,$result);
    }

    /**
     * 入金银行卡信息
     */
    public function coinTopUpBankInfo(Request $request): JsonResponse
    {
        $request->validate([
            'currency_id' => 'required',
        ]);

        $result=WireTransferAccount::with([])->where('wire_transfer_id',$request->currency_id)->where('is_display',1)->first();
        return $this->success('',0,$result);
    }

    /**
     * 提币数字货币列表
     * @return JsonResponse
     */
    public function extractCurrency(): JsonResponse
    {
        $result=DigitalCurrencySet::with([])->where('is_display',1)->get();
        return $this->success('',0,$result);
    }

    /**
     * 提币银行卡货币列表
     * @return JsonResponse
     */
    public function extractBank(): JsonResponse
    {
        $result=DigitalBankSet::with([])->where('is_display',1)->get();
        return $this->success('',0,$result);
    }


    //数字货币充币
    public function chargeReq(Request $request)
    {
        $user_id = $request->user()->id;

        $currency_id = $request->get("currency", '');
        $account = $request->get("account", '');
        $amount = $request->get("amount", 0);
        if (empty($currency_id) || empty($amount)) {
            return $this->error('参数错误');
        }
        $currency = DigitalCurrencyAddress::with([])->where('id', $currency_id)->first();
        if (!$currency) {
            return $this->error('参数错误');
        }
        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error('用户不存在');
        }
        $give=$amount;
        $practical_amount=$amount * $currency->usd_price;
        $data = [
            'uid' => $user_id,
            'currency_id' => $currency->currency_id,
            'amount' => $practical_amount,
            'payment_address' => $currency->payment_address,
            'give' => $give,
            'user_account' => $account,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $id = DB::table('charge_req')->insertGetId($data);
        $admins = \App\Models\Admin::get();
        foreach ($admins as $admin) {
            $admin->notify(new ChargeReqOrderAlert($user->account_number, $id));
        }
        $message = "💬充值通知：\n会员账号：{$user_id} [{$user->email}]\n充值金额：{$practical_amount} USDT\n充值地址：{$currency->payment_address}";
        // SendTelegramRechargeNotification::dispatch($message)->onQueue('default');
        return $this->success('申请成功');
    }


    //数字货币充币记录
    public function rechargeLog(Request $request)
    {
        $user_id = $request->user()->id;
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $lists = ChargeReq::with([])
            ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            ->where('charge_req.uid', $user_id)
            ->select('charge_req.*', 'currency.name')
            ->orderBy('charge_req.id', 'desc')
            ->paginate($limit);

        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('充币记录', 0, $result);
    }

    //银行卡充币
    public function chargeReqBank(Request $request)
    {
        $user_id = $request->user()->id;

        $currency_id = $request->get("currency", '');
        $account = $request->get("account", '');
        $amount = $request->get("amount", 0);
        if (empty($currency_id) || empty($amount)) {
            return $this->error('参数错误');
        }
        $currency = WireTransferAccount::with(['wireTransferCurrency'])->where('id', $currency_id)->first();
        if (!$currency) {
            return $this->error('参数错误');
        }
        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error('用户不存在');
        }
        $give=$amount;
        $practical_amount=$amount*$currency->wireTransferCurrency->usd_price;
        $data = [
            'uid' => $user_id,
            'currency_id' => $currency->wire_transfer_id,
            'amount' => $practical_amount,
            'payment_address' => $currency->payee_account,
            'give' => $give,
            'user_account' => $account,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        Db::table('charge_req_bank')->insert($data);
        return $this->success('申请成功');
    }

    //数字货币充币记录
    public function rechargeBankLog(Request $request)
    {
        $user_id = $request->user()->id;
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $lists = ChargeReqBank::with([])
            ->join('wire_transfer_currency', 'wire_transfer_currency.id', '=', 'charge_req_bank.currency_id')
            ->where('charge_req_bank.uid', $user_id)
            ->select('charge_req_bank.*', 'wire_transfer_currency.name')
            ->orderBy('charge_req_bank.id', 'desc')
            ->paginate($limit);

        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('充币记录', 0, $result);
    }

    public function hasLeverTrade($user_id)
    {
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)
            ->whereNotIn('status', [LeverTransaction::CLOSED, LeverTransaction::CANCEL])
            ->count();
        return $exist_close_trade > 0 ? true : false;
    }


    private $fromArr = [
        'legal' => AccountLog::WALLET_LEGAL_OUT,
        'lever' => AccountLog::WALLET_LEVER_OUT,
        'micro' => AccountLog::WALLET_MCIRO_OUT,
        'change' => AccountLog::WALLET_CHANGE_OUT,
    ];
    private $toArr = [
        'legal' => AccountLog::WALLET_LEGAL_IN,
        'lever' => AccountLog::WALLET_LEVER_IN,
        'micro' => AccountLog::WALLET_MCIRO_IN,
        'change' => AccountLog::WALLET_CHANGE_IN,
    ];
    private $mome = [
        'legal' => 'c2c',
        'lever' => '合约',
        'micro' => '秒合约',
        'change' => '闪兑',
    ];

    public function changeWallet(Request $request)  //BY tian
    {
        $type = [
            'legal' => 1,
            'lever' => 3,
            'micro' => 4,
            'change' => 2,
        ];
        $user_id = $request->user()->id;
        $currency_id = $request->get("currency_id", '');
        $number = $request->get("number", '');

        $user = Users::find($user_id);
        if ($user->frozen_funds == 1) {
            return $this->error('资金已冻结');
        }
        $from_field = $request->get('from_field', "");
        $to_field = $request->get('to_field', "");
        if (empty($from_field) || empty($number) || empty($to_field) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');
        }
        $from_account_log_type = $this->fromArr[$from_field];
        $to_account_log_type = $this->toArr[$to_field];
        $memo = $this->mome[$from_field] . '划转' . $this->mome[$to_field];
        if ($from_field == 'lever') {
            if ($this->hasLeverTrade($user_id)) {
                return $this->error('您有正在进行中的杆杠交易,不能进行此操作');
            }
        }
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception('钱包不存在');
            }
            $result = change_wallet_balance($user_wallet, $type[$from_field], -$number, $from_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            $result = change_wallet_balance($user_wallet, $type[$to_field], $number, $to_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('划转成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('操作失败:' . $e->getMessage());
        }
    }

    public function hzhistory(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);

        $arr = [
            AccountLog::WALLET_LEGAL_OUT,
            AccountLog::WALLET_LEVER_OUT,
            AccountLog::WALLET_MCIRO_OUT,
            AccountLog::WALLET_CHANGE_OUT,
            AccountLog::WALLET_LEGAL_IN,
            AccountLog::WALLET_LEVER_IN,
            AccountLog::WALLET_MCIRO_IN,
            AccountLog::WALLET_CHANGE_IN,
        ];
        $result = AccountLog::where('user_id', $user_id)->whereIn('type', $arr)->orderBy('id', 'desc')->paginate($limit);
        return $this->success($result);

    }

    //数字货币提币
    public function postWalletOut(Request $request)
    {
        $user_id = $request->user()->id;
        $wallet_id= $request->get("wallet_id", '');
        $number = $request->get("number", '');//数量
        $pay_password = $request->get('pay_password', '');//支付密码
        $remark = $request->get("remark", '');
        
        $user = Users::getById($user_id);
        
        
        if (empty($wallet_id) || empty($number)) {
            return $this->error('参数错误');
        }
        if (empty($pay_password)) {
            return $this->error('请输入二级密码');
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');
        }
        if (Users::MakePassword($pay_password) != $user->pay_password) {
            return $this->error('二级密码错误');
        }
        $withdraw_deposit_real = Setting::getValueByKey('withdraw_deposit_real','1');//是否开启实名制出金
        if($withdraw_deposit_real){
            if($user->is_realname != 2){
                return $this->error('请您完成实名认证才能申请提现');
            }
        }
        if ($user->frozen_funds == 1) {
            return $this->error('资金已冻结');
        }
        $walletInfo = UserUsdtInfo::with(['digitalCurrency'])->where('id',$wallet_id)->first();

        if ($number < $walletInfo->digitalCurrency->min_number) {
            return $this->error('数量不能少于最小值');
        }
        if ($number > $walletInfo->digitalCurrency->max_number) {
            return $this->error('数量不能大于最大值');
        }
        
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        
        $todayWithdrawCount = UsersWalletOut::where('user_id', $user_id)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();
        if ($todayWithdrawCount >= $walletInfo->digitalCurrency->number) {
            return $this->error("每日最多只能提币 {$walletInfo->digitalCurrency->number} 次");
        }
        
        try {
            DB::beginTransaction();
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', 1)->lockForUpdate()->first();

            if ($number > $wallet->change_balance) {
                DB::rollBack();
                return $this->error('余额不足');
            }

           if($walletInfo->digitalCurrency->service_charge_type==1){//计算手续费
               $rate=$number*$walletInfo->digitalCurrency->rate;
           }else{
               $rate=$walletInfo->digitalCurrency->rate;
           }
            $real_number=($number-$rate)*$walletInfo->digitalCurrency->exchange_rate;

            $walletOut = new UsersWalletOut();
            $walletOut->user_id = $user_id;
            $walletOut->currency = $walletInfo->digitalCurrency->name;
            $walletOut->number = $number;
            $walletOut->address = $walletInfo->account;
            $walletOut->remark = $remark;
            $walletOut->rate = $rate;
            $walletOut->real_number = $real_number;
            $walletOut->create_time = time();
            $walletOut->created_at = date('Y-m-d H:i:s');
            $walletOut->status = 1;
            $walletOut->save();

            $result = change_wallet_balance($wallet, 2, -$number, AccountLog::WALLETOUT, '申请提币抵扣余额');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, 2, $number, AccountLog::WALLETOUT, '申请提币并锁定余额', true);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            $admins = \App\Models\Admin::get();
            foreach ($admins as $admin) {
                $admin->notify(new WithdrawOrderAlert($user->account_number, $walletOut->id));
            }
            $message = "💬提款通知：\n会员账号：{$user_id} [{$user->account_number}]\n提款金额：{$number} USDT\n手续费：{$rate}\n到账金额：{$real_number}\n提款地址：{$walletInfo->account}\n备注：{$remark}";
            // SendTelegramRechargeNotification::dispatch($message)->onQueue('default');
            return $this->success('提币申请已成功，等待审核');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }


    //数字货币提币记录
    public function withdrawList(Request $request)
    {
        $user_id = $request->user()->id;
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $lists = UsersWalletOut::with([])
            ->where('users_wallet_out.user_id', $user_id)
            ->orderBy('users_wallet_out.id', 'desc')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('提币记录', 0, $result);
    }

    //银行卡提币
    public function postWalletOutBank(Request $request)
    {
        $user_id = $request->user()->id;
        $wallet_id= $request->get("wallet_id", '');
        $number = $request->get("number", '');//数量
        $remark = $request->get("remark", '');
        if (empty($wallet_id) || empty($number)) {
            return $this->error('参数错误');
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');
        }
        $user = Users::getById($user_id);
        $withdraw_deposit_real = Setting::getValueByKey('withdraw_deposit_real','1');//是否开始实名制出金
        if($withdraw_deposit_real){
            if ($user->status == 1) {
                return $this->error("用户无效");
            }
        }
        if ($user->frozen_funds == 1) {
            return $this->error('资金已冻结');
        }
        $walletInfo = UserCashInfo::with(['digitalBankSet'])->where('id',$wallet_id)->first();

        if ($number < $walletInfo->digitalBankSet->min_number) {
            return $this->error('数量不能少于最小值');
        }
        if ($number > $walletInfo->digitalBankSet->max_number) {
            return $this->error('数量不能大于最大值');
        }
        try {
            DB::beginTransaction();
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', 1)->lockForUpdate()->first();

            if ($number > $wallet->change_balance) {
                DB::rollBack();
                return $this->error('余额不足');
            }

            if($walletInfo->digitalBankSet->service_charge_type==1){//计算手续费
                $rate=$number*$walletInfo->digitalBankSet->rate;
            }else{
                $rate=$walletInfo->digitalBankSet->rate;
            }
            $real_number=($number-$rate)*$walletInfo->digitalBankSet->exchange_rate;

            $walletOut = new UsersWalletOutBank();
            $walletOut->user_id = $user_id;
            $walletOut->currency = $walletInfo->digitalBankSet->name;
            $walletOut->number = $number;
            $walletOut->bank_name = $walletInfo->bank_name;
            $walletOut->real_name = $walletInfo->real_name;
            $walletOut->address = $walletInfo->bank_account;
            $walletOut->swift = $walletInfo->swift;
            $walletOut->remark = $remark;
            $walletOut->rate = $rate;
            $walletOut->real_number = $real_number;
            $walletOut->create_time = time();
            $walletOut->created_at = date('Y-m-d H:i:s');
            $walletOut->status = 1;
            $walletOut->save();

            $result = change_wallet_balance($wallet, 2, -$number, AccountLog::WALLETOUT, '申请提币抵扣余额');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, 2, $number, AccountLog::WALLETOUT, '申请提币并锁定余额', true);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('提币申请已成功，等待审核');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    //银行卡提币记录
    public function withdrawListBank(Request $request)
    {
        $user_id = $request->user()->id;
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $lists = UsersWalletOutBank::with([])
            ->where('users_wallet_out_bank.user_id', $user_id)
            ->orderBy('users_wallet_out_bank.id', 'desc')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success('提币记录', 0, $result);
    }

    //用户钱包详情
    public function getWalletDetail(Request $request)
    {
        $user_id = $request->user()->id;
        $currency_id = $request->get("currency", '');
        $type = $request->get("type", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);
        if ($type == 'legal') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'legal_balance', 'lock_legal_balance', 'address']);
        } else if ($type == 'change') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'change_balance', 'lock_change_balance', 'address']);

        } else if ($type == 'lever') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'lever_balance', 'lock_lever_balance', 'address']);
        } else if ($type == 'micro') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'micro_balance', 'lock_micro_balance', 'address']);
        } else {
            return $this->error('类型错误');
        }
        if (empty($wallet)) return $this->error("钱包未找到");

        $wallet->ExRate = $ExRate;

        if (in_array($wallet->currency, [1, 2, 3])) {
            $wallet->is_charge = true;
        } else {
            $wallet->is_charge = false;
        }

        $wallet->coin_trade_fee = Setting::getValueByKey('COIN_TRADE_FEE');
        return $this->success('', 0, $wallet);
    }
    //财务记录
    public function legalLog(Request $request)
    {

        $limit = $request->get('limit', 10);
        $currency = $request->get('currency', 0);
        $type = $request->get('type', 0);
        $user_id = $request->user()->id;
        $list = new AccountLog();
        if (!empty($currency)) {
            $list = $list->where('currency', $currency);
        }
        if (!empty($user_id)) {
            $list = $list->where('user_id', $user_id);
        }
        if (!empty($type)) {
            $list = $list->whereHas('walletLog', function ($query) use ($type) {
                $query->where('balance_type', $type);
            });
        }
        $list = $list->orderBy('id', 'desc')->paginate($limit);

        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;

        return $this->success('列表', 0, array(
            "list" => $list->items(), 'count' => $list->total(),
            "limit" => $limit,
            "is_open_CTbi" => $is_open_CTbi
        ));
    }
}
