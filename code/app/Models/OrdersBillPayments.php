<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class OrdersBillPayments
 * Notes: 付款|退款记录表
 * @package App\Models
 * Data: 2019/3/7 14:25
 * Author: zt7785
 */
class OrdersBillPayments extends Model
{
    protected $table = 'orders_bill_payments';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','order_id','bill_code','order_type','currency_code','rate','type','amount','status','created_at','updated_at'];


    /**
     * @var 原始订单乐天
     */
    const ORDERS_ORIG_RAKUTEN = 1;

    /**
     * @var 原始订单亚马逊
     */
    const ORDERS_ORIG_AMAZON = 2;
    /**
     * @var CW_REP订单
     */
    const ORDERS_CWERP = 3;

    /**
     * @var 单据类型付款
     */
    const BILLS_PAY = 1;
    /**
     * @var 单据类型退款
     */
    const BILLS_REFUND = 2;

    /**
     * @var 单据初始化状态
     */
    const BILLS_STATUS_INIT = 0;
    /**
     * @var 单据完成状态
     */
    const BILLS_STATUS_FINISH = 1;

    /**
     * @var 单据取消状态
     */
    const BILLS_STATUS_CANCEL= 2;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 订单表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function Orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }


    public static function postData($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param $options
     * @return array
     * Note: 获取付款|退款单信息
     * Data: 2019/4/16 15:57
     * Author: zt7785
     */
    public static function getBillsByOptionss($options)
    {
        $collection = self::select('id','created_man','order_id','order_type','currency_code','rate','type','amount','status');
        foreach ($options as $key => $val) {
            $collection->where($key,$val);
        }
        return $collection->get()->toArray();
    }
}
