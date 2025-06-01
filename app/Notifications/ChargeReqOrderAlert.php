<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\URL;

class ChargeReqOrderAlert extends Notification
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
            ->message($this->account . '的充币订单待处理')
            ->icon('cash')
            ->type('info')
            ->action('查看充币订单', URL::remote('/admin/resources/charge-reqs/'.$this->orderid));
    }
}