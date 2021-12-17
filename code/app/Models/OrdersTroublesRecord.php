<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class OrdersTroublesRecord
 * Notes: 订单问题记录表
 * @package App\Models
 * Data: 2019/3/7 14:30
 * Author: zt7785
 */
class OrdersTroublesRecord extends Model
{
    protected $table = 'orders_troubles_record';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','order_id','trouble_rule_id','trouble_name','trouble_desc','manage_remark','manage_id','dispose_status','created_at','updated_at'];

    /**
     * @var 问题类型订单
     */
    const QUESTION_TYPE_ORDERS = 1;

    /**
     * @var 问题类型仓库
     */
    const QUESTION_TYPE_WAREHOUSES = 2;

    /**
     * @var 问题类型物流
     */
    const QUESTION_TYPE_LOGISTICS = 3;
    /**
     * @var 问题类型拦截
     */
    const QUESTION_TYPE_INTERCEPT = 5;

    /**
     * @var 处理状态处理中
     */
    const STATUS_DISPOSING = 1;

    /**
     * @var 处理状态已处理
     */
    const STATUS_DISPOSED = 2;
    /**
     * @var 订单问题类型 合并订单
     */
    const STATUS_MERGE = 4;

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
     * @return $this
     * Note: 处理人
     * Data: 2019/5/16 9:22
     * Author: zt7785
     */
    public function Manage()
    {
        return $this->belongsTo(Users::class, 'manage_id', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 订单表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function Orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    /**
     * @return mixed
     * Note: 获取三种问题类型记录条数
     * Data: 2019/3/15 16:03
     * Author: zt7785
     */
    public static function getProblemTypeCount()
    {
        $result ['orders'] = self::where([
            ['question_type',self::QUESTION_TYPE_ORDERS],
            ['dispose_status',self::STATUS_DISPOSING],
        ])->count();

        $result ['warehouses'] = self::where([
            ['question_type',self::QUESTION_TYPE_WAREHOUSES],
            ['dispose_status',self::STATUS_DISPOSING],
        ])->count();

        $result ['logistics'] = self::where([
            ['question_type',self::QUESTION_TYPE_LOGISTICS],
            ['dispose_status',self::STATUS_DISPOSING],
        ])->count();

        return $result;
    }

    /**
     * @param int $id
     * @param $data
     * @return Model
     * Note: 新增更新
     * Data: 2019/3/13 18:41
     * Author: zt7785
     */
    public static function postDatas($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param $user_id
     * Note: 未涉及权限
     * Data: 2019/6/17 10:19
     * Author: zt7785
     */
    public static function getOrderProblemCount($user_id) {
//        $collection = self::with('Orders');
//
//        $collection = $collection->whereHas('Orders',function ($query) use($user_id) {
//            $query->where('user_id',$user_id);
//            $query->where('status',Orders::ORDER_STATUS_UNFINISH);
//            $query->where('picking_status',Orders::ORDER_PICKING_STATUS_UNMATCH);
//        })->where('dispose_status',self::STATUS_DISPOSING)->count();

        $collection = Orders::with('OrdersTroublesRecord');
        $collection = $collection->whereHas('OrdersTroublesRecord',function ($query) {
            $query->where('dispose_status',self::STATUS_DISPOSING);
        })->where('user_id',$user_id)->where('status',Orders::ORDER_STATUS_UNFINISH)->where('picking_status',Orders::ORDER_PICKING_STATUS_UNMATCH)->count();
        return $collection;
    }
}