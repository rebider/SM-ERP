<?php
/**
 * Created by PhpStorm.
 * User: jungle
 * Date: 2018/10/24
 * Time: 16:01
 */

return [
    /*
       |--------------------------------------------------------------------------
       | 命令行参数配置文件debug为真是测试接口，为否是正式接口
       |--------------------------------------------------------------------------
        */
//    'wsdl'=> env('APP_DEBUG')? 'http://202.104.134.94:6192/default/svc/wsdl':'http://202.104.134.94:6192/default/svc/wsdl',
//    'token'=> env('APP_DEBUG')? '15b4203e7a53d062804097434e063590':'15b4203e7a53d062804097434e063590',
//    'key'=> env('APP_DEBUG')? 'd204b59c53d3fddb3612fcca0c21b74e':'d204b59c53d3fddb3612fcca0c21b74e',
    'exchange_url'=>[
        "http://www.boc.cn/sourcedb/whpj/"
    ],
    //日本海外仓物流接口信息
    'logistic_api'=>[
        'appToken' => '9c659b4c079e63d382d99ccb6477f5fb',
        'appKey' => '6cb4f2f4027ed4cc6ff2122e1c295730'
    ],
    'sumaoTestRakutenSecret'=>[
        //serviceSecret
//        'appKey' => 'SP365811_uxiIpmu3wAtyIvqx',
        'appKey' => '',
        //LICENSEKEY
//        'appSecret' => 'SL365811_EC5VbBIQnjHSOeV5', //SL365811_EC5VbBIQnjHSOeV5
        'appSecret' => '', //SL365811_EC5VbBIQnjHSOeV5
        //加密
        //base64_encode(serviceSecret.':'.licenseKey);
        //base64_encode(serviceSecret.'%3A'.licenseKey);
        //ESA Base 64（serviceSecret：licenseKey）serviceSecret SP365811_uxiIpmu3wAtyIvqx
        // licenseKey SL365811_EC5VbBIQnjHSOeV5
    ],
    'sumaoTestAmazonSecret'=>[
        //awsAccessKeyId
//        'license_key' => 'AKIAJXQ3OZYWCRIKYPVA',
        'license_key' => '',
        //awsSecretAccessKey
//        'service_secret' => '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', //SL365811_EC5VbBIQnjHSOeV5
        'service_secret' => '', //SL365811_EC5VbBIQnjHSOeV5
        //seller id
        'sellerId' => '',
//        'sellerId' => 'A4AOJJ0MI7WHO',
//        'MarketplaceId' => 'A1VC38T7YXB528',
        'MarketplaceId' => '',
        'Amazon_MWS_Endpoint' => 'https://mws.amazonservices.jp',
        'open_state' => '1',
        'shop_name' => '辣条小店',
        'id' => '6',
        'user_id' => '5',
        'plat_id' => '1',
        'API_Action' => 'Orders',
        'APP_Version' => '2013-09-01',
        //加密
        //base64_encode(serviceSecret.':'.licenseKey);
        //base64_encode(serviceSecret.'%3A'.licenseKey);
        //ESA Base 64（serviceSecret：licenseKey）serviceSecret SP365811_uxiIpmu3wAtyIvqx
        // licenseKey SL365811_EC5VbBIQnjHSOeV5
    ],
    //商品图片的存储的路径
    'upload_path'=>[
        'public'=>'images/collect' ,
        'storage'=>'public/product'
    ]
] ;