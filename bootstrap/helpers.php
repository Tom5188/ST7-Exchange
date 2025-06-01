<?php

use App\Http\Resources\ImageResource;
use App\Models\Gateways\QxtGateway;
use GuzzleHttp\Client;
use Overtrue\EasySms\EasySms;

/**
 * 上传图片
 * @param SplFileInfo $file
 * @param int $user_id 用户 id
 * @param string $type 类型(存储目录) avatar,passport,cert,edu,course,banner,other
 * @param string $disk 磁盘名称
 */
function upload_images(SplFileInfo $file, int $user_id, string $type = "image", string $disk = "public"): ImageResource
{
    if (config('filesystems.default') != 'public') {
        $disk = config('filesystems.default');
    }
    $path = Storage::disk($disk)->putFile($type . '/' . date('Y/m/d'), $file);
    $image = new App\Models\Image();
    $image->type = $type; // 上传类型(存储目录)
    $image->path = $path; // 存储路径
    $image->disk = $disk; // 上传磁盘
    $image->size = $file->getSize(); // 获取文件大小
    $image->size_kb = number_fixed($image->size / 1024, 2); // 获取文件大小 k
    $image->user_id = $user_id; // 上传用户 ID
    $image->save();

    return new ImageResource($image);
}

/**
 *  允许上传图像类型
 * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed|string
 */
function image_ext(): mixed
{
    if (config('upload.image_ext')) {
        $ext = config('upload.image_ext');
    } else {
        $ext = "gif,bmp,jpeg,png"; // 默认上传图像类型
    }

    return $ext;
}

/**
 * 允许上传文件类型
 * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed|string
 */
function file_ext(): mixed
{
    if (config('image.file_ext')) {
        $ext = config('image.file_ext');
    } else {
        $ext = "gif,bmp,jpeg,png,zip,rar,docx,dox,mp4,mov,jpg"; // 默认上传文件类型
    }

    return $ext;
}

/**
 * 隐藏银行卡号
 * @param string $number
 * @param string $maskingCharacter
 * @return string
 */
function addMaskCC(string $number, string $maskingCharacter = '*'): string
{
    return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 8) . substr($number, -4);
}

/**
 * 保留几位小数 默认 5
 * @param float $num 数字
 * @param int $precision 保留位数
 * @return float|int
 */
function number_fixed(float $num, int $precision = 5): float|int
{
    return intval($num * pow(10, $precision)) / pow(10, $precision);
}

/**
 * 获取数组内的 id
 * @param array $data 数组
 * @param string $key 提取 key
 * @return array
 */
function get_array_ids(array $data, string $key = 'id'): array
{
    $ids = [];
    foreach ($data as $item) {
        $id = $item[$key] ?? false;
        if ($id === false) {
            continue;
        }
        $ids[$id] = 0;
    }
    return array_keys($ids);
}

/**
 * 发送短信
 * @param string $mobile 手机号
 * @param int $code 验证码
 * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
 * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
 */
function send_sms(string $mobile, int $code): array
{
    $sign = config('easysms.sms_sign_name');
    $easySms = new EasySms(config('easysms'));
    // 注册
    $easySms->extend('qxt', function ($gatewayConfig) {
        // $gatewayConfig 来自配置文件里的 `gateways.mygateway`
        return new QxtGateway($gatewayConfig);
    });
    $text = '【' . $sign . '】您的验证码是：' . $code . '。请不要把验证码泄露给其他人。';
    $result = $easySms->send($mobile, $text);

    return $result;
}

/**
 * @param float|int|string $num 科学计数法字符串  如 2.1E-5
 * @param int $double 小数点保留位数 默认5位
 * @return string
 */
function sctonum(float|int|string $num, int $double = 5)
{
    if (false !== stripos($num, "e")) {
        $a = explode("e", strtolower($num));
        return bcmul($a[0], bcpow(10, $a[1], $double), $double);
    }

    return $num;
}

/**
 * 生成唯一订单号
 * @param string $model 模型名称,首字母大写
 * @param string $field 订单号查询字段
 * @return bool|string
 */
function createNO(string $model, string $field): bool|string
{
    // 订单流水号前缀
    $prefix = date('YmdHis');
    for ($i = 0; $i < 10; $i++) {
        // 随机生成 6 位的数字
        $sn = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // 查询该模型是否已经存在对应订单号
        $modelName = '\\App\\Models\\' . $model;
        $MODEL = new $modelName;
        if (!$MODEL::query()->where($field, $sn)->exists()) {
            return $sn;
        }
    }
    \Log::warning('生成单号失败-' . $modelName);

    return false;
}

/**
 * 判断是否都是中文
 * @param string $str
 * @return bool|int
 */
function isAllChinese(string $str): bool|int
{
    $len = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
    if ($len) {
        return true;
    }
    return false;
}

/**
 * 验证是否是url
 * @param string $url url
 * @return boolean        是否是url
 */
function is_url(string $url): bool
{
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 格式化数字
 * @param int $number
 * @return int|string
 */
function float_number(int $number): int|string
{
    $length = strlen($number);  //数字长度
    if ($length > 8) { //亿单位
        $str = substr_replace(floor($number * 0.0000001), '.', -1, 0) . "亿";
    } elseif ($length > 4) { //万单位
        //截取前俩为
        $str = floor($number * 0.001) * 0.1 . "万";
    } else {
        return $number;
    }
    return $str;
}

/**
 * 二维数组根据某个字段排序
 * @param array $array 要排序的数组
 * @param string $keys 要排序的键字段
 * @param string $sort 排序类型  SORT_ASC     SORT_DESC
 * @return array 排序后的数组
 */
function arraySort(array $array, string $keys, string $sort = SORT_DESC): array
{
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}

/**
 * 二分查找法
 * @param int $num 数量
 * @param array $filter 对应集合
 * @return array
 */
function priceSearch(int $num, array $filter)
{
    if (count($filter) == 1) {
        return $filter;
    }
    $half = floor(count($filter) / 2); // 取出中间数

    // 判断数量在哪个区间
    if ($num < $filter[$half]['number']) {
        $filter = array_slice($filter, 0, $half);
    } else {
        $filter = array_slice($filter, $half, count($filter));
    }
    //print_r($filter);
    // 继续递归直到只剩一个元素
    if (count($filter) > 1) {
        $filter = priceSearch($num, $filter);
    }

    return $filter;
}

/**
 * PDF转图片
 * @param string $file 本地文件路径
 * @return array
 * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
 * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist
 */
function pdfToImg(string $file): array
{
    if (config('filesystems.default') != 'public') {
        $pdfPath_oss = Storage::disk(config('filesystems.default'))->url($file); // OSS 存储路径
        $to_local = Storage::disk('public')->put($file, (new Client())->get($pdfPath_oss)->getBody()); // 存储到本地
        $pdfPath = storage_path('app/public/') . $file; // 在本地磁盘文件实际路径
        sleep(5); // 暂停 N 秒  等待文件下载完毕
    } else {
        $pdfPath = public_path('uploads/') . $file; // 在本地磁盘文件实际路径
    }
    if (Storage::disk('public')->exists($file)) {
        $PdfToImage = new \Spatie\PdfToImage\Pdf($pdfPath);
        $pages = $PdfToImage->getNumberOfPages(); // 获取 PDF 总页数
        $fullPath = Str::uuid(); // 随机图片前缀名
        $imgs = [];
        // 循环生成图片存储
        for ($i = 1; $i <= $pages; $i++) {
            $file_name = $i . '.png';
            $path = 'pdftoimg/' . $fullPath . '_' . $file_name; // 最终文件相对存储路径
            $img = $PdfToImage->setPage($i)->getImageData($path); // 获取图片
            Storage::disk(config('filesystems.default'))->put($path, $img); // 图片存储到本地磁盘或者 OSS
            $imgs[] = $path;

            Storage::disk('public')->delete($file); // 删除本地缓存文件
            return $imgs;//图片地址数组
        }
    }
}

/**
 * 格式化文件大小
 * @param int $filesize 字节
 * @return string
 */
function getFileSize(int $filesize): string
{
    if ($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
    } elseif ($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
    } elseif ($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
    } else {
        $filesize = $filesize . ' 字节';
    }

    return $filesize;
}

function getLatByTencentMap($address): array
{
    $url = 'https://apis.map.qq.com/ws/geocoder/v1/?address=' . $address . '&key=JCQBZ-DOW3Z-H4QXJ-7IVIV-LHKPQ-VPBZB';
    $result = file_get_contents($url);
    $data = json_decode($result, TRUE);

    $res['lng'] = $data['result']['location']['lng'];
    $res['lat'] = $data['result']['location']['lat'];
    return $res;
}

/**
 * 替换图片域名
 * @param $string
 * @return string|string[]
 */
function replace_domain($string): array|string
{
    $domain = config('app.url').'/storage/';

    return str_ireplace($domain, '', $string);
}
