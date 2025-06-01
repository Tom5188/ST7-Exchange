<?php

namespace App\Http\Controllers\Api;


use App\Models\CurrencyMatch;
use App\Models\CurrencyType;
use App\Models\MyQuotation;
use App\MyQuotation as NeedleModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Utils\RPC;
use App\Models\Currency;
use App\Models\TransactionComplete;
use App\Models\Users;
use App\Models\MarketHour;
use App\Models\CurrencyQuotation;
use App\Models\AreaCode;
use App\Models\UsersWallet;

class CurrencyController extends Controller
{

    protected string $code = '99aa7fc8390140fab4b0f244fa93c368';
    //折线图详情
    public function klineMarket(Request $request)
    {
        $ZKtQpVv = $request->input('symbol');
        $IlcOTQQ = $request->input('period');
        $skXnrZv = $request->input('from', null);
        $lhpKpPv = $request->input('to', null);
        $wjttOOQ = [];
        $RLgCtMv = ['1min' => '1min', '5min' => '5min', '15min' => '15min', '30min' => '30min', '60min' => '60min', '1H' => '60min', '1D' => '1day', '1W' => '1week', '1M' => '1mon', '1Y' => '1year', '1day' => '1day', '1week' => '1week', '1mon' => '1mon', '1year' => '1year'];
        if ($skXnrZv == null || $lhpKpPv == null) {
            return ['code' => -1, 'msg' => 'error: from time or to time must be filled in', 'data' => $wjttOOQ];
        }
        if ($skXnrZv > $lhpKpPv) {
            return ['code' => -1, 'msg' => 'error: from time should not exceed the to time.', 'data' => $wjttOOQ];
        }
        $nKJlPuQ = array_keys($RLgCtMv);
        if ($IlcOTQQ == '' || !in_array($IlcOTQQ, $nKJlPuQ)) {
            return ['code' => -1, 'msg' => 'error: period invalid', 'data' => $wjttOOQ];
        }
        if ($ZKtQpVv == '' || stripos($ZKtQpVv, '/') === false) {
            return ['code' => -1, 'msg' => 'error: symbol invalid', 'data' => $wjttOOQ];
        }
        $IlcOTQQ = $RLgCtMv[$IlcOTQQ];
        list($djNwDwJ, $QhRIIIv) = explode('/', $ZKtQpVv);
        $bUjuraQ = Currency::where('name', $djNwDwJ)->where('is_display', 1)->first();
        $IJLSyCQ = Currency::where('name', $QhRIIIv)->where('is_display', 1)->where('is_legal', 1)->first();
        

        if (!$bUjuraQ || !$IJLSyCQ) {
            return ['code' => -1, 'msg' => 'error: symbol not exist', 'data' => null];
        }
        $cache = Cache::get($djNwDwJ . '&' . $IlcOTQQ);
        if ($cache) {
            return $this->success('cache', 0, $cache);
        } else {
            if($bUjuraQ->platform == 0){
                $wjttOOQ = MarketHour::getEsearchMarketXNB($djNwDwJ, $QhRIIIv, $IlcOTQQ, $skXnrZv, $lhpKpPv);
                $wjttOOQ = array_map(function ($value) {
                    $value['time'] = $value['id'] * 1000;
                    $value['volume'] = $value['amount'] ?? 0;
                    return $value;
                }, $wjttOOQ);
            }else{
                $wjttOOQ = MarketHour::getEsearchMarket($djNwDwJ, $QhRIIIv, $IlcOTQQ, $skXnrZv, $lhpKpPv);
                $wjttOOQ = array_map(function ($value) {
                    $value['time'] = $value['id'] * 1000;
                    $value['volume'] = $value['amount'] ?? 0;
                    return $value;
                }, $wjttOOQ);
            }
            Cache::put($djNwDwJ . '&' . $IlcOTQQ, $wjttOOQ, Carbon::now()->addSeconds(30));
        }

        return $this->success('api', 0, $wjttOOQ);
    }

// 币种列表
    public function klineMarketHome(Request $request)
    {
        $ZKtQpVv = $request->input('symbol');
        $IlcOTQQ = $request->input('period');
        $skXnrZv = $request->input('from', null);
        $lhpKpPv = $request->input('to', null);
        $ZKtQpVv = strtoupper($ZKtQpVv);
        $wjttOOQ = [];
        $RLgCtMv = ['1min' => '1min', '5min' => '5min', '15min' => '15min', '30min' => '30min', '60min' => '60min', '1H' => '60min', '1D' => '1day', '1W' => '1week', '1M' => '1mon', '1Y' => '1year', '1day' => '1day', '1week' => '1week', '1mon' => '1mon', '1year' => '1year'];
        if ($skXnrZv == null || $lhpKpPv == null) {
            return ['code' => -1, 'msg' => 'error: from time or to time must be filled in', 'data' => $wjttOOQ];
        }
        if ($skXnrZv > $lhpKpPv) {
            return ['code' => -1, 'msg' => 'error: from time should not exceed the to time.', 'data' => $wjttOOQ];
        }
        $nKJlPuQ = array_keys($RLgCtMv);
        if ($IlcOTQQ == '' || !in_array($IlcOTQQ, $nKJlPuQ)) {
            return ['code' => -1, 'msg' => 'error: period invalid', 'data' => $wjttOOQ];
        }
        if ($ZKtQpVv == '' || stripos($ZKtQpVv, '/') === false) {
            return ['code' => -1, 'msg' => 'error: symbol invalid', 'data' => $wjttOOQ];
        }
        $IlcOTQQ = $RLgCtMv[$IlcOTQQ];
        list($djNwDwJ, $QhRIIIv) = explode('/', $ZKtQpVv);
        $bUjuraQ = Currency::where('name', $djNwDwJ)->where('is_display', 1)->first();
        $IJLSyCQ = Currency::where('name', $QhRIIIv)->where('is_display', 1)->where('is_legal', 1)->first();

        if (!$bUjuraQ || !$IJLSyCQ) {
            return ['code' => -1, 'msg' => 'error: symbol not exist', 'data' => null];
        }

        $wjttOOQ = MarketHour::getEsearchMarket($djNwDwJ, $QhRIIIv, '1min', $skXnrZv, $lhpKpPv);
        
              
        foreach ($wjttOOQ as &$value){
            $value->data = array();
            $value->data[0] = $value->open;
            $value->data[1] = $value->close;
            $value->data[2] = $value->high;
            $value->data[3] = $value->low;
            if($value->open > $value->close){
                $value->state = 1;
            }else{
                $value->state = 0;
            }
        }

        $array = $wjttOOQ->toArray();  
          
        // 现在你可以使用array_slice()了  
        $dataToShow = array_slice($array, 0, 15);

        return $this->success('success', 0, $dataToShow);
    }
    
    public function newQuotation(Request $request)
    {
        $type = CurrencyType::with([])->get();
        $title = $request->input('title', '');

        foreach ($type as $key => $value) {
            $quotation = CurrencyMatch::with(['currency'])
                ->leftJoin('currency', 'currency_matches.currency_id', '=', 'currency.id')
                ->where('currency.is_display', 1)
                ->where('currency.name', 'like', '%' . $title . '%')
                ->where('currency.currency_type', $value['id'])
                ->orderBy('currency.sort', 'desc')
                ->select(['currency_matches.*'])->get();
            $type[$key]['quotation'] = $quotation;
        }
        
        return $this->success('币种列表', 0, $type);
    }

    //点差调节器币种列表
    public function currencyList(Request $request)
    {
        $currency = Currency::with([])->select(['id', 'name'])->get();
        return $this->success('币种列表', 0, $currency);
    }

//货币转换执行列表
    public function userCurrencyList(Request $request)
    {
        $user_id = $request->user()->id;
        $XOylMHJ = Currency::where('is_display', 1)->orderBy('sort', 'desc')->get();
        $XOylMHJ = $XOylMHJ->filter(function ($item, $key) {
            $fRtZmfQ = array_sum([$item->is_legal, $item->is_lever, $item->is_match, $item->is_micro]);
            return $fRtZmfQ > 1;
        })->values();
        $XOylMHJ->transform(function ($item, $key) use ($user_id) {
            $VxDXWbJ = UsersWallet::where('user_id', $user_id)->where('currency', $item->id)->first();
            $item->setVisible(['id', 'name', 'is_legal', 'is_lever', 'is_match', 'is_micro', 'wallet']);
            return $item->setAttribute('wallet', $VxDXWbJ);
        });
        return $this->success('列表', 0, $XOylMHJ);
    }

    //生成风控折线图
    public function generateBrokenLine(Request $request): JsonResponse
    {

        $rand_start = $request->input('start');//起始价格
        $fudu = $request->input('fudu');//涨跌幅度
        $jingdu = $request->input('jingdu');//增长精度
        $rate1 = $request->input('rate1');//上涨比例
        $rate2 = $request->input('rate2');//下跌比例
        $speed = floatval($request->input('speed'));//上涨幅度
        $dates = $request->input('dates');//插入时间
        $duration = $request->input('duration',60);//插入时长
        $numbers = $this->getNumberArray($rand_start, $speed,$jingdu,$rate1,$rate2, $duration);

        $obj = [];
        $start = $rand_start;

        foreach ($numbers as $number) {

            $arr = ['open' => count($obj) === 0 ? $start : $obj[count($obj) - 1]['close']];

            $arr['close'] = $number;
            if (count($obj) === 0) {
                $arr['close'] = $number + ((rand(-100, 100)) * $fudu);
            }
            $val = max(array_values($arr));
            $minVal = min(array_values($arr));

            $arr['high'] = $val + ((rand(5, 100)) * $fudu);
            $arr['low'] = $minVal - ((rand(5, 100)) * $fudu);

            array_walk($arr, function (&$val) {
                $val = sprintf('%.6f', $val);
            });
            $obj[] = $arr;

        }
        $rsp = [];
        $i = 0;
        foreach ($obj as $v) {
            $rsp[] = [date('Y-m-d H:i:s', strtotime($dates) + $i * 60), $v['open'], $v['high'], $v['low'], $v['close'], rand(0, 100)];
            $i++;
        }
        return $this->success('', 0, ['data' => $rsp, 'next' => date('Y-m-d H:i:s', strtotime($dates)+($duration*60))]);
    }

    function getNumberArray($start, $speed, $jingdu, $rate1, $rate2, $numbers)
    {

        $now = [];
        $end = $start + ($start * $speed / 24);
        for ($i = 0; $i < $numbers; $i++) {

            if ($i === 0) {
                $now[] = $start;
            } elseif ($i === $numbers - 1) {
                $now[] = $end;
            } else {
                $rand = rand(1, 10) * $jingdu;
                if ($this->get_rand([$rate2, $rate1]) === 1) {
                    $now[] = $now[$i - 1] + $rand;
                } else {
                    $now[] = $now[$i - 1] - $rand;
                }
            }
        }
        return $now;
    }

    function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
    //添加行情
    public function saveQuotation(Request $request)
    {
        $con = $request->post('kline');
        $date = $request->post('date');
        $currency = strtoupper($request->post('currency'));
        $obj = json_decode($con, true);
        $timestamp = strtotime($date);
        for ($i = 0; $i < count($obj); $i++) {
            $timeed = strtotime('+ ' . ($i * 60) . ' seconds', $timestamp);
            $needle = MyQuotation::where('itime', $timeed)->where('symbol', $currency.'/USDT')->first();
            if($needle){
                continue;
            }
            $needle = new MyQuotation();
            $needle->open = $obj[$i][1];
            $needle->high = $obj[$i][2];
            $needle->low = $obj[$i][3];
            $needle->close = $obj[$i][4];
            $needle->vol = $obj[$i][5];
            $needle->base = $currency;
            $needle->target = 'USDT';
            $needle->symbol = "{$needle->base}/{$needle->target}";
            $needle->itime = date("Y-m-d H:i:s",$timeed);
            $timefor = date('i', $timeed);
            $period = '1min';
            if($timefor%5==0){
                $period .= ',5min';
                $needle->period5=1;
            }
            if($timefor%15==0){
                $period .= ',15min';
                $needle->period15=1;
            }
            if($timefor%30==0){
                $period .= ',30min';
                $needle->period30=1;
            }
            if($timefor%60==0){
                $period .= ',60min';
                $needle->period60=1;
            }
            $needle->period = $period;
            $needle->save();
        }
        return $this->success('success');
    }
    //行情列表
    public function myQuotationList(Request $request): JsonResponse
    {
        $base=$request->get("base",'BTC');
        $news = MyQuotation::where("base",$base)->orderBy('id', 'desc')->paginate(20);
        return $this->success('列表', 0, $news);
    }
    //删除行情
    public function deleteMyQuotation(Request $request): JsonResponse
    {
        $ids = $request->get("ids");
        $ids=explode(',',$ids);
        MyQuotation::whereIn("id", $ids)->delete();
        return $this->success('success');
    }
    //行情修复
    public function repair(Request $request){
        $symbol = strtoupper($request->input('symbol'));
        $start=urlencode($request->input('start'));
        $end=urlencode($request->input('end'));
        if((strtotime($request->input('end'))-strtotime($request->input('start'))>28800)){
            return $this->error('修复时间不能大于8小时');
        }
        $times=[5=>'1M',6=>'5M',1=>'15M',7=>'30M',2=>'1H'];
        $timeType=[5=>'1min',6=>'5min',1=>'15min',7=>'30min',2=>'60min'];
        foreach ($times as $i=>$item){
            $query = "datest=" . $start ."&dateed=".$end . "&period=".$item."&symbol=" . $symbol . "&withlast=0";
            $data = $this->curl("/query/comkm2v2", $query);
            $history = explode(';', json_decode($data, true)['Obj']);
            if(count($history)>1){
                foreach ($history as $value) {
                    $info = explode(',', $value);
                    $time = $this->formatTimeline($i, $info[0]);//
                    $data = [
                        'id' => $time,
                        'period' => $timeType[$i],//
                        'base-currency' => $symbol,
                        'quote-currency' => 'USDT',
                        'open' => $info[2],
                        'close' => $info[1],
                        'high' => $info[3],
                        'low' => $info[4],
                        'vol' => $info[5],
                        'amount' => $info[6],
                    ];
                    MarketHour::setEsearchMarket($data);
                }
            }
        }

        return $this->success('success');
    }
    public function curl($path, $query)
    {
        $host = "http://alirmcom2.market.alicloudapi.com";
        $method = "GET";
        $appcode = $this->code;
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $url = $host . $path . "?" . $query;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        return curl_exec($curl);
    }
    public function formatTimeline($type, $day_time = null)
    {
        empty($day_time) && $day_time = time();
        switch ($type) {
            //15分钟
            case 1:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 15);
                $minute = $multiple * 15;
                $time = $start_time + $minute * 60;
                break;
            //1小时
            case 2:
                $time = strtotime(date('Y-m-d H:00:00', $day_time));
                break;
            //4小时
            case 3:
                $start_time = strtotime(date('Y-m-d', $day_time));
                $hours = intval(date('H', $day_time));
                $multiple = floor($hours / 4);
                $hours = $multiple * 4;
                $time = $start_time + $hours * 3600;
                break;
            //一天
            case 4:
                $time = strtotime(date('Y-m-d', $day_time));
                break;
            //分时
            case 5:
                $time_string = date('Y-m-d H:i', $day_time);
                $time = strtotime($time_string);
                break;
            //5分钟
            case 6:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 5);
                $minute = $multiple * 5;
                $time = $start_time + $minute * 60;
                break;
            //30分钟
            case 7:
                $start_time = strtotime(date('Y-m-d H:00:00', $day_time));
                $minute = intval(date('i', $day_time));
                $multiple = floor($minute / 30);
                $minute = $multiple * 30;
                $time = $start_time + $minute * 60;
                break;
            //一周
            case 8:
                $start_time = strtotime(date('Y-m-d', $day_time));
                $week = intval(date('w', $day_time));
                $diff_day = $week;
                $time = $start_time - $diff_day * 86400;
                break;
            //一月
            case 9:
                $time_string = date('Y-m', $day_time);
                $time = strtotime($time_string);
                break;
            //一年
            case 10:
                $time = strtotime(date('Y-01-01', $day_time));
                break;
            default:
                $time = $day_time;
                break;
        }
        return $time;
    }
}
