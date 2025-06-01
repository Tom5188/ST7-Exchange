<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\MailMessage;
use App\Models\MailMessageUser;
use App\Models\Users;
use App\Models\UserChat;

class MailMessageController extends Controller
{
    public function getCount(Request $request)
    {
        $user_id = $request->user()->id;
        $count = Db::table('mail_message')
            ->join('mail_message_user', 'mail_message.id', '=', 'mail_message_user.mail_message_id')
            ->where('mail_message_user.user_id', $user_id)
            ->where(function ($query) use ($user_id) {
                $query->whereRaw('JSON_CONTAINS(mail_message.user_id, ?)', ['"'.$user_id.'"'])
                      ->orWhereRaw('JSON_CONTAINS(mail_message.user_id, ?)', ['"0"']);
            })
            ->selectRaw('count(1) as count')
            ->first();
        $list_count = Db::table('mail_message')
            ->where(function ($query) use ($user_id) {
                $query->whereRaw('JSON_CONTAINS(mail_message.user_id, ?)', ['"'.$user_id.'"'])
                      ->orWhereRaw('JSON_CONTAINS(mail_message.user_id, ?)', ['"0"']);
            })
            ->selectRaw('count(1) as count')
            ->first();
        
        $result = $list_count->count - $count->count;
        return $this->success($result);
    }
    public function getList(Request $request)
    {
        $user_id = $request->user()->id;
    
        // 获取该用户应看到的所有消息（user_id 包含当前用户或 0）
        $list = MailMessage::where(function ($query) use ($user_id) {
                $query->whereRaw('JSON_CONTAINS(user_id, ?)', ['"'.$user_id.'"'])
                      ->orWhereRaw('JSON_CONTAINS(user_id, ?)', ['"0"']);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    
        // 获取用户已读的 message_id 列表（一次性查出）
        $readIds = MailMessageUser::where('user_id', $user_id)->pluck('mail_message_id')->toArray();
    
        // 加状态字段（0=未读，1=已读）
        foreach ($list as $key=>$msg) {
            $list[$key]->status = in_array($msg->id, $readIds) ? 1 : 0;
        }
    
        return $this->success($list);
    }

    public function detail(Request $request)
    {
        $user_id = $request->user()->id;
        $id = $request->get('id');
    
        $mailMessage = MailMessage::where('id', $id)->where(function ($query) use ($user_id) {
                $query->whereRaw('JSON_CONTAINS(user_id, ?)', ['"'.$user_id.'"'])
                      ->orWhereRaw('JSON_CONTAINS(user_id, ?)', ['"0"']);
            })->first();
    
        if (empty($mailMessage)) {
            return $this->error("消息不存在");
        }
    
        try {
            // 检查是否已读
            $hasRead = MailMessageUser::where([
                'user_id' => $user_id,
                'mail_message_id' => $mailMessage->id
            ])->exists();
    
            // 如果没读过，记录为已读
            if (! $hasRead) {
                MailMessageUser::create([
                    'user_id' => $user_id,
                    'mail_message_id' => $mailMessage->id
                ]);
            }
    
            return $this->success($mailMessage);
        } catch (\Exception $ex) {
            return $this->error('系统异常：' . $ex->getMessage());
        }
    }
}
