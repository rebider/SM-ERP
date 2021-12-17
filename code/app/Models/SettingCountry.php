<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingCountry extends Model
{
    protected $table = 'setting_country';

    public $primaryKey = 'id';

    public $fillable = ['id','country_name','country_name_en','country_local_name','country_alias','country_code','country_sort','country_short_name','trade_country'];

    /**
     * @return array
     * Note: 获取所有国家
     * Data: 2019/3/22 11:53
     * Author: zt7785
     */
    public static function getAllCountry()
    {
        return self::orderBy('country_sort')->get(['id','country_name','country_code','country_sort'])->toArray();
    }

    /**
     * @param $ids
     * @return array
     * Note: 编辑信息
     * Data: 2019/3/22 12:19
     * Author: zt7785
     */
    public static function getAllCountryExclud($ids)
    {
        return self::whereNotIn('id',$ids)->orderBy('country_sort')->get(['id','country_name','country_code','country_sort'])->toArray();
    }

    /**
     * @param $ids
     * @return array
     * Note: 编辑信息
     * Data: 2019/3/22 12:19
     * Author: zt7785
     */
    public static function getAllCountryInclud($ids)
    {
        return self::whereIn('id',$ids)->orderBy('country_sort')->get(['id','country_name','country_code','country_sort'])->toArray();
    }

    public static function getCountryIdByCode($code)
    {
        return self::where('country_code',$code)->pluck('id')->first();
    }
}
