<?php

namespace App\Console\Commands;

use App\Models\LogHelper;
use App\Models\OrdersInvoices;
use App\Models\SettingShops;
use Illuminate\Console\Command;

class UpdateShippingLogics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateShippingLogics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '物流跟踪号回传';

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

        $platArr = [SettingShops::PLAT_AMAZON=>'亚马逊',SettingShops::PLAT_RAKUTEN=>'乐天'];
        try {
            foreach ($platArr as $key => $value ) {
                $shopInfo = SettingShops::getShopInfoByType($key);
                if ($shopInfo->isEmpty())
                {
                    //无店铺信息
                    echo "无有效".$value."店铺信息". "\r\n";
                    continue;
                }
                (new OrdersInvoices ())->updateShippingLogics($key);
                echo $value."物流跟踪号回传结束". "\r\n";
            }
        } catch (\Exception $e) {
            $exception_data = [
                'start_time'                => date('Y-m-d H:i:s'),
                'msg'                       => '失败信息：' . $e->getMessage(),
                'line'                      => '失败行数：' . $e->getLine(),
                'file'                      => '失败文件：' . $e->getFile(),
            ];
            LogHelper::setExceptionLog($exception_data,$this->signature);
        }
    }
}
