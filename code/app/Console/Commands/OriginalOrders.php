<?php

namespace App\Console\Commands;

use App\AmazonMWS\MarketplaceWebServiceOrders\MarketplaceWebServiceOrders_Client;
use App\Models\AmazonServices;
use App\Models\OrdersOriginal;
use App\Models\SettingShops;
use Illuminate\Console\Command;

class OriginalOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'OrdersTask:OriginalOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '原始订单匹配任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ignore_user_abort(true);
        set_time_limit (0);
        date_default_timezone_set('PRC');//设置时间为中国时区
        exec("chcp 65001");//设置命令行窗口为中文编码
        echo $this->description.'开始'. "\r\n";
        OrdersOriginal::originalOrdersMappingLogic();
        echo $this->description.'结束'. "\r\n";
    }
}
