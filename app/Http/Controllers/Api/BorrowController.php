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
use App\Models\Borrow;
use App\Models\BorrowOrder;
use App\Models\Levertolegal;
use App\Models\LeverTransaction;
use App\Jobs\UpdateBalance;

class BorrowController extends Controller
{
    public function borrowList(Request $request){
        $limit = $request->input('limit', '12');
        $page = $request->input('page', '1');
        $lists = Borrow::orderBy('id', 'DESC')->paginate($limit);
        return $this->success('',0,$lists);
    }
    
    public function info(Request $request){
        $id = $request->input('id', '');
        $borrow = Borrow::where('id',$id)->first();
        return $this->success('',0,$borrow);
    }
    
    public function buy(Request $request){
        $user_id = $request->user()->id;
        $id = $request->post('id', '');
        $amount = $request->post('amount', 0);
        
        
        try {
            DB::beginTransaction();
            $user = Users::find($user_id);
            throw_unless($user, new \Exception('用户无效'));
            
            if($user->is_realname != 2){
                return $this->error('请您完成实名认证才能申请贷款');
            }
            
            $borrow = Borrow::where('id',$id)->first();
            throw_unless($borrow, new \Exception('项目无效'));
            
            $borrowOrder = BorrowOrder::where('user_id', $user_id)->whereIn('status', [1, 2])->first();
            if($borrowOrder){
                return $this->error('有未完成订单');
            }
            $margin = $amount * $borrow['borrow_margin'];//保证金
            $wallet = UsersWallet::where('currency', 1)
                ->where('user_id', $user_id)
                ->first();
            throw_unless($wallet, new \Exception('用户钱包不存在'));
            if($wallet->change_balance < $margin){
                return $this->error('余额不足');
            }
            
            $data = [
                'user_id'  => $user_id,
                'borrow_id' => $id,
                'margin' => $borrow['borrow_margin'],
                'amount' => $amount,
                'sub_time' => '',
                'borrow_desc' => '',
                'day_profit' => $borrow['borrow_lixi'],
                'sum_profit' => bcmul($borrow['lock_dividend_days'],bcmul($borrow['borrow_lixi'], $amount)),
                'lock_dividend_days' => $borrow['lock_dividend_days'],
                'status' => 1
            ];
            
            BorrowOrder::unguard();
            BorrowOrder::create($data);
            $result = change_wallet_balance($wallet, 2, -$margin, AccountLog::USER_BORROW_ORDER_BUY_MARGIN, '借贷保证金扣除');
            throw_unless($result === true, new \Exception($result));
            DB::commit();
            return $this->success('申请成功');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }   
    }
    
    public function orderList(Request $request){
        $user_id = $request->user()->id;
        $limit = $request->input('limit', '12');
        $page = $request->input('page', '1');
        $lists = BorrowOrder::where('user_id',$user_id)->orderBy('id', 'DESC')->paginate($limit);
        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            $item->setAttribute('borrow', Borrow::find($item->borrow_id));
            $item->setAttribute('get_user_info', Users::find($item->user_id));
            return $item;
        });
        $lists->setCollection($items);
        return $this->success('', 0, $lists);
    }
    
    public function applyRefund(Request $request){
        $user_id = $request->user()->id;
        $orderId = $request->input('orderId', '');
        try {
            DB::beginTransaction();
            $user = Users::find($user_id);
            throw_unless($user, new \Exception('用户无效'));
            
            $borrowOrder = BorrowOrder::where('id',$orderId)->where('status',2)->first();
            throw_unless($borrowOrder, new \Exception('订单无效'));
            
            $borrow = Borrow::where('id',$borrowOrder['borrow_id'])->first();
            throw_unless($borrow, new \Exception('项目无效'));
            
            $wallet = UsersWallet::where('currency', 1)
                ->where('user_id', $user_id)
                ->first();
            throw_unless($wallet, new \Exception('用户钱包不存在'));
            
            $amount = $borrowOrder['amount'] + $borrowOrder['sum_profit'];
            
            if($wallet->change_balance < $amount){
                return $this->error('余额不足');
            }
            $result = change_wallet_balance($wallet, 2, -$amount, AccountLog::USER_BORROW_ORDER_RETURN, '借贷还款扣除借贷金额'.$borrowOrder['amount'].'和利息'.$borrowOrder['sum_profit']);
            $margin = $borrowOrder['amount'] * $borrowOrder['margin'];//保证金
            change_wallet_balance($wallet, 2, $margin , AccountLog::USER_BORROW_ORDER_BUY_MARGIN, '借贷保证金退回');
            BorrowOrder::with([])->where('id',$orderId)->update(['is_return'=>1,'status'=>3,'updated_at'=>date('Y-m-d H:i:s')]);
            DB::commit();
            return $this->success('还款成功');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
