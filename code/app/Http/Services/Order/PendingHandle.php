<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/8
     * Time: 14:29
     */

    namespace App\Http\Services\Order;

    use App\Auth\Common\CurrentUser;
    use App\Common\Common;
    use App\Exceptions\DataNotFoundException;
    use App\Models\Goods;
    use App\Models\Orders;
    use App\Models\OrdersInvoices;
    use App\Models\OrdersInvoicesProducts;
    use App\Models\OrdersLogs;
    use App\Models\OrdersProducts;
    use App\Models\OrdersTroublesRecord;
    use App\Models\SettingCountry;
    use App\Models\SettingLogistics;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseGoods;
    use App\Models\WarehouseSecretkey;
    use App\Models\WarehouseTypeGoods;
    use Illuminate\Support\Facades\DB;

    class PendingHandle
    {
        protected $_err = [];
        protected $input = null;
        protected static $COMMON;

        public function getErrs()
        {
            return $this->_err;
        }

        public function setInput($input)
        {
            return $this->input = $input;
        }

        public function __construct()
        {
            self::$COMMON = new Common();
        }

        /**
         * @return string
         * Note: 生成配货单号
         * Date: 2019/3/27 15:22
         * Author: zt8067
         */
        protected function generateNumber($order_number = '', $sort = '')
        {
            return 'SM' . ltrim($order_number, 'S') . '-' . $sort;
        }

        /**
         * @return array
         * Note: 待配货单数据
         * Date: 2019/3/20 11:00
         * Desc: a)指定仓库库存不足；b)跑匹配仓库规则，没有可用仓库； c)跑匹配物流规则，没有可用物流；
         * Author: zt8067
         */
        public static function getSummaryByPage($params, $user_id,$permissionParam = [])
        {
            $data = $params->all();
            $limit = $params->get('limit', 20);
            DB::enableQueryLog();
            $collection = Orders::query();
            //店铺权限
            if(isset($permissionParam ['source_shop'])) {
                $collection->whereIn('orders.source_shop',$permissionParam['source_shop']);
            }
            //问题类型
            if (isset($data ['problem']) && !empty($data ['problem'])) {
                $param['problem'] = $data ['problem'];
            } else {
                $param['problem'] = (isset($data['data']['problem']) && !empty($data['data']['problem']))
                    ? $data['data']['problem'] : '';
            }

            $param['problem'] && $collection->where('orders.problem',$param['problem']);
            //可配货比
            $param['radio'] = (isset($data['data']['radio']) && !empty($data['data']['radio']))
                ? $data['data']['radio'] : '';
            if ($param['radio']) {
                switch ($param['radio']) {
                    case 1:
                        $collection->where('orders.radio', 0);
                        break;
                    case 2:
                        $collection->where('orders.radio', '>', 0);
                        $collection->where('orders.radio', '<', 100);
                        break;
                    case 3:
                        $collection->where('orders.radio', '>=', 100);
                        break;
                }
            }
            //订单单号
            $param['order_number'] = (isset($data['data']['order_number']) && !empty($data['data']['order_number']))
                ? $data['data']['order_number'] : '';
            $param['order_number'] && $collection->where('order_number', $param['order_number']);
            //电商单号
            $param['plat_order_number'] = (isset($data['data']['plat_order_number']) && !empty($data['data']['plat_order_number']))
                ? $data['data']['plat_order_number'] : '';
            $param['plat_order_number'] && $collection->where('plat_order_number', $param['plat_order_number']);
            //下单order_time,创建created_at,付款payment_time,发货logistics_time,时间
            $param['time_type'] = (isset($data['data']['time_type']) && !empty($data['data']['time_type']))
                ? $data['data']['time_type'] : '';
            $param['start_time'] = (isset($data['data']['start_time']) && !empty($data['data']['start_time']))
                ? $data['data']['start_time'] : '';
            $param['end_time'] = (isset($data['data']['end_time']) && !empty($data['data']['end_time']))
                ? $data['data']['end_time'] : '';
            $param['time_type'] && $param['start_time'] && $param['end_time'] && $collection->where($param['time_type'], '>=', $param['start_time'])->where($param['time_type'], '<=', $param['end_time']);
            //                        DB::enableQueryLog();
            $pagingData = $collection->whereDoesntHave('OrdersTroublesRecord', function($query){
                $query->where('dispose_status',1);
             })->where('user_id', $user_id)
                ->where('deliver_status', '<>', Orders::ORDER_DELIVER_STATUS_FILLED)//订单发货状态发货成功
                ->where('status', Orders::ORDER_STATUS_UNFINISH)//未完结
                ->where('picking_status', '<>', Orders::ORDER_PICKING_STATUS_MATCHED_SUCC)//ORDER_PICKING_STATUS_MATCHED_SUCC
                ->where('problem', '<>', Orders::NO_PROBLEM)//没有问题
                ->orderBy('payment_time')
                ->paginate($limit)
                ->toArray();
           //  dd(DB::getQueryLog());
            return $pagingData;
        }

        /**
         * @return array
         * Note: 获取待配货单信息
         * Date: 2019/3/26 10:00
         * params : order_id   warehouse_id  total_weight
         * Author: zt8067
         */
        public function getOrdersDesc($params = [], $user_id = '')
        {
            $results = ['code' => -1, 'msg' => '', 'data' => ''];
            do {
                $collection = Orders::query();
                $collection->with(['Platforms', 'Shops', 'OrdersProducts.Goods', 'Warehouse', 'Logistics'])->where(['id' => $params['order_id']]);
                $user_id && $collection->where(['user_id' => $user_id]);
                $Orders = $collection->first();
                if (empty($Orders)) {
                    $results['msg'] = '没有订单信息';
                    break;
                }
                $OrdersM = $Orders->toArray();
                $warehouse_id = empty($params['warehouse_id']) ? $OrdersM['warehouse_id'] : $params['warehouse_id'];
                $orders_products = &$OrdersM['orders_products'];
                $warehouseM = SettingWarehouse::where(['user_id' => $user_id, 'id' => $warehouse_id])->first(['type']);
                if (!empty($warehouseM)) {
                    $warehouse = $warehouseM->toArray();
                }
                if (!empty($orders_products)) {
                    $total_weight = 0;
                    $results['code'] = 1;
                    foreach ($orders_products as &$item) {
                        unset($item['cargo_distribution_number']);
                        //TODO WarehouseTypeGoods表 user_id
                        if (!empty($warehouse)) {
                            $WarehouseGoods = WarehouseTypeGoods::where(['goods_id' => $item['goods_id'], 'setting_warehouse_id' => $warehouse_id])->first();
                            if (empty($WarehouseGoods)) {
                                $results['code'] = -1;
                                $results['msg'] = '未找到仓库商品';
                                $results['data'] = $OrdersM;
                                return $results;
                            }
                            $WarehouseGoods = $WarehouseGoods->toArray();
                            $Goods = Goods::where('id',$item['goods_id'])->first();
                            $item['cargo_distribution_number'] = $WarehouseGoods['available_in_stock'];
                            $dispensable_number = $item['buy_number'] - $item['already_stocked_number'];
                            if ($WarehouseGoods['available_in_stock'] < $dispensable_number) {
                                $total_weight += self::$COMMON->PriceCalculate($WarehouseGoods['available_in_stock'], '*', $Goods['goods_weight'], 3);
                            } else {
                                $total_weight += self::$COMMON->PriceCalculate($dispensable_number, '*', $Goods['goods_weight'], 3);
                            }
                        }
                    }
                    $results['data'] = $OrdersM;
                    if (isset($params['total_weight']) && $params['total_weight'] > 0) {
                        $tmp_weight = $params['total_weight'];
                    } else {
                        $tmp_weight = $total_weight;
                    }
                    $results['data']['total_weight'] = $tmp_weight;
                }
            } while (0);
            return $results;
        }

        /**
         * @return array
         * Note: 获取物流方式
         * Date: 2019/3/26 11:00
         * Author: zt8067
         */
        public function sendLogistics($params)
        {
            $results = ['code' => -1];
            $data['warehouseCode'] = $params;
            $results = self::$COMMON->sendWarehouse('getShippingMethod', $data);  //未调用
            $results && $results['code'] = 1;
            return $results;
        }

        /**
         * @return array
         * Note: 获取费用试算
         * DESC: $params ①$params['orders']  ②国家
         * Date: 2019/3/26 20:40
         * Author: zt8067
         */
        public function freightTrial($OrdersDesc, $params, $user_id)
        {
            $results = ['code' => -1, 'msg' => '', 'data' => ''];
            do {
                $freight = false;
                $status = 0;
                //获取用户开通的物流方式
                $Warehouse = SettingWarehouse::with(['Logistics' => function ($query) use ($user_id) {
                    $query->where('setting_logistics_warehouses.user_id', $user_id);
                },
                ])->where('user_id', $user_id)->where('id', $params['warehouse_id'])->first()->toArray();

                //客户仓库密钥信息
                $warehouseConfigInfo = WarehouseSecretkey::where([
                    'status'=>WarehouseSecretkey::STATUS_ON,
                    'user_id'=>$user_id,
                ])->first(['user_id','appToken','appKey']);
                if (empty($warehouseConfigInfo)) {
                    $results ['msg'] = '仓库密钥信息异常';
                    break;
                }
                $warehouseConfigInfo = $warehouseConfigInfo->toArray();
                $account ['appToken'] = $warehouseConfigInfo ['appToken'];
                $account ['appKey'] = $warehouseConfigInfo ['appKey'];
                if (!empty($Warehouse['logistics'])) {
                    foreach ($Warehouse['logistics'] as $k => $item) {
                       if (isset($params['country']) && !empty($params['country'])){
                           $country_id = $params['country'];
                       }else{
                           $country_id = $OrdersDesc['country_id'];
                       }
                        $countryM = SettingCountry::where('id',$country_id)->first();
                        if(!$countryM){
                            $results['msg'] = '国家维护字段未找到';
                            break 2;
                        }
                        $country = $countryM->country_code;

                        $arr['warehouse_code'] = $Warehouse['warehouse_code'];//仓库
                        $arr['country_code'] =  $country;//国家
                        $arr['postcode'] = (isset($params['postal_code']) && !empty($params['postal_code'])) ? $params['postal_code'] : $OrdersDesc['postal_code'];//邮编
                        $arr['shipping_method'] = $item['logistic_code'];//物流方式
                        $arr['weight'] = $OrdersDesc['total_weight'] ?? $params['total_weight'];//订单重量
                        if ($Warehouse['type'] == SettingWarehouse::SM_TYPE) {
                            if (empty($arr['warehouse_code'])) {
                                $results['msg'] = '仓库code必须的';
                                break 2;
                            }
                            if (empty($arr['country_code'])) {
                                $results['msg'] = '国家简码必须的';
                                break 2;
                            }
                            if (empty($arr['shipping_method'])) {
                                $results['msg'] = '物流运输方式必须的';
                                break 2;
                            }
                            if ($arr['weight'] <= 0) {
                                $results['msg'] = '重量参数不能为零';
                                break 2;
                            }
                            $response = self::$COMMON->sendWarehouse('getCalculateFee', $arr,$account);//已调整
                            if(empty($response)){
                                $results['msg'] = '接口异常，或网络故障！';
                                break 2;
                            }
                            $status = SettingWarehouse::SM_TYPE;
                            $freight[$k]['ask'] = $response['ask'];
                            $freight[$k]['error'] = '';
                            $response['ask'] == 'Success' && $freight[$k]['totalFee'] = $response['data']['totalFee'];
                            $response['ask'] == 'Failure' && $freight[$k]['error'] = $response['Error']['errMessage'];
                        } else {
                            $status = SettingWarehouse::CUSTOM_TYPE;
                        }
                        $freight[$k]['logistic_id'] = $item['id'];
                        $freight[$k]['logistic_code'] = $item['logistic_code'];
                        $freight[$k]['logistic_name'] = $item['logistic_name'];
                    }
                    $results['msg'] = 'Success';
                    $results['code'] = 1;
                    $results['data'] = $freight;
                    $results['status'] = $status;
                } else {
                    $results['msg'] = '仓库未绑定物流方式';
                    break;
                }
            } while (0);
            return $results;
        }

        /**
         * @return boolean
         * Note: 处理配货单
         * Date: 2019/3/27 15:18
         * Author: zt8067
         */
        public function distributionOrderProcess($params = [], $user_id = '',$method = true)
        {
            do {
                $results = ['code' => -1, 'msg' => '配货单添加失败'];
                $order_id = $params['order_id'];
                $goods = $params['goods'];
                if (!empty($goods)) {
                    DB::beginTransaction();
                    try {
                        //客户仓库密钥信息
                        $warehouseConfigInfo = WarehouseSecretkey::where([
                            'status'=>WarehouseSecretkey::STATUS_ON,
                            'user_id'=>$user_id,
                        ])->first(['user_id','appToken','appKey']);
                        if (empty($warehouseConfigInfo)) {
                            $results ['msg'] = '仓库密钥信息异常';
                            break;
                        }
                        $warehouseConfigInfo = $warehouseConfigInfo->toArray();
                        $account ['appToken'] = $warehouseConfigInfo ['appToken'];
                        $account ['appKey'] = $warehouseConfigInfo ['appKey'];

                        $OrdersModel = Orders::with('Platforms')->where(['user_id' => $user_id, 'id' => $order_id]);
                        if(isset($params ['source_shop'])) {
                            $OrdersModel->whereIn('source_shop',$params ['source_shop']);
                        }
                        $OrdersModel = $OrdersModel->first();
                        if (empty($OrdersModel)) {
                            $results['msg'] = '未匹配到订单信息';
                            break;
                        }
                        $Orders = $OrdersModel->toArray();
                        $OrdersCount = OrdersInvoices::where('user_id', $user_id)->where('order_id', $order_id)->count();
                        $OrdersCount || $OrdersCount = 0;
                        //校验仓库是否可用
                        $WarehouseM = SettingWarehouse::whereHas('Logistics' , function ($query) use ($user_id,$params) {
                            $query->where(['setting_logistics_warehouses.user_id'=> $user_id,'setting_logistics.id'=>$params['logistic_id'],'setting_logistics.disable'=>SettingWarehouse::ON]);
                        })->where('user_id', $user_id)->where(['id'=>$params['warehouse_id'],'disable'=>SettingWarehouse::ON])->first();
                        if (empty($WarehouseM)) {
                            $results['msg'] = ' 未绑定仓库物流或未启用';
                            break;
                        }
                        $Warehouse = $WarehouseM->toArray();
                        $total_already_stocked_number = 0;
                        $total_dispensable_number = 0;
                        $total_stock = 0;
                        $total_weight = 0;
                        $count = 0;
                        foreach ($goods as $k => $item) {
                            //再次校验商品信息
                            $_checkGoodsModel = OrdersProducts::with('Goods')->where('user_id', $user_id)->where('id', $item['id'])->lockForUpdate()->first();
                            if (empty($_checkGoodsModel)) {
                                $this->_err[] = $item['sku'] . ' 商品数据异常';
                                continue;
                            }
                            $checkGoods = $_checkGoodsModel->toArray();
                            //再次校验库存
                            $WarehouseGoods = WarehouseTypeGoods::where(['setting_warehouse_id' => $params['warehouse_id'],'goods_id'=> $checkGoods['goods_id']])->lockForUpdate()->first();
                            if (empty($WarehouseGoods)) {
                                $this->_err[] = $item['sku'] . ' 产品未匹配到库存';
                                continue;
                            }
                            if ($WarehouseGoods['available_in_stock'] < $item['dispensable_number']) {
                                $results['msg'] = $item['sku'] . ' 产品配货数小于可配货库存';
                                continue;
                            }
                            $nowStock = $WarehouseGoods['available_in_stock'];
                            $total_stock += $nowStock;
                            //判断部分匹配还是完全匹配
                            if((($item['already_stocked_number']+$item['dispensable_number']) == $checkGoods['buy_number'])){
                                $count++;
                            }
                            //剔除没配货，库存不足的商品
                            if (($nowStock <= 0 && $item['dispensable_number'] <= 0) || $item['dispensable_number'] <= 0 || ($item['already_stocked_number'] == $item['buy_number'])) {
                                unset($goods[$k]);//移除已配货完成的以及【库存|配货】为零的商品等待库存补充配货
                                continue;
                            }
                            $total_already_stocked_number += $item['already_stocked_number'];
                            $total_dispensable_number += $item['dispensable_number'];
                            $total_weight += self::$COMMON->PriceCalculate($item['dispensable_number'], '*', $checkGoods['goods']['goods_weight'], '2');
                        }
                        $FreightParams['country_id'] = $OrdersModel['country_id'];
                        $FreightParams['postal_code'] = $OrdersModel['postal_code'];
                        $FreightParams['total_weight'] = $total_weight;
                        $WarehouseParams['warehouse_id'] = $Warehouse['id'];
                        $results_freightTrial = $this->freightTrial($FreightParams, $WarehouseParams, $user_id);
                        if ($results_freightTrial['code']!==1) {
                            $results['msg'] = $results_freightTrial['msg'];
                            break;
                        }
                        $logistics_id = null;
                        $logistic_code = null;
                        $logistic_name = null;
                        $Freight = 0;
                        $taotlaFreight = 0;
                        $taotla_value = OrdersInvoices::where(['order_id' => $order_id, 'intercept_status' => OrdersInvoices::UNINTERCEPT, 'invoices_status' => OrdersInvoices::ENABLE_INVOICES_STATUS])->sum('taotla_value');
                        if ($Warehouse['type'] == SettingWarehouse::SM_TYPE) {
                           // var_dump($results_freightTrial)
                            //array:4 [
                            //  "code" => 1
                            //  "msg" => "Success"
                            //  "data" => array:2 [
                            //    0 => array:5 [
                            //      "ask" => "Failure"
                            //      "error" => "国家简码[JPo]未找到"
                            //      "logistic_id" => 43
                            //      "logistic_code" => "JP-EMS"
                            //      "logistic_name" => "日本标准邮政快件"
                            //    ]
                            //    1 => array:5 [
                            //      "ask" => "Failure"
                            //      "error" => "未找到价格方案"
                            //      "logistic_id" => 2
                            //      "logistic_code" => "AK-JKLAS"
                            //      "logistic_name" => "美国头程空派"
                            //    ]
                            //  ]
                            //  "status" => 1
                            //]
                            $check_warehouse = false;
                            //取出物流费用
                            foreach ($results_freightTrial['data'] as $item_freightTrial) {
                                if ($item_freightTrial['logistic_id'] == $params['logistic_id']) {
                                    $check_warehouse = true;
                                    $Freight = empty($item_freightTrial['totalFee']) ? 0 : $item_freightTrial['totalFee'];
                                    $taotlaFreight = ($item_freightTrial['totalFee'] + $taotla_value);
                                    $logistics_id = $item_freightTrial['logistic_id'];
                                    $logistic_code = $item_freightTrial['logistic_code'];
                                    $logistic_name = $item_freightTrial['logistic_name'];
                                    break;
                                }
                            }
                            if (!$check_warehouse) {
                                $results['msg'] = '选择的物流运输方式不可用，请重新选择';
                                break;
                            }
                        } else {
                            foreach ($results_freightTrial['data'] as $item_freightTrial) {
                                if ($item_freightTrial['logistic_id'] == $params['logistic_id']) {
                                    $Freight = 0;
                                    $taotlaFreight = $taotla_value;
                                    $logistics_id = $item_freightTrial['logistic_id'];
                                    $logistic_code = $item_freightTrial['logistic_code'];
                                    $logistic_name = $item_freightTrial['logistic_name'];
                                    break;
                                }
                            }
                        }
                        if ($total_stock <= 0) {
                            $results['msg'] = '商品库存为空，请补充商品';
                            break;
                        }
                        if ($total_dispensable_number <= 0) {
                            $results['msg'] = '配货数量为0，请配货';
                            break;
                        }
                       $ProductsCount = OrdersProducts::where(['order_id'=>$order_id,'is_deleted' => OrdersProducts::ORDERS_PRODUCT_UNDELETED])->count();
                        if ($count == $ProductsCount) {
                            $picking_status = Orders::ORDER_PICKING_STATUS_MATCHED_SUCC;//整体生成配货单 已匹配
                        } else {
                            $picking_status = Orders::ORDER_PICKING_STATUS_MATCHED_PART;//拆分配货单 已部分匹配成功
                        }
                        $generateNumber = $this->generateNumber($Orders['order_number'], $OrdersCount + 1, true);//配货单号

                        $country = SettingCountry::find($OrdersModel['country_id']);
                        //组装仓库发送数据
                        $orderData['reference_no'] = $generateNumber;
                        $orderData['platform'] = strtoupper($Orders['platforms']['name_EN']);
                        $orderData['shipping_method'] = $logistic_code;
                        $orderData['warehouse_code'] = $Warehouse['warehouse_code'];
                        $orderData['country_code'] = $country->country_code;
                        $orderData['province'] = $Orders['province'];
                        $orderData['city'] = $Orders['city'];
                        $orderData['address1'] = $Orders['addressee'];
                        $orderData['address2'] = $Orders['addressee1'];
                        $orderData['address3'] = $Orders['addressee2'];
                        $orderData['zipcode'] = $Orders['postal_code'];
                        $orderData['order_desc'] = "新建订单";
                        $orderData['doorplate'] = "";
                        $orderData['name'] = $Orders['addressee_name'];
                        $orderData['phone'] = $Orders['mobile_phone'] ?? $Orders['phone'];
                        $orderData['email'] = $Orders['addressee_email'];
                        $orderData['pay_style'] = 1;
                        $temp_order['verify'] = 1;//是否审核,0不审核，1审核，默认为0，
                        //$temp_order['pay_style'] = 1;//付款类型  1: 已付款 2:货到付款 payment_method
                        //$temp_order['specify_send_day'] = '';//指定发货日 string
                        //$temp_order['specify_send_quantum'] =
                        //发货时间段,如18時~20時为1820,空白为0000，18時-20時为1820，午时中为0812
                        //$temp_order['substitution_amount'] = '';//代引金额，付款方式为“货到付款”时必传
                        $OrdersInvoicesModel = new OrdersInvoices;
                        $OrdersInvoicesModel->created_man = $Orders['created_man'];//操作者
                        $OrdersInvoicesModel->user_id = $Orders['user_id'];
                        $OrdersInvoicesModel->type = $results_freightTrial['status'];//仓库类型
                        $OrdersInvoicesModel->order_id = $Orders['id'];//订单表id
                        $OrdersInvoicesModel->platforms_id = $Orders['platforms_id'];//平台id
                        $OrdersInvoicesModel->source_shop = $Orders['source_shop'];//来源店铺id
                        $OrdersInvoicesModel->logistics_id = $logistics_id;//物流方式id
                        $OrdersInvoicesModel->warehouse_id = $Warehouse['id'];//仓库id
                        $OrdersInvoicesModel->invoices_number = $generateNumber;//配货单号
                        $OrdersInvoicesModel->warehouse_order_number = null;//物流跟踪号
                        $OrdersInvoicesModel->warehouse = $Warehouse['warehouse_name'];//仓库
                        $OrdersInvoicesModel->logistics_way = $logistic_name;//物流方式
                        $OrdersInvoicesModel->taotla_value = $Freight;//运费
                        $OrdersInvoicesModel->currency_code = $Orders['currency_code'];//币种code
                        $OrdersInvoicesModel->rate = $Orders['rate'];//实时汇率
                        $OrdersInvoicesModel->invoices_status = OrdersInvoices::ENABLE_INVOICES_STATUS;//配货单状态 1:未作废 2:已作废
                        $OrdersInvoicesModel->sync_status = OrdersInvoices::UN_SYNC_STATUS;//仓库拿物流跟踪号 再回传平台 同步状态 1:未同步 2:已同步 3:同步失败
                        $OrdersInvoicesModel->sync_number = 0;//同步至平台次数
                        $OrdersInvoicesModel->ware_number = 0;//仓库获取跟踪号次数
                        if ($OrdersInvoicesModel->save()) {
                            $orderData['items'] = [];
                            foreach ($goods as $k => $item) {
                                //TODO 实时汇率获取
                                $OrdersProducts = OrdersProducts::with('Goods')->where(['user_id' => $user_id, 'id' => $item['id']])->first();
                                $OrdersInvoicesProductsModel = new OrdersInvoicesProducts;
                                $OrdersInvoicesProductsModel->created_man = $Orders['created_man'];//操作者
                                $OrdersInvoicesProductsModel->user_id = $user_id;
                                $OrdersInvoicesProductsModel->order_id = $Orders['id'];//订单表id
                                $OrdersInvoicesProductsModel->invoice_id = $OrdersInvoicesModel->id;//配货单的ID
                                $OrdersInvoicesProductsModel->goods_id = $OrdersProducts['goods_id'];//商品的Id
                                $OrdersInvoicesProductsModel->product_name = $OrdersProducts['product_name'];//平台订单商品名称
                                $OrdersInvoicesProductsModel->sku = $OrdersProducts['goods']['sku'];
                                $OrdersInvoicesProductsModel->attribute = '';//属性
                                $OrdersInvoicesProductsModel->buy_number = $OrdersProducts['buy_number'];//购买数量
                                $OrdersInvoicesProductsModel->already_stocked_number = $item['dispensable_number'];//已配货数量
                                $OrdersInvoicesProductsModel->cargo_distribution_number = $item['cargo_distribution_number'];//可配货数量记录
                                $OrdersInvoicesProductsModel->weight = $OrdersProducts['weight'];//重量
                                $OrdersInvoicesProductsModel->univalence = $OrdersProducts['univalence'];//单价
                                $OrdersInvoicesProductsModel->rate = $OrdersProducts['rate'];//实时汇率
                                $OrdersInvoicesProductsModel->currency = $OrdersProducts['currency'];//币种
                                $OrdersInvoicesProductsModel->status = OrdersInvoicesProducts::ON_STATUS;//配货是否完成： 0 未完成 1 已完成
                                $OrdersInvoicesProductsModel->AmazonOrderItemCode = $OrdersProducts['AmazonOrderItemCode'];//亚马逊定义的订单商品识别号
                                if ($OrdersInvoicesProductsModel->save()) {
                                    //更新订单已配货数量
                                    $oldInvoicesProductsModel = OrdersProducts::where('id', $item['id'])->update([
                                        'already_stocked_number' => ($item['dispensable_number'] + $OrdersProducts['already_stocked_number']),
                                    ]);
                                    //更新在售库存
                                    WarehouseTypeGoods::where(['setting_warehouse_id' => $params['warehouse_id'], 'goods_id' => $OrdersProducts['goods_id']])->decrement('available_in_stock', $item['dispensable_number']);
                                    if (empty($oldInvoicesProductsModel)) break;
                                }
                                //组装仓库发送商品数据
                                $orderData['items'][$k]['product_sku'] = $OrdersProducts['goods']['sku'] ?? '';
                                $orderData['items'][$k]['quantity'] = $item['dispensable_number'] ?? 0;
                            }
                        }
                        //创建仓库入库单速贸仓储走。
                        if ($Warehouse['type'] == SettingWarehouse::SM_TYPE) {
                            $Response = $this->sendDistrbutuonData($orderData,$account);
                            if (empty($Response)) {
                                DB::rollBack();
                                $results['msg'] = '入库单添加失败，网络或接口异常';
                                break;
                            }
                            if ($Response['ask'] != 'Success') {
                                DB::rollBack();
                                $results['msg'] = $Response['message'];
                                break;
                            }
                            $OrdersInvoicesModel->where('id', $OrdersInvoicesModel->id)->update(['warehouse_order_number' => $Response['order_code']]);
                        }
                        $updateData = [
                            'invoices_freight'               => $taotlaFreight,
                            'invoices_freight_currency_code' => 'RMB',
                            //                            'warehouse_id'   => $Warehouse['id'],
                            //                            'warehouse'      => $Warehouse['warehouse_code'],
                            //                            'logistics_id'   => $logistics_id,
                            //                            'logistics'      => $logistic_code,
                            'picking_status'                 => $picking_status,
                            'logistics_time'                 => date('Y-m-d H:i:s'),
                        ];
                        $OrdersModel = Orders::where(['user_id' => $user_id, 'id' => $order_id])->update($updateData);
                        $results = ['code' => 1, 'msg' => '配货单添加成功'];
                        //定时任务 默认系统管理员写入日志
                        $creat_id = $this->is_cli() ? 1 : $user_id;
                        if ($updateData ['picking_status'] == Orders::ORDER_PICKING_STATUS_MATCHED_SUCC ) {
                            OrdersLogs::standardOrderLogs($order_id,$creat_id,OrdersLogs::LOGS_ORDERS_PRODUCT_PICKED);
                        } else if ($updateData ['picking_status'] == Orders::ORDER_PICKING_STATUS_MATCHED_PART ) {
                            OrdersLogs::standardOrderLogs($order_id,$creat_id,OrdersLogs::LOGS_ORDERS_PART_PRODUCT_PICKING);
                        }
                        $OrdersModel && DB::commit();
                    } catch (\Exception $e) {
                        $method && Common::mongoLog($e,'手动配货单','手动配货单添加失败',__FUNCTION__);
                        DB::rollBack();
                    } catch (\Error $e) {
                        DB::rollBack();
                        $method && Common::mongoLog($e,'手动配货单','手动配货单添加失败',__FUNCTION__);
                    }
                }
            } while (0);
            return $results;
        }

        /**
         * @return boolean
         * Note: 定时可配货比列
         * Date: 2019/3/27 15:18
         * Author: zt8067
         */
        public static function DistributableRatio()
        {
            //更新待配货单 配货比
            $collection = Orders::query();
            $DataModel = $collection->with('OrdersProducts.Goods')
                ->whereHas('OrdersTroublesRecord', function ($query) {
                    $query->where('dispose_status', OrdersTroublesRecord::STATUS_DISPOSED);
                })
                ->where('deliver_status', '<>', Orders::ORDER_DELIVER_STATUS_FILLED)
                ->where('problem', '<>', Orders::NO_PROBLEM)
                ->where(function ($query) {
                    $query->where('picking_status', Orders::ORDER_PICKING_STATUS_UNMATCH)->orWhere(function ($query) {
                        $query->where('picking_status', Orders::ORDER_STATUS_OBSOLETED);
                    });
                })
                ->orderBy('payment_time')
                ->get();
            if ($DataModel->isNotEmpty()) {
                $data = $DataModel->toArray();
                foreach ($data as $order_item) {
                    if ($order_item['orders_products'] && is_array($order_item['orders_products'])) {
                        $GoodStock = 0;
                        $GoodAlready_stocked_number = 0;
                        $radio = 0;
                        foreach ($order_item['orders_products'] as $orders_products_item) {
                            //取出仓库可售的商品和
                            $GoodStock += WarehouseTypeGoods::where('goods_id', $orders_products_item['goods_id'])->sum('available_in_stock');
                            $GoodAlready_stocked_number += $orders_products_item['buy_number'] - $orders_products_item['already_stocked_number'];
                        }
                        $radio = Common::PriceCalculate($GoodStock, '/', $GoodAlready_stocked_number, $scale = '3');
                        $radio = $radio > 100 ? 100 : $radio;
                        $results =  Orders::where('id', $order_item['id'])->update(['radio' => $radio]);
                        if($results){
                            echo "订单id：{$order_item['id']},更新配货比成功";
                        }
                    }
                }
            }
        }

        /**
         * @return array
         * Note: 发送配货单数据到仓库
         * Date: 2019/3/22 14:00
         * Author: zt8067
         */
        protected function sendDistrbutuonData($data ,$account)
        {
            $results = self::$COMMON->sendWarehouse('createOrder', $data ,$account);//已经调整
            return $results;
        }

        /**
         * @return bool
         * Note: cli模式
         * Data: 2019/5/31 14:17
         * Author: zt7785
         */
        public function is_cli () {
            return preg_match("/cli/i", php_sapi_name()) ? true : false;
        }
    }