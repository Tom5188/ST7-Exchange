<?php

namespace App\Utils\Workerman;

use App\Http\Controllers\Api\AliMarketController;
use App\Http\Controllers\Api\LeverController;
use App\Jobs\UpdateCurrencyPrice;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Type\Decimal;
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use App\Services\RedisService;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Currency,
    CurrencyMatch,
    CurrencyQuotation,
    MyQuotation,
    UsersWallet,
    MarketHour,
    UserChat,
    AccountLog,
    BindBox,
    BindBoxOrder,
    BindBoxQuotationLog,
    BindBoxSuccessOrder,
    BindBoxCollect,
    BindBoxMarginLog,
    BindBoxRaityHouse
};
use App\Jobs\{
    CoinTradeHandel,
    EsearchMarket,
    LeverUpdate,
    LeverPushPrice,
    SendLever,
    SendMarket,
    WriteMarket,
    HandleMicroTrade
};
use Illuminate\Support\Carbon;

class WsConnection
{
    protected $server_address = 'ws://api.huobi.pro:443/ws';
    protected $alltick_address = 'ws://quote.alltick.io/quote-b-ws-api?token=79d4cac1cac608ae460af90dec8cad98-c-app'; //ws国内开发调试wss://quote.tradeswitcher.com/quote-b-ws-api
    protected $server_ping_freq = 5; //服务器ping检测周期,单位秒
    protected $server_time_out = 2; //服务器响应超时
    protected $send_freq = 3; //写入和发送数据的周期，单位秒
    protected $micro_trade_freq = 1; //秒合约处理时间周期

    protected $worker_id;

    protected $events = [
        'onConnect',
        'onClose',
        'onMessage',
        'onError',
        'onBufferFull',
        'onBufferDrain',
    ];
    
    protected static $currencyData = [];

    protected static $marketKlineData = [];
    
    protected static $marketDepthData = [];
    
    protected static $matchTradeData = []; //撮合交易全站交易
    
    protected $alltick_timer;

    protected $connection;

    protected $timer;

    protected $pingTimer;

    protected $sendKlineTimer;

    protected $bidding_nft;

    protected $depthTimer;

    protected $handleTimer;

    protected $microTradeHandleTimer;

    protected $subscribed = [];

    protected $topicTemplate = [
        'sub' => [
            'market_kline' => 'market.$symbol.kline.$period',
            'market_detail' => 'market.$symbol.detail',
            'market_depth' => 'market.$symbol.depth.$type',
            'market_trade' => 'market.$symbol.trade.detail', //成交的交易
        ],
    ];

    public function __construct($worker_id)
    {
        $currency = Currency::where("platform", 1)->get()->toArray();
        foreach($currency as $key=>$val){
            self::$currencyData[$val['name']] = $val;
        }
        $this->worker_id = $worker_id;
        AsyncTcpConnection::$defaultMaxPackageSize = 1048576000;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 绑定所有事件到连接
     *
     * @return void
     */
    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = [$this, $event];
                //echo '绑定' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    /**
     * 解除连接所有绑定事件
     *
     * @return void
     */
    protected function unBindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = null;
                //echo '解绑' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    public function getSubscribed($topic = null)
    {
        if (is_null($topic)) {
            return $this->subscribed;
        }
        return $this->subscribed[$topic] ?? null;
    }

    protected function setSubscribed($topic, $value)
    {
        $this->subscribed[$topic] = $value;
    }

    protected function delSubscribed($topic)
    {
        unset($this->subscribed[$topic]);
    }
    
    public function connect_alltick()
    {
        $ws_connection = new AsyncTcpConnection($this->alltick_address);
        $ws_connection->onConnect = function($conn){
            $currency = Currency::where("platform", 1)->pluck('name');
            $symbol_list = [];

            foreach ($currency as $key => $value) {
                $symbol_list[] = [
                    "code" => $value
                ];
            }
            $conn->send('{
                "cmd_id":22002,
                "seq_id":123,
                "trace":"asdfsdfa",
                "data":{
                    "symbol_list": ' . json_encode($symbol_list) . '
                }
            }');
        };
        $ws_connection->onMessage = function($conn, $data){
            $data = json_decode($data, true);
            if(isset($data['data'])){
                $tick = $data['data'];
                foreach($tick['asks'] as $k=>&$v){
                    $v = [floatval($v['price']), intval(intval($v['volume']) - intval($v['volume']) * mt_rand(1, 5) / 10)];
                }
                foreach($tick['bids'] as $k=>&$v){
                    $v = [floatval($v['price']), intval(intval($v['volume']) - intval($v['volume']) * mt_rand(1, 5) / 10)];
                }
                $depth_data = [
                    'type' => 'market_depth',
                    'symbol' => $tick['code'] . '/USDT',
                    'base-currency' => $tick['code'],
                    'quote-currency' => 'USDT',
                    'currency_id' => self::$currencyData[$tick['code']]['id'],
                    'currency_name' => $tick['code'],
                    'legal_id' => 1,
                    'legal_name' => 'USDT',
                    'bids' => array_slice($tick['bids'], 0, 10), //买入盘口
                    'asks' => array_slice(array_reverse($data->tick->asks), 0, 10), //卖出盘口
                ];
                $symbol_key = $tick['code'] . '.' . 'USDT';
                self::$marketDepthData[$symbol_key] = $depth_data;
            }
        };
        $ws_connection->onError = function($conn, $code, $msg){
            echo "Error: $msg\n";
        };
        $ws_connection->onClose = function($conn){
            $this->alltick_timer && Timer::del($this->alltick_timer);
            $conn->reConnect(10);
        };
        $ws_connection->connect();
    }

    public function connect()
    {
        $this->connection = new AsyncTcpConnection($this->server_address);
        $this->bindEvent();
        $this->connection->transport = 'ssl';
        $this->connection->connect();
        if ($this->worker_id == 9) {
            $this->connect_alltick();
        }
    }

    public function onConnect($con)
    {
        //连接成功后定期发送ping数据包检测服务器是否在线
        $this->timer = Timer::add($this->server_ping_freq, [$this, 'ping'], [$this->connection], true);
        if ($this->worker_id < 8) {
            $this->sendKlineTimer = Timer::add($this->send_freq, [$this, 'writeMarketKline'], [], true);
        } else {
            $this->depthTimer = Timer::add($this->send_freq, [$this, 'sendDepthData'], [], true);
            $this->sendMatchTradeTimer = Timer::add($this->send_freq, [$this, 'sendMatchTradeData'], [], true);
        }

        if ($this->worker_id == 5) {
            $this->handleTimer = Timer::add($this->send_freq, [self::class, 'sendLeverHandle'], [], true);
        }
        if ($this->worker_id == 0) {
            // echo  '处理期权订单' . PHP_EOL;
            $this->microTradeHandleTimer = Timer::add($this->micro_trade_freq, [self::class, 'handleMicroTrade'], [], true);
        }
        //添加订阅事件代码
        $this->subscribe($con);
    }
    
    /**
     * 发送盘口数据
     *
     * @return void
     */
    public function sendDepthData()
    {
        $market_depth = self::$marketDepthData;
        foreach ($market_depth as $depth_data) {
            SendMarket::dispatch($depth_data)->onQueue('market.depth');
        }
    }
    
    /**
     * 发送全站交易数据
     *
     * @return void
     */
    public function sendMatchTradeData()
    {
        $market_trade = self::$matchTradeData;
        foreach ($market_trade as $trade_data) {
            SendMarket::dispatch($trade_data)->onQueue('default');
        }
        self::$matchTradeData = []; //发送完清空,以避免重复发送相同的数据
    }

    public function onClose($con)
    {
        echo $this->server_address . '连接关闭' . PHP_EOL;
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $this->server_address . '连接关闭' . PHP_EOL, 3, $path . $filename);
        //解除事件
        $this->timer && Timer::del($this->timer);
        $this->sendKlineTimer && Timer::del($this->sendKlineTimer);
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->depthTimer && Timer::del($this->depthTimer);
        $this->handleTimer && Timer::del($this->handleTimer);
        $this->bidding_nft && Timer::del($this->bidding_nft);
        $this->microTradeHandleTimer && Timer::del($this->microTradeHandleTimer);
        $this->unBindEvent();
        unset($this->connection);
        $this->connection = null;
        $this->subscribed = null; //清空订阅
        echo '尝试重新连接' . PHP_EOL;
        $this->connect();
    }

    public function close($msg)
    {
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $msg, 3, $path . $filename);
        $this->connection->destroy();
    }

    protected function makeSubscribeTopic($topic_template, $param)
    {
        $need_param = [];
        $match_count = preg_match_all('/\$([a-zA-Z_]\w*)/', $topic_template, $need_param);
        if ($match_count > 0 && count(reset($need_param)) > count($param)) {
            throw new \Exception('所需参数不匹配');
        }
        $diff = array_diff(next($need_param), array_keys($param));
        if (count($diff) > 0) {
            throw new \Exception('topic:' . $topic_template . '缺少参数：' . implode(',', $diff));
        }
        return preg_replace_callback('/\$([a-zA-Z_]\w*)/', function ($matches) use ($param) {
            extract($param);
            $value = $matches[1];
            return $$value ?? '';
        }, $topic_template);
    }

    public function onBufferFull()
    {
        echo 'buffer is full' . PHP_EOL;
    }

    protected function subscribe($con)
    {
        $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
        if ($this->worker_id < 8) {
            $value = $periods[$this->worker_id];
            echo '进程' . $this->worker_id . '开始订阅' . $value . '数据' . PHP_EOL;
            $this->subscribeKline($con, $value); //订阅k线行情
        } else {
            if ($this->worker_id == 8) {
                $this->subscribeMarketDepth($con); //订阅盘口数据
                $this->subscribeMarketTrade($con); //订阅全站交易数据
            }
        }
    }
    
    /**
     * 订阅全站交易(已完成)
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param \App\Models\CurrencyMatch $currency_match
     * @return void
     */
    public function subscribeMarketTrade($con)
    {
        $currency_list = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_list as $key => $currency_match) {
            $param = [
                'symbol' => $currency_match->match_name,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_trade'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            $subscribed_data = $this->getSubscribed($topic);
            $match_data = $subscribed_data['match'] ?? [];
            $match_data[] = $currency_match;
            $this->setSubscribed($topic, [
                'callback' => 'onMatchTrade',
                'match' => $match_data,
            ]);
            // 未订阅过的才能订阅
            if (is_null($subscribed_data)) {
                $con->send($sub_data);
            }
        }
    }
    
    /**
     * 撮合交易全站交易数据回调
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection $currency_matches
     * @return void
     */
    protected function onMatchTrade($con, $data, $currency_matches)
    {
        $topic = $data->ch;
        $data = $data->tick->data;
        foreach ($currency_matches as $key => $currency_match) {
            $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            if ($symbol_key == 'BTC.USDT') {
                $self_match_ids = self::getSelfMatchIds();
                foreach ($self_match_ids as $id) {
                    $currencyMatch = CurrencyMatch::query()->find($id);
                    if (!$currencyMatch) {
                        continue 1;
                    }
                    $now_price = CurrencyQuotation::where('match_id', $id)->value('now_price');
                    $rate_arr = [-0.004, -0.003, -0.002, -0.001, 0.001, 0.002, 0.003, 0.004];
                    $price = bc_add($now_price, $rate_arr[array_rand($rate_arr)]);
                    $self_data = [];
                    $self_data[] = [
                        'id' => time() . rand(1, 100000),
                        'amount' => rand(100, 39 * 56 - 184) / (16 * 56 + 104),
                        'direction' => $data[0]->direction,
                        'price' => $price,
                        'tradeId' => time() . rand(1, 3333),
                        'ts' => self::getMillisecond()
                    ];
                    $self_trade_data = [
                        'type' => 'match_trade',
                        'symbol' => $currencyMatch->currency_name . '/' . $currencyMatch->legal_name,
                        'base-currency' => $currencyMatch->currency_name,
                        'quote-currency' => $currencyMatch->legal_name,
                        'currency_id' => $currencyMatch->currency_id,
                        'currency_name' => $currencyMatch->currency_name,
                        'legal_id' => $currencyMatch->legal_id,
                        'legal_name' => $currencyMatch->legal_name,
                        'data' => $self_data
                    ];
                    self::$matchTradeData[$currencyMatch->currency_name . '.' . $currencyMatch->legal_name] = $self_trade_data;
                }
            }
            if ($currency_match->market_from == 2) {
                $trade_data = [
                    'type' => 'match_trade',
                    'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                    'base-currency' => $currency_match->currency_name,
                    'quote-currency' => $currency_match->legal_name,
                    'currency_id' => $currency_match->currency_id,
                    'currency_name' => $currency_match->currency_name,
                    'legal_id' => $currency_match->legal_id,
                    'legal_name' => $currency_match->legal_name,
                    'data' => $data,
                ];
                self::$matchTradeData[$symbol_key] = $trade_data;
            }
        }
    }
    
    //订阅盘口数据
    protected function subscribeMarketDepth($con)
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->match_name,
                'type' => 'step0',
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_depth'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            //未订阅过的才能订阅
            if (is_null($this->getSubscribed($topic))) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMarketDepth',
                    'match' => $value
                ]);
                $con->send($sub_data);
            }
        }
    }
    
    //盘口数据回调
    protected function onMarketDepth($con, $data, $match)
    {
        $topic = $data->ch;
        $subscribed_data = $this->getSubscribed($topic);
        $currency_match = $subscribed_data['match'];
        krsort($data->tick->asks);
        $data->tick->asks = array_values($data->tick->asks);
        if ($currency_match->market_from == 2) {
            $depth_data = [
                'type' => 'market_depth',
                'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                'base-currency' => $currency_match->currency_name,
                'quote-currency' => $currency_match->legal_name,
                'currency_id' => $currency_match->currency_id,
                'currency_name' => $currency_match->currency_name,
                'legal_id' => $currency_match->legal_id,
                'legal_name' => $currency_match->legal_name,
                'bids' => array_slice($data->tick->bids, 0, 10), //买入盘口
                'asks' => array_slice($data->tick->asks, 0, 10), //卖出盘口
            ];
            $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            self::$marketDepthData[$symbol_key] = $depth_data;
        }
    }

    //订阅回调
    protected function onSubscribe($data)
    {
        if ($data->status == 'ok') {
            echo $data->subbed . '订阅成功' . PHP_EOL;
        } else {
            echo '订阅失败:' . $data->{'err-msg'} . PHP_EOL;
        }
    }


    //订阅K线行情
    protected function subscribeKline($con, $period)
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->match_name,
                'period' => $period,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_kline'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            //未订阅过的才能订阅
            if (is_null($this->getSubscribed($topic))) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMarketKline',
                    'match' => $value
                ]);
                $con->send($sub_data);
            }
        }
    }

    protected function onMarketKline($con, $data, $match)
    {
        $topic = $data->ch;
        // echo date('Y-m-d H:i:s') . '--｜进程' . $this->worker_id . '接收' . $topic . '行情' . PHP_EOL;
        // echo '--｜数据：'.json_encode($data);
        list($name, $symbol, $detail_name, $period) = explode('.', $topic);
        $subscribed_data = $this->getSubscribed($topic);
        $currency_match = $subscribed_data['match'];
        $tick = $data->tick;
        $currency = Currency::with([])->where('name', $currency_match->currency_name)
            ->where('floating', '<>', 0)
            ->first();
        if ($currency) {
            $tick->open += $currency->floating;
            $tick->close += $currency->floating;
            $tick->high += $currency->floating;
            $tick->low += $currency->floating;
        }
        $itime = strtotime(date('Y-m-d H:i:00', time()));
        $myquotation = MyQuotation::with([])
            ->where('base', $currency_match->currency_name)
            ->where('itime', $itime)
            ->first();
        if ($myquotation) {
            $tick->open = (float)$myquotation->open;
            $tick->close = (float)$myquotation->close;
            $tick->high = (float)$myquotation->high;
            $tick->low =(float)$myquotation->low;
        }
        if ($currency_match->market_from == 2) {
            $market_data = [
                'id' => $tick->id,
                'period' => $period,
                'base-currency' => $currency_match->currency_name,
                'quote-currency' => $currency_match->legal_name,
                'open' => sctonum($tick->open),
                'close' => sctonum($tick->close),
                'high' => sctonum($tick->high),
                'low' => sctonum($tick->low),
                'vol' => sctonum($tick->vol),
                'amount' => sctonum($tick->amount),
            ];

            $kline_data = [
                'type' => 'kline',
                'period' => $period,
                'match_id' => $currency_match->id,
                'currency_id' => $currency_match->currency_id,
                'currency_name' => $currency_match->currency_name,
                'legal_id' => $currency_match->legal_id,
                'legal_name' => $currency_match->legal_name,
                'open' => sctonum($tick->open),
                'close' => sctonum($tick->close),
                'high' => sctonum($tick->high),
                'low' => sctonum($tick->low),
                'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                'volume' => sctonum($tick->amount),
                'time' => $tick->id,
            ];
            $key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            self::$marketKlineData[$period][$key] = [
                'market_data' => $market_data,
                'kline_data' => $kline_data,
            ];
            if ($period == '1day') {
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
                self::$marketKlineData[$period][$key]['kline_data'] = $kline_data;
            }
            // echo json_encode($kline_data).PHP_EOL;
        }
    }


    private function getMillisecond()
    {
        list($pSppfwv, $hmvXQMv) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($pSppfwv) + floatval($hmvXQMv)) * (0 - 1352 + 42 * 56));
    }

    public static function getSelfMatch()
    {
        $redis = RedisService::getInstance(4);
        $result = $redis->get('self_match');
        if (!$result) {
            $match_ids = CurrencyMatch::where('market_from', 0)->where('fluctuate_min', '>', 0)->where('fluctuate_max', '>', 0)->pluck('id')->toArray();
            $redis->set('self_match', implode(',', $match_ids));
            $redis->expire('self_match', 10);
            return $match_ids;
        } else {
            return explode(',', $result);
        }
    }

    public static function getSelfMatchIds()
    {
        $redis = RedisService::getInstance(4);
        $result = $redis->get('self_match');
        if (!$result) {
            $match_ids = CurrencyMatch::whereHas('currency', function ($query) {
                $query->where('platform', 1);
            })->pluck('id')->toArray();
            $redis->set('self_match', implode(',', $match_ids));
            $redis->expire('self_match', 10);
            return $match_ids;
        } else {
            return explode(',', $result);
        }
    }

    public function onMessage($con, $data)
    {
        $data = gzdecode($data);
        $data = json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
        if (isset($data->ping)) {
            $this->onPong($con, $data);
        } elseif (isset($data->pong)) {
            $this->onPing($con, $data);
        } elseif (isset($data->id) && $this->getSubscribed($data->id) != null) {
            $this->onSubscribe($data);
        } elseif (isset($data->id)) {

        } else {
            $this->onData($con, $data);
        }
    }

    protected function onData($con, $data)
    {
        if (isset($data->ch)) {
            $subscribed = $this->getSubscribed($data->ch);
            if ($subscribed != null) {
                //调用回调处理
                $callback = $subscribed['callback'];
                $this->$callback($con, $data, $subscribed['match']);
            } else {
                //不在订阅中的数据
            }
        } else {
            echo '未知数据' . PHP_EOL;
            var_dump($data);
        }
    }



    public static function sendLeverHandle()
    {
        $now = microtime(true);
        $market_kiline = self::$marketKlineData['1day'];
        foreach ($market_kiline as $key => $value) {
            $kline_data = $value['kline_data'];
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
    }

    public static function handleMicroTrade()
    {
        $market_data = self::$marketKlineData;
        foreach ($market_data as $period => $data) {
            foreach ($data as $key => $symbol) {
                if ($period == '1min') {
                    echo '期权时间:' . date('Y-m-d H:i:s') . ', Symbol:' . $key . '.' . $period . '数据' . PHP_EOL;
                    //处理期权
                    HandleMicroTrade::dispatch($symbol['kline_data'])->onQueue('micro_trade:handle');
                }
            }
        }
    }

    public function writeMarketKline()
    {
        if ($this->worker_id < 8) {
            $market_data = self::$marketKlineData;
            foreach ($market_data as $period => $data) {
                foreach ($data as $key => $symbol) {
                    echo '--｜处理' . $key . '.' . $period . '数据' . PHP_EOL;

                    $result = MarketHour::getEsearchMarketById(
                        $symbol['market_data']['base-currency'],
                        $symbol['market_data']['quote-currency'],
                        $period,
                        $symbol['market_data']['id']
                    );
                    if (isset($result['_source'])) {
                        $origin_data = $result['_source'];
                        bc_comp($symbol['kline_data']['high'], $origin_data['high']) < 0
                        && $symbol['kline_data']['high'] = $origin_data['high']; //新过来的价格如果不高于原最高价则不更新
                        bc_comp($symbol['kline_data']['low'], $origin_data['low']) > 0
                        && $symbol['kline_data']['low'] = $origin_data['low']; //新过来的价格如果不低于原最低价则不更新
                    }
                    SendMarket::dispatch($symbol['kline_data'])->onQueue('kline.all');

                    EsearchMarket::dispatch($symbol['market_data'])->onQueue('esearch:market');//统一用一个队列

                    if ($period == '1day') {
                        //推送一天行情
                        $day_kline = $symbol['kline_data'];
                        $day_kline['type'] = 'kline';
                        //存入数据库
                        CurrencyQuotation::getInstance($symbol['kline_data']['legal_id'], $symbol['kline_data']['currency_id'])
                            ->updateData([
                                'change' => $symbol['kline_data']['change'],
                                'now_price' => $symbol['kline_data']['close'],
                                'volume' => $symbol['kline_data']['volume'],
                            ]);
                    } else {
                        continue;
                    }
                }
            }
        }
    }

    protected function calcIncreasePair($kline_data)
    {
        $open = $kline_data['open'];
        $close = $kline_data['close'];;
        $change_value = bc_sub($close, $open);
        $change = bc_mul(bc_div($change_value, $open), 100, 2);
        return $change;
    }

    //心跳响应
    protected function onPong($con, $data)
    {
        //echo '收到心跳包,PING:' . $data->ping . PHP_EOL;
        $send_data = [
            'pong' => $data->ping,
        ];
        $send_data = json_encode($send_data);
        $con->send($send_data);
        //echo '已进行心跳响应' . PHP_EOL;
    }

    public function ping($con)
    {
        $ping = time();
        //echo '进程' . $this->worker_id . '发送ping服务器数据包,ping值:' . $ping . PHP_EOL;
        $send_data = json_encode([
            'ping' => $ping,
        ]);
        $con->send($send_data);

    }

    protected function onPing($con, $data)
    {
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->pingTimer = null;
        //echo '进程' . $this->worker_id . '服务器正常响应中,pong:' . $data->pong. PHP_EOL;
    }
}
