<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Currency;
use App\Models\MarketHour;

class EmailAccountProfile extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        //
        foreach ($models as $model){
             $es_client = MarketHour::getEsearchClient();
            $currency = Currency::find($model->symbol_id);
            $periods = ['1min', '15min', '30min', '60min', '1day'];
            $offsetInSeconds = 15 * 3600;
            $dataStart = strtotime($model->data_start) - $offsetInSeconds;
            $dataEnd = strtotime($model->data_end) - $offsetInSeconds;
            $putStart = strtotime($model->put_start) - $offsetInSeconds;
            $putEnd = strtotime($model->put_end) - $offsetInSeconds;
            foreach ($periods as $value){
                var_dump($value);
                $newTime = $dataStart;
            
            $period = 1;
            if($value == '15min'){
                $period = 3;
            }
            if($value == '30min'){
                $period = 4;
            }
            if($value == '60min'){
                $period = 5;
            }
            if($value == '60min'){
                $period = 8;
            }
            
            try {
                $data = $this->curl_k($currency->name,$period,1000,$dataEnd);
            } catch (\Throwable $e) {
                $data = $this->curl_k($currency->name,$period,1000,$dataEnd);
            }
            
            
            $data_json = json_decode($data, true);

            $data = $data_json['data']['kline_list'];

            DB::table('market_kine')->where('period',$value)->where('symbol',$currency->name)->whereBetween('id', [$dataStart, $dataEnd])->delete();
                  foreach ($data as $v){

                     if($v['timestamp'] < $dataStart){
                         continue;
                     }

                    
                    $insert['id'] = $v['timestamp'];
                    $insert['time'] = $v['timestamp'];
                    $insert['period'] = $value;
                    $insert['open'] = $v['open_price'];
                    $insert['close'] = $v['close_price'];
                    $insert['high'] = $v['high_price'];
                    $insert['low'] = $v['low_price'];
                    $insert['vol'] = $v['volume'];
                    $insert['amount'] = $v['close_price'];
                    $insert['symbol'] =$currency->name;
                     DB::table('market_kine')->insert($insert);
                    
                }
            }
        }
        
 

    }
    
    public function curl_k( $query,$period,$num = 1,$time)
    {
        
        $host = "https://quote.tradeswitcher.com/quote-b-api/kline";
        $method = "GET";
        $query = "{'trace':'79d4cac1cac608ae460af90dec8cad98-c-app','data':{'code':'".$query."','kline_type':".$period.",'kline_timestamp_end':".$time.",'query_kline_num':".$num.",'adjust_type':0}}";
        
        $url = $host . "?" . "token=79d4cac1cac608ae460af90dec8cad98-c-app&query=".$query;
          
        $res = file_get_contents($url);
        

        return $res;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
