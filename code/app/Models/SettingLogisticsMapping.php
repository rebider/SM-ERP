<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SettingLogisticsMapping
 * Notes: 物流映射
 * @package App\Models
 * Data: 2019/6/4 10:14
 * Author: zt7785
 */
class SettingLogisticsMapping extends Model
{
    protected $table = 'setting_logistics_mapping';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","created_man","user_id","plat_id","logistic_id","logistic_name","plat_logistic_name","is_deleted","created_at","updated_at","carrier_name"];

    /**
     * @var 已删除
     */
    const IS_DELETED = 1;
    /**
     * @var 未删除
     */
    const UN_DELETED = 0;


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

    public function Plat(){
        return $this->belongsTo(Platforms::class,'plat_id','id')->select(['id','name_CN','name_EN']);
    }

    public function Logistics()
    {
        return $this->belongsTo(SettingLogistics::class,  'logistic_id', 'id');
    }

    /**
     * @param $param
     * @param int $offset
     * @param int $limit
     * @return array
     * Note: 物流映射首页数据
     * Data: 2019/6/4 10:14
     * Author: zt7785
     */
    public static function getLogisticsMappingDatas($param, $offset = 1, $limit = 0)
    {
        $collection = self::with('Plat','Logistics');

        $collection->where('is_deleted',self::UN_DELETED);

        //用户id
        if (isset($param['user_id'])) {
            $param['user_id'] && $collection->where('user_id',$param['user_id']);
        }
        //平台id
        $param['plat_id'] && $collection->where('plat_id',$param['plat_id']);
        //物流id
        $param['logistic_id'] && $collection->where('logistic_id',$param['logistic_id']);
        //规则名称 暂未模糊 0402 V2.13 F20.0 模糊查询

        $param['plat_logistic_name'] !== '' && $collection->where('plat_logistic_name','like','%'.$param['plat_logistic_name'].'%');
        //表单列排列顺序：最新创建的规则，排列在前面； id排序一个意思
        if ($limit) {
            $result ['count'] = $collection->count();
            $result ['data'] = $collection->orderBy('id','desc')->skip(($offset - 1) * $limit)->take($limit)->get(['id','logistic_name','plat_logistic_name','plat_id','created_at','updated_at','carrier_name'])->toArray();
            return $result;
        } else {
            return $collection->orderBy('id')->get()->toArray();
        }
    }

    /**
     * @param $id
     * @param int $user_id
     * @return Model|null|static
     * Note: 获取物流映射信息
     * Data: 2019/6/4 10:16
     * Author: zt7785
     */
    public static function getlogisticsMappingInfoById($id,$user_id = 0 )
    {
        $collection = self::where('id',$id)->where('is_deleted',self::UN_DELETED);
        $user_id && $collection->where('user_id',$user_id);
        return $collection->first();
    }

    /**
     * @param int $id
     * @param $data
     * @return Model
     * Note: updateOrCreate 返回模型
     * Data: 2019/3/12 19:07
     * Author: zt7785
     */
    public static function postDatas($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }
}
