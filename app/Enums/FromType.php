<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class FromType extends Enum
{
    // 积分来源

    // 核心操作项
    const ADMIN = -1; // 后台
    const DEFAULT = 0; // 默认
    const ORDER = 1; // 订单
    const ORDER_CANCEL = 2; // 订单取消
    const ORDER_REFUND = 3; // 订单退款
    const RECHARGE = 4; // Deposit
    const RECHARGE_CANCEL = 5; // 充值取消
    const GIVE_OUT = 6; // 赠送转出
    const GIVE_INTO = 7; // 赠送转入
    const WITHDRAW = 8; // 提现
    const WITHDRAW_CANCEL = 9; // 取消提现
    const CREDIT_EXCHANGE = 10; // 积分兑换
    const CREDIT_EXCHANGE_CANCEL = 11; // 积分兑换取消
    const CREDIT_GIVE_OUT = 12; // 积分转账转出
    const CREDIT_GIVE_INTO = 13; // 积分转账转入

    // 佣金项
    const COMMISSION_1 = 21; // 佣金1级
    const COMMISSION_2 = 22; // 佣金2级
    const COMMISSION_3 = 23; // 佣金3级
    const PARENT_1 = 24; // 推荐1级
    const PARENT_2 = 25; // 推荐2级
    const PARENT_3 = 26; // 推荐3级

    // 订单项

    // 其他杂项
    const INVITE = 51; // 邀请
    const REGISTER = 52; // 注册
    const SIGN = 53; // 签到

    public static function parseDatabase($value): int
    {
        return (int)$value;
    }

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::DEFAULT:
                return '默认';
                break;
            case self::ADMIN:
                return '后台';
                break;
            case self::ORDER:
                return '订单';
                break;
            case self::ORDER_CANCEL:
                return '订单取消';
                break;
            case self::ORDER_REFUND:
                return '订单退款';
                break;
            case self::RECHARGE:
                return 'Deposit';
                break;
            case self::RECHARGE_CANCEL:
                return '充值取消';
                break;
            case self::GIVE_OUT:
                return '赠送转出';
                break;
            case self::GIVE_INTO:
                return '赠送转入';
                break;
            case self::WITHDRAW:
                return '提现';
                break;
            case self::WITHDRAW_CANCEL:
                return '取消提现';
                break;
            case self::CREDIT_EXCHANGE:
                return '积分兑换';
                break;
            case self::CREDIT_EXCHANGE_CANCEL:
                return '积分兑换取消';
                break;
            case self::CREDIT_GIVE_OUT:
                return '积分转账转出';
                break;
            case self::CREDIT_GIVE_INTO:
                return '积分转账转入';
                break;
            case self::COMMISSION_1:
                return '佣金1级';
                break;
            case self::COMMISSION_2:
                return '佣金2级';
                break;
            case self::COMMISSION_3:
                return '佣金3级';
                break;
            case self::PARENT_1:
                return '推荐1级';
                break;
            case self::PARENT_2:
                return '推荐2级';
                break;
            case self::PARENT_3:
                return '推荐3级';
                break;
            case self::INVITE:
                return '邀请';
                break;
            case self::REGISTER:
                return '注册';
                break;
            case self::SIGN:
                return '签到';
                break;
            default:
                return self::getKey($value);
        }
    }
}
