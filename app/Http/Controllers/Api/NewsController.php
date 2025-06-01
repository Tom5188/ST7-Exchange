<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\News;
use App\Models\Setting;
use App\Models\NewsCategory;

    class NewsController extends Controller
    {
        //获取公告
        public function getAnnouncement(Request $request): \Illuminate\Http\JsonResponse
        {
            $lang = $request->get('language', '');
            $lang == '' && $lang = 'en';
            $news = News::with([])->where('c_id', 1)->where('lang', $lang)->paginate();
            return $this->success('', 0, $news);
        }

        //获取资讯
        public function getInformation(Request $request): \Illuminate\Http\JsonResponse
        {
            $lang = $request->get('language', '');
            $lang == '' && $lang = 'en';
            $news = News::with([])->where('c_id', 2)->where('lang', $lang)->paginate();
            return $this->success('', 0, $news);
        }
    //隐私政策
        public function getPrivacyPolicy(Request $request): \Illuminate\Http\JsonResponse
        {
            $lang = $request->get('language', '');
            $lang == '' && $lang = 'en';
            $news = News::with([])->where('c_id', 3)->where('lang', $lang)->first();
            return $this->success('', 0, $news);
        }
        //用户协议
        public function getUserAgreement(Request $request): \Illuminate\Http\JsonResponse
        {
            $lang = $request->get('language', '');
            $lang == '' && $lang = 'en';
            $news = News::with([])->where('c_id', 4)->where('lang', $lang)->first();
            return $this->success('', 0, $news);
        }
        //公告
        public function getWindowAnnouncement(Request $request): \Illuminate\Http\JsonResponse
        {
            $lang = $request->get('language', '');
            $lang == '' && $lang = 'en';
            $news = News::with([])->where('c_id', 5)->where('lang', $lang)->first();
            return $this->success('', 0, $news);
        }
        public function get(Request $request)
        {
            $id = $request->get('id', 0);
            $news = News::find($id);
            return $this->success('',0,$news);
        }

    }
