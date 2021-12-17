<?php

    namespace App\Http\Services\Order;

    use App\Common\Common;
    use App\Console\Logic\TrackingNo;
    use App\Exceptions\DataNotFoundException;
    use App\Models\Orders;
    use App\Models\OrdersBillPayments;
    use App\Models\OrdersInvoices;
    use App\Models\OrdersInvoicesProducts;
    use App\Models\OrdersLogs;
    use App\Models\OrdersProducts;
    use App\Models\OrdersTroublesRecord;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseSecretkey;
    use App\Models\WarehouseTypeGoods;
    use Illuminate\Support\Facades\DB;

    class OrdersService
    {
        public $ordersModel = null;
        public $orderLogModel = null;
        public $commonAction = null;

        public function __construct()
        {
            $this->ordersModel = new Orders();
            $this->orderLogModel = new OrdersLogs();
            $this->commonAction = new Common();
        }

        /**
         * @param $logistics_number String|Array 订单物流跟踪号 仓库平台订单号 非物流跟踪号
         * @param $orderInfo        订单信息
         * @param $remark           拦截原因
         *                          Note: 订单拦截
         *                          Data: 2019/4/11 13:34
         *                          Author: zt7785
         */
        public function interceptOrders($logistics_number, $remark)
        {
            //订单对应多个配货单 需要找到未拦截的配货单依次拦截
            if (is_array($logistics_number)) {
                $param ['order_code'] = json_encode($logistics_number);
            } else {
                $param ['order_code'] = $logistics_number;
            }
            $param ['reason'] = $remark;
            $serviceName = 'cancelOrder';
            $response ['msg'] = '仓储物流订单: ' . $logistics_number;
            $response ['is_succ'] = false;
            $response ['action_code'] = 400;
            try {
                $wareHouseResult = $this->commonAction->sendWarehouse($serviceName, $param);//未使用
                if (!isset($wareHouseResult['ask'])) {
                    $response ['msg'] .= '接口请求异常';
                    $response ['is_succ'] = false;
                    $response ['action_code'] = 400;
                }
                if ($wareHouseResult['ask'] == 'Success') {
                    $response ['msg'] .= '拦截成功';
                    $response ['is_succ'] = true;
                    $response ['action_code'] = 200;
                } else {
                    $response ['msg'] .= '接口请求成功,但拦截失败';
                    $response ['is_succ'] = false;
                    $response ['action_code'] = 200;
                }
            } catch (\Exception $e) {
                $response ['msg'] .= '接口请求异常';
                $response ['is_succ'] = false;
                $response ['action_code'] = 500;
            }
            return $response;
        }

        /**
         * @return mixed
         * Note: 部分退款
         * Date: 2019/4/19 14:20
         * Desc: 针对未发货部分的商品，可以进行部分退款操作，不再进行发货操作，同时自动生成退款单
         * Author: zt8067
         */
        public function partialRefundProcess($params = [], $user_id)
        {
            $result = ['code' => -1, 'msg' => '部分退款失败', 'data' => '', 'errorArr' => ''];
            do {
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

                $order_id = $params['order_id'] ?? '';
                $goods = $params['goods'] ?? '';
                $check = false;
                $Orders = Orders::where('deliver_status','<>',Orders::ORDER_DELIVER_STATUS_FILLED)
                                ->where('picking_status','<>',Orders::ORDER_PICKING_STATUS_MATCHED_SUCC)
                                ->where([
                                    'id' => $order_id,
                                    'user_id' => $user_id,
                                    //'intercept_status' => Orders::ORDER_INTERCEPT_STATUS_INITIAL,
                                    'status' => Orders::ORDER_STATUS_UNFINISH
                                ])->first();
                if (empty($Orders)) {
                    $result['msg'] = '订单不是未发货，和部分发货订单，不允许部分退款操作';
                    break;
                }
                if (empty($goods)) {
                    $result['msg'] = '商品为空';
                    break;
                }
                //更新物流号 更新发货状态 和已发货数量
                (new TrackingNo)->handel($Orders->id);
                self::updateShipment($order_id,$user_id);
                DB::beginTransaction();
                try {
                    $totalPrice = 0;
                    $totalNum = 0;
                    $refundStr = '';
                    //校验商品真实性
                    foreach ($goods as $k => &$good_item) {
                        $OrdersProductsM = OrdersProducts::where('id', $good_item['id'])->sharedLock()->first();
                        if (empty($OrdersProductsM)) {
                            $result['msg'] = 'SKU：' . $good_item['sku'] . '不存在的商品';
                            break 2;
                        }
                        $OrdersProducts = $OrdersProductsM->toArray();
                        if ($good_item['partial_refund_number'] <= 0 || $OrdersProducts['buy_number'] == $OrdersProducts['delivery_number']) {
                            unset($goods[$k]);
                            continue;
                        }
                        if ($good_item['partial_refund_number'] > ($OrdersProducts['buy_number'] - $OrdersProducts['delivery_number'])) {
                            $result['msg'] = 'SKU：' . $good_item['sku'] . '退款商品数不能大于购买数减已发货数量';
                            break 2;
                        }

                        $good_item['goods_id'] = $OrdersProducts['goods_id'];
                        $totalPrice += $this->commonAction->PriceCalculate($good_item['partial_refund_number'], '*', $OrdersProducts['univalence']);
                        $totalNum += $good_item['partial_refund_number'];
                        $refundStr .= 'SKU:' . $good_item['sku'] . '部分退款,退款数量:' . $good_item['partial_refund_number'] . ',商品单价:' . $OrdersProducts['univalence'] . "<br/>";

                    }
                    $orderM = Orders::where(['id'=> $order_id,'user_id'=>$user_id])->first();
                    if (empty($orderM)) {
                        $result['msg'] = '不存在的订单';
                        break;
                    }
                    $order = $orderM->toArray();
                    if ($totalNum <= 0) {
                        $result['msg'] = '部分退款商品数不能为0';
                        break;
                    }
                    //未生成配货单直接退款操作
                    $OrdersInvoicesAll = OrdersInvoices::where(['order_id' => $order_id, 'invoices_status' => OrdersInvoices::ENABLE_INVOICES_STATUS, 'intercept_status' => OrdersInvoices::UNINTERCEPT])->get();
                    if ($OrdersInvoicesAll->isEmpty()) {
                        $checkResults = true;
                    }
                    else {
                        // 拦截未发货仓库配货单 取消仓库配货单
                        $OrdersInvoicesM = OrdersInvoices::where(['order_id'=> $order_id,'delivery_status'=> OrdersInvoices::DELIVERY_STATUS_NO, 'invoices_status' => OrdersInvoices::ENABLE_INVOICES_STATUS, 'intercept_status' => OrdersInvoices::UNINTERCEPT])->sharedLock()->get();
                        if ($OrdersInvoicesM->isNotEmpty()) {
                            $OrdersInvoices = $OrdersInvoicesM->toArray();
                            foreach ($OrdersInvoices as $k => $Invoices) {
                                //存在速贸配货单发送仓库拦截
                                if ($Invoices['type'] == SettingWarehouse::SM_TYPE) {
                                    $arr['order_code'] = $Invoices['warehouse_order_number'];
                                    $arr['reason'] = '部分退款';
                                    $response = $this->commonAction->sendWarehouse('cancelOrder', $arr,$account);//已调整
                                    if (empty($response)) throw new \Exception('仓库接口异常，或网络错误！');
                                    if ($response['ask'] == 'Success') {
                                        //软删除配货单
                                        $check = OrdersInvoices::where('id', $Invoices['id'])->update(['invoices_status' => OrdersInvoices::DEL_INVOICES_STATUS]);
                                    } else {
                                        $result['msg'] = '抱歉，因系统后台发货数据更新，配货单号：' . $Invoices['invoices_number'] . ' 已发货成功，部分退款操作失败，请刷新页面尝试重新退款！';
                                        //存在拦截失败则代表已发货成功及时更改发货状态
                                        OrdersInvoices::where('id', $Invoices['id'])->update([
                                            'delivery_status' => OrdersInvoices::DELIVERY_STATUS_YES,
                                        ]);
                                        DB::commit();
                                        if($Invoices['delivery_status'] != OrdersInvoices::DELIVERY_STATUS_YES){
                                            //再次更新发货状态 和已发货数量
                                            self::updateShipment($order_id,$user_id,true);
                                        }
                                        break 2;
                                    }
                                }
                                else {
                                    //自定义仓库
                                    //软删除配货单
                                    $check = OrdersInvoices::where(['id'=> $Invoices['id']])->update(['invoices_status' => OrdersInvoices::DEL_INVOICES_STATUS]);
                                }
                                if ($check) {
                                    $OrdersInvoicesProducts = OrdersInvoicesProducts::where('invoice_id', $Invoices['id'])->get();
                                    if ($OrdersInvoicesProducts->isEmpty()) throw new \Exception('未找到配货单商品');
                                    foreach ($OrdersInvoicesProducts->toArray() as $invoicesProduct) {
                                        $checkResults = OrdersProducts::where(['goods_id'=> $invoicesProduct['goods_id'],'order_id'=>$order_id])->decrement('already_stocked_number', $invoicesProduct['already_stocked_number']);
                                        //回退仓库库存
                                        $checkResults && WarehouseTypeGoods::where(['goods_id' => $invoicesProduct['goods_id'], 'setting_warehouse_id' => $Invoices['warehouse_id']])->increment('available_in_stock', $invoicesProduct['already_stocked_number']);
                                    }
                                }
                            }
                        }else{
                            $checkResults = true;
                        }
                    }
                    //退款单
                    if ($checkResults) {
                        //删除配货单成功后
                        foreach ($goods as $good_item2) {
                            //减去购买商品
                            $checkResults = OrdersProducts::where('goods_id', $good_item2['goods_id'])->decrement('buy_number', $good_item2['partial_refund_number']);
                            $checkResults = OrdersProducts::where('goods_id', $good_item2['goods_id'])->increment('partial_refund_number', $good_item2['partial_refund_number']);
                        }
                        //删除为零的商品
                        OrdersProducts::where(['order_id'=>$order_id,'buy_number'=>0])->update(['is_deleted' => OrdersProducts::ORDERS_PRODUCT_DELETED]);
                        $checkPickingStatus = OrdersInvoices::where(['order_id'=>$order_id,'delivery_status'=>OrdersInvoices::DELIVERY_STATUS_YES,'intercept_status'=>OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL,'invoices_status'=>OrdersInvoices::ENABLE_INVOICES_STATUS])->exists();
                            $picking_status = Orders::ORDER_PICKING_STATUS_UNMATCH;
                        if($checkPickingStatus){
                            $picking_status = Orders::ORDER_PICKING_STATUS_MATCHED_PART;
                        }

                        //修改退款状态 ，订单金额， 配货状态 订单problem 改为无问题
                        Orders::where(['id' => $order_id,'user_id' => $user_id])->update(['picking_status'=>$picking_status,'problem'=>Orders::NO_PROBLEM,'sales_status' => Orders::ORDER_SALES_STATUS_APPLYED, 'order_price' => $this->commonAction->PriceCalculate($order ['order_price'], '-', $totalPrice)]);
                        $PaymentsCount = OrdersBillPayments::where('created_man', $user_id)->where('order_id', $order_id)->count();
                        $PaymentsCount || $PaymentsCount = 0;
                        $OrdersBillPaymentsM = new OrdersBillPayments();
                        $OrdersBillPaymentsM->created_man = $user_id;
                        $OrdersBillPaymentsM->order_id = $params['order_id'];
                        $OrdersBillPaymentsM->order_type = $order['platforms_id'];
                        $OrdersBillPaymentsM->status = OrdersBillPayments::BILLS_STATUS_FINISH;
                        $OrdersBillPaymentsM->type = OrdersBillPayments::BILLS_REFUND;
                        $OrdersBillPaymentsM->currency_code = $order['currency_code'];
                        $OrdersBillPaymentsM->amount = -$totalPrice;
                        $OrdersBillPaymentsM->rate = $order['rate'];
                        $OrdersBillPaymentsM->bill_code = 'T' . $order['order_number'] . '-' . ($PaymentsCount + 1);
                        //日志
                        $orderLogsData ['created_man'] = $user_id;
                        $orderLogsData ['order_id'] = $order_id;
                        $orderLogsData ['behavior_types'] = OrdersLogs::LOGS_ORDERS_PART_PRODUCT_REFUNDED;
                        $orderLogsData ['behavior_type_desc'] = OrdersLogs::ORDERS_LOGS_TYPE_DESC[$orderLogsData ['behavior_types']];
                        $orderLogsData ['behavior_desc'] = $refundStr;
                        $orderLogsData ['updated_at'] = $orderLogsData ['created_at'] = date('Y-m-d H:i:s');
                        OrdersLogs::postDatas(0, $orderLogsData);
                        $OrdersBillPaymentsM->save() && DB::commit();
                        $result['code'] = 1;
                        $result['msg'] = '订单退款操作成功';
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    $result['msg'] = $e->getMessage();
                    Common::mongoLog($e, '订单部分退款', '退款操作失败', __FUNCTION__);
                    OrdersLogs::standardOrderLogs($params['order_id'], $user_id, OrdersLogs::LOGS_ORDERS_PART_PRODUCT_REFUND_FAILED);
                } catch (\Error $e) {
                    DB::rollBack();
                    $result['msg'] = $e->getMessage();
                    Common::mongoLog($e, '订单部分退款', '退款操作失败', __FUNCTION__);
                    OrdersLogs::standardOrderLogs($params['order_id'], $user_id, OrdersLogs::LOGS_ORDERS_PART_PRODUCT_REFUND_FAILED);
                } finally{
                    //再次更新发货状态 和已发货数量
                    self::updateShipment($order_id,$user_id);
                }
            } while (0);
            return $result;
        }

        /**
         * @return mixed
         * Note: 拦截订单
         * Date: 2019/4/16 15:00
         * Desc: 订单拦截调用 A ① $params['id'] = 订单id     ② $params['intercept_reason'] = 拦截原因  B $user_id
         * Author: zt8067
         */
        public function interceptOrderProcess($params = [], $user_id)
        {
            $result = ['code' => -1, 'msg' => '拦截失败', 'data' => '', 'errorArr' => ''];
            do {
                $intercept_reason = $params['intercept_reason'] ?? '';
                if (empty($intercept_reason)) {
                    $result['msg'] = '请填写拦截原因';
                    break;
                }
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

                $trouble_name = '拦截订单';
                //更新发货状态 和已发货数量
                (new TrackingNo)->handel();
                self::updateShipment($params['order_id'],$user_id);
                DB::beginTransaction();
                try {
                    $Orders = Orders::where('id', $params['order_id'])
                        ->where('intercept_status', Orders::ORDER_INTERCEPT_STATUS_INITIAL)//未拦截
                        ->where('deliver_status', '<>', Orders::ORDER_DELIVER_STATUS_FILLED)//发货成功
                        ->exists();
                    if (!$Orders) break;
                    $OrdersInvoicesM = OrdersInvoices::where('order_id', $params['order_id'])
                        ->where('user_id', $user_id)
                        ->where('intercept_status', OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL)//未拦截
                        ->where('invoices_status', OrdersInvoices::ENABLE_INVOICES_STATUS)//未作废
                        ->get();
                    if ($OrdersInvoicesM->isEmpty()) {
                        $updata = true;
                    }
                    else{
                        $OrdersInvoices = $OrdersInvoicesM->toArray();
                        $checkNoramlNum = 0;
                        $checkOKNum = 0;
                        //存在已发货的配货单则拦截失败
                        foreach ($OrdersInvoices as $invoice) {
                            if ($invoice['delivery_status'] === OrdersInvoices::DELIVERY_STATUS_YES) {
                                $result['msg'] = '存在已发货配货单，订单拦截失败！';
                                break 2;
                            }
                            if ($invoice['type'] == SettingWarehouse::SM_TYPE) {
                                $checkNoramlNum++;
                            }
                        }
                        foreach ($OrdersInvoices as $invoice_item) {
                            //判断是不是速贸仓储
                            if ($invoice_item['type'] == SettingWarehouse::SM_TYPE) {
                                /*
                                 * a) 拦截成功后，作废拦截成功的配货单；回退库存；
                                 * b) “拦截中“状态时，可人工操作结束拦截；
                                 * c) 拦截成功后，将订单添加进行订单问题，问题类型”拦截订单“，问题描述，即”添加的备注“，操作“已处理“。点击”已处理“后，订单再重新跑订单流程，进行配货发货处理。
                                 */
                                $arr['order_code'] = $invoice_item['warehouse_order_number'];
                                $arr['reason'] = $intercept_reason;
                                $response = $this->commonAction->sendWarehouse('cancelOrder', $arr,$account);//已调整
                                if (empty($response)) throw new \Exception('仓库接口异常，或网络错误');
                                if ($response['ask'] == 'Success') {
                                    $checkOKNum++;
                                    $updata = OrdersInvoices::where('id', $invoice_item['id'])->update([
                                        'intercept_status' => OrdersInvoices::ORDER_INTERCEPT_STATUS_INTERCEPTED,//订单拦截状态拦截成功
                                        'invoices_status'  => OrdersInvoices::DISABLED_INVOICES_STATUS,//已作废
                                    ]);
                                } else {
                                    //拦截失败
                                    OrdersInvoices::where('id', $invoice_item['id'])->update([
                                        'intercept_status' => OrdersInvoices::ORDER_INTERCEPT_STATUS_FAILED,
                                    ]);
                                    Orders::where('id', $invoice_item['order_id'])->update(['intercept_status' => Orders::ORDER_INTERCEPT_STATUS_FAILED]);
                                    break;
                                }
                            } else {
                                $updata = OrdersInvoices::where('id', $invoice_item['id'])->update([
                                    'intercept_status' => OrdersInvoices::ORDER_INTERCEPT_STATUS_INTERCEPTED,//订单拦截状态拦截成功
                                    'invoices_status'  => OrdersInvoices::DISABLED_INVOICES_STATUS,//已作废
                                ]);
                            }
                            if ($updata) {
                                $OrdersInvoicesProductsM = OrdersInvoicesProducts::where(['invoice_id'=>$invoice_item['id'],'user_id'=>$user_id])->get();
                                if ($OrdersInvoicesProductsM->isEmpty()) throw new \Exception('配货单参数错误');
                                $OrdersInvoicesProducts = $OrdersInvoicesProductsM->toArray();
                                foreach ($OrdersInvoicesProducts as $InProduct_item) {
                                    WarehouseTypeGoods::where(['user_id'=>$user_id,'goods_id' => $InProduct_item['goods_id'], 'setting_warehouse_id' => $invoice_item['warehouse_id']])->increment('available_in_stock', $InProduct_item['already_stocked_number']);
                                    OrdersProducts::where('goods_id', $InProduct_item['goods_id'])->decrement('already_stocked_number', $InProduct_item['already_stocked_number']);
                                    OrdersProducts::where('goods_id', $InProduct_item['goods_id'])->update(['cargo_distribution_number' => 0]);
                                }
                            }
                        }

                        if($checkNoramlNum !== $checkOKNum){
                        //部分拦截配货单
                            $trouble_name = '拦截部分配货单';
                        }
                    }
                    //拦截成功后，将订单添加进行订单问题，问题类型”拦截订单“，问题描述，即”添加的备注“，操作“已处理“。点击”已处理“后，订单再重新跑订单流程，进行配货发货处理。
                    if ($updata) {
                        Orders::where('id', $params['order_id'])->update(['problem'=>Orders::NO_PROBLEM,'intercept_status' => Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED, 'picking_status' => Orders::ORDER_PICKING_STATUS_UNMATCH, 'deliver_status' => Orders::ORDER_DELIVER_STATUS_UNFILLED]);
                        $OrdersTroublesRecord = new OrdersTroublesRecord();
                        $OrdersTroublesRecord->manage_id = 0;
                        $OrdersTroublesRecord->created_man = $user_id;
                        $OrdersTroublesRecord->order_id = $params['order_id'];
                        $OrdersTroublesRecord->question_type = OrdersTroublesRecord::QUESTION_TYPE_INTERCEPT;
                        $OrdersTroublesRecord->trouble_name = $trouble_name;
                        $OrdersTroublesRecord->trouble_desc = $intercept_reason;
                        $OrdersTroublesRecord->save();
                        //日志
                        OrdersLogs::standardOrderLogs($params['order_id'], $user_id, OrdersLogs::LOGS_ORDERS_INTERCEPT_SUCC);
                        DB::commit();
                        $result['code'] = 1;
                        $result['msg'] = '拦截成功';
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Common::mongoLog($e, '拦截订单', '拦截订单失败', __FUNCTION__);
                    //日志
                    OrdersLogs::standardOrderLogs($params['order_id'], $user_id, OrdersLogs::LOGS_ORDERS_INTERCEPT_FAILED);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Common::mongoLog($e, '拦截订单', '拦截订单失败', __FUNCTION__);
                    //日志
                    OrdersLogs::standardOrderLogs($params['order_id'], $user_id, OrdersLogs::LOGS_ORDERS_INTERCEPT_FAILED);
                }
            } while (0);
            return $result;
        }

        /**
         * @return mixed
         * Note: 取消订单
         * Date: 2019/4/17 13:00
         * Desc: “未发货“的订单可进行“取消订单”操作；
         * Author: zt8067
         */
        public function cancelOrderProcess($params = [], $user_id)
        {
            $result = ['code' => -1, 'msg' => '取消订单失败', 'data' => '', 'errorArr' => ''];
            do {
                $cancel_reason = $params['cancel_reason'] ?? '';

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

                //更新发货状态 和已发货数量
                self::updateShipment($params['order_id'],$user_id);
                DB::beginTransaction();
                try {
                    $OrdersM = Orders::where(['id' => $params['order_id'], 'deliver_status' => Orders::ORDER_DELIVER_STATUS_UNFILLED, 'status' => Orders::ORDER_STATUS_UNFINISH])->first();
                    if (empty($OrdersM)) {
                        $result['msg'] = '非法订单不能进行取消操作';
                        break;
                    }
                    $Orders = $OrdersM->toArray();
                    $tracking_no_exists = OrdersInvoices::where('order_id', $params['order_id'])
                        ->where('user_id', $user_id)
                        ->where('intercept_status', OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL)//未拦截
                        ->where('invoices_status', OrdersInvoices::ENABLE_INVOICES_STATUS)//未作废
                        ->where('delivery_status',  OrdersInvoices::DELIVERY_STATUS_YES)//已发货
                        ->exists();
                    if ($tracking_no_exists) {
                        $result['msg'] = '订单已部分发货成功，不能进行取消操作';
                        break;
                    }
                    $OrdersInvoicesM = OrdersInvoices::where('order_id', $params['order_id'])
                        ->where('user_id', $user_id)
                        ->where('intercept_status', OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL)//未拦截
                        ->where('invoices_status', OrdersInvoices::ENABLE_INVOICES_STATUS)//未作废
                        ->get();
                    if ($OrdersInvoicesM->isEmpty()) {
                        //取消订单
                        $updata = true;
                    } else {
                        $OrdersInvoices = $OrdersInvoicesM->toArray();
                        foreach ($OrdersInvoices as $invoice_item) {
                            //判断是不是速贸仓储
                            if ($invoice_item['type'] == SettingWarehouse::SM_TYPE) {
                                $arr['order_code'] = $invoice_item['warehouse_order_number'];
                                $arr['reason'] = $cancel_reason;
                                $response = $this->commonAction->sendWarehouse('cancelOrder', $arr,$account);//已调整
                                if (empty($response)) throw new \Exception('仓库接口异常，或网络错误');
                                if ($response['ask'] == 'Success') {
                                    $updata = OrdersInvoices::where('id', $invoice_item['id'])->update(['invoices_status' => OrdersInvoices::DEL_INVOICES_STATUS]);
                                } else {
                                    $result['code'] = -1;
                                    $result['msg'] = '配货单号：'.$invoice_item['invoices_number'] . ' 已发货成功，取消订单失败';
                                    break 2;
                                }
                            } else {
                                $updata = OrdersInvoices::where('id', $invoice_item['id'])->update(['invoices_status' => OrdersInvoices::DEL_INVOICES_STATUS]);
                            }
                            if ($updata) {
                                $OrdersInvoicesProductsM = OrdersInvoicesProducts::where(['invoice_id'=>$invoice_item['id'],'user_id'=>$user_id])->get();
                                if ($OrdersInvoicesM->isEmpty()) throw new \Exception('配货单商品异常');
                                foreach ($OrdersInvoicesProductsM->toArray() as $InProduct_item) {
                                    WarehouseTypeGoods::where(['goods_id' => $InProduct_item['goods_id'], 'setting_warehouse_id' => $invoice_item['warehouse_id']])->increment('available_in_stock', $InProduct_item['already_stocked_number']);
                                }
                            }
                        }
                    }
                    //拦截成功后，取消订单
                    if ($updata) {

                        Orders::where(['id'=> $params['order_id'],'user_id'=>$user_id])->update(['cancel_reason' => $cancel_reason, 'status' => Orders::ORDER_STATUS_OBSOLETED, 'picking_status' => Orders::ORDER_PICKING_STATUS_UNMATCH, 'deliver_status' => Orders::ORDER_DELIVER_STATUS_UNFILLED]);
                        $PaymentsCount = OrdersBillPayments::where('created_man', $user_id)->where('order_id', $params['order_id'])->count();
                        $PaymentsCount || $PaymentsCount = 0;
                        //生成退款单
                        $OrdersBillPaymentsM = new OrdersBillPayments();
                        $OrdersBillPaymentsM->created_man = $user_id;
                        $OrdersBillPaymentsM->order_id = $params['order_id'];
                        $OrdersBillPaymentsM->order_type = $Orders['platforms_id'];
                        $OrdersBillPaymentsM->status = OrdersBillPayments::BILLS_STATUS_FINISH;
                        $OrdersBillPaymentsM->type = OrdersBillPayments::BILLS_REFUND;
                        $OrdersBillPaymentsM->amount = -$params['money'];
                        $OrdersBillPaymentsM->currency_code = $Orders['currency_code'];
                        $OrdersBillPaymentsM->rate = $Orders['rate'];
                        $OrdersBillPaymentsM->bill_code = 'T' . $Orders['order_number'] . '-' . ($PaymentsCount + 1);
                        //日志
                        OrdersLogs::standardOrderLogs($params['order_id'], $user_id, OrdersLogs::LOGS_ORDERS_CANCEL);
                        $OrdersBillPaymentsM->save() && DB::commit();
                        $result['code'] = 1;
                        $result['msg'] = '订单取消成功';
                    } else {
                        DB::rollBack();
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Common::mongoLog($e, '取消订单', '取消订单失败', __FUNCTION__);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Common::mongoLog($e, '取消订单', '取消订单失败', __FUNCTION__);
                }
            } while (0);
            return $result;
        }

        /**
         * @return mixed
         * Note:更新发货状态 并且更新已发货数量
         * Date: 2019/4/17 14:00
         * $state 部分退款出现 部分发货 更新 $state
         * Author: zt8067
         */
        public static function updateShipment($order_id = '',$user_id='',$state = false)
        {
            if ($order_id) {
                $OrdersM = Orders::whereHas('OrdersInvoicesMany')->with(['OrdersProducts', 'OrdersInvoicesMany' => function ($query) {
                    $query->with('OrdersInvoicesProduct')->where('delivery_status',OrdersInvoices::DELIVERY_STATUS_YES);//已发货
                    $query->where('intercept_status',OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL);//未拦截
                    $query->where('invoices_status',OrdersInvoices::ENABLE_INVOICES_STATUS); //未作废
                },
                ])->where('id', $order_id)->get();
            } else {
                $OrdersM = Orders::whereHas('OrdersInvoicesMany')->with(['OrdersProducts', 'OrdersInvoicesMany' => function ($query) {
                    $query->with('OrdersInvoicesProduct')->where('delivery_status',OrdersInvoices::DELIVERY_STATUS_YES);//已发货
                    $query->where('intercept_status',OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL);//未拦截
                    $query->where('invoices_status',OrdersInvoices::ENABLE_INVOICES_STATUS); //未作废
                },
                ])->get();

            } //['id','orders_products','orders_invoices_many','order_number']
            if ($OrdersM->isNotEmpty()) {
                $Orders = $OrdersM->toArray();
                $invoicesGoods = [];
                $ordersGoods = [];
                foreach ($Orders as $order_item) {
                    $orders_products = $order_item['orders_products'];
                    $orders_invoices = $order_item['orders_invoices_many'];
                    if (empty($orders_products) || empty($orders_invoices)) {
                        continue;
                    }
                    //配货单商品数量
                    foreach ($orders_invoices as $invoice) {
                        $orders_invoices_product = $invoice['orders_invoices_product'];
                        foreach ($orders_invoices_product as $k => $invoices_product) {
                            if (isset($invoicesGoods[$invoices_product['goods_id']])) {
                                $invoicesGoods[$invoices_product['goods_id']] += $invoices_product['already_stocked_number'];
                            } else {
                                $invoicesGoods[$invoices_product['goods_id']] = $invoices_product['already_stocked_number'];
                            }
                        }
                    }
                    //订单商品数量
                    foreach ($orders_products as $orders_product) {
                        $ordersGoods[$orders_product['goods_id']] = $orders_product['buy_number'];
                    }
                    //商品比较判断是否全部发货
                    if ($invoicesGoods == $ordersGoods) {
                        $deliver_status = Orders::ORDER_DELIVER_STATUS_FILLED;//发货成功 2
                        $status = Orders::ORDER_STATUS_FINISHED;//已完结
                        $picking = Orders::ORDER_PICKING_STATUS_MATCHED_SUCC;//配货成功
                    } else {
                        $deliver_status = Orders::ORDER_DELIVER_STATUS_FILLED_PART;//已部分发货 3
                        $status = Orders::ORDER_STATUS_UNFINISH;//未完结
                        $picking = Orders::ORDER_PICKING_STATUS_MATCHED_PART;//部分配货
                    }
                    unset($invoicesGoods, $ordersGoods);
                    Orders::where('id', $order_item['id'])->update([
                        'deliver_status' => $deliver_status,
                        'picking_status' => $picking,
                        //'status' => $status
                    ]);
                    if(Common::is_cli()){
                        echo '订单号：'.$order_item['order_number']."更新发货状态成功！\r\n";
                    }
                }
                if($state || Common::is_cli()){
                    (new self())->updateDeliveryNumber($order_id,$order_item['order_number'],$user_id);
                }
            }
        }
        /**
         * @return mixed
         * Note:更新已发货数量
         * Date: 2019/4/17 14:00
         * Author: zt8067
         */
        public function updateDeliveryNumber($order_id = '',$order_number='',$user_id = ''){
            $connection = OrdersInvoices::query();
            $connection->with('OrdersInvoicesProduct');
            if($order_id && $user_id){
                $connection->where('user_id',$user_id);
            }else{
                $connection->limit(5000);
            }
            $connection->where('state',OrdersInvoices::STATE_NO);
            $connection->where([
                'delivery_status'  => OrdersInvoices::DELIVERY_STATUS_YES,//已发货
                'intercept_status' => OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL,//单拦截状态未拦截初始状态
                'invoices_status'  => OrdersInvoices::ENABLE_INVOICES_STATUS,//未作废
            ]);
            $OrdersInvoicesM =  $connection->get();
            if ($OrdersInvoicesM->isNotEmpty()) {
                $OrdersInvoices = $OrdersInvoicesM->toArray();
                foreach ($OrdersInvoices as $invoice) {
                    if (isset($invoice['orders_invoices_product']) && is_array($invoice['orders_invoices_product'])) {
                        $is_check = false;
                        foreach ($invoice['orders_invoices_product'] as $invoices_product) {
                            $is_check = OrdersProducts::where(['order_id' => $invoices_product['order_id'], 'goods_id' => $invoices_product['goods_id']])->increment('delivery_number' ,$invoices_product['already_stocked_number']);
                        }
                        $is_check && OrdersInvoices::where('id',$invoice['id'])->update(['state' => OrdersInvoices::STATE_YES,'delivered_at'=>date('Y-m-d H:i:s')]);
                        if($is_check){
                            if(Common::is_cli()) {
                                echo '订单号：' . $order_number . "更新发货数成功！\r\n";
                            }
                        }
                    }
                }
            }
        }
 }