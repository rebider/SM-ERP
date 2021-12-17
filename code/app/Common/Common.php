<?php

    namespace App\Common;
    use App\Models\DingRobotWarn;
    use App\Models\LogHelper;

    /**
     * 公共方法
     * Class Common
     * @package App\Common
     */
    class Common
    {
        /**
         * 合并咨询和回复数据
         * @author zt6768
         * @param object|null $consult 咨询
         * @param object|null $reply   回复
         * @param boolean     $isClass 是否归类,true-是,false-否
         * @param string      $sort    排序,desc-降序,asc-升序
         * @return array
         */
        public static function toConsultReply($consult, $reply, $isClass = true, $sort = 'desc')
        {
            $consultReply = [];
            $userIds = [];
            $newConsult = [];
            $newReply = [];
            if (count($consult)) {
                foreach ($consult as $key => $item) { //咨询
                    $newConsult[$key]['ticket_id'] = $item->ticket_id;
                    $newConsult[$key]['ticket_consult_id'] = $item->ticket_consult_id;
                    $newConsult[$key]['method'] = 'consult';
                    $newConsult[$key]['content'] = $item->consult_content;
                    $newConsult[$key]['consult_type'] = $item->consult_type;
                    $newConsult[$key]['user_id'] = $item->consult_id;
                    if (!in_array($item->consult_id, $userIds)) {
                        array_push($userIds, $item->consult_id);
                    }
                    $newConsult[$key]['user_id'] = $item->consult_id;
                    $newConsult[$key]['time'] = $item->consult_time;
                    $newConsult[$key]['sort_time'] = strtotime($item->consult_time);
                    $newConsult[$key]['ymd'] = date('Y-m-d', strtotime($item->consult_time));
                }
                $tempConsult = array_column($newConsult, null, 'ticket_consult_id');
            }
            if (count($reply)) {
                foreach ($reply as $key => $item) { //回复
                    $newReply[$key]['ticket_id'] = $item->ticket_id;
                    $newReply[$key]['ticket_consult_id'] = $item->ticket_consult_id;
                    $newReply[$key]['method'] = 'reply';
                    $newReply[$key]['content'] = $item->reply_content;
                    $consult_type = 0;
                    if (isset($tempConsult[$item->ticket_consult_id]['consult_type'])) {
                        $consult_type = $tempConsult[$item->ticket_consult_id]['consult_type'];
                    }
                    $newReply[$key]['consult_type'] = $consult_type;
                    $newReply[$key]['user_id'] = $item->reply_man;
                    if (!in_array($item->reply_man, $userIds)) {
                        array_push($userIds, $item->reply_man);
                    }
                    $newReply[$key]['time'] = $item->reply_time;
                    $newReply[$key]['sort_time'] = strtotime($item->reply_time);
                    $newReply[$key]['ymd'] = date('Y-m-d', strtotime($item->reply_time));
                }
            }
            $consultReply = array_merge($newConsult, $newReply);
            if ($consultReply) {
                if ($sort == 'desc') { //降序
                    array_multisort(array_column($consultReply, 'sort_time'), SORT_DESC, $consultReply);
                } else { //升序
                    array_multisort(array_column($consultReply, 'sort_time'), SORT_ASC, $consultReply);
                }
            }
            if ($isClass) {
                $tempConsultReply = [];
                $isFirstWarehouse = true;
                $isFirstLogistics = true;
                foreach ($consultReply as $key => $item) {
                    if ($item['consult_type'] == 1 && $isFirstWarehouse) {
                        $consultReply[$key]['is_look'] = 1;
                        $isFirstWarehouse = false;
                    }
                    if ($item['consult_type'] == 2 && $isFirstLogistics) {
                        $consultReply[$key]['is_look'] = 1;
                        $isFirstLogistics = false;
                    }
                    $tempConsultReply[$item['ymd']][] = $consultReply[$key];
                }
                $consultReply = $tempConsultReply;
            }
            $result = [];
            $result['consult_reply'] = $consultReply;
            $result['user_ids'] = $userIds;
            return $result;
        }

        /**
         * 仓库对接请求方法
         * @author zt8067
         * @param $service string
         * @param $data    array
         * @return array
         */
        public function sendWarehouse($service, $data = [], $account = [])
        {
            $url = config('warehouse.url');
            $appToken = empty($account['appToken']) ? config('warehouse.appToken') : $account['appToken'];
            $appKey = empty($account['appKey']) ? config('warehouse.appKey') : $account['appKey'];
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.example.org/Ec/">';
            $xml .= '<SOAP-ENV:Body>';
            $xml .= '<ns1:callService>';
            $xml .= '<paramsJson>' . json_encode($data) . '</paramsJson>';
            $xml .= '<appToken>' . $appToken . '</appToken>';
            $xml .= '<appKey>' . $appKey . '</appKey>';
            $xml .= '<service>' . $service . '</service>';
            $xml .= '</ns1:callService>';
            $xml .= '</SOAP-ENV:Body>';
            $xml .= '</SOAP-ENV:Envelope>';
            $header = ["Content-Type: application/xml"];
            $response = $this->curl_send($url, $xml, $header, 'POST');
            preg_match_all('/<response>(.*?)<\/response>/i', $response, $xml);
            $response = $xml[1][0];
            $response = json_decode($response, true);
            return $response;
        }

        /**
         * curl请求方法
         * @author zt8067
         * @param $url    string
         * @param $data   array
         * @param $header array
         * @return array
         */
        public function curl_send($url, $data = '', $header = [], $type = 'GET', $authentication = false, $timeout = 30)
        {
            $url = $url; //接收xml数据的文件
            $ch = curl_init();  // 初始一个curl会话
            curl_setopt($ch, CURLOPT_URL, $url);    // 设置url
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            if ('POST' == strtoupper($type)) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            if (!empty($header)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // PHP脚本在成功连接服务器前等待多久，单位秒
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if ($timeout > 0) {
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            }
            if ($authentication) {
                curl_setopt($ch, CURLOPT_USERPWD, $authentication);
            }
            $result = curl_exec($ch);   // 抓取URL并把它传递给浏览器
            // 是否报错
            if (curl_errno($ch) != 0) {
                $error = '发送CURL时发生错误:' . curl_error($ch) . '(code:' . curl_errno($ch) . ')' . PHP_EOL;
                curl_close($ch);
                return ["error" => $error];
            }
            curl_close($ch);    // //关闭cURL资源，并且释放系统资源
            return $result;
        }

        /**
         * PHP精确计算  主要用于货币的计算用法
         * @param        $n1     第一个数
         * @param        $symbol 计算符号 + - * / %
         * @param        $n2     第二个数
         * @param string $scale  精度 默认为小数点后两位
         * @return  string
         */
        public static function PriceCalculate($n1, $symbol, $n2, $scale = '2')
        {
            $res = "";
            if (function_exists("bcadd")) {
                switch ($symbol) {
                    case "+"://加法
                        $res = bcadd($n1, $n2, $scale);
                        break;
                    case "-"://减法
                        $res = bcsub($n1, $n2, $scale);
                        break;
                    case "*"://乘法
                        $res = bcmul($n1, $n2, $scale);
                        break;
                    case "/"://除法
                        $res = bcdiv($n1, $n2, $scale);
                        break;
                    case "%"://求余、取模
                        $res = bcmod($n1, $n2, $scale);
                        break;
                    default:
                        $res = "";
                        break;
                }
            } else {
                switch ($symbol) {
                    case "+"://加法
                        $res = $n1 + $n2;
                        break;
                    case "-"://减法
                        $res = $n1 - $n2;
                        break;
                    case "*"://乘法
                        $res = $n1 * $n2;
                        break;
                    case "/"://除法
                        $res = $n1 / $n2;
                        break;
                    case "%"://求余、取模
                        $res = $n1 % $n2;
                        break;
                    default:
                        $res = "";
                        break;
                }
            }
            return $res;
        }

        public static function getUUid()
        {
            if (function_exists('com_create_guid')) {
                return com_create_guid();
            } else {
                mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
                $charid = strtoupper(md5(uniqid(rand(), true)));
                $hyphen = chr(45);// "-"
                $uuid = chr(123)// "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125);// "}"
                return $uuid;
            }
        }

        public static function getIp()
        {

            if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
                $cip = $_SERVER["HTTP_CLIENT_IP"];
            } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (!empty($_SERVER["REMOTE_ADDR"])) {
                $cip = $_SERVER["REMOTE_ADDR"];
            } else {
                $cip = '';
            }
            preg_match("/[\d\.]{7,15}/", $cip, $cips);
            $cip = isset($cips[0]) ? $cips[0] : 'unknown';
            unset($cips);
            return $cip;
        }

        /**
         * 将ascii码转为字符串
         * @param type $str  要解码的字符串
         * @param type $prefix  前缀，默认:&#
         * @return type
         */
        public static function asciiToStr($str, $prefix="&#") {
            $str = str_replace($prefix, "", $str);
            $a = explode(";", $str);
            $utf = '';
            foreach ($a as $dec) {
                if ($dec < 128) {
                    $utf .= chr($dec);
                } else if ($dec < 2048) {
                    $utf .= chr(192 + (($dec - ($dec % 64)) / 64));
                    $utf .= chr(128 + ($dec % 64));
                } else {
                    $utf .= chr(224 + (($dec - ($dec % 4096)) / 4096));
                    $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
                    $utf .= chr(128 + ($dec % 64));
                }
            }
            return $utf;
        }

        //将XML转为array
        public function xmlToArray($xml)
        {
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $values;
        }
        /**
         * mongo日志以及钉钉
         * @param  $e  错误
         * @param  $model  模块
         * @param  $detailed  页面描述
         * @param  $type  接口填 api
         * @param  $method  方法名
         * @param  $supplement  额外提交描述
         */
        public static function mongoLog($e,$model='',$detailed='',$method='',$type='task',$supplement=[]){
            $exception_data = [
                'start_time'                => date('Y-m-d H:i:s'),
                'msg'                       => '失败信息：' . $e->getMessage(),
                'line'                      => '失败行数：' . $e->getLine(),
                'file'                      => '失败文件：' . $e->getFile(),
            ];
            if (!empty($supplement)){
                array_merge($exception_data, $supplement);
            }
            LogHelper::setExceptionLog($exception_data,$model);
            $exceptionDing ['type'] = $type;
            $dingPushData ['task'] = $detailed;
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = $method;
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($exception_data,null,$exceptionDing ['type']);
        }
         /**
          * 判断数据是合法的json数据: (PHP版本大于5.3)
          */
       public static function is_json($string) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        /**
         * @return bool
         * Note: cli模式
         * Data: 2019/5/31 14:17
         * Author: zt7785
         */
        public static function is_cli () {
            return preg_match("/cli/i", php_sapi_name()) ? true : false;
        }
    }