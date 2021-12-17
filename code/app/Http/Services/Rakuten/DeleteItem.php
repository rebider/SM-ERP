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

    class DeleteItem extends BaseRakuten
    {
        /**
         * Convert an array to XML
         * @param array $array
         * @param SimpleXMLElement $xml
         * @param array $parentKeyName (その要素が配列で、子要素を親要素の単数形にして登録したい時指定)
         */
        public function _arrayToXml($arr)
        {
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
            $xml .= "<request>";
            $xml .= "<itemDeleteRequest>";
            $xml .= "<item>";
            $xml .= $this->recursiveArrayToXml($arr);
            $xml .= "</item>";
            $xml .= "</itemDeleteRequest>";
            $xml .= "</request>";
            return $xml;
        }

        public function deleteItem($item, $access)
        {
            $authkey = base64_encode($access['appKey'] . ':' . $access['appSecret']);
            $header = [
                "Content-Type: text/xml;charset=UTF-8",
                "Authorization: ESA {$authkey}",
            ];
            $url = self::RMS_API_ITEM_DELETE;
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
        public static function treatmentProcess($value = '', $access)
        {
            $results = ['code' => -1, 'msg' => '下架失败', 'errAll' => []];
            do {
                $item = [];
                $item['itemUrl'] = $value['cmn'];//商品管理番号
                list($reqXml, $httpStatusCode, $response) = (new self())->deleteItem($item, $access);
                if ($httpStatusCode === 0) {
                    throw new DataNotFoundException('网络问题请求失败！');
                }
                if ($httpStatusCode !== 200) {
                    throw new DataNotFoundException('接口异常，或网络问题！');
                }
               if(isset($response['itemDeleteResult']['errorMessages'])){
                   $errorMessages = array_column($response['itemDeleteResult']['errorMessages']['errorMessage'],'msg');
                   $results['msg'] = $errorMessages;
                   break;
               }
                $results['code'] = 1;
                $results['msg'] = '下架成功';
            } while (0);
            return $results;
        }
    }