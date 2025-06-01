<?php

/**
 * 工具类接口
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class UtilsController extends Controller
{
    /**
     * 统一文件图片上传接口
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $user = $request->user(); // 获取登录用户信息

//        $image = file_ext(); // 上传文件类型

        $request->validate([
//            'file' => 'required|mimes:'.$image, // 允许的上传文件类型
            'type' => 'required|in:0,1', // 类型 0-图片 1-视频文件
        ]);

        // 处理上传类型
        if ($request->type == 1) {
            $type = 'file';
        } else {
            $type = 'image';
        }

        $file = upload_images($request->file('file'), $user->id, $type);

        $data['file_path'] = $file->path;
        $data['file_url'] = Storage::disk(config('filesystems.default'))->url($file->path);

        $data['message'] = "";
        return response()->json($data, 200);
    }

    /**
     * 生成 JSSDK 签名
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsSdkSign(Request $request)
    {
        $app = app('easywechat.official_account');
        $utils = $app->getUtils();
        $data = $utils->buildJsSdkConfig(
            url: $request->url, // 提交的网址,需要在微信公众号授权 URL地址 中
        );

        return response()->json($data, 200);
    }

    /**
     * 获取微信 token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function weixinCode(Request $request)
    {
        $data = Socialite::driver('weixin')->getAccessTokenResponse($request->code);

        return response()->json($data, 200);
    }

    /**
     * 获取指定用户 token
     * 提交 user_id
     * php artisan passport:client --personal
     * @param  Request  $request
     * @return array
     */
    public function getUserToken(Request $request)
    {
        $user_id = $request->user_id;
        $user = Users::find($user_id);
        $token = $user->createToken('api')->accessToken;
        $data = ['token_type' => "Bearer", 'expires_in' => 1296000, 'access_token' => $token, 'user_id' => $user_id];
        return $data;
    }
}
