<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/3/28
     * Time: 9:06
     */

    namespace App\Http\Requests;
    class ManualDistributionRequest extends BaseRequest
    {
        protected $rules = [
            'order_id' => 'required|integer',
            'goods' => 'required',
            'warehouse_id' => 'required|integer',
            'logistic_id' => 'required|integer',
        ];
        protected $messages = [
            'goods.required' => '商品信息必须的',
            'warehouse_id.required' => '仓库信息必须的',
            'logistic_id.required' => '物流信息必须的',
        ];

    }