<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BorrowOrder extends BaseModel
{
    protected $table = "borrow_order";
    use HasFactory;

    protected $appends = ['account', 'overdue', 'expire'];

    protected $casts = [
        'sub_time' => 'datetime',
    ];

    protected static $statusList = [
        '',
        '审核中',
        '待还款',
        '借贷完成',
        '借贷拒绝',
    ];

    // 定义用户关系
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    // 账户邮箱
    public function getAccountAttribute()
    {
        return $this->user->email ?? null;
    }

    // 状态名称
    public function getStatusNameAttribute()
    {
        $value = $this->attributes['status'] ?? 0;
        return self::$statusList[$value] ?? '';
    }

    // 是否到期
    public function getExpireAttribute()
    {
        if (!$this->sub_time || !$this->lock_dividend_days) {
            return 0;
        }

        $expireDate = $this->sub_time->copy()->addDays($this->lock_dividend_days);
        return now()->lt($expireDate) ? 0 : 1;
    }

    // 是否逾期
    public function getOverdueAttribute()
    {
        if (!$this->sub_time || !$this->lock_dividend_days) {
            return 0;
        }

        $overdueDate = $this->sub_time->copy()->addDays($this->lock_dividend_days + 3);
        return now()->lt($overdueDate) ? 0 : 1;
    }
}