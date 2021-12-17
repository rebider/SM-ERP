<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/3/16
     * Time: 16:34
     */

    namespace App\Http\Requests;
    class IDNotEmpty extends BaseRequest
    {
        protected $rules = [
            'id' => 'integer',
        ];
    }