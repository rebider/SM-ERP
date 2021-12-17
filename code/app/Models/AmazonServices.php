<?php
namespace App\Models;
use App\AmazonMWS\GithubMWS\MCS\MWSEndPoint;
use App\AmazonMWS\MarketplaceWebService\MarketplaceWebService_Client;
use App\AmazonMWS\MarketplaceWebService\MarketplaceWebService_Interface;
use App\AmazonMWS\MarketplaceWebService\MarketplaceWebService_Mock;
use App\AmazonMWS\MarketplaceWebService\MarketplaceWebService_Exception;
use App\AmazonMWS\MarketplaceWebService\Model\MarketplaceWebService_Model_CancelFeedSubmissionsRequest;
use App\AmazonMWS\MarketplaceWebService\Model\MarketplaceWebService_Model_GetFeedSubmissionListRequest;
use App\AmazonMWS\MarketplaceWebService\Model\MarketplaceWebService_Model_GetFeedSubmissionResultRequest;
use App\AmazonMWS\MarketplaceWebService\Model\MarketplaceWebService_Model_IdList;
use App\AmazonMWS\MarketplaceWebService\Model\MarketplaceWebService_Model_StatusList;
use App\AmazonMWS\MarketplaceWebServiceOrders\MarketplaceWebServiceOrders_Client;
use App\AmazonMWS\MarketplaceWebServiceOrders\MarketplaceWebServiceOrders_Exception;
use App\AmazonMWS\MarketplaceWebServiceOrders\MarketplaceWebServiceOrders_Interface;
use App\AmazonMWS\MarketplaceWebServiceOrders\MarketplaceWebServiceOrders_Mock;
use App\AmazonMWS\MarketplaceWebServiceOrders\Model\MarketplaceWebServiceOrders_Model_GetOrderRequest;
use App\AmazonMWS\MarketplaceWebServiceOrders\Model\MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenRequest;
use App\AmazonMWS\MarketplaceWebServiceOrders\Model\MarketplaceWebServiceOrders_Model_ListOrderItemsRequest;
use App\AmazonMWS\MarketplaceWebServiceOrders\Model\MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest;
use App\AmazonMWS\MarketplaceWebServiceOrders\Model\MarketplaceWebServiceOrders_Model_ListOrdersRequest;
use App\AmazonMWS\MarketplaceWebService\Model\MarketplaceWebService_Model_SubmitFeedRequest;
use Illuminate\Support\Facades\DB;
use Spatie\ArrayToXml\ArrayToXml;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Class AmazonServices
 * Notes: 亚马逊接口类
 * Data: 2019/4/19 14:44
 * Author: zt7785
 */
class AmazonServices {

    /**
     * @var MarketplaceWebServiceOrders_Client|null MWS
     */
    public $MWS_API = null;

    public $mdb = null;
    /*
     * @var 异常类型
     */
    public $exceptionTask = 'orders';

    public $exceptionAPI = 'orders_api';

    public $exceptionItemAPI = 'orders_item';
    /**
     * @var array|string 店铺信息
     */
    public $sale_info = '';

    protected $client = NULL;

    /**
     * @var mixed|string 站点marketID
     */
    public $MarketplaceId = '';

    /**
     * @var array 订单商品信息
     */
    public $orderProductsItem = [];

    public $defaultOrderTotal = [
        'CurrencyCode'=>'JPY',
        'Amount'=>'0.00',
    ];

    /*
     * @var array 待定状态 暂未使用
     * Pending:订单已下达，但未经授权付款。订单尚未准备好发货。请注意，对于OrderType = Standard的订单 ，初始订单状态为Pending。对于订单订单类型 = 预订（在仅JP可用），初始的订单状态是 PendingAvailability，以及订单通入挂起 状态支付授权过程开始时。
     * Canceled:订单取消
     */
    protected static $undetermined_status = ['Pending','Canceled'];
    
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    const SIGNATURE_METHOD = 'HmacSHA256';
    const SIGNATURE_VERSION = '2';
//    const DATE_FORMAT = "Y-m-d\TH:i:s.\\0\\0\\0\\Z";
    //店铺id
    const APPLICATION_NAME = 'ShopName';
    const APPLICATION_VERSION =  '0.0.*';

    private $config = [
        'Seller_Id' => null,
        'Marketplace_Id' => null,
        'Access_Key_ID' => null,
        'Secret_Access_Key' => null,
        'MWSAuthToken' => null,
        'Application_Version' => '0.0.*'
    ];

    private $MarketplaceIds = [
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
    /**
     * AmazonServices constructor.
     */
    public function __construct(array $sale_info = [],$is_feed = false)
    {
        //https://www.amazon.co.jp/sp?_encoding=UTF8&asin=&isAmazonFulfilled=&isCBA=&marketplaceID=A1VC38T7YXB528&orderID=&seller=A4AOJJ0MI7WHO&tab=&vasStoreID=
        /**
         * Importhouse King
         * 发行人： Import House King
        联系电话： +8613106386834
        地址：
        1
        伊势崎市Manotanicho
        群马县
        379-2201
        JP
        管理员名称： Michiko
        店铺名称： Import House King
         */
        if (empty($sale_info)) {
            $sale_info ['license_key'] = Config('common.sumaoTestAmazonSecret.license_key');
            $sale_info ['service_secret'] = Config('common.sumaoTestAmazonSecret.service_secret');
            $sale_info ['sellerId'] = Config('common.sumaoTestAmazonSecret.sellerId');
            $sale_info ['MarketplaceId'] = Config('common.sumaoTestAmazonSecret.MarketplaceId');
            $sale_info ['shop_name'] = Config('common.sumaoTestAmazonSecret.shop_name');
            $sale_info ['id'] = Config('common.sumaoTestAmazonSecret.id');
            $sale_info ['user_id'] = Config('common.sumaoTestAmazonSecret.user_id');
            $sale_info ['plat_id'] = Config('common.sumaoTestAmazonSecret.plat_id');
            $sale_info ['Amazon_MWS_Endpoint'] = Config('common.sumaoTestAmazonSecret.Amazon_MWS_Endpoint');
            $sale_info ['API_Action'] = Config('common.sumaoTestAmazonSecret.API_Action');
            $sale_info ['APP_Version'] = Config('common.sumaoTestAmazonSecret.APP_Version');
        }
        if ($is_feed) {
            $applicationVersion = '2009-01-01';
            $serviceUrl = $sale_info ['Amazon_MWS_Endpoint'];
        } else {
            $applicationVersion = '2013-09-01';
            $serviceUrl = $sale_info ['Amazon_MWS_Endpoint'].'/'.$sale_info ['API_Action'].'/'.$applicationVersion;
        }
        $config = array (
            'ServiceURL' => $serviceUrl,
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'ProxyUsername' => null,
            'ProxyPassword' => null,
            'MaxErrorRetry' => 3,
        );
        $this->sale_info = $sale_info;
        if ($is_feed) {
            $this->MWS_API = new MarketplaceWebService_Client($sale_info['license_key'],$sale_info['service_secret'],$config,$sale_info['shop_name'],$applicationVersion);
            $this->config['Region_Host'] = $this->MarketplaceIds[$this->sale_info['MarketplaceId']];
            $this->config['Region_Url'] = 'https://' . $this->config['Region_Host'];
        } else {
            $this->MWS_API = new MarketplaceWebServiceOrders_Client($sale_info['license_key'],$sale_info['service_secret'],$sale_info['shop_name'],$applicationVersion,$config);
        }
        $this->MarketplaceId = $sale_info['MarketplaceId'];
        $this->mdb = DB::getPdo();
    }


    /**
     * @param string $start_time 创建的订单的日期
     * @param string $shipType 订单类型 MFN 由卖方完成
     * @param array $orderStatus 订单状态
     * @param string $nextToken 下一页信息
     * @return bool
     * Note: 获取订单列表
     * Data: 2019/4/22 17:14
     * Author: zt7785
     * 只抓取Unshipped状态的订单，且“非FBA仓“的订单，
     * 抓取订单的付款时间超过30分钟 ???????
     */
    public function getOrderData($start_time,$shopData, $shipType = 'MFN', $orderStatus = [], $nextToken = '') {
        $current_time = date('Y-m-d H:i:s');
        if ($nextToken) {
            sleep(20);
            $res = $this->listOrdersByNextToken($nextToken,$shopData);
        } else {
            $res = $this->listOrders($start_time, $shipType, $orderStatus);
        }
        if (empty($res['data']))
        {
            if (!empty($res['exception_Info']))
            {
                SettingShops::postShopData($this->sale_info['id'],$shopData ['failData']);
                //todo
                //记录异常信息
                $exception_data = [
                    'start_time'                => $current_time,
                    'request_params'                => $nextToken??json_encode([$shipType,$orderStatus]),
                    'sale_info'                 => '客户店铺信息：'. json_encode($this->sale_info),
                    'msg'                       => '失败信息：' . json_encode($res ['exception_Info']),
                ];
                LogHelper::setExceptionLog($exception_data,$this->exceptionAPI);
                $exception ['type'] = 'api';
                $dingPushData ['task'] = 'Amazon订单接口请求';
                $dingPushData ['message'] = $exception_data ['sale_info']."\n\n".$exception_data ['msg'];
                $exception ['path'] = $nextToken ? 'listOrders' : 'listOrdersByNextToken';
                DingRobotWarn::robot($exception,$dingPushData);
                LogHelper::info($exception_data['request_params'],$res,$exception ['type']);
            }
            return false;
        }

        if (empty($res['data'] ['ListOrdersResult'] ['Orders'] ['Order']))
        {
            //无数据
            return false;
        }
        $orderList    = $res ['data'] ['ListOrdersResult'] ['Orders'] ['Order'];
        $newNextToken = $res ['data'] ['ListOrdersResult'] ['NextToken'] ??'';
        $requestId = $res ['data'] ['ResponseMetadata'] ['RequestId'];
        if (count($orderList) == 0) {
            echo 'No OrderList' . PHP_EOL;
            return false;
        }
        foreach ($orderList as $order) {
            try {
//                $this->saveOrder($order,$requestId,$shopData,$current_time);
                //orders_original 原始表写数据
                $this->saveOrders($order,$requestId,$shopData,$current_time);
            } catch (\Exception $e) {
                $exception_data = [
                    'start_time'                => $current_time,
                    'msg'                       => '失败信息：' . $e->getMessage(),
                    'line'                      => '失败行数：' . $e->getLine(),
                    'file'                      => '失败文件：' . $e->getFile(),
                ];
                LogHelper::setExceptionLog($exception_data,$this->exceptionTask);
                $exception ['type'] = 'task';
                $dingPushData ['task'] = 'Amazon订单信息处理';
                $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
                $exception ['path'] = 'saveOrders';
                DingRobotWarn::robot($exception,$dingPushData);
                LogHelper::info($exception_data,null,$exception ['type']);
            }
        }
        if ($newNextToken) {
            $this->getOrderData($start_time, $shopData,$shipType, [], $newNextToken);
        }
    }

    public function testOrders ($orderList,$shopData) {
        $requestId = 'a7c5ec59-2761-4376-b028-774ad4de2ee2';
        $current_time = date('Y-m-d H:i:s');
        foreach ($orderList as $order) {
            try {
                $this->saveOrder($order,$requestId,$shopData,$current_time);
            } catch (\Exception $e) {
                $exception_data = [
                    'start_time'                => $current_time,
                    'msg'                       => '失败信息：' . $e->getMessage(),
                    'line'                      => '失败行数：' . $e->getLine(),
                    'file'                      => '失败文件：' . $e->getFile(),
                ];
                LogHelper::setExceptionLog($exception_data,$this->exceptionTask);
                $exception ['type'] = 'task';
                $dingPushData ['task'] = 'Amazon订单信息处理';
                $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
                $exception ['path'] = 'saveOrders';
                DingRobotWarn::robot($exception,$dingPushData);
                LogHelper::info($exception_data,null,$exception ['type']);
            }
        }
    }
    /**
     * @param string $start_time Y-m-d\TH:i:s\Z
     * @param string $shipType 订单的履行方式
     * @param array $orderStatus 订单状态
     * @return mixed
     * Note: 订单列表
     * Data: 2019/4/22 17:23
     * Author: zt7785
     * Remark: 亚马逊接口限制
     * 节流
     * 该ListOrders和 ListOrdersByNextToken操作起来分享一个 最大请求限额六个和恢复速度每分钟一个请求。有关限制术语的定义
     * 该PendingAvailability状态仅适用于在日本预购。
     * Unshipped and PartiallyShipped should be used together when filtering by OrderStatus.  You cannot use one and not the other
     */
    public function ListOrders($start_time, $shipType, $orderStatus) {
        $request = new MarketplaceWebServiceOrders_Model_ListOrdersRequest();
        $request->setSellerId($this->sale_info['sellerId']);
//        $request->setCreatedAfter(gmdate("Y-m-d\TH:i:s\Z", strtotime("-90 day")));
        $request->setCreatedAfter($start_time);
        $request->setCreatedBefore(gmdate("Y-m-d\TH:i:s\Z", strtotime("-30 minutes")));
//        $request->setOrderStatus(['Unshipped']);
//        $request->setOrderStatus($orderStatus);
        $request->setFulfillmentChannel($shipType);
//        $request->setMaxResultsPerPage(2);
        $request->setMarketplaceId($this->MarketplaceId);
        $service = $this->invokeGetOrdersData($this->MWS_API, $request,'ListOrders');
        return $service;
    }

    /**
     * @param $nextToken
     * @return mixed
     * Note: 使用NextToken参数返回下一页订单
     * Data: 2019/4/22 17:17
     * Author: zt7785
     */
    public function listOrdersByNextToken($nextToken) {
        $request = new MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest();
        $request->setNextToken($nextToken);
        $request->setSellerId($this->sale_info['sellerId']);
        $service = $this->invokeGetOrdersData($this->MWS_API, $request,'ListOrdersByNextToken');
        return $service;
    }

    /**
     * @param $AmazonOrderId
     * @param string $nextToken
     * @return bool
     * Note: 获取订单商品信息
     * Data: 2019/4/23 11:06
     * Author: zt7785
     */
    public function getOrderItemData($AmazonOrderId,$current_time,$nextToken = '') {
        if ($nextToken) {
            $res = $this->ListOrderItemsByNextToken($nextToken);
        } else {
            $res = $this->listOrderItems($AmazonOrderId);
        }
        if (empty($res['data']))
        {
            if (!empty($res['exception_Info']))
            {
                $exception_data = [
                    'start_time'                => $current_time,
                    'sale_info'                 => '客户店铺信息：'. json_encode($this->sale_info),
                    'msg'                       => '失败信息：' . json_encode($res ['exception_Info']),
                ];
                LogHelper::setExceptionLog($exception_data,$this->exceptionItemAPI);
                $exception ['type'] = 'api';
                $dingPushData ['task'] = 'Amazon订单商品接口请求';
                $dingPushData ['message'] = $exception_data ['sale_info']."\n\n".$exception_data ['msg'];
                $exception ['path'] = 'getOrderItemData';
                DingRobotWarn::robot($exception,$dingPushData);
                LogHelper::info($exception_data,$res,$exception ['type']);
            }
            return false;
        }

        if (empty($res['data'] ['ListOrderItemsResult'] ['OrderItems'] ['OrderItem']))
        {
            //无数据
            return false;
        }
        $itemList    = $res['data'] ['ListOrderItemsResult'] ['OrderItems'] ['OrderItem'];
        $newNextToken = $res ['data'] ['ListOrderItemsResult'] ['NextToken'] ??'';
//        $str = '[{"ASIN":"B07CQCRHBK","SellerSKU":"87024548","OrderItemId":"00629446067286","Title":"MacBook Air\/Pro Retina13\u30a4\u30f3\u30ca\u30fc\u30b1\u30fc\u30b9 \u30ce\u30fc\u30c8\u30d1\u30bd\u30b3\u30f3\u30b1\u30fc\u30b9 \u30d5\u30a7\u30eb\u30c8\u30ab\u30d0\u30fc \u96fb\u6e90\u30dd\u30fc\u30c1\u4ed8\u304d 13\u30a4\u30f3\u30c1\u7528PC\u30d0\u30c3\u30b0 \u30b9\u30ea\u30fc\u30d7\u30b1\u30fc\u30b9 \u8efd\u91cf \u4fdd\u8b77 \u30b7\u30f3\u30d7\u30eb \u901a\u5b66 \u901a\u52e4 \u30b0\u30ec\u30fc","QuantityOrdered":"1","QuantityShipped":"1","ProductInfo":{"NumberOfItems":"1"},"ItemPrice":{"CurrencyCode":"JPY","Amount":"1840.00"},"ShippingPrice":{"CurrencyCode":"JPY","Amount":"0.00"},"ItemTax":{"CurrencyCode":"JPY","Amount":"136.00"},"ShippingTax":{"CurrencyCode":"JPY","Amount":"0.00"},"ShippingDiscount":{"CurrencyCode":"JPY","Amount":"0.00"},"ShippingDiscountTax":{"CurrencyCode":"JPY","Amount":"0.00"},"PromotionDiscount":{"CurrencyCode":"JPY","Amount":"0.00"},"PromotionDiscountTax":{"CurrencyCode":"JPY","Amount":"0.00"},"PromotionIds":[],"IsGift":"false","ConditionNote":"\u3010\u4e88\u7d04\u6ce8\u6587\u3011\u3010yt3\u6708\u4e0b\u65ec\u3011","ConditionId":"New","ConditionSubtypeId":"New","IsTransparency":"false"}]';
//        $itemList = json_decode($str,true);
//        $this->orderProductsItem = $itemList;

        $this->orderProductsItem [] = $itemList;
        if ($newNextToken) {
            $this->getOrderItemData($AmazonOrderId,$current_time,$newNextToken);
        }
    }

    /**
     * @param $AmazonOrderId
     * @return mixed
     * Note: 根据您指定的AmazonOrderId返回订单商品 。
     * Data: 2019/4/23 11:07
     * Author: zt7785
     */
    public function listOrderItems($AmazonOrderId) {
        $request = new MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
        $request->setSellerId($this->sale_info['sellerId']);
        $request->setAmazonOrderId($AmazonOrderId);
        $service = $this->invokeGetOrdersData($this->MWS_API, $request,'ListOrderItems');
        return $service;
    }

    /**
     * @param $NextToken
     * @return mixed
     * Note: 使用NextToken参数返回订单商品的下一页。
     * Data: 2019/4/23 11:07
     * Author: zt7785
     */
    public function ListOrderItemsByNextToken($NextToken) {
        $request = new MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenRequest();
        $request->setNextToken($NextToken);
        $service = $this->invokeGetOrdersData($this->MWS_API, $request,'ListOrderItemsByNextToken');
        return $service;
    }

    /**
     * @param MarketplaceWebServiceOrders_Interface $service 订单接口服务
     * @param $request 请求类
     * @param $action 方法名
     * @return mixed
     * Note: 接口响应数据处理
     * Data: 2019/4/22 16:59
     * Author: zt7785
     */
    public function invokeGetOrdersData(MarketplaceWebServiceOrders_Interface $service, $request ,$action)
    {
        $apiResponse ['data'] = '';
        $apiResponse ['exception_Info'] = [];
        try {
            $response = $service->$action($request);
            $dom = new \DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $apiResponse ['data'] = $this->xmlToArray($dom->saveXML());
        } catch (MarketplaceWebServiceOrders_Exception $ex) {
            $apiResponse ['exception_Info'] ['msg']= $ex->getMessage();
            $apiResponse ['exception_Info'] ['status_code']= $ex->getStatusCode();
            $apiResponse ['exception_Info'] ['error_code']= $ex->getErrorCode();
            $apiResponse ['exception_Info'] ['error_type']= $ex->getErrorType();
            $apiResponse ['exception_Info'] ['request_id']= $ex->getRequestId();
        }
        return $apiResponse;
    }




    /**
     * @param $order
     * @return bool
     * @throws \Exception
     * Note: 订单入库
     * Data: 2019/4/23 10:10
     * Author: zt7785
     */
    public function saveOrder($order,$requestId,$shopData,$current_time) {
        $shopInfo = $shopData ['shopInfo'];
        $shopIds = $shopData ['shopIds'];
        $sale_info['user_id'] = $this->sale_info ['user_id'];
        $sale_info['shop_id'] = $this->sale_info ['id'];
        $sale_info['platforms_id'] = Platforms::AMAZON;
        $orig_amazon_order = [];
        $erpOrderInfo = [];
        $this->orderProductsItem = [];
        $amazon_order_code = $order['AmazonOrderId']; //3-7-7
        //是否FBA  AFN亚马逊配送 MFN卖家自行配送
        $ship_type       = $order['FulfillmentChannel'];
        $order_status    = $order['OrderStatus'];
        $match_status = false;

        $SalesChannel  = $order['SalesChannel'];
        $orderTotal = $order['OrderTotal'];
        $CurrencyCode = $orderTotal['CurrencyCode'];
        $Amount = $orderTotal['Amount'];
        if (!$amazon_order_code) {
            return true;
        }

        //检查当前交易和当前账号的站点是否一致
        $MarketplaceId = $order['MarketplaceId'];
        if ($MarketplaceId != $this->MarketplaceId) {
            return false;
        }

        //已存在的订单不作处理
        $orig_amazon_info = OrdersAmazon::getOrgOrderInfoByOpt('AmazonOrderId',$amazon_order_code);
        if ($orig_amazon_info)
        {
            return true;
        }
        //已存在的订单不作处理
        $cw_erp_exist = Orders::getOrdersByOriordercode($amazon_order_code);
        if ($cw_erp_exist)
        {
            return true;
        }
        //获取订单下商品信息
        $this->getOrderItemData($amazon_order_code,$current_time);
        $orderItemList = $this->orderProductsItem;
        if (empty($orderItemList)) {
            //订单无商品
            return false;
        }

        //商品总价
        $goodsAmount   = 0.00;
        //运费
        $ShippingPrice = 0.00;
        //消费税
        $excise = 0.00;
        $order_product_info = $orig_order_product_info = [];

        $this->mdb->beginTransaction();
        try
        {
            $BuyerEmail             = $order['BuyerEmail']??''; //买家邮箱
            $BuyerName              = $order['BuyerName']??''; //买家名称
            //地址信息数组
            $ShippingAddress        = $order['ShippingAddress'];
            $phone_number           = $ShippingAddress ? $ShippingAddress['Phone']??'' : 'no';
            $recipient_name         = $ShippingAddress ? str_replace("&", " ", $ShippingAddress['Name']) : 'no';
            $ship_address_1         = $ShippingAddress? $ShippingAddress['AddressLine1']??'' : 'no';
            $ship_address_2         = $ShippingAddress? $ShippingAddress['AddressLine2']??'' : 'no';
            $ship_address_3         = $ShippingAddress? $ShippingAddress['AddressLine3']??'' : 'no';
            $ship_city              = $ShippingAddress? $ShippingAddress['City']??'' : 'no';
            $ship_state             = $ShippingAddress? $ShippingAddress['StateOrRegion']??'' : 'no';
            $ship_postal_code       = $ShippingAddress? $ShippingAddress['PostalCode']??'' : 'no';
            $ship_country           = $ShippingAddress? $ShippingAddress['CountryCode']??'' : 'no';

            //初始化数据
            $orig_amazon_order['created_man']          = $this->sale_info['user_id'];
            $orig_amazon_order['cw_order_id']          = 0;
            $orig_amazon_order['cw_code']              = '';
            $orig_amazon_order['is_system']            = OrdersAmazon::UN_SYSTEM_ORDER;
            $orig_amazon_order['MatchStatus']          = OrdersAmazon::AMAZON_MAPPING_STATUS_UNFINISH;
            $orig_amazon_order['created_at']           = $orig_amazon_order['updated_at']  = $current_time;

            //收货地址相关
            $orig_amazon_order['Name']                 = $recipient_name;
            $orig_amazon_order['AddressLine1']         = $ship_address_1;
            $orig_amazon_order['AddressLine2']         = $ship_address_2;
            $orig_amazon_order['AddressLine3']         = $ship_address_3;
            $orig_amazon_order['City']                 = $ship_city;
            $orig_amazon_order['StateOrRegion']        = $ship_state;
            $orig_amazon_order['PostalCode']           = $ship_postal_code;
            $orig_amazon_order['CountryCode']          = $ship_country;
            $orig_amazon_order['Phone']                = $phone_number;
            $orig_amazon_order['CurrencyCode']         = $CurrencyCode;
            $orig_amazon_order['Amount']               = $Amount;
            $orig_amazon_order['BuyerEmail']           = $BuyerEmail;
            $orig_amazon_order['BuyerName']            = $BuyerName;

            $orig_amazon_order['AmazonOrderId']        = $amazon_order_code;
            $orig_amazon_order['SellerOrderId']        = $order['SellerOrderId']??'';
            $orig_amazon_order['RequestId']            = $requestId;
            $orig_amazon_order['MarketplaceId']        = $MarketplaceId;
            $orig_amazon_order['OrderType']            = $order['OrderType'];
            $orig_amazon_order['OrderStatus']          = $order_status;
            $orig_amazon_order['SalesChannel']         = $SalesChannel;
            //当地下单时间 日本东9区
            $orig_amazon_order['LocalPurchaseDate']    = $this->getLocalDateTime($order ['PurchaseDate']);
            //下单时间
            $orig_amazon_order['PurchaseDate']         = $this->resolverDateTime($order ['PurchaseDate']);
            //付款时间
            $orig_amazon_order['PaymentDate']         = $this->resolverDateTime($order ['PurchaseDate']);
            //订单的最后更新日期
            $orig_amazon_order['LastUpdateDate']       = $this->resolverDateTime($order ['LastUpdateDate']);
            //承诺的订单发货时间范围的最后一天
            $orig_amazon_order['LatestShipDate']       = $this->resolverDateTime($order ['LatestShipDate']);
            //订单最早的发货日期
            $orig_amazon_order['EarliestShipDate']     = $this->resolverDateTime($order ['EarliestShipDate']);
            $orig_amazon_order['FulfillmentChannel']   = $ship_type;
            $orig_amazon_order['ShipServiceLevel']     = $order['ShipServiceLevel'];
            /*
             * COD - 货到付款。仅适用于中国 (CN) 和日本 (JP)。
               CVS - 便利店。仅适用于日本 (JP)。
               Other - COD 和 CVS 之外的付款方式。
             */
            $orig_amazon_order['PaymentMethod']        = $order['PaymentMethod'];


            //订单的配送服务级别分类
            /*
             *  Expedited
                FreeEconomy
                NextDay
                SameDay
                SecondDay
                Scheduled
                Standard
             */
            $orig_amazon_order['ShipmentServiceLevelCategory'] = $order['ShipmentServiceLevelCategory'];
//        //亚马逊 TFM订单的状态 仅适用于中国
//        $orig_amazon_order['ShippedByAmazonTFM'] = $order['ShippedByAmazonTFM'];
//        //卖家自定义的配送方式 CBA 仅适用于美国 (US)、英国 (UK) 和德国 (DE) 的卖家。
//        $orig_amazon_order['CbaDisplayableShippingLabel'] = $order['CbaDisplayableShippingLabel'];


            $orig_amazon_order['IsPrime']                = $order['IsPrime'];
            $orig_amazon_order['IsPremiumOrder']         = $order['IsPremiumOrder'];
            $orig_amazon_order['IsBusinessOrder']        = $order['IsBusinessOrder'];
            $orig_amazon_order['IsReplacementOrder']     = $order['IsReplacementOrder'];
            //已发货
            $orig_amazon_order['NumberOfItemsShipped']   = $order['NumberOfItemsShipped'];
            //未发货
            $orig_amazon_order['NumberOfItemsUnshipped'] = $order['NumberOfItemsUnshipped'];
            //付款信息
            $orig_amazon_order['PaymentMethodDetails']   = json_encode($order['PaymentMethodDetails']);

            $orig_amazon_order ['cw_code']      = CodeInfo::getACode(CodeInfo::CW_ORDERS_CODE);

            //货到付款 (COD) 订单的次级付款方式的相关信息
//        PaymentExecutionDetail
            //"PaymentMethodDetails" => ["PaymentMethodDetail" => "Standard"]

            //原始数据插入
            $temp_order_id = OrdersAmazon::insertGetId($orig_amazon_order);
            //原始数据付款单
            $orig_payment_data ['created_man'] = $this->sale_info['user_id'];
            $orig_payment_data ['cw_order_id'] = $temp_order_id;
            $orig_payment_data ['totalPrice'] = $orig_amazon_order ['Amount'];
            $currencyInfo ['currency_code'] = $CurrencyCode;
            $rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($currencyInfo ['currency_code'],$this->sale_info['user_id']);
            $currencyInfo ['rate'] = $rate;
            $orig_payment_datas = $this->getOrdersBill($orig_payment_data,$currencyInfo,OrdersBillPayments::BILLS_PAY,$current_time,OrdersBillPayments::ORDERS_ORIG_AMAZON);
            $orig_payment_datas ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$amazon_order_code,'1');
            OrdersBillPayments::postData(0,$orig_payment_datas);

            //订单商品全部有映射关系才算匹配成功
            $skus = array_column($orderItemList, 'SellerSKU');
            $mappingInfo = GoodsMapping::getMappingInfoByUseridSku($this->sale_info['user_id'], $skus, Platforms::AMAZON);
            $mappingInfoSkus = array_column($mappingInfo, 'seller_sku');
            if (empty($mappingInfo)) {
                $match_status = false;
            }
            if (!empty($mappingInfo) && (count($skus) == count($mappingInfo))) {
                $match_status = true;
            }

            //匹配
            if ($match_status)
            {
                //写订单表
                $erpOrderInfo ['created_man']           = $this->sale_info['user_id'];
                $erpOrderInfo ['user_id']               = $this->sale_info['user_id'];
                $erpOrderInfo ['platforms_id']          = Platforms::AMAZON;
                $erpOrderInfo ['source_shop']           = $this->sale_info['id'];
                $erpOrderInfo ['order_number']          = $orig_amazon_order ['cw_code'];
                $erpOrderInfo ['plat_order_number']     = $amazon_order_code;
                $erpOrderInfo ['type']                  = Orders::ORDERS_GETINFO_API;
                $erpOrderInfo ['platform_name']         = '亚马逊';
                //店铺名称
                $shopKey = array_search($erpOrderInfo ['source_shop'], $shopIds);
                $erpOrderInfo ['source_shop_name']      = $shopInfo[$shopKey]['shop_name'];
                //默认状态
                $erpOrderInfo ['picking_status']        = Orders::ORDER_PICKING_STATUS_UNMATCH;
                $erpOrderInfo ['deliver_status']        = Orders::ORDER_DELIVER_STATUS_UNFILLED;
                $erpOrderInfo ['intercept_status']      = Orders::ORDER_INTERCEPT_STATUS_INITIAL;
                $erpOrderInfo ['sales_status']          = Orders::ORDER_SALES_STATUS_INITIAL;
                $erpOrderInfo ['status']                = Orders::ORDER_STATUS_UNFINISH;
                $erpOrderInfo ['order_price']           = $orig_amazon_order['Amount'];
                $erpOrderInfo ['currency_code']         = $erpOrderInfo['currency_freight'] = $currencyInfo['currency_code'];
                $erpOrderInfo ['rate']                  = $currencyInfo ['rate'];
                $erpOrderInfo ['payment_method']        = $orig_amazon_order['PaymentMethod'];
                $erpOrderInfo ['freight']               = '0.00';
                $erpOrderInfo ['postal_code']           = $orig_amazon_order['PostalCode'];
                $country_id = SettingCountry::getCountryIdByCode($CurrencyCode)??0;
                $erpOrderInfo ['country_id']            = $country_id;
                $erpOrderInfo ['country']               = $orig_amazon_order['CountryCode'];
                $erpOrderInfo ['province']              = $orig_amazon_order['StateOrRegion'];
                $erpOrderInfo ['city']                  = $orig_amazon_order['City'];
                $erpOrderInfo ['mobile_phone']           = $orig_amazon_order['Phone'];
                $erpOrderInfo ['phone']                 = $orig_amazon_order['Phone'];
                $erpOrderInfo ['addressee_name']        = $orig_amazon_order['Name'];
                $erpOrderInfo ['addressee_email']       = $orig_amazon_order['BuyerEmail'];
                //仓库
                $erpOrderInfo ['warehouse_id']          = '0';
                $erpOrderInfo ['warehouse']             = '';
                $erpOrderInfo ['logistics']             = '';
                $erpOrderInfo ['logistics_id']          = '0';

//            $userLogisticsKey = array_search($erpOrderInfo ['logistics'], $userLogisticsCodes);
//            if (is_bool($userLogisticsKey)) {
//                $erpOrderInfo ['logistics_id'] = '';
//            } else {
//                $erpOrderInfo ['logistics_id'] = $userLogisticsInfo[$userLogisticsKey]['id'];
//            }

                $erpOrderInfo ['addressee']  = $orig_amazon_order['AddressLine1'];
                $erpOrderInfo ['addressee1'] = $orig_amazon_order['AddressLine2'];
                $erpOrderInfo ['addressee2'] = $orig_amazon_order['AddressLine3'];
                $erpOrderInfo ['order_time'] = $erpOrderInfo ['payment_time'] = $orig_amazon_order['PaymentDate'];
                $erpOrderInfo ['created_at'] = $erpOrderInfo ['updated_at'] = $current_time;
                //订单id
                $erpOrderId = Orders::insertGetId($erpOrderInfo);

                //复制付款单
                $billOptions ['order_id']   = $erpOrderId;
                $billOptions ['order_type'] = OrdersBillPayments::ORDERS_ORIG_AMAZON;
                $billsDatas = OrdersBillPayments::getBillsByOptionss($billOptions);
                foreach ($billsDatas as $billsData) {
                    unset($billsData ['id']);
                    $billsData ['order_id']     = $erpOrderId;
                    $billsData ['order_type']   = OrdersBillPayments::ORDERS_CWERP;
                    if ($billsData ['type'] == OrdersBillPayments::BILLS_PAY) {
                        //目前一个订单只有一个付款单
                        $billsData ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$erpOrderInfo ['order_number'],'1');
                    } else {
                        $billsData ['bill_code'] = '';
                    }
                    OrdersBillPayments::postData(0,$billsData);
                }

                //关联原始订单
                $orig_amazon_order_update_data ['cw_order_id']  = $erpOrderId;
                $orig_amazon_order_update_data ['is_system']    = OrdersAmazon::IS_SYSTEM_ORDER;//是系统订单
                $orig_amazon_order_update_data ['MatchStatus'] = OrdersAmazon::AMAZON_MAPPING_STATUS_FINISHED;//匹配成功

                //订单量逻辑
                $record_time = strtotime(date('Y-m-d'));
                OrdersQuantityRecord::orderQuantityLogics($sale_info,$record_time);
            } else {
                //匹配失败更新原始订单状态
                if ($orig_amazon_order ['is_system'] == OrdersAmazon::UN_SYSTEM_ORDER && $orig_amazon_order ['match_status'] == OrdersAmazon::AMAZON_MAPPING_STATUS_UNFINISH) {
                    $orig_amazon_order_update_data ['is_system']    = OrdersAmazon::UN_SYSTEM_ORDER;//是系统订单
                    $orig_amazon_order_update_data ['MatchStatus'] = OrdersAmazon::AMAZON_MAPPING_STATUS_FAIL;//匹配成功
                }
            }

            //订单产品插入
            foreach ($orderItemList as $k => $orderItem) {
                //ShippingPrice : 运费  ShippingTax : 运费的税费 GiftWrapPrice : 商品的礼品包装金额 GiftWrapTax : 礼品包装金额的税费 ShippingDiscount : 运费的折扣
                $ShippingPriceAmount        = $orderItem['ShippingPrice'] ['Amount']??0.00;
                $ShippingTaxAmount          = $orderItem['ShippingTax'] ['Amount']??0.00;
                $GiftWrapPriceAmount        = $orderItem['GiftWrapPrice'] ['Amount']??0.00;
                $GiftWrapTaxAmount          = $orderItem['GiftWrapTax'] ['Amount']??0.00;
                $ShippingDiscountAmount     = $orderItem['ShippingDiscount'] ['Amount']??0.00;
                $ItemPriceAmount            = $orderItem['ItemPrice'] ['Amount']??0.00;
                $PromotionDiscountAmount    = $orderItem['PromotionDiscount'] ['Amount']??0.00;
                $ItemTaxAmount    = $orderItem['ItemTax'] ['Amount']??0.00;
                $ShippingPriceItem          = $ShippingPriceAmount + $ShippingTaxAmount + $GiftWrapPriceAmount + $GiftWrapTaxAmount - $ShippingDiscountAmount;
                //ItemPrice : 订单商品的售价 PromotionDiscount : 报价中的全部促销折扣总计
                $goodsAmountItem = $ItemPriceAmount - $PromotionDiscountAmount;
                $ShippingPrice += $ShippingPriceItem;
                $goodsAmount += $goodsAmountItem;
                //ItemTax : 商品价格的税费
                $excise += $ItemTaxAmount;

                //亚马逊商品
                $orig_order_product_info[$k]['amazon_order_id']     = $temp_order_id;
                $orig_order_product_info[$k]['user_id']             = $this->sale_info['user_id'];
                $orig_order_product_info[$k]['ASIN']                = $orderItem['ASIN'];
                $orig_order_product_info[$k]['SellerSKU']           = $orderItem['SellerSKU'];
                $orig_order_product_info[$k]['OrderItemId']         = $orderItem['OrderItemId'];
                $orig_order_product_info[$k]['Title']               = $orderItem['Title'];
                $orig_order_product_info[$k]['QuantityOrdered']     = $orderItem['QuantityOrdered'];
                $orig_order_product_info[$k]['QuantityShipped']     = $orderItem['QuantityShipped'];
                $orig_order_product_info[$k]['ItemPrice']           = $ItemPriceAmount;
                $orig_order_product_info[$k]['CurrencyCode']        = $orderItem['ItemPrice'] ['CurrencyCode'];
                $orig_order_product_info[$k]['ShippingPrice']       = $ShippingPriceAmount;
                $orig_order_product_info[$k]['GiftWrapPrice']       = $GiftWrapPriceAmount;
                $orig_order_product_info[$k]['ItemTax']             = $ItemTaxAmount;
                $orig_order_product_info[$k]['ShippingTax']         = $ShippingTaxAmount;
                $orig_order_product_info[$k]['GiftWrapTax']         = $GiftWrapTaxAmount;
                $orig_order_product_info[$k]['ShippingDiscount']    = $ShippingDiscountAmount;
                $orig_order_product_info[$k]['PromotionDiscount']   = $PromotionDiscountAmount;
                $orig_order_product_info[$k]['PromotionIds']        = json_encode($orderItem['PromotionIds']);
                $orig_order_product_info[$k]['CODFee']              = $orderItem['CODFee']??'';
                $orig_order_product_info[$k]['CODFeeDiscount']      = $orderItem['CODFeeDiscount']??'';
                $orig_order_product_info[$k]['GiftMessageText']     = $orderItem['GiftMessageText']??'';
                $orig_order_product_info[$k]['GiftWrapLevel']       = $orderItem['GiftWrapLevel']??'';
                $orig_order_product_info[$k]['InvoiceData']         = $orderItem['InvoiceData']??'';
                $orig_order_product_info[$k]['ConditionNote']       = $orderItem['ConditionNote']??'';
                $orig_order_product_info[$k]['ConditionId']         = $orderItem['ConditionId']??'';
                $orig_order_product_info[$k]['ConditionSubtypeId']              = $orderItem['ConditionSubtypeId']??'';
                $orig_order_product_info[$k]['ScheduledDeliveryStartDate']      = isset($orderItem['ScheduledDeliveryStartDate']) ? $this->resolverDateTime($orderItem['ScheduledDeliveryStartDate']) : '';
                $orig_order_product_info[$k]['ScheduledDeliveryEndDate']        = isset($orderItem['ScheduledDeliveryEndDate']) ? $this->resolverDateTime($orderItem['ScheduledDeliveryEndDate']) : '';
                //订单商品的折扣之后单价
                $orig_order_product_info[$k]['unit_discount_price'] = bcdiv($goodsAmountItem, $orderItem['QuantityOrdered'], 2);
                //优惠后运费
                $orig_order_product_info[$k]['shipping_fee']        = $ShippingPriceItem;
                $orig_order_product_info[$k]['created_at']          = $orig_order_product_info[$k]['updated_at'] = $current_time;
                $orig_order_product_info[$k]['goods_id']            = 0 ;
                //匹配成功之后 应用该数据
                if ($match_status) {
                    $order_product_info[$k]['created_man']          = $this->sale_info['user_id'];
                    $order_product_info[$k]['user_id']          = $this->sale_info['user_id'];
                    $order_product_info[$k]['order_id']             = $erpOrderId;
                    $mappingKey =  array_search($orderItem['SellerSKU'], $mappingInfoSkus);
                    $goods_id = GoodsMappingGoods::getGoodsIdByMappingid($mappingInfo[$mappingKey]['id']);
                    $orig_order_product_info[$k]['goods_id']        = $order_product_info[$k]['goods_id'] = $goods_id['goods_id'];
                    $order_product_info[$k]['order_type']           = OrdersProducts::ORDERS_CWERP;
                    $order_product_info[$k]['product_name']         = $orderItem['Title'];
                    $order_product_info[$k]['sku']                  = $orderItem['SellerSKU'];
                    $order_product_info[$k]['currency']             = $orderItem['ItemPrice'] ['CurrencyCode'];
                    $order_product_info[$k]['buy_number']               = $orderItem['QuantityOrdered'];
                    $order_product_info[$k]['univalence']           = $ItemPriceAmount;
                    $order_product_info[$k]['rate']                 = $currencyInfo ['rate'];
                    $order_product_info[$k]['RMB']                  = bcmul(bcmul($currencyInfo ['rate'], $ItemPriceAmount), $orderItem['QuantityOrdered']);
                    $order_product_info[$k]['created_at']           = $order_product_info[$k]['updated_at'] = $current_time;
                }

            }
            if (!empty($orig_order_product_info)) {
                OrdersAmazonProducts::insert($orig_order_product_info);
            }
            if (!empty($order_product_info)) {
                OrdersProducts::insert($order_product_info);
            }
            //运费
            $orig_amazon_order_update_data ['updated_at']   = $cw_order_update_data ['updated_at'] = $current_time;
            $cw_order_update_data['freight']                = $orig_amazon_order_update_data ['freight']   = $ShippingPrice;
            if ($match_status) {
                Orders::where('id',$erpOrderId)->update($cw_order_update_data);
            }
            //商品总价
            $orig_amazon_order_update_data['goods_amount']      = $goodsAmount;
            $orig_amazon_order_update_data['excise']            = $excise;
            OrdersAmazon::where('id',$temp_order_id)->update($orig_amazon_order_update_data);

            $this->mdb->commit();
        } catch ( \Exception $exception)
        {
            $this->mdb->rollback();
            $exception_data = [
                'orig_order_id_amazon'      => $temp_order_id,
                'request_id'                => $requestId,
                'order_status'              => $order_status,
                'last_update_date'          => $orig_amazon_order['LastUpdateDate'],
                'start_time'                => $current_time,
                'msg'                       => '失败信息：' . $exception->getMessage(),
                'line'                      => '失败行数：' . $exception->getLine(),
                'file'                      => '失败文件：' . $exception->getFile(),
            ];

            LogHelper::setExceptionLog($exception_data,$this->exceptionTask);
            $exceptionDing ['type'] = 'task';
            $dingPushData ['task'] = 'Amazon订单信息处理2';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = 'saveOrder';
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($exception_data,null,$exceptionDing ['type']);
        }
        return true;
    }

    /**
     * @param $arr
     * @return string
     * Note: Array 转 XML
     * Data: 2019/3/7 9:03
     * Author: zt7785
     */
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * @param $xml
     * @return mixed
     * Note: XML 转 Array
     * Data: 2019/3/7 9:03
     * Author: zt7785
     */
    public function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    /**
     * @param $dateTime
     * @return false|string
     * Note: UTC时间转换 写数据库
     * Data: 2019/4/23 14:01
     * Author: zt7785
     */
    public function resolverDateTime ($dateTime) {
        return date('Y-m-d H:i:s',strtotime($dateTime));
    }

    /**
     * @param $dateTime
     * @return false|string
     * Note: 获取UTC时间 orderList 参数
     * Data: 2019/4/23 14:08
     * Author: zt7785
     */
    public function getFormatTimes ($dateTime) {
        return  gmdate("Y-m-d\TH:i:s.\\0\\0\\0\Z",strtotime($dateTime));
    }

    /**
     * @param $dateTime
     * @return string
     * Note: 获取ISO8601时间
     * Data: 2019/4/23 14:05
     * Author: zt7785
     */
    public function getISODateTime ($dateTime) {
        $Date = new \DateTime($dateTime);
        return $Date->format(\DATE_ISO8601);
    }

    /**
     * @param $dateTime
     * @param int $GMT
     * @return false|string
     * Note: 转换为当地时间 默认日本东9区
     * Data: 2019/4/23 14:55
     * Author: zt7785
     */
    public function getLocalDateTime ($dateTime,$GMT = 9) {
        return gmdate('Y-m-d H:i:s', strtotime($dateTime) + $GMT * 3600);
    }

    /**
     * @param $data
     * @param $currency_code
     * @param $rate
     * @return mixed
     * Note: 生成付款单|退款单
     * Data: 2019/4/11 11:05
     * Author: zt7785
     */
    public function getOrdersBill ($data,$currencyInfo,$type,$current_time,$order_type) {
        $bill ['created_man'] = $data['created_man'];
        $bill ['order_id'] = $data['cw_order_id'];//平台订单id
        $bill ['order_type'] = $order_type;//平台订单
        $bill ['status'] = OrdersBillPayments::BILLS_STATUS_INIT;
//        $bill ['type'] = OrdersBillPayments::BILLS_PAY;
        $bill ['type'] = $type;
        $bill ['amount'] = $data['totalPrice'];
        $bill ['rate'] = $currencyInfo['rate'];
        $bill ['currency_code'] = $currencyInfo['currency_code'];
        $bill ['created_at'] = $bill ['updated_at'] = $current_time;
        return $bill;
    }


    /**
     * @param $order
     * @param $requestId
     * @param $shopData
     * @param $current_time
     * @return bool
     * Note: 同步数据写入原始订单表
     * Data: 2019/5/9 11:07
     * Author: zt7785
     */
    public function saveOrders($order,$requestId,$shopData,$current_time) {
        $shopInfo = $shopData ['shopInfo'];
        $shopIds = $shopData ['shopIds'];
        $sale_info['user_id'] = $this->sale_info ['user_id'];
        $sale_info['shop_id'] = $this->sale_info ['id'];
        $sale_info['platforms_id'] = Platforms::AMAZON;
        //店铺名称
        $shopKey = array_search($sale_info['shop_id'], $shopIds);
        $sale_info['shop_name']      = $shopInfo[$shopKey]['shop_name'];

        $orig_amazon_order = [];
        $erpOrderInfo = [];
        $this->orderProductsItem = [];
        $amazon_order_code = $order['AmazonOrderId']; //3-7-7
        //是否FBA  AFN亚马逊配送 MFN卖家自行配送
        $ship_type       = $order['FulfillmentChannel'];
        $order_status    = $order['OrderStatus'];
        if (in_array($order_status,self::$undetermined_status)) {
            return true;
        }
        $match_status = false;

        $SalesChannel  = $order['SalesChannel'];
        $orderTotal = isset($order['OrderTotal']) ? $order['OrderTotal'] : $this->defaultOrderTotal;
        $CurrencyCode = $orderTotal['CurrencyCode'];

        $currencyInfo ['currency_code'] = $CurrencyCode;
        $rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($currencyInfo ['currency_code'],$this->sale_info['user_id']);
        $currencyInfo ['rate'] = $rate;

        $country_id = SettingCountry::getCountryIdByCode($CurrencyCode)??42;

        $Amount = $orderTotal['Amount'];
        if (!$amazon_order_code) {
            return true;
        }

        //检查当前交易和当前账号的站点是否一致
        $MarketplaceId = $order['MarketplaceId'];
        if ($MarketplaceId != $this->MarketplaceId) {
            return false;
        }

        //已存在的订单不作处理
        $orig_amazon_info = OrdersAmazon::getOrgOrderInfoByOpt('AmazonOrderId',$amazon_order_code);
        if ($orig_amazon_info)
        {
            return true;
        }
        //已存在的订单不作处理
        $cw_erp_exist = Orders::getOrdersByOriordercode($amazon_order_code);
        if ($cw_erp_exist)
        {
            return true;
        }
        //获取订单下商品信息
        $this->getOrderItemData($amazon_order_code,$current_time);
        $orderItemList = $this->orderProductsItem;
        if (empty($orderItemList)) {
            //订单无商品
            return false;
        }

        //商品总价
        $goodsAmount   = 0.00;
        //运费
        $ShippingPrice = 0.00;
        //消费税
        $excise = 0.00;
        $order_product_info = $orig_order_product_info = $orders_original_product_info = $orders_original_update_data = [];

        $this->mdb->beginTransaction();
        try
        {
            $BuyerEmail             = $order['BuyerEmail']??''; //买家邮箱
            $BuyerName              = $order['BuyerName']??''; //买家名称
            //地址信息数组
            if (!isset($order['ShippingAddress'])) {
                $ShippingAddress        = '';
            } else {
                $ShippingAddress        = $order['ShippingAddress'];
            }
            $phone_number           = $ShippingAddress ? $ShippingAddress['Phone']??'' : 'no';
            $recipient_name         = $ShippingAddress ? str_replace("&", " ", $ShippingAddress['Name']) : 'no';
            $ship_address_1         = $ShippingAddress? $ShippingAddress['AddressLine1']??'' : 'no';
            $ship_address_2         = $ShippingAddress? $ShippingAddress['AddressLine2']??'' : 'no';
            $ship_address_3         = $ShippingAddress? $ShippingAddress['AddressLine3']??'' : 'no';
            $ship_city              = $ShippingAddress? $ShippingAddress['City']??'' : 'no';
            $ship_state             = $ShippingAddress? $ShippingAddress['StateOrRegion']??'' : 'no';
            $ship_postal_code       = $ShippingAddress? $ShippingAddress['PostalCode']??'' : 'no';
            $ship_country           = $ShippingAddress? $ShippingAddress['CountryCode']??'' : 'no';

            //初始化数据
            $orig_amazon_order['created_man']          = $this->sale_info['user_id'];
            $orig_amazon_order['cw_order_id']          = 0;
            $orig_amazon_order['cw_code']              = '';
            $orig_amazon_order['is_system']            = OrdersAmazon::UN_SYSTEM_ORDER;
            $orig_amazon_order['MatchStatus']          = OrdersAmazon::AMAZON_MAPPING_STATUS_UNFINISH;
            $orig_amazon_order['created_at']           = $orig_amazon_order['updated_at']  = $current_time;

            //收货地址相关
            $orig_amazon_order['Name']                 = $recipient_name;
            $orig_amazon_order['AddressLine1']         = $ship_address_1;
            $orig_amazon_order['AddressLine2']         = $ship_address_2;
            $orig_amazon_order['AddressLine3']         = $ship_address_3;
            $orig_amazon_order['City']                 = $ship_city;
            $orig_amazon_order['StateOrRegion']        = $ship_state;
            $orig_amazon_order['PostalCode']           = $ship_postal_code;
            $orig_amazon_order['CountryCode']          = $ship_country;
            $orig_amazon_order['Phone']                = $phone_number;
            $orig_amazon_order['CurrencyCode']         = $CurrencyCode;
            $orig_amazon_order['Amount']               = $Amount;
            $orig_amazon_order['BuyerEmail']           = $BuyerEmail;
            $orig_amazon_order['BuyerName']            = $BuyerName;

            $orig_amazon_order['AmazonOrderId']        = $amazon_order_code;
            $orig_amazon_order['SellerOrderId']        = $order['SellerOrderId']??'';
            $orig_amazon_order['RequestId']            = $requestId;
            $orig_amazon_order['MarketplaceId']        = $MarketplaceId;
            $orig_amazon_order['OrderType']            = $order['OrderType'];
            $orig_amazon_order['OrderStatus']          = $order_status;
            $orig_amazon_order['SalesChannel']         = $SalesChannel;
            //当地下单时间 日本东9区
            $orig_amazon_order['LocalPurchaseDate']    = $this->getLocalDateTime($order ['PurchaseDate']);
            //下单时间
            $orig_amazon_order['PurchaseDate']         = $this->resolverDateTime($order ['PurchaseDate']);
            //付款时间
            $orig_amazon_order['PaymentDate']         = $this->resolverDateTime($order ['PurchaseDate']);
            //订单的最后更新日期
            $orig_amazon_order['LastUpdateDate']       = isset($order ['LastUpdateDate']) ? $this->resolverDateTime($order ['LastUpdateDate']):'0000-00-00 00:00:00';
            //承诺的订单发货时间范围的最后一天
            $orig_amazon_order['LatestShipDate']       = $this->resolverDateTime($order ['LatestShipDate']);
            //订单最早的发货日期
            $orig_amazon_order['EarliestShipDate']     = $this->resolverDateTime($order ['EarliestShipDate']);
            $orig_amazon_order['FulfillmentChannel']   = $ship_type;
            $orig_amazon_order['ShipServiceLevel']     = $order['ShipServiceLevel'];
            /*
             * COD - 货到付款。仅适用于中国 (CN) 和日本 (JP)。
               CVS - 便利店。仅适用于日本 (JP)。
               Other - COD 和 CVS 之外的付款方式。
             */
            $orig_amazon_order['PaymentMethod']        = $order['PaymentMethod'];


            //订单的配送服务级别分类
            /*
             *  Expedited
                FreeEconomy
                NextDay
                SameDay
                SecondDay
                Scheduled
                Standard
             */
            $orig_amazon_order['ShipmentServiceLevelCategory'] = $order['ShipmentServiceLevelCategory'];
//        //亚马逊 TFM订单的状态 仅适用于中国
//        $orig_amazon_order['ShippedByAmazonTFM'] = $order['ShippedByAmazonTFM'];
//        //卖家自定义的配送方式 CBA 仅适用于美国 (US)、英国 (UK) 和德国 (DE) 的卖家。
//        $orig_amazon_order['CbaDisplayableShippingLabel'] = $order['CbaDisplayableShippingLabel'];


            $orig_amazon_order['IsPrime']                = $order['IsPrime'];
            $orig_amazon_order['IsPremiumOrder']         = $order['IsPremiumOrder'];
            $orig_amazon_order['IsBusinessOrder']        = $order['IsBusinessOrder'];
            $orig_amazon_order['IsReplacementOrder']     = $order['IsReplacementOrder'];
            //已发货
            $orig_amazon_order['NumberOfItemsShipped']   = $order['NumberOfItemsShipped'];
            //未发货
            $orig_amazon_order['NumberOfItemsUnshipped'] = $order['NumberOfItemsUnshipped'];
            //付款信息
            $orig_amazon_order['PaymentMethodDetails']   = json_encode($order['PaymentMethodDetails']);

            $orig_amazon_order ['cw_code']      = CodeInfo::getACode(CodeInfo::CW_ORDERS_CODE);

            //货到付款 (COD) 订单的次级付款方式的相关信息
//        PaymentExecutionDetail
            //"PaymentMethodDetails" => ["PaymentMethodDetail" => "Standard"]

            //原始数据插入
            $temp_order_id = OrdersAmazon::insertGetId($orig_amazon_order);


            //组装原始订单表 orders_original 数据
            $orders_original_data ['created_man']       = $this->sale_info['user_id'];
            $orders_original_data ['platform']          = $sale_info['platforms_id'];
            $orders_original_data ['source_shop']       = $sale_info['shop_id'];
            $orders_original_data ['bill_payments']     = 0;
            $orders_original_data ['order_id']          = 0;
            $orders_original_data ['order_number']      = $orig_amazon_order['cw_code'];
            $orders_original_data ['platform_name']     = '亚马逊';
            $orders_original_data ['source_shop_name']  = $sale_info['shop_name'];
            $orders_original_data ['match_status']      = $orig_amazon_order['MatchStatus'];
            $orders_original_data ['order_price']       = $orig_amazon_order['Amount'];
            $orders_original_data ['payment_method']    = $orig_amazon_order['PaymentMethod'];
            $orders_original_data ['freight']           = '0.00';
            $orders_original_data ['currency_freight']  = $currencyInfo['currency_code'];
            $orders_original_data ['country']           = $orig_amazon_order['CountryCode'];
            $orders_original_data ['province']          = $orig_amazon_order['StateOrRegion'];
            $orders_original_data ['city']              = $orig_amazon_order['City'];
            $orders_original_data ['mobile_phone']       = $orig_amazon_order['Phone'];
            $orders_original_data ['phone']             = $orig_amazon_order['Phone'];
            $orders_original_data ['addressee_email']   = $orig_amazon_order['BuyerEmail'];
            //仓库物流
            $orders_original_data ['warehouse']         = '';
            $orders_original_data ['logistics']         = '';
            $orders_original_data ['warehouse_id']      = 0;
            $orders_original_data ['logistics_id']      = 0;
            $orders_original_data ['addressee_name']    = $orig_amazon_order['Name'];
            $orders_original_data ['addressee1']        = $orig_amazon_order['AddressLine1'];
            $orders_original_data ['addressee2']        = $orig_amazon_order['AddressLine2'];
            $orders_original_data ['order_time']        = $orders_original_data ['payment_time'] = $orig_amazon_order['PaymentDate'];
            $orders_original_data ['currency']          = $currencyInfo['currency_code'];
            $orders_original_data ['order_source']      = OrdersOriginal::ORDERS_ORIGINAL_FROM_API;
            $orders_original_data ['user_id']           = $this->sale_info['user_id'];
            $orders_original_data ['grab_time']         = $current_time;
            $orders_original_data ['zip_code']          = $orig_amazon_order['PostalCode'];
            $orders_original_data ['platform_order']    = $amazon_order_code;
            $orders_original_data ['rate']              = $currencyInfo['rate'];
            $orders_original_data ['country_id']        = $country_id;
            $orders_original_data ['order_source_id']   = $temp_order_id;
            $orders_original_data ['created_at']   = $current_time;
            $orders_original_data ['updated_at']   = $current_time;

            $orders_original_id = OrdersOriginal::insertGetId($orders_original_data);

            //原始数据付款单
            $orig_payment_data ['created_man'] = $this->sale_info['user_id'];
            $orig_payment_data ['cw_order_id'] = $orders_original_id;
            $orig_payment_data ['totalPrice'] = $orig_amazon_order ['Amount'];
            $orig_payment_datas = $this->getOrdersBill($orig_payment_data,$currencyInfo,OrdersBillPayments::BILLS_PAY,$current_time,OrdersBillPayments::ORDERS_ORIG_AMAZON);
            $orig_payment_datas ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$amazon_order_code,'1');
            $orig_payment_result = OrdersBillPayments::postData(0,$orig_payment_datas);
            //付款单
            $orders_original_update_data ['bill_payments'] = $orig_payment_result->id;


            //订单商品全部有映射关系才算匹配成功
            $skus = array_column($orderItemList, 'SellerSKU');
            $mappingInfo = GoodsMapping::getMappingInfoByUseridSku($this->sale_info['user_id'], $skus, Platforms::AMAZON);
            $mappingInfoSkus = array_column($mappingInfo, 'seller_sku');
            if (empty($mappingInfo)) {
                $match_status = false;
            }
            if (!empty($mappingInfo) && (count($skus) == count($mappingInfo))) {
                $match_status = true;
            }

            //匹配
            if ($match_status)
            {
                //写订单表
                $erpOrderInfo ['created_man']           = $this->sale_info['user_id'];
                $erpOrderInfo ['user_id']               = $this->sale_info['user_id'];
                $erpOrderInfo ['platforms_id']          = Platforms::AMAZON;
                $erpOrderInfo ['source_shop']           = $this->sale_info['id'];
                $erpOrderInfo ['order_number']          = $orig_amazon_order ['cw_code'];
                $erpOrderInfo ['plat_order_number']     = $amazon_order_code;
                $erpOrderInfo ['type']                  = Orders::ORDERS_GETINFO_API;
                $erpOrderInfo ['platform_name']         = '亚马逊';
                //店铺名称
                $erpOrderInfo ['source_shop_name']      = $sale_info['shop_name'];
                //默认状态
                $erpOrderInfo ['picking_status']        = Orders::ORDER_PICKING_STATUS_UNMATCH;
                $erpOrderInfo ['deliver_status']        = Orders::ORDER_DELIVER_STATUS_UNFILLED;
                $erpOrderInfo ['intercept_status']      = Orders::ORDER_INTERCEPT_STATUS_INITIAL;
                $erpOrderInfo ['sales_status']          = Orders::ORDER_SALES_STATUS_INITIAL;
                $erpOrderInfo ['status']                = Orders::ORDER_STATUS_UNFINISH;
                $erpOrderInfo ['order_price']           = $orig_amazon_order['Amount'];
                $erpOrderInfo ['currency_code']         = $erpOrderInfo['currency_freight'] = $currencyInfo['currency_code'];
                $erpOrderInfo ['rate']                  = $currencyInfo ['rate'];
                $erpOrderInfo ['payment_method']        = $orig_amazon_order['PaymentMethod'];
                $erpOrderInfo ['freight']               = '0.00';
                $erpOrderInfo ['postal_code']           = $orig_amazon_order['PostalCode'];
                $erpOrderInfo ['country_id']            = $country_id;
                $erpOrderInfo ['country']               = $orig_amazon_order['CountryCode'];
                $erpOrderInfo ['province']              = $orig_amazon_order['StateOrRegion'];
                $erpOrderInfo ['city']                  = $orig_amazon_order['City'];
                $erpOrderInfo ['mobile_phone']           = $orig_amazon_order['Phone'];
                $erpOrderInfo ['phone']                 = $orig_amazon_order['Phone'];
                $erpOrderInfo ['addressee_name']        = $orig_amazon_order['Name'];
                $erpOrderInfo ['addressee_email']       = $orig_amazon_order['BuyerEmail'];
                //仓库
                $erpOrderInfo ['warehouse']             = '';
                $erpOrderInfo ['logistics']             = '';
                $erpOrderInfo ['logistics_id']          = 0;
                $erpOrderInfo ['warehouse_id']          = 0;
                $erpOrderInfo ['logistics_choose_status']          = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;
                $erpOrderInfo ['warehouse_choose_status']          = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;


//            $userLogisticsKey = array_search($erpOrderInfo ['logistics'], $userLogisticsCodes);
//            if (is_bool($userLogisticsKey)) {
//                $erpOrderInfo ['logistics_id'] = '';
//            } else {
//                $erpOrderInfo ['logistics_id'] = $userLogisticsInfo[$userLogisticsKey]['id'];
//            }

                $erpOrderInfo ['addressee']  = $orig_amazon_order['AddressLine1'];
                $erpOrderInfo ['addressee1'] = $orig_amazon_order['AddressLine2'];
                $erpOrderInfo ['addressee2'] = $orig_amazon_order['AddressLine3'];
                $erpOrderInfo ['order_time'] = $erpOrderInfo ['payment_time'] = $orig_amazon_order['PaymentDate'];
                $erpOrderInfo ['created_at'] = $erpOrderInfo ['updated_at'] = $current_time;
                //订单id
                $erpOrderId = Orders::insertGetId($erpOrderInfo);

                //复制付款单
                $billOptions ['order_id']   = $orders_original_id;
                $billOptions ['order_type'] = OrdersBillPayments::ORDERS_ORIG_AMAZON;
                $billsDatas = OrdersBillPayments::getBillsByOptionss($billOptions);
                foreach ($billsDatas as $billsData) {
                    unset($billsData ['id']);
                    $billsData ['order_id']     = $erpOrderId;
                    $billsData ['order_type']   = OrdersBillPayments::ORDERS_CWERP;
                    if ($billsData ['type'] == OrdersBillPayments::BILLS_PAY) {
                        //目前一个订单只有一个付款单
                        $billsData ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$erpOrderInfo ['order_number'],'1');
                    } else {
                        $billsData ['bill_code'] = '';
                    }
                    OrdersBillPayments::postData(0,$billsData);
                }

                //关联原始亚马逊订单
                $orig_amazon_order_update_data ['cw_order_id']  = $erpOrderId;
                $orig_amazon_order_update_data ['is_system']    = OrdersAmazon::IS_SYSTEM_ORDER;//是系统订单
                $orig_amazon_order_update_data ['MatchStatus']  = OrdersAmazon::AMAZON_MAPPING_STATUS_FINISHED;//匹配成功

                //关联原始订单
                $orders_original_update_data ['order_id']       = $erpOrderId;
                $orders_original_update_data ['match_status']   = OrdersAmazon::AMAZON_MAPPING_STATUS_FINISHED;//匹配成功

                //订单量逻辑
                $record_time = strtotime(date('Y-m-d'));
                OrdersQuantityRecord::orderQuantityLogics($sale_info,$record_time);
            } else {
                //匹配失败更新原始订单状态
                if ($orig_amazon_order ['is_system'] == OrdersAmazon::UN_SYSTEM_ORDER && $orig_amazon_order ['MatchStatus'] == OrdersAmazon::AMAZON_MAPPING_STATUS_UNFINISH) {
                    $orig_amazon_order_update_data ['is_system']    = OrdersAmazon::UN_SYSTEM_ORDER;//不是是系统订单
                    $orig_amazon_order_update_data ['MatchStatus']  = OrdersAmazon::AMAZON_MAPPING_STATUS_FAIL;//匹配失败

                    $orders_original_update_data ['match_status']   = OrdersAmazon::AMAZON_MAPPING_STATUS_FAIL;//匹配失败

                }
            }

            //订单产品插入
            foreach ($orderItemList as $k => $orderItem) {
                //ShippingPrice : 运费  ShippingTax : 运费的税费 GiftWrapPrice : 商品的礼品包装金额 GiftWrapTax : 礼品包装金额的税费 ShippingDiscount : 运费的折扣
                $ShippingPriceAmount        = $orderItem['ShippingPrice'] ['Amount']??0.00;
                $ShippingTaxAmount          = $orderItem['ShippingTax'] ['Amount']??0.00;
                $GiftWrapPriceAmount        = $orderItem['GiftWrapPrice'] ['Amount']??0.00;
                $GiftWrapTaxAmount          = $orderItem['GiftWrapTax'] ['Amount']??0.00;
                $ShippingDiscountAmount     = $orderItem['ShippingDiscount'] ['Amount']??0.00;
                $ItemPriceAmount            = $orderItem['ItemPrice'] ['Amount']??0.00;
                $PromotionDiscountAmount    = $orderItem['PromotionDiscount'] ['Amount']??0.00;
                $ItemTaxAmount    = $orderItem['ItemTax'] ['Amount']??0.00;
                $ShippingPriceItem          = $ShippingPriceAmount + $ShippingTaxAmount + $GiftWrapPriceAmount + $GiftWrapTaxAmount - $ShippingDiscountAmount;
                //ItemPrice : 订单商品的售价 PromotionDiscount : 报价中的全部促销折扣总计
                $goodsAmountItem = $ItemPriceAmount - $PromotionDiscountAmount;
                $ShippingPrice += $ShippingPriceItem;
                $goodsAmount += $goodsAmountItem;
                //ItemTax : 商品价格的税费
                $excise += $ItemTaxAmount;

                //亚马逊商品
                $orig_order_product_info[$k]['amazon_order_id']     = $temp_order_id;
                $orig_order_product_info[$k]['user_id']             = $this->sale_info['user_id'];
                $orig_order_product_info[$k]['ASIN']                = $orderItem['ASIN'];
                $orig_order_product_info[$k]['SellerSKU']           = $orderItem['SellerSKU'];
                $orig_order_product_info[$k]['OrderItemId']         = $orderItem['OrderItemId'];
                $orig_order_product_info[$k]['Title']               = $orderItem['Title'];
                $orig_order_product_info[$k]['QuantityOrdered']     = $orderItem['QuantityOrdered'];
                $orig_order_product_info[$k]['QuantityShipped']     = $orderItem['QuantityShipped'];
                $orig_order_product_info[$k]['ItemPrice']           = $ItemPriceAmount;
                $orig_order_product_info[$k]['CurrencyCode']        = $orderItem['ItemPrice'] ['CurrencyCode'];
                $orig_order_product_info[$k]['ShippingPrice']       = $ShippingPriceAmount;
                $orig_order_product_info[$k]['GiftWrapPrice']       = $GiftWrapPriceAmount;
                $orig_order_product_info[$k]['ItemTax']             = $ItemTaxAmount;
                $orig_order_product_info[$k]['ShippingTax']         = $ShippingTaxAmount;
                $orig_order_product_info[$k]['GiftWrapTax']         = $GiftWrapTaxAmount;
                $orig_order_product_info[$k]['ShippingDiscount']    = $ShippingDiscountAmount;
                $orig_order_product_info[$k]['PromotionDiscount']   = $PromotionDiscountAmount;
                $orig_order_product_info[$k]['PromotionIds']        = json_encode($orderItem['PromotionIds']);
                $orig_order_product_info[$k]['CODFee']              = $orderItem['CODFee']??'';
                $orig_order_product_info[$k]['CODFeeDiscount']      = $orderItem['CODFeeDiscount']??'';
                $orig_order_product_info[$k]['GiftMessageText']     = $orderItem['GiftMessageText']??'';
                $orig_order_product_info[$k]['GiftWrapLevel']       = $orderItem['GiftWrapLevel']??'';
                $orig_order_product_info[$k]['InvoiceData']         = $orderItem['InvoiceData']??'';
                $orig_order_product_info[$k]['ConditionNote']       = $orderItem['ConditionNote']??'';
                $orig_order_product_info[$k]['ConditionId']         = $orderItem['ConditionId']??'';
                $orig_order_product_info[$k]['ConditionSubtypeId']              = $orderItem['ConditionSubtypeId']??'';
                $orig_order_product_info[$k]['ScheduledDeliveryStartDate']      = isset($orderItem['ScheduledDeliveryStartDate']) ? $this->resolverDateTime($orderItem['ScheduledDeliveryStartDate']) : '';
                $orig_order_product_info[$k]['ScheduledDeliveryEndDate']        = isset($orderItem['ScheduledDeliveryEndDate']) ? $this->resolverDateTime($orderItem['ScheduledDeliveryEndDate']) : '';
                //订单商品的折扣之后单价
                $orig_order_product_info[$k]['unit_discount_price'] = bcdiv($goodsAmountItem, $orderItem['QuantityOrdered'], 2);
                //优惠后运费
                $orig_order_product_info[$k]['shipping_fee']        = $ShippingPriceItem;
                $orig_order_product_info[$k]['created_at']          = $orig_order_product_info[$k]['updated_at'] = $current_time;
                $orig_order_product_info[$k]['goods_id']            = 0 ;

                //原始订单商品数据
                $orders_original_product_info[$k]['created_man']            = $this->sale_info['user_id'];
                $orders_original_product_info[$k]['user_id']                = $this->sale_info['user_id'];
                $orders_original_product_info[$k]['original_order_id']      = $orders_original_id;
                $orders_original_product_info[$k]['goods_id']               = 0;
                $orders_original_product_info[$k]['goods_img']              = '';
                $orders_original_product_info[$k]['platform_id']            = Platforms::AMAZON;
                $orders_original_product_info[$k]['sku']                    = $orderItem['SellerSKU'];
                $orders_original_product_info[$k]['price']                  = $ItemPriceAmount;
                $orders_original_product_info[$k]['quantity']               = $orderItem['QuantityOrdered'];
                $orders_original_product_info[$k]['goods_name']             = $orderItem['Title'];
                $orders_original_product_info[$k]['rate']                   = $currencyInfo ['rate'];
                $orders_original_product_info[$k]['RMB']                    = bcmul(bcmul($currencyInfo ['rate'], $ItemPriceAmount), $orderItem['QuantityOrdered']);
                $orders_original_product_info[$k]['created_at']             = $orders_original_product_info[$k]['updated_at'] = $current_time;
                $orders_original_product_info[$k]['AmazonOrderItemCode']    = $orderItem['OrderItemId'];

                //匹配成功之后 应用该数据
                if ($match_status) {
                    $order_product_info[$k]['created_man']          = $this->sale_info['user_id'];
                    $order_product_info[$k]['order_id']             = $erpOrderId;
                    $mappingKey =  array_search($orderItem['SellerSKU'], $mappingInfoSkus);
                    $goods_id = GoodsMappingGoods::getGoodsIdByMappingid($mappingInfo[$mappingKey]['id']);
                    $orig_order_product_info[$k]['goods_id']        = $order_product_info[$k]['goods_id'] = $orders_original_product_info[$k]['goods_id']    = $goods_id['goods_id'];
                    //原始表商品图片
                    $orders_original_product_info[$k]['goods_img']  = $goods_id ['goods'] ? $goods_id ['goods'] ['goods_pictures'] : '';
                    $order_product_info[$k]['order_type']           = OrdersProducts::ORDERS_CWERP;
                    $order_product_info[$k]['product_name']         = $orderItem['Title'];
                    $order_product_info[$k]['sku']                  = $orderItem['SellerSKU'];
                    $order_product_info[$k]['currency']             = $orderItem['ItemPrice'] ['CurrencyCode'];
                    $order_product_info[$k]['buy_number']           = $orderItem['QuantityOrdered'];
                    $order_product_info[$k]['weight']               = $goods_id['goods']? $goods_id['goods'] ['goods_weight']:'0.00';
                    $order_product_info[$k]['univalence']           = $ItemPriceAmount;
                    $order_product_info[$k]['rate']                 = $currencyInfo ['rate'];
                    $order_product_info[$k]['is_deleted']           = OrdersProducts::ORDERS_PRODUCT_UNDELETED;
                    $order_product_info[$k]['RMB']                  = bcmul(bcmul($currencyInfo ['rate'], $ItemPriceAmount), $orderItem['QuantityOrdered']);
                    $order_product_info[$k]['AmazonOrderItemCode']   = $orderItem['OrderItemId'];
                    $order_product_info[$k]['created_at']               = $order_product_info[$k]['updated_at'] = $current_time;
                    $order_product_info[$k]['already_stocked_number']   = $order_product_info[$k]['cargo_distribution_number'] = $order_product_info[$k]['delivery_number'] = $order_product_info[$k]['partial_refund_number'] = 0;
                }
            }
            //原始亚马逊商品表写数据
            if (!empty($orig_order_product_info)) {
                OrdersAmazonProducts::insert($orig_order_product_info);
            }

            //原始订单商品表写数据
            if (!empty($orders_original_product_info)) {
                OrdersOriginalProducts::insert($orders_original_product_info);
            }
            //订单表商品数据
            if (!empty($order_product_info)) {
                OrdersProducts::insert($order_product_info);
            }
            //运费
            $cw_order_update_data['freight']                = $orig_amazon_order_update_data ['freight']   = $ShippingPrice;

            $orig_amazon_order_update_data ['updated_at']   = $orders_original_update_data ['updated_at'] = $cw_order_update_data ['updated_at'] = $current_time;
            if ($match_status) {
                Orders::where('id',$erpOrderId)->update($cw_order_update_data);
            }
            //商品总价
            $orig_amazon_order_update_data['goods_amount']      = $goodsAmount;
            $orig_amazon_order_update_data['excise']            = $excise;
            //原始亚马逊订单 匹配状态等数据更新
            OrdersAmazon::where('id',$temp_order_id)->update($orig_amazon_order_update_data);
            //原始订单 匹配状态等数据更新
            OrdersOriginal::where('id',$orders_original_id)->update($orders_original_update_data);

            $this->mdb->commit();
        } catch ( \Exception $exception)
        {
            $this->mdb->rollback();
            $exception_data = [
                'orig_order_id_amazon'      => $temp_order_id ??0,
                'request_id'                => $requestId,
                'order_status'              => $order_status,
                'start_time'                => $current_time,
                'msg'                       => '失败信息：' . $exception->getMessage(),
                'line'                      => '失败行数：' . $exception->getLine(),
                'file'                      => '失败文件：' . $exception->getFile(),
                'mapping_status'            => $match_status ? '匹配成功' : '匹配失败',
            ];

            LogHelper::setExceptionLog($exception_data,$this->exceptionTask);
            $exceptionDing ['type'] = 'task';
            $dingPushData ['task'] = 'Amazon订单信息处理2';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = 'saveOrder';
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($exception_data,null,$exceptionDing ['type']);
        }
        return true;
    }


    /**
     *         $feed = <<<EOD
    <?xml version="1.0" encoding="UTF-8"?>
    <AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>{$this->sale_info['sellerId']}</MerchantIdentifier>
    </Header>
    <MessageType>OrderFulfillment</MessageType>
    <Message>
    <MessageID>1</MessageID>
    <!--<OperationType>Update</OperationType>-->
    <OrderFulfillment>
    <AmazonOrderID>{$param ['AmazonOrderID']}</AmazonOrderID>
    <FulfillmentDate>2009-07-22T23:59:59-07:00</FulfillmentDate>
    <FulfillmentData>
    <CarrierName>{$param['CarrierName']}</CarrierName>
    <ShippingMethod>{$param['ShippingMethod']}</ShippingMethod>
    <ShipperTrackingNumber>{$param['ShipperTrackingNumber']}</ShipperTrackingNumber>
    </FulfillmentData>
    <Item>
    <AmazonOrderItemCode>{$param['AmazonOrderItemCode']}</AmazonOrderItemCode>
    <Quantity>{$param['Quantity']}</Quantity>
    </Item>
    </OrderFulfillment>
    </Message>
    </AmazonEnvelope>
    EOD;

     */
    /**
     * Note: 亚马逊物流跟踪号回传 上传物流信息
     * Data: 2019/5/28 18:12
     * Author: zt7785
     */
    public function submitFeedShipping ($param) {
        //MerchantIdentifier :　此选项可以随便填写
        // ShippingMethod ： 根据自己的需求可以有可以没有，
        // 如果要确认多个订单可以增加多个<message>

        //MessageType :　OrderFulfillment　订单配送
//        订单执行提要允许您的系统使用订单执行信息更新Amazon的系统。亚马逊
//将信息发布到客户的Amazon帐户中，以便客户可以查看发货状态。
//一旦您发送了订单，请向Amazon发送带有履行信息的发货确认。如果你发货
//使用可跟踪的发货方法订购，在提要中包含跟踪号。Amazon提供标准的发货人
//代码(承运人代码)以及自由文本字段，以便您可以输入不同的托运人。
//这个提要很重要，因为它向亚马逊发出信号，让它向买家收取费用，并将其记入你的市场支付账户，
//并通知买方订单正在途中。如果亚马逊没有收到确认在30天内
//下订单后，订单将自动取消，您将不会得到订单的付款。
        /*
         *
            <?xml version="1.0" encoding="UTF-8"?>
             <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
              <Header>
               <DocumentVersion>1.01</DocumentVersion>
               <MerchantIdentifier>MYID</MerchantIdentifier>
              </Header>
              <MessageType>OrderFulfillment</MessageType>
              <Message>
               <MessageID>1</MessageID>
               <OrderFulfillment>
               <AmazonOrderID>XXXXXXXXXXXXXXXX</AmazonOrderID>
               <FulfillmentDate>2012-14-12T11:00:00</FulfillmentDate>
               <FulfillmentData>
               <CarrierName>USPS</CarrierName>
               <ShippingMethod>Standard</ShippingMethod>
               <ShipperTrackingNumber>XXXXXXXXXXXXXXXXXXX</ShipperTrackingNumber>
               </FulfillmentData>
               <Item>
                <AmazonOrderItemCode>1234567890123456789</AmazonOrderItemCode>
                <Quantity>1</Quantity>
               </Item>
              </OrderFulfillment>
             </Message>
         */
        //Inventory 商品库存更新
        /**
                 *<?xml version="1.0" ?><AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
                <Header>
                <DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>A3QPCC6I4V1QU3</MerchantIdentifier>
                </Header>
                <MessageType>Inventory</MessageType>
                <Message>
                <MessageID>1</MessageID>
                <OperationType>Update</OperationType>
                <Inventory>
                <SKU>6000013953</SKU>
                <Quantity>1</Quantity>
                </Inventory>
                </Message>
                </AmazonEnvelope>
         */
        //Product 商品刊登
        /*
                 *<?xml version="1.0" encoding="utf-8"?>
        <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
          <Header>
            <DocumentVersion>1.01</DocumentVersion>
            <MerchantIdentifier>M_EXAMPLE_123456</MerchantIdentifier>
          </Header>
          <MessageType>Product</MessageType>
          <PurgeAndReplace>false</PurgeAndReplace>
          <Message>
            <MessageID>1</MessageID>
            <OperationType>Update</OperationType>
            <Product>
              <SKU>56789</SKU>
              <StandardProductID>
                <Type>ASIN</Type>
                <Value>B0EXAMPLEG</Value>
              </StandardProductID>
              <ProductTaxCode>A_GEN_NOTAX</ProductTaxCode>
              <DescriptionData>
                <Title>Example Product Title</Title>
                <Brand>Example Product Brand</Brand>
                <Description>This is an example product description.</Description>
                <BulletPoint>Example Bullet Point 1</BulletPoint>
                <BulletPoint>Example Bullet Point 2</BulletPoint>
                <MSRP currency="USD">25.19</MSRP>
                <Manufacturer>Example Product Manufacturer</Manufacturer>
                <ItemType>example-item-type</ItemType>
              </DescriptionData>
              <ProductData>
                <Health>
                  <ProductType>
                    <HealthMisc>
                      <Ingredients>Example Ingredients</Ingredients>
                      <Directions>Example Directions</Directions>
                    </HealthMisc>
                  </ProductType>
                </Health>
              </ProductData>
            </Product>
          </Message>
        </AmazonEnvelope>
         */
        //OrderAcknowledgment 订单确认
        /*
             * <AmazonEnvelope
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
      <Header>
        <DocumentVersion>1.01</DocumentVersion>
        <MerchantIdentifier>F85S4E7G4FSE98</MerchantIdentifier>
       </Header>
       <MessageType>OrderAcknowledgment</MessageType>
       <Message>
        <MessageID>1</MessageID>
        <OrderAcknowledgment>
          <AmazonOrderID>654-8547853-2598634</AmazonOrderID>
          <MerchantOrderID>658795124</MerchantOrderID>
          <StatusCode>Success</StatusCode>
          <Item>
            <AmazonOrderItemCode>35287489587654</AmazonOrderItemCode>
            <MerchantOrderItemID>587487</MerchantOrderItemID>
            <AmazonOrderItemCode>35287489587655</AmazonOrderItemCode>
            <MerchantOrderItemID>587488</MerchantOrderItemID>
          </Item>
        </OrderAcknowledgment>
      </Message>
    </AmazonEnvelope>
         */
        /**
         *
         * CarrierName 承运人名称-航运承运人名称
            ShippingMethod 配送方式 —用于交付项目的装运方法
         */
        $i = 1;
        foreach ($param as $items) {
            foreach ($items ['item'] as $item) {
                $feed['Message'][] = [
                    'MessageID' => $i,
                    'OrderFulfillment' => [
                        'AmazonOrderID' => $items ['logistics_info'] ['plat_order_number'],
                        'FulfillmentDate' => $this->getFormatTimes($items ['logistics_info'] ['logistics_time']),//发货时间
                        'FulfillmentData'=>[
                            'CarrierName'=>$items ['logistics_info'] ['logistics'],
                            'ShippingMethod'=>$items ['logistics_info'] ['logistics'],
                            'ShipperTrackingNumber' =>$items ['logistics_info'] ['tracking_no'],
                        ],
                        'Item' => [
                            'AmazonOrderItemCode'=>$item ['OrderItemId'],
                            'Quantity'=>$item ['quantity'],
                        ],
                    ]
                ];
                $i ++;
            }
        }
        $feedContent = $this->arrayToXmls(
            array_merge([
                'Header' => [
                    'DocumentVersion' => 1.01,
                    'MerchantIdentifier' => $this->sale_info['sellerId']
                ],
                'MessageType' => 'OrderFulfillment',
            ], $feed)
        );

        /*   MERCHANT_ID ： * All MWS requests must contain the seller's merchant ID and
        * marketplace ID.*/
        $marketplaceIdArray = array("Id" => array($this->sale_info['MarketplaceId']));
        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feedContent);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->sale_info['sellerId'],
            'MarketplaceIdList' => $marketplaceIdArray,
            'FeedType' => '_POST_ORDER_FULFILLMENT_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
        );

        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        return $this->invokeSubmitFeed($this->MWS_API, $request);
//        $feedHandle = @fopen('php://memory', 'rw+');
//        fwrite($feedHandle, $feed);
//        rewind($feedHandle);

//        $request = new MarketplaceWebService_Model_SubmitFeedRequest();
//        $request->setMerchant(MERCHANT_ID);
//        $request->setMarketplaceIdList($marketplaceIdArray);
//        $request->setFeedType('_POST_PRODUCT_DATA_');
//        $request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
//        rewind($feedHandle);
//        $request->setPurgeAndReplace(false);
//        $request->setFeedContent($feedHandle);
//        $request->setMWSAuthToken('<MWS Auth Token>'); // Optional
//
//        rewind($feedHandle);
//
//        @fclose($feedHandle);

        //MerchantIdentifier :　此选项可以随便填写
        // ShippingMethod ： 根据自己的需求可以有可以没有，
        // 如果要确认多个订单可以增加多个<message>
        /*
         * POST /?AWSAccessKeyId=testAWSAccessKeyId
  &Action=SubmitFeed
  &Merchant=testSellerId
  &SignatureVersion=2
  &Timestamp=2015-03-24T12%3A21%3A27Z
  &Version=2009-01-01
  &Signature=JYT%2Fl5RvDXbleeUQu9051qeMiISdSGV3sALZuDbj3nQ%3D
  &SignatureMethod=HmacSHA256
  &FeedType=_POST_ORDER_FULFILLMENT_DATA_
  &PurgeAndReplace=false HTTP/1.1
Host: mws.amazonservices.com.cn
x-amazon-user-agent: AmazonJavascriptScratchpad/1.0 (Language=Javascript)
Content-MD5: o1KTS/B6+ec1reNdutpN9A==
Content-Type: text/xml
         */
/*        $str = '<?xml version="1.0" encoding="UTF-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>A2G1MIHXYJ4AZZ</MerchantIdentifier>
  </Header>
  <MessageType>OrderFulfillment</MessageType>
  <Message>
    <MessageID>1</MessageID>
    <OrderFulfillment>
      <AmazonOrderID>503-1655932-9183849</AmazonOrderID>
      <FulfillmentDate>2015-01-05T00:00:00</FulfillmentDate>
      <FulfillmentData>
        <CarrierName>ヤマト運輸</CarrierName>
        <ShippingMethod>宅急便</ShippingMethod>
        <ShipperTrackingNumber>400954712712</ShipperTrackingNumber>
      </FulfillmentData>
      <Item>
        <AmazonOrderItemCode>54773538225942</AmazonOrderItemCode>
        <Quantity>4</Quantity>
      </Item>
    </OrderFulfillment>
  </Message>
</AmazonEnvelope>';

        $feed = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Header>
        <DocumentVersion>1.01</DocumentVersion>
        <MerchantIdentifier>M_MWSTEST_49045593</MerchantIdentifier>
    </Header>
    <MessageType>OrderFulfillment</MessageType>
    <Message>
        <MessageID>1</MessageID>
        <OperationType>Update</OperationType>
        <OrderFulfillment>
            <AmazonOrderID>002-3275191-2204215</AmazonOrderID>
            <FulfillmentDate>2009-07-22T23:59:59-07:00</FulfillmentDate>
            <FulfillmentData>
                <CarrierName>Contact Us for Details</CarrierName>
                <ShippingMethod>Standard</ShippingMethod>
            </FulfillmentData>
            <Item>
                <AmazonOrderItemCode>42197908407194</AmazonOrderItemCode>
                <Quantity>1</Quantity>
            </Item>
        </OrderFulfillment>
    </Message>
</AmazonEnvelope>
EOD;
*/
    }

    /*
     *         $feed = <<<EOD
<?xml version="1.0" encoding="utf-8"?>

<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>{$this->sale_info ['sellerId']}</MerchantIdentifier>
  </Header>
  <MessageType>Product</MessageType>
  <!--衍生参数-->
  <PurgeAndReplace>true</PurgeAndReplace>
  <Message>
    <MessageID>1</MessageID>
    <OperationType>Update</OperationType>
    <Product>
      <SKU>{$param ['SellerSKU']}</SKU>
      <DescriptionData>
        <Title>{$param ['Title']}</Title>
        <Brand>{$param ['Brand']}</Brand>
        <Description>{$param ['Description']}</Description>
        <!--<BulletPoint>made in Italy</BulletPoint>  -->
        <BulletPoint>{$param ['BulletPoint']}</BulletPoint>
        <!--<BulletPoint>500 thread count</BulletPoint>  -->
        <!--<BulletPoint>plain weave (percale)</BulletPoint>  -->
        <!--<BulletPoint>100% Egyptian cotton</BulletPoint>  -->
        <Manufacturer>{$param ['Manufacturer']}</Manufacturer>
        <SearchTerms>{$param ['SearchTerms']}</SearchTerms>
        <ItemType>{$param ['ItemType']}</ItemType>
        <IsGiftWrapAvailable>{$param ['IsGiftWrapAvailable']}</IsGiftWrapAvailable>
        <IsGiftMessageAvailable>{$param ['IsGiftMessageAvailable']}</IsGiftMessageAvailable>
        <RecommendedBrowseNode>{$param ['RecommendedBrowseNode']}</RecommendedBrowseNode>
      </DescriptionData>
    </Product>
  </Message>
</AmazonEnvelope>
EOD;

     */
    /**
     * Note: 亚马逊商品上传
     * Data: 2019/6/5 17:29
     * ProductTaxCode : 用于识别产品的税务属性 不用于加拿大、欧洲或日本。
     * LaunchDate : 控制产品何时出现在亚马逊网站的搜索和浏览中
     * BulletPoint : 产品特性简介
     * Manufacturer - 制造商-产品的制造商
     * MfrPartNumber -原厂提供的零件编号
     * IsGiftWrapAvailable—指示产品是否有礼品包装
     * IsGiftMessageAvailable——指示产品是否可用礼品消息传递
     * IsDiscontinuedByManufacturer 表示制造商已停止制造
     * SearchTerms — 当客户搜索时，您提交的提供产品搜索结果的术语
        使用条款
     * Author: zt7785
     */
    public function submitFeedProduct ($param) {
        //表单参数
        //SellerSKU ** 产品编码 (UPC ASIN) **
        //物品状态 condition_type (全新 二手)
        //产品标题 title
        //产品品牌 Brand
        // 制造商 Manufacturer
        //产品颜色 产品型号 BulletPoint (产品特性)
        //关键词 SearchTerms [] 5个
        //销售价格
        //优惠价格 优惠时间区间
        //RecommendedBrowseNode和FEDAS_ID仅供欧洲商家使用
        $DescriptionDataArrKey = ['Brand','Description','BulletPoint','Manufacturer','SearchTerms','Title','RecommendedBrowseNode'];
        $DescriptionDataArr = [];
        foreach ($param as $field => $value) {
            if (in_array($field,$DescriptionDataArrKey)) {
                if (is_string($value)) {
                    $DescriptionDataArr [$field] = $value;
                } elseif (is_array($value)) {
                    foreach ($value as $childKey => $childVal){
                        $DescriptionDataArr [$field][] = $childVal;
                    }
                }
            }
        }
        $feed['Message'][] = [
            'MessageID' => 1,
            'OperationType' => 'Update',
            'Product' => [
                'SKU' => $param ['SellerSKU'],
                'DescriptionData' => [
                    $DescriptionDataArr
                ]
            ]
        ];
        $feedContent = $this->arrayToXmls(
            array_merge([
                'Header' => [
                    'DocumentVersion' => 1.01,
                    'MerchantIdentifier' => $this->sale_info['sellerId']
                ],
                'MessageType' => 'Product',
                'PurgeAndReplace' => 'true',
            ], $feed)
        );

        $marketplaceIdArray = array("Id" => array($this->sale_info['MarketplaceId']));
        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feedContent);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->sale_info['sellerId'],
            'MarketplaceIdList' => $marketplaceIdArray,
            'FeedType' => '_POST_PRODUCT_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
//            'MWSAuthToken' => '<MWS Auth Token>', // Optional
        );
        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        return $this->invokeSubmitFeed($this->MWS_API, $request);
    }

    /**
     * Note: 亚马逊商品价格编辑 可用
     * Data: 2019/6/5 17:29
     * Author: zt7785
     */
    public function submitFeedProductPrice ($param) {
        /*$feed = <<<EOD
<?xml version="1.0" encoding="utf-8"?>

<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">
  <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>{$this->sale_info ['sellerId']}</MerchantIdentifier>
  </Header>
    <MessageType>Price</MessageType>
  <Message>
    <MessageID>1</MessageID>
    <Price>
      <SKU>{$param ['SellerSKU']}</SKU>
      <StandardPrice currency="{$param ['currency_code']}">{$param ['price']}</StandardPrice>
      <!--优惠信息-->
      <Sale>
        <!--<StartDate>2008-10-01T00:00:00Z</StartDate>  -->
        <StartDate>{$param ['StartDate']}</StartDate>
        <!--<EndDate>2009-01-31T00:00:00Z</EndDate>  -->
        <EndDate>{$param ['EndDate']}</EndDate>
        <!--<SalePrice currency="USD">28.38</SalePrice> -->
        <SalePrice currency="{$param ['currency']}">{$param ['SalePrice']}</SalePrice>
      </Sale>
    </Price>
  </Message>
</AmazonEnvelope>
EOD;
*/
        $feed = <<<EOD
<?xml version="1.0" encoding="utf-8"?>

<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">  
  <Header> 
    <DocumentVersion>1.01</DocumentVersion>  
    <MerchantIdentifier>{$this->sale_info ['sellerId']}</MerchantIdentifier> 
  </Header>  
    <MessageType>Price</MessageType>
  <Message> 
    <MessageID>1</MessageID>  
    <Price> 
      <SKU>{$param ['SellerSKU']}</SKU>  
      <StandardPrice currency="{$param ['currency_code']}">{$param ['price']}</StandardPrice>  
      <!--优惠信息-->
    </Price> 
  </Message> 
</AmazonEnvelope>
EOD;

        $marketplaceIdArray = array("Id" => array($this->sale_info['MarketplaceId']));
        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->sale_info['sellerId'],
            'MarketplaceIdList' => $marketplaceIdArray,
            'FeedType' => '_POST_PRODUCT_PRICING_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
//            'MWSAuthToken' => '<MWS Auth Token>', // Optional
        );
        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        return $this->invokeSubmitFeed($this->MWS_API, $request);
    }

    /**
     * Note: 亚马逊商品图片上传
     * Data: 2019/6/5 17:29
     * Author: zt7785
     */
    public function submitFeedProductImage ($param) {

        $feed = <<<EOD
<?xml version="1.0" encoding="utf-8"?>

<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">  
  <Header> 
    <DocumentVersion>1.01</DocumentVersion>  
    <MerchantIdentifier>{$this->sale_info ['sellerId']}</MerchantIdentifier> 
  </Header>  
    <MessageType>ProductImage</MessageType>
    <Message>
        <MessageID>1</MessageID>
        <OperationType>Update</OperationType>
        <ProductImage>
            <SKU>{$param ['SellerSKU']}</SKU>
            <ImageType>Main</ImageType>
            <ImageLocation>{$param ['ImageLocation']}</ImageLocation>
        </ProductImage>
    </Message>
</AmazonEnvelope>
EOD;

        $marketplaceIdArray = array("Id" => array($this->sale_info['MarketplaceId']));
        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->sale_info['sellerId'],
            'MarketplaceIdList' => $marketplaceIdArray,
            'FeedType' => '_POST_PRODUCT_IMAGE_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
//            'MWSAuthToken' => '<MWS Auth Token>', // Optional
        );
        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        return $this->invokeSubmitFeed($this->MWS_API, $request);
    }

    /**
     * Note: 亚马逊商品库存提交　可用
     * Data: 2019/6/5 17:29
     * Author: zt7785
     */
    public function submitFeedProductInventory ($param) {
        $feed = <<<EOD
<?xml version="1.0" encoding="utf-8"?>

<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">  
  <Header> 
    <DocumentVersion>1.01</DocumentVersion>  
    <MerchantIdentifier>{$this->sale_info ['sellerId']}</MerchantIdentifier> 
  </Header>  
  <MessageType>Inventory</MessageType>  
  <Message> 
    <MessageID>1</MessageID>  
    <OperationType>Update</OperationType>  
    <Inventory> 
      <SKU>{$param ['SellerSKU']}</SKU>  
      <Quantity>{$param ['Quantity']}</Quantity>  
    </Inventory> 
  </Message>  
</AmazonEnvelope>
EOD;
        //      <!--<FulfillmentLatency>1</FulfillmentLatency> -->

        /*
         * 库存上传数据（商品、价格、库存、关系、图片或配送修改上传数据）以及订单上传数据均单独处理；但可同时提交这两种上传数据。
_POST_PRODUCT_DATA_ 上传数据可以与价格、库存和其他 XML 上传数据同时处理。 但是，如果价格、库存和其他上传数据引用了商品上传数据尚未完成处理的 SKU，则处理这些上传数据将失败。您应在完成商品上传数据后，再按顺序处理价格、库存和图片更新。
可以同时提交所有库存上传数据（除 _POST_PRODUCT_DATA_ 之外）。例如，可以同时提交所有价格、库存状况、关系和图片上传数据。
系统将按顺序处理相同类型的上传数据。这适用于所有库存上传数据类型。例如，如果您提交两份价格上传数据，则每次只能处理一份上传数据。
优化上传数据的提交内容。每隔几秒钟就上传很多份较小的上传数据是非常低效的，还可能导致系统任务积压，从而妨碍处理其他上传数据，并强制您取消某些之前提交的上传数据。
         */

        /*   MERCHANT_ID ： * All MWS requests must contain the seller's merchant ID and
        * marketplace ID.*/
        $marketplaceIdArray = array("Id" => array($this->sale_info['MarketplaceId']));
        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->sale_info['sellerId'],
            'MarketplaceIdList' => $marketplaceIdArray,
            'FeedType' => '_POST_INVENTORY_AVAILABILITY_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
//            'MWSAuthToken' => '<MWS Auth Token>', // Optional
        );
        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        return $this->invokeSubmitFeed($this->MWS_API, $request);
    }

    /**
     * Note: 返回过去 90 天内提交的所有上传数据提交列表。
     * Data: 2019/5/29 15:21
     * Author: zt7785
     */
    public function getFeedSubmissionList($SubmissionId = '')
    {
        // _POST_PRODUCT_DATA_ _POST_FLAT_FILE_LISTINGS_DATA_
        if ($SubmissionId){
            $parameters = array (
                'Merchant' => $this->sale_info['sellerId'],
                'FeedProcessingStatusList' => array ('Status' => array ('_SUBMITTED_')),
                'FeedSubmissionIdList' => array ('Id' => array ($SubmissionId)),
            );
        } else {
            $parameters = array (
                'Merchant' => $this->sale_info['sellerId'],
                'FeedProcessingStatusList' => array ('Status' => array ('_SUBMITTED_')),
                'FeedSubmissionIdList' => [],
            );
        }
        $request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest($parameters);
        $response =  $this->invokeGetFeedSubmissionList($this->MWS_API, $request);
        if (empty($response ['exception_Info'])) {
            return ['RequestId'=>$response ['data'] ['RequestId'],'FeedSubmissionInfo'=> $response ['data'] ['FeedSubmissionInfo']]; //['FeedSubmissionInfo'] array Feed处理结果信息
        } else {
            return ['exception_Info'=>$response ['exception_Info']];
        }
    }


    /**
     * Note: 返回上传数据处理报告及 Content-MD5 标头。
     * Data: 2019/5/29 16:58
     * Author: zt7785
     */
    public function getFeedSubmissionResult($SubmissionId)
    {
        $query = [
            'FeedSubmissionId' => $SubmissionId
        ];
        $endPoint = MWSEndPoint::get('GetFeedSubmissionResult');
        $body = null; $raw = false;
        $merge = [
            'Timestamp' => gmdate(self::DATE_FORMAT, time()),
            'AWSAccessKeyId' => $this->sale_info['license_key'],
            'Action' => $endPoint['action'],
            //'MarketplaceId.Id.1' => $this->config['Marketplace_Id'],
            'SellerId' => $this->sale_info['sellerId'],
            'SignatureMethod' => self::SIGNATURE_METHOD,
            'SignatureVersion' => self::SIGNATURE_VERSION,
            'Version' => $endPoint['date'],
        ];

        $query = array_merge($merge, $query);

        if (!isset($query['MarketplaceId.Id.1'])) {
            $query['MarketplaceId.Id.1'] = $this->sale_info['MarketplaceId'];
        }

        if (isset($this->sale_info['MWSAuthToken']) && !is_null($this->sale_info['MWSAuthToken']) and $this->sale_info['MWSAuthToken'] != "") {
            $query['MWSAuthToken'] = $this->sale_info['MWSAuthToken'];
        }

        if (isset($query['MarketplaceId'])) {
            unset($query['MarketplaceId.Id.1']);
        }

        if (isset($query['MarketplaceIdList.Id.1'])) {
            unset($query['MarketplaceId.Id.1']);
        }

        try{

            $headers = [
                'Accept' => 'application/xml',
                'x-amazon-user-agent' => self::APPLICATION_NAME . '/' . self::APPLICATION_VERSION
            ];

            if ($endPoint['action'] === 'SubmitFeed') {
                $headers['Content-MD5'] = base64_encode(md5($body, true));
                $headers['Content-Type'] = 'text/xml; charset=iso-8859-1';
                $headers['Host'] = $this->config['Region_Host'];

                unset(
                    $query['MarketplaceId.Id.1'],
                    $query['SellerId']
                );
            }

            $requestOptions = [
                'headers' => $headers,
                'body' => $body
            ];

            ksort($query);

            $query['Signature'] = base64_encode(
                hash_hmac(
                    'sha256',
                    $endPoint['method']
                    . "\n"
                    . $this->config['Region_Host']
                    . "\n"
                    . $endPoint['path']
                    . "\n"
                    . http_build_query($query, null, '&', PHP_QUERY_RFC3986),
                    $this->sale_info['service_secret'],
                    true
                )
            );

            $requestOptions['query'] = $query;

            if($this->client === NULL) {
                $this->client = new Client();
            }
            $response = $this->client->request(
                $endPoint['method'],
                $this->config['Region_Url'] . $endPoint['path'],
                $requestOptions
            );
            $body = (string) $response->getBody();
            $result = '';
            if ($raw) {
                $result =  $body;
            } else if (strpos(strtolower($response->getHeader('Content-Type')[0]), 'xml') !== false) {
                $result =  $this->xmlToArray($body);
            } else {
                $result =  $body;
            }

            if (isset($result['Message']['ProcessingReport'])) {
                return $result['Message']['ProcessingReport'];
            } else {
                return $result;
            }

        } catch (BadResponseException $e) {
            if ($e->hasResponse()) {
                $message = $e->getResponse();
                $message = $message->getBody();
                if (strpos($message, '<ErrorResponse') !== false) {
                    $error = simplexml_load_string($message);
                    $message = $error->Error->Message;
                }
            } else {
                $message = 'An error occured';
            }
            throw new \Exception($message);
        }


//        $parameters = array (
//            'Merchant' => $this->sale_info['sellerId'],
////            'FeedSubmissionId' => '58056018045',
//            'FeedSubmissionId' => '58250018052',
//            'FeedSubmissionResult' => @fopen('php://memory', 'rw+'),
////            'MWSAuthToken' => '<MWS Auth Token>', // Optional
//        );
//
//        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest($parameters);

        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $request->setMerchant($this->sale_info['sellerId']);
//        $request->setFeedSubmissionId('58456018059');
        $request->setFeedSubmissionId($SubmissionId);
//        $request->setFeedSubmissionId('58056018045');
        $request->setFeedSubmissionResult(@fopen('php://memory', 'rw+'));
////        $request->setMWSAuthToken('<MWS Auth Token>'); // Optional
        return $this->invokeGetFeedSubmissionResult($this->MWS_API, $request);
    }

    public function getFeedSubmissionResults($SubmissionId)
    {

//        $parameters = array (
//            'Merchant' => $this->sale_info['sellerId'],
////            'FeedSubmissionId' => '58056018045',
//            'FeedSubmissionId' => '58250018052',
//            'FeedSubmissionResult' => @fopen('php://memory', 'rw+'),
////            'MWSAuthToken' => '<MWS Auth Token>', // Optional
//        );
//
//        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest($parameters);

        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $request->setMerchant($this->sale_info['sellerId']);
//        $request->setFeedSubmissionId('58456018059');
        $request->setFeedSubmissionId($SubmissionId);
//        $request->setFeedSubmissionId('58056018045');
        $request->setFeedSubmissionResult(@fopen('php://memory', 'rw+'));
////        $request->setMWSAuthToken('<MWS Auth Token>'); // Optional
        return $this->invokeGetFeedSubmissionResult($this->MWS_API, $request);
    }

    public function cancelFeedSubmissions (string $Submission_id) {
        $options = array (
          'Merchant' => $this->sale_info['sellerId'],
          'FeedSubmissionIdList' => array ('Id' => $Submission_id),
        );

        $request = new MarketplaceWebService_Model_CancelFeedSubmissionsRequest($options);

//         Or the request can be constructed like
//        $request = new MarketplaceWebService_Model_CancelFeedSubmissionsRequest();
//        $request->setMerchant(MERCHANT_ID);
//
//        $idList = new MarketplaceWebService_Model_IdList();
//        $request->setFeedSubmissionIdList($idList->withId('<Feed Submission Id>'));
//        $request->setMWSAuthToken('<MWS Auth Token>'); // Optional
        return $this->invokeCancelFeedSubmissions($this->MWS_API, $request);
    }
    /**
     * @param MarketplaceWebService_Interface $service
     * @param $request
     * Note: submitFeed数据处理
     * Data: 2019/5/29 14:58
     * Author: zt7785
     */
    function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request)
    {
        $responseData ['data'] ['FeedSubmissionId'] = '';
        $responseData ['data'] ['FeedType'] = '';
        $responseData ['data'] ['SubmittedDate'] = '';
        $responseData ['data'] ['FeedProcessingStatus'] = '';
        $responseData ['data'] ['StartedProcessingDate'] = '';
        $responseData ['data'] ['CompletedProcessingDate'] = '';

        $responseData ['data'] ['ResponseMetadata'] = '';
        $responseData ['data'] ['RequestId']  = '';
        $responseData ['data'] ['ResponseHeaderMetadata']  = '';
        $responseData ['exception_Info'] = [];
        try {
            $response = $service->submitFeed($request);

            if ($response->isSetSubmitFeedResult()) {
                $submitFeedResult = $response->getSubmitFeedResult();
                if ($submitFeedResult->isSetFeedSubmissionInfo()) {
                    $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                    if ($feedSubmissionInfo->isSetFeedSubmissionId())
                    {
                        $responseData ['data'] ['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();
                    }
                    if ($feedSubmissionInfo->isSetFeedType())
                    {
                        $responseData ['data'] ['FeedType'] = $feedSubmissionInfo->getFeedType();
                    }
                    if ($feedSubmissionInfo->isSetSubmittedDate())
                    {
                        $responseData ['data'] ['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(self::DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetFeedProcessingStatus())
                    {
                        $responseData ['data'] ['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
                    }
                    if ($feedSubmissionInfo->isSetStartedProcessingDate())
                    {
                        $responseData ['data'] ['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(self::DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetCompletedProcessingDate())
                    {
                        $responseData ['data'] ['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(self::DATE_FORMAT);
                    }
                }
            }

            if ($response->isSetResponseMetadata()) {
                $responseMetadata = $response->getResponseMetadata();
                $responseData ['data'] ['ResponseMetadata'] = $responseMetadata;
                if ($responseMetadata->isSetRequestId())
                {
                    $responseData ['data'] ['RequestId']  = $responseMetadata->getRequestId();
                }
            }
            $responseData ['data'] ['ResponseHeaderMetadata']  = $response->getResponseHeaderMetadata();
        } catch (MarketplaceWebService_Exception $ex) {
            $responseData ['exception_Info'] ['msg']= $ex->getMessage();
            $responseData ['exception_Info'] ['status_code']= $ex->getStatusCode();
            $responseData ['exception_Info'] ['error_code']= $ex->getErrorCode();
            $responseData ['exception_Info'] ['error_type']= $ex->getErrorType();
            $responseData ['exception_Info'] ['request_id']= $ex->getRequestId();
            $responseData ['exception_Info'] ['xml']= $ex->getXML();
            $responseData ['exception_Info'] ['response_header_metadata']= $ex->getResponseHeaderMetadata();
        }
        return $responseData;
    }


    /**
     * @param MarketplaceWebService_Interface $service
     * @param $request
     * Note: GetFeedSubmissionList数据处理
     * Data: 2019/5/29 15:15
     * Author: zt7785
     */
    function invokeGetFeedSubmissionList(MarketplaceWebService_Interface $service, $request)
    {
        $responseData ['data'] ['GetFeedSubmissionListResult'] = '';
        $responseData ['data'] ['NextToken'] = '';
        $responseData ['data'] ['HasNext'] = '';
        $responseData ['data'] ['FeedSubmissionInfo'] = '';
        $responseData ['data'] ['ResponseMetadata'] = '';
        $responseData ['data'] ['RequestId']  = '';
        $responseData ['data'] ['ResponseHeaderMetadata']  = '';
        $responseData ['exception_Info'] = [];
        try {
            $response = $service->getFeedSubmissionList($request);
            if ($response->isSetGetFeedSubmissionListResult()) {
                $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();
                $responseData ['data'] ['GetFeedSubmissionListResult'] = $getFeedSubmissionListResult;
                if ($getFeedSubmissionListResult->isSetNextToken())
                {
                    $responseData ['data'] ['NextToken'] = $getFeedSubmissionListResult->getNextToken();
                }
                if ($getFeedSubmissionListResult->isSetHasNext())
                {
                    $responseData ['data'] ['HasNext'] = $getFeedSubmissionListResult->getHasNext();
                }
                $feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();
                foreach ($feedSubmissionInfoList as $feedSubmissionInfoKey => $feedSubmissionInfo) {
                    if ($feedSubmissionInfo->isSetFeedSubmissionId())
                    {
                        $responseData ['data'] ['FeedSubmissionInfo'] [$feedSubmissionInfoKey] ['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();
                    }
                    if ($feedSubmissionInfo->isSetFeedType())
                    {
                        $responseData ['data'] ['FeedSubmissionInfo'] [$feedSubmissionInfoKey] ['FeedType'] = $feedSubmissionInfo->getFeedType();
                    }
                    if ($feedSubmissionInfo->isSetSubmittedDate())
                    {
                        $responseData ['data'] ['FeedSubmissionInfo'] [$feedSubmissionInfoKey] ['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(self::DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetFeedProcessingStatus())
                    {
                        $responseData ['data'] ['FeedSubmissionInfo'] [$feedSubmissionInfoKey] ['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
                    }
                    if ($feedSubmissionInfo->isSetStartedProcessingDate())
                    {
                        $responseData ['data'] ['FeedSubmissionInfo'] [$feedSubmissionInfoKey] ['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(self::DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetCompletedProcessingDate())
                    {
                        $responseData ['data'] ['FeedSubmissionInfo'] [$feedSubmissionInfoKey] ['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(self::DATE_FORMAT);
                    }
                }
            }
            if ($response->isSetResponseMetadata()) {
                $responseMetadata = $response->getResponseMetadata();
                $responseData ['data'] ['ResponseMetadata'] = $responseMetadata;
                if ($responseMetadata->isSetRequestId())
                {
                    $responseData ['data'] ['RequestId']  = $responseMetadata->getRequestId();
                }
            }
            $responseData ['data'] ['ResponseHeaderMetadata']  = $response->getResponseHeaderMetadata();
        } catch (MarketplaceWebService_Exception $ex) {
            $responseData ['exception_Info'] ['msg']= $ex->getMessage();
            $responseData ['exception_Info'] ['status_code']= $ex->getStatusCode();
            $responseData ['exception_Info'] ['error_code']= $ex->getErrorCode();
            $responseData ['exception_Info'] ['error_type']= $ex->getErrorType();
            $responseData ['exception_Info'] ['request_id']= $ex->getRequestId();
            $responseData ['exception_Info'] ['xml']= $ex->getXML();
            $responseData ['exception_Info'] ['response_header_metadata']= $ex->getResponseHeaderMetadata();
        }
        return $responseData;
    }

    /**
     * @param MarketplaceWebService_Interface $service
     * @param $request
     * Note: getFeedSubmissionResult数据处理
     * Data: 2019/5/29 16:26
     * Author: zt7785
     */
    function invokeGetFeedSubmissionResult(MarketplaceWebService_Interface $service, $request)
    {
        $responseData ['data'] ['GetFeedSubmissionResult'] = '';
        $responseData ['data'] ['ContentMd5'] = '';
        $responseData ['data'] ['ResponseMetadata'] = '';
        $responseData ['data'] ['RequestId']  = '';
        $responseData ['exception_Info'] = [];
//        $filename = __DIR__.'/file.xml';
//        $handle = fopen($filename, 'w+');
//        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
//        $request->setMerchant(MERCHANT_ID);
//        $request->setFeedSubmissionId(ID_TO_CHANGE);
//        $request->setFeedSubmissionResult($handle);

        try {
            $response = $service->getFeedSubmissionResult($request);
            if ($response->isSetGetFeedSubmissionResultResult()) {
                $getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult();
                $responseData ['data'] ['GetFeedSubmissionResult'] = $getFeedSubmissionResultResult;
                if ($getFeedSubmissionResultResult->isSetContentMd5()) {
                    $responseData ['data'] ['ContentMd5'] = $getFeedSubmissionResultResult->getContentMd5();
                }
            }
            if ($response->isSetResponseMetadata()) {
                $responseMetadata = $response->getResponseMetadata();
                $responseData ['data'] ['ResponseMetadata'] = $responseMetadata;
                if ($responseMetadata->isSetRequestId())
                {
                    $responseData ['data'] ['RequestId']  = $responseMetadata->getRequestId();
                }
            }
        } catch (MarketplaceWebService_Exception $ex) {
            $responseData ['exception_Info'] ['msg']= $ex->getMessage();
            $responseData ['exception_Info'] ['status_code']= $ex->getStatusCode();
            $responseData ['exception_Info'] ['error_code']= $ex->getErrorCode();
            $responseData ['exception_Info'] ['error_type']= $ex->getErrorType();
            $responseData ['exception_Info'] ['request_id']= $ex->getRequestId();
            $responseData ['exception_Info'] ['xml']= $ex->getXML();
            $responseData ['exception_Info'] ['response_header_metadata']= $ex->getResponseHeaderMetadata();
        }
        return $responseData;
    }

    /**
     * @param MarketplaceWebService_Interface $service
     * @param $request
     * Note: 取消CancelFeedSubmissions数据处理
     * Data: 2019/6/12 14:21
     * Author: zt7785
     */
    function invokeCancelFeedSubmissions(MarketplaceWebService_Interface $service, $request)
    {
        $responseData ['data'] ['CancelFeedSubmissionsInfo'] = [];
        $responseData ['data'] ['FeedSubmissionsCount'] = 0;
        $responseData ['data'] ['ResponseMetadata'] = '';
        $responseData ['data'] ['ResponseHeaderMetadata'] = '';
        $responseData ['data'] ['RequestId']  = '';
        $responseData ['exception_Info'] = [];
        try {
            $response = $service->cancelFeedSubmissions($request);
            if ($response->isSetCancelFeedSubmissionsResult()) {
                $cancelFeedSubmissionsResult = $response->getCancelFeedSubmissionsResult();
                if ($cancelFeedSubmissionsResult->isSetCount())
                {
                    $responseData ['data'] ['FeedSubmissionsCount'] = $cancelFeedSubmissionsResult->getCount();
                }
                $feedSubmissionInfoList = $cancelFeedSubmissionsResult->getFeedSubmissionInfoList();
                foreach ($feedSubmissionInfoList as $key => $feedSubmissionInfo) {
                    if ($feedSubmissionInfo->isSetFeedSubmissionId())
                    {
                        $responseData ['data'] ['CancelFeedSubmissionsInfo'][$key]['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();
                    }
                    if ($feedSubmissionInfo->isSetFeedType())
                    {
                        $responseData ['data'] ['CancelFeedSubmissionsInfo'][$key]['FeedType'] = $feedSubmissionInfo->getFeedType();
                    }
                    if ($feedSubmissionInfo->isSetSubmittedDate())
                    {
                        $responseData ['data'] ['CancelFeedSubmissionsInfo'][$key]['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(self::DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetFeedProcessingStatus())
                    {
                        $responseData ['data'] ['CancelFeedSubmissionsInfo'][$key]['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
                    }
                    if ($feedSubmissionInfo->isSetStartedProcessingDate())
                    {
                        $responseData ['data'] ['CancelFeedSubmissionsInfo'][$key]['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(self::DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetCompletedProcessingDate())
                    {
                        $responseData ['data'] ['CancelFeedSubmissionsInfo'][$key]['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(self::DATE_FORMAT);
                    }
                }
            }
            if ($response->isSetResponseMetadata()) {
                $responseMetadata = $response->getResponseMetadata();
                $responseData ['data'] ['ResponseMetadata'] = $responseMetadata;
                if ($responseMetadata->isSetRequestId())
                {
                    $responseData ['data'] ['RequestId']  = $responseMetadata->getRequestId();
                }
            }
            $responseData ['data'] ['ResponseHeaderMetadata'] = $response->getResponseHeaderMetadata();
        } catch (MarketplaceWebService_Exception $ex) {
            $responseData ['exception_Info'] ['msg']= $ex->getMessage();
            $responseData ['exception_Info'] ['status_code']= $ex->getStatusCode();
            $responseData ['exception_Info'] ['error_code']= $ex->getErrorCode();
            $responseData ['exception_Info'] ['error_type']= $ex->getErrorType();
            $responseData ['exception_Info'] ['request_id']= $ex->getRequestId();
            $responseData ['exception_Info'] ['xml']= $ex->getXML();
            $responseData ['exception_Info'] ['response_header_metadata']= $ex->getResponseHeaderMetadata();
        }
        return $responseData;
    }
    /**
     * 将数组转换为xml
     * Convert an array to xml
     * @param $array array to convert
     * @param $customRoot [$customRoot = 'AmazonEnvelope']
     * @return sting
     */
    private function arrayToXmls(array $array, $customRoot = 'AmazonEnvelope')
    {
        return ArrayToXml::convert($array, $customRoot);
    }
}
