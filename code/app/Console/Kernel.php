<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\getSettingExchange::class,
        Commands\GetAsnList::class,
        Commands\RakutenOrders::class,
        Commands\AmazonOrders::class,
        Commands\UpdateWarehouse::class,
        Commands\getLogicByWareCode::class,
        Commands\OrderRulesProcessing::class,
        Commands\GetmergeRulesOrder::class,
        Commands\UpdateShipmentProcessing::class,
        Commands\UpdateDistributionRatioProcessing::class,
        Commands\CheckWareHouseGoods::class,
        Commands\GetProductCategory::class,
        Commands\AmazonRequestTask::class,
        Commands\AmazonResponseTask::class,
        Commands\OriginalOrders::class,
        Commands\UpdateShippingLogics::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        exec("chcp 65001");//设置命令行窗口为中文编码
        $schedule->command('command:UpdateDistributionRatioProcessing')->everyMinute()->runInBackground();//zt8067 更新商品配货比 每分钟执行一次任务
        $schedule->command('command:UpdateShipmentProcessing')->everyMinute()->runInBackground();//zt8067 发货状态	每分钟执行一次任务
        $schedule->command('command:OrderRulesProcessing')->everyFiveMinutes()->runInBackground();//zt8067 规则 每五分钟执行一次任务
        $schedule->command('command:GetAsnList')->hourly()->runInBackground();//zt7785 入库单同步任务
        $schedule->command('command:UpdateWarehouse')->daily()->runInBackground();//zt12779 更新仓库，每天运行一次
        $schedule->command('command:CheckWareHouseGoods')->everyFiveMinutes()->runInBackground();//商品同步 每五分钟执行一次
        $schedule->command('command:GetProductCategory')->monthly()->runInBackground();//速贸商品分类同步 每月运行一次
        $schedule->command('command:getSettingExchange')->dailyAt('9:00')->runInBackground();//更新汇率每天9点运行一次
        //载单任务
        $schedule->command('OrdersTask:AmazonOrders')->hourly()->runInBackground();//亚马逊载单
        $schedule->command('OrdersTask:RakutenOrders')->hourly()->runInBackground();//乐天载单
        $schedule->command('OrdersTask:OriginalOrders')->everyFiveMinutes()->runInBackground();//原始订单匹配任务
        $schedule->command('UpdateShippingLogics')->everyThirtyMinutes()->runInBackground();//物流跟踪号回传
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
