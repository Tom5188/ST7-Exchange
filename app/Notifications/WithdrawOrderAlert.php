<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\URL;

class WithdrawOrderAlert extends Notification
{
    protected $account;
    
    protected $orderid;
    
    public function __construct($account, $orderid)
    {
        $this->account = $account;
        $this->orderid = $orderid;
    }
    
    public function via($notifiable)
    {
        return [NovaChannel::class];
    }

    public function toNova($notifiable)
    {
        return NovaNotification::make()
            ->message($this->account . '的提现申请待处理')
            ->icon('cash')
            ->type('info')
            ->action('查看提现申请', URL::remote('/admin/resources/users-wallet-outs/'.$this->orderid));
    }
}
