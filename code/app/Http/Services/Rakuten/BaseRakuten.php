<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/5/28
     * Time: 15:20
     */

    namespace App\Http\Services\Rakuten;

    use App\Common\FTP;

    class BaseRakuten
    {
        //V1 shop.categorysets.get
        protected $wsdlUrl = 'https://api.rms.rakuten.co.jp/es/1.0/order/ws?WSDL';
        //V2
        protected $url = 'https://api.rms.rakuten.co.jp/es/2.0/';
        //商品API_URL
        protected $itemUrl = 'https://api.rms.rakuten.co.jp/es/1.0/';
        protected $sale_info = null;//客户信息
        const RMS_API_ITEM_INSERT                       = 'https://api.rms.rakuten.co.jp/es/1.0/item/insert';
        const RMS_API_ITEM_UPDATE                       = 'https://api.rms.rakuten.co.jp/es/1.0/item/update';
        const RMS_API_ITEM_GET                          = 'https://api.rms.rakuten.co.jp/es/1.0/item/get';
        const RMS_API_ITEM_DELETE                          = 'https://api.rms.rakuten.co.jp/es/1.0/item/delete';
        const RMS_API_CABINET_USAGE_GET                 = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/usage/get';
        const RMS_API_CABINET_FOLDERS_GET               = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/folders/get';
        const RMS_API_CABINET_FOLDERS_INSERT            = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/folder/insert';
        const RMS_API_CABINET_FILES_SEARCH              = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/files/search';
        const RMS_API_CABINET_FILE_INSERT               = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/file/insert';
        const RMS_API_CABINET_FILE_UPDATE               = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/file/update';
        const RMS_API_CABINET_FILE_DELETE               = 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/file/delete';
        const RMS_API_ORDER_GET                         = 'https://api.rms.rakuten.co.jp/es/1.0/order/ws';
        const RMS_API_ORDER_SOAP_WSDL                   = 'https://api.rms.rakuten.co.jp/es/1.0/order/ws?WSDL';
        const RMS_API_INVENTORY_SOAP_ADDRESS            = 'https://api.rms.rakuten.co.jp/es/1.0/inventory/ws';
        const RMS_API_INVENTORY_SOAP_WSDL               = 'https://inventoryapi.rms.rakuten.co.jp/rms/mall/inventoryapi';
        const RMS_API_PAYMENT_SOAP_WSDL                 = 'https://orderapi.rms.rakuten.co.jp/rccsapi-services/RCCSApiService?wsdl';
        const RMS_API_CATEGORY_SETS_GET                 = 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/categorysets/get';
        const RMS_API_CATEGORIES_GET                    = 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/categories/get';
        const RMS_API_CATEGORY_GET                      = 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/get';
        const RMS_API_CATEGORY_INSERT                   = 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/insert';
        const RMS_API_CATEGORY_UPDATE                   = 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/update';
        const RMS_API_CATEGORY_DELETE                   = 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/delete';
        const RMS_API_RAKUTEN_PAY_GET_ORDER             = 'https://api.rms.rakuten.co.jp/es/2.0/order/getOrder/';
        const RMS_API_RAKUTEN_PAY_SEARCH_ORDER          = 'https://api.rms.rakuten.co.jp/es/2.0/order/searchOrder/';
        const RMS_API_RAKUTEN_PAY_CONFIRM_ORDER         = 'https://api.rms.rakuten.co.jp/es/2.0/order/confirmOrder/';
        const RMS_API_RAKUTEN_PAY_UPDATE_ORDER_DELIVERY = 'https://api.rms.rakuten.co.jp/es/2.0/order/updateOrderDelivery/';
        const RMS_API_RAKUTEN_PAY_UPDATE_ORDER_SENDER   = 'https://api.rms.rakuten.co.jp/es/2.0/order/updateOrderSender/';
        const RMS_API_RAKUTEN_PAY_GET_PAYMENT           = 'https://api.rms.rakuten.co.jp/es/2.0/order/getPayment/';
        const RMS_API_RAKUTEN_PAY_CANCEL_ORDER          = 'https://api.rms.rakuten.co.jp/es/2.0/order/cancelOrder/';
        const RMS_API_RAKUTEN_PAY_UPDATE_ORDER_SHIPPING = 'https://api.rms.rakuten.co.jp/es/2.0/order/updateOrderShipping/';
        const RMS_IMAGE_BASE_URL                        = 'https://image.rakuten.co.jp/';
        protected $config = '';
        protected $env = '';
        protected $port = '';

        public function __construct(array $sale_info = [])
        {
            $this->sale_info = $sale_info;
            //ftp上传图片配置
            //TODO 数据库客户的 user pass
            $this->config = ['host' => '133.237.61.63'];
            //异步处理图片上传配置端口
            $this->env = config('app.env');
            if ($this->env == 'dev') {
                $this->port = 9055;
            } else if ($this->env == 'test') {
                $this->port = 62610;
            } else {
                $this->port = 80;
            }
        }
    }