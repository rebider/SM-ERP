<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
/**
 * Class SettingCurrencyExchangeHistory
 * Notes: 历史汇率表
 * @package App
 * Data: 2018/10/30 18:14
 * Author: zt7785
 */
class SettingCurrencyExchangeHistory extends Model
{
    //TODO
    //需要增加历史记录 人工维护汇率 不适用 换成中行接口  感觉不对劲 汇率表 针对客户应该有个 针对全平台有一个
    protected $table = 'setting_currency_exchange_history';
    public $timestamps = true;
    public $fillable = ['id','currency_to_code','currency_to_name','currency_form_code','currency_form_name','exchange_rate','created_at','updated_at'];
    const SHOWEXCHANGE  = 1;
    const UNSHOWEXCHANGE = 2;

    /**
     * Note: 生成汇率历史记录
     * Data: 2019/3/7 17:33
     * Author: zt7785
     */
    public static function exchangeRecord ()
    {
        return DB::insert('INSERT INTO setting_currency_exchange_history (currency_to_code,currency_to_name,currency_form_code,currency_form_name,exchange_rate,created_at,updated_at) SELECT currency_to_code,currency_to_name,currency_form_code,currency_form_name,exchange_rate,created_at,updated_at FROM setting_currency_exchange');
    }

    /**
     * @note
     * 关联官方汇率表
     * @since: 2019/4/3
     * @author: zt7837
     * @param:
     * @return: array
     */
    public  function currencyExchange(){
        return $this->belongsTo(SettingCurrencyExchange::class,'cur_exchange_id','id')->select('currency_to_name');
    }

    /**
     * @note
     * 获取历史汇率数据
     * @since: 2019/3/28
     * @author: zt7837
     * @param:
     * @return: array
     */
    public static function getHistoryData($currency_form_name,$user_id){
        $collection = self::with('currencyExchange')->where(['user_id'=>$user_id])->whereHas('currencyExchange',function($query){
            $query->where(['is_show'=>SettingCurrencyExchange::ISSHOW]);
        });
        if(isset($currency_form_name) && !empty($currency_form_name)){
            $collection->where('currency_form_name','like','%'.$currency_form_name.'%');
        }
        return $collection->orderBy('updated_at','desc')->get();
    }

}
