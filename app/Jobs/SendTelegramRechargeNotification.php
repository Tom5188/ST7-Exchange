<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramRechargeNotification
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $message;
    protected ?string $photo;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = config('services.telegram.bot_token');
        $chat_id = config('services.telegram.chat_id');

        if (!$token || !$chat_id) {
            \Log::warning('Telegram token/chat_id 未配置，消息未发送');
            return;
        }
        //https://api.telegram.org/bot{$token}/getUpdates 获取ChatID
        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chat_id,
            'text' => $this->message,
            'parse_mode' => 'HTML',
        ]);
    }
}
