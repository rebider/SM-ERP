<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/5/29
     * Time: 14:13
     */

    namespace App\Http\Services\Rakuten;
    class GetItem extends BaseRakuten
    {
        public function process($itemUrl = '')
        {

            $item = [];
            $queryItemUrl = $itemUrl; // クエリストリングでitemurl指定

            $item['itemUrl'] = $queryItemUrl; // 取りたい商品のItemURL

            list($httpStatusCode, $response) = $this->getItem($item);
            dd($httpStatusCode, $response);
        }

        public function getItem($item)
        {
            $responseBody = [];
            $authkey = base64_encode($this->sale_info['appKey'] . ':' . $this->sale_info['appSecret']);
            $header = [
                "Content-Type: text/xml;charset=UTF-8",
                "Authorization: ESA {$authkey}",
            ];
            // クエリストリングに取得したい商品のitemUrlを入れる
            $url = self::RMS_API_ITEM_GET . "?itemUrl={$item['itemUrl']}";
            $ch = curl_init($url);
            if(stripos($url, 'https://') !== FALSE) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
            $response = curl_exec($ch);
            if (curl_error($ch)) {
                $response = curl_error($ch);
            }
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return [$httpStatusCode, $response];
        }
    }