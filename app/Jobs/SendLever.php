<?php

namespace App\Jobs;

use App\Http\Controllers\Api\LeverController;
use App\Models\UserChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLever implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        echo '处理合约' . PHP_EOL;
        $lever = new LeverController();
        $info = $lever->queueMyTrade(1085842);
        UserChat::sendText($info);
    }
}
