<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class OrdersAfterSales
 * Notes: 售后单
 * @package App\Models
 * Data: 2019/3/7 14:12
 * Author: zt7785
 */
class OrdersAfterSales extends Model
{
    protected $table = 'orders_after_sales';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','order_id','swap_order_id','currency_code','rate','resolvent_id','invoice_id','warehouse_id','after_sale_code','type','sales_return_status','again_deliver_status','refund','supplement','created_at','updated_at'];

    /**
     * @var 操作类型退货
     */
    const AFTERSALE_TYPE_RETURN = 1;
    /**
     * @var 操作类型换货
     */
    const AFTERSALE_TYPE_EXCHANGE = 2;
    /**
     * @var 操作类型退款
     */
    const AFTERSALE_TYPE_REFUND = 3;
    /**
     * @var 是否作废 不作废
     */
    const AFTER_UNCANCEL = 1;

    /**
     * @var 是否作废 作废
     */
    const AFTER_CANCEL = 2;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 换货单
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function SwapOrders()
    {
        return $this->belongsTo(Orders::class, 'swap_order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Note: 售后单商品
     * Data: 2019/4/12 9:38
     * Author: zt7785
     */
    public function OrdersAfterSalesProducts()
    {
        return $this->hasMany(OrdersAfterSalesProducts::class, 'after_sale_id', 'id');
    }

    /**
     * @param $order_id
     * @param $type
     * @return $this
     * Note: 根据订单id获取售后单及商品信息
     * Data: 2019/4/12 9:50
     * Author: zt7785
     */
    public static function getAfterSaleInfoByOrder_id($order_id,$type)
    {
        $collection = self::with(['OrdersAfterSalesProducts'=>function ($query) {
            $query->select('id','created_man','after_sale_id','product_name','goods_id','sku','attribute','number','univalence','rate','currency');
        }]);
         return $collection->where('order_id',$order_id)->where('type',$type)->get(['id','created_man','order_id','swap_order_id','resolvent_id','invoice_id','warehouse_id','after_sale_code','type','sales_return_status','again_deliver_status','refund','supplement','currency_code','rate'])->toArray();
    }

    /**
     * @param $orderInfo
     * @param $amount
     * @param $type
     * Note: 生成退款单
     * Data: 2019/4/11 18:28
     * Author: zt7785
     */
    public static function creatAfterSaleBill($orderInfo ,$amount ,$type,$currencyInfo)
    {
        $salesInfo ['created_man'] = $orderInfo ['user_id'];
        $salesInfo ['order_id'] = $orderInfo ['id'];
        $salesInfo ['after_sale_code'] = self::getAfterSaleCodeByOrder_id($orderInfo['id'],$orderInfo['order_number']);
        $salesInfo ['type'] = $type;
        $salesInfo ['currency_code'] = $currencyInfo['currency_code'];
        $salesInfo ['rate'] = $currencyInfo['rate'];
        $salesInfo ['sales_return_status'] = 1;
        $salesInfo ['again_deliver_status'] = 1;
        $salesInfo ['refund'] = $amount;
        $salesInfo ['supplement'] = 0.00;
        self::postGoods(0,$salesInfo);
    }

    /**
     * @note
     * 创建售后单
     * @since: 2019/4/17
     * @author: zt7837
     * @return: array
     */
    public static function creatAfterOrder($orderInfo ,$type,$currencyInfo)
    {
        $salesInfo ['created_man'] = $orderInfo ['user_id'];
        $salesInfo ['user_id'] = $orderInfo ['user_id'];
        $salesInfo ['order_id'] = $orderInfo ['id'];
        $salesInfo ['swap_order_id'] = isset($orderInfo ['swap_order_id']) && !empty($orderInfo ['swap_order_id']) ? $orderInfo ['swap_order_id'] : 0;
        $salesInfo ['invoice_id'] = isset($orderInfo ['invoice_id']) && !empty($orderInfo ['invoice_id']) ? $orderInfo ['invoice_id'] : 0;
        $salesInfo ['warehouse_id'] = isset($orderInfo ['warehouse_id']) && !empty($orderInfo ['warehouse_id']) ? $orderInfo ['warehouse_id'] : 0;
        $salesInfo ['after_sale_code'] = self::getAfterSaleCodeByOrder_id($orderInfo['id'],$orderInfo['order_number']);
        $salesInfo ['type'] = $type;
        $salesInfo ['currency_code'] = $currencyInfo['currency_code'];
        $salesInfo ['rate'] = $currencyInfo['rate'];
        $salesInfo ['sales_return_status'] = 1;
        $salesInfo ['again_deliver_status'] = 1;
        $salesInfo ['refund'] = $orderInfo['refund'];
        $salesInfo ['is_cancel'] = self::AFTER_UNCANCEL;
        $salesInfo ['created_at'] = date('Y-m-d H:i:s');
        $salesInfo ['updated_at'] = date('Y-m-d H:i:s');
        $salesInfo ['supplement'] = 0.00;
        return self::insertGetId($salesInfo);
//        return self::postGoods(0,$salesInfo);
    }

    public static function postGoods($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param $order_id
     * @param $order_code
     * @return string
     * Note: 获取售后单号
     * Data: 2019/4/11 18:27
     * Author: zt7785
     */
    public static function getAfterSaleCodeByOrder_id($order_id,$order_code)
    {
        $afterInfoNums = self::where('order_id',$order_id)->count();
        if (empty($afterInfoNums)) {
            $afterInfoNums = 0;
        }
        $afterInfoNums++;
        return CodeInfo::getACode(CodeInfo::AFTER_SALES_CODE,$order_code,$afterInfoNums);
    }

    /**
     * @note
     * 售后单数据列表
     * @since: 2019/4/18
     * @author: zt7837
     * @return: array
     */
    public static function getAfterOrder($param,$limit,$page,$user_id){
        $collection = self::with('Orders');
        $collection->whereHas('Orders',function ($query) use ($param,$user_id) {
            $query->where('user_id',$user_id);
            if (isset($param ['source_shop'])) {
                $query->whereIn('source_shop',$param['source_shop']);
            }
        });
        $param['type'] && $collection->where(['type' =>$param['type'] ]);
        $param['is_cancel'] && $collection->where(['is_cancel'=>$param['is_cancel']]);
        $param['after_sale_code'] && $collection->where(['after_sale_code' =>$param['after_sale_code']]);
        $param['order_number'] && $collection->whereHas('Orders',function($query) use ($param){
            $query->where(['order_number' =>$param['order_number']]);
        });
        $param['start_time'] && $collection->where('created_at','>=',$param['start_time']);
        $param['end_time'] && $collection->where('created_at','<=',date('Y-m-d H:i:s',strtotime($param['end_time']) + 60*60*24));
        $count = $collection->count();
        $data = $collection->where(['user_id'=>$user_id])->skip(($page-1)*$limit)->take($limit)->orderBy('created_at','desc')->get()->toArray();
        return [
            'count' => $count,
            'data' => $data
        ];
    }

    /**
     * @note
     * 获取售后单详情
     * @since: 2019/4/19
     * @author: zt7837
     * @return: array
     */
    public static function getAfterOrderDetail($id,$user_id){
        return self::with([
            'OrdersAfterSalesProducts',
            'OrdersAfterSalesProducts.goods',
            'Orders' => function($query){
            $query->select('id','order_number');
            }
        ])->where(['id' => $id,'user_id'=>$user_id])->first();
    }
}
