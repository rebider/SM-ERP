<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/5/28
     * Time: 14:42
     */

    namespace App\Http\Services\Rakuten;

    use App\Common\Common;
    use App\Common\FTP;
    use App\Exceptions\DataNotFoundException;
    use App\Models\SettingShops;

    class InsertItem extends BaseRakuten
    {
        /**
         * Convert an array to XML
         * @param array $array
         * @param SimpleXMLElement $xml
         * @param array $parentKeyName (その要素が配列で、子要素を親要素の単数形にして登録したい時指定)
         */
        public function _arrayToXml($arr, $type = 'itemInsertRequest')
        {
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
            $xml .= "<request>";
            $xml .= "<{$type}>";
            $xml .= "<item>";
            $xml .= $this->recursiveArrayToXml($arr);
            $xml .= "</item>";
            $xml .= "</{$type}>";
            $xml .= "</request>";
            return $xml;
        }

        public function insertItem($item,$access)
        {
            $authkey = base64_encode($access['appKey'] . ':' . $access['appSecret']);
            $header = [
                "Content-Type: text/xml;charset=UTF-8",
                "Authorization: ESA {$authkey}",
            ];
            $url = self::RMS_API_ITEM_INSERT;
            $ch = curl_init($url);
            $reqXml = $this->_arrayToXml($item);
            // return array($reqXml, $httpStatusCode, $response);
            if (stripos($url, 'https://') !== false) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $reqXml);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
            $response = curl_exec($ch);
            if (curl_error($ch)) {
                $response = curl_error($ch);
            }
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return [$reqXml, $httpStatusCode, $response];
        }

        /**
         * 更新产品
         * @param $item
         * @param $access
         * @return array
         */
        public function updateItem($item,$access)
        {
            $authkey = base64_encode($access['appKey'] . ':' . $access['appSecret']);
            $header = [
                "Content-Type: text/xml;charset=UTF-8",
                "Authorization: ESA {$authkey}",
            ];
            $url = self::RMS_API_ITEM_UPDATE;
            $ch = curl_init($url);
            $reqXml = $this->_arrayToXml($item, 'itemUpdateRequest');
            if (stripos($url, 'https://') !== false) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $reqXml);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
            $response = curl_exec($ch);
            if (curl_error($ch)) {
                $response = curl_error($ch);
            }
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return [$reqXml, $httpStatusCode, $response];
        }

        /**
         * @author zt8067
         *  递归处理xml数组
         * @param array
         * @return mixed
         */
        public function recursiveArrayToXml($arr)
        {
            $xml = '';
            foreach ($arr as $key => $val) {
                if (is_array($val)) {
                    if (preg_match('/[0-9]\d*$/', $key)) {
                        $key = preg_replace('/[0-9]\d*$/', '', $key);
                        $xml .= "<" . $key . ">";
                        $xml .= $this->recursiveArrayToXml($val);
                        $xml .= "</" . $key . ">";
                    } else {
                        $xml .= "<" . $key . ">";
                        $xml .= $this->recursiveArrayToXml($val);
                        $xml .= "</" . $key . ">";
                    }
                } else {
                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                }
            }
            return $xml;
        }

        /**
         * @author zt8067
         * 商品上架
         * @param $value 商品数据
         * @return Boolean
         */
        public static function treatmentProcess($value = '',$user_id)
        {
            $results = ['code' => -1, 'msg' => '上架失败', 'errAll' => []];
            do {

                    $Shops = SettingShops::where(['id'=>$value['belongs_shop'],'user_id'=>$user_id])->first();
                    if(empty($Shops)){
                        $results['msg'] = "没有找到关联店铺！";
                        break;
                    }
                    $access['appKey'] = $Shops->service_secret;
                    $access['appSecret'] = $Shops->license_key;
                    $ftp_user = $Shops->ftp_user;
                    $ftp_pass = $Shops->ftp_pass;
                    $shop_name = $Shops->shop_name;
                    $item = [];
                    $item['itemUrl'] = $value['cmn'] ?? '';
                    $item['itemNumber'] = $value['sku'] ?? '';
                    $item['itemName'] = $value['goods_name'];
                    $item['itemPrice'] = intval($value['sale_price']);//必须取整
                    if(isset($value['rakuten_category_id']) && is_numeric($value['rakuten_category_id']))
                    {
                        $item['genreId'] = $value['rakuten_category_id'];//所有产品目录ID
                    }
                    else{
                        $item['genreId'] = substr($value['rakuten_category_id'],strripos($value['rakuten_category_id'],',')+1);//所有产品目录ID
                    }
                    /**  没有目录ID的原因-设置内容：
                     * 1：设置项目
                     * 2：服务项目
                     * 3：存储原始项目
                     * 4：根据项目选择的库存项目
                     * 5：没有相应的产品代码 默认推荐
                     */
                    if (empty($value['catalogIdExemptionReason'])) {
                        $item['catalogId'] = $value['catalogId']; // 目录ID（JAN代码）
                    } else {
                        $item['catalogIdExemptionReason'] = $value['catalogIdExemptionReason'];
                    }
                    $dir = date("ymd") . $value['id'];

                    $link = [];
                    if(isset($value['img_url']) && !empty($value['img_url'])){
                        $bigPictureUrl = substr($value['img_url'], strripos($value['img_url'], '/') + 1);
                        if (isset($value['pictures']) && !empty($value['pictures'])) {
                            foreach ($value['pictures'] as $goods_pics) {
                                $url = substr($goods_pics['link'], strripos($goods_pics['link'], '/') + 1);
                                $link['link'][$goods_pics['id']] = $url;
                            }
                            $link['link'] = array_merge((array)$bigPictureUrl, $link['link']);
                        }else{
                            $link['link'] = (array)$bigPictureUrl;
                        }
                        foreach ($link['link'] as $k => $item_file) {
                            $image = [];
                            $image['imageUrl'] = 'https://image.rakuten.co.jp/' . $shop_name . '/cabinet/' . $dir . '/' . $item_file;
                            $image['imageAlt'] = $item['itemName'] . $k;
                            $item['images']['image' . $k] = $image; // 商品に画像をセット
                        }
                    }

                    // 描述相关设置
                    //            $item['descriptionForPC'] = '結構html使える';
                    //            $item['descriptionForMobile'] = '一部html使用可能';
                    //            $item['descriptionForSmartPhone'] = '一部html使用可能';
                    //            $item['catchCopyForPC'] = 'PC用キャッチコピー';
                    //            $item['catchCopyForMobile'] = 'モバイル用キャッチコピー';
                    // 运送和其他设置
                    $item['isIncludedPostage'] = 0; // 免运费标志（0：运费单独 1：包括运费）
                    $item['isIncludedCashOnDeliveryPostage'] = 0; // 1:货到付款（默认0：货到付款单独）
                    //$item['postage'] = 108; // 个别送料
                    //$item['isDepot'] = 1; // 默认值：false true：放入仓库 false：自动
                    // 库存相关设置
                    $itemInventory = [];
                    $itemInventory['inventoryType'] = 1; // 通常在庫設定允许值：1到2  1：正常库存设置 2：项目设置根据选项 自动将全宽度转换为半宽度的库存设置。
                    $itemInventory['inventories']['inventory']['inventoryCount'] = $value['platform_in_stock'];
                    $itemInventory['inventories']['inventory']['normalDeliveryDateId'] = 1;//3天内发货
                    //$itemInventory['inventories']['inventory']['backorderDeliveryDateId'] = 1000;
                    $itemInventory['inventoryQuantityFlag'] = 1;//显示剩余库存数量
                    // 将库存信息设置为产品
                    $item['itemInventory'] = $itemInventory;
                     if(empty($value['belongs_shop'])){
                         $results['msg'] = "没有关联店铺字段！";
                         break;
                     }
                    list($reqXml, $httpStatusCode, $response) = (new self())->insertItem($item,$access);
                    if ($httpStatusCode === 0) {
                        throw new DataNotFoundException('网络问题请求失败！');
                    }
                    if($httpStatusCode !== 200){
                        throw new DataNotFoundException('接口异常，或网络问题！');
                    }
                    $response = (new Common)->xmlToArray($response);

                    if (!empty($response['itemInsertResult']['errorMessages'])) {
                        $errorMessage = $response['itemInsertResult']['errorMessages']['errorMessage'];
                        if(count($errorMessage) == count($errorMessage, 1)){
                            $results['msg'] = $errorMessage['msg'];
                        }else{
                            $errorMessages = array_column($response['itemInsertResult']['errorMessages']['errorMessage'],'msg');
                            $results['msg'] = implode(',',$errorMessages);
                        }
                        break;
                    }
                    $results['code'] = 1;
                    $results['msg'] = '商品上架成功';
                    //商品图片非必传
                    if ($httpStatusCode===200 && isset($value['img_url']) && !empty($value['img_url'])) {
                        //异步断点上传商品图片
                        $url = '127.0.0.1:'.(new self())->port.'/api/ratuken_img';
                        $params['link'] = $link['link'];
                        $params['dir'] = $dir;
                        $params['ftp'] = ['ftp_user'=>$ftp_user,'ftp_pass'=>$ftp_pass];
                        $params['token'] = config('api.token');
                        $header = [
                            "Content-Type:application/json",
                        ];
                        $params = json_encode($params);
                        (new Common())->curl_send($url, $params,$header,'POST',false,1);//timeout 1 不等待返回
                    }

            } while (0);
            return $results;
        }

        /**
         * @author zt8067
         * 商品更新
         * @param $value 商品数据
         * @return array
         */
        public static function updateTreatmentProcess($value = '',$user_id)
        {
            $results = ['code' => -1, 'msg' => '上架失败', 'errAll' => []];
            do {
                $Shops = SettingShops::where(['id'=>$value['belongs_shop'],'user_id'=>$user_id])->first();
                if(empty($Shops)){
                    $results['msg'] = "没有找到关联店铺！";
                    break;
                }
                $access['appKey'] = $Shops->service_secret;
                $access['appSecret'] = $Shops->license_key;
                $ftp_user = $Shops->ftp_user;
                $ftp_pass = $Shops->ftp_pass;
                $shop_name = $Shops->shop_name;

                $item = [];
                $item['itemUrl'] = $value['cmn'] ?? '';
                $item['itemNumber'] = $value['sku'] ?? '';
                $item['itemName'] = $value['goods_name'];
                $item['itemPrice'] = intval($value['sale_price']);//必须取整
                if(isset($value['rakuten_category_id']) && is_numeric($value['rakuten_category_id']))
                {
                    $item['genreId'] = $value['rakuten_category_id'];//所有产品目录ID
                }
                else{
                    $item['genreId'] = substr($value['rakuten_category_id'],strripos($value['rakuten_category_id'],',')+1);//所有产品目录ID
                }
                /**  没有目录ID的原因-设置内容：
                 * 1：设置项目
                 * 2：服务项目
                 * 3：存储原始项目
                 * 4：根据项目选择的库存项目
                 * 5：没有相应的产品代码 默认推荐
                 */
                if (empty($value['catalogIdExemptionReason'])) {
                    $item['catalogId'] = $value['catalogId']; // 目录ID（JAN代码）
                } else {
                    $item['catalogIdExemptionReason'] = $value['catalogIdExemptionReason'];
                }
                $dir = str_pad($value['id'].date("d"),6,'0',STR_PAD_LEFT);
                //目录文件必须7位字符
                if (isset($value['pictures']) && !empty($value['pictures'])) {
                    $link = [];
                    $bigPictureUrl = substr($value['img_url'], strripos($value['img_url'], '/') + 1);
                    foreach ($value['pictures'] as $goods_pics) {
                        $url = substr($goods_pics['link'], strripos($goods_pics['link'], '/') + 1);
                        $link['link'][$goods_pics['id']] = $url;
                    }

                    $link['link'] = isset($link['link']) ? array_merge((array)$bigPictureUrl, $link['link']) : (array)$bigPictureUrl;
                    foreach ($link['link'] as $k => $item_file) {
                        $image = [];
                        $image['imageUrl'] = 'https://image.rakuten.co.jp/' . $shop_name . '/cabinet/' . $dir . '/' . $item_file;
                        $image['imageAlt'] = $item['itemName'] . $k;
                        $item['images']['image' . $k] = $image; // 商品に画像をセット
                    }
                }
                // 运送和其他设置
                $item['isIncludedPostage'] = 0; // 免运费标志（0：运费单独 1：包括运费）
                if(empty($value['belongs_shop'])){
                    $results['msg'] = "没有关联店铺字段！";
                    break;
                }

                list($reqXml, $httpStatusCode, $response) = (new self())->updateItem($item,$access);
                if ($httpStatusCode === 0) {
                    return ['code' => -1, 'msg' => '网络异常，请尝试再次提交'];
//                    throw new DataNotFoundException('网络问题请求失败！');
                }
                if($httpStatusCode !== 200){
                    return ['code' => -1, 'msg' => '接口异常，或网络问题！'];
//                    throw new DataNotFoundException('接口异常，或网络问题！');
                }
                $response = (new Common)->xmlToArray($response);
                if (!empty($response['itemInsertResult']['errorMessages'])) {
                    $errorMessage = $response['itemInsertResult']['errorMessages']['errorMessage'];
                    if(count($errorMessage) == count($errorMessage, 1)){
                        $results['msg'] = $errorMessage['msg'];
                    }else{
                        $errorMessages = array_column($response['itemInsertResult']['errorMessages']['errorMessage'],'msg');
                        $results['msg'] = implode(',',$errorMessages);
                    }
                    break;
                }
                $results['code'] = 1;
                $results['msg'] = '商品上架成功';
                //商品图片非必传
                if ($httpStatusCode===200 && isset($value['pictures']) && !empty($value['pictures'])) {
                    //异步断点上传商品图片
                    $url = '127.0.0.1:'.(new self())->port.'/api/ratuken_img';
                    $params['link'] = $link['link'];
                    $params['dir'] = $dir;
                    $params['ftp'] = ['ftp_user'=>$ftp_user,'ftp_pass'=>$ftp_pass];
                    $params['token'] = config('api.token');
                    $header = [
                        "Content-Type:application/json",
                    ];
                    $params = json_encode($params);
                    (new Common())->curl_send($url, $params,$header,'POST',false,1);//timeout 1 不等待返回
                }

            } while (0);
            return $results;
        }


        /**
         * @return array
         * Note: 回调处理图片上传
         * Date: 2019/6/17 14:00
         * Author: zt8067
         */
        public function eachProcessing($data)
        {
            $link = $data['link'];
            $dir = $data['dir'];
            $ftp = $data['ftp'];
            foreach ($link as $k => $_item_file) {
                //上架后ftp上传图片等待系统不定时抓取注册 5分钟到10分钟分钟多不等 图像注册标准：10,000张图像约12分钟 （时间只是一个标准，所以很多商店同时注册了大量图像等）可能需要一段时间。）
                $remote_file = $dir . '/' . $_item_file;
                $this->connection($_item_file, $remote_file,$ftp);
            }
        }

            /**
         * @return array
         * Note: 乐天商品图片上传方法
         * Date: 2019/6/13 14:10
         * Author: zt8067
         */
        public function connection($local_file, $remote_file,$ftp_access)
        {
            $config = [
                'host' => '133.237.61.63',
                'user' => $ftp_access['ftp_user'],
                'pass' => $ftp_access['ftp_pass'],
            ];
            $ftp = new FTP($config);
            $result = $ftp->connect();
            if (!$result) {
                $result = $ftp->get_error_msg();
            }
            $local_file = storage_path('app/public/'. $local_file);
            $remote_file = 'cabinet/images/' . $remote_file;
            //上传文件
            if ($ftp->upload($local_file, $remote_file)) {
                $result = true;
            } else {
                $result = false;
            }
            $ftp->close();
            return $result;
        }
    }