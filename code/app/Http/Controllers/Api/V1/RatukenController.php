<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/6/17
     * Time: 15:46
     */

    namespace App\Http\Controllers\Api\V1;
    use App\Common\Common;
    use App\Http\Services\Goods\RakutenGoodsHandle;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;

    class RatukenController
    {
        /**
         * @return array
         * Note: 回调乐天商品图片asyn上传方法
         * Date: 2019/6/17 11:10
         * Author: zt8067
         */
        public function asynFtpImage(Request $request)
        {
            set_time_limit(0);
            //无视请求断开
            ignore_user_abort();
            $params = file_get_contents('php://input');
            if(!Common::is_json($params)){
             return "json格式错误";
            }
            $data =json_decode($params,true);
            if(empty($data['link'])){
                Log::warning('乐天商品图片上传：图片数组为空');
            }
            (new RakutenGoodsHandle)->asynFtpImageProcessing($data);
            return true;
        }

    }