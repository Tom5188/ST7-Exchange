<?php

namespace App\Console\Commands;

use App\Models\AccountLog;
use App\Models\Currency;
use App\Models\Level;
use App\Models\Users;
use App\Models\UsersWallet;
use App\Models\Setting;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class Test extends Command
{
    protected $signature = "Testtest";
    protected $description = "测试";
    public function handle()
    {
        $this->comment("start");
        Users::rebate(357, 357, 3, 100, 1, 2);
        $this->comment("end");
    }
}
?>
