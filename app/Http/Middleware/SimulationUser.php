<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimulationUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->simulation==1) {
            return $this->error('模拟账号未开放该功能');// 返回结果和状态码
        }
        return $next($request);
    }

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
