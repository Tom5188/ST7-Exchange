<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanMarketKine extends Command
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'clean:market-kine';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '按 period 和 symbol 分块清理 market_kine 表，保留每个组合的前 2000 条记录';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        // 获取所有 period 和 symbol 的组合
        $groups = DB::table('market_kine')
            ->select('period', 'symbol')
            ->groupBy('period', 'symbol')
            ->get();
        
        foreach ($groups as $group) {
            $period = $group->period;
            $symbol = $group->symbol;
        
            $this->info("正在清理 period: {$period}, symbol: {$symbol} 的数据...");
        
            // 找到需要删除的记录的 ID
            $idsToDelete = DB::table('market_kine')
                ->select('id')
                ->where('period', $period)
                ->where('symbol', $symbol)
                ->orderBy('id', 'desc')
                ->offset(2000) // 跳过前 2000 条
                ->limit(PHP_INT_MAX) // 确保获取所有需要删除的记录
                ->pluck('id');
        
            // 如果没有需要删除的记录，则跳过
            if ($idsToDelete->isEmpty()) {
                $this->info("没有需要删除的记录。");
                continue;
            }
        
            // 删除记录
            $deletedRows = DB::table('market_kine')
                ->whereIn('id', $idsToDelete)
                ->delete();
        
            $this->info("删除了 {$deletedRows} 条记录。");
        }
        
        $this->info('market_kine 表清理完成。');
        return 0;
    }
}