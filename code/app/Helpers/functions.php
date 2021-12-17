<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/12
     * Time: 11:29
     */
    //curl 操作
    function curl_send($url, $data = '', $header = [], $type = 'get', $authentication = false, $timeout = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if ($timeout > 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        }
        if ('post' === $type) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        if ($authentication) {
            curl_setopt($curl, CURLOPT_USERPWD, $authentication);
        }
        $result = curl_exec($curl);
        if (curl_errno($curl) != 0) {
            $error = '发送CURL时发生错误:' . curl_error($curl) . '(code:' . curl_errno($curl) . ')' . PHP_EOL;
            curl_close($curl);
            return ["error" => $error];
        }
        curl_close($curl);
        return $result;
    }