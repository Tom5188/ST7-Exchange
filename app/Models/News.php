<?php
/**
 * create by vscode
 * @author lion
 */
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class News extends ShopModel
{
    protected $table = 'news';
    //自动时间戳
    protected $dateFormat = 'U';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $appends = [
       'thumbnail_info'
    ];

        // 'jp' => '日语',
        // 'ko' =>'韩语'
    protected static $langList = [
        'zh' => '中文简体',
        'hk' => '中文繁体',
        'en' => '英文',
        'jp' => '日语',
        'kor' => '韩语'
    ];
    //新闻图片
    public function getThumbnailInfoAttribute(){
        return  config('app.url') . "/storage/" .$this->attributes['thumbnail'];
    }
}
