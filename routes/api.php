<?php

use App\Http\Controllers\Agent\CapitalController;
use App\Http\Controllers\Agent\MemberController;
use App\Http\Controllers\Agent\OrderController;
use App\Http\Controllers\Agent\ReportController;
use App\Http\Controllers\Api\AliMarketController;
use App\Http\Controllers\Api\AuthorizationsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DefaultController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\LeverController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MicroOrderController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\OptionalController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Agent\UserController as AgentUser;
use App\Http\Controllers\Api\UtilsController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\BorrowController;
use App\Http\Controllers\Api\MailMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\ChargeReq;
use App\Models\UsersWalletOut;
use App\Models\UserReal;
use App\Models\Setting;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        Route::middleware(['throttle:' . config('api.rate_limits.access'),'lang'])
            ->group(function () {
                Route::get('check-chainreq-alert', function () {
                    $deal_real = Setting::getValueByKey('prompt_tone','1');
                    if($deal_real) {
                        $ChainreqOrderNum = ChargeReq::where('status', 1)->count();
                        if ($ChainreqOrderNum) {
                            return ['should_alert' => true, 'count' => $ChainreqOrderNum];
                        }
                    }
                    return ['should_alert' => false, 'count' => 0];
                });
                Route::get('check-withdraw-alert', function () {
                    $deal_real = Setting::getValueByKey('prompt_tone','1');
                    if($deal_real) {
                        $WithdrawOrderNum = UsersWalletOut::where('status', 1)->count();
                        if ($WithdrawOrderNum) {
                            return ['should_alert' => true, 'count' => $WithdrawOrderNum];
                        }
                    }
                    return ['should_alert' => false, 'count' => 0];
                });
                Route::get('check-realname-alert', function () {
                    $deal_real = Setting::getValueByKey('prompt_tone','1');
                    if($deal_real) {
                        $RealNameNum = UserReal::where('review_status', 1)->where('simulation', 0)->count();
                        if ($RealNameNum) {
                            return ['should_alert' => true, 'count' => $RealNameNum];
                        }
                    }
                    return ['should_alert' => false, 'count' => 0];
                });
                Route::get('test', [IndexController::class, 'test']);//生成验证码
                Route::post('sms_mail', [SmsController::class, 'sendMail']);//发送邮件（注册验证码）
                Route::post('user/register', function(Request $request) {
                    $request->merge(['type' => 'email']);
                    return app(LoginController::class)->register($request);
                });
                
                Route::post('user/phoneregister', function(Request $request) {
                    $request->merge(['type' => 'mobile']);
                    return app(LoginController::class)->register($request);
                });
                Route::get('verification', [LoginController::class, 'verification']);//生成验证码
                Route::post('user/login', [LoginController::class, 'login']);//用户登陆

                Route::get('generateAccount', [UserController::class, 'generateAccount']);//生成模拟用户

                Route::post('adminLogin', [LoginController::class, 'adminLogin']);//登录点差控制器后台
                Route::post('agent/login', [MemberController::class, 'login']);//登录代理商后台

                Route::get('/api/menu', [DefaultController::class, 'getMenu']);//获取菜单
                Route::get('getSiteConfig', [DefaultController::class, 'getSiteConfig']); // 获取系统信息
                Route::get('seconds', [MicroOrderController::class, 'seconds']); // 获取秒合约时间盈利

                Route::get('getPrivacyPolicy', [NewsController::class, 'getPrivacyPolicy'])->withoutMiddleware('simulation'); //隐私政策
                Route::get('getUserAgreement', [NewsController::class, 'getUserAgreement'])->withoutMiddleware('simulation'); //用户协议
                Route::get('getWindowAnnouncement', [NewsController::class, 'getWindowAnnouncement'])->withoutMiddleware('simulation'); //用户协议
                Route::get('initTestDay', [AliMarketController::class, 'initTestDay']); //
                Route::get('initTest', [AliMarketController::class, 'initTest']); //
                Route::get('projectSettlement', [ProjectController::class, 'projectSettlement']); //
                Route::get('initTest1', [AliMarketController::class, 'initTest1']); //
                Route::get('indexTest', [AliMarketController::class, 'indexTest']); //
                Route::get('getTest', [AliMarketController::class, 'getTest']); //
                Route::get('historyD', [AliMarketController::class, 'historyD']); //
                Route::get('getTest30', [AliMarketController::class, 'getTest30']); //
                Route::get('getTest60', [AliMarketController::class, 'getTest60']); //
                Route::get('getTest1', [AliMarketController::class, 'getTest1']); //
                Route::get('queueTest', [AliMarketController::class, 'queueTest']); // 队列测试
                // Route::post('test', [CurrencyController::class, 'klineMarketHome'])->withoutMiddleware('simulation'); // 币种列表
                // Route::post('klineMarketHome', [CurrencyController::class, 'klineMarketHome'])->middleware(null); // 币种列表
                Route::post('klineMarketHome', [CurrencyController::class, 'klineMarket'])->middleware(null); // 币种列表
                Route::post('forgetPassword', [LoginController::class, 'forgetPassword']); // 重置密码
                 
                // 登录后可以访问的接口
                Route::middleware(['auth:api','simulation','freeze'])->group(function () {//->withoutMiddleware('simulation')开启模拟用户功能
                    Route::post('upload', [UtilsController::class, 'upload']); // 文件上传接口
                    Route::post('updatePassword', [LoginController::class, 'updatePassword']); // 修改密码
                    Route::post('updatePayPassword', [LoginController::class, 'updatePayPassword']); // 修改支付密码
                    Route::post('realState', [UserController::class, 'realState']); // 实名认证状态
                    Route::post('saveUserReal', [UserController::class, 'saveUserReal']); // 实名认证
                    Route::get('user', [UserController::class, 'info'])->withoutMiddleware('simulation'); // 当前登录用户信息
                    Route::post('wallet/list', [WalletController::class, 'walletList']);//用户钱包列表
                    Route::post('userWalletList', [UserController::class, 'userWalletList']);//用户钱包地址列表
                    Route::post('userWalletSave', [UserController::class, 'userWalletSave']);//用户钱包地址新增
                    Route::post('userWalletDelete', [UserController::class, 'userWalletDelete']);//用户钱包地址删除
                    Route::post('wallet/detail', [WalletController::class, 'getWalletDetail']);//用户钱包详情

                    Route::get('coinTopUpList', [WalletController::class, 'coinTopUpList']); // 入金地址列表
                    Route::get('coinTopUpBankCurrency', [WalletController::class, 'coinTopUpBankCurrency']); // 入金银行卡货币列表
                    Route::get('coinTopUpBankInfo', [WalletController::class, 'coinTopUpBankInfo']); // 入金银行卡信息

                    Route::get('extractCurrency', [WalletController::class, 'extractCurrency']); // 提币数字货币列表
                    Route::get('extractBank', [WalletController::class, 'extractBank']); // 提币银行卡货币列表

                    Route::post('saveCashInfo', [UserController::class, 'saveCashInfo']); // 添加&修改 银行卡
                    Route::get('cashInfo', [UserController::class, 'cashInfo']); // 银行卡列表
                    Route::get('cashDelete', [UserController::class, 'cashDelete']); // 删除银行卡

                    Route::post('saveUsdtInfo', [UserController::class, 'saveUsdtInfo']); // 添加&修改 数字货币
                    Route::get('usdtInfo', [UserController::class, 'usdtInfo']); // 数字货币列表
                    Route::get('usdtDelete', [UserController::class, 'usdtDelete']); // 删除数字货币

                    Route::post('wallet/legalLog', [WalletController::class, 'legalLog'])->withoutMiddleware('simulation');//财务记录
 
                    Route::post('chargeReq', [WalletController::class, 'chargeReq']);//数字货币充币
                    Route::post('rechargeLog', [WalletController::class, 'rechargeLog']);//数字货币充币记录
                    Route::post('chargeReqBank', [WalletController::class, 'chargeReqBank']);//银行卡充币
                    Route::post('rechargeBankLog', [WalletController::class, 'rechargeBankLog']);//银行卡充币记录

                    Route::post('postWalletOut', [WalletController::class, 'postWalletOut']);//数字货币提币
                    Route::post('withdrawList', [WalletController::class, 'withdrawList']);//数字货币提币记录
                    Route::post('postWalletOutBank', [WalletController::class, 'postWalletOutBank']);//银行卡提币
                    Route::post('withdrawListBank', [WalletController::class, 'withdrawListBank']);//银行卡提币记录

                    Route::get('borrowList', [BorrowController::class, 'borrowList'])->withoutMiddleware('simulation');//借贷列表
                    Route::get('borrow/info', [BorrowController::class, 'info'])->withoutMiddleware('simulation');//项目详情
                    Route::post('borrow/buy', [BorrowController::class, 'buy'])->withoutMiddleware('simulation');//申请借贷
                    Route::get('borrow/order/list', [BorrowController::class, 'orderList'])->withoutMiddleware('simulation');//借贷订单列表
                    Route::post('borrow/order/applyRefund', [BorrowController::class, 'applyRefund'])->withoutMiddleware('simulation');//借贷还款
                    
                    Route::get('projectList', [ProjectController::class, 'projectList'])->withoutMiddleware('simulation');//项目列表
                    Route::get('project/info', [ProjectController::class, 'info'])->withoutMiddleware('simulation');
                    Route::post('project/buy', [ProjectController::class, 'buy'])->withoutMiddleware('simulation');
                    Route::get('project/order/list', [ProjectController::class, 'orderList'])->withoutMiddleware('simulation');
                    // Route::post('project/order/applyRefund', [ProjectController::class, 'applyRefund'])->withoutMiddleware('simulation');
                    Route::get('project/order/profit', [ProjectController::class, 'getprofit'])->withoutMiddleware('simulation');
                    
                    Route::get('quotation_new', [CurrencyController::class, 'newQuotation'])->withoutMiddleware('simulation'); // 币种列表
                    Route::post('klineMarket', [CurrencyController::class, 'klineMarket'])->withoutMiddleware('simulation'); // 币种K线图详情

                    Route::get('optional/list', [OptionalController::class, 'list'])->withoutMiddleware('simulation'); // 我的收藏
                    Route::post('optional/add', [OptionalController::class, 'add'])->withoutMiddleware('simulation'); // 添加收藏
                    Route::post('optional/del', [OptionalController::class, 'del'])->withoutMiddleware('simulation'); // 删除收藏
                    
                    //站内信
                    Route::get('message/getCount', [MailMessageController::class, 'getCount'])->withoutMiddleware('simulation');
                    Route::get('message/getList', [MailMessageController::class, 'getList'])->withoutMiddleware('simulation');
                    Route::get('message/detail', [MailMessageController::class, 'detail'])->withoutMiddleware('simulation');

                    //新闻
                    Route::post('getAnnouncement', [NewsController::class, 'getAnnouncement'])->withoutMiddleware('simulation'); //获取公告
                    Route::post('getInformation', [NewsController::class, 'getInformation'])->withoutMiddleware('simulation'); //获取资讯

                    Route::post('news/get', [NewsController::class, 'get'])->withoutMiddleware('simulation'); //获取新闻详情

                    //秒合约
                    Route::post('MicroOrder/submit', [MicroOrderController::class, 'submit'])->withoutMiddleware('simulation'); // 秒合约 下单
                    Route::get('MicroOrder/getResult', [MicroOrderController::class, 'getResult'])->withoutMiddleware('simulation'); // 返回订单结果
                    Route::post('MicroOrder/lists', [MicroOrderController::class, 'lists'])->withoutMiddleware('simulation'); // 秒合约（期权）列表

                    //合约
                    Route::post('lever/submit', [LeverController::class, 'submit'])->withoutMiddleware('simulation'); // 合约订单 提交订单
                    Route::post('lever/myTrade', [LeverController::class, 'myTrade'])->withoutMiddleware('simulation'); // 合约订单 持仓
                    Route::post('lever/setStopPrice', [LeverController::class, 'setStopPrice'])->withoutMiddleware('simulation'); //设置止盈止亏
                    Route::post('lever/close', [LeverController::class, 'close'])->withoutMiddleware('simulation'); //平仓
                    Route::post('lever/cancelTrade', [LeverController::class, 'cancelTrade'])->withoutMiddleware('simulation'); //取消挂单(撤单)
                });
                Route::middleware('auth:admin')->group(function () {
                    Route::get('currencyList', [CurrencyController::class, 'currencyList']); // 币种列表
                    Route::post('generateBrokenLine', [CurrencyController::class, 'generateBrokenLine']); // 生成折线图
                    Route::post('saveQuotation', [CurrencyController::class, 'saveQuotation']); // 保存数据
                    Route::get('myQuotationList', [CurrencyController::class, 'myQuotationList']); // 查询风控数据
                    Route::post('deleteMyQuotation', [CurrencyController::class, 'deleteMyQuotation']); // 删除行情数据
                    Route::post('repair', [CurrencyController::class, 'repair']); // 修复行情
                });
                Route::middleware('auth:agent')->group(function () {
                    Route::get('agent/home', [ReportController::class, 'home']); // 获取主页信息
                    Route::post('get_user_num', [AgentUser::class, 'get_user_num']); // 获取用户管理的统计
                    Route::post('agent/UserLists', [AgentUser::class, 'lists']); // 获取用户管理列表
                    Route::get('walletTotalList', [CapitalController::class, 'walletTotalList']); // 获取用户资金
                    Route::get('userLeverList', [OrderController::class, 'userLeverList']); // 获取用户合约订单

                    Route::get('agent/lists', [MemberController::class, 'lists']); // 获取代理商列表
                    Route::post('addAgent', [MemberController::class, 'addAgent']); // 添加 编辑代理商
                    Route::post('searchUser', [MemberController::class, 'searchUser']); // 添加下级代理商时，查询该用户是否存在，是否已经是代理商等

                    Route::post('getOrderAccount', [OrderController::class, 'get_order_account']); // 获取合约统计数据
                    Route::post('orderList', [OrderController::class, 'order_list']); // 合约订单列表
                    Route::post('microListStatistics', [OrderController::class, 'microListStatistics']); // 获取秒合约统计数据
                    Route::post('microList', [OrderController::class, 'microList']); // 秒合约订单列表

                    Route::get('rechargeList', [CapitalController::class, 'rechargeList']); // 充币列表
                    Route::get('withdrawList', [CapitalController::class, 'withdrawList']); // 提币列表

                    Route::post('changePWD', [MemberController::class, 'changePWD']); // 修改密码
                    Route::post('saveUserInfo', [MemberController::class, 'saveUserInfo']); // 修改资料

                });
            });
    });
