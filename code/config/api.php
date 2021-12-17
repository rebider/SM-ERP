<?php
    return [
        //线上为'true',测试&开发为'false'
        'app_model' => true,
        'token'     => 'MMRi5dU6cbgtjpwzL2GcBrB2ebnwS5cxaoxxGr5A',
        //速贸仓库创建入库单API
        'createAsn' => [
            //API密钥
            'appToken'   => '4f64b8a37a3a570c1a35fd774a0faedc',
            //API标识
            'appKey'     => '5795b5a385d9c656873d33b75f47a7bf',
            //正式环境
            'appUrl'     => 'http://正式ip/default/svc/wsdl',
            //测试环境
            'testAppUrl' => 'http://202.104.134.94:62607/default/svc/wsdl',
        ],
    ];