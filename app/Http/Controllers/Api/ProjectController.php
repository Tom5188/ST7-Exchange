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
use App\Models\Project;
use App\Models\ProjectOrder;
use App\Models\ProjectOrderProfit;
use App\Models\Levertolegal;
use App\Models\LeverTransaction;
use App\Jobs\UpdateBalance;

class ProjectController extends Controller
{
    public function projectList(Request $request){
        $limit = $request->input('limit', '12');
        $page = $request->input('page', '1');
        $lists = Project::orderBy('id', 'DESC')->paginate($limit);
         $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            $item->setAttribute('project_img', config('app.url') . "/storage/".$item->project_img );
            return $item;
        });
        $lists->setCollection($items);
        return $this->success('',0,$lists);
    }
    
    public function info(Request $request){
        $id = $request->input('id', '');
        $project = Project::where('id',$id)->first();
        $project->setAttribute('project_img', config('app.url') . "/storage/".$project->project_img );
        return $this->success('',0,$project);
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
                return $this->error('请您完成实名认证才能购买理财');
            }
            $project = Project::where('id',$id)->first();
            throw_unless($project, new \Exception('项目无效'));
            $ordernum = ProjectOrder::where('project_id',$project->id)->where('user_id',$user_id)->where('status', 1)->count();
            if($ordernum >= $project->buynum){
                return $this->error('请勿重复购买理财');
            }
            $wallet = UsersWallet::where('currency', 1)
                ->where('user_id', $user_id)
                ->first();
            throw_unless($wallet, new \Exception('用户钱包不存在'));
            if($wallet->change_balance < $amount){
                return $this->error('余额不足');
            }
            $data = [
                'user_id'  => $user_id,
                'project_id' => $id,
                'amount' => $amount,
                'sub_time' => date('Y-m-d H:i:s', strtotime('today')),
                'day_profit' => bcmul(bcdiv($project['project_lixi'],100),$amount),
                'interest_gen_next_time' => date('Y-m-d H:i:s', strtotime('tomorrow')),
                'sum_profit' => bcmul($project['lock_dividend_days'],bcmul(bcdiv($project['project_lixi'],100),$amount)),
                'lock_dividend_days' => $project['lock_dividend_days'],
                'status' => 1
            ];
            ProjectOrder::unguard();
            ProjectOrder::create($data);
            $result = change_wallet_balance($wallet, 2, -$amount, AccountLog::USER_PROJECT_ORDER_BUY, '理财扣除');
            throw_unless($result === true, new \Exception($result));
             DB::commit();
             return $this->success('',0,$result);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }   
    }
    
    public function projectSettlement()
    {
        $projects = ProjectOrder::where('status', 1)->get();
    
        foreach ($projects as $value) {
            $wallet = UsersWallet::where('currency', 1)
                ->where('user_id', $value->user_id)
                ->first();
    
            if (!$wallet) {
                continue;
            }
    
            while (strtotime($value->interest_gen_next_time) <= time()) {
                // ✅ 检查是否已存在收益记录
                $exists = ProjectOrderProfit::where('order_id', $value->id)
                    ->where('created_time', $value->interest_gen_next_time)
                    ->exists();
            
                if ($exists) {
                    $value->interest_gen_next_time = Carbon::parse($value->interest_gen_next_time)->addDay()->toDateTimeString();
                    $value->already_ettled_day += 1;
                    continue;
                }
                // 写入每日收益记录
                ProjectOrderProfit::create([
                    'order_id' => $value->id,
                    'user_id' => $value->user_id,
                    'value' => $value->day_profit,
                    'created_time' => $value->interest_gen_next_time
                ]);
    
                // 判断是否为最后一天
                if ($value->already_ettled_day + 1 == $value->lock_dividend_days) {
                    $value->already_ettled_day += 1;
                    $value->save();
                    // 返还本金+利息
                    ProjectOrder::where('id', $value->id)->update([
                        'status' => 3
                    ]);
                    change_wallet_balance(
                        $wallet,
                        2,
                        $value->sum_profit + $value->amount,
                        AccountLog::USER_PROJECT_ORDER_END,
                        '订单到期，退还本金 ' . $value->amount . '，结算利息 ' . $value->sum_profit,
                        false, 0, 0, '', false, false, $value->id
                    );
                    break;
                } else {
                    // 继续下一天
                    $value->already_ettled_day += 1;
                    $value->interest_gen_next_time = Carbon::parse($value->interest_gen_next_time)->addDay()->toDateTimeString();
                    $value->save();
                }
            }
        }
    
        return $this->success('ok');
    }

    
    public function orderList(Request $request){
        $user_id = $request->user()->id;
        $limit = $request->input('limit', '12');
        $page = $request->input('page', '1');
        $lists = ProjectOrder::where('user_id',$user_id)->orderBy('id', 'DESC')->paginate($limit);
        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            $item->setAttribute('project', Project::find($item->project_id));
            $item->setAttribute('get_user_info', Users::find($item->user_id));
            return $item;
        });
        $lists->setCollection($items);
        return $this->success('',0,$lists);
    }
    public function applyRefund(Request $request){
        $orderId = $request->input('orderId', '');
        $projectOrder = ProjectOrder::where('id',$orderId)->first();
        $project = Project::where('id',$projectOrder['project_id'])->first();
        $wyj = bcmul($projectOrder['amount'],bcdiv($project['project_default'],100));
        ProjectOrder::where('id',$orderId)->update([
                        'status' => 2,
                        'wyj' => $wyj
                    ]);
        return $this->success('提交成功，等待稽核');
    }
    
    public function getprofit(Request $request){
        $orderId = $request->input('orderId', '');
        $user_id = $request->user()->id;
        $list = ProjectOrderProfit::where('user_id',$user_id)->where('order_id',$orderId)->get();
        return $this->success('',0,$list);
    }
}
