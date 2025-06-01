<?php

namespace App\Models;

use App\Enums\FromType;
use App\Traits\dateTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletLog extends Model
{
    use HasFactory;
    use SoftDeletes;
    use dateTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'wallet_type_id', 'order_id', 'from_user_id', 'day', 'old', 'add', 'new', 'from', 'remark',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'day'=>'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'wallet_slug', 'wallet_icon_url', 'from_text', 'wallet_name',
    ];

    // 获取钱包类型的名称
    public function getWalletSlugAttribute()
    {
        if ($this->wallet_type_id > 0) {
            return WalletType::find($this->wallet_type_id)->slug;
        } else {
            return '';
        }
    }

    // 获取钱包类型的图片
    public function getWalletIconUrlAttribute()
    {
        if ($this->wallet_type_id > 0) {
            return WalletType::find($this->wallet_type_id)->icon_url;
        } else {
            return '';
        }
    }

    public function getFromTextAttribute(): string
    {
        return FromType::getDescription($this->from);
    }

    // 获取钱包类型的名称
    public function getWalletNameAttribute()
    {
        if ($this->wallet_type_id > 0) {
            return WalletType::find($this->wallet_type_id)->name;
        } else {
            return '';
        }
    }

    // 关联 用户
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Users::class);
    }

    // 关联 来自用户
    public function fromUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Users::class,'from_user_id','id');
    }

    // 关联 钱包类型
    public function walletType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WalletType::class);
    }

    // 关联 订单  有订单模块以后解除注释 TODO
//    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
//    {
//        return $this->belongsTo(Order::class);
//    }

    // 查询 大小 正负数
    public function scopeNum($query, $num)
    {
        if ($num == 1) {
            return $query->where('add', '>', 0);
        } elseif ($num == -1) {
            return $query->where('add', '<', 0);
        }
    }
}
