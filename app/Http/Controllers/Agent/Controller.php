<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {

    /**
     * @param array $data
     * @param string $msg
     * @param int $code
     */
    public function ajaxReturn( $data = [] , $msg = ''  , $code = 0){
        $result = array(
            'code' => $code,  //0成功，1失败，1001未登录
            'msg' => $msg,   //提示信息
            'data' => $data    //数据或其它信息
        );
        return response()->json($result);
    }

    /**
     * @param array|string $data
     * @param string $info
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($msg = '',  $data=[]){
        $result = array(
            'code' => 200,  //0成功，1失败，1001未登录
            'msg' => $msg,   //提示信息
            'data' => $data    //数据或其它信息
        );
        return response()->json($result);
    }

    /**
     * @param
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($msg) {
        $result = array(
            'code' => 400,  //0成功，1失败，1001未登录
            'msg' => $msg,   //提示信息
            'data' => []    //数据或其它信息
        );
        return response()->json($result);
    }

    /**
     * @param
     * @return \Illuminate\Http\JsonResponse
     */
    public function notice($msg) {
        $result = array(
            'code' => 2,  //0成功，1失败，1001未登录
            'msg' => $msg,   //提示信息
            'data' => []    //数据或其它信息
        );
        return response()->json($result);
    }

    /**
     * @param string $data
     * @param string $info
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function outmsg($msg = ''){
        $result = array(
            'code' => 1001,  //0成功，1失败，1001未登录
            'msg' => $msg,   //提示信息
            'data' => []    //数据或其它信息
        );
        return response()->json($result);
    }

    /**
     * @param $paginateObj
     * @return \Illuminate\Http\JsonResponse
     */
    public function layuiData($paginateObj,$extra_data = ''){
        if ($paginateObj->total() >=1){
            return response()->json(['code'=>0,'msg'=>'','count'=>$paginateObj->total(),'data'=>$paginateObj->items(),'extra_data' => $extra_data]);
        }else{
            return response()->json(['code'=>1,'msg'=>'暂无数据','count'=>0,'data'=>[],'extra_data' => $extra_data]);
        }
    }
}
