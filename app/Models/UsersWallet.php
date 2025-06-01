<?php

namespace App\Models;

use App\Models\Currency;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersWallet extends Model
{

    protected $table = "users_wallet";
    public $timestamps = false;
    protected $hidden = ["private"];
    protected $appends = ["account_number","currency_name", "currency_type", "is_legal", "is_lever", "is_match", "is_micro", "cny_price", "usdt_price","balance","lock_balance"];//, "pb_price"


    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency')->value('type');
    }


    public function getCurrencyNameAttribute()
    {
        return $this->currencyCoin()->value('name');
    }

    public function getIsLegalAttribute()
    {
        return $this->currencyCoin()->value('is_legal');
    }

    public function getIsLeverAttribute()
    {
        return $this->currencyCoin()->value('is_lever');
    }

    public function getIsMatchAttribute()
    {
        return $this->currencyCoin()->value('is_match');
    }

    public function getIsMicroAttribute()
    {
        return $this->currencyCoin()->value('is_micro');
    }

    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }



    public static function makeWallet($user_id)
    {
        $currency = Currency::all();

        foreach ($currency as $key => $value) {
            $res = self::where([
                    'currency' => $value->id,
                    'user_id' => $user_id
                ])->first();
            if(!$res){
                self::insert([
                    'currency' => $value->id,
                    'user_id' => $user_id,
                    'address' => null,
                    'create_time' => time()
                 ]);
            }

        }
        return true;
    }

    public static function makeWalletSimulation($user_id)
    {
        $currency = Currency::all();

        foreach ($currency as $key => $value) {
            $res = self::where([
                    'currency' => $value->id,
                    'user_id' => $user_id
                ])->first();
            if(!$res){
                self::insert([
                    'currency' => $value->id,
                    'user_id' => $user_id,
                    'address' => null,
                    'change_balance' => 1000000,
                    'create_time' => time()
                ]);
            }

        }
        return true;
    }

    public static function getAddress(UsersWallet $wallet){
        $saveFlag = false;

        if($wallet->currency == 3){

            $return = ['omni'=>$wallet->address_2,'erc20' => $wallet->address];
        }else{


            $return = $wallet->address;
        }

        return $return;
    }


    public static function getUsdtWallet($userId){
       return  self::where("user_id", $userId)
            ->where("currency", 1)
            ->first();
    }

    public static function getDF1Wallet($userId){
        $res =   self::where("user_id", $userId)
            ->where("currency", 29)
            ->first();
        return $res;
    }


    public function getUsdtPriceAttribute()
    {
        return $this->currencyCoin()->value('price') ?? 1;
    }
    public function getBalanceAttribute()
    {
        $value = $this->user()->value('id');
        if(empty($value)){
            return '';
        }
        $wal = UsersWallet::where('currency', $this->attributes['currency'])->where('user_id', $value)->first();

        return  $wal->lever_balance+$wal->change_balance+$wal->micro_balance;
    }

    public function getLockBalanceAttribute()
    {
        $value = $this->user()->value('id');
        if (empty($value)) {
            return '';
        }
        $wal = UsersWallet::where('currency', $this->attributes['currency'])->where('user_id', $value)->first();

        return $wal->lock_lever_balance + $wal->lock_change_balance + $wal->lock_micro_balance;
    }
    public function getCnyPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getCnyPrice($currency_id);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getPrivateAttribute($value)
    {

        return empty($value) ? '' : decrypt($value);
    }
    public function setPrivateAttribute($value)
    {
        $this->attributes['private'] = encrypt($value);
    }

    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
}
