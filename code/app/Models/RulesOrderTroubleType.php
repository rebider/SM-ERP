<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RulesOrderTroubleType
 * Notes: 订单问题规则类型表
 * @package App\Models
 * Data: 2019/3/13 10:28
 * Author: zt7785
 */
class RulesOrderTroubleType extends Model
{
    protected $table = 'rules_order_trouble_type';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','trouble_type_name','trouble_type_desc','opening_status','created_at','updated_at'];

    /**
     * @var 开启
     */
    const STATUS_OPENING = 1;
    /**
     * @var 关闭
     */
    const STATUS_CLOSEDING = 2;

    /**
     * @var 订单问题规则类型
     */
    const TROUBLE_FROM_TYPE_TASK = 1;
    /**
     * @var 其他类型
     */
    const TROUBLE_FROM_TYPE_OTHER = 2;


    /**
     * @return array
     * Note: 获取问题类型
     * Data: 2019/3/13 18:19
     * Author: zt7785
     */
    public static function getTroubleType ($fromType = self::TROUBLE_FROM_TYPE_TASK) {
        $collection = self::where('opening_status',self::STATUS_OPENING);
        if (!empty($fromType)){
            $collection->where('trouble_from_type',$fromType);
        }
        return $collection->get(['id','trouble_type_name'])->toArray();
    }
}
