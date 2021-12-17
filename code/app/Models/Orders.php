<?php

namespace App\Models;

use App\Auth\Models\Menus;
use App\Auth\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Orders
 * Notes: 平台订单主表
 * @package App\Models
 * Data: 2019/3/7 10:20
 * Author: zt7785
 */
class Orders extends Model
{
    protected $table = 'orders';
    public $timestamps = true;
    public $primaryKey = 'id';
    public $fillable = ['id', 'created_man', 'platforms_id', 'user_id', 'warehouse_id', 'country_id', 'logistics_id', 'source_shop', 'order_number', 'plat_order_number', 'type', 'platform_name', 'source_shop_name', 'picking_status', 'deliver_status', 'intercept_status', 'sales_status', 'status', 'order_price', 'currency_code', 'rate', 'payment_method', 'freight', 'currency_freight', 'postal_code', 'country', 'province', 'city', 'mobile_phone', 'phone', 'addressee_name', 'addressee_email', 'warehouse', 'logistics', 'addressee', 'addressee1', 'addressee2', 'mark', 'problem', 'match_fail_reason', 'logistics_time', 'order_time', 'cancel_reason', 'intercept_reason', 'payment_time', 'radio', 'created_at', 'updated_at', 'logistics_choose_status', 'warehouse_choose_status', 'invoices_freight_currency_code', 'invoices_freight', 'merge_orders_id'];
    /**
     * @var 订单状态未完成status
     */

    const ORDER_STATUS_UNFINISH = 1;//订单状态--未完成status
    /**
     * @var 订单状态已完成status
     */
    const ORDER_STATUS_FINISHED = 2;//订单状态--已完成status
    /**
     * @var 订单状态已作废status
     */
    const ORDER_STATUS_OBSOLETED = 3;//订单状态--已作废status
    /**
     * @var 订单配货状态未匹配
     */
    const ORDER_PICKING_STATUS_UNMATCH = 1;//配货状态--未匹配
    /**
     * @var 订单配货状态已匹配成功
     */
    const ORDER_PICKING_STATUS_MATCHED_SUCC = 2;//配货状态--已配货
    /**
     * @var 订单配货状态已部分匹配成功
     */
    const ORDER_PICKING_STATUS_MATCHED_PART = 3;//配货状态--已部分匹配成功
    /**
     * @var 订单配货状态匹配失败
     */
    const ORDER_PICKING_STATUS_MATCHED_FAIL = 4;//配货状态--匹配失败
    /**
     * @var 订单发货状态未发货
     */
    const ORDER_DELIVER_STATUS_UNFILLED = 1;//发货状态--未发货
    /**
     * @var 订单发货状态发货成功
     */
    const ORDER_DELIVER_STATUS_FILLED = 2;//发货状态--发货成功
    /**
     * @var 订单发货状态已部分发货
     */
    const ORDER_DELIVER_STATUS_FILLED_PART = 3;//发货状态--已部分发货
    /**
     * @var 订单拦截状态未拦截初始状态
     */
    const ORDER_INTERCEPT_STATUS_INITIAL = 1;//拦截状态--未拦截初始状态
    /**
     * @var 订单拦截状态拦截中
     */
    const ORDER_INTERCEPT_STATUS_INTERCEPTING = 2;//拦截状态--拦截中
    /**
     * @var 订单拦截状态拦截成功
     */
    const ORDER_INTERCEPT_STATUS_INTERCEPTED = 3;//拦截状态--拦截成功
    /**
     * @var 订单拦截状态拦截失败
     */
    const ORDER_INTERCEPT_STATUS_FAILED = 4;//拦截状态--拦截失败
    /**
     * @var 订单售中状态未申请部分退款初始状态
     */
    const ORDER_SALES_STATUS_INITIAL = 1;//售中状态--退款初始状态
    /**
     * @var 订单售中状态部分退款申请中
     */
    const ORDER_SALES_STATUS_APPLYING = 2;//售中状态--退款申请中
    /**
     * @var 订单售中状态申请部分退款成功
     */
    const ORDER_SALES_STATUS_APPLYED = 3;//售中状态--部分退款成功
    /**
     * @var 订单拦截状态退款失败
     */
    const ORDER_SALES_STATUS_FAILED = 4;//售中状态--退款失败

    /**
     * @var 订单菜单id
     */
    const ORDER_MENUS_ID = 4;
    /**
     * @var 订单类型接口获取
     */
    const ORDERS_GETINFO_API = 1;
    /**
     * @var 订单类型手动创建
     */
    const ORDERS_GETINFO_MANUAL = 2;
    /**
     * @var 订单类型售后单
     */
    const ORDERS_GETINFO_AFTER = 3;
    /**
     * @var 订单类型合并订单
     */
    const ORDER_MERGE_TYPE = 4;

    const NO_PROBLEM = 0;//没问题
    const A_PROBLEM = 1;//部分缺货
    const B_PROBLEM = 2;//超重需拆包
    /**
     * @var 无法找到仓库
     */
    const C_PROBLEM = 3;//无法找到仓库
    /**
     * @var 无法找到物流
     */
    const D_PROBLEM = 4;//无法找到物流
    const O_PROBLEM = 5;//其它

    /**
     * @var 订单物流仓库选中状态默认未选中
     */
    const ORDER_CHOOSE_STATUS_UNCHECKED = 0;

    /**
     * @var 订单物流仓库选中状态选中
     */
    const ORDER_CHOOSE_STATUS_CHECKED = 1;

    /**
     * @return $this
     * Note: 平台表
     * Data: 2019/3/7 11:34
     * Author: zt7785
     */
    public function Platforms()
    {
        return $this->belongsTo(Platforms::class, 'platforms_id', 'id')->select(['id', 'created_man', 'name_CN', 'name_EN']);
    }

    /**
     * @var API同步订单
     */
    const ORDER_FROM_TYPE_API = 1;

    /**
     * @var 手动创建订单
     */
    const ORDER_FROM_TYPE_MANUAL = 0;

    /**
     * @var 售后订单
     */
    const ORDER_FROM_TYPE_AFTERSALE = 1;

    /**
     * @var 合并订单
     */
    const ORDER_CHOOSE_STATUS_MERGE = 0;

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    /**
     * @return $this
     * Note: 累加运费
     * Data: 2019/3/23 16:04
     * Author: zt7785
     */
    public function OrdersInvoicesValue()
    {
        return $this->hasMany(OrdersInvoices::class, 'order_id', 'id')->where('invoices_status','<>',OrdersInvoices::DEL_INVOICES_STATUS)->orderBy('created_at', 'desc')->select('id', 'order_id', 'invoices_number', 'currency_code', 'sync_status', 'created_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 配货单表
     * Data: 2019/3/11 15:00
     * Author: zt7785
     */
    public function OrdersInvoices()
    {
        return $this->belongsTo(OrdersInvoices::class, 'id', 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 配货单表
     * Data: 2019/3/11 15:00
     * Author: zt7785
     */
    public function OrdersInvoicesMany()
    {
        return $this->hasMany(OrdersInvoices::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 配货单表
     * Data: 2019/3/11 15:00
     * Author: zt7785
     */
    public function OrdersTroublesRecord()
    {
        return $this->hasMany(OrdersTroublesRecord::class, 'order_id', 'id')->select('id', 'order_id', 'created_man', 'question_type', 'trouble_name', 'dispose_status', 'trouble_desc', 'created_at', 'updated_at', 'manage_id', 'manage_remark', 'trouble_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Note: 订单日志
     * Data: 2019/3/11 18:52
     * Author: zt7785
     */
    public function OrdersLogs()
    {
        return $this->hasMany(OrdersLogs::class, 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Note: 订单商品
     * Data: 2019/3/11 18:52
     * Author: zt7785
     */
    public function OrdersProducts()
    {
        return $this->hasMany(OrdersProducts::class, 'order_id', 'id')->where('is_deleted',OrdersProducts::ORDERS_PRODUCT_UNDELETED);
    }

    /**
     * @return $this
     * Note: 店铺设置表
     * Data: 2019/3/7 11:44
     * Author: zt7785
     */
    public function Shops()
    {
        return $this->belongsTo(SettingShops::class, 'source_shop', 'id')->select(['id', 'created_man', 'plat_id', 'shop_name', 'shop_url', 'status', 'recycle']);
    }

    /**
     * @return $this
     * Note: 付款单 赔款单
     * Data: 2019/3/7 11:44
     * Author: zt7785
     */
    public function OrdersBillPayments()
    {
        return $this->hasMany(OrdersBillPayments::class, 'order_id', 'id')->select(['id', 'created_man', 'order_id', 'order_type', 'type', 'amount', 'status', 'bill_code', 'created_at', 'currency_code']);
    }

    /**
     * @return $this
     * Note: 售后
     * Data: 2019/3/23 16:23
     * Author: zt7785
     */
    public function OrdersAfterSales()
    {
        return $this->hasMany(OrdersAfterSales::class, 'order_id', 'id')->select(['id', 'created_man', 'order_id', 'swap_order_id', 'resolvent_id', 'invoice_id', 'warehouse_id', 'after_sale_code', 'type', 'sales_return_status', 'again_deliver_status', 'refund', 'supplement', 'created_at']);
    }

    /**
     * @return $this
     * Note: 仓库
     * Data: 2019/4/11 9:40
     * Author: zt8076
     */
    public function Warehouse()
    {
        return $this->belongsTo(SettingWarehouse::class, 'warehouse_id', 'id');
    }

    /**
     * @return $this
     * Note: 物流
     * Data: 2019/4/11 9:42
     * Author: zt8076
     */
    public function Logistics()
    {
        return $this->belongsTo(SettingLogistics::class, 'logistics_id', 'id');
    }

    /**
     * @return $this
     * Note: 配货比取值
     * Data: 2019/4/12 13:33
     * Author: zt8076
     */
    public function getRadioAttribute($value)
    {
        return round($value, 1);
    }

    /**
     * @return $this
     * Note: 配货比取值
     * Data: 2019/4/12 13:33
     * Author: zt8076
     */
    public function getProblemAttribute($value)
    {
        if (empty($value)) return $value;
        $temp = [1 => '部分缺货', 2 => '超重需拆包', 3 => '无法找到仓库', 4 => '无法找到物流'];
        $data = explode(',', $value);
        $str = '';
        foreach ($data as $vv) {

            $str .= $temp[$vv] . ',';
        }
        return rtrim($str, ',');
    }

    /**
     * @param       $param
     * @param int $offset
     * @param int $limit
     * @param array $permissionParam 权限参数
     * @return mixed
     * Note: 平台订单列表数据搜索
     * Data: 2019/3/11 17:17
     * Author: zt7785
     */
    public static function getOrdersDatas($param, $permissionParam = [], $offset = 1, $limit = 0)
    {
        //todo
        //所有订单操作 基于 用户权限 应先获取客户平台 店铺权限id
        $collection = self::with('OrdersInvoicesMany','OrdersTroublesRecord');
//        $collection->whereBetween('status',[self::ORDER_STATUS_UNFINISH,self::ORDER_STATUS_FINISHED]);
        //用户id
//        $collection->where('id', 413);
        if ($permissionParam) {
            $collection->where('user_id', $permissionParam ['user_id']);
            //店铺权限
            if (isset($permissionParam ['source_shop'])) {
                $collection->whereIn('source_shop', $permissionParam ['source_shop']);
            }
        }
        //问题订单
        if (isset($param['is_problem']) && $param['is_problem'] ) {
            if ($param['question_type']) {
                $collection->whereHas('OrdersTroublesRecord', function ($query) use ($param) {
                    $query->where('trouble_type_id', $param['question_type']);
                    $query->where('dispose_status', OrdersTroublesRecord::STATUS_DISPOSING);
                });
            } else {
                //有问题的订单
                $collection->whereHas('OrdersTroublesRecord', function ($query) use ($param) {
                    $query->where('dispose_status', OrdersTroublesRecord::STATUS_DISPOSING);
                });
            }
        } else {
            //问题类型
            if ($param['question_type']) {
                $collection->whereHas('OrdersTroublesRecord', function ($query) use ($param) {
                    $query->where('trouble_type_id', $param['question_type']);
                    $query->where('dispose_status', OrdersTroublesRecord::STATUS_DISPOSING);
                });
            }
        }

        if (isset($param['problem'])) {
            $collection->where('problem', $param['problem']);
        }

        //配货状态
        $param['picking_status'] && $collection->where('picking_status', $param['picking_status']);
        //发货状态
        $param['deliver_status'] && $collection->where('deliver_status', $param['deliver_status']);

        //平台id
        $param['platforms_id'] && $collection->where('platforms_id', $param['platforms_id']);
        //店铺id
        $param['source_shop'] && $collection->where('source_shop', $param['source_shop']);
        //店铺id
        $param['order_number'] && $collection->where('order_number', $param['order_number']);
        //物流方式
        $param['logistics_id'] && $collection->where('logistics_id', $param['logistics_id']);
//        if ($param['logistics_id']) {
//            $collection->whereHas('OrdersInvoices', function ($query) use ($param) {
//                $query->where('logistics_id', $param['logistics_id']);
//            });
//        }
        //仓库id
        $param['warehouse_id'] && $collection->where('warehouse_id', $param['warehouse_id']);
//        if ($param['warehouse_id']) {
//            $collection->whereHas('OrdersInvoices', function ($query) use ($param) {
//                $query->where('warehouse_id', $param['warehouse_id']);
//            });
//        }
        //自定义搜索
        if ($param['search_type']) {
            if (isset($param['search_type_code']) && !empty($param['search_type_code'])) {
                switch ($param['search_type']) {
                    case 1 :
                        //电商单号
                        $param['search_type_code'] && $collection->where('plat_order_number', $param['search_type_code']);
                        break;
                    case 2 :
                        //物流跟踪号
                        $collection->whereHas('OrdersInvoices', function ($query) use ($param) {
                            $query->where('tracking_no', $param['search_type_code']);
                        });
                        break;
                    case 3 :
                        //收件人
                        $param['search_type_code'] && $collection->where('addressee_name', $param['search_type_code']);
                        break;
                    case 4 :
                        //收件人邮箱
                        $param['search_type_code'] && $collection->where('addressee_email', $param['search_type_code']);
                        break;
                }
            }
        }
        //自定义时间区间
        if ($param['times_type']) {
            $field = '';
            switch ($param['times_type']) {
                case 1;
                    //下单时间
                    $field = 'order_time';
                    break;
                case 2;
                    //创建时间
                    $field = 'created_at';
                    break;
                case 3;
                    //付款时间
                    $field = 'order_time';
                    break;
                case 4;
                    //发货时间
                    $field = 'logistics_time';
                    break;
            }
            //如果有开始时间无结束时间 默认当前时间
            if ($param['start_date'] && empty($param['end_date'])) {
                $param['end_date'] = date('Y-m-d H:i:s');
            }
            if (!empty($param['start_date']) && !empty($param['end_date'])) {
                $collection->whereBetween($field, [$param['start_date'], $param['end_date']]);
            }
        }
        if ($limit) {
            $result['count'] = $collection->count();
            $result['data'] = $collection->orderByDesc('created_at')->orderByDesc('payment_time')->skip(($offset - 1) * $limit)->take($limit)->get()->toArray();
            return $result;
        } else {
            $collection->with('OrdersProducts', 'Platforms', 'Shops', 'OrdersInvoicesValue');
            //正常状态的店铺
            $collection->with(['Shops' => function ($query) {
                $query->where('recycle', SettingShops::SHOP_RECYCLE_UNDEL);
            },
            ]);
            //退款单 已完成的退款单 一笔订单可以多笔退款单
            $collection->with(['OrdersBillPayments' => function ($query) {
                $query->where('order_type', OrdersBillPayments::ORDERS_CWERP);
                $query->where('type', OrdersBillPayments::BILLS_REFUND);
                $query->where('status', OrdersBillPayments::BILLS_STATUS_FINISH);
            },
            ]);
            return $collection->orderBy('id')->get()->toArray();
        }
    }

    /**
     * @param $order_id
     * @return array
     * Note: 获取订单详情
     * Data: 2019/3/11 18:55
     * Author: zt7785
     */
    public static function getorderDetails($order_id, $user_id)
    {
        //需要处理的问题 已处理的问题 OK
        //配货单信息 多条
        //售后单信息 没
        //付款单信息 // 省
        //日志 OK
        //店铺信息
        //平台信息
        //操作
        //DB::enableQueryLog();
        $collection = self::with( 'OrdersLogs.Users', 'OrdersTroublesRecord.Manage', 'OrdersAfterSales', 'OrdersProducts.Goods', 'Platforms', 'Shops');
        $collection->with(['OrdersInvoicesValue'=>function($query){
            $query->with('OrdersInvoicesProduct.Goods')->where('invoices_status',OrdersInvoices::ENABLE_INVOICES_STATUS);
        }]);
        $collection->with(['OrdersBillPayments' => function ($query) {
            $query->where('order_type', OrdersBillPayments::ORDERS_CWERP);
            $query->where('type', OrdersBillPayments::BILLS_PAY);
        },
        ]);
        $collection->where('id', $order_id)->where('user_id', $user_id);
//        $collection->whereBetween('status',[self::ORDER_STATUS_UNFINISH,self::ORDER_STATUS_FINISHED]);
        $result = $collection->first();
        //dd(DB::getQueryLog());
        if (empty($result)) {
            return [];
        }
        return $result->toArray();
    }

    /**
     * @param $orderInfo
     * @return mixed
     * Note: 订单状态逻辑
     * Data: 2019/3/23 18:03
     * Author: zt7785
     */
    public static function orderStatusOpeLogic($orderInfo)
    {
        //状态
        /*
         * 1.未配货 部分配货订单 允许编辑
         * 2. 未发货 部分发货订单 允许拦截
         * 3. 未发货订单 允许取消
         * 4. 未发货 部分发货 允许部分退款
         * 5. 部分发货 已发货完成 允许创建售后单
         */
        //编辑
        $action ['edit'] = '0';
        //拦截
        $action ['intercept'] = '0';
        //取消
        $action ['cancel'] = '0';
        //退款
        $action ['refound'] = '0';
        //状态
        $action ['status'] = '0';
        //售后
        $action ['aftersale'] = '0';
        //配货
        $action ['picking'] = '0';

        //配货失败 部分配货 未配货 系统订单为“未配货”“部分配货”状态时可编辑，可编辑订单的商品信息、地址信息等等
        $pickArr = [self::ORDER_PICKING_STATUS_MATCHED_FAIL, self::ORDER_PICKING_STATUS_MATCHED_PART, self::ORDER_PICKING_STATUS_UNMATCH];
        $pickArr = [self::ORDER_PICKING_STATUS_MATCHED_PART, self::ORDER_PICKING_STATUS_UNMATCH];

        if ($orderInfo ['status'] == self::ORDER_STATUS_OBSOLETED) {
            return $action;
        }
        if (in_array($orderInfo ['picking_status'], $pickArr)) {
            $action ['edit'] = '1';
        }
        //部分发货 未发货
        $deliverArr = [self::ORDER_DELIVER_STATUS_FILLED_PART, self::ORDER_DELIVER_STATUS_UNFILLED];

       // $deliverArr = [self::ORDER_DELIVER_STATUS_UNFILLED];
        if (in_array($orderInfo ['deliver_status'], $deliverArr)) {
            $action ['intercept'] = '1';
            $action ['refound'] = '1';
        }
        //未发货订单允许取消
        if ($orderInfo ['deliver_status'] == self::ORDER_DELIVER_STATUS_UNFILLED) {
            $action ['cancel'] = '1';
        }
        //部分发货 发货完成
        $deliverFinishArr = [self::ORDER_DELIVER_STATUS_FILLED_PART, self::ORDER_DELIVER_STATUS_FILLED];
        if (in_array($orderInfo ['deliver_status'], $deliverFinishArr)) {
            $action ['aftersale'] = '1';
        }
        //部分配货
        $pickingArr = [self::ORDER_PICKING_STATUS_MATCHED_SUCC, self::ORDER_PICKING_STATUS_MATCHED_PART, self::ORDER_PICKING_STATUS_MATCHED_FAIL];
        if (in_array($orderInfo ['picking_status'], $pickingArr)) {
            $action ['picking'] = '1';
        }

        return $action;
    }


    /**
     * @return array
     * Note: 便捷菜单
     * Data: 2019/3/12 16:40
     * Author: zt7785
     */
    public static function getOrderShortcutMenu($user_id)
    {
        $problemCountArr = self::getExceptionOrdersCount($user_id);
        $problemMenus ['name'] = '综合处理';
        $problemMenus ['parent_id'] = self::ORDER_MENUS_ID;
        $problemMenus ['url'] = '';
        //匹配失败 点击跳转到原始订单
        $problemMenus ['_child']['picking_fail']['name'] = '匹配失败';
        $problemMenus ['_child']['picking_fail']['menu_name'] = '原始订单';
        $problemMenus ['_child']['picking_fail']['count'] = $problemCountArr ['mappingFailCount'];
        $problemMenus ['_child']['picking_fail']['url'] = url('order/originalOrder').'?mapping_status=3';
        //订单问题 订单问题记录表
        $problemMenus ['_child']['orders_troubles']['name'] = '问题订单';
        $problemMenus ['_child']['orders_troubles']['menu_name'] = '问题订单';
        $problemMenus ['_child']['orders_troubles']['count'] = $problemCountArr ['troublesCount'];
        $problemMenus ['_child']['orders_troubles']['url'] = url('order/orderIndex').'?is_problem=true';
        //无法找到仓库 仓库问题中间表
//        $problemCountArr ['warehouseProblem'] = 10;
//        $problemCountArr ['logisticsProblem'] = 10;
        $problemMenus ['_child']['orders_warehouse_troubles']['name'] = '无法找到仓库';
        $problemMenus ['_child']['orders_warehouse_troubles']['menu_name'] = '无法找到仓库';
        $problemMenus ['_child']['orders_warehouse_troubles']['count'] = $problemCountArr ['warehouseProblem'];
        $problemMenus ['_child']['orders_warehouse_troubles']['url'] = (int)$problemCountArr ['warehouseProblem'] > 0 ? url('order/pending/index').'?problem=3' : '';
        //无法找到物流 物流问题中间表
        $problemMenus ['_child']['orders_logistics_troubles']['name'] = '无法找到物流';
        $problemMenus ['_child']['orders_logistics_troubles']['menu_name'] = '无法找到物流';
        $problemMenus ['_child']['orders_logistics_troubles']['count'] = $problemCountArr ['logisticsProblem'];
        $problemMenus ['_child']['orders_logistics_troubles']['url'] = (int)$problemCountArr ['logisticsProblem'] > 0 ? url('order/pending/index').'?problem=4' : '';

//        $menusList = array_merge([$problemMenus], array_values($menusList));
        return $problemMenus;
    }

    /**
     * @param $user_id
     * @return mixed
     * Note: 异常订单信息
     * Data: 2019/6/26 19:33
     * Author: zt7785
     */
    public static function getExceptionOrdersCount($user_id)
    {
        //问题订单数量
        $troublesCountOrm = self::with('OrdersTroublesRecord')->where(['user_id'=>$user_id,'status'=>self::ORDER_STATUS_UNFINISH,'picking_status'=>self::ORDER_PICKING_STATUS_UNMATCH]);
        $troublesCountOrm->whereHas('OrdersTroublesRecord', function ($query) {
            $query->where('dispose_status', OrdersTroublesRecord::STATUS_DISPOSING);
        });
        $response ['troublesCount'] = $troublesCountOrm->count();
        //匹配失败
        $response ['mappingFailCount'] = OrdersOriginal::where(['user_id'=>$user_id,'match_status'=>OrdersOriginal::MAPPING_STATUS_FAIL])->count();
        //无法找到物流
        $response ['logisticsProblem'] = self::where(['user_id'=>$user_id,'status'=>self::ORDER_STATUS_UNFINISH,'problem'=>self::D_PROBLEM])->count();
        //无法找到仓库
        $response ['warehouseProblem'] = self::where(['user_id'=>$user_id,'status'=>self::ORDER_STATUS_UNFINISH,'problem'=>self::C_PROBLEM])->count();
        return $response;
    }

    /**
     * @param $orderCode 订单编号
     * @param $oriPlat 源平台
     * @return mixed
     * Note: 根据源平台订单号查ERP订单
     * Data: 2019/4/11 11:11
     * Author: zt7785
     */
    public static function getOrdersByOriordercode($orderCode)
    {
//        if ($oriPlat == Platforms::AMAZON) {
//
//        } elseif ($oriPlat == Platforms::RAKUTEN) {
//
//        } else {
//            return [];
//        }
        $collection = self::with(['OrdersInvoicesValue' => function ($query) {
            $query->with(['OrdersInvoicesProduct' => function ($query) {
                $query->select('id', 'goods_id', 'sku', 'invoice_id', 'buy_number', 'already_stocked_number', 'cargo_distribution_number', 'product_name', 'attribute', 'univalence');
            }]);
            $query->select('id', 'order_id', 'invoices_number', 'warehouse_order_number', 'tracking_no', 'intercept_status', 'invoices_status');
        }]);
        $result = $collection->where('plat_order_number', $orderCode)->first(['id', 'user_id', 'picking_status', 'deliver_status', 'order_number', 'intercept_status', 'sales_status', 'status']);
        if (empty($result)) {
            return [];
        }
        return $result->toArray();
    }

    /**
     * @note
     * 根据订单号获取商品
     * @since: 2019/4/16
     * @author: zt7837
     * @return: array
     */
    public static function getOrdersByordernum($orderCode,$user_id,$shopsId = [])
    {
        $collection = self::with(['OrdersProducts' => function ($query) {
            $query->with(['Goods' => function ($querys) {
                $querys->where(['status'=>Goods::STATUS_PASS])->select('id','goods_pictures', 'goods_name','sku','id');
            }]);
            $query->where(['is_deleted'=>OrdersProducts::ORDERS_PRODUCT_UNDELETED])->where('delivery_number','>','0')->select('id', 'goods_id', 'order_id', 'order_type', 'buy_number', 'currency', 'delivery_number', 'weight', 'univalence', 'rate','aftersale_refund_number');
        }]);
        if ($shopsId) {
            $collection->whereIn('source_shop',$shopsId);
        }
        return $collection->where(['order_number'=>$orderCode,'user_id'=>$user_id])->where('deliver_status','<>',self::ORDER_DELIVER_STATUS_UNFILLED)->where('status','<>',self::ORDER_STATUS_OBSOLETED)->first(['id', 'user_id', 'picking_status', 'deliver_status', 'order_number', 'intercept_status', 'sales_status', 'status', 'rate', 'currency_code']);
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


    /**
     * @param $field
     * @param $value
     * @return string
     * Note: 订单字段修改器
     * Data: 2019/3/23 14:57
     * Author: zt7785
     */
    public static function modifier($field, $value)
    {
        $str = '';
        if ($field == 'type') {
            if ($value == 1) {
                $str = '同步订单';
            } else if ($value == 2) {
                $str = '人工订单';
            } else if ($value == 3) {
                $str = '售后订单';
            }
        } else if ($field == 'status') {
            if ($value == 1) {
                $str = '未完结';
            } else if ($value == 2) {
                $str = '已完结';
            } else if ($value == 3) {
                $str = '已作废';
            }
        } else if ($field == 'picking_status') {
            if ($value == 1) {
                $str = '未匹配';
            } else if ($value == 2) {
                $str = '匹配成功';
            } else if ($value == 3) {
                $str = '部分匹配';
            } else if ($value == 4) {
                $str = '匹配失败';
            }
        } else if ($field == 'deliver_status') {
            if ($value == 1) {
                $str = '未发货';
            } else if ($value == 2) {
                $str = '发货成功';
            } else if ($value == 3) {
                $str = '部分发货';
            }
        } else if ($field == 'intercept_status') {
            if ($value == 1) {
                $str = '未拦截';
            } else if ($value == 2) {
                $str = '拦截中';
            } else if ($value == 3) {
                $str = '拦截成功';
            } else if ($value == 4) {
                $str = '拦截失败';
            }
        } else if ($field == 'sales_status') {
            if ($value == 1) {
                $str = '未申请部分退款';
            } else if ($value == 2) {
                $str = '部分退款申请中';
            } else if ($value == 3) {
                $str = '申请部分退款成功';
            } else if ($value == 4) {
                $str = '申请部分退款失败';
            }
        }
        return $str;
    }

    /**
     * @note
     * 合并订单 新增
     * @since: 2019/5/20
     * @author: zt7837
     * @return: array
     */
    public static function addMergeOrders($ids, $cur_id, $user_id, $problem_id)
    {
        DB::beginTransaction();
        try {
            $orderInfold = self::whereIn('id',$ids)
                ->where(['user_id'=>$user_id,'picking_status'=>self::ORDER_PICKING_STATUS_UNMATCH])
                ->where('status','=',self::ORDER_STATUS_UNFINISH)
                ->where('merge_orders_id','=','')
                ->get();
            if($orderInfold->isEmpty()){
                //其他操作改变本可以合单的数据
                return false;
            }
            $orderInfold = $orderInfold->toArray();
            //新订单的电商单号 逗号隔开
            $plat_arr = array_column($orderInfold,'plat_order_number');
            $plat_str = $plat_arr ? implode(',',$plat_arr) : '';
            $pickIds = array_column($orderInfold,'id');
            $ids = array_intersect($pickIds,$ids);
            //只剩一个单 无需合并
            if(count($ids) <= 1){
                return 'error';
            }
            //订单总价
            $order_price = array_sum(array_column($orderInfold,'order_price'));
            //运费
            $freight = array_sum(array_column($orderInfold,'freight'));
            //作废旧订单
            self::whereIn('id', $ids)->update(['status' => self::ORDER_STATUS_OBSOLETED]);
            //生成新订单
            $curOrder = Orders::with('OrdersBillPayments')->where(['id' => $cur_id,'user_id' => $user_id])->first();
            if(!$curOrder){
                return false;
            }
            $param['user_id'] = $user_id;
            $param['created_man'] = $user_id;
            $param['platforms_id'] = $curOrder->platforms_id;
            $param['source_shop'] = $curOrder->source_shop;
            $param['order_number'] = CodeInfo::getACode(CodeInfo::CW_ORDERS_CODE);
            $param['type'] = self::ORDER_MERGE_TYPE;
            $param['platform_name'] = $curOrder->platform_name;
            $param['source_shop_name'] = $curOrder->source_shop_name;
            $param['picking_status'] = $curOrder->picking_status;
            $param['deliver_status'] = $curOrder->deliver_status;
            $param['intercept_status'] = $curOrder->intercept_status;
            $param['sales_status'] = $curOrder->sales_status;
            $param['status'] = self::ORDER_STATUS_UNFINISH;
            $param['order_price'] = $order_price;
            $param['currency_code'] = $curOrder->currency_code;
            $param['rate'] = $curOrder->rate;
            $param['payment_method'] = $curOrder->payment_method;
            $param['freight'] = $freight;
            $param['currency_freight'] = $curOrder->currency_freight;
            $param['postal_code'] = $curOrder->postal_code;
            $param['country_id'] = $curOrder->country_id;
            $param['country'] = $curOrder->country;
            $param['province'] = $curOrder->province;
            $param['city'] = $curOrder->city;
            $param['phone'] = $curOrder->phone;
            $param['addressee_name'] = $curOrder->addressee_name;
            $param['addressee_email'] = $curOrder->addressee_email;
            $param['warehouse_id'] = $curOrder->warehouse_id;
            $param['warehouse'] = $curOrder->warehouse;
            $param['logistics_id'] = $curOrder->logistics_id;
            $param['logistics'] = $curOrder->logistics;
            $param['addressee'] = $curOrder->addressee;
            $param['addressee1'] = $curOrder->addressee1;
            $param['addressee2'] = $curOrder->addressee2;
            $param['mark'] = $curOrder->mark;
            $param['radio'] = $curOrder->radio;
            $param['cancel_reason'] = $curOrder->cancel_reason;
            $param['match_fail_reason'] = $curOrder->match_fail_reason;
            $param['warehouse_choose_status'] = $curOrder->warehouse_choose_status;
            $param['logistics_choose_status'] = $curOrder->logistics_choose_status;
            $param['plat_order_number'] =  $plat_str;//$curOrder->plat_order_number; 逗号分隔
            $param['merge_orders_id'] = implode($ids,',');//合并订单 新增
            if(strtotime($curOrder->order_time)){
                $param['order_time'] = $curOrder->order_time;

            }
            if(strtotime($curOrder->payment_time)){
                $param['payment_time'] = $curOrder->payment_time;
            }
            $orderReObj = self::postDatas(0, $param);
            if (!$orderReObj) {
                return false;
                DB::rollback();
            }
            $orderReId = $orderReObj->id;

            //记录合单字段
            $mergeResult = Orders::whereIn('id',$ids)
                ->where(['picking_status'=>self::ORDER_PICKING_STATUS_UNMATCH])
                ->update(['merge_orders_id'=>$orderReId]);
            if(!$mergeResult){
                return false;
                DB::rollback();
            }

            //处理订单产品表
            $orderProducts = self::with('OrdersProducts.Goods')
                ->where(['picking_status'=>self::ORDER_PICKING_STATUS_UNMATCH])
                ->whereIn('id', $ids)
                ->get();
            if (!empty($orderProducts)) {
                $orderProductsArr = $orderProducts->toArray();
                $repeatGoodsId = [];
                $addProductsArr = [];
                foreach ($orderProductsArr as $key => $val) {
                    if (!empty($val['orders_products'])) {
                        foreach ($val['orders_products'] as $k => $v) {
                            //俩个订单有同一个商品 数量累加
                            if(!in_array($v['goods_id'],$repeatGoodsId)){
                                $repeatGoodsId[$key] = $v['goods_id'];
                            }else{
                                $prokey = array_search($v['goods_id'],$repeatGoodsId);
                                $addProductsArr[$prokey]['buy_number'] += $v['buy_number'];
                                continue;
                            }
                            //订单总金额
                            /*$priceTotal = $v['univalence'] * $v['buy_number'];
                            $orderPrice += $priceTotal;*/
                            $addProductsArr[$key]['created_man'] = $user_id;
                            $addProductsArr[$key]['user_id'] = $user_id;
                            $addProductsArr[$key]['order_id'] = $orderReId;
                            $addProductsArr[$key]['goods_id'] = $v['goods_id'];
                            $addProductsArr[$key]['product_name'] = $v['product_name'];
                            $addProductsArr[$key]['sku'] = $v['sku'];
                            $addProductsArr[$key]['currency'] = $v['currency'];
                            $addProductsArr[$key]['buy_number'] = $v['buy_number'];
                            $addProductsArr[$key]['already_stocked_number'] = $v['already_stocked_number'];
                            $addProductsArr[$key]['cargo_distribution_number'] = $v['cargo_distribution_number'];
                            $addProductsArr[$key]['delivery_number'] = $v['delivery_number'];
                            $addProductsArr[$key]['partial_refund_number'] = $v['partial_refund_number'];
                            $addProductsArr[$key]['weight'] = $v['weight'];
                            $addProductsArr[$key]['univalence'] = $v['univalence'];
                            $addProductsArr[$key]['rate'] = $v['rate'];
                            $addProductsArr[$key]['RMB'] = $v['RMB'];
                        }
                    }
                }
                $productsRe = DB::table('orders_products')->insert($addProductsArr);
                if (!$productsRe) {
                    return false;
                    DB::rollback();
                }
            }
            //更新订单总金额
//            Orders::where(['id' => $orderReObj->id])->update(['order_price' => $orderPrice]);
            //生成付款单
            $collection = self::withCount(['OrdersBillPayments' => function ($query) {
                $query->where('order_type', OrdersBillPayments::ORDERS_CWERP);
                $query->where('type', OrdersBillPayments::BILLS_PAY);
                $query->select(DB::raw('sum(amount) as relacount'));
            },
            ]);
            $paymentsObj = $collection->whereIn('id', $ids)->get();
            if (!$paymentsObj->isEmpty()) {
                $paymentsArr = $paymentsObj->toArray();
                //付款单总和
                $billSum = array_sum(array_column($paymentsArr, 'orders_bill_payments_count'));
            }
            $addPaymentsArr['created_man'] = $user_id;
            $addPaymentsArr['order_id'] = $orderReId;
            $addPaymentsArr['order_type'] = OrdersBillPayments::ORDERS_CWERP;
            $addPaymentsArr['type'] = OrdersBillPayments::BILLS_PAY;
            $addPaymentsArr['amount'] = !$billSum ? 0 : $billSum;
            $addPaymentsArr['currency_code'] = isset($curOrder->OrdersBillPayments[0]) && !empty($curOrder->OrdersBillPayments[0]) ? $curOrder->OrdersBillPayments[0]->currency_code : '';
            $addPaymentsArr['rate'] = isset($curOrder->OrdersBillPayments) && !empty($curOrder->OrdersBillPayments[0]) ? $curOrder->OrdersBillPayments[0]->rate : 0.0000;
            $addPaymentsArr['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE, $param['order_number'], '1');

            $payResults = OrdersBillPayments::insert($addPaymentsArr);
            if (!$payResults) {
                DB::rollback();
                return false;
            }

            //问题记录表
            $orderNumbers = Orders::whereIn('id', $ids)->get(['order_number']);
            if (!$orderNumbers->isEmpty()) {
                $orderNumbers = $orderNumbers->toArray();
            }
            $orderNumberStr = implode(',', array_column($orderNumbers, 'order_number'));
            $desc = $orderNumberStr . '合并成了订单' . $orderReObj->order_number;
            OrdersTroublesRecord::whereIn('order_id', $ids)->where(['trouble_type_id'=>OrdersTroublesRecord::STATUS_MERGE])->update(['trouble_desc' => $desc, 'dispose_status' => OrdersTroublesRecord::STATUS_DISPOSED]);

            //新订单
            $recordNewData['manage_id'] = $user_id;
            $recordNewData['created_man'] = 1;
            $recordNewData['order_id'] = $orderReObj->id;
            $recordNewData['trouble_name'] = '合并订单';
            $recordNewData['trouble_desc'] = '由' . $orderNumberStr . '合并';
            $recordNewData['dispose_status'] = OrdersTroublesRecord::STATUS_DISPOSED;
            $recordNewData['trouble_type_id'] = OrdersTroublesRecord::STATUS_MERGE;
            $recordNewData['created_at'] = date('Y-m-d H:i:s');
            $recordNewData['updated_at'] = date('Y-m-d H:i:s');
            $newtroubleRe = OrdersTroublesRecord::insert($recordNewData);
            if (!$newtroubleRe) {
                DB::rollback();
                return false;
            }
            DB::commit();
            return $orderReObj;
        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }
}
