<?php

namespace App\Http\Controllers\Api;

use App\Jobs\HandleMicroTrade;
use App\Jobs\LeverUpdate;
use App\Jobs\SendLever;
use App\Models\Currency;
use App\Models\MarketKine;
use App\Models\CurrencyOpening;
use App\Models\CurrencyQuotation;
use App\Jobs\EsearchMarket;
use App\Jobs\SendMarket;
use App\Jobs\UpdateCurrencyPrice;
use App\Logic\MicroTradeLogic;
use App\Models\LeverTransaction;
use App\Models\MarketHour;
use App\Models\UserChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendTelegramRechargeNotification;
defined('ACCOUNT_ID') || define('ACCOUNT_ID', '50154012');
defined('ACCESS_KEY') || define('ACCESS_KEY', 'c96392eb-b7c57373-f646c2ef-25a14');
defined('SECRET_KEY') || define('SECRET_KEY', '');

class AliMarketController
{

    protected $code = '99aa7fc8390140fab4b0f244fa93c368';

    protected $signature = "get_kline_data";
    protected $description = "获取K线图数据";
    private $url = "https://api.huobi.br.com";
    private $api = "";
    public $api_method = "";
    public $req_method = "";

    /**
     * 行情查询
     */
    public function marketInfo($symbol)
    {
        $symbol = strtoupper($symbol);
        $querys = "symbol=" . $symbol . "&withks=1&withticks=0";//USDCNH
        $data = $this->curl("/query/com", $querys);
        return json_decode($data, true)['Obj'];
    }

    /**
     * 交易查询
     * @param $symbol
     * @return mixed
     */
    public function ticks($symbol)
    {
        $symbol = strtoupper($symbol);
        $querys = "symbol=" . $symbol . "&count=50";//USDCNH
        $data = $this->curl("/query/ticks", $querys);
        return json_decode($data, true)['Obj'];
    }
    
    public function kline_history_huobi(){
        $symbol_list = Currency::where("platform", 0)->pluck('name');
        $periods = [60 =>'1min',300=>'5min',900=>'15min',1800=>'30min',3600=>'60min',86400=>'1day', 604808=>'1week', 2592000=>'1mon'];
        foreach ($periods as $key=>$period){
            foreach ($symbol_list as $symbol){
                if($symbol != 'USDT'){
                    $datakline = file_get_contents("https://api.huobi.pro/market/history/kline?symbol=" . strtolower($symbol . 'USDT') . "&period={$period}&size=1000");
                    $kline_list = json_decode($datakline, true)['data'];
                    foreach ($kline_list as $kline){
                        $rqkline = [
                            'id' => $kline['id'],
                            'period' => $period,
                            'base-currency' => $symbol,
                            'quote-currency' => 'USDT',
                            'open' => $kline['open'],
                            'close' => $kline['close'],
                            'high' => $kline['high'],
                            'low' => $kline['low'],
                            'vol' => $kline['vol'],
                            'amount' => $kline['amount'],
                        ];
                        var_dump(json_encode($rqkline));
                        MarketHour::setEsearchMarket($rqkline);
                    }
                }
            }
        }
        return 'ok';
    }

    public function kline_history_ali()
    {
        //1是1分钟K，2是5分钟K，3是15分钟K，4是30分钟K，5是小时K，6是2小时K(股票不支持2小时)，7是4小时K(股票不支持4小时)，8是日K，9是周K，10是月K （注：股票不支持2小时K、4小时K）
        $symbol_list = Currency::where("platform", 1)->pluck('name');
        $periods = [1=>'1min', 2=>'5min', 3=>'15min', 4=>'30min', 5=>'60min', 8=>'1day', 9=>'1week', 10=>'1mon'];
        foreach ($periods as $key=>$period){
            foreach ($symbol_list as $symbol){
                $datakline = $this->curl_k($symbol, $key, 1000);
                $kline_list = json_decode($datakline, true)['data']['kline_list'];
                foreach ($kline_list as $kline){
                    $rqkline = [
                        'id' => $kline['timestamp'],
                        'period' => $period,
                        'base-currency' => $symbol,
                        'quote-currency' => 'USDT',
                        'open' => $kline['open_price'],
                        'close' => $kline['close_price'],
                        'high' => $kline['high_price'],
                        'low' => $kline['low_price'],
                        'vol' => $kline['volume'],
                        'amount' => $kline['close_price'],
                    ];
                    var_dump(json_encode($rqkline));
                    MarketHour::setEsearchMarket($rqkline);
                }
            }
        }
        return 'ok';
    }

    public function historyW(Request $request)
    {
        $symbol = $request->input('symbol');
        $date = $request->input('date');
        $symbol = strtoupper($symbol);
        $query = "date=" . $date . "&period=W&symbol=" . $symbol . "&withlast=0";
        $data = $this->curl("/query/comkm4v2", $query);
        $history = explode(';', json_decode($data, true)['Obj']);
        foreach ($history as $value) {
            $info = explode(',', $value);
            $time = $this->formatTimeline(8, $info[0]);//
            $data = [
                'id' => $time,
                'period' => "1week",//
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
        return 'ok';
    }

    public function historyM(Request $request)
    {
        $symbol = $request->input('symbol');
        $date = $request->input('date');
        $symbol = strtoupper($symbol);
        $query = "date=" . $date . "&period=M&symbol=" . $symbol . "&withlast=0";
        $data = $this->curl("/query/comkm4v2", $query);
        $history = explode(';', json_decode($data, true)['Obj']);
        foreach ($history as $value) {
            $info = explode(',', $value);
            $time = $this->formatTimeline(9, $info[0]);
            $data = [
                'id' => $time,
                'period' => "1mon",
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
        return 'ok';
    }

    public function getAliInfo()
    {
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);
        foreach ($all as $i=>$item) {
            if($this->getOpening($item->id)==0){
                continue;
            }
            $symbol = strtoupper($item->name);
            $querys = "symbol=" . $symbol . "&withks=1&withticks=0";//USDCNH
            $data = $this->curl("/query/com", $querys);
            $data = json_decode($data, true);

            if (!$data) {
                continue;
            }
            if ($data['Code'] != '0') {
                continue;
            }
            $ali_market_data = $data['Obj'];
            $price = explode(',', $ali_market_data['M1']);
            if ($item->floating != 0) {
                $price[1] += $item->floating;
                $ali_market_data['P'] += $item->floating;
                $price[2] += $item->floating;
                $price[3] += $item->floating;
            }
            $timeType = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1day' => 4, '1week' => 8, '1mon' => 9];
            $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week'];
            foreach ($periods as $value) {
                $time = $this->formatTimeline($timeType[$value], $ali_market_data['Tick']);
                $market_data = [
                    'id' => $time,
                    'period' => $value,
                    'base-currency' => $item->name,
                    'quote-currency' =>  'USDT',
                    'open' => sctonum($price[1]),
                    'close' => sctonum($ali_market_data['P']),
                    'high' => sctonum($price[2]),
                    'low' => sctonum($price[3]),
                    'vol' => sctonum($ali_market_data['V']),
                    'amount' => sctonum($ali_market_data['NV']),
                ];

                $kline_data = [
                    'type' => 'kline',
                    'period' => $value,
                    'match_id' => $item->match_id,
                    'currency_id' => $item->id,
                    'currency_name' => $item->name,
                    'legal_id' => 1,
                    'legal_name' =>  'USDT',
                    'open' => sctonum($price[1]),
                    'close' => sctonum($ali_market_data['P']),
                    'high' => sctonum($price[2]),
                    'low' => sctonum($price[3]),
                    'symbol' => $item->name . '/' . 'USDT',
                    'volume' => sctonum($ali_market_data['NV']),
                    'time' => $time,
                ];

                if ($value == '1min') {
                    //处理期权
                    HandleMicroTrade::dispatch($kline_data)->onQueue('micro_trade:handle');
                }
                if ($value == '1day') {
                    //推送币种的日行情(带涨副)
                    $change = $this->calcIncreasePair($kline_data);
                    bc_comp($change, 0) > 0 && $change = '+' . $change;
                    //追加涨副等信息
                    $daymarket_data = [
                        'type' => 'daymarket',
                        'change' => $change,
                        'now_price' => $market_data['close'],
                        'api_form' => 'huobi_websocket',
                    ];
                    $kline_data = array_merge($kline_data, $daymarket_data);
                    //存入数据库
                    CurrencyQuotation::getInstance(1,$item->id)
                        ->updateData([
                            'change' => $daymarket_data['change'],
                            'now_price' => $kline_data['close'],
                            'volume' => $kline_data['volume'],
                        ]);
                    $now = microtime(true);
                    $params = [
                        'legal_id' => $kline_data['legal_id'],
                        'legal_name' => $kline_data['legal_name'],
                        'currency_id' => $kline_data['currency_id'],
                        'currency_name' => $kline_data['currency_name'],
                        'now_price' => $kline_data['close'],
                        'now' => $now
                    ];
                    //价格大于0才进行任务推送
                    if (bc_comp($kline_data['close'], 0) > 0) {
                        LeverUpdate::dispatch($params)->onQueue('lever:update');
                    }
                }
                SendMarket::dispatch($kline_data)->onQueue('kline.all');
                EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列

            }
        }
    }

    public function getOpening($id): int
    {
        $opening=CurrencyOpening::with([])->where('currency_id',$id)->first();
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
    

    public function indexTest()
    {
         $es_client = MarketHour::getEsearchClient();
                 $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);
            
            
        foreach ($all as $v){
                    $params = [
            'index' => 'market.kline',
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [ 'match' => [ 'period' => '60min' ] ],
                            [ 'match' => [ 'base-currency' => $v->name ] ],
                            [ 'match' => [ 'quote-currency' => 'USDT' ] ],
                        ]
                        // 'filter' => [
                        //     'range' => [
                        //         'id' => [
                        //             'gte' => '1722519681',
                        //             'lte' => '1723297281',
                        //         ],
                        //     ],
                        // ],
                    ]
                ],
                'sort' => [
                    'id' => [
                        'order' => 'asc',
                    ],
                ],
                'size' => '10000',
            ]
        ];
        
        
        // $result = $es_client->search($params);
        // if (isset($result['hits'])) {
        //     $data = array_column($result['hits']['hits'], '_source');
        // } else {
        //     $data = [];
        // }
        // var_dump($data); 
        // return;
        
        $es_client->deleteByQuery($params);  
        }

        // foreach ($data as $v){
        //     $basecurrency = 'USDCAD';
        //     $querycurrency = 'USDT';
        //     $v['base-currency'] = $basecurrency;
        //     $v['quote-currency'] = $querycurrency;
        //     $type = $basecurrency . '.' . $querycurrency . '.' . $v['period'];
        //     $params2 = [
        //         'index' => 'market.kline',
        //         'type' => '_doc',
        //         'id' => $type . '.' . $v['id'],
        //         'body' => $v,
        //     ];
            
        //     $es_client->index($params2);
        // }
        return;
        //     echo "开始推送\r\n";
        //     $all = DB::table('currency')->where('is_display', '1')->get();
        //     $all_arr = $this->object2array($all);
        //     $legal = DB::table('currency')->where('is_display', '1')->where('is_legal', '1')->get();
        //     $legal_arr = $this->object2array($legal);
        //     $ar = [];
        //     foreach ($legal_arr as $legal) {
        //         foreach ($all_arr as $item) {
        //             if ($legal['id'] != $item['id']) {
        //                 echo "begin2";
        //                 $ar_a = [];
        //                 $ar_a['name'] = strtolower($item['name']) . strtolower($legal['name']);
        //                 $ar_a['currency_id'] = $item['id'];
        //                 $ar_a['legal_id'] = $legal['id'];
        //                 $ar[] = $ar_a;
        //             }
        //         }
        //     }
        //     echo "开始遍历币种\r\n";
        //     foreach ($ar as $vv) {
        //         if (in_array($vv["name"], array("btcusdt", "ethusdt", "ltcusdt", "bchusdt", "eosusdt"))) {
        //             $ar_new[] = $vv;
        //         }
        //     }
        //     file_put_contents("ar_new.txt", json_encode($ar_new) . PHP_EOL, FILE_APPEND);
        //     foreach ($ar_new as $it) {
        //         echo "遍历币种开始\r\n";
        //         $data = array();
        //         echo "开始请求\r\n";
        //         $data = $this->get_history_kline($it['name'], '1min', 1);
        //         dd($data);
        //   }

    }
    public function initTest1(){
        $period = '1min';
        
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);
                // 初始化一个空数组来存储所有的name值  
        $names = [];  
          
        // 遍历$people数组  
        foreach ($all as $person) {  
            // 将每个person的name值添加到$names数组中  
            $names[] = $person->name;  
        }  
         
        // 使用implode()函数将$names数组中的所有元素用逗号连接起来  
        $namesString = implode(',', $names); 
        $datak = $this->testCurl("/exchange_pluralK.action", $namesString,'1','202408091300');   

        $datak = explode(PHP_EOL, $datak);

 $rq = $datak[1];
        unset($datak[0]);
        unset($datak[1]);
        unset($datak[2]);
        $datak_arr = array();
        foreach ($datak as $v){
            array_push($datak_arr,explode(",", $v));
        }

        foreach ($all as $i=>$item) {
            foreach ($datak_arr as $data){
                  // 假设的时间字符串  
                // 原始时间字符串  
                if($item->name != $data[0]){
                    continue;
                }
                
                $dateTimeStr = $rq; 

                  
                // 将时间字符串分割成年、月、日、时、分  
                $year = substr($dateTimeStr, 0, 4);  
                $month = substr($dateTimeStr, 4, 2);  
                $day = substr($dateTimeStr, 6, 2);  
                  
                  if($period == '1day'){
                      // 构造一个PHP能理解的日期时间字符串  
                $dateTimeStrFormatted = "$year-$month-$day";  
                  }else{
                      // 构造一个PHP能理解的日期时间字符串  
                $dateTimeStrFormatted = "$year-$month-$day ".$data[1];  
                  }
                
                // 使用strtotime函数将字符串转换为时间戳  
                $time = strtotime($dateTimeStrFormatted); 
                
                $market_data = [
                        'id' => $time,
                        'period' => $period,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($data[2]),
                        'close' => sctonum($data[5]),
                        'high' => sctonum($data[3]),
                        'low' => sctonum($data[4]),
                        'vol' => sctonum(0),
                        'amount' => sctonum($data[2]),
                    ];
                   
                    $kline_data = [
                        'type' => 'kline',
                        'period' => $period,
                        'match_id' => $item->match_id,
                        'currency_id' => $item->id,
                        'currency_name' => $item->name,
                        'legal_id' => 1,
                        'legal_name' =>  'USDT',
                        'open' => sctonum($data[2]),
                        'close' => sctonum($data[5]),
                        'high' => sctonum($data[3]),
                        'low' => sctonum($data[4]),
                        'symbol' => $item->name . '/' . 'USDT',
                        'volume' => 0,
                        'time' => $time * 1000,
                    ];
                    SendMarket::dispatch($kline_data)->onQueue('kline.all');
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
            }
                  
        }

        
        
        
    }
    public function initTest(){
        $period = '30min';
        
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);
                // 初始化一个空数组来存储所有的name值  
        $names = [];  
          
        // 遍历$people数组  
        foreach ($all as $person) {  
            // 将每个person的name值添加到$names数组中  
            $names[] = $person->name;  
        }  
         
        // 使用implode()函数将$names数组中的所有元素用逗号连接起来  
        $namesString = implode(',', $names); 
        $datak = $this->testCurl("/exchange_pluralK.action", $namesString,'30');    
        $datak = explode(PHP_EOL, $datak);

//  $rq = $datak[1];
        unset($datak[0]);
        // unset($datak[1]);
        // unset($datak[2]);
        $datak_arr = array();
        foreach ($datak as $v){
            array_push($datak_arr,explode(",", $v));
        }

        foreach ($all as $i=>$item) {
            foreach ($datak_arr as $data){
                  // 假设的时间字符串  
                // 原始时间字符串  
                if($item->name != $data[0]){
                    continue;
                }
                $rq = explode(" ", $data[1]);
                $dateTimeStr = $rq[0]; 

                  
                // 将时间字符串分割成年、月、日、时、分  
                $year = substr($dateTimeStr, 0, 4);  
                $month = substr($dateTimeStr, 4, 2);  
                $day = substr($dateTimeStr, 6, 2);  
                  
                  if($period == '1day'){
                      // 构造一个PHP能理解的日期时间字符串  
                $dateTimeStrFormatted = "$year-$month-$day";  
                  }else{
                      // 构造一个PHP能理解的日期时间字符串  
                $dateTimeStrFormatted = "$year-$month-$day ".$rq[1];  
                  }
                
                 
                // 使用strtotime函数将字符串转换为时间戳  
                $time = strtotime($dateTimeStrFormatted); 

                $market_data = [
                        'id' => $time,
                        'period' => $period,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($data[2]),
                        'close' => sctonum($data[5]),
                        'high' => sctonum($data[3]),
                        'low' => sctonum($data[4]),
                        'vol' => sctonum(0),
                        'amount' => sctonum($data[2]),
                    ];
                   
                    $kline_data = [
                        'type' => 'kline',
                        'period' => $period,
                        'match_id' => $item->match_id,
                        'currency_id' => $item->id,
                        'currency_name' => $item->name,
                        'legal_id' => 1,
                        'legal_name' =>  'USDT',
                        'open' => sctonum($data[2]),
                        'close' => sctonum($data[5]),
                        'high' => sctonum($data[3]),
                        'low' => sctonum($data[4]),
                        'symbol' => $item->name . '/' . 'USDT',
                        'volume' => 0,
                        'time' => $time * 1000,
                    ];
    
                    
                    SendMarket::dispatch($kline_data)->onQueue('kline.all');
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
            }
                  
        }

        
        
        
    }
    public function getTestday()
    {
       $utcTime = gmdate('Y-m-d H:i:s');  
// 如果你想检查这个UTC时间是否是周末，你可以这样做：  
$utcTimestamp = strtotime($utcTime); // 通常这一步是多余的，因为gmdate()已经返回了UTC时间  
$utcDayOfWeek = date('N', $utcTimestamp); // 但这里我们使用date()和UTC时间戳来检查星期几 
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);

             
        foreach ($all as $i=>$item) {
            try {
                $datak = $this->curl_k($item->name,8,1);
            } catch (\Throwable $e) {

                
            }
            
            
            // var_dump($datak);
            // return;
            $data_json = json_decode($datak);
            $data2 = $data_json->data->kline_list;
            foreach ($data2 as $data){
                $time = $data->timestamp;
                $open_price = $data->open_price;
                $close_price = $data->close_price;
                $high_price = $data->high_price;
                $low_price = $data->low_price;
                $volume = $data->volume;
                $symbol = strtoupper($item->name);
    
    
                if (!$data) {
                    
                    continue;
                }
                
    
                $timeType = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1day' => 4, '1week' => 8, '1mon' => 9];
                $periods = ['1day'];
    
    
                foreach ($periods as $value) {
                    
                    // $time = $this->formatTimeline($timeType[$value], $time);
                    // var_dump($res);
                     
                    $market_data = [
                        'id' => $time,
                        'period' => $value,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'vol' => sctonum($volume),
                        'amount' => sctonum($close_price),
                    ];
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
                    
                }
            }
            
            
            // return;
             
        }
    }
    public function getTest60()
    {
       $utcTime = gmdate('Y-m-d H:i:s');  
// 如果你想检查这个UTC时间是否是周末，你可以这样做：  
$utcTimestamp = strtotime($utcTime); // 通常这一步是多余的，因为gmdate()已经返回了UTC时间  
$utcDayOfWeek = date('N', $utcTimestamp); // 但这里我们使用date()和UTC时间戳来检查星期几 
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);

             
        foreach ($all as $i=>$item) {
            try {
                $datak = $this->curl_k($item->name,5,1);
            } catch (\Throwable $e) {

                
            }
            
            
            // var_dump($datak);
            // return;
            $data_json = json_decode($datak);
            $data2 = $data_json->data->kline_list;
            foreach ($data2 as $data){
                $time = $data->timestamp;
                $open_price = $data->open_price;
                $close_price = $data->close_price;
                $high_price = $data->high_price;
                $low_price = $data->low_price;
                $volume = $data->volume;
                $symbol = strtoupper($item->name);
    
    
                if (!$data) {
                    
                    continue;
                }
                
    
                $timeType = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1day' => 4, '1week' => 8, '1mon' => 9];
                $periods = ['60min'];
    
    
                foreach ($periods as $value) {
                    
                    // $time = $this->formatTimeline($timeType[$value], $time);
                    // var_dump($res);
                     
                    $market_data = [
                        'id' => $time,
                        'period' => $value,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'vol' => sctonum($volume),
                        'amount' => sctonum($close_price),
                    ];
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
                    
                }
            }
            
            
            // return;
             
        }
    }
    public function getTest30()
    {
       $utcTime = gmdate('Y-m-d H:i:s');  
// 如果你想检查这个UTC时间是否是周末，你可以这样做：  
$utcTimestamp = strtotime($utcTime); // 通常这一步是多余的，因为gmdate()已经返回了UTC时间  
$utcDayOfWeek = date('N', $utcTimestamp); // 但这里我们使用date()和UTC时间戳来检查星期几 
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);

             
        foreach ($all as $i=>$item) {
            try {
                $datak = $this->curl_k($item->name,4,1);
            } catch (\Throwable $e) {

                
            }
            
            
            // var_dump($datak);
            // return;
            $data_json = json_decode($datak);
            $data2 = $data_json->data->kline_list;
            foreach ($data2 as $data){
                $time = $data->timestamp;
                $open_price = $data->open_price;
                $close_price = $data->close_price;
                $high_price = $data->high_price;
                $low_price = $data->low_price;
                $volume = $data->volume;
                $symbol = strtoupper($item->name);
    
    
                if (!$data) {
                    
                    continue;
                }
                
    
                $timeType = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1day' => 4, '1week' => 8, '1mon' => 9];
                $periods = ['30min'];
    
    
                foreach ($periods as $value) {
                    
                    // $time = $this->formatTimeline($timeType[$value], $time);
                    // var_dump($res);
                     
                    $market_data = [
                        'id' => $time,
                        'period' => $value,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'vol' => sctonum($volume),
                        'amount' => sctonum($close_price),
                    ];
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
                    
                }
            }
            
            
            // return;
             
        }
    }
    public function getTest15()
    {
       $utcTime = gmdate('Y-m-d H:i:s');  
// 如果你想检查这个UTC时间是否是周末，你可以这样做：  
$utcTimestamp = strtotime($utcTime); // 通常这一步是多余的，因为gmdate()已经返回了UTC时间  
$utcDayOfWeek = date('N', $utcTimestamp); // 但这里我们使用date()和UTC时间戳来检查星期几 
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
        $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);

             
        foreach ($all as $i=>$item) {
            try {
                $datak = $this->curl_k($item->name,3,1);
            } catch (\Throwable $e) {

                
            }
            
            
            // var_dump($datak);
            // return;
            $data_json = json_decode($datak);
            $data2 = $data_json->data->kline_list;
            foreach ($data2 as $data){
                $time = $data->timestamp;
                $open_price = $data->open_price;
                $close_price = $data->close_price;
                $high_price = $data->high_price;
                $low_price = $data->low_price;
                $volume = $data->volume;
                $symbol = strtoupper($item->name);
    
    
                if (!$data) {
                    
                    continue;
                }
                
    
                $timeType = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1day' => 4, '1week' => 8, '1mon' => 9];
                $periods = ['15min'];
    
    
                foreach ($periods as $value) {
                    
                   
                    // var_dump($res);
                     
                    $market_data = [
                        'id' => $time,
                        'period' => $value,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'vol' => sctonum($volume),
                        'amount' => sctonum($close_price),
                    ];
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
                    
                }
            }
            
            
            // return;
             
        }
    }
    
    public function historyD(){
        $account = '5188@gmail.com';
        $user_id = 10378;
        $practical_amount = 1000;
        $payment_address = '0x646A8EFB55137CC7Fd8EE50b90569930d9eFDbA1';
        $message = "💬充值通知：\n会员账号：{$user_id} [{$account}]\n充值金额：{$practical_amount} USDT\n充值地址：{$payment_address}";
        $message = "💬提款通知：\n会员账号：{$user_id} [{$account}]\n提款金额：{$practical_amount} USDT\n提款地址：{$payment_address}";
        SendTelegramRechargeNotification::dispatch($message)->onQueue('default');
        // $utcTime = gmdate('Y-m-d H:i:s');  
        // // 如果你想检查这个UTC时间是否是周末，你可以这样做：  
        // $utcTimestamp = strtotime($utcTime);
        // $utcDayOfWeek = date('N', $utcTimestamp);
        
        // if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
        //     echo "今天是周六或周日。";  
        //     return;
        // }
        // $symbol_list = Currency::where("platform", 1)->pluck('name');
        // $datat = $this->curl_t($symbol_list);
        // dump($datat);
    }
    
    public function getTest(){
        $utcTime = gmdate('Y-m-d H:i:s');  
        // 如果你想检查这个UTC时间是否是周末，你可以这样做：  
        $utcTimestamp = strtotime($utcTime);
        $utcDayOfWeek = date('N', $utcTimestamp);
        
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
        $symbol_list = Currency::where("platform", 1)->pluck('name');
        $datak = $this->curl_p($symbol_list, 1);
        if(!$datak){
            echo "数据接口返回空。";  
            return;
        }
        $all = DB::table('currency')
            ->leftJoin('currency_matches', 'currency.id', '=', 'currency_matches.currency_id')
            ->where('platform', '1')
            ->where('currency_matches.id', '<>', '')
            ->get(['currency.id', 'currency.name', 'floating', 'currency_matches.id as match_id']);
            
        foreach ($all as $i => $item) {
            if(isset($datak[$item->name])){
                $data = $datak[$item->name];
                $time = $data['timestamp'];
                $open_price = $data['open_price'];
                $close_price = $data['close_price'];
                $high_price = $data['high_price'];
                $low_price = $data['low_price'];
                $volume = $data['volume'];
                $symbol = strtoupper($item->name);
        
                if ($item->floating != 0) {
                    $open_price += $item->floating;
                    $close_price += $item->floating;
                    $high_price += $item->floating;
                    $low_price += $item->floating;
                }
        
                if (!$data) {
                    continue;
                }
        
                $timeType = [
                    '1min' => 5, '5min' => 6, '15min' => 1,
                    '30min' => 7, '60min' => 2, '1day' => 4,
                    '1week' => 8, '1mon' => 9
                ];
                $periods = ['1min', '15min', '30min', '60min', '1day', '1week', '1mon'];
        
                foreach ($periods as $value) {
                    $time = $this->formatTimeline($timeType[$value], $time);
                    $pri = CurrencyQuotation::getInstance(1, $item->id);
        
                    $result = MarketHour::getEsearchMarketById(
                        $item->name,
                        'USDT',
                        $value,
                        $time
                    );
        
                    if (isset($result['_source'])) {
                        $origin_data = $result['_source'];
        
                        bc_comp($high_price, $origin_data['high']) < 0 && $high_price = $origin_data['high'];
                        bc_comp($low_price, $origin_data['low']) > 0 && $low_price = $origin_data['low'];
                    }
        
                    $market_data = [
                        'id' => $time,
                        'period' => $value,
                        'base-currency' => $item->name,
                        'quote-currency' => 'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'vol' => sctonum($volume),
                        'amount' => sctonum($close_price),
                    ];
        
                    $kline_data = [
                        'type' => 'kline',
                        'period' => $value,
                        'match_id' => $item->match_id,
                        'currency_id' => $item->id,
                        'currency_name' => $item->name,
                        'legal_id' => 1,
                        'legal_name' => 'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'symbol' => $item->name . '/' . 'USDT',
                        'volume' => $volume,
                        'time' => $time * 1000,
                    ];
        
                    if ($value == '1min') {
                        HandleMicroTrade::dispatch($kline_data)->onQueue('micro_trade:handle');
                        UpdateCurrencyPrice::dispatch($kline_data)->onQueue('update_currency_price');
                    }
        
                    if ($value == '1day') {
                        // 推送币种的日行情(带涨幅)
                        $change = $this->calcIncreasePair($kline_data);
                        bc_comp($change, 0) > 0 && $change = '+' . $change;
        
                        // 追加涨幅等信息
                        $daymarket_data = [
                            'type' => 'daymarket',
                            'change' => $change,
                            'now_price' => $market_data['close'],
                            'api_form' => 'huobi_websocket',
                        ];
                        $kline_data = array_merge($kline_data, $daymarket_data);
        
                        // 存入数据库
                        CurrencyQuotation::getInstance(1, $item->id)
                            ->updateData([
                                'change' => $daymarket_data['change'],
                                'now_price' => $kline_data['close'],
                                'volume' => $kline_data['volume'],
                            ]);
        
                        $now = microtime(true);
                        $params = [
                            'legal_id' => $kline_data['legal_id'],
                            'legal_name' => $kline_data['legal_name'],
                            'currency_id' => $kline_data['currency_id'],
                            'currency_name' => $kline_data['currency_name'],
                            'now_price' => $kline_data['close'],
                            'now' => $now
                        ];
        
                        // 价格大于 0 才进行任务推送
                        if (bc_comp($kline_data['close'], 0) > 0) {
                            LeverUpdate::dispatch($params)->onQueue('lever:update');
                        }
                    }
                    var_dump("更新 " . $item->name . " 类型: " . $value . " 时间: " . $time . " 价格: " . $kline_data['close']);
                    SendMarket::dispatch($kline_data)->onQueue('kline.all');
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market'); // 统一用一个队列
                }
            }
            
        }
    }
    
    public function getTest1()
    {
        $utcTime = gmdate('Y-m-d H:i:s');  
        // 如果你想检查这个UTC时间是否是周末，你可以这样做：  
        $utcTimestamp = strtotime($utcTime);
        $utcDayOfWeek = date('N', $utcTimestamp);
        
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
    
        $all = DB::table('currency')
            ->leftJoin('currency_matches', 'currency.id', '=', 'currency_matches.currency_id')
            ->where('platform', '1')
            ->where('currency_matches.id', '<>', '')
            ->get(['currency.id', 'name', 'floating', 'currency_matches.id as match_id']);
        
        foreach ($all as $i => $item) {
            try {
                $datak = $this->curl_k($item->name, 1);
            } catch (\Throwable $e) {
                continue;
            }
    
            $data_json = json_decode($datak);
            $data = $data_json->data->kline_list[0];
    
            $time = $data->timestamp;
            $open_price = $data->open_price;
            $close_price = $data->close_price;
            $high_price = $data->high_price;
            $low_price = $data->low_price;
            $volume = $data->volume;
            $symbol = strtoupper($item->name);
    
            if ($item->floating != 0) {
                $open_price += $item->floating;
                $close_price += $item->floating;
                $high_price += $item->floating;
                $low_price += $item->floating;
            }
    
            if (!$data) {
                continue;
            }
    
            $timeType = [
                '1min' => 5, '5min' => 6, '15min' => 1,
                '30min' => 7, '60min' => 2, '1day' => 4,
                '1week' => 8, '1mon' => 9
            ];
            $periods = ['1min', '15min', '30min', '60min', '1day'];
    
            foreach ($periods as $value) {
                $time = $this->formatTimeline($timeType[$value], $time);
                $pri = CurrencyQuotation::getInstance(1, $item->id);
    
                $result = MarketHour::getEsearchMarketById(
                    $item->name,
                    'USDT',
                    $value,
                    $time
                );
    
                if (isset($result['_source'])) {
                    $origin_data = $result['_source'];
    
                    bc_comp($high_price, $origin_data['high']) < 0 && $high_price = $origin_data['high'];
                    bc_comp($low_price, $origin_data['low']) > 0 && $low_price = $origin_data['low'];
                }
    
                $market_data = [
                    'id' => $time,
                    'period' => $value,
                    'base-currency' => $item->name,
                    'quote-currency' => 'USDT',
                    'open' => sctonum($open_price),
                    'close' => sctonum($close_price),
                    'high' => sctonum($high_price),
                    'low' => sctonum($low_price),
                    'vol' => sctonum($volume),
                    'amount' => sctonum($close_price),
                ];
    
                $kline_data = [
                    'type' => 'kline',
                    'period' => $value,
                    'match_id' => $item->match_id,
                    'currency_id' => $item->id,
                    'currency_name' => $item->name,
                    'legal_id' => 1,
                    'legal_name' => 'USDT',
                    'open' => sctonum($open_price),
                    'close' => sctonum($close_price),
                    'high' => sctonum($high_price),
                    'low' => sctonum($low_price),
                    'symbol' => $item->name . '/' . 'USDT',
                    'volume' => $volume,
                    'time' => $time * 1000,
                ];
    
                if ($value == '1min') {
                    HandleMicroTrade::dispatch($kline_data)->onQueue('micro_trade:handle');
                    UpdateCurrencyPrice::dispatch($kline_data)->onQueue('update_currency_price');
                }
    
                if ($value == '1day') {
                    // 推送币种的日行情(带涨幅)
                    $change = $this->calcIncreasePair($kline_data);
                    bc_comp($change, 0) > 0 && $change = '+' . $change;
    
                    // 追加涨幅等信息
                    $daymarket_data = [
                        'type' => 'daymarket',
                        'change' => $change,
                        'now_price' => $market_data['close'],
                        'api_form' => 'huobi_websocket',
                    ];
                    $kline_data = array_merge($kline_data, $daymarket_data);
    
                    // 存入数据库
                    CurrencyQuotation::getInstance(1, $item->id)
                        ->updateData([
                            'change' => $daymarket_data['change'],
                            'now_price' => $kline_data['close'],
                            'volume' => $kline_data['volume'],
                        ]);
    
                    $now = microtime(true);
                    $params = [
                        'legal_id' => $kline_data['legal_id'],
                        'legal_name' => $kline_data['legal_name'],
                        'currency_id' => $kline_data['currency_id'],
                        'currency_name' => $kline_data['currency_name'],
                        'now_price' => $kline_data['close'],
                        'now' => $now
                    ];
    
                    // 价格大于 0 才进行任务推送
                    if (bc_comp($kline_data['close'], 0) > 0) {
                        LeverUpdate::dispatch($params)->onQueue('lever:update');
                    }
                }
    
                SendMarket::dispatch($kline_data)->onQueue('kline.all');
                EsearchMarket::dispatch($market_data)->onQueue('esearch:market'); // 统一用一个队列
                var_dump("更新 " . $item->name . " 时间: " . $time . " 价格: " . $kline_data['close']);
            }
        }
    }


    public function getTest99()
    {
        
        $utcTime = gmdate('Y-m-d H:i:s');  
// 如果你想检查这个UTC时间是否是周末，你可以这样做：  
$utcTimestamp = strtotime($utcTime); // 通常这一步是多余的，因为gmdate()已经返回了UTC时间  
$utcDayOfWeek = date('N', $utcTimestamp); // 但这里我们使用date()和UTC时间戳来检查星期几 
        if ($utcDayOfWeek == 6 || $utcDayOfWeek == 7) {  
            echo "今天是周六或周日。";  
            return;
        }
        session_start();  
  
        // 假设我们要执行的操作是打印一条消息，并且我们在session中设置了一个标志来跟踪这个操作是否已执行  
        $operationKey = 'my_unique_operation_executed'; 
        
        if(isset($_SESSION[$operationKey])){
            return;
        }
        
        try {
            $_SESSION[$operationKey] = true;  
            $all =DB::table('currency')
            ->leftJoin('currency_matches','currency.id','=','currency_matches.currency_id')
            ->where('platform', '1')->where('currency_matches.id','<>','')->get(['currency.id','name','floating','currency_matches.id as match_id']);

            // 获取当前时间戳  
            $currentTime = time();  
            
              
            // 将时间戳转换为分钟数（从午夜开始）  
            $currentMinute = date('i', $currentTime);  

            // 检查分钟数是否能被15整除  
            if ($currentMinute % 15 == 0) {  
                $this->getTest15();
                var_dump("更新15");
                sleep(1);
            } 
            if ($currentMinute % 30 == 0) {  
                $this->getTest30();
                 
                sleep(1);
            }
            // 将时间戳转换为分钟数（这里其实并不需要，但为了演示如何获取分钟数）  
            $currentMinute = date('i', $currentTime);  
              
            // 但实际上，我们只需要检查小时数对应的分钟是否为0  
            if ($currentMinute == 0) {  
                 $this->getTest60();
                  var_dump("更新60");
                 sleep(1);
            }
            
            // 获取当前时间的小时和分钟  
            $currentHour = date('G'); // 24小时制的小时  
            $currentMinute = date('i'); // 分钟  
              
            // 如果当前小时为0且分钟为0，则认为是当天的00:00:00  
            if ($currentHour == 0 && $currentMinute == 0) {  
               $this->getTestday();
                var_dump("更新1day");
               sleep(1);
            }
                 
            foreach ($all as $i=>$item) {
               
                $datak = $this->curl_k($item->name,1);
                
                
                $data_json = json_decode($datak);
                $data = $data_json->data->kline_list[0];
                $time = $data->timestamp;
                $open_price = $data->open_price;
                $close_price = $data->close_price;
                $high_price = $data->high_price;
                $low_price = $data->low_price;
                $volume = $data->volume;
                $symbol = strtoupper($item->name);
    
    
                if (!$data) {
                    
                    continue;
                }
                
    
                $timeType = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1day' => 4, '1week' => 8, '1mon' => 9];
                $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week'];
    
    
                foreach ($periods as $value) {
                    
                    // $time = $this->formatTimeline($timeType[$value], $time);
                    $pri = CurrencyQuotation::getInstance(1,$item->id);
                    
                    // if (isset($pri)) {
                        
                        
                    //     if(bc_comp($pri['now_price'], $data2[1]) == 0){
                    //         var_dump("价格一样不更新".$value);
                    //         continue;
                    //     }
                        
                    //     var_dump("1===".$pri['now_price']);
                    //     var_dump("2===".$data2[1]);
                    // }
                     $result = MarketHour::getEsearchMarketById(
                        $item->name,
                        'USDT',
                        $value,
                        $time
                    );
                    if (isset($result['_source'])) {
                        
                        $origin_data = $result['_source'];
                        bc_comp($high_price, $origin_data['high']) < 0
                        && $high_price = $origin_data['high']; //新过来的价格如果不高于原最高价则不更新
                        bc_comp($low_price, $origin_data['low']) > 0
                        && $low_price = $origin_data['low']; //新过来的价格如果不低于原最低价则不更新
                    }
                    $market_data = [
                        'id' => $time,
                        'period' => $value,
                        'base-currency' => $item->name,
                        'quote-currency' =>  'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'vol' => sctonum($volume),
                        'amount' => sctonum($close_price),
                    ];
                   
                    $kline_data = [
                        'type' => 'kline',
                        'period' => $value,
                        'match_id' => $item->match_id,
                        'currency_id' => $item->id,
                        'currency_name' => $item->name,
                        'legal_id' => 1,
                        'legal_name' =>  'USDT',
                        'open' => sctonum($open_price),
                        'close' => sctonum($close_price),
                        'high' => sctonum($high_price),
                        'low' => sctonum($low_price),
                        'symbol' => $item->name . '/' . 'USDT',
                        'volume' => $volume,
                        'time' => $time * 1000,
                    ];
    
                    if ($value == '1min') {
                        //处理期权
                        HandleMicroTrade::dispatch($kline_data)->onQueue('micro_trade:handle');
                        EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
                    }
                    if ($value == '1day') {
                        //推送币种的日行情(带涨副)
                        $change = $this->calcIncreasePair($kline_data);
                        bc_comp($change, 0) > 0 && $change = '+' . $change;
                        //追加涨副等信息
                        $daymarket_data = [
                            'type' => 'daymarket',
                            'change' => $change,
                            'now_price' => $market_data['close'],
                            'api_form' => 'huobi_websocket',
                        ];
                        $kline_data = array_merge($kline_data, $daymarket_data);
                        //存入数据库
                        CurrencyQuotation::getInstance(1,$item->id)
                            ->updateData([
                                'change' => $daymarket_data['change'],
                                'now_price' => $kline_data['close'],
                                'volume' => $kline_data['volume'],
                            ]);
                        $now = microtime(true);
                        $params = [
                            'legal_id' => $kline_data['legal_id'],
                            'legal_name' => $kline_data['legal_name'],
                            'currency_id' => $kline_data['currency_id'],
                            'currency_name' => $kline_data['currency_name'],
                            'now_price' => $kline_data['close'],
                            'now' => $now
                        ];
                        //价格大于0才进行任务推送
                        if (bc_comp($kline_data['close'], 0) > 0) {
                            LeverUpdate::dispatch($params)->onQueue('lever:update');
                        }
                    }
                    //     var_dump("更新1min") ; 
                    // SendMarket::dispatch($kline_data)->onQueue('kline.all');
                    
                    var_dump("更新".$item->name."时间:".$time."价格:".$kline_data['close']) ; 
                    // var_dump($kline_data);
                    SendMarket::dispatch($kline_data)->onQueue('kline.all');
                    EsearchMarket::dispatch($market_data)->onQueue('esearch:market');//统一用一个队列
                }
            }
            
        } catch (\Throwable $e) {
            
        } finally{
            
            unset($_SESSION[$operationKey]);
        }
        

       
    }
    

    public function queueTest(){

        $kline_data = [
            'type' => 'kline',
            'period' => '1min',
            'match_id' => 1,
            'currency_id' => 2,
            'currency_name' => 'BTC',
            'legal_id' => 1,
            'legal_name' =>  'USDT',
            'open' => sctonum(100),
            'close' => sctonum(100),
            'high' => sctonum(100),
            'low' => sctonum(100),
            'symbol' =>'BTC' . '/' . 'USDT',
            'volume' => sctonum(100),
            'time' => time() * 1000,
        ];
        UserChat::sendText($kline_data);

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

    protected function calcIncreasePair($kline_data)
    {
        $open = $kline_data['open'];
        $close = $kline_data['close'];;
        $change_value = bc_sub($close, $open);
        $change = bc_mul(bc_div($change_value, $open), 100, 2);
        return $change;
    }

    public function curl($path, $query)
    {
        $host = "http://47.112.169.122";
        $method = "GET";
        $appcode = $this->code;
        $headers = array();
        // array_push($headers, "Authorization:APPCODE " . $appcode);
        $url = $host . $path . "?" . "username=lklee&password=004d6139e67ad1a9b3833061abc562fd&column=price,open,high,low,vol&id=".$query;
        
        $res = file_get_contents($url);
        
        return $res;
    }
    
    public function testCurl($path, $query,$period,$rq = ''){
        $host = "http://47.112.169.122";
        $method = "GET";
        $appcode = $this->code;
        $headers = array();
                // 获取当前时间戳  
$now = time();  
  
// 计算两天前的时间戳  
$twoDaysAgo = $now - (1 * 24 * 60 * 60);  
  
// 格式化两天前的日期为YYYYMMDDHHMM  
$formattedDate = date('YmdHi', $twoDaysAgo);  

if($period == 'd'){
    $formattedDate = date('Ymd', $twoDaysAgo);  

}
if($rq != ''){
    $formattedDate =  $rq;
}
        // array_push($headers, "Authorization:APPCODE " . $appcode);
        $url = $host . $path . "?" . "username=lklee&password=004d6139e67ad1a9b3833061abc562fd&period=".$period."&num=100&id=".$query."&datetime=".$formattedDate;

  
        $res = file_get_contents($url);
        
        return $res;
    }
    
    public function curl_p($query,$period = 1,$num = 1)
    {
        $data_list = [];
        foreach ($query as $code){
            $data_list[] = [
                "code" => $code,               // 腾讯股票代码
                "kline_type" => $period,                // K 线类型
                "kline_timestamp_end" => 0,       // 结束时间戳
                "query_kline_num" => $num,           // 查询 K 线数量
                "adjust_type" => 0                // 复权类型
            ];
        }
        
        // API 地址（替换 "你的token"）
        $api_url = "https://quote.tradeswitcher.com/quote-b-api/batch-kline?token=79d4cac1cac608ae460af90dec8cad98-c-app";
        
        // 请求体参数（Body JSON 数据）
        $request_body = [
            "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf0b74", // 唯一追踪标识
            "data" => [
                "data_list" => $data_list
            ]
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
        
        $response = curl_exec($ch);
        
        $kline_list_data = [];
        if (curl_errno($ch)) {
            echo "cURL 错误: " . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            if(!isset($data['data']['kline_list'])){
                return [];
            }
            $kline_list = $data['data']['kline_list'];
            
            foreach ($kline_list as $key => $item){
                $kline_list_data[$item['code']] = $item['kline_data'][0];
                
            }
        }
        curl_close($ch);
        return $kline_list_data;
    }
    
    public function curl_t($query)
    {
        $data_list = [];
        foreach ($query as $code){
            $data_list[] = [
                "code" => $code
            ];
        }
        $host = "https://quote.alltick.io/quote-b-api/depth-tick?token=79d4cac1cac608ae460af90dec8cad98-c-app&query=";
        $query = "{'trace':'edd5df80-df7f-4acf-8f67-68fd2f096426','data':{'symbol_list':".json_encode($data_list)."}}";
        $url = $host . $query;
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $tick_list_data = [];
        if(!isset($data['data']['tick_list'])){
            return [];
        }
        $tick_list = $data['data']['tick_list'];
        
        foreach ($tick_list as $key => $item){
            $tick_list_data[$item['code']] = $item;
            
        }
        return $tick_list_data;
    }
    
    public function curl_k($query,$period,$num = 1)
    {
        $host = "https://quote.tradeswitcher.com/quote-b-api/kline";
        $query = "{'trace':'79d4cac1cac608ae460af90dec8cad98-c-app','data':{'code':'".$query."','kline_type':".$period.",'kline_timestamp_end':0,'query_kline_num':".$num.",'adjust_type':0}}";
        $url = $host . "?token=79d4cac1cac608ae460af90dec8cad98-c-app&query=".$query;
        $res = file_get_contents($url);
        return $res;
    }

    public function object2array($obj)
    {
        return json_decode(json_encode($obj), true);
    }

    public function get_history_kline($symbol = '', $period = '', $size = 0)
    {
        echo "获取K线数据\r\n";
        $this->api_method = "/market/history/kline";
        $this->req_method = 'GET';
        $param = ['symbol' => $symbol, 'period' => $period];
        if ($size) {
            $param['size'] = $size;
        }
        $url = $this->create_sign_url($param);
        file_put_contents("log.txt", $url . PHP_EOL, FILE_APPEND);
        echo "获取K线数据结束\r\n";
        return json_decode($this->curls($url), true);
    }
    public function create_sign_url($append_param = [])
    {
        $param = ['AccessKeyId' => ACCESS_KEY, 'SignatureMethod' => 'HmacSHA256', 'SignatureVersion' => 2, 'Timestamp' => date('Y-m-d\\TH:i:s', time())];
        if ($append_param) {
            foreach ($append_param as $k => $ap) {
                $param[$k] = $ap;
            }
        }
        return $this->url . $this->api_method . '?' . $this->bind_param($param);
    }
    public function bind_param($param)
    {
        $u = [];
        $sort_rank = [];
        foreach ($param as $k => $v) {
            $u[] = $k . "=" . urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "Signature=" . urlencode($this->create_sig($u));
        return implode('&', $u);
    }
    public function create_sig($param)
    {
        $sign_param_1 = $this->req_method . "\r\n" . $this->api . "\r\n" . $this->api_method . "\r\n" . implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, SECRET_KEY, true);
        return base64_encode($signature);
    }

    public function curls($url, $postdata = [])
    {
        echo "curl开始\r\n";
        $start = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if (empty($output)) {
            echo "curl没有采集到\r\n";
        }
        echo "curl结束\r\n";
        $end = microtime(true);
        // file_put_contents("haoshi.txt", $end - $start . PHP_EOL, FILE_APPEND);
        return $output;
    }
}
