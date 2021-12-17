<?php

namespace App\Models;

use App\AmazonMWS\GithubMWS\MCS\MWSClient;
use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OrdersInvoices
 * Notes: 订单配货单
 * @package App\Models
 * Data: 2019/3/7 14:30
 * Author: zt7785
 */
class OrdersInvoices extends Model
{
    const ENABLE_INVOICES_STATUS = 1;//未作废
    const DISABLED_INVOICES_STATUS = 2;//已作废
    const DEL_INVOICES_STATUS = 3;//已删除

    const UN_SYNC_STATUS = 1;//未同步
    const ALREADY_SYNC_STATUS = 1;//已同步
    const ERR_SYNC_STATUS = 1;//同步失败

    const NO_PROBLEM = 0;//没问题
    const PARTIAL_SHORTAGE_PROBLEM = 1;//部分缺货
    const OVERWEIGHT_SHORTAGE_PROBLEM = 2;//超重缺货
    const UNABLE_WAREHOUSE_PROBLEM = 3;//无法找到仓库
    const UNABLE_LOGISTICS_PROBLEM = 4;//无法找到仓库

    /**
     * @var 未拦截
     */
    const UNINTERCEPT = 1;
    /**
     * @var 拦截中
     */
    const INTERCEPTING = 2;
    /**
     * @var 拦截成功
     */
    const INTERCEPT_SUCC = 3;
    /**
     * @var 拦截失败
     */
    const INTERCEPT_FAIL = 4;

    const ORDER_INTERCEPT_STATUS_INITIAL = 1;//单拦截状态未拦截初始状态

    const ORDER_INTERCEPT_STATUS_INTERCEPTING = 2;//订单拦截状态拦截中

    const ORDER_INTERCEPT_STATUS_INTERCEPTED = 3;//订单拦截状态拦截成功

    const ORDER_INTERCEPT_STATUS_FAILED = 4;//订单拦截状态拦截失败

    const DELIVERY_STATUS_NO = 1;//未发货

    /**
     * @var 已发货
     */
    const DELIVERY_STATUS_YES = 2;//已发货

    /**
     * @var 未回传
     */
    const PASS_BACK_STATUS_INIT = 0;

    /**
     * @var 回传请求中
     */
    const PASS_BACK_STATUS_REQUEST = 1;

    /**
     * @var 回传成功
     */
    const PASS_BACK_STATUS_SUCC = 2;

    /**
     * @var 回传失败
     */
    const PASS_BACK_STATUS_FAIL = 3;


    const STATE_NO = 1;//未更新

    const STATE_YES = 2;//已更新

    /**
     * @var array 物流跟踪号list 数据
     */
    public $trackingList = [
            Platforms::AMAZON => [] ,
            Platforms::RAKUTEN => [] ,
        ];
    /**
     * @var array Api请求数据
     */
    public $amazonServiceApiData = [];

    protected $table = 'orders_invoices';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $MarketplaceIds = [
        'A2EUQ1WTGCTBG2' => 'mws.amazonservices.ca',
        'ATVPDKIKX0DER' => 'mws.amazonservices.com',
        'A1AM78C64UM0Y8' => 'mws.amazonservices.com.mx',
        'A1PA6795UKMFR9' => 'mws-eu.amazonservices.com',
        'A1RKKUPIHCS9HS' => 'mws-eu.amazonservices.com',
        'A13V1IB3VIYZZH' => 'mws-eu.amazonservices.com',
        'A21TJRUUN4KGV' => 'mws.amazonservices.in',
        'APJ6JRA9NG5V4' => 'mws-eu.amazonservices.com',
        'A1F83G8C2ARO7P' => 'mws-eu.amazonservices.com',
        'A1VC38T7YXB528' => 'mws.amazonservices.jp',
        'AAHKV2X7AFYLW' => 'mws.amazonservices.com.cn',
        'A39IBJ37TRP1C6' => 'mws.amazonservices.com.au',
        'A2Q3Y263D00KWC' => 'mws.amazonservices.com'
    ];

    public $exceptionAPI = 'SubmitFeed';

    //2019年3月23日15:48:43 delivered_at 发货时间

    public $fillable = ["id","type","created_man","user_id","order_id","platforms_id","source_shop","logistics_id","logistics_way","warehouse_id","warehouse","invoices_number","warehouse_order_number","tracking_no","taotla_value","currency_code","rate","intercept_status","delivery_status","invoices_status","sync_status","state","sync_number","ware_number","delivered_at","created_at","updated_at",'pass_back_status','pass_back_count','pass_back_fail_info'];

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 订单表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function Orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 物流表
     * Data: 2019/3/11 18:23
     * Author: zt7785
     */
    public function SettingLogistics()
    {
        return $this->belongsTo(SettingLogistics::class, 'logistics_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 仓库表
     * Data: 2019/3/11 18:23
     * Author: zt7785
     */
    public function SettingWarehouse()
    {
        return $this->belongsTo(SettingWarehouse::class, 'warehouse_id', 'id');
    }

    public static function postGoods($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 配货单商品表
     * Data: 2019/3/22 16:23
     * Author: zt8076
     */

    public function OrdersInvoicesProduct(){
        return $this->hasMany(OrdersInvoicesProducts::class,'invoice_id','id');

    }

    /**
     * @return $this
     * Note: 物流映射信息
     * Data: 2019/6/14 13:30
     * Author: zt7785
     */
    public function SettingLogisticsMapping(){
        return $this->hasMany(SettingLogisticsMapping::class,'logistic_id','logistics_id')->where('is_deleted',SettingLogisticsMapping::UN_DELETED);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 平台
     * Data: 2019/3/22 18:23
     * Author: zt8076
     */
    public function Platforms(){

        return $this->belongsTo(Platforms::class,'platforms_id','id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 平台
     * Data: 2019/3/22 18:23
     * Author: zt8076
     */
    public function SettingShops(){

        return $this->belongsTo(SettingShops::class,'source_shop','id');
    }

    /**
     * @param $order_id
     * @return array
     * Note: 获取已发货的配货单
     * Data: 2019/4/11 16:57
     * Author: zt7785
     */
    public static function getInvoiceByOrderId($order_id)
    {
        $collection = self::with(['OrdersInvoicesProduct'=>function ($query) {
            $query->select('id','goods_id','sku','invoice_id','buy_number','already_stocked_number','cargo_distribution_number');
        }]);
        return $collection->where('tracking_no','!=','')->where('order_id',$order_id)->get(['id','warehouse_order_number','tracking_no','intercept_status','invoices_status'])->toArray();
    }

    /**
     * @param $order_id
     * @return array
     * Note: 获取部分配货商品id
     * Data: 2019/6/27 20:25
     * Author: zt7785
     */
    public static function getPickGoodsByOrderId($order_id)
    {
        $collection = self::with(['OrdersInvoicesProduct'=>function ($query) {
            $query->select('id','goods_id','sku','invoice_id','buy_number','already_stocked_number','cargo_distribution_number');
        }]);
        return $collection->where('order_id',$order_id)->get(['id','warehouse_order_number','tracking_no','intercept_status','invoices_status'])->toArray();
    }

    /**
     * Note: 物流跟踪号回传任务
     * Data: 2019/6/12 15:41
     * Author: zt7785
     */
    public  function updateShippingLogics($plat_id = 0)
    {
        //合并而成的订单物流跟踪号回传 逻辑上有问题
        $orderInvCollection = self::with ('Orders','OrdersInvoicesProduct','SettingShops','SettingLogisticsMapping');
        $orderInvCollection->whereHas('Orders',function ($query) use ($plat_id) {
            $query->where('type',Orders::ORDER_FROM_TYPE_API);
            //非合并订单
            $query->where('merge_orders_id','');
            $plat_id && $query->where('platforms_id',$plat_id);
        });
        $plat_id && $orderInvCollection->whereHas('SettingLogisticsMapping',function ($query) use ($plat_id) {
            $query->where('plat_id',$plat_id);
        });
        $orderInvCollection->where(
            [
                ['tracking_no','!=',''],
                ['delivery_status',self::DELIVERY_STATUS_YES],
                //失败次数小于十次
                ['pass_back_count','<','10'],
            ]
        //未回传或者回传失败
        )->whereIn('pass_back_status',[self::PASS_BACK_STATUS_INIT,self::PASS_BACK_STATUS_FAIL]);

         $orderInvInfos = $orderInvCollection->select('id','tracking_no','delivery_status','pass_back_count','delivered_at','order_id','source_shop','user_id','logistics_way','logistics_id','pass_back_count')->get();

         if ($orderInvInfos->isEmpty()) {
             return false;
         }

        $orderInvInfos = $orderInvInfos ->toArray();
         try {
             //数据处理
            foreach ($orderInvInfos as $orderInvInfoKey => $orderInvInfoVal) {
                    if (empty($orderInvInfoVal ['orders'])) {
                        continue;
                    }
                    //无映射关系 直接跳过
                    if (empty($orderInvInfoVal ['setting_logistics_mapping'])) {
                        continue;
                    }
                    if ($plat_id > 0 ) {
                        $logistics_mapping_info_plat_id = array_column($orderInvInfoVal ['setting_logistics_mapping'],'plat_id');
                        $logistics_mapping_info_plat_key = array_search($plat_id,$logistics_mapping_info_plat_id);
                        if (is_bool($logistics_mapping_info_plat_key)) {
                            continue;
                        }
                    }
                    if ($plat_id == Platforms::AMAZON) {
                    if (!isset($this->trackingList [Platforms::AMAZON] [$orderInvInfoVal['user_id'].'_'.$orderInvInfoVal['source_shop']])) {
                        //用于初始化亚马逊客户请求类
                        $cusSaleInfo ['MarketplaceId'] = $orderInvInfoVal ['setting_shops']['Marketplace_Id'];
                        $cusSaleInfo ['Amazon_MWS_Endpoint'] = 'https://' .$this->MarketplaceIds [$cusSaleInfo ['MarketplaceId']];
                        $cusSaleInfo ['sellerId'] = $orderInvInfoVal ['setting_shops'] ['seller_id'];
                        $cusSaleInfo ['license_key'] = $orderInvInfoVal ['setting_shops'] ['license_key'];
                        $cusSaleInfo ['service_secret'] = $orderInvInfoVal ['setting_shops'] ['service_secret'];
                        $cusSaleInfo ['user_id'] = $orderInvInfoVal['user_id'];
                        $cusSaleInfo ['shop_name'] = $orderInvInfoVal ['setting_shops'] ['shop_name'];
                        $cusSaleInfo ['shop_id'] = $orderInvInfoVal ['setting_shops'] ['id'];

                        $cusSaleInfo ['Marketplace_Id'] = $orderInvInfoVal ['setting_shops']['Marketplace_Id'];
                        $cusSaleInfo ['Amazon_MWS_Endpoint'] = 'https://' .$this->MarketplaceIds [$cusSaleInfo ['MarketplaceId']];
                        $cusSaleInfo ['Seller_Id'] = $orderInvInfoVal ['setting_shops'] ['seller_id'];
                        $cusSaleInfo ['Access_Key_ID'] = $orderInvInfoVal ['setting_shops'] ['license_key'];
                        $cusSaleInfo ['Secret_Access_Key'] = $orderInvInfoVal ['setting_shops'] ['service_secret'];
                        $cusSaleInfo ['user_id'] = $orderInvInfoVal['user_id'];
                        $cusSaleInfo ['Application_Name'] = $orderInvInfoVal ['setting_shops'] ['shop_name'];

                        $this->trackingList [Platforms::AMAZON] [$orderInvInfoVal['user_id'].'_'.$orderInvInfoVal['source_shop']]['sale_info'] = $cusSaleInfo;
                    }
                    //物流方式
//                    $platform_express = !empty($orderInvInfoVal ['logistics_way']) ? $orderInvInfoVal ['logistics_way'] : 'other';

                    //处理待回传物流商品数据
                    $fulfillmentData['plat_order_number'] = $orderInvInfoVal ['orders'] ['plat_order_number'];
                    $fulfillmentData['logistics_time'] = $orderInvInfoVal ['delivered_at'];
                    $fulfillmentData['plat_logistic_name'] = $orderInvInfoVal ['setting_logistics_mapping'] [$logistics_mapping_info_plat_key] ['plat_logistic_name'];
                    $fulfillmentData['carrier_name'] = $orderInvInfoVal ['setting_logistics_mapping'] [$logistics_mapping_info_plat_key] ['carrier_name'];
                    $fulfillmentData['tracking_no'] = $orderInvInfoVal ['tracking_no'];
                    $itemList = [] ;
                    foreach ($orderInvInfoVal ['orders_invoices_product'] as $orders_invoice_products){
                        $itemProduct = [];
                        $itemProduct['OrderItemId'] = $orders_invoice_products['AmazonOrderItemCode'];
                        $itemProduct['quantity'] = $orders_invoice_products['cargo_distribution_number'];
                        $itemList[] = $itemProduct;
                    }
                    $logistics_info ['logistics_info'] =  $fulfillmentData;
                    $logistics_info ['invoice_id'] =  $orderInvInfoVal ['id'];
                    $logistics_info ['pass_back_count'] =  $orderInvInfoVal ['pass_back_count'];
                    $logistics_info ['item'] =  $itemList;
                    $this->trackingList [Platforms::AMAZON] [$orderInvInfoVal['user_id'].'_'.$orderInvInfoVal['source_shop']]['list'][] = $logistics_info;
                } else if ($plat_id == Platforms::RAKUTEN){
                        if (!isset($this->trackingList [Platforms::RAKUTEN] [$orderInvInfoVal['user_id'].'_'.$orderInvInfoVal['source_shop']])) {
                            //用于初始化乐天客户请求类
                            $cusSaleInfo ['id'] = $orderInvInfoVal ['setting_shops'] ['id'];
                            $cusSaleInfo ['appSecret'] = $orderInvInfoVal ['setting_shops'] ['license_key'];
                            $cusSaleInfo ['appKey'] = $orderInvInfoVal ['setting_shops'] ['service_secret'];
                            $cusSaleInfo ['user_id'] = $orderInvInfoVal['user_id'];
                            $cusSaleInfo ['shop_name'] = $orderInvInfoVal ['setting_shops'] ['shop_name'];
                            $this->trackingList [Platforms::RAKUTEN] [$orderInvInfoVal['user_id'].'_'.$orderInvInfoVal['source_shop']]['sale_info'] = $cusSaleInfo;
                        }
                        //处理待回传物流商品数据
                        //订单号
                        $fulfillmentData['orderNumber'] = $orderInvInfoVal ['orders'] ['plat_order_number'];
                        //***
                        $fulfillmentData['basketId'] = $orderInvInfoVal ['delivered_at'];
                        //***
                        $fulfillmentData['deliveryCompany'] = $orderInvInfoVal ['setting_logistics_mapping'] [$logistics_mapping_info_plat_key] ['plat_logistic_name'];
                        //物流跟踪号
                        $fulfillmentData['shippingNumber'] = $orderInvInfoVal ['tracking_no'];
                        //发货时间
                        $fulfillmentData['shippingDate'] = strtotime($orderInvInfoVal ['delivered_at']) > 0 ? substr(date($orderInvInfoVal ['delivered_at']),0,10) : substr(date('Y-m-d H:i:s'),0,10);
                        $this->trackingList [Platforms::RAKUTEN] [$orderInvInfoVal['user_id'].'_'.$orderInvInfoVal['source_shop']]['list'][] = $fulfillmentData;
                }
            }
             //物流跟踪号回传操作
             $amazonServiceApiData = [];
             $current_time = date('Y-m-d H:i:s');
             foreach ($this->trackingList as $plat_key => $itemList) {
                if ($plat_key == Platforms::AMAZON) {
//                    $amazonService = new AmazonServices($itemList ['sale_info'],true);
                    $amazonService = new MWSClient($itemList ['sale_info']);
                    $shippingRequestRes = $amazonService->submitFeedShipping($itemList ['list']);
                    if (isset($shippingRequestRes ['FeedSubmissionId'])) {
                        $amazonServiceApiData ['api_name'] = 'SubmitFeed';
                        $amazonServiceApiData ['RequestId'] = $shippingRequestRes ['RequestId'];
                        $amazonServiceApiData ['FeedSubmissionId'] = $shippingRequestRes ['FeedSubmissionId'];
                        $amazonServiceApiData ['FeedType'] = $shippingRequestRes ['FeedType'];
                        $amazonServiceApiData ['SubmittedDate'] = $shippingRequestRes ['SubmittedDate'];
                        $amazonServiceApiData ['FeedProcessingStatus'] = $shippingRequestRes ['FeedProcessingStatus'];
                        $amazonServiceApiData ['is_finished'] = $this->getFeedStatus($shippingRequestRes ['FeedProcessingStatus']);
                        $amazonServiceApiData ['request_user_id'] = $itemList ['sale_info'] ['user_id'];
                        $amazonServiceApiData ['request_shop_id'] = $itemList ['sale_info'] ['shop_id'];
                        $amazonServiceApiData ['param'] = json_encode(array_merge($itemList ['list'],['FeedType'=>'_POST_ORDER_FULFILLMENT_DATA_']));
                        $amazonServiceApiData ['created_at'] = $amazonServiceApiData ['updated_at'] = $current_time;
                        $this ->amazonServiceApiData [] = $amazonServiceApiData;
                        //更新为回传中
                        self::where('id',$itemList ['list'] ['invoice_id'])->update(['pass_back_status'=>self::PASS_BACK_STATUS_REQUEST,'pass_back_count'=> $itemList ['list'] ['pass_back_count'] + 1]);
                    }
                    /**
                     *   "FeedSubmissionId" => "58466018059"
                    "FeedType" => "_POST_INVENTORY_AVAILABILITY_DATA_"
                    "SubmittedDate" => "2019-06-12T11:20:08+00:00"
                    "FeedProcessingStatus" => "_SUBMITTED_"
                     */

                } else if ($plat_key == Platforms::RAKUTEN) {
                    $rakutenService = new RakutenService($itemList ['sale_info']);
                    $shippingRequestRes = $rakutenService->updateOrderShipping($itemList ['list']);
                    if (isset($shippingRequestRes ['messageCode'])) {
                        if (in_array($shippingRequestRes ['messageCode'],$rakutenService->order_shipping_response_code ['finish'])) {
                            //上传成功
                            self::where('id',$itemList ['list'] ['invoice_id'])->update(['pass_back_status'=>self::PASS_BACK_STATUS_SUCC,'pass_back_count'=> $itemList ['list'] ['pass_back_count'] + 1]);
                        } elseif (in_array($shippingRequestRes ['messageCode'],$rakutenService->order_shipping_response_code ['exception'])) {
                            //上传失败 记录异常
                            self::where('id',$itemList ['list'] ['invoice_id'])->update(['pass_back_status'=>self::PASS_BACK_STATUS_FAIL,'pass_back_count'=> $itemList ['list'] ['pass_back_count'] + 1 ,'pass_back_fail_info'=>$shippingRequestRes ['message']]);
                        }
                    }
                }
             }
             //写feed数据
             if (!empty($this->amazonServiceApiData)) {
                 $len = ceil(count($this->amazonServiceApiData) / 500);
                 $temp = [];
                 $i = 0;
                 for ($len; $i < $len; $i++) {
                     $temp = array_slice($this->amazonServiceApiData, 100 * $i, 500);
                     AmazonApiServiceResponse::insert($temp);
                 }
             }
         } catch (\Exception $e) {
             $exception_data = [
                 'start_time'                => $current_time,
                 'msg'                       => '失败信息：' . $e->getMessage(),
                 'line'                      => '失败行数：' . $e->getLine(),
                 'file'                      => '失败文件：' . $e->getFile(),
             ];
             LogHelper::setExceptionLog($exception_data,$this->exceptionAPI);

             $exception ['type'] = 'task';
             if ($plat_id == 1) {
                 $dingPushData ['task'] = 'Amazon物流跟踪号任务';
             } else {
                 $dingPushData ['task'] = 'Rakuten物流跟踪号任务';
             }
             $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
             $exception ['path'] = __FUNCTION__;
             DingRobotWarn::robot($exception,$dingPushData);
             LogHelper::info($exception_data,null,$exception ['type']);
         }
    }

    /**
     * Note: feed状态转换
     * Data: 2019/6/12 19:29
     * Author: zt7785
     */
    public function getFeedStatus ($FeedProcessingStatus) {
        $finishStatus = ['_DONE_','_CANCELLED_'];
        $middleStatus = ['_SUBMITTED_','_AWAITING_ASYNCHRONOUS_REPLY_','_IN_SAFETY_NET_','_UNCONFIRMED_','_IN_PROGRESS_'];
        if (in_array($FeedProcessingStatus,$middleStatus)) {
            return AmazonApiServiceResponse::UN_FINISHED;
        }
        if (in_array($FeedProcessingStatus,$finishStatus)) {
            return AmazonApiServiceResponse::IS_FINISHED;
        }
    }
}
