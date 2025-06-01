<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\ProjectController;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class ProjectOrder extends Command
{
	protected $signature = "project_interest";
	
	protected $description = "理财结算";
	
	public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = app(ProjectController::class);
        
        $response = $controller->projectSettlement();
        
        $this->info($response);
    }
}
