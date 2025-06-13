<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\CurrencyMatch;
use App\Models\MarketHour;
use App\Models\MicroNumber;
use App\Models\Setting;
use App\Models\UsersWallet;

class Currency extends BaseModel
{
    protected $table = "currency";
    public $timestamps = false;
    protected $appends = ['opening'];//"to_pb_price"
    protected $hidden = ["key"];

    private $second_pwd = '000000';
    
    protected static function booted()
    {
        static::deleting(function ($currency) {
            // 删除所有 users_wallet 中该 currency 的记录
            $currency->wallets()->delete();
        });
    }

    public function wallets()
    {
        return $this->hasMany(UsersWallet::class, 'currency');
    }

    public function digitalCurrencyAddress()
    {
        return $this->hasMany(DigitalCurrencyAddress::class);
    }

    public function CurrencyOpening()
    {
        return $this->hasMany(CurrencyOpening::class);
    }
    public function CurrencyType()
    {
        return $this->belongsTo(CurrencyType::class, "currency_type");
    }
    public function quotation()
    {
        return $this->hasMany(CurrencyMatch::class, "legal_id", "id");
    }

    public function userswalletout()
    {
        return $this->hasMany(UsersWalletOut::class);
    }

    public function leverTransaction()
    {
        return $this->hasMany(LeverTransaction::class);
    }

    public function leverMultiple()
    {
        return $this->hasMany(LeverMultiple::class, "currency_id", "id");
    }

    public function match()
    {
        return $this->hasMany(CurrencyMatch::class);
    }
    public function microNumbers()
    {
        return $this->hasMany(MicroNumber::class)->orderBy("number", "asc");
    }
    public function getCreateTimeAttribute()
    {
        return date("Y-m-d H:i:s", $this->attributes["create_time"]);
    }
    public static function getNameById($currency_id)
    {
        $zeuJRlv = self::find($currency_id);
        return $zeuJRlv->name;
    }
    public static function getCnyPrice($currency_id)
    {
        $NwpvAUQ = Setting::getValueByKey("USDTRate", 7.08);
        $vIkVmCQ = Currency::where("name", "USDT")->select(["id"])->first();
        $aaubRQv = MarketHour::orderBy("id", "desc")->where("currency_id", $currency_id)->where("legal_id", $vIkVmCQ->id)->first();
        if (!empty($aaubRQv)) {
            $PieWyUJ = $aaubRQv->highest * $NwpvAUQ;
        } else {
            $PKZDitQ = Currency::where("id", $currency_id)->first();
            $PieWyUJ = $PKZDitQ->price * $NwpvAUQ;
        }
        if ($currency_id == $vIkVmCQ->id) {
            $PieWyUJ = 1 * $NwpvAUQ;
        }
        return $PieWyUJ;
    }
    public function getRmbRelationAttribute()
    {
        $hlOeRCQ = Setting::getValueByKey("USDTRate", 7.08);
        return $hlOeRCQ;
    }
    public function getOriginKeyAttribute($value)
    {
        $sZVuipJ = $this->attributes["key"] ?? "";
        return $sZVuipJ != "" ? decrypt($sZVuipJ) : "";
    }
    public function getKeyAttribute($value)
    {
        return $value == "" ?: "********";
    }
    public function getOpeningAttribute(): int
    {
        $opening=CurrencyOpening::with([])->where('currency_id',$this->id)->first();
        if($opening){
            $time=date('H:i:s',time());
            switch (date('w',time())){
                case 1:
                    if($time>$opening->mon_begin&&$time<$opening->mon_end){
                        return 1;
                    }else{
                        return 0;
                    }
                case 2:
                    if($time>$opening->tue_begin&&$time<$opening->tue_end){
                        return 1;
                    }else{
                        return 0;
                    }
                case 3:
                    if($time>$opening->wed_begin&&$time<$opening->wed_end){
                        return 1;
                    }else{
                        return 0;
                    }
                case 4:
                    if($time>$opening->thu_begin&&$time<$opening->thu_end){
                        return 1;
                    }else{
                        return 0;
                    }
                case 5:
                    if($time>$opening->fin_begin&&$time<$opening->fin_end){
                        return 1;
                    }else{
                        return 0;
                    }
                case 6:
                    if($time>$opening->sat_begin&&$time<$opening->sat_end){
                        return 1;
                    }else{
                        return 0;
                    }
                case 7:
                    if($time>$opening->sun_begin&&$time<$opening->sun_end){
                        return 1;
                    }else{
                        return 0;
                    }
                default:
                    return 1;
            }
        }else{
            return 1;
        }
    }
    public function setKeyAttribute($value)
    {
        if ($value != "") {
            return $this->attributes["key"] = encrypt($value);
        }
    }
}
