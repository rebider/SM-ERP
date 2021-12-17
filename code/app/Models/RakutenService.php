<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class RakutenService
{
    const ORDER_STATUS_NEW = 1;
    const ORDER_STATUS_NO_PAY = 2;
    const ORDER_STATUS_UN_SHIPPED = 3;
    const ORDER_STATUS_SHIPPED = 4;
    const ORDER_STATUS_DONE = 5;
    const ORDER_STATUS_HOLD = 5;

    protected $appKey;
    protected $appSecret;
    protected $authKey;
    //V1 shop.categorysets.get
    protected $wsdlUrl = 'https://api.rms.rakuten.co.jp/es/1.0/order/ws?WSDL';
    //V2
    protected $url = 'https://api.rms.rakuten.co.jp/es/2.0/';

    //商品API_URL
    protected $itemUrl = 'https://api.rms.rakuten.co.jp/es/1.0/';

    protected $sale_info = null;//客户信息

    /*
     * @var 异常类型
     */
    public $exceptionTask = 'orders';

    public $exceptionAPI = 'orders_api';

    public $exceptionItemAPI = 'orders_item';
    /**
     * @var array 物流跟踪号回传响应信息
     */
    public $order_shipping_response = [
        'finish'=>[
            '発送情報の更新設定が完了しました。',
            '発送情報の追加設定が完了しました。',
            ],
        'cancel'=>[
          '発送情報の削除設定が完了しました。'
        ],
        'exception'=>[
            'shippingDetailIdの書式が不正です。',
            'deliveryCompanyの書式が不正です。',
        ]
    ];
    /**
     * @var array 物流跟踪号回传响应code
     */
    public $order_shipping_response_code = [
        'finish'=>[
            'ORDER_EXT_API_UPDATE_ORDERSHIPPING_INFO_102',
            'ORDER_EXT_API_UPDATE_ORDERSHIPPING_INFO_101',
            ],
        'cancel'=>[
          'ORDER_EXT_API_UPDATE_ORDERSHIPPING_INFO_103'
        ],
        'exception'=>[
            'ORDER_EXT_API_UPDATE_ORDERSHIPPING_ERROR_011',
        ]
    ];

    public function __construct(array $sale_info = [])
    {
        if (empty($sale_info)) {
            $sale_info ['appKey'] = Config('common.sumaoTestRakutenSecret.appKey');
            $sale_info ['appSecret'] = Config('common.sumaoTestRakutenSecret.appSecret');
        }
        $this->sale_info = $sale_info;
    }

    /**
     * @param $paramData json数组
     * {
    "dateType": 1, 1：订单日期 2：订单确认日期 3：订单确认日期 4：发货日期 5：发货完成通知日期 6：结算日期
    "startDatetime": "2019-04-04T00:00:00+0900", 格林威治时间 +0900(东京时区) $Timestamp = gmdate('Y-m-d\TH:i:s', time());
    "endDatetime": "'.$Timestamp.'+0900",
    "orderProgressList": [
     * 100：等待订单确认 200：Rakuten处理 300：等待装运 400：等待更改确认 500：装运
     * 600：支付正在进行 700：支付已完成 800：等待取消确认 900：取消确认
       100,300
    ]
    }
     * @return mixed|null jsonString
     * {"orderNumberList":["365811-20190407-00001811"],"MessageModelList":[{"messageType":"INFO","messageCode":"ORDER_EXT_API_SEARCH_ORDER_INFO_101","message":"注文検索に成功しました。"}],"PaginationResponseModel":{"totalRecordsAmount":1,"totalPages":1,"requestPage":1}}
     * Note: 订单查询
     * Data: 2019/4/9 14:47
     * Author: zt7785
     */
    public function searchOrder($paramData)
    {
        $result = null;
        try {
            $curl = new HttpCurl();
            $header = $this->getHeader($this->sale_info);
            $url = $this->url.'order/searchOrder/';
            $curl->setHeader($header);
            $curl->setParams($paramData);
            $result = $curl->post($url,'json');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '订单查询失败';
            $exception_data = [
                'request_header'      => $header,
                'api_url'      => $url,
                'request_params'      => $paramData,
                'sale_info'      => json_encode($this->sale_info),
                'msg'                       => '失败信息：' . $e->getMessage(),
                'line'                      => '失败行数：' . $e->getLine(),
                'file'                      => '失败文件：' . $e->getFile(),
            ];
            LogHelper::setExceptionLog($exception_data,$this->exceptionAPI);

            $exceptionDing ['type'] = 'api';
            $dingPushData ['task'] = 'Rakuten订单接口请求';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = 'searchOrder';
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($result,$exception_data,$exceptionDing ['type']);
        }
        return $result;
    }

    /**
     * @param $orderCodeList Array  ["xxx-xxxx-xxxx","xxx-xxxx-xxxxxxx"]
     * @return mixed|null jsonString
     * {"MessageModelList":[{"messageType":"INFO","messageCode":"ORDER_EXT_API_GET_ORDER_INFO_101","message":"受注情報取得に成功しました。(取得件数1件)"}],"OrderModelList":[{"orderNumber":"365811-20190406-00003803","orderProgress":500,"subStatusId":null,"subStatusName":null,"orderDatetime":"2019-04-06T22:20:28+0900","shopOrderCfmDatetime":"2019-04-06T22:20:39+0900","orderFixDatetime":"2019-04-06T22:51:19+0900","shippingInstDatetime":"2019-04-06T22:51:19+0900","shippingCmplRptDatetime":"2019-04-09T00:32:36+0900","cancelDueDate":null,"deliveryDate":null,"shippingTerm":0,"remarks":"[離島一部地域の配送料金について:]\n※離島・一部地域は追加送料がかかる場合があります。\n","giftCheckFlag":0,"severalSenderFlag":0,"equalSenderFlag":1,"isolatedIslandFlag":0,"rakutenMemberFlag":1,"carrierCode":21,"emailCarrierCode":0,"orderType":1,"reserveNumber":null,"reserveDeliveryCount":null,"cautionDisplayType":0,"rakutenConfirmFlag":0,"goodsPrice":2598,"goodsTax":0,"postagePrice":0,"deliveryPrice":0,"totalPrice":2598,"requestPrice":2598,"couponAllTotalPrice":0,"couponShopPrice":0,"couponOtherPrice":0,"asurakuFlag":0,"drugFlag":0,"dealFlag":0,"membershipType":0,"memo":null,"operator":null,"mailPlugSentence":null,"modifyFlag":0,"isTaxRecalc":1,"OrdererModel":{"zipCode1":"480","zipCode2":"0305","prefecture":"愛知県","city":"春日井市","subAddress":"坂下町４丁目２８１ー１０","familyName":"野下","firstName":"昭","familyNameKana":"ノシタ","firstNameKana":"アキラ","phoneNumber1":"0568","phoneNumber2":"88","phoneNumber3":"6763","emailAddress":"7e0de3039bfb1b999d4b757c6b0bdfc1s1@pc.fw.rakuten.ne.jp","sex":"男","birthYear":1960,"birthMonth":12,"birthDay":3},"SettlementModel":{"settlementMethod":"クレジットカード","cardName":"VISA","cardNumber":"XXXX-XXXX-XXXX-6376","cardOwner":"AKIRA NOSHITA","cardYm":"2024-01","cardPayType":0,"cardInstallmentDesc":null},"DeliveryModel":{"deliveryName":"メール便","deliveryClass":null},"PointModel":{"usedPoint":0},"WrappingModel1":null,"WrappingModel2":null,"PackageModelList":[{"basketId":383746225,"postagePrice":0,"deliveryPrice":0,"goodsTax":0,"goodsPrice":2598,"totalPrice":2598,"noshi":null,"packageDeleteFlag":0,"SenderModel":{"zipCode1":"480","zipCode2":"0305","prefecture":"愛知県","city":"春日井市","subAddress":"坂下町４丁目２８１ー１０","familyName":"野下","firstName":"昭","familyNameKana":"ノシタ","firstNameKana":"アキラ","phoneNumber1":"0568","phoneNumber2":"88","phoneNumber3":"6763"},"ItemModelList":[{"itemDetailId":383746225,"itemName":"前後9Ｈ強化ガラス 360°フルケース ミラー 全面保護 強磁力 iphone アイホン アイフォンケース 対応機種iPhone7/iPhone8/iPhone7P/iPhone8P/iphone X/iPhone XS/iPhoneXR/iPhone XS カラー展開ブラック レッド パープル ゴールド ブルー等【送料無料】","itemId":10000020,"itemNumber":"PA-PC3-XXSBR","manageNumber":"pa-pc3-","price":2598,"units":1,"includePostageFlag":1,"includeTaxFlag":1,"includeCashOnDeliveryPostageFlag":0,"selectedChoice":"機種:iphone X/XS\nカラー:Black+Red","pointRate":1,"inventoryType":2,"delvdateInfo":"3営業日以内に発送","restoreInventoryFlag":0,"deleteItemFlag":0}],"ShippingModelList":[{"shippingDetailId":139033338,"shippingNumber":"425792094324","deliveryCompany":"1003","deliveryCompanyName":"日本郵便","shippingDate":"2019-04-08"}],"DeliveryCvsModel":null}],"CouponModelList":null,"ChangeReasonModelList":[{"changeId":153888082,"changeType":8,"changeTypeDetail":null,"changeReason":null,"changeReasonDetail":null,"changeApplyDatetime":"2019-04-06T22:20:39+0900","changeFixDatetime":"2019-04-06T22:20:39+0900","changeCmplDatetime":"2019-04-06T22:20:39+0900"}]}]}
     * Note: 订单详情查询
     * Data: 2019/4/9 14:54
     * Author: zt7785
     */
    public function getOrder($orderCodeList) {
        $result = null;
        $param = json_encode(['orderNumberList'=>$orderCodeList]);
        try {
            $curl = new HttpCurl();
            $curl->setParams($param);
            $header = $this->getHeader($this->sale_info);
            $curl->setHeader($header);
            $url = $this->url.'order/getOrder/';
            $result = $curl->post($url,'json');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '订单详情查询失败';
            $exception_data = [
                'request_header'      => $header,
                'api_url'      => $url,
                'request_params'      => $param,
                'sale_info'      => json_encode($this->sale_info),
                'msg'                       => '失败信息：' . $e->getMessage(),
                'line'                      => '失败行数：' . $e->getLine(),
                'file'                      => '失败文件：' . $e->getFile(),
            ];
            LogHelper::setExceptionLog($exception_data,$this->exceptionAPI);

            $exceptionDing ['type'] = 'api';
            $dingPushData ['task'] = 'Rakuten订单详情接口请求';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = 'getOrder';
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($result,$exception_data,$exceptionDing ['type']);
        }
        return $result;
    }


    /**
     * @return bool|mixed|null
     * Note: 获取设置的分类信息
     * Data: 2019/4/9 15:32
     * Author: zt7785
     */
    public function getCategorysets() {
        $result = null;
        try {
            $curl = new HttpCurl();
            //不需要content-type
            $header = ['Authorization: ' . 'ESA ' . base64_encode($this->sale_info['appKey'] . ":" . $this->sale_info['appSecret'])];
            $curl->setHeader($header);
            $url = $this->itemUrl.'categoryapi/shop/categorysets/get';
            $result = $curl->get($url,'xml');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '商品分类设置信息查询失败';
        }
        return $result;
    }

    /**
     * @return bool|mixed|null
     * Note: 获取分类信息
     * Data: 2019/4/9 15:32
     * Author: zt7785
     */
    public function getCategories() {
        $result = null;
        try {
            $curl = new HttpCurl();
            //不需要content-type
            $header = ['Authorization: ' . 'ESA ' . base64_encode($this->sale_info['appKey'] . ":" . $this->sale_info['appSecret'])];
            $curl->setHeader($header);
            $url = $this->itemUrl.'categoryapi/shop/categories/get';
            $result = $curl->get($url,'xml');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '商品分类信息查询失败';
        }
        return $result;
    }


    /**
     * @param $cate_id
     * @return bool|mixed|null
     * Note: 获取指定分类id信息
     * Data: 2019/4/11 16:26
     * Author: zt7785
     */
    public function getCategorie($cate_id) {
        $result = null;
        $param ['categoryId'] = $cate_id;
        try {
            $curl = new HttpCurl();
            //不需要content-type
            $header = ['Authorization: ' . 'ESA ' . base64_encode($this->sale_info['appKey'] . ":" . $this->sale_info['appSecret'])];
            $curl->setHeader($header);
            $curl->setParams($param);
            $url = $this->itemUrl.'categoryapi/shop/category/get';
            $result = $curl->get($url,'xml');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '商品分类信息查询失败';
        }
        return $result;
    }


    /**
     * @return bool|mixed|null
     * Note: 获取店铺分类信息
     * Data: 2019/4/9 16:54
     * Author: zt7785
     */
    public function getShopCategorysets() {
        $result = null;
        try {
            $curl = new HttpCurl();
            //不需要content-type
            $header = ['Authorization: ' . 'ESA ' . base64_encode($this->sale_info['appKey'] . ":" . $this->sale_info['appSecret'])];
            $curl->setHeader($header);
            $url = $this->itemUrl.'shopmngt/design/category/list/get';
            $result = $curl->get($url,$header,'xml');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '商品分类信息查询失败';
        }
        return $result;
    }

    /**
     * 上传物流跟踪号
     * @param $param
     * @author dengxingtian
     * @return mixed|null
     */
    public function updateOrderShipping($param)
    {
        /*
         * {
    "orderNumber": "xxxxxx-yyyymmdd-01000001",
    "BasketidModelList": [
        {
            "basketId": "100079",
            "ShippingModelList": [
                {
                    "deliveryCompany": "1001",
                    "shippingNumber": "1234567890",
                    "shippingDate": "2018-02-17"
                }
            ]
        }
    ]
}
         */
        $result = null;
        try {
            $curl = new HttpCurl();
            $header = $this->getHeader($this->sale_info);
            $curl->setHeader($header);
            $curl->setParams($param);
            $url = $this->url.'order/updateOrderShipping/';
            $result = $curl->post($url,'json');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '回传乐天物流跟踪号失败';
        }
        return $result;
    }

    /**
     * @param $param
     * Note: 商品刊登
     * Data: 2019/5/24 14:59
     * Author: zt7785
     */
    public function itemInsert ($param) {
        //https://webservice.rms.rakuten.co.jp/merchant-portal/view?contents=/ja/common/1-1_service_index/itemapi/iteminsert
        //少下架
        $result = null;
        try {
            $curl = new HttpCurl();
            $header = $this->getHeader($this->sale_info);
            $curl->setHeader($header);
            $curl->setParams($param);
            $url = $this->itemUrl.'item/insert';
            dd($url);
            $result = $curl->post($url,'json');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '回传乐天物流跟踪号失败';
        }
        return $result;
    }


    /**
     * @param $param
     * @return mixed|null
     * Note:更新在RMS中注册的产品信息。
     * Data: 2019/5/24 15:14
     * Author: zt7785
     */
    public function itemUpdate ($param) {
        //https://webservice.rms.rakuten.co.jp/merchant-portal/view?contents=/ja/common/1-1_service_index/itemapi/itemupdate
        $result = null;
        try {
            $curl = new HttpCurl();
            $header = $this->getHeader($this->sale_info);
            $curl->setHeader($header);
            $curl->setParams($param);
            $url = $this->itemUrl.'item/update';
            $result = $curl->post($url,'json');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '回传乐天物流跟踪号失败';
        }
        return $result;
    }


    /**
     * @param $param
     * @return mixed|null
     * Note: 获取客户genre 信息
     * Data: 2019/5/24 15:05
     * Author: zt7785
     */
    public function getItemGenreId ($params) {
        $result = null;
        $param = "";
        //https://webservice.rms.rakuten.co.jp/merchant-portal/view?contents=/ja/common/1-1_service_index/navigationapi/navigationgenreget
        try {
            $curl = new HttpCurl();
            $header = $this->getHeader($this->sale_info);
            $curl->setHeader($header);
            $curl->setParams($params);
            $url = $this->itemUrl.'navigation/genre/get';
            $result = $curl->get($url,'xml');
        } catch (Exception $e) {
            $result ['exception_info'] ['msg'] = $e->getMessage();
            $result ['exception_info'] ['action_msg'] = '回传乐天物流跟踪号失败';
        }
        return $result;
    }






    /**
     * @param $saleInfo
     * @return array
     * Note: header头授权信息
     * Data: 2019/4/9 15:48
     * Author: zt7785
     */
    public function getHeader($saleInfo)
    {
        $header = [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: ' . 'ESA ' . base64_encode($saleInfo['appKey'] . ":" . $saleInfo['appSecret'])
        ];
        return $header;
    }

}
