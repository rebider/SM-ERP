<?php

namespace App\Models;

use function foo\func;
use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;
use Illuminate\Support\Facades\DB;

/**
 * Class OrdersOriginal
 * Notes: 手动创建订单 批量导入订单生成原始订单存储
 * @package App\Models
 * Data: 2019/3/7 16:09
 * Author: zt7785
 */
class OrdersOriginal extends Model
{
    protected $table = 'orders_original';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','platform','order_id','source_shop','bill_payments','order_number',
        'platform_name','source_shop_name','match_status','order_price','payment_method',
        'freight','currency_freight','country','province','city','mobile_phone','phone','addressee_email',
        'warehouse','logistics','addressee_name','addressee1','addressee2','mark','match_fail_reason','order_time',
        'payment_time','created_at','updated_at', 'grab_time', 'user_id', 'order_source', 'currency','order_source_id'];

    /**
     * @var 原始订单来源订单同步
     */
    const ORDERS_ORIGINAL_FROM_API = 1;
    /**
     * @var 原始订单来源手动
     */
    const ORDERS_ORIGINAL_FROM_HAND = 2;


    /**
     * @var 未匹配
     */
    const MAPPING_STATUS_UNFINISH = 1;
    /**
     * @var 已匹配
     */
    const MAPPING_STATUS_FINISHED = 2;
    /**
     * @var 匹配失败
     */
    const MAPPING_STATUS_FAIL = 3;

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
     * @return $this
     * Note: 平台表
     * Data: 2019/3/7 11:34
     * Author: zt7785
     */
    public function Platforms()
    {
        return $this->belongsTo(Platforms::class, 'platforms', 'id')->select(['id', 'created_man', 'name_CN','name_EN' ]);
    }


    /**
     * @return $this
     * Note: 店铺设置表
     * Data: 2019/3/7 11:44
     * Author: zt7785
     */
    public function Shops()
    {
        return $this->belongsTo(SettingShops::class, 'source_shop', 'id')->select(['id', 'created_man', 'plat_id', 'shop_name','shop_url','status']);
    }

    public function originalOrderProducts()
    {
        return $this->hasMany(OrdersOriginalProducts::class, 'original_order_id', 'id');
    }

    public function PayBill()
    {
        RETURN $this->hasMany(OrdersBillPayments::class, 'id', 'bill_payments');
    }

    /**
     * Note: 原始订单列表检索
     * Date：2019/04/22
     * Author：zt12779
     * @param $param
     * @param array $permissionParam
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public static function getOrdersDatas($param, $permissionParam = [], $offset = 1, $limit = 0)
    {
        $collection = self::select('*');
        //用户id
        if ($permissionParam) {
            $collection->where('user_id', $permissionParam ['user_id']);
            //店铺权限
            if (isset($permissionParam ['source_shop'])) {
                $collection->whereIn('source_shop', $permissionParam ['source_shop']);
            }
        }

        $param['order_number'] && $collection->where('order_number', $param['order_number']);
        $param['match_status'] && $collection->where('match_status', $param['match_status']);
        $param['order_source'] && $collection->where('order_source', $param['order_source']);
        $param['platform_name'] && $collection->where('platform', $param['platform_name']);
        $param['source_shop'] && $collection->where('source_shop', $param['source_shop']);
        $param['platform_order'] && $collection->where('platform_order', $param['platform_order']);

        //如果有开始时间无结束时间 默认当前时间
        if ($param['start_date'] && empty($param['end_date'])) {
            $param['end_date'] = date('Y-m-d H:i:s');
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $collection->whereBetween('order_time', [$param['start_date'], $param['end_date']]);
        }

        $result['count'] = $collection->count();
        $result['data'] = $collection->orderBy('created_at', 'desc')
            ->skip(($offset - 1) * $limit)
            ->take($limit)
            ->get()->toArray();
        return $result;
    }

    public static function getOrderDetail($orderNumber)
    {
        $collection = self::with('originalOrderProducts')
            ->with("Shops")
            ->with('PayBill')
            ->where('orders_original.order_number', $orderNumber)
            ->join('platforms', 'platforms.id', '=', 'orders_original.platform')
            ->select('orders_original.*', 'platforms.name_CN as platform_name')
            ->first();
        return $collection;
    }

    /**
     * @param $option
     * @param $value
     * @return array
     * Note: 获取原始订单信息
     * Data: 2019/5/22 17:07
     * Author: zt7785
     */
    public static function getOrgOrderInfoByOpt($option,$value)
    {
        $result = self::where($option,$value)->first();
        if (empty($result)) {
            return [];
        }
        return $result ->toArray() ;
    }


    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * Note: 原始订单匹配任务
     * Data: 2019/6/29 9:15
     * Author: zt7785
     */
    public static function originalOrdersMappingLogic()
    {
        //匹配失败的API 原始订单重新匹配
        $collction = self::with('originalOrderProducts','PayBill')
            ->where(['match_status'=>self::MAPPING_STATUS_FAIL,'order_source'=>self::ORDERS_ORIGINAL_FROM_API])
            ->select([
            'id','created_man','platform','order_id','source_shop','bill_payments','order_number',
            'platform_name','source_shop_name','order_price','payment_method',
            'freight','currency_freight','country','province','city','mobile_phone','phone','addressee_email',
            'warehouse','logistics','addressee_name','addressee1','addressee2','mark','order_time',
            'payment_time','created_at','updated_at', 'grab_time', 'user_id', 'order_source', 'currency','order_source_id','platform_order','rate','zip_code','country_id','warehouse_id','logistics_id']);
        $orig_order_infos = $collction->get();
        if ($orig_order_infos->isEmpty()) {
            echo "无匹配失败原始订单信息". "\r\n";
            return false;
        }
        $orig_order_infos = $orig_order_infos->toArray();
        $current_time = date('Y-m-d H:i:s');

        //V1 订单下商品查询
        try {
            foreach ($orig_order_infos as $key => $orig_order_info) {
                DB::beginTransaction();
                $match_status = false;
                $erpOrderInfo  = [] ;
                if (empty($orig_order_info ['original_order_products']) || empty($orig_order_info ['pay_bill'])) {
                    DB::rollback();
                    continue;
                }
                //订单商品全部有映射关系才算匹配成功
                //todo
                //1个平台sku信息 映射多个本地商品 怎么解决
                $skus = array_column($orig_order_info ['original_order_products'], 'sku');
                $mappingInfo = GoodsMapping::getMappingInfoByUseridSku($orig_order_info['user_id'], $skus, $orig_order_info ['platform']);
                $field = 'seller_sku';
                if ($orig_order_info ['platform'] == Platforms::RAKUTEN) {
                    $field = 'itemURL';
                }
                $mappingInfoSkus = array_column($mappingInfo, $field);
                if (empty($mappingInfo)) {
                    $match_status = false;
                    DB::rollback();
                    continue;
                }
                if (!empty($mappingInfo) && (count($skus) == count($mappingInfo))) {
                    $match_status = true;
                }

                //V2 写平台订单数据
                $erpOrderInfo ['created_man']           = $orig_order_info ['created_man'];
                $erpOrderInfo ['user_id']               = $orig_order_info ['user_id'];
                $erpOrderInfo ['platforms_id']          = $orig_order_info ['platform'];
                $erpOrderInfo ['source_shop']           = $orig_order_info ['source_shop'];
                $erpOrderInfo ['order_number']          = $orig_order_info ['order_number'];
                $erpOrderInfo ['plat_order_number']     = $orig_order_info ['platform_order'];
                $erpOrderInfo ['type']                  = Orders::ORDERS_GETINFO_API;
                $erpOrderInfo ['platform_name']         = $orig_order_info ['platform_name'];
                //店铺名称
                $erpOrderInfo ['source_shop_name']      = $orig_order_info ['source_shop_name'];
                //默认状态
                $erpOrderInfo ['picking_status']        = Orders::ORDER_PICKING_STATUS_UNMATCH;
                $erpOrderInfo ['deliver_status']        = Orders::ORDER_DELIVER_STATUS_UNFILLED;
                $erpOrderInfo ['intercept_status']      = Orders::ORDER_INTERCEPT_STATUS_INITIAL;
                $erpOrderInfo ['sales_status']          = Orders::ORDER_SALES_STATUS_INITIAL;
                $erpOrderInfo ['status']                = Orders::ORDER_STATUS_UNFINISH;
                $erpOrderInfo ['order_price']           = $orig_order_info ['order_price'];
                $erpOrderInfo ['currency_code']         = $erpOrderInfo ['currency_freight'] = $orig_order_info ['currency'];
                $erpOrderInfo ['rate']                  = $orig_order_info ['rate'];
                $erpOrderInfo ['payment_method']        = $orig_order_info ['payment_method'];
                $erpOrderInfo ['freight']               = '0.00';
                $erpOrderInfo ['postal_code']           = $orig_order_info ['zip_code'];
                $erpOrderInfo ['country_id']            = $orig_order_info ['country_id'];
                $erpOrderInfo ['country']               = $orig_order_info ['country'];
                $erpOrderInfo ['province']              = $orig_order_info ['province'];
                $erpOrderInfo ['city']                  = $orig_order_info ['city'];
                $erpOrderInfo ['mobile_phone']          = $orig_order_info ['mobile_phone'];
                $erpOrderInfo ['phone']                 = $orig_order_info ['phone'];
                $erpOrderInfo ['addressee_name']        = $orig_order_info ['addressee_name'];
                $erpOrderInfo ['addressee_email']       = $orig_order_info ['addressee_email'];
                $erpOrderInfo ['addressee']             = $orig_order_info ['addressee1'];
                $erpOrderInfo ['addressee1']            = $orig_order_info ['addressee2'];
                $erpOrderInfo ['addressee2']            = '';
                //仓库
                $erpOrderInfo ['warehouse_id']          = $orig_order_info ['warehouse_id'];
                $erpOrderInfo ['warehouse']             = $orig_order_info ['warehouse'];
                $erpOrderInfo ['logistics']             = $orig_order_info ['logistics'];
                $erpOrderInfo ['logistics_id']          = $orig_order_info ['logistics_id'];
                $erpOrderInfo ['warehouse_choose_status']          = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;
                $erpOrderInfo ['logistics_choose_status']          = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;

                $erpOrderInfo ['order_time']            = $orig_order_info ['order_time'] ;
                $erpOrderInfo ['payment_time']          = $orig_order_info ['payment_time'] ;
                $erpOrderInfo ['created_at'] = $erpOrderInfo ['updated_at'] = $current_time;
                //API订单 全部跑规则吧
                // warehouse_choose_status logistics_choose_status
                //订单id
                $erpOrderId = Orders::insertGetId($erpOrderInfo);
                //V3 付款单 复制
                foreach ($orig_order_info ['pay_bill'] as $billsData) {
                    unset($billsData ['id']);
                    $billsData ['order_id']     = $erpOrderId;
                    $billsData ['order_type']   = OrdersBillPayments::ORDERS_CWERP;
                    $billsData ['created_at']   = $billsData ['updated_at'] = $current_time;
                    if ($billsData ['type'] == OrdersBillPayments::BILLS_PAY) {
                        //目前一个订单只有一个付款单
                        $billsData ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$erpOrderInfo ['order_number'],'1');
                    } else {
                        $billsData ['bill_code'] = '';
                    }
                    OrdersBillPayments::postData(0,$billsData);
                }
                //V4 更新原始订单数据
                $orig_amazon_order_update_data ['order_id']  = $erpOrderId;
                $orig_amazon_order_update_data ['match_status']  = OrdersAmazon::AMAZON_MAPPING_STATUS_FINISHED;//匹配成功
                $orig_amazon_order_update_data ['updated_at']   = $current_time;
                OrdersOriginal::where('id',$orig_order_info ['id'])->update($orig_amazon_order_update_data);
                //V5 原始数据表更新
                if ($orig_order_info ['platform'] == Platforms::RAKUTEN) {
                    //原始乐天订单关联
                    $orig_rakuten_update_data ['cw_order_id']       = $erpOrderId;
                    $orig_rakuten_update_data ['is_system']         = OrdersRakuten::IS_SYSTEM_ORDER;//是系统订单
                    $orig_rakuten_update_data ['match_status']      = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FINISHED;//匹配成功
                    $orig_rakuten_update_data ['updated_at']        =  $current_time;
                    OrdersRakuten::where('id',$orig_order_info ['order_source_id'])->update($orig_rakuten_update_data);
                } else if ($orig_order_info ['platform'] == Platforms::AMAZON) {
                    //原始乐天订单关联
                    $orig_amazon_update_data ['cw_order_id']       = $erpOrderId;
                    $orig_amazon_update_data ['is_system']         = OrdersAmazon::IS_SYSTEM_ORDER;//是系统订单
                    $orig_amazon_update_data ['MatchStatus']      = OrdersAmazon::AMAZON_MAPPING_STATUS_FINISHED;//匹配成功
                    $orig_amazon_update_data ['updated_at']        =  $current_time;
                    OrdersAmazon::where('id',$orig_order_info ['order_source_id'])->update($orig_amazon_update_data);
                }
                //日志
                $orderLogsData ['created_man'] = $orig_order_info ['user_id'];
                $orderLogsData ['order_id'] = $erpOrderId;
                $orderLogsData ['behavior_types'] = OrdersLogs::LOGS_ORDERS_CREATED;
                $orderLogsData ['behavior_desc'] = OrdersLogs::ORDERS_LOGS_DESC[$orderLogsData['behavior_types']];
                $orderLogsData ['behavior_type_desc'] = OrdersLogs::ORDERS_LOGS_TYPE_DESC[$orderLogsData ['behavior_types']];
                $orderLogsData ['updated_at'] = $orderLogsData ['created_at'] = $current_time;
                OrdersLogs::postDatas(0,$orderLogsData);
                //V6  写商品数据
                $mapping_exception = false;
                foreach ($orig_order_info ['original_order_products'] as $k => $original_order_products) {
                    $order_product_info[$k]['created_man']          = $orig_order_info['user_id'];
                    $order_product_info[$k]['order_id']             = $erpOrderId;
                    $mappingKey =  array_search($original_order_products['sku'], $mappingInfoSkus);
                    if (is_bool($mappingKey)) {
                        $mapping_exception = true;
                        break;
                    }
                    $goods_id = GoodsMappingGoods::getGoodsIdByMappingid($mappingInfo[$mappingKey]['id']);
                    $order_product_info[$k]['goods_id']             = $goods_id['goods_id'];
                    $order_product_info[$k]['order_type']           = OrdersProducts::ORDERS_CWERP;
                    $order_product_info[$k]['product_name']         = $goods_id ['goods'] ? $goods_id ['goods'] ['goods_name'] : $original_order_products ['goods_name'];
                    $order_product_info[$k]['sku']                  = $original_order_products ['sku'];
                    $order_product_info[$k]['currency']             = $orig_order_info ['currency'];
                    $order_product_info[$k]['buy_number']           = $original_order_products['quantity'];
                    $order_product_info[$k]['weight']               = $goods_id['goods']? $goods_id['goods'] ['goods_weight']:'0.00';
                    $order_product_info[$k]['univalence']           = $original_order_products ['price'];
                    $order_product_info[$k]['rate']                 = $original_order_products ['rate'];
                    $order_product_info[$k]['is_deleted']           = OrdersProducts::ORDERS_PRODUCT_UNDELETED;
                    $order_product_info[$k]['RMB']                  = $original_order_products ['RMB'];
                    $order_product_info[$k]['AmazonOrderItemCode']   = $original_order_products['OrderItemId'] ??'';
                    $order_product_info[$k]['created_at']               = $order_product_info[$k]['updated_at'] = $current_time;
                    $order_product_info[$k]['already_stocked_number']   = $order_product_info[$k]['cargo_distribution_number'] = $order_product_info[$k]['delivery_number'] = $order_product_info[$k]['partial_refund_number'] = 0;
                    //V7 更新原始商品 商品id
                    OrdersOriginalProducts::where('id',$original_order_products ['id'])->update([
                        'goods_id'=>$goods_id['goods_id'],
                        'goods_img'=>$goods_id ['goods'] ? $goods_id ['goods'] ['goods_pictures'] : $original_order_products ['goods_img'],
                        'updated_at'=>$current_time,
                    ]);
                }
                if (!empty($order_product_info)) {
                    OrdersProducts::insert($order_product_info);
                }
                if ($mapping_exception) {
                    DB::rollback();
                    continue;
                }
                //V8 匹配成功日志
                $succ_data = [
                    'start_time'                => $current_time,
                    'mapping_info'             => [
                        '原始订单 ID: ' . $orig_order_info ['id'],
                        '平台订单 ID: ' . $erpOrderId,
                    ]
                ];
                LogHelper::setSuccessLog($succ_data,'orders');
                DB::commit();
            }
        } catch (\Exception $exception) {
            DB::rollback();
            $exception_data = [
                'start_time'                => $current_time,
                'msg'                       => '失败信息：' . $exception->getMessage(),
                'line'                      => '失败行数：' . $exception->getLine(),
                'file'                      => '失败文件：' . $exception->getFile(),
            ];

            LogHelper::setExceptionLog($exception_data,'orders');
            $exceptionDing ['type'] = 'task';
            $dingPushData ['task'] = '原始订单匹配任务';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = 'originalOrdersMappingLogic';
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($exception_data,null,$exceptionDing ['type']);
        }

    }
}
