<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CurrencyMatch extends Model
{
	public $timestamps = false;
	protected $appends = ["legal_name", "currency_name","show_name", "market_from_name", "change", "volume", "now_price", "rmb_relation", "logo", "category_text", "content", "issue_num", "sell_status","multiple","each_piece","logo_url","micro_trade_fee"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
    public function getMicroTradeFeeAttribute()
    {
        return $this->currency()->value("micro_trade_fee");
    }
    public function getEachPieceAttribute()
    {
        return $this->currency()->value("each_piece");
    }
	public function getSellStatusAttribute()
	{
		return $this->currency()->value("sell_status");
	}
	public function getIssueNumAttribute()
	{
		return $this->currency()->value("issue_num");
	}
	public function getContentAttribute()
	{
		return $this->currency()->value("content");
	}
	public function getRmbRelationAttribute()
	{
		return $this->currency()->value("rmb_relation");
	}
	protected static $marketFromNames = ["无", "交易所", "火币接口", "机器人"];
	protected function getLogoAttribute()
	{
		return $this->currency()->value("logo");
	}

    protected function getLogoUrlAttribute(): string
    {
        return config('app.url') . "/storage/" .$this->currency()->value("logo");
    }
	protected function getCategoryTextAttribute()
	{
		switch ($this->attributes["category"]) {
			case 1:
				return "主流区";
				break;
			case 2:
				return "创新区";
				break;
			default:
				return "未知区";
		}
	}
    public function getMultipleAttribute()
    {
        return LeverMultiple::with([])->where('currency_id',$this->attributes["currency_id"])->where("type",1)->get();
    }
	public function legal()
	{
		return $this->belongsTo(Currency::class, "legal_id", "id")->withDefault();
	}
	public function currency()
	{
		return $this->belongsTo(Currency::class, "currency_id", "id")->withDefault();
	}
	public static function enumMarketFromNames()
	{
		return self::$marketFromNames;
	}
	public function getSymbolAttribute()
	{
		return $this->getCurrencyNameAttribute() . "/" . $this->getLegalNameAttribute();
	}
	public function getMatchNameAttribute()
	{
		return strtolower($this->getCurrencyNameAttribute() . $this->getLegalNameAttribute());
	}
	public function getLegalNameAttribute()
	{
		return $this->legal()->value("name");
	}
	public function getCurrencyNameAttribute()
	{
		return $this->currency()->value("name");
	}
    public function getShowNameAttribute()
    {
        return $this->currency()->value("alias");
    }
//    public function getOpeningAttribute()
//    {
//        return $this->currency()->value("opening");
//    }
	public function getMarketFromNameAttribute($value)
	{
		return self::$marketFromNames[$this->attributes["market_from"]??1];
	}
	public function getDaymarketAttribute()
	{
		$JZCwucJ = $this->attributes["legal_id"];
		$PslPuOv = $this->attributes["currency_id"];
		CurrencyQuotation::unguard();
		$NiSCQSJ = CurrencyQuotation::firstOrCreate(["legal_id" => $JZCwucJ, "currency_id" => $PslPuOv], ["match_id" => $this->attributes["id"], "change" => "", "volume" => 0, "now_price" => 0, "add_time" => time()]);
		CurrencyQuotation::reguard();
		return $NiSCQSJ;
	}
	public function getChangeAttribute()
	{
		return $this->getDaymarketAttribute()->change;
	}
	public function getVolumeAttribute()
	{
		return $this->getDaymarketAttribute()->volume;
	}
	public function getNowPriceAttribute()
	{
		return $this->getDaymarketAttribute()->now_price;
	}
	public function quotation()
	{
		return $this->hasOne(CurrencyQuotation::class, "legal_id", "legal_id");
	}
	public static function getHuobiMatchs()
	{
		$qBXDpPQ = self::with(["legal", "currency"])->where("market_from", 2)->get();
		$huobi_symbols = HuobiSymbol::pluck("symbol")->all();
		$qBXDpPQ->transform(function ($item, $key) {
			$item->setHidden(["currency"]);
			$item->setHidden(["legal"]);
			$item->append("match_name");
			return $item;
		});
		$qBXDpPQ = $qBXDpPQ->filter(function ($value, $key) use($huobi_symbols) {
			return in_array($value->match_name, $huobi_symbols);
		});
		return $qBXDpPQ;
	}
	public function getRiskGroupResultNameAttribute()
	{
		$wrVJFiQ = [-1 => "亏损", 0 => "无", 1 => "盈利"];
		$pklTcwv = $this->attributes["risk_group_result"] ?? 0;
		return $wrVJFiQ[$pklTcwv];
	}
}
