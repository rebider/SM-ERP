<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/16
     * Time: 15:18
     */

    namespace App\Http\Requests;
    class InterceptOrderRequest extends BaseRequest
    {
        protected $rules = [
            'id' => 'required|integer',
            'mark' => 'required',
        ];
        protected $messages = [
            'mark.required' => '拦截原因必须的',
        ];
    }