<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SettingCurrencyExchange
 * Notes: 汇率转换
 * @package App
 * Data: 2018/10/30 18:14
 * Author: zt7785
 */
class SettingCurrencyExchange extends Model
{
    //TODO
    //需要增加历史记录 人工维护汇率 不适用 换成中行接口  感觉不对劲 汇率表 针对客户应该有个 针对全平台有一个
    protected $table = 'setting_currency_exchange';
    public $timestamps = true;
    public $fillable = ['id','currency_to_code','currency_to_name','currency_form_code','currency_form_name','exchange_rate','created_at','updated_at'];

    protected static $rateArr = [];

    const ISSHOW = 1;

    const UNSHOW = 2;

    public static $currencyJson = '[
        {
            "code":"USD",  
            "name":"美元"  
        },
        {
            "code":"CNY",  
            "name":"人民币"  
        },
        {
            "code":"EUR",  
            "name":"欧元"  
        },
        {
            "code":"GBP",  
            "name":"英镑"  
        },
        {
            "code":"HKD",  
            "name":"港元"  
        },
        {
            "code":"MOP",  
            "name":"澳门元"  
        },
        {
            "code":"TWD",  
            "name":"台币"  
        },
        {
            "code":"JPY",  
            "name":"日元"  
        },
        {
            "code":"CAD",  
            "name":"加元"  
        },
        {
            "code":"AUD",  
            "name":"澳元"  
        },
        {
            "code":"KRW",  
            "name":"韩元"  
        },
        {
            "code":"THB",  
            "name":"泰铢"  
        },
        {
            "code":"VND",  
            "name":"越南盾"  
        },
        {
            "code":"DKK",  
            "name":"丹麦克朗"  
        },
        {
            "code":"DEM",  
            "name":"德国马克"  
        },
        {
            "code":"CHF",  
            "name":"瑞士法郎"  
        },
        {
            "code":"SGD",  
            "name":"新加坡元"  
        },
        {
            "code":"SEK",  
            "name":"瑞典克朗"  
        },
        {
            "code":"PHP",  
            "name":"菲律宾比索"  
        },
        {
            "code":"NOK",  
            "name":"挪威克朗"  
        },
        {
            "code":"DZD",  
            "name":"阿尔及利亚第纳尔"  
        },
        {
            "code":"ARS",  
            "name":"阿根廷比索"  
        },
        {
            "code":"BHD",  
            "name":"巴林第纳尔"  
        },
        {
            "code":"BDT",  
            "name":"孟加拉塔卡"  
        },
        {
            "code":"BYR",  
            "name":"白俄罗斯卢布"  
        },
        {
            "code":"BMD",  
            "name":"百慕大元"  
        },
        {
            "code":"BTN",  
            "name":"不丹努扎姆"  
        },
        {
            "code":"BWP",  
            "name":"博茨瓦纳普拉"  
        },
        {
            "code":"BRL",  
            "name":"巴西雷亚尔"  
        },
        {
            "code":"BND",  
            "name":"文莱币"  
        },
        {
            "code":"BGN",  
            "name":"保加利亚列弗"  
        },
        {
            "code":"KHR",  
            "name":"柬埔寨瑞尔"  
        },
        {
            "code":"CLP",  
            "name":"智利比索"  
        },
        {
            "code":"COP",  
            "name":"哥伦比亚比索"  
        },
        {
            "code":"CRC",  
            "name":"哥斯达黎加科朗"  
        },
        {
            "code":"HRK",  
            "name":"克罗地亚库纳"  
        },
        {
            "code":"CUP",  
            "name":"古巴比索"  
        },
        {
            "code":"CZK",  
            "name":"捷克克朗"  
        },
        {
            "code":"EGP",  
            "name":"埃及镑"  
        },
        {
            "code":"GTQ",  
            "name":"危地马拉格查尔"  
        },
        {
            "code":"HUF",  
            "name":"匈牙利福林"  
        },
        {
            "code":"INR",  
            "name":"印度卢比"  
        },
        {
            "code":"IDR",  
            "name":"印尼卢比"  
        },
        {
            "code":"IQD",  
            "name":"伊拉克第纳尔"  
        },
        {
            "code":"ILS",  
            "name":"以色列新锡克尔"  
        },
        {
            "code":"JMD",  
            "name":"牙买加元"  
        },
        {
            "code":"JOD",  
            "name":"约旦第纳尔"  
        },
        {
            "code":"KES",  
            "name":"肯尼亚先令"  
        },
        {
            "code":"KWD",  
            "name":"科威特第纳尔"  
        },
        {
            "code":"LAK",  
            "name":"老挝基普"  
        },
        {
            "code":"LBP",  
            "name":"黎巴嫩镑"  
        },
        {
            "code":"LTL",  
            "name":"立陶宛立特"  
        },
        {
            "code":"MYR",  
            "name":"马来西亚林吉特"  
        },
        {
            "code":"MXN",  
            "name":"墨西哥比索"  
        },
        {
            "code":"MNT",  
            "name":"蒙古图格里克"  
        },
        {
            "code":"MAD",  
            "name":"摩洛哥迪拉姆"  
        },
        {
            "code":"MMK",  
            "name":"缅元"  
        },
        {
            "code":"OMR",  
            "name":"阿曼里亚尔"  
        },
        {
            "code":"PKR",  
            "name":"巴基斯坦卢比"  
        },
        {
            "code":"PEN",  
            "name":"秘鲁新索尔"  
        },
        {
            "code":"PLN",  
            "name":"波兰兹罗提"  
        },
        {
            "code":"QAR",  
            "name":"卡塔尔里亚尔"  
        },
        {
            "code":"RON",  
            "name":"罗马尼亚新列伊"  
        },
        {
            "code":"RUB",  
            "name":"俄罗斯卢布"  
        },
        {
            "code":"RSD",  
            "name":"赛尔维亚第纳尔"  
        },
        {
            "code":"SOS",  
            "name":"索马里先令"  
        },
        {
            "code":"ZAR",  
            "name":"南非兰特"  
        },
        {
            "code":"LKR",  
            "name":"斯里兰卡卢比"  
        },
        {
            "code":"SYP",  
            "name":"叙利亚磅"  
        },
        {
            "code":"TZS",  
            "name":"坦桑尼亚先令"  
        },
        {
            "code":"NZD",  
            "name":"新西兰元"  
        },
        {
            "code":"TND",  
            "name":"突尼斯第纳尔"  
        },
        {
            "code":"TRY",  
            "name":"土耳其新里拉"  
        },
        {
            "code":"AED",  
            "name":"阿联酋迪拉姆"  
        },
        {
            "code":"UGX",  
            "name":"乌干达先令"  
        },
        {
            "code":"UYU",  
            "name":"乌拉圭比索"  
        }
    ]';

    public static $cache = null;


    /**
     * @var 模型前缀
     */
    public static $cachePrefix = 'Currency_';
    //关联客户汇率维护
    public function maintain(){
        return $this->hasOne(SettingCurrencyExchangeMaintain::class,'currency_exchange_id','id');
    }

    /**
     * @param null $cache
     * Note: 汇率数据刷新
     * Data: 2018/11/8 9:47
     * Author: zt7785
     */
    public static function setCacheData($cache = null)
    {
        if(is_null($cache)){
            $redis = new Redis();
            if (empty($redis->exception_mes)) {
                $redisStatus = is_string($redis->checkCacheService()) ? false : true;
            } else {
                $redisStatus = false;
            }
            self::$cache = $redisStatus ? $redis : null;
            $cache =   self::$cache;
        }
        if (empty($cache)) {
            return ;
        }
        self::chunk(10,function ($values) use ($cache){
            if (!$values->isEmpty()) {
                $values = $values->toArray();
                foreach ($values as $value ) {
                    $cache->setCurrencyExchange($value['currency_form_code'].'_'.$value['currency_to_code'],$value);
                }
            }
        });
    }

    /**
     * Note: 币种汇率任务
     * Data: 2018/9/20 16:50
     * Author: zt7785
     */
    public static function taskCurrencyExchange ($redis = null) {
        $rateArr = self::getRate();
        $param ['currency_form_code'] = 'CNY';
        $currencyArr = json_decode(self::$currencyJson,true);
        if (empty($rateArr)) {
            $rateInfos = self::get(['id','currency_form_code','currency_to_code']);
            foreach ($rateInfos as $rateInfo) {
                $shenjianExchangeUrl = 'https://api.shenjian.io/exchange/currency';
                $param ['appid'] = env('SHENJIAN_EXCHANGE_SECRET');
                $param ['to'] = $rateInfo['currency_form_code'];
                $param ['form'] = $rateInfo['currency_to_code'];
                $url = Curl::combineURL($shenjianExchangeUrl,$param);
                $shenjianExchangeRe = json_decode(Curl::curl_get($url),true);
                if (empty($shenjianExchangeRe ['error_code'])) {
                    //error_code = 0 succ
                    $param['exchange_rate'] = $shenjianExchangeRe['data'] ['rate'];
                    self::postData($rateInfo['id'],$param);
                }
            }
        } else {
            foreach ($rateArr as $ratekey => $rateVal) {
                $param['currency_to_code'] = $ratekey;
                $rateInfo = self::where('currency_form_code',$param['currency_form_code'])->where('currency_to_code',$param['currency_to_code'])->first(['id']);
                $rateId = $rateInfo ? $rateInfo['id'] : 0 ;
                if (empty($rateId)) {
                    $currencyKey = array_search($param['currency_to_code'],array_column($currencyArr,'code'));
                    if (is_bool($currencyKey)) {
                        //false
                        $param['currency_to_name'] = '异常币种';
                    } else {
                        $param['currency_to_name'] = $currencyArr[$currencyKey]['name'];
                    }
                }
                $param['exchange_rate'] = $rateVal;
                self::postData($rateId,$param);
            }
        }
        if ($redis) {
            self::setCacheData($redis);
        }
    }

    /**
     * @param $id
     * @param $data
     * Note: 更新|新增
     * Data: 2018/10/8 10:15
     * Author: zt7785
     */
    public static function postData ($id ,$data) {
        self::updateOrCreate(['id'=>$id],$data);
    }
    /**
     * @return array
     * Note: 获取汇率
     * Data: 2018/8/17 16:14
     * Author: zt7785
     */
    public static function getRate(){
        $returnArr= array();
        $returnArr['USD']=6.5;//添加上美元的汇率
        $returnArr['CNY']=1;//添加上中国的汇率
        try{
            do{
                $url = "http://data.bank.hexun.com/other/cms/fxjhjson.ashx?callback=PereMoreData";
                $content = Curl::curl_get($url);
                //curl 乱码
                $content = mb_convert_encoding($content, 'utf-8', 'GBK,UTF-8,ASCII');
                $reg = '/PereMoreData\((.*?)\)/is';
                preg_match_all($reg,$content,$m);
                if($m[1]){
                    //print_r($m[1][0]);
                    //将单引号转双引号
                    $json_str = str_replace("'",'"',$m[1][0]);
                    $json_str = str_replace("currency",'"currency"',$json_str);
                    $json_str = str_replace("refePrice",'"refePrice"',$json_str);
                    $json_str = str_replace("code",'"code"',$json_str);
                    $json_str = preg_replace('/"currency":".*?",/','',$json_str);
                    $decodeArr = json_decode($json_str,true);
                    if(!is_array($decodeArr)){
                        break;
                    }

                    $currencyArr = array("USD","HKD","JPY","MOP","PHP","SGD","KRW","THB","EUR","DKK","GBP","DEM","FRF","ITL","ESP","ATS","FIM","NOK","SEK","CHF","CAD","AUD","NZD");
                    foreach($currencyArr as $cv){
                        foreach($decodeArr as $v){
                            if($cv==trim($v['code'])){
                                $returnArr[$cv]=$v['refePrice']/100;
                                break;
                            }
                        }
                    }
                }
            }while(0);

        }catch(Exception $e){

        }
        return $returnArr;
    }

    /**
     * @param $price
     * @param $currency
     * Note: 获取转换后的价格
     * Data: 2018/8/17 16:14
     * Author: zt7785
     */
    public static function getExchangedRMB($price,$currency,$redis = null) {
        bcscale (2);
        if (strtoupper($currency) == 'RMB') {
            $optCurrency = 'CNY';
        } else {
            $optCurrency = $currency ;
        }
        if ($redis) {
            $rateInfo = $redis->getCurrencyExchange('CNY_'.$optCurrency);
        } else {
            $rateInfo = self::where('currency_form_code','CNY')->where('currency_to_code',$optCurrency)->first(['id','exchange_rate']);
        }
        $exchange_rate_exceptional = true;
        if ($rateInfo) {
            if ($rateInfo ['exchange_rate'] > 0 ) {
                return bcmul ($price,$rateInfo['exchange_rate']);
            }
        }
        if ($exchange_rate_exceptional) {
            $currency = strtoupper($currency);
            $rateArr = self::getRate();
            if (array_key_exists($currency,$rateArr)) {
                return bcmul ($price,$rateArr[$currency]);
            }
            $shenjianExchangeUrl = 'https://api.shenjian.io/exchange/currency';
            $param ['appid'] = env('SHENJIAN_EXCHANGE_SECRET');
            $param ['to'] = 'CNY';
            $param ['form'] = $currency;
            $url = Curl::combineURL($shenjianExchangeUrl,$param);
            $shenjianExchangeRe = json_decode(Curl::curl_get($url),true);
            if (empty($shenjianExchangeRe ['error_code'])) {
                //error_code = 0 succ
                return bcmul ($price,$shenjianExchangeRe['data'] ['rate']);
            }
        }
    }


    /**
     * @param $price
     * @param $currency
     * @param null $redis
     * @return mixed
     * Note: 采集价格记录JSON信息新增运算汇率
     * Data: 2019/2/18 10:27
     * Author: zt7785
     */
    public static function getExchangedCNY($price,$currency,$redis = null) {
        bcscale (2);
        $response ['price'] = '';
        $response ['exchange'] = '1';
        if (strtoupper($currency) == 'RMB') {
            $optCurrency = 'CNY';
        } else {
            $optCurrency = $currency ;
        }
        if ($redis) {
            $rateInfo = $redis->getCurrencyExchange('CNY_'.$optCurrency);
        } else {
            $rateInfo = self::where('currency_form_code','CNY')->where('currency_to_code',$optCurrency)->first(['id','exchange_rate']);
        }
        $exchange_rate_exceptional = true;
        if ($rateInfo) {
            if ($rateInfo ['exchange_rate'] > 0 ) {
                $response ['price'] = bcmul ($price,$rateInfo['exchange_rate']);
                $response ['exchange'] = $rateInfo['exchange_rate'];
                return $response;
            }
        }
        if ($exchange_rate_exceptional) {
            $currency = strtoupper($currency);
            $rateArr = self::getRate();
            if (array_key_exists($currency,$rateArr)) {
                $response ['price'] = bcmul ($price,$rateArr[$currency]);
                $response ['exchange'] = $rateArr[$currency];
                return $response;
            }
            $shenjianExchangeUrl = 'https://api.shenjian.io/exchange/currency';
            $param ['appid'] = env('SHENJIAN_EXCHANGE_SECRET');
            $param ['to'] = 'CNY';
            $param ['form'] = $currency;
            $url = Curl::combineURL($shenjianExchangeUrl,$param);
            $shenjianExchangeRe = json_decode(Curl::curl_get($url),true);
            if (empty($shenjianExchangeRe ['error_code'])) {
                //error_code = 0 succ
                $response ['price'] = bcmul ($price,$shenjianExchangeRe['data'] ['rate']);
                $response ['exchange'] = $shenjianExchangeRe['data'] ['rate'];
                return $response;
            }
            return $response;
        }
    }

    public static function getExchangedRMBTest($price,$currency,$redis = null) {
        bcscale (2);
        $responseData ['exchange_rate'] = '' ;
        $responseData ['currency_from'] = '' ;
        $responseData ['currency_to'] = 'CNY' ;
        $responseData ['org_price'] = $price ;
        $responseData ['exchange_price'] = '' ;
        if (strtoupper($currency) == 'RMB') {
            $optCurrency = 'CNY';
        } else {
            $optCurrency = $currency ;
        }
        $responseData ['currency_from'] = strtoupper($optCurrency);
        if ($redis) {
            $rateInfo = $redis->getCurrencyExchange('CNY_'.$optCurrency);
        } else {
            $rateInfo = self::where('currency_form_code','CNY')->where('currency_to_code',$optCurrency)->first(['id','exchange_rate']);
        }
        $exchange_rate_exceptional = true;
        if ($rateInfo) {
            if ($rateInfo ['exchange_rate'] > 0 ) {
                $responseData ['exchange_price'] =  bcmul ($price,$rateInfo['exchange_rate']);
                $responseData ['exchange_rate'] = $rateInfo['exchange_rate'];
            }
        }
        if ($exchange_rate_exceptional) {
            $currency = strtoupper($currency);
            $rateArr = self::getRate();
            if (array_key_exists($currency,$rateArr)) {
                $responseData ['exchange_price'] =  bcmul ($price,$rateArr[$currency]);
                $responseData ['exchange_rate'] = $rateArr[$currency];
            }
            $shenjianExchangeUrl = 'https://api.shenjian.io/exchange/currency';
            $param ['appid'] = env('SHENJIAN_EXCHANGE_SECRET');
            $param ['to'] = 'CNY';
            $param ['form'] = $currency;
            $url = Curl::combineURL($shenjianExchangeUrl,$param);
            $shenjianExchangeRe = json_decode(Curl::curl_get($url),true);
            if (empty($shenjianExchangeRe ['error_code'])) {
                //error_code = 0 succ
                $responseData ['exchange_price'] =  bcmul ($price,$shenjianExchangeRe['data'] ['rate']);
                $responseData ['exchange_rate'] =  $shenjianExchangeRe['data'] ['rate'];
            }
        }
        return $responseData;
    }

    /**
     * @note
     * 汇率列表
     * @since: 2019/3/28
     * @author: zt7387
     * @param:
     * @return: array
     */
    public static function ajaxGetExchangeData($data,$limit,$page,$user_id){
        $collection = self::with(['maintain'=>function($query) use($user_id) {
            $query->where('user_id',$user_id);
        }]);
        if($data['currency_form_name']){
            $collection->where('currency_form_name','like','%'.$data['currency_form_name'].'%');
        }
        if($data['currency_form_code']){
            $collection->whereIn('currency_form_code',$data['currency_form_code']);//['currency_form_code'=>$data['currency_form_code']]
        }

        $count = $collection->count();
        $res = $collection->where(['is_show'=>self::ISSHOW])->skip(($page-1)*$limit)->take($limit)->orderBy('updated_at','desc')->get();
        return [
            'count' => $count,
            'data' => $res
        ];
    }

    /**
     * @note
     * 获取汇率币种
     * @since: 2019/4/17
     * @author: zt7837
     * @return: array
     */
    public static function getSettingExchange(){
        return self::get(['currency_form_name','currency_form_code']);
    }

    /**
     * @desc 获取所有的货币
     * @author zt6650
     * CreateTime: 2019-04-23 16:57
     * @return SettingCurrencyExchange[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllCurrency($user_id)
    {
        $re = self::select( 'id','currency_form_code as code','currency_form_name as name')->get();

        return $re ;
    }

}