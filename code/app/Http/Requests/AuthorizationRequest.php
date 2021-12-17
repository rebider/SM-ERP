<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/2
     * Time: 15:22
     */

    namespace App\Http\Requests;
    class AuthorizationRequest extends BaseRequest
    {
        protected $rules = [
            'appToken'      => 'required',
            'appKey'      => 'required',
        ];

    }