<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FreezeUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->status==1) {
            $request->user()->tokens->each(function ($token) {
                $token->delete(); // 删除每个令牌
            });
            return $this->error('您的资产账号因涉及可疑活动/政策违规已被冻结，请联系客服了解详情');// 返回结果和状态码
        }
        return $next($request);
    }
    
    /**
     * 返回一个错误响应
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($message)
    {
        header('Content-Type:application/json');
        // header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,Authorization');
        if (is_string($message)){
            $message=str_replace('massage.', '', __("massage.$message"));
        }
        return response()->json(['type' => 'error', 'message' => $message]);
    }
}
