<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/4
     * Time: 11:09
     */

    namespace App\Http\Requests;
    class AnnouncementRequest extends BaseRequest
    {
        protected $rules = [
            'title' => 'required|string|max:100',
            'content' => 'required|max:65535',
        ];
        protected $messages = [
            'title.required' => '标题为必填项',
            'content.required' => '内容为必填项',
        ];
    }