<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class SettingCurrencyExchangeMaintain
 * Notes: 历史汇率表
 * @package App
 * Data: 2018/10/30 18:14
 * Author: zt7785
 */
class SettingCurrencyExchangeMaintain extends Model
{
    protected $table = 'setting_currency_exchange_maintain';
    public $timestamps = true;
    public $fillable = ['id','created_man','currency_exchange_id','currency_form_code','currency_form_name','exchange_rate','created_at','updated_at'];

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    /**
     * Note: 生成汇率历史记录
     * Data: 2019/3/7 17:33
     * Author: zt7785
     */
    public static function exchangeRecord ()
    {

    }

    //待完成
    /**
     * @param $currency 币种code
     * @param $user_id 客户id
     * @return string 汇率信息
     * Note: 根据客户id 币种 响应汇率
     * Data: 2019/4/11 10:22
     * Author:
     */
    public static function getExchangeByCodeUserid($currency, $user_id)
    {
        $main_wh = ['currency_form_code'=>$currency,'user_id'=>$user_id];
        $rate = SettingCurrencyExchangeMaintain::where($main_wh)->select('exchange_rate')->first();
        if(!$rate) {
           $rate = SettingCurrencyExchange::where(['currency_form_code'=>$currency])->select('exchange_rate')->first();
        }
        $rate_arr = $rate ? $rate->toArray() : '';
        $exchange_rate = '';
        if(!empty($rate_arr)) {
            $exchange_rate = $rate_arr['exchange_rate'];
        }

        return $exchange_rate ? $exchange_rate : '0.00' ;
    }

}
