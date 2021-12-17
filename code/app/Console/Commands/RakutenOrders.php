<?php

namespace App\Console\Commands;

use App\Models\OrdersRakuten;
use App\Models\OrdersRakutenTemp;
use App\Models\RakutenService;
use App\Models\SettingShops;
use Illuminate\Console\Command;

class RakutenOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'OrdersTask:RakutenOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '乐天抓单';
    /**
     * @var array 订单数组 saleInfo销售信息 orderList订单号
     *   "2_18" => array:2 [▼
    "saleInfo" => array:4 [▼
    "appKey" => "SP365811_uxiIpmu3wAtyIvqx"
    "appSecret" => "SL365811_EC5VbBIQnjHSOeV5"
    "user_id" => 2
    "id" => 18
    ]
    "orderList" => array:48 [▼
    0 => "365811-20190410-00002701"
     * ]]
     */
    public $orderList = [];


    public $listOrderInfos = [];

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
        //V.1 获取店铺信息
        $shopInfo = SettingShops::getShopInfoByType(SettingShops::PLAT_RAKUTEN);

        if ($shopInfo->isEmpty())
        {
            //无店铺信息
            echo "无有效乐天店铺信息". "\r\n";
            return false;
        }
        $shopInfo = $shopInfo->toArray();
        //遍历店铺信息
        //最近一个月
        $startDateTime =  gmdate('Y-m-d\TH:i:s+0900', strtotime('-1 month'));
        $crrentDateTime = gmdate('Y-m-d\TH:i:s+0900',time());
        $params ['dateType'] = 1;
        $params ['startDatetime'] = $startDateTime;
        $params ['endDatetime'] = $crrentDateTime;
        //需求变更 只要300
//        $params ['orderProgressList'] = [300,500,800,900];
        $params ['orderProgressList'] = [300];
        $params ['PaginationRequestModel']['requestRecordsAmount'] = 100;
        $params ['PaginationRequestModel']['requestPage'] = 1;
        $paramsData = json_encode($params);
        $powerFailData ['status'] = SettingShops::SHOP_STATUS_POWER_FAILED;
        $powerFailData ['remark'] = '店铺授权失败,请重新授权';
        //订单号 searchOrder
        foreach ($shopInfo as $value) {
                $sale_info = [];
                $sale_info['appKey'] = $value['service_secret'];
                $sale_info['appSecret'] = $value['license_key'];
                $sale_info['user_id'] = $value['user_id'];
                $sale_info['id'] = $value['id'];
                $sale_info['shop_name'] = $value['shop_name'];
                $rakuten = new RakutenService($sale_info);
                //下单时间最近一个月、各平台定义状态的订单；
                $ordersInfo = $rakuten->searchOrder($paramsData);

                //httpcode 不为200将未false
                if (empty($ordersInfo) || (isset($ordersInfo ['orderNumberList']) && empty($ordersInfo['orderNumberList']))) {
                    //请求失败
                    SettingShops::postShopData($sale_info['id'],$powerFailData);
                    continue;
                }
                //可能涉及翻页

                //翻页信息
                $totalPage = $ordersInfo ['PaginationResponseModel']['totalPages'];
                $currrntPage = $params ['PaginationRequestModel']['requestPage'];
//                $totalRecordsAmount = $ordersInfo ['PaginationResponseModel']['totalRecordsAmount'];
                //默认30个
                //一个客户可能会有多个乐天店铺
                $orderNumberList = $ordersInfo ['orderNumberList'];
                $this->orderList ["{$sale_info['user_id']}_{$sale_info['id']}"] ['saleInfo'] = $sale_info ;
                $this->orderList ["{$sale_info['user_id']}_{$sale_info['id']}"] ['orderList'] = $orderNumberList ;
                //没下一页
                if ($currrntPage == $totalPage) {
                        continue;
                }

                //第二页开始
                for ($i = 2;$i <= $totalPage;$i++) {
                    $ordersInfo = [];
                    $params ['PaginationRequestModel']['requestPage'] = $i;
                    $pagingParam = json_encode($params);
                    $ordersInfo = $rakuten->searchOrder($pagingParam);
                    $this->orderList ["{$sale_info['user_id']}_{$sale_info['id']}"] ['orderList'] = array_merge($this->orderList ["{$sale_info['user_id']}_{$sale_info['id']}"] ['orderList'],$ordersInfo ['orderNumberList']);
                }
        }
        //订单详情 getOrder
        foreach ($this->orderList as $listKey => $listValue) {
            if (empty($listValue ['orderList'])) {
                continue;
            }
            $listRakuten = new RakutenService($listValue['saleInfo']);
            $listLengthsPage = ceil(count($listValue['orderList']) /100 );
            //默认getOrder最多请求100
            $listI = 0;
            $listTemp = [];
            $this->listOrderInfos[$listKey] ['saleInfo'] = $listValue['saleInfo'];
            for ($listI ; $listI < $listLengthsPage ; $listI ++) {
                $listTemp = array_slice($listValue['orderList'],100*$listI, 100);
                $listOrderInfo = $listRakuten->getOrder($listTemp);
                if (empty($listOrderInfo) && (isset($listOrderInfo ['OrderModelList']) && empty($listOrderInfo['OrderModelList']))) {
                    continue;
                }
                if (isset($this->listOrderInfos[$listKey] ['orderInfo'])) {
                    $this->listOrderInfos[$listKey] ['orderInfo'] = array_merge($this->listOrderInfos[$listKey] ['orderInfo'],$listOrderInfo['OrderModelList']);
                } else {
                    $this->listOrderInfos[$listKey] ['orderInfo'] = $listOrderInfo['OrderModelList'];
                }
            }
        }

        if (empty($this->listOrderInfos)) {
            //无店铺信息
            echo "无订单信息". "\r\n";
            return false;
        }

        $ordersRakuten = new OrdersRakuten();
        //数据处理 写入临时表
        $ordersRakuten->orderDataLogics($this->listOrderInfos,$shopInfo);
        //临时表数据跑匹配规则
        //匹配成功生成相关数据
        //数据处理完成之后 truncate 临时表
   }
}
