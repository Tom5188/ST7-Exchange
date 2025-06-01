<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProjectOrder extends BaseModel
{
    protected $table = "project_order";
    use HasFactory;
     protected $appends = [
        'account',
        'status_name',
        'end_time'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'interest_gen_next_time' => 'datetime',
        'sub_time' => 'datetime',
    ];
    
    public function getAccountAttribute()
    {
        return $this->belongsTo(Users::class,  'user_id', 'id')->value('email');
    }
    
    public function getProjectNameAttribute()
    {
        return $this->belongsTo(Project::class,  'project_id', 'id')->value('project_name');
    }
    
    public function getEndTimeAttribute()
    {
        $startTime = $this->attributes['sub_time'];
        $lockDays = $this->attributes['lock_dividend_days'];
        // 计算加上锁仓期后的时间
        $endTime = Carbon::parse($startTime)->addDays($lockDays);
        return $endTime;
    }
    
    public function getStatusNameAttribute(): string
    {
        $status='';
        if($this->attributes['status']==1){
            $status='交易中';
        }else if($this->attributes['status']==2){
            $status='申请退款';
        }else if($this->attributes['status']==3){
            $status='交易完成';
        }else if($this->attributes['status']==4){
            $status='提前赎回';
        }
        return $status;
    }
    
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
}
