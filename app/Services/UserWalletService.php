<?php

namespace App\Services;

use App\Models\Users;
use App\Models\WalletType;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CastServiceInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserWalletService
{
    /**
     * 写入钱包数据
     * @param  int  $uid  用户 ID
     * @param  int  $wallet_type_id  钱包类型 ID
     * @param  float|int  $money  操作金额
     * @param  array|null  $remark  备注
     * @return true
     * @throws ExceptionInterface
     * @throws \Throwable
     */
    public function store(int $uid, int $wallet_type_id, float|int $money, ?array $remark = null)
    {
        $user = Users::find($uid); // 获取用户信息
        $this->checkWallet($uid); // 检测用户钱包
        $wallet_type = WalletType::find($wallet_type_id); // 获取钱包类型信息
        $name = $wallet_type->slug; // 钱包代码
        $wallet = $user->getWallet($name); // 获取用户指定钱包

        DB::beginTransaction(); // 开始事务
        try {
            $key = 'lock_'.$wallet_type_id.'_'.$uid; // 钱包缓存 key
            $wallet->balanceInt; // 钱包进入事务锁定模式
            // 如果钱包带小数点
            if ($wallet->decimal_places > 0) {
                if ($money > 0) {
                    $wallet->depositFloat($money, $remark); // 增加
                }
                if ($money < 0) {
                    $wallet->withdrawFloat(abs($money), $remark); // 减少
                }
            } else {
                if ($money > 0) {
                    $wallet->deposit($money, $remark); // 增加
                }
                if ($money < 0) {
                    $wallet->withdraw(abs($money), $remark); // 减少
                }
            }

            \Cache::forget($key); // 清除钱包缓存
            DB::commit(); // 结束事务
            return true;
        } catch (\Exception $e) {
            DB::rollBack(); // 回滚事务
            // 异常处理
            Log::error(__METHOD__.'|'.__METHOD__.'-UserWalletService-store-执行失败', ['error' => $e]);
            return false;
        }
    }

    /**
     * 检测用户钱包,没有的钱包类型自动创建
     * @param  int  $uid  用户 ID
     * @return void
     */
    public function checkWallet(int $uid): void
    {
        $user = Users::find($uid); // 获取用户信息
        $lists = WalletType::where('is_enabled', '=', 1)->get(); // 钱包类型列表: 状态为 启用 的
        foreach ($lists as $value) {
            $check = $user->hasWallet($value->slug); // 用户是否有某类型钱包
            if (!$check) {
                $user->createWallet([
                    'name' => $value->name, // 钱包名称
                    'slug' => $value->slug, // 钱包代码
                    'description' => '用户 '.$user->id.' 的 '.$value->description, // 钱包介绍
                    'decimal_places' => $value->decimal_places, // 钱包小数位数
                ]); // 创建钱包
            }
        }
    }

    /**
     * 获得用户指定钱包余额
     * @param  int  $uid  用户 ID
     * @param  int  $wallet_type_id  钱包类型 ID
     * @return mixed
     * @throws \Throwable
     */
    public function checkBalance(int $uid, int $wallet_type_id): mixed
    {
        $user = Users::find($uid); // 获取用户信息
        $this->checkWallet($uid); // 检测用户钱包
        $wallet_type = WalletType::find($wallet_type_id); // 获取钱包类型信息
        $name = $wallet_type->slug; // 钱包代码
        $wallet = $user->getWallet($name); // 获取用户指定钱包

        DB::beginTransaction(); // 开始事务
        $wallet->refreshBalance(); // 强制刷新用户该钱包余额
        $wallet->balanceInt; // 钱包进入事务锁定模式
        // 如果钱包带小数点
        if ($wallet->decimal_places > 0) {
            $money = $wallet->balanceFloat; // 获取指定钱包余额 带小数点
        } else {
            $money = $wallet->balance; // 获取指定钱包余额
        }
        DB::commit(); // 结束事务

        return $money;
    }

    /**
     * 用户指定钱包昨日增加金额
     * @param  int  $uid  用户 ID
     * @param  int  $wallet_type_id  钱包类型 ID
     * @return int|string
     */
    public function yesterday(int $uid, int $wallet_type_id): int|string
    {
        $user = Users::find($uid); // 获取用户信息
        $this->checkWallet($uid); // 检测用户钱包
        $wallet_type = WalletType::find($wallet_type_id); // 获取钱包类型信息
        $name = $wallet_type->slug; // 钱包代码
        $wallet = $user->getWallet($name); // 获取用户指定钱包

        $data = $wallet->transactions()
            ->where('type', '=', Transaction::TYPE_DEPOSIT)
            ->where('wallet_id', '=', $wallet->id)
            ->whereDate('created_at', '=', Carbon::yesterday())
            ->sum('amount'); // 昨日增加金额
        if ($data <= 0) {
            return 0;
        } else {
            $math = app(MathServiceInterface::class);
            $decimalPlacesValue = app(CastServiceInterface::class)
                ->getWallet($wallet)
                ->decimal_places;
            $decimalPlaces = $math->powTen($decimalPlacesValue);

            return $math->div($data, $decimalPlaces, $decimalPlacesValue); // 格式化输出
        }
    }

    /**
     * 用户指定钱包累计收入金额
     * @param  int  $uid  用户 ID
     * @param  int  $wallet_type_id  钱包类型 ID
     * @return int|string
     */
    public function total(int $uid, int $wallet_type_id): int|string
    {
        $user = Users::find($uid); // 获取用户信息
        $this->checkWallet($uid); // 检测用户钱包
        $wallet_type = WalletType::find($wallet_type_id); // 获取钱包类型信息
        $name = $wallet_type->slug; // 钱包代码
        $wallet = $user->getWallet($name); // 获取用户指定钱包
        $data = $wallet->transactions()
            ->where('type', '=', Transaction::TYPE_DEPOSIT)
            ->where('wallet_id', '=', $wallet->id)
            ->sum('amount'); // 累计收入
        if ($data <= 0) {
            return 0;
        } else {
            $math = app(MathServiceInterface::class);
            $decimalPlacesValue = app(CastServiceInterface::class)
                ->getWallet($wallet)
                ->decimal_places;
            $decimalPlaces = $math->powTen($decimalPlacesValue);

            return $math->div($data, $decimalPlaces, $decimalPlacesValue); // 格式化输出
        }
    }

    /**
     * 指定类型钱包当前总余额
     * @param  int  $wallet_type_id  钱包类型 ID
     * @return int|string
     */
    public function walletBalance(int $wallet_type_id): int|string
    {
        $wallet_type = WalletType::find($wallet_type_id); // 获取钱包类型信息
        $name = $wallet_type->slug; // 钱包代码

        $data = Wallet::where('slug', $name)
            ->sum('balance'); // 指定类型钱包当前总余额
        $wallet = Wallet::where('slug', $name)->first(); // 指定钱包信息
        if ($wallet) {
            if ($data <= 0) {
                return 0;
            } else {
                $math = app(MathServiceInterface::class);
                $decimalPlacesValue = app(CastServiceInterface::class)
                    ->getWallet($wallet)
                    ->decimal_places;
                $decimalPlaces = $math->powTen($decimalPlacesValue);

                return $math->div($data, $decimalPlaces, $decimalPlacesValue); // 格式化输出
            }
        } else {
            return 0;
        }
    }

    /**
     * 指定类型钱包累计收入
     * @param  int  $wallet_type_id  钱包类型 ID
     * @return int|string
     */
    public function walletTotal(int $wallet_type_id): int|string
    {
        $wallet_type = WalletType::find($wallet_type_id); // 获取钱包类型信息
        $name = $wallet_type->slug; // 钱包代码

        $wallet = Wallet::where('slug', $name)->first(); // 指定钱包信息
        $list = Wallet::where('slug', $name)->get(); // 指定钱包列表
        $ids = [];

        foreach ($list as $key => $value) {
            $ids[] = $value['id'];
        }
        $ids = array_values($ids); // 处理钱包 ID

        $data = Transaction::where('type', '=', Transaction::TYPE_DEPOSIT)
            ->whereIn('wallet_id', $ids)
            ->sum('amount'); // 指定类型钱包累计收入
        if ($data <= 0) {
            return 0;
        } else {
            $math = app(MathServiceInterface::class);
            $decimalPlacesValue = app(CastServiceInterface::class)
                ->getWallet($wallet)
                ->decimal_places;
            $decimalPlaces = $math->powTen($decimalPlacesValue);

            return $math->div($data, $decimalPlaces, $decimalPlacesValue); // 格式化输出
        }
    }

    /**
     * 获取某个用户的所有钱包余额
     * @param  int  $uid 用户 ID
     * @return array
     */
    public function getUserWallets(int $uid): array
    {
        $this->checkWallet($uid); // 检测用户钱包
        $data = [];
        $list = Users::with('wallets')->find($uid);
        foreach ($list->wallets as $v) {
            $type = WalletType::where('slug','=',$v->slug)->first();
            $name = strtolower($v->slug);
            $data[$name.'_id'] = $type->id;
            $data[$name.'_name'] = $v->name;
            $data[$name.'_balance'] = $v->balanceFloat;
        }

        return $data;
    }
}
