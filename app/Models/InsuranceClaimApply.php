<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceClaimApply extends Model
{
    //
    protected $table = 'insurance_claim_applies';
    protected $guarded = [];
    protected $appends = [
        'status_name',
        'mobile',
        'insurance_type_name',
        'amount',
        'user_name'
    ];

    public function getUserNameAttribute()
    {
        $user = $this->user()->first();
        if($user){
            return $user->userreal_name;
        }else{
            return '--';
        }
    }

    public function getMobileAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }

    public function getInsuranceTypeNameAttribute()
    {
        return $this->hasOne(InsuranceType::class, 'id', 'insurance_type')->value('name');
    }

    public function getAmountAttribute()
    {
        return $this->hasOne(UsersInsurance::class, 'id', 'user_insurance_id')->value('amount');
    }

    protected function getStatusNameAttribute()
    {
        $value=$this->attributes['apply_status'];
        if ($value==0){
            $str='理赔中';
        }elseif ($value==1) {
            $str='已赔付';
        }elseif ($value==2){
            $str='已拒绝';
        }else{
            $str="";
        }
        return $str;
    }

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }

}
