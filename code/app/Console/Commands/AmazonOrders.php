<?php

namespace App\Console\Commands;

use App\AmazonMWS\MarketplaceWebServiceOrders\MarketplaceWebServiceOrders_Client;
use App\Models\AmazonServices;
use App\Models\SettingShops;
use Illuminate\Console\Command;

class AmazonOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'OrdersTask:AmazonOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '亚马逊抓单任务';

    //订单服务API
    public $API_Action = 'Orders';

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
        $shopInfo = SettingShops::getShopInfoByType(SettingShops::PLAT_AMAZON);
        if ($shopInfo->isEmpty())
        {
            //无店铺信息
            echo "无有效亚马逊店铺信息". "\r\n";
            return false;
        }
        $shopInfo = $shopInfo->toArray();
        $shopIds = array_column($shopInfo,'id');
        $shopData ['shopIds'] = $shopIds;
        $shopData ['shopInfo'] = $shopInfo;
        $shopData ['failData'] ['status'] = SettingShops::SHOP_STATUS_POWER_FAILED;
        $shopData ['failData'] ['remark'] = '店铺授权失败,请重新授权';
        foreach ($shopInfo as $value) {
            $value['API_Action'] = $this->API_Action;
            $value['sellerId'] = $value ['seller_id'];
            //订单API版本号
            $value['APP_Version'] = MarketplaceWebServiceOrders_Client::SERVICE_VERSION;
            $stationInfo = SettingShops::AMAZON_STATION[$value['open_state']];
            $sale_info = array_merge($value,$stationInfo);
            $amazonService = new AmazonServices($sale_info);
            $start_time = gmdate("Y-m-d\TH:i:s\Z", strtotime("-30 day"));
            $shipType = 'MFN';
//            $orderStatus = ['Unshipped','PartiallyShipped'];
            $orderStatus = ['Unshipped'];
//            $orderStatus = [];
//            $orderList = $amazonService->getOrderItemData('249-0296112-1574272');
            $amazonService->getOrderData($start_time,$shopData, $shipType, $orderStatus);
        }
    }
}
