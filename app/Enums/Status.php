<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Status extends Enum
{
    // 是否显示 0-否 1-是
    const HIDDEN = 0;
    const SHOW = 1;

    public static function parseDatabase($value): int
    {
        return (int) $value;
    }

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::HIDDEN:
                return '否';
                break;
            case self::SHOW:
                return '是';
                break;
            default:
                return self::getKey($value);
        }
    }
}
