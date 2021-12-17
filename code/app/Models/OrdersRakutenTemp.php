<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersRakutenTemp extends Model
{

    protected $table = 'orders_rakuten_temp';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','cw_order_id','created_man','cw_code','is_system','match_status','orderNumber','asurakuFlg','cardStatus','carrierCode','deliveryClass','deliveryName','deliveryPrice','emailCarrierCode','enclosureDeliveryPrice','enclosureId','enclosureStatus','firstAmount','goodsPrice','goodsTax','isBlackUser','isGift','isGiftCheck','isRakutenMember','modify','option','orderDate','orderPayDate','orderType','buyer_sex','buyer_birthDay','buyer_birthMonth','buyer_birthYear','buyer_city','buyer_prefecture','buyer_emailAddress','buyer_familyName','buyer_familyNameKana','buyer_firstName','buyer_firstNameKana','buyer_phoneNumber1','buyer_phoneNumber2','buyer_phoneNumber3','buyer_subAddress','buyer_zipCode1','buyer_zipCode2','package_basketId','package_deliveryCompanyId','package_deliveryPrice','package_goodsPrice','package_goodsTax','package_postagePrice','pointUsage','point_status','usedPoint','postagePrice','requestPrice','seqId','pay_settlementName','pay_brandName','pay_cardNo','pay_expYM','pay_ownerName','pay_payType','shippingTerm','status','totalPrice','wishDeliveryDate','memo','send_city','send_prefecture','send_familyName','send_familyNameKana','send_firstName','send_firstNameKana','send_phoneNumber1','send_phoneNumber2','send_phoneNumber3','send_subAddress','send_zipCode1','send_zipCode2','created_at','updated_at'];

    protected $settlementMethodNameArr = [
        'クレジットカード'                 =>    '信用卡',
        'ローソン前払'                     =>    '便利店',
        'ローソン、郵便局ATM等（前払）'    =>    '便利店',
        'セブンイレブン前払'               =>    '便利店',
        'セブンイレブン（前払）'           =>    '便利店',
        '楽天Edy決済'                      =>    '便利店',
        '後払い決済'                       =>    '便利店',
        '楽天バンク決済'                   =>    '便利店',
        '銀行振込'                         =>    '银行汇款',
        '楽天バンク'                       =>    '乐天银行',
        '代金引換'                       =>    '代金引換',
    ];

    /*
     * @var 异常类型
     */
    public $exceptionTask = 'orders';

    public $exceptionAPI = 'orders_api';

    public $exceptionItemAPI = 'orders_item';

    public $temp_ori_order = [];

    /**
     * @param $orderDatas
     * Note: 订单逻辑
     * Data: 2019/4/9 17:07
     * Author: zt7785
     */
    public function orderDataLogics ($orderDatas ,$shopInfo = [] ,$is_delete = false) {
        $country  = 'JP';
        $currency = 'JPY';
        $current_time = date('Y-m-d H:i:s');
        $shopIds = array_column($shopInfo,'id');
        $country_id = SettingCountry::getCountryIdByCode($country)??0;
        $currencyInfo = [];
        $currencyInfo ['currency_code'] = $currency;
        foreach ($orderDatas as $key=>$value){
            $remark = '';//信息备注
            $sale_info = $value ['saleInfo'];
            $sale_info['shop_id'] = $sale_info ['id'];
            $sale_info['platforms_id'] = Platforms::RAKUTEN;
            $userLogisticsInfo = SettingLogistics::getLogisticInfoByUserId($sale_info['user_id']);
            $userLogisticsCodes = array_column($userLogisticsInfo,'logistic_code');
            $rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($currency,$sale_info['user_id']);
            $currencyInfo ['rate'] = $rate;
            foreach ($value['orderInfo'] as $v) {
                try {
                    $order_rakuten_source = $v['orderNumber'];
                    $is_temp_exist = self::getRakutenOrdersInfoByCode($order_rakuten_source, $sale_info['user_id']);
                    if ($is_temp_exist) {
                        continue;
                    }
                    //返回订单信息 配货单信息 配货单下商品信息
                    $cw_erp_exist = Orders::getOrdersByOriordercode($order_rakuten_source);
                    if (!empty($cw_erp_exist)) {
                        continue;
                    }
                    //写新数据逻辑
//                $this->postNewOrderInfo($sale_info ,$v ,$current_time ,$order_rakuten_source ,$shopIds ,$shopInfo ,$currencyInfo ,$country_id ,$country ,$userLogisticsCodes ,$userLogisticsInfo );
                    //写原始订单逻辑
                    $this->postNewOrderInfos($sale_info ,$v ,$current_time ,$order_rakuten_source ,$shopIds ,$shopInfo ,$currencyInfo ,$country_id ,$country ,$userLogisticsCodes ,$userLogisticsInfo );
                } catch (\Exception $e) {
                    $exception_data = [
                        'start_time'                => $current_time,
                        'msg'                       => '失败信息：' . $e->getMessage(),
                        'line'                      => '失败行数：' . $e->getLine(),
                    ];
                    LogHelper::setExceptionLog($exception_data,$this->exceptionTask);
                }
            }
        }

        if ($is_delete)
        {
            //todo
            //orders_rakuten_temp 需要delete权限
            //临时表删除
            self::where('id','>','0')->delete();
            //订单商品表继续关联
            DB::insert('INSERT INTO orders_rakuten (id,cw_order_id,created_man,cw_code,is_system,match_status,orderNumber,asurakuFlg,cardStatus,carrierCode,deliveryClass,deliveryName,deliveryPrice,emailCarrierCode,enclosureDeliveryPrice,enclosureId,enclosureStatus,firstAmount,goodsPrice,goodsTax,isBlackUser,isGift,isGiftCheck,isRakutenMember,modify,`option`,orderDate,orderPayDate,orderType,buyer_sex,buyer_birthDay,buyer_birthMonth,buyer_birthYear,buyer_city,buyer_prefecture,buyer_emailAddress,buyer_familyName,buyer_familyNameKana,buyer_firstName,buyer_firstNameKana,buyer_phoneNumber1,buyer_phoneNumber2,buyer_phoneNumber3,buyer_subAddress,buyer_zipCode1,buyer_zipCode2,package_basketId,package_deliveryCompanyId,package_deliveryPrice,package_goodsPrice,package_goodsTax,package_postagePrice,pointUsage,point_status,usedPoint,postagePrice,requestPrice,seqId,pay_settlementName,pay_brandName,pay_cardNo,pay_expYM,pay_ownerName,pay_payType,shippingTerm,status,totalPrice,wishDeliveryDate,memo,send_city,send_prefecture,send_familyName,send_familyNameKana,send_firstName,send_firstNameKana,send_phoneNumber1,send_phoneNumber2,send_phoneNumber3,send_subAddress,send_zipCode1,send_zipCode2,created_at,updated_at) SELECT id,cw_order_id,created_man,cw_code,is_system,match_status,orderNumber,asurakuFlg,cardStatus,carrierCode,deliveryClass,deliveryName,deliveryPrice,emailCarrierCode,enclosureDeliveryPrice,enclosureId,enclosureStatus,firstAmount,goodsPrice,goodsTax,isBlackUser,isGift,isGiftCheck,isRakutenMember,modify,`option`,orderDate,orderPayDate,orderType,buyer_sex,buyer_birthDay,buyer_birthMonth,buyer_birthYear,buyer_city,buyer_prefecture,buyer_emailAddress,buyer_familyName,buyer_familyNameKana,buyer_firstName,buyer_firstNameKana,buyer_phoneNumber1,buyer_phoneNumber2,buyer_phoneNumber3,buyer_subAddress,buyer_zipCode1,buyer_zipCode2,package_basketId,package_deliveryCompanyId,package_deliveryPrice,package_goodsPrice,package_goodsTax,package_postagePrice,pointUsage,point_status,usedPoint,postagePrice,requestPrice,seqId,pay_settlementName,pay_brandName,pay_cardNo,pay_expYM,pay_ownerName,pay_payType,shippingTerm,status,totalPrice,wishDeliveryDate,memo,send_city,send_prefecture,send_familyName,send_familyNameKana,send_firstName,send_firstNameKana,send_phoneNumber1,send_phoneNumber2,send_phoneNumber3,send_subAddress,send_zipCode1,send_zipCode2,created_at,updated_at FROM orders_rakuten_temp');
        }
    }

    /**
     * @param $orderDatas
     * Note: 订单逻辑 | 弃用
     * Data: 2019/4/9 17:07
     * Author: zt7785
     */
    public function orderDataLogic ($orderDatas,$shopInfo = []) {
        $country  = 'JP';
        $currency = 'JPY';
        $current_time = date('Y-m-d H:i:s');
        $shopIds = array_column($shopInfo,'id');
        $country_id = SettingCountry::getCountryIdByCode($country)??0;
        $ordersService = new OrdersService();
        $currencyInfo = [];
        $currencyInfo ['currency_code'] = $currency;
        foreach ($orderDatas as $k=>$value){
            $exceptionStatus = false; //接口异常状态
            $exceptionMsg = '';//接口异常信息
            $remark = '';//信息备注
            $sale_info = $value ['saleInfo'];
            $userLogisticsInfo = SettingLogistics::getLogisticInfoByUserId($sale_info['user_id']);
            $userLogisticsCodes = array_column($userLogisticsInfo,'logistic_code');
            $rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($currency,$sale_info['user_id']);
            $currencyInfo ['rate'] = $rate;
            foreach ($value['orderInfo'] as $v) {
                $order_rakuten_source = $v['orderNumber'];
                //判断订单是否已经在临时表
                $is_temp_exist = self::getRakutenOrdersInfoByCode($order_rakuten_source, $sale_info['user_id']);
                if ($is_temp_exist) {
                    continue;
                }

                //返回订单信息 配货单信息 配货单下商品信息
                $cw_erp_exist = Orders::getOrdersByOriordercode($order_rakuten_source);
                $orig_rakuten_info = OrdersRakuten::getRakutenOrdersInfoByCode($order_rakuten_source);
                //订单是否在订单表
                if (empty($cw_erp_exist)) {
                    if ($v['orderProgress'] == 300) {
                        if ($orig_rakuten_info) {
                            //更新数据逻辑
                            //todo
                            //原始订单金额 大于此次同步订单金额 客户在退款
                            //可能在订单匹配成功之前 客户就已经开始退款
                            if ($orig_rakuten_info ['totalPrice'] > $v ['totalPrice']) {
                                //原始订单生成退款单
                                $orig_rakuten_refund_price['totalPrice'] = $orig_rakuten_info ['totalPrice'] - $v ['totalPrice'];
                                $orig_rakuten_refund_price ['created_man'] = $sale_info['user_id'];
                                $orig_rakuten_refund_price ['cw_order_id'] = $orig_rakuten_info['id'];
                                //给原始订单生成退款单
                                $orig_rakuten_refund_data = $this->getOrdersBill($orig_rakuten_refund_price,$currencyInfo,OrdersBillPayments::BILLS_REFUND,$current_time,OrdersBillPayments::ORDERS_ORIG_RAKUTEN);
                                OrdersBillPayments::postData(0,$orig_rakuten_refund_data);
                                //300的时候写匹配规则
                                $this->mappingLogic($v,$sale_info,$order_rakuten_source,$shopIds,$shopInfo,$currencyInfo,$country_id,$country,$userLogisticsCodes,$userLogisticsInfo,$current_time,$orig_rakuten_info);
                            }
                        } else {
                            //写新数据逻辑
                            $this->postNewOrderInfo($sale_info,$v,$current_time,$order_rakuten_source,$shopIds,$shopInfo,$currencyInfo,$country_id,$country,$userLogisticsCodes,$userLogisticsInfo);
                        }
                    }
                    continue;
                }
                //订单取消处理
                if($v['orderProgress'] == 900){
                    //针对历史订单处理
                    //V1
                    //已作废订单
                    if ($cw_erp_exist['status'] == Orders::ORDER_STATUS_OBSOLETED) {
                        //已拦截成功的订单不允许再次操作
                        continue;
                    }
                    //判断订单拦截状态
                    if ($cw_erp_exist['intercept_status'] == Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED) {
                        //已拦截成功的订单不允许再次操作
                        continue;
                    }
                    //未发货 有配货单不代表已发货  有物流跟踪号才发货
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_UNFILLED) {
                        //1.拦截配货单
                        // a.拦截成功将作废
                        // b.拦截失败 将拦截成功部分金额记录
                        //2.创建退款单
                        // 3.订单信息变更 拦截状态:拦截成功 订单状态:已作废 如果有拦截失败将更新售后状态为部分退款
                        $this->cancelOrder($cw_erp_exist,$sale_info,$current_time,$currencyInfo,$ordersService,$v['totalPrice'],false);
                        continue;
                    }
                    //部分发货 只处理未发货部分
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_FILLED_PART) {
                        //针对已发货的部分，创建退款类型售后单
                        //针对未发货部分：拦截订单 直接创建一个退款单
                        if ($cw_erp_exist ['sales_status'] == Orders::ORDER_SALES_STATUS_INITIAL) {
                            $this->cancelOrder($cw_erp_exist,$sale_info,$current_time,$currencyInfo,$ordersService,$v['totalPrice'],true);
                        } else if($cw_erp_exist ['sales_status'] == Orders::ORDER_SALES_STATUS_APPLYING) {
                            //部分发货 部分退款 部分退款中将手动生成了售后单
                            //产品反馈 只要申请部分退款 生成单据直接成功 相当于 部分退款申请中 (中间态不存在)
                        } else if ($cw_erp_exist ['sales_status'] == Orders::ORDER_SALES_STATUS_APPLYED) {
                            //已发货商品
                            $deliveredGoodsInfo = [];
                            //已退货商品
                            $afterSalesGoodsInfo = [];
                            //已发货商品信息 sku 数量
                            $allGoodsInfo = [];
                            //未发货部分金额
                            $unDeliverAmount = 0.00;
                            foreach ($cw_erp_exist ['orders_invoices_value'] as $allIntercept) {
                                $invoicesData ['updated_at'] = $current_time;
                                    //部分退款的只针对未发货的退款
                                    if (!empty($allIntercept ['tracking_no'])) {
                                        //配货单下商品信息
                                        foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                            if (isset($deliveredGoodsInfo [$orders_invoices_product['goods_id']])) {
                                                $deliveredGoodsInfo [$orders_invoices_product['goods_id']] += $orders_invoices_product['already_stocked_number'];
                                            } else {
                                                $deliveredGoodsInfo [$orders_invoices_product['goods_id']] = $orders_invoices_product['already_stocked_number'];
                                                $allProductInfo = OrdersRakutenProducts::getProductInfoByOrderidGoodsid($cw_erp_exist['id'],$orders_invoices_product['goods_id']);
                                                $orders_invoices_product ['ori_price'] = $allProductInfo['price'];
                                                $allGoodsInfo [] = $orders_invoices_product;
                                            }

                                        }
                                    } else {
                                        //未发货部分直接拦截
                                        $allInterceptRe = $ordersService->interceptOrders($allIntercept['warehouse_order_number'],'ERP接口任务乐天订单取消(900)拦截指令');
                                        if ($allInterceptRe ['is_succ'] && $allInterceptRe ['action_code'] == 200) {
                                            //拦截成功
                                            $invoicesData ['invoices_status'] = OrdersInvoices::DISABLED_INVOICES_STATUS;
                                            $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_SUCC;
                                            //从乐天订单商品表中获取对应订单商品当时价格记录
                                            foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                                $allProductInfo = OrdersRakutenProducts::getProductInfoByOrderidGoodsid($cw_erp_exist['id'], $orders_invoices_product['goods_id']);
                                                //源币种
                                                $unDeliverAmount += bcmul($orders_invoices_product['already_stocked_number'], $allProductInfo['price']);
                                            }
                                            $exceptionStatus = false;
                                        } else {
                                            $exceptionMsg = $allInterceptRe ['msg'];
                                            //TODO
                                            //拦截失败 未发货拦截失败怎么处理
                                            $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_FAIL;
                                            //接口异常抛出
                                            if ($allInterceptRe ['action_code'] == 500 ) {
                                                $exceptionStatus = true;
                                            }
                                        }
                                        OrdersInvoices::where('id',$allIntercept['id'])->update($invoicesData);
                                    }
                            }
                            //部分退款成功检测具体的哪些商品创建了售后退款单
                            //获取已退款部分商品信息
                            $afterSalesPartInfos = OrdersAfterSales::getAfterSaleInfoByOrder_id($cw_erp_exist['id'],OrdersAfterSales::AFTERSALE_TYPE_REFUND);

                            foreach ($afterSalesPartInfos as $afterSalesPartInfo) {
                                if (isset($afterSalesPartInfo ['orders_after_sales_products'])) {
                                    foreach ($afterSalesPartInfo ['orders_after_sales_products'] as $orders_after_sales_products) {
                                        if (isset($deliveredGoodsInfo [$orders_after_sales_products['goods_id']])) {
                                            $afterSalesGoodsInfo [$orders_after_sales_products['goods_id']] += $orders_after_sales_products['number'];
                                        } else {
                                            $afterSalesGoodsInfo [$orders_after_sales_products['goods_id']] = $orders_after_sales_products['number'];
                                        }
                                    }
                                }
                            }

                            //一定有配货单信息 部分退款 一定有退款售后单
                            $stayAfterSalesAmount = 0.00;
                            $afterSaleDatas = [];
                            $allGoodsInfoIds = array_column($allGoodsInfo,'goods_id');
                            foreach ($deliveredGoodsInfo as $deliveredGoodsKey => $deliveredGoodsVal) {
                                $afterSaleData = [];
                                //已发货商品和售后退款处理商品数量对比
                                if (isset($afterSalesGoodsInfo[$deliveredGoodsKey])) {
                                    //如果发货数量大于商品数量 将数量差记录并创建退款信息
                                    if ($deliveredGoodsVal > $afterSalesGoodsInfo[$deliveredGoodsKey]) {
                                        $afterSaleData ['number'] = $deliveredGoodsVal - $afterSalesGoodsInfo[$deliveredGoodsKey];
                                        $goodsKey = array_search($deliveredGoodsKey,$allGoodsInfoIds);
                                        if (is_bool($goodsKey)) {
                                            continue ;
                                        }
                                        $afterSaleData ['univalence'] = $allGoodsInfo [$goodsKey] ['ori_price'] ;
                                        $stayAfterSalesAmount += bcmul($afterSaleData ['number'],$afterSaleData ['univalence'] );
                                        $afterSaleData ['attribute'] = $allGoodsInfo [$goodsKey]['attribute'];
                                        $afterSaleData ['product_name'] = $allGoodsInfo [$goodsKey]['product_name'];
                                        $afterSaleData ['sku'] = $allGoodsInfo [$goodsKey]['sku'];
                                        $afterSaleData ['rate'] = $currencyInfo ['rate'];
                                        $afterSaleData ['currency'] = $currencyInfo ['currency_code'];
                                        $afterSaleData ['updated_at'] = $afterSaleData ['created_at'] = $current_time;
                                        $afterSaleData ['created_man'] = 1;//默认系统管理员
                                        $afterSaleData ['after_sale_id'] = '$after_sale_id';
                                        $afterSaleDatas [] = $afterSaleData;
                                    }
                                } else {
                                    //如果已发货但未检测到商品售后信息 将直接创建
                                    $allProductInfo = OrdersRakutenProducts::getProductInfoByOrderidGoodsid($cw_erp_exist['id'],$deliveredGoodsKey);
                                    $afterSaleData ['number'] = $deliveredGoodsVal;
                                    $goodsKey = array_search($deliveredGoodsKey,$allGoodsInfoIds);
                                    if (is_bool($goodsKey)) {
                                        continue ;
                                    }
                                    $afterSaleData ['univalence'] = $allGoodsInfo [$goodsKey] ['ori_price'] ;
                                    $stayAfterSalesAmount += bcmul($afterSaleData ['number'],$afterSaleData ['univalence'] );
                                    $afterSaleData ['attribute'] = $allGoodsInfo [$goodsKey]['attribute'];
                                    $afterSaleData ['product_name'] = $allGoodsInfo [$goodsKey]['product_name'];
                                    $afterSaleData ['sku'] = $allGoodsInfo [$goodsKey]['sku'];
                                    $afterSaleData ['rate'] = $currencyInfo ['rate'];
                                    $afterSaleData ['currency'] = $currencyInfo ['currency_code'];
                                    $afterSaleData ['updated_at'] = $afterSaleData ['created_at'] = $current_time;
                                    $afterSaleData ['created_man'] = 1;//默认系统管理员
                                    $afterSaleData ['after_sale_id'] = '$after_sale_id';
                                    $afterSaleDatas [] = $afterSaleData;
                                }
                            }

                            //未发货商品退款单生成
                            $is_obsoleted = false;
                            if ($unDeliverAmount > 0) {
                                $partRefoundData ['created_man'] = $sale_info['user_id'];
                                $partRefoundData ['cw_order_id'] = $cw_erp_exist['id'];
                                $partRefoundData ['totalPrice'] = $unDeliverAmount;
                                $refundData = $this->getOrdersBill($partRefoundData,$currencyInfo,OrdersBillPayments::BILLS_REFUND,$current_time,OrdersBillPayments::ORDERS_CWERP);
                                OrdersBillPayments::postData(0,$refundData);
                                //订单完结
                                $interceptData ['updated_at'] = $current_time;
                                $interceptData ['status'] = Orders::ORDER_STATUS_OBSOLETED;
                                $interceptData ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED;
                                Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

                                //V4取消订单日志
                                self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_OBSOLETED,'接口同步订单检测平台订单已取消');
                                $is_obsoleted = true;
                            }

                            //针对部分发货状态下 已发货未及时创建售后退款单的商品创建退款单
                            if ($stayAfterSalesAmount > 0 ) {
                                $afterSalesInfo = OrdersAfterSales::creatAfterSaleBill($cw_erp_exist,$stayAfterSalesAmount,OrdersAfterSales::AFTERSALE_TYPE_REFUND,$currencyInfo);
                                if ($afterSalesInfo) {
                                    //售后单商品信息
                                    $afterSaleDatas = json_decode(str_replace('$after_sale_id',$afterSalesInfo->id,json_encode($afterSaleDatas)),true);
                                    OrdersRakutenProducts::insert($afterSaleDatas);
                                    if (empty($is_obsoleted)) {
                                        //订单完结
                                        $interceptData ['updated_at'] = $invoicesData ['updated_at'] = $current_time;
                                        $interceptData ['status'] = Orders::ORDER_STATUS_OBSOLETED;
                                        Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

                                        //V4取消订单日志
                                        self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_OBSOLETED,'接口同步订单检测平台订单已取消');
                                    }
                                }
                            }
                            //将未创建的售后退款单及时创建
                            //部分退款失败 不处理
                        }
                        continue;
                    }
                    //已发货 1.自动创建退款售后单；完结订单；
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_FILLED) {
                        //V1创建售后单
                        $afterSaleDatas = [];
                        foreach ($cw_erp_exist ['orders_invoices_value'] as $allIntercept) {
                                //需要组装售后
                                foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                    $afterSaleData ['created_man'] = 1;//默认系统管理员
                                    $afterSaleData ['after_sale_id'] = '$after_sale_id';
                                    //SKU 信息整合 防止售后单一个商品出现多次
                                    $skuArrs = [];
                                    if (!empty($afterSaleDatas)) {
                                        $skuArrs = array_column($afterSaleDatas,'sku');
                                    }
                                    if (in_array($orders_invoices_product['sku'],$skuArrs)) {
                                        $skuKey = array_search($orders_invoices_product['sku'],$skuArrs);
                                        if (is_bool($skuKey)) {
                                            $afterSaleData ['number'] = $orders_invoices_product['already_stocked_number'];
                                        } else {
                                            //累加
                                            $afterSaleData ['number'] = $afterSaleDatas [$skuKey] ['number'] + $orders_invoices_product['already_stocked_number'];
                                        }
                                    } else {
                                        $afterSaleData ['number'] = $orders_invoices_product['already_stocked_number'];
                                    }
                                    $afterSaleData ['univalence'] = $orders_invoices_product['univalence'];
                                    $afterSaleData ['attribute'] = $orders_invoices_product['attribute'];
                                    $afterSaleData ['product_name'] = $orders_invoices_product['product_name'];
                                    $afterSaleData ['sku'] = $orders_invoices_product['sku'];
                                    $afterSaleData ['rate'] = $currencyInfo ['rate'];
                                    $afterSaleData ['currency'] = $currencyInfo ['currency_code'];
                                    $afterSaleData ['updated_at'] = $afterSaleData ['created_at'] = $current_time;
                                    $afterSaleDatas [] = $afterSaleData;
                                }
                        }

                        if (!empty($afterSaleDatas)) {
                            $afterSalesInfo = OrdersAfterSales::creatAfterSaleBill($cw_erp_exist,$v['totalPrice'],OrdersAfterSales::AFTERSALE_TYPE_REFUND,$currencyInfo);
                            if ($afterSalesInfo) {
                                //售后单商品信息
                                $afterSaleDatas = json_decode(str_replace('$after_sale_id',$afterSalesInfo->id,json_encode($afterSaleDatas)),true);
                                OrdersRakutenProducts::insert($afterSaleDatas);
                            }
                        }

                        //V2 订单主表状态更新
                        $interceptData ['updated_at'] = $current_time;
                        $interceptData ['status'] = Orders::ORDER_STATUS_OBSOLETED;
                        Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

                        //V4取消订单日志
                        self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_OBSOLETED,'接口同步订单检测平台订单已取消');
                        continue;
                    }

                    //orderFixDatetime有值；订单发货表无信息
                    //是系统订单
                    //已付款  未发货 拦截订单   a.拦截成功，取消订单，作废配货单；同步退款状态；创建退款单；完结订单；  b.拦截失败，继续跑订单流程；等变为发货状态后，再走售后流程；
                    //已付款  未发货  已取消 不进行处理
                    //已付款  部分发货 针对已发货的部分，创建售后退款单    针对未发货部分：拦截订单   a.拦截成功，变跟订单状态为部分退款，作废拦截的配货单；同时创建退款退款单；完结订单；    b.拦截失败，针对继续发货的部分，继续跑订单流程；等变为发货状态后，再走售后流程。
                    //已付款  部分发货  部分退款  已完结 不进行处理
                    //已付款  部分发货  部分退款 1、不存在未发货部分  a.创建退款售后单，完结订单 2、存在未发货部分：拦截订单   a.拦截成功，变跟订单状态为部分退款，作废拦截的配货单；同时创建退款退款单；完结订单；    b.拦截失败，针对继续发货的部分，继续跑订单流程；等变为发货状态后，再走售后流程。
                    //已付款  已发货 自动创建退款售后单；完结订单
                    //已付款  已发货  已完结 已付款  已发货  已完结
                    continue;
                }

                // 800 未付款：orderFixDatetime无值 已付款：orderFixDatetime有值
                //todo
                //未发货订单不一定有配货单啊
                if($v['orderProgress'] == 800) {
                    //已发货
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_FILLED) {
                        continue;
                    }
                    //未发货
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_UNFILLED) {
                            //拦截订单
                            $allRefoundAmount = 0.00;
                            $exceptionStatus = false;
                            foreach ($cw_erp_exist ['orders_invoices_value'] as $allIntercept) {
                                $invoicesData ['updated_at'] = $current_time;
                                //发拦截指令
                                //怕怕
                                if (empty($allIntercept ['tracking_no'])) {
                                    $allInterceptRe = $ordersService->interceptOrders($allIntercept['warehouse_order_number'],'ERP接口任务乐天订单取消(900)拦截指令');
                                    if ($allInterceptRe ['is_succ'] && $allInterceptRe ['action_code'] == 200) {
                                        //拦截成功
                                        $invoicesData ['invoices_status'] = OrdersInvoices::DISABLED_INVOICES_STATUS;
                                        $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_SUCC;
                                        //从乐天订单商品表中获取对应订单商品当时价格记录
                                        foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                            //源币种
                                            $allRefoundAmount += bcmul($orders_invoices_product['already_stocked_number'], $orders_invoices_product['price']);
                                        }
                                        $exceptionStatus = false;
                                    } else {
                                        $exceptionMsg = $allInterceptRe ['msg'];
                                        //TODO
                                        //拦截失败 未发货拦截失败怎么处理
                                        $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_FAIL;
                                        //接口异常抛出
                                        if ($allInterceptRe ['action_code'] == 500 ) {
                                            $exceptionStatus = true;
                                        }
                                    }
                                    if ($exceptionStatus) {
                                        break;
                                    }
                                    OrdersInvoices::where('id',$allIntercept['id'])->update($invoicesData);
                                }
                            }
                            if ($allRefoundAmount > 0 ) {
                                //生成退款单
                                $allRefoundData ['created_man'] = $sale_info['user_id'];
                                $allRefoundData ['cw_order_id'] = $cw_erp_exist['id'];
                                $allRefoundData ['totalPrice'] = $allRefoundAmount;
                                $refundData = $this->getOrdersBill($allRefoundData,$currencyInfo,OrdersBillPayments::BILLS_REFUND,$current_time,OrdersBillPayments::ORDERS_CWERP);
                                OrdersBillPayments::postData(0,$refundData);
                            } else {
                                continue;
                            }
                            if (empty($exceptionStatus)) {
                                //订单拦截成功
                                $interceptData ['updated_at'] = $current_time;
                                $interceptData ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED;
                                Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

                                //拦截订单日志
                                self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_INTERCEPT_SUCC,'接口同步订单800检测订单未发货,订单拦截');
                            }
                    }

                    //部分发货
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_UNFILLED) {
                        $afterSaleRefund = $allRefoundAmount = 0.00;
                        foreach ($cw_erp_exist ['orders_invoices_value'] as $allIntercept) {
                            //发拦截指令
                            $invoicesData ['updated_at'] = $current_time;
                                //部分退款的只针对未发货的退款
                                if (!empty($allIntercept ['tracking_no'])) {
                                    //需要组装售后
                                    foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                        //退款金额
                                        $afterSaleRefund += bcmul($orders_invoices_product['already_stocked_number'],$orders_invoices_product['price']);
                                        $afterSaleData ['created_man'] = 1;//默认系统管理员
                                        $afterSaleData ['after_sale_id'] = '$after_sale_id';
                                        //SKU 信息整合 防止售后单一个商品出现多次
                                        $skuArrs = [];
                                        if (!empty($afterSaleDatas)) {
                                            $skuArrs = array_column($afterSaleDatas,'sku');
                                        }
                                        if (in_array($orders_invoices_product['sku'],$skuArrs)) {
                                            $skuKey = array_search($orders_invoices_product['sku'],$skuArrs);
                                            if (is_bool($skuKey)) {
                                                $afterSaleData ['number'] = $orders_invoices_product['already_stocked_number'];
                                            } else {
                                                //累加
                                                $afterSaleData ['number'] = $afterSaleDatas [$skuKey] ['number'] + $orders_invoices_product['already_stocked_number'];
                                            }
                                        } else {
                                            $afterSaleData ['number'] = $orders_invoices_product['already_stocked_number'];
                                        }
                                        $afterSaleData ['univalence'] = $allProductInfo['price'];
                                        $afterSaleData ['attribute'] = $orders_invoices_product['attribute'];
                                        $afterSaleData ['product_name'] = $orders_invoices_product['product_name'];
                                        $afterSaleData ['sku'] = $orders_invoices_product['sku'];
                                        $afterSaleData ['rate'] = $currencyInfo ['rate'];
                                        $afterSaleData ['currency'] = $currencyInfo ['currency_code'];
                                        $afterSaleData ['updated_at'] = $afterSaleData ['created_at'] = $current_time;
                                        $afterSaleDatas [] = $afterSaleData;
                                    }
                                    continue;
                                }
                            $allInterceptRe = $ordersService->interceptOrders($allIntercept['warehouse_order_number'],'ERP接口任务乐天订单取消(900)拦截指令');
                            if ($allInterceptRe ['is_succ'] && $allInterceptRe ['action_code'] == 200) {
                                //拦截成功
                                $invoicesData ['invoices_status'] = OrdersInvoices::DISABLED_INVOICES_STATUS;
                                $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_SUCC;
                                //从乐天订单商品表中获取对应订单商品当时价格记录
                                foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                    //源币种
                                    $allRefoundAmount += bcmul($orders_invoices_product['already_stocked_number'], $orders_invoices_product['price']);
                                }
                                $exceptionStatus = false;
                            } else {
                                $exceptionMsg = $allInterceptRe ['msg'];
                                //TODO
                                //拦截失败 未发货拦截失败怎么处理
                                $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_FAIL;
                                //接口异常抛出
                                if ($allInterceptRe ['action_code'] == 500 ) {
                                    $exceptionStatus = true;
                                }
                            }
                            if ($exceptionStatus) {
                                break;
                            }
                            OrdersInvoices::where('id',$allIntercept['id'])->update($invoicesData);
                        }

                        //退款售后单
                        if ($afterSaleRefund > 0 ) {
                            $afterSalesInfo = OrdersAfterSales::creatAfterSaleBill($cw_erp_exist,$afterSaleRefund,OrdersAfterSales::AFTERSALE_TYPE_REFUND,$currencyInfo);
                            if ($afterSalesInfo) {
                                //售后单商品信息
                                $afterSaleDatas = json_decode(str_replace('$after_sale_id',$afterSalesInfo->id,json_encode($afterSaleDatas)),true);
                                OrdersRakutenProducts::insert($afterSaleDatas);
                            }
                        }
                        //退款单
                        if ($allRefoundAmount > 0) {
                            $partRefoundData ['created_man'] = $sale_info['user_id'];
                            $partRefoundData ['cw_order_id'] = $cw_erp_exist['id'];
                            $partRefoundData ['totalPrice'] = $allRefoundAmount;
                            $refundData = $this->getOrdersBill($partRefoundData,$currencyInfo,OrdersBillPayments::BILLS_REFUND,$current_time,OrdersBillPayments::ORDERS_CWERP);
                            OrdersBillPayments::postData(0,$refundData);
                        } else {
                            continue;
                        }

                        //V3 订单主表状态更新
                        $interceptData ['updated_at'] = $current_time;
                        $interceptData ['sales_status'] = Orders::ORDER_SALES_STATUS_APPLYED;
                        $interceptData ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED;
                        $interceptData ['status'] = Orders::ORDER_STATUS_OBSOLETED;
                        Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

                        //V4取消订单日志
                        self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_INTERCEPT_SUCC,'接口同步订单检测订单状态800部分发货拦截订单');
                    }
                }
                // 已付款
                if($v['orderProgress'] == 300){
                    //订单金额<第一次订单金额 生成退款单 未完成 不知道具体什么场景
                    //未发货
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_UNFILLED) {
                        if ($orig_rakuten_info ['totalPrice'] > $v ['totalPrice']) {
                            //未退款
                            if ($cw_erp_exist ['sales_status'] == Orders::ORDER_SALES_STATUS_INITIAL) {
                                //拦截订单
                                $allRefoundAmount = 0.00;
                                $exceptionStatus = false;
                                foreach ($cw_erp_exist ['orders_invoices_value'] as $allIntercept) {
                                    $invoicesData ['updated_at'] = $current_time;
                                    //发拦截指令
                                    //怕怕
                                    if (empty($allIntercept ['tracking_no'])) {
                                        $allInterceptRe = $ordersService->interceptOrders($allIntercept['warehouse_order_number'],'ERP接口任务乐天订单取消(900)拦截指令');
                                        if ($allInterceptRe ['is_succ'] && $allInterceptRe ['action_code'] == 200) {
                                            //拦截成功
                                            $invoicesData ['invoices_status'] = OrdersInvoices::DISABLED_INVOICES_STATUS;
                                            $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_SUCC;
                                            //从乐天订单商品表中获取对应订单商品当时价格记录
                                            foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                                                //源币种
                                                $allRefoundAmount += bcmul($orders_invoices_product['already_stocked_number'], $orders_invoices_product['price']);
                                            }
                                            $exceptionStatus = false;
                                        } else {
                                            $exceptionMsg = $allInterceptRe ['msg'];
                                            //TODO
                                            //拦截失败 未发货拦截失败怎么处理
                                            $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_FAIL;
                                            //接口异常抛出
                                            if ($allInterceptRe ['action_code'] == 500 ) {
                                                $exceptionStatus = true;
                                            }
                                        }
                                        if ($exceptionStatus) {
                                            break;
                                        }
                                        OrdersInvoices::where('id',$allIntercept['id'])->update($invoicesData);
                                    }
                                }
                                if ($allRefoundAmount > 0 ) {
                                    //生成退款单
                                    $allRefoundData ['created_man'] = $sale_info['user_id'];
                                    $allRefoundData ['cw_order_id'] = $cw_erp_exist['id'];
                                    $allRefoundData ['totalPrice'] = $allRefoundAmount;
                                    $refundData = $this->getOrdersBill($allRefoundData,$currencyInfo,OrdersBillPayments::BILLS_REFUND,$current_time,OrdersBillPayments::ORDERS_CWERP);
                                    OrdersBillPayments::postData(0,$refundData);
                                } else {
                                    continue;
                                }
                                if (empty($exceptionStatus)) {
                                    //订单拦截成功
                                    $interceptData ['updated_at'] = $current_time;
                                    $interceptData ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED;
                                    Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

                                    //拦截订单日志
                                    self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_INTERCEPT_SUCC,'接口同步订单检测平台订单价格小于原始价格,订单拦截');
                                }
                            }
                            //已申请部分退款 退款商品信息一致，不进行处理；   退款商品信息不一致，拦截订单：
                            //  a.拦截成功：自动创建退款单；
                            //  b.拦截失败：继续跑订单流程；等变为发货状态后，再走售后流程；
                            if ($cw_erp_exist ['sales_status'] == Orders::ORDER_SALES_STATUS_APPLYED) {
                                //这个情况 做不下去啊
                            }


                        }
                        continue;
                    }
                        //部分发货 || 已发货
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_FILLED || $cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_FILLED_PART) {
                        if ($orig_rakuten_info ['totalPrice'] == $v ['totalPrice']) {
                            //回传物流跟踪号
                            $listRakuten = new RakutenService($sale_info);
                            //物流跟踪号回传
                            //订单号
                            $shippingData ['orderNumber'] = $order_rakuten_source;
                            //送货明细ID
                            $shippingData ['BasketidModelList'] [0]['basketId']= 666;
                            //物流送货公司
                            $shippingData ['BasketidModelList'] [0] ['ShippingModelList'] [0]['deliveryCompany']= 666;
                            //物流单号
                            $shippingData ['BasketidModelList'][0] ['ShippingModelList'] [0]['shippingNumber']= 666;
                            //配送日期
                            $shippingData ['BasketidModelList'] [0]['ShippingModelList'][0] ['shippingDate']= 666;
                            $listRakuten->updateOrderShipping(json_encode($shippingData));
                        }
                    }
                }
                //已付款 已发货
                if($v['orderProgress'] == 500){
                    if ($cw_erp_exist ['deliver_status'] == Orders::ORDER_DELIVER_STATUS_FILLED) {
                        continue;
                    }
                    //订单金额<第一次订单金额
                }





            }
        }

//        $len = ceil(count($this->temp_ori_order)/500 ) ;
//        $tempArray = [];
//        $i = 0;
//        for($i;$i < $len; $i++){
//            $tempArray = array_slice($this->temp_ori_order,500*$i, 500);
//            self::insert($tempArray);
//        }
    }

    /**
     * @param $orderProgress
     * @return string
     * Note: 订单状态映射
     * Data: 2019/4/9 16:03
     * Author: zt7785
     */
    public function getOrderStatus($orderProgress){
        switch ($orderProgress){
            case 100 :
                $status = '注文確認待ち';
                break;
            case 200 :
                $status = '楽天処理中';
                break;
            case 300 :
                $status = '発送待ち';
                break;
            case 400 :
                $status = '変更確定待ち';
                break;
            case 500 :
                $status = '発送済';
                break;
            case 600 :
                $status = '支払手続き中';
                break;
            case 700 :
                $status = '支払手続き済';
                break;
            case 800 :
                $status = 'キャンセル確定待ち';
                break;
            case 900 :
                $status = 'キャンセル確定';
                break;
            default :
                $status = '';
        }
        return $status;
    }



    public static function getRakutenOrdersInfoByCode($orderCode,$userId)
    {
        $result = self::where('orderNumber',$orderCode)->where('created_man',$userId)->first(['id']);
        return $result;
    }

    /**
     * @param $data
     * @param $currency_code
     * @param $rate
     * @return mixed
     * Note: 生成付款单|退款单
     * Data: 2019/4/11 11:05
     * Author: zt7785
     */
    public function getOrdersBill ($data,$currencyInfo,$type,$current_time,$order_type) {
        $bill ['created_man'] = $data['created_man'];
        $bill ['order_id'] = $data['cw_order_id'];//平台订单id
        $bill ['order_type'] = $order_type;//平台订单
        $bill ['status'] = OrdersBillPayments::BILLS_STATUS_INIT;
//        $bill ['type'] = OrdersBillPayments::BILLS_PAY;
        $bill ['type'] = $type;
        $bill ['amount'] = $data['totalPrice'];
        $bill ['rate'] = $currencyInfo['rate'];
        $bill ['currency_code'] = $currencyInfo['currency_code'];
        $bill ['created_at'] = $bill ['updated_at'] = $current_time;
        return $bill;
    }

    /**
     * @param $cw_erp_exist
     * @param $sale_info
     * @param $refoundAmount
     * @param $current_time
     * @param $currencyInfo
     * @param bool $is_part
     * Note: 取消订单逻辑
     * 1.生成退款单
     * 2.订单主表更新
     * 3.订单日志写入
     * Data: 2019/4/11 19:35
     * Author: zt7785
     */
    public function cancelOrder ($cw_erp_exist,$sale_info,$current_time,$currencyInfo,$ordersService,$totalPrice = 0.00,$is_part = false) {
        //V1 作废配货单作废
        $allRefoundAmount = 0.00;
        $exceptionStatus = false;
        $afterSaleRefund = 0.00;
        $afterSaleDatas = [];
        foreach ($cw_erp_exist ['orders_invoices_value'] as $allIntercept) {
            //发拦截指令
            $invoicesData ['updated_at'] = $current_time;
            if ($is_part) {
                //部分退款的只针对未发货的退款
                if (!empty($allIntercept ['tracking_no'])) {
                    //需要组装售后
                    foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                        $allProductInfo = OrdersRakutenProducts::getProductInfoByOrderidGoodsid($cw_erp_exist['id'],$orders_invoices_product['goods_id']);
                        //退款金额
                        $afterSaleRefund += bcmul($orders_invoices_product['already_stocked_number'],$allProductInfo['price']);
                        $afterSaleData ['created_man'] = 1;//默认系统管理员
                        $afterSaleData ['after_sale_id'] = '$after_sale_id';
                        //SKU 信息整合 防止售后单一个商品出现多次
                        $skuArrs = [];
                        if (!empty($afterSaleDatas)) {
                            $skuArrs = array_column($afterSaleDatas,'sku');
                        }
                        if (in_array($orders_invoices_product['sku'],$skuArrs)) {
                            $skuKey = array_search($orders_invoices_product['sku'],$skuArrs);
                            if (is_bool($skuKey)) {
                                $afterSaleData ['number'] = $orders_invoices_product['already_stocked_number'];
                            } else {
                                //累加
                                $afterSaleData ['number'] = $afterSaleDatas [$skuKey] ['number'] + $orders_invoices_product['already_stocked_number'];
                            }
                        } else {
                            $afterSaleData ['number'] = $orders_invoices_product['already_stocked_number'];
                        }
                        $afterSaleData ['univalence'] = $allProductInfo['price'];
                        $afterSaleData ['attribute'] = $orders_invoices_product['attribute'];
                        $afterSaleData ['product_name'] = $orders_invoices_product['product_name'];
                        $afterSaleData ['sku'] = $orders_invoices_product['sku'];
                        $afterSaleData ['rate'] = $currencyInfo ['rate'];
                        $afterSaleData ['currency'] = $currencyInfo ['currency_code'];
                        $afterSaleData ['updated_at'] = $afterSaleData ['created_at'] = $current_time;
                        $afterSaleDatas [] = $afterSaleData;
                    }
                }
                    continue;
                }
            $allInterceptRe = $ordersService->interceptOrders($allIntercept['warehouse_order_number'],'ERP接口任务乐天订单取消(900)拦截指令');
            if ($allInterceptRe ['is_succ'] && $allInterceptRe ['action_code'] == 200) {
                //拦截成功
                $invoicesData ['invoices_status'] = OrdersInvoices::DISABLED_INVOICES_STATUS;
                $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_SUCC;
                //从乐天订单商品表中获取对应订单商品当时价格记录
                foreach ($allIntercept ['orders_invoices_product'] as $orders_invoices_product) {
                    $allProductInfo = OrdersRakutenProducts::getProductInfoByOrderidGoodsid($cw_erp_exist['id'], $orders_invoices_product['goods_id']);
                    //源币种
                    $allRefoundAmount += bcmul($orders_invoices_product['already_stocked_number'], $allProductInfo['price']);
                }
                $exceptionStatus = false;
            } else {
                $exceptionMsg = $allInterceptRe ['msg'];
                //TODO
                //拦截失败 未发货拦截失败怎么处理
                $invoicesData ['intercept_status'] = OrdersInvoices::INTERCEPT_FAIL;
                //接口异常抛出
                if ($allInterceptRe ['action_code'] == 500 ) {
                    $exceptionStatus = true;
                }
            }
            if ($exceptionStatus) {
                break;
            }
            OrdersInvoices::where('id',$allIntercept['id'])->update($invoicesData);
        }
        //创建售后类型的退款单
        if ($is_part) {
            if ($afterSaleRefund > 0 ) {
                $afterSalesInfo = OrdersAfterSales::creatAfterSaleBill($cw_erp_exist,$afterSaleRefund,OrdersAfterSales::AFTERSALE_TYPE_REFUND,$currencyInfo);
                if ($afterSalesInfo) {
                    //售后单商品信息
                    $afterSaleDatas = json_decode(str_replace('$after_sale_id',$afterSalesInfo->id,json_encode($afterSaleDatas)),true);
                    OrdersRakutenProducts::insert($afterSaleDatas);
                }
            }
        }

        $refoundAmount = $allRefoundAmount;
        if (empty($is_part)) {
            if (empty($exceptionStatus)) {
                $refoundAmount = $totalPrice;
            } else {
                $is_part = true;
            }
        }
        //金额异常
        if (!($refoundAmount > 0 )) {
            return;
        }

        //V2 创建退款单
        $partRefoundData ['created_man'] = $sale_info['user_id'];
        $partRefoundData ['cw_order_id'] = $cw_erp_exist['id'];
        $partRefoundData ['totalPrice'] = $refoundAmount;
        $refundData = $this->getOrdersBill($partRefoundData,$currencyInfo,OrdersBillPayments::BILLS_REFUND,$current_time,OrdersBillPayments::ORDERS_CWERP);
        OrdersBillPayments::postData(0,$refundData);

        //V3 订单主表状态更新
        $interceptData ['updated_at'] = $current_time;
        if ($is_part) {
            $interceptData ['sales_status'] = Orders::ORDER_SALES_STATUS_APPLYED;
        }
        $interceptData ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INTERCEPTED;
        $interceptData ['status'] = Orders::ORDER_STATUS_OBSOLETED;
        Orders::where('id',$cw_erp_exist['id'])->update($interceptData);

        //V4取消订单日志
        self::orderTsakLogs($cw_erp_exist,$current_time,OrdersLogs::LOGS_ORDERS_OBSOLETED,'接口同步订单检测平台订单已取消');
    }

    /**
     * @param $cw_erp_exist
     * @param $current_time
     * @param $type
     * @param $msg
     * Note: 订单日志
     * Data: 2019/4/12 9:14
     * Author: zt7785
     */
    public static function orderTsakLogs ($cw_erp_exist,$current_time,$type,$msg)
    {
        $orderLogsData ['created_man'] = 1;//默认系统管理员
        $orderLogsData ['order_id'] = $cw_erp_exist['id'];
        $orderLogsData ['behavior_types'] = $type;
        $orderLogsData ['behavior_desc'] = $msg;
        $orderLogsData ['behavior_type_desc'] = OrdersLogs::ORDERS_LOGS_TYPE_DESC[$orderLogsData ['behavior_types']];
        $orderLogsData ['updated_at'] = $orderLogsData ['created_at'] = $current_time;
        OrdersLogs::postDatas(0,$orderLogsData);
    }

    /**
     * @param $sale_info 销售信息
     * @param $v 订单信息
     * @param $current_time 当前时间
     * @param $order_rakuten_source
     * @param $shopIds
     * @param $shopInfo
     * @param $currencyInfo 汇率币种信息
     * @param $country_id
     * @param $country
     * @param $userLogisticsCodes
     * @param $userLogisticsInfo
     * Note: 订单同步写入逻辑
     * Data: 2019/4/16 9:11
     * Author: zt7785
     */
    public function postNewOrderInfo($sale_info ,$v ,$current_time ,$order_rakuten_source ,$shopIds ,$shopInfo ,$currencyInfo ,$country_id ,$country ,$userLogisticsCodes ,$userLogisticsInfo )
    {
        $match_status = false;
        //写数据逻辑
        //先临时表处理数据 然后统一写入
        $temp_ori_order = array();
        //OrderModel
        $temp_ori_order['created_man'] = $sale_info['user_id'];
        $temp_ori_order['source_shop'] = $sale_info['id'];//店铺id
        //初始化赋值
        $temp_ori_order['cw_order_id'] = 0;//关联平台订单id
        $temp_ori_order['is_system'] = OrdersRakuten::UN_SYSTEM_ORDER;//默认非平台订单
        $temp_ori_order['cw_code'] = CodeInfo::getACode(CodeInfo::CW_ORDERS_CODE);//平台订单号
        $temp_ori_order['match_status'] = OrdersRakuten::RAKUTEN_MAPPING_STATUS_UNFINISH;//未匹配
        $temp_ori_order['asurakuFlg'] = '0';//SKU标识
        $temp_ori_order['cardStatus'] = ''; //卡包结算状态
        $temp_ori_order['enclosureDeliveryPrice'] = '';//附件费用
        $temp_ori_order['enclosureId'] = '';//附件ID
        $temp_ori_order['enclosureStatus'] = '';//附件状态
        $temp_ori_order['firstAmount'] = '';//首重金额

        $temp_ori_order['orderNumber'] = $v['orderNumber'];//乐天平台订单号
        $temp_ori_order['carrierCode'] = $v['carrierCode'];//卡车号码
        $temp_ori_order['status'] = $this->getOrderStatus($v['orderProgress']);//订单状态
        $temp_ori_order['deliveryClass'] = $v['DeliveryModel']['deliveryClass'];//物流级别
        $temp_ori_order['deliveryName'] = $v['DeliveryModel']['deliveryName'];//物流名称
        $temp_ori_order['deliveryPrice'] = $v['deliveryPrice'];//运费
        $temp_ori_order['emailCarrierCode'] = $v['emailCarrierCode'];//电子邮件运营商代码
        $temp_ori_order['goodsPrice'] = $v['goodsPrice'];//'商品金额
        $temp_ori_order['goodsTax'] = $v['goodsTax'];//商品税费
        $temp_ori_order['isBlackUser'] = '';//是否黑名单
        $temp_ori_order['isGift'] = '';//是否是礼品
        $temp_ori_order['isGiftCheck'] = $v['giftCheckFlag'];//是否是礼品检查
        $temp_ori_order['isRakutenMember'] = $v['rakutenMemberFlag'];//是否是会员
        $temp_ori_order['modify'] = $v['modifyFlag'];//是否修改
        $temp_ori_order['option'] = $v['remarks'];//其它信息
        $temp_ori_order['orderDate'] = $v['orderDatetime'];//下单时间
        //TODO
        //待确定
        $temp_ori_order['orderPayDate'] = $v['orderDatetime'];//付款时间
        $temp_ori_order['orderType'] = $v['orderType'];//订单类型
        $temp_ori_order['shippingTerm'] = $v['shippingTerm'];//发货期限
        $temp_ori_order['totalPrice'] = $v['totalPrice'];//总价格
        $temp_ori_order['memo'] = $v['memo'];//备注
        $temp_ori_order['postagePrice'] = $v['postagePrice'];//邮费
        $temp_ori_order['requestPrice'] = $v['requestPrice'];//要求的价格

        //OrdererModel 购物者信息模型
        $temp_ori_order['buyer_sex'] = $v['OrdererModel']['sex'];
        $temp_ori_order['buyer_birthDay'] = $v['OrdererModel']['birthDay'];
        $temp_ori_order['buyer_birthMonth'] = $v['OrdererModel']['birthMonth'];
        $temp_ori_order['buyer_birthYear'] = $v['OrdererModel']['birthYear'];
        $temp_ori_order['buyer_city'] = $v['OrdererModel']['city'];
        $temp_ori_order['buyer_emailAddress'] = $v['OrdererModel']['emailAddress'];
        $temp_ori_order['buyer_familyName'] = $v['OrdererModel']['familyName'];
        $temp_ori_order['buyer_familyNameKana'] = $v['OrdererModel']['familyNameKana'];
        $temp_ori_order['buyer_firstName'] = $v['OrdererModel']['firstName'];
        $temp_ori_order['buyer_firstNameKana'] = $v['OrdererModel']['firstNameKana'];
        $temp_ori_order['buyer_phoneNumber1'] = $v['OrdererModel']['phoneNumber1'];
        $temp_ori_order['buyer_phoneNumber2'] = $v['OrdererModel']['phoneNumber2'];
        $temp_ori_order['buyer_phoneNumber3'] = $v['OrdererModel']['phoneNumber3'];
        $temp_ori_order['buyer_prefecture'] = $v['OrdererModel']['prefecture'];
        $temp_ori_order['buyer_subAddress'] = $v['OrdererModel']['subAddress'];
        $temp_ori_order['buyer_zipCode1'] = $v['OrdererModel']['zipCode1'];
        $temp_ori_order['buyer_zipCode2'] = $v['OrdererModel']['zipCode2'];

        //PackageModelList 收件人模型列表
        $temp_ori_order['package_basketId'] = $v['PackageModelList'][0]['basketId'];
        $temp_ori_order['package_deliveryCompanyId'] = '';
        $temp_ori_order['package_deliveryPrice'] = $v['PackageModelList'][0]['deliveryPrice'];
        $temp_ori_order['package_goodsPrice'] = $v['PackageModelList'][0]['goodsPrice'];
        $temp_ori_order['package_goodsTax'] = $v['PackageModelList'][0]['goodsTax'];
        $temp_ori_order['package_postagePrice'] = $v['PackageModelList'][0]['postagePrice'];

        //senderModel 发件人信息模型
        $temp_ori_order['send_city'] = $v['PackageModelList'][0]['SenderModel']['city'];
        $temp_ori_order['send_familyName'] = $v['PackageModelList'][0]['SenderModel']['familyName'];
        $temp_ori_order['send_familyNameKana'] = $v['PackageModelList'][0]['SenderModel']['familyNameKana'];
        $temp_ori_order['send_firstName'] = $v['PackageModelList'][0]['SenderModel']['firstName'];
        $temp_ori_order['send_firstNameKana'] = $v['PackageModelList'][0]['SenderModel']['familyNameKana'];
        $temp_ori_order['send_phoneNumber1'] = $v['PackageModelList'][0]['SenderModel']['phoneNumber1'];
        $temp_ori_order['send_phoneNumber2'] = $v['PackageModelList'][0]['SenderModel']['phoneNumber2'];
        $temp_ori_order['send_phoneNumber3'] = $v['PackageModelList'][0]['SenderModel']['phoneNumber3'];
        $temp_ori_order['send_prefecture'] = $v['PackageModelList'][0]['SenderModel']['prefecture'];
        $temp_ori_order['send_subAddress'] = $v['PackageModelList'][0]['SenderModel']['subAddress'];
        $temp_ori_order['send_zipCode1'] = $v['PackageModelList'][0]['SenderModel']['zipCode1'];
        $temp_ori_order['send_zipCode2'] = $v['PackageModelList'][0]['SenderModel']['zipCode2'];
        //pointModel 积分点模型
        $temp_ori_order['pointUsage'] = '';
        $temp_ori_order['point_status'] = '';
        $temp_ori_order['usedPoint'] = $v['PointModel']['usedPoint'];
        //settlementModel 付款方式模型
        $temp_ori_order['pay_settlementName'] = $this->settlementMethodNameArr[$v['SettlementModel']['settlementMethod']];
        $temp_ori_order['pay_brandName'] = $v['SettlementModel']['cardName'];
        $temp_ori_order['pay_cardNo'] = $v['SettlementModel']['cardNumber'];
        $temp_ori_order['pay_expYM'] = $v['SettlementModel']['cardYm'];
        $temp_ori_order['pay_ownerName'] = $v['SettlementModel']['cardOwner'];
        $temp_ori_order['pay_payType'] = $v['SettlementModel']['cardPayType'];
        $temp_ori_order['created_at'] = $temp_ori_order['updated_at'] = $current_time;
//        if (empty($orig_id)) {
        $temp_order_id = self::insertGetId($temp_ori_order);
//        } else {
//            OrdersRakuten::postDatas($orig_id,$temp_ori_order);
//            $temp_order_id = $orig_id;
//        }
        //                $this->temp_ori_order [] =  $temp_ori_order;
        //原始订单生成付款单
        $orig_payment_data ['created_man'] = $sale_info['user_id'];
        $orig_payment_data ['cw_order_id'] = $temp_order_id;
        $orig_payment_data ['totalPrice'] = $temp_ori_order ['totalPrice'];
        $orig_payment_datas = $this->getOrdersBill($orig_payment_data,$currencyInfo,OrdersBillPayments::BILLS_PAY,$current_time,OrdersBillPayments::ORDERS_ORIG_RAKUTEN);
        $orig_payment_datas ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$order_rakuten_source,'1');
        OrdersBillPayments::postData(0,$orig_payment_datas);
        //商品数据
        $order_product_info = array();

        //订单商品全部有映射关系才算匹配成功
        $skus = array_column($v['PackageModelList'][0]['ItemModelList'], 'itemNumber');
        $mappingInfo = GoodsMapping::getMappingInfoByUseridSku($sale_info['user_id'], $skus, Platforms::RAKUTEN);
        $mappingInfoItemURL = array_column($mappingInfo, 'itemURL');
        if (empty($mappingInfo)) {
            $match_status = false;
        }
        if (!empty($mappingInfo) && (count($skus) == count($mappingInfo))) {
            $match_status = true;
        }

        $erpOrderInfo = [];
        $orig_rakuten_data = [];
        //匹配成功 将生成平台订单
        if ($match_status) {
            $buyer_phone = $temp_ori_order['buyer_phoneNumber1'] . '-' . $temp_ori_order['buyer_phoneNumber2'] . '-' . $temp_ori_order['buyer_phoneNumber3'];
            $consignee_name = $temp_ori_order['send_familyName'] . ' ' . $temp_ori_order['send_firstName'] . '[' . $temp_ori_order['send_familyNameKana'] . ' ' . $temp_ori_order['send_firstNameKana'] . ']';
            $consignee_phone = $temp_ori_order['send_phoneNumber1'] . '-' . $temp_ori_order['send_phoneNumber2'] . '-' . $temp_ori_order['send_phoneNumber3'];
            $consignee_zipcode = $temp_ori_order['send_zipCode1'] . '-' . $temp_ori_order['send_zipCode2'];
            //写订单表
            $erpOrderInfo ['created_man'] = $sale_info['user_id'];
            $erpOrderInfo ['user_id'] = $sale_info['user_id'];
            $erpOrderInfo ['platforms_id'] = Platforms::RAKUTEN;
            $erpOrderInfo ['source_shop'] = $sale_info['id'];
            $erpOrderInfo ['order_number'] = $temp_ori_order['cw_code'];
            $erpOrderInfo ['plat_order_number'] = $order_rakuten_source;
            $erpOrderInfo ['type'] = Orders::ORDERS_GETINFO_API;
            $erpOrderInfo ['platform_name'] = '乐天';
            //店铺名称
            $shopKey = array_search($sale_info['id'], $shopIds);
            $erpOrderInfo ['source_shop_name'] = $shopInfo[$shopKey]['shop_name'];
            //默认状态
            $erpOrderInfo ['picking_status'] = Orders::ORDER_PICKING_STATUS_UNMATCH;
            $erpOrderInfo ['deliver_status'] = Orders::ORDER_DELIVER_STATUS_UNFILLED;
            $erpOrderInfo ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INITIAL;
            $erpOrderInfo ['sales_status'] = Orders::ORDER_SALES_STATUS_INITIAL;
            $erpOrderInfo ['status'] = Orders::ORDER_STATUS_UNFINISH;
            $erpOrderInfo ['order_price'] = $temp_ori_order['totalPrice'];
            $erpOrderInfo ['currency_code'] = $erpOrderInfo['currency_freight'] = $currencyInfo['currency_code'];
            $erpOrderInfo ['rate'] = $currencyInfo ['rate'];
            $erpOrderInfo ['payment_method'] = $temp_ori_order['pay_settlementName'];
            $erpOrderInfo ['freight'] = $temp_ori_order['deliveryPrice'];
            $erpOrderInfo ['postal_code'] = $consignee_zipcode;
            $erpOrderInfo ['country_id'] = $country_id;
            $erpOrderInfo ['country'] = $country;
            $erpOrderInfo ['province'] = $temp_ori_order['send_prefecture'];
            $erpOrderInfo ['city'] = $temp_ori_order['send_city'];
            $erpOrderInfo ['mobile_phone'] = $buyer_phone;
            $erpOrderInfo ['phone'] = $consignee_phone;
            $erpOrderInfo ['addressee_name'] = $consignee_name;
            $erpOrderInfo ['addressee_email'] = $temp_ori_order['buyer_emailAddress'];
            //仓库
            $erpOrderInfo ['warehouse_id'] = '';
            $erpOrderInfo ['warehouse'] = '';
            $erpOrderInfo ['logistics'] = $temp_ori_order['deliveryName'];
            $userLogisticsKey = array_search($erpOrderInfo ['logistics'], $userLogisticsCodes);
            if (is_bool($userLogisticsKey)) {
                $erpOrderInfo ['logistics_id'] = '';
            } else {
                $erpOrderInfo ['logistics_id'] = $userLogisticsInfo[$userLogisticsKey]['id'];
            }

            $erpOrderInfo ['addressee'] = $temp_ori_order['send_subAddress'];
            $erpOrderInfo ['addressee1'] = $erpOrderInfo ['addressee2'] = '';
            $erpOrderInfo ['order_time'] = $erpOrderInfo ['payment_time'] = $temp_ori_order['orderDate'];
            $erpOrderInfo ['created_at'] = $erpOrderInfo ['updated_at'] = $current_time;
            //订单id
            $erpOrderId = Orders::insertGetId($erpOrderInfo);

            //针对原始订单之前的退款单 付款单 写一份平台订单数据

            //乐天平台的指定id的 退款 付款单据 待优化
            $billOptions ['order_id'] = $erpOrderId;
            $billOptions ['order_type'] = OrdersBillPayments::ORDERS_ORIG_RAKUTEN;
            $billsDatas = OrdersBillPayments::getBillsByOptionss($billOptions);
            foreach ($billsDatas as $billsData) {
                unset($billsData ['id']);
                $billsData ['order_id'] = $erpOrderId;
                $billsData ['order_type'] = OrdersBillPayments::ORDERS_CWERP;
                if ($billsData ['type'] == OrdersBillPayments::BILLS_PAY) {
                    //目前一个订单只有一个付款单
                    $billsData ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$erpOrderInfo ['order_number'],'1');
                } else {
                    $billsData ['bill_code'] = '';
                }
                OrdersBillPayments::postData(0,$billsData);
            }

            //原始订单关联
            $orig_rakuten_data ['cw_order_id'] = $erpOrderId;
            $orig_rakuten_data ['is_system'] = OrdersRakuten::IS_SYSTEM_ORDER;//是系统订单
            $orig_rakuten_data ['match_status'] = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FINISHED;//匹配成功
            $orig_rakuten_data ['updated_at'] = $current_time;
            OrdersRakuten::where('id',$temp_order_id)->update($orig_rakuten_data);

            //订单量逻辑
            $record_time = strtotime(date('Y-m-d'));
            OrdersQuantityRecord::orderQuantityLogics($sale_info,$record_time);
        } else {
            if ($temp_ori_order ['is_system'] == OrdersRakuten::UN_SYSTEM_ORDER && $temp_ori_order ['match_status'] == OrdersRakuten::RAKUTEN_MAPPING_STATUS_UNFINISH) {
                $orig_rakuten_data ['is_system'] = OrdersRakuten::UN_SYSTEM_ORDER;//是系统订单
                $orig_rakuten_data ['match_status'] = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FAIL;//匹配成功
                $orig_rakuten_data ['updated_at'] = $current_time;
                OrdersRakuten::where('id',$temp_order_id)->update($orig_rakuten_data);
            }
        }
        //写订单商品数据
        foreach ($v['PackageModelList'][0]['ItemModelList'] as $key => $rakuten_product) {
            $two_sku = "";
            $result = array();
            preg_match_all("/(?<=\()[^()]\d*(?=\))/", $rakuten_product['selectedChoice'], $result);
            if (count($result[0]) === 1) {
                $two_sku = $result[0][0];
            }
            $rakuten_products = array();

            $rakuten_products['basketId'] = $v['PackageModelList'][0]['basketId'];//目的地ID
            $rakuten_products['isIncludedCashOnDeliveryPostage'] = $rakuten_product['includeCashOnDeliveryPostageFlag'];//包括COD费用 0：货到付款 1：包含货到付款
            $rakuten_products['isIncludedPostage'] = $rakuten_product['includePostageFlag']; //：0运费  ：包括运费或免运费
            $rakuten_products['isIncludedTax'] = $rakuten_product['includeTaxFlag'];//：0不含税 1：含税
            $rakuten_products['itemId'] = $rakuten_product['itemId'];//产品编号
            $rakuten_products['itemName'] = $rakuten_product['itemName'];//产品名称
            $rakuten_products['itemNumber'] = $rakuten_product['itemNumber'];//物品编号 项目编号（商店注册的编号）+项目选项ID（横轴）+项目选项ID（垂直轴）
            $rakuten_products['Item_delvdateInfo'] = $rakuten_product['delvdateInfo'];//交货时间信息
            $rakuten_products['Item_inventoryType'] = $rakuten_product['inventoryType'];//0：无库存设置 1：正常库存设置 2：项目选择库存设置
            $rakuten_products['pageURL'] = '';
            $rakuten_products['pointRate'] = $rakuten_product['pointRate'];//点率
            $rakuten_products['pointType'] = '';
            $rakuten_products['price'] = $rakuten_product['price'];//价格
            $rakuten_products['selectedChoice'] = $rakuten_product['selectedChoice'];//1：正常购买3：联合购买4：常规购买5：分发会议6：预留产品
            $rakuten_products['units'] = $rakuten_product['units'];
            $rakuten_products['rakuten_order_id'] = $temp_order_id;
            $rakuten_products['user_id'] = $sale_info['user_id'];
            $rakuten_products['created_at'] = $rakuten_products['updated_at'] = $current_time;
            OrdersRakutenProducts::insert($rakuten_products);
            //匹配成功之后 应用该数据
            if ($match_status) {
                $order_product_info[$key]['created_man'] = $sale_info['user_id'];
                $order_product_info[$key]['order_id'] = $erpOrderId;
                $mappingKey =  array_search($rakuten_products['itemNumber'], $mappingInfoItemURL);
                $goods_id = GoodsMappingGoods::getGoodsIdByMappingid($mappingInfo[$mappingKey]['id']);
                $order_product_info[$key]['goods_id'] = $goods_id['goods_id'];
                $order_product_info[$key]['order_type'] = OrdersProducts::ORDERS_CWERP;
                $order_product_info[$key]['product_name'] = $rakuten_products['itemName'];
                $order_product_info[$key]['sku'] = ($two_sku) ? $two_sku : $rakuten_products['itemNumber'];
                $order_product_info[$key]['currency'] = $currencyInfo ['currency_code'];
                $order_product_info[$key]['number'] = $rakuten_products['units'];
                $order_product_info[$key]['univalence'] = $rakuten_products['price'];
                $order_product_info[$key]['rate'] = $currencyInfo ['rate'];
                $order_product_info[$key]['RMB'] = bcmul(bcmul($currencyInfo ['rate'], $rakuten_products['price']), $rakuten_products['units']);
                $order_product_info[$key]['created_at'] = $order_product_info[$key]['updated_at'] = $current_time;
                OrdersProducts::insert($order_product_info);
            }
        }
    }

    /**
     * @param $sale_info
     * @param $v
     * @param $current_time
     * @param $order_rakuten_source
     * @param $shopIds
     * @param $shopInfo
     * @param $currencyInfo
     * @param $country_id
     * @param $country
     * @param $userLogisticsCodes
     * @param $userLogisticsInfo
     * Note: 写orders_original 原始订单表
     * Data: 2019/5/9 15:23
     * Author: zt7785
     */
    public function postNewOrderInfos($sale_info ,$v ,$current_time ,$order_rakuten_source ,$shopIds ,$shopInfo ,$currencyInfo ,$country_id ,$country ,$userLogisticsCodes ,$userLogisticsInfo )
    {
        $match_status = false;
        //写数据逻辑
        //先临时表处理数据 然后统一写入
        $temp_ori_order = array();
        //OrderModel
        $temp_ori_order['created_man'] = $sale_info['user_id'];
        $temp_ori_order['source_shop'] = $sale_info['id'];//店铺id
        //初始化赋值
        $temp_ori_order['cw_order_id'] = 0;//关联平台订单id
        $temp_ori_order['is_system'] = OrdersRakuten::UN_SYSTEM_ORDER;//默认非平台订单
        $temp_ori_order['cw_code'] = CodeInfo::getACode(CodeInfo::CW_ORDERS_CODE);//平台订单号
        $temp_ori_order['match_status'] = OrdersRakuten::RAKUTEN_MAPPING_STATUS_UNFINISH;//未匹配
        $temp_ori_order['asurakuFlg'] = '0';//SKU标识
        $temp_ori_order['cardStatus'] = ''; //卡包结算状态
        $temp_ori_order['enclosureDeliveryPrice'] = '';//附件费用
        $temp_ori_order['enclosureId'] = '';//附件ID
        $temp_ori_order['enclosureStatus'] = '';//附件状态
        $temp_ori_order['firstAmount'] = '';//首重金额

        $temp_ori_order['orderNumber'] = $v['orderNumber'];//乐天平台订单号
        $temp_ori_order['carrierCode'] = $v['carrierCode'];//卡车号码
        $temp_ori_order['status'] = $this->getOrderStatus($v['orderProgress']);//订单状态
        $temp_ori_order['deliveryClass'] = $v['DeliveryModel']['deliveryClass'];//物流级别
        $temp_ori_order['deliveryName'] = $v['DeliveryModel']['deliveryName'];//物流名称
        $temp_ori_order['deliveryPrice'] = $v['deliveryPrice'];//运费
        $temp_ori_order['emailCarrierCode'] = $v['emailCarrierCode'];//电子邮件运营商代码
        $temp_ori_order['goodsPrice'] = $v['goodsPrice'];//'商品金额
        $temp_ori_order['goodsTax'] = $v['goodsTax'];//商品税费
        $temp_ori_order['isBlackUser'] = '';//是否黑名单
        $temp_ori_order['isGift'] = '';//是否是礼品
        $temp_ori_order['isGiftCheck'] = $v['giftCheckFlag'];//是否是礼品检查
        $temp_ori_order['isRakutenMember'] = $v['rakutenMemberFlag'];//是否是会员
        $temp_ori_order['modify'] = $v['modifyFlag'];//是否修改
        $temp_ori_order['option'] = $v['remarks'];//其它信息
        $temp_ori_order['orderDate'] = $v['orderDatetime'];//下单时间
        //TODO
        //待确定
        $temp_ori_order['orderPayDate'] = $v['orderDatetime'];//付款时间
        $temp_ori_order['orderType'] = $v['orderType'];//订单类型
        $temp_ori_order['shippingTerm'] = $v['shippingTerm'];//发货期限
        $temp_ori_order['totalPrice'] = $v['totalPrice'];//总价格
        $temp_ori_order['memo'] = $v['memo'];//备注
        $temp_ori_order['postagePrice'] = $v['postagePrice'];//邮费
        $temp_ori_order['requestPrice'] = $v['requestPrice'];//要求的价格

        //OrdererModel 购物者信息模型
        $temp_ori_order['buyer_sex'] = $v['OrdererModel']['sex'];
        $temp_ori_order['buyer_birthDay'] = $v['OrdererModel']['birthDay'];
        $temp_ori_order['buyer_birthMonth'] = $v['OrdererModel']['birthMonth'];
        $temp_ori_order['buyer_birthYear'] = $v['OrdererModel']['birthYear'];
        $temp_ori_order['buyer_city'] = $v['OrdererModel']['city'];
        $temp_ori_order['buyer_emailAddress'] = $v['OrdererModel']['emailAddress'];
        $temp_ori_order['buyer_familyName'] = $v['OrdererModel']['familyName'];
        $temp_ori_order['buyer_familyNameKana'] = $v['OrdererModel']['familyNameKana'];
        $temp_ori_order['buyer_firstName'] = $v['OrdererModel']['firstName'];
        $temp_ori_order['buyer_firstNameKana'] = $v['OrdererModel']['firstNameKana'];
        $temp_ori_order['buyer_phoneNumber1'] = $v['OrdererModel']['phoneNumber1'];
        $temp_ori_order['buyer_phoneNumber2'] = $v['OrdererModel']['phoneNumber2'];
        $temp_ori_order['buyer_phoneNumber3'] = $v['OrdererModel']['phoneNumber3'];
        $temp_ori_order['buyer_prefecture'] = $v['OrdererModel']['prefecture'];
        $temp_ori_order['buyer_subAddress'] = $v['OrdererModel']['subAddress'];
        $temp_ori_order['buyer_zipCode1'] = $v['OrdererModel']['zipCode1'];
        $temp_ori_order['buyer_zipCode2'] = $v['OrdererModel']['zipCode2'];

        //PackageModelList 收件人模型列表
        $temp_ori_order['package_basketId'] = $v['PackageModelList'][0]['basketId'];
        $temp_ori_order['package_deliveryCompanyId'] = '';
        $temp_ori_order['package_deliveryPrice'] = $v['PackageModelList'][0]['deliveryPrice'];
        $temp_ori_order['package_goodsPrice'] = $v['PackageModelList'][0]['goodsPrice'];
        $temp_ori_order['package_goodsTax'] = $v['PackageModelList'][0]['goodsTax'];
        $temp_ori_order['package_postagePrice'] = $v['PackageModelList'][0]['postagePrice'];

        //senderModel 发件人信息模型
        $temp_ori_order['send_city'] = $v['PackageModelList'][0]['SenderModel']['city'];
        $temp_ori_order['send_familyName'] = $v['PackageModelList'][0]['SenderModel']['familyName'];
        $temp_ori_order['send_familyNameKana'] = $v['PackageModelList'][0]['SenderModel']['familyNameKana'];
        $temp_ori_order['send_firstName'] = $v['PackageModelList'][0]['SenderModel']['firstName'];
        $temp_ori_order['send_firstNameKana'] = $v['PackageModelList'][0]['SenderModel']['familyNameKana'];
        $temp_ori_order['send_phoneNumber1'] = $v['PackageModelList'][0]['SenderModel']['phoneNumber1'];
        $temp_ori_order['send_phoneNumber2'] = $v['PackageModelList'][0]['SenderModel']['phoneNumber2'];
        $temp_ori_order['send_phoneNumber3'] = $v['PackageModelList'][0]['SenderModel']['phoneNumber3'];
        $temp_ori_order['send_prefecture'] = $v['PackageModelList'][0]['SenderModel']['prefecture'];
        $temp_ori_order['send_subAddress'] = $v['PackageModelList'][0]['SenderModel']['subAddress'];
        $temp_ori_order['send_zipCode1'] = $v['PackageModelList'][0]['SenderModel']['zipCode1'];
        $temp_ori_order['send_zipCode2'] = $v['PackageModelList'][0]['SenderModel']['zipCode2'];
        //pointModel 积分点模型
        $temp_ori_order['pointUsage'] = '';
        $temp_ori_order['point_status'] = '';
        $temp_ori_order['usedPoint'] = $v['PointModel']['usedPoint'];
        //settlementModel 付款方式模型
        $temp_ori_order['pay_settlementName'] = $this->settlementMethodNameArr[$v['SettlementModel']['settlementMethod']];
        $temp_ori_order['pay_brandName'] = $v['SettlementModel']['cardName'];
        $temp_ori_order['pay_cardNo'] = $v['SettlementModel']['cardNumber'];
        $temp_ori_order['pay_expYM'] = $v['SettlementModel']['cardYm'];
        $temp_ori_order['pay_ownerName'] = $v['SettlementModel']['cardOwner'];
        $temp_ori_order['pay_payType'] = $v['SettlementModel']['cardPayType'];
        $temp_ori_order['created_at'] = $temp_ori_order['updated_at'] = $current_time;
        $temp_order_id = self::insertGetId($temp_ori_order);

        $buyer_phone = $temp_ori_order['buyer_phoneNumber1'] . '-' . $temp_ori_order['buyer_phoneNumber2'] . '-' . $temp_ori_order['buyer_phoneNumber3'];
        $consignee_name = $temp_ori_order['send_familyName'] . ' ' . $temp_ori_order['send_firstName'] . '[' . $temp_ori_order['send_familyNameKana'] . ' ' . $temp_ori_order['send_firstNameKana'] . ']';
        $consignee_phone = $temp_ori_order['send_phoneNumber1'] . '-' . $temp_ori_order['send_phoneNumber2'] . '-' . $temp_ori_order['send_phoneNumber3'];
        $consignee_zipcode = $temp_ori_order['send_zipCode1'] . '-' . $temp_ori_order['send_zipCode2'];

        //组装原始订单表 orders_original 数据
        $orders_original_data ['created_man']       = $sale_info['user_id'];
        $orders_original_data ['platform']          = $sale_info['platforms_id'];
        $orders_original_data ['source_shop']       = $sale_info['shop_id'];
        $orders_original_data ['bill_payments']     = 0;
        $orders_original_data ['order_id']          = 0;
        $orders_original_data ['order_number']      = $order_rakuten_source;
        $orders_original_data ['platform_name']     = '乐天';
        $orders_original_data ['source_shop_name']  = $sale_info['shop_name'];
        $orders_original_data ['match_status']      = $temp_ori_order['match_status'];
        $orders_original_data ['order_price']       = $temp_ori_order['totalPrice'];
        $orders_original_data ['payment_method']    = $temp_ori_order['pay_settlementName'];
        $orders_original_data ['freight']           = '0.00';
        $orders_original_data ['currency_freight']  = $currencyInfo['currency_code'];

        $orders_original_data ['country']           = $country;
        $orders_original_data ['province']          = $temp_ori_order['send_prefecture'];
        $orders_original_data ['city']              = $temp_ori_order['send_city'];
        $orders_original_data ['mobile_phone']       = $buyer_phone;
        $orders_original_data ['phone']             = $consignee_phone;
        $orders_original_data ['addressee_email']   = $temp_ori_order['buyer_emailAddress'];
        //仓库物流
        $orders_original_data ['warehouse']         = '';
        $orders_original_data ['warehouse_id']      = 0;
        $orders_original_data ['logistics']         = $temp_ori_order['deliveryName'];
        $userLogisticsKey = array_search($orders_original_data ['logistics'], $userLogisticsCodes);
        if (is_bool($userLogisticsKey)) {
            $orders_original_data ['logistics_id']  = '';
        } else {
            $orders_original_data ['logistics_id']  = $userLogisticsInfo[$userLogisticsKey]['id'];
        }

        $orders_original_data ['addressee']         = $temp_ori_order['send_subAddress'];
        $orders_original_data ['addressee1']        = $orders_original_data ['addressee2'] = '';

        $orders_original_data ['order_time']        = $orders_original_data ['payment_time'] = $temp_ori_order['orderDate'];
        $orders_original_data ['currency']          = $currencyInfo['currency_code'];
        $orders_original_data ['order_source']      = OrdersOriginal::ORDERS_ORIGINAL_FROM_API;
        $orders_original_data ['user_id']           = $sale_info['user_id'];
        $orders_original_data ['grab_time']         = $current_time;
        $orders_original_data ['zip_code']          = $consignee_zipcode;
        $orders_original_data ['platform_order']    = '乐天';
        $orders_original_data ['rate']              = $currencyInfo['rate'];
        $orders_original_data ['country_id']        = $country_id;
        $orders_original_data ['order_source_id']   = $temp_order_id;

        $orders_original_id = OrdersOriginal::insertGetId($orders_original_data);

        //原始订单生成付款单
        $orig_payment_data ['created_man']  = $sale_info['user_id'];
        $orig_payment_data ['cw_order_id']  = $orders_original_id;
        $orig_payment_data ['totalPrice']   = $temp_ori_order ['totalPrice'];
        $orig_payment_datas                 = $this->getOrdersBill($orig_payment_data,$currencyInfo,OrdersBillPayments::BILLS_PAY,$current_time,OrdersBillPayments::ORDERS_ORIG_RAKUTEN);
        $orig_payment_datas ['bill_code']   = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$order_rakuten_source,'1');
        $orig_payment_result = OrdersBillPayments::postData(0,$orig_payment_datas);
        //付款单
        $orders_original_update_data ['bill_payments'] = $orig_payment_result->id;


        //订单商品全部有映射关系才算匹配成功
        $skus               = array_column($v['PackageModelList'][0]['ItemModelList'], 'itemNumber');
        $mappingInfo        = GoodsMapping::getMappingInfoByUseridSku($sale_info['user_id'], $skus, Platforms::RAKUTEN);
        $mappingInfoItemURL = array_column($mappingInfo, 'itemURL');
        if (empty($mappingInfo)) {
            $match_status = false;
        }
        if (!empty($mappingInfo) && (count($skus) == count($mappingInfo))) {
            $match_status = true;
        }

        $erpOrderInfo = $orig_rakuten_update_data = $orders_original_update_data = [];

        //匹配成功 将生成平台订单
        if ($match_status) {
            //写订单表
            $erpOrderInfo ['created_man']           = $sale_info['user_id'];
            $erpOrderInfo ['user_id']               = $sale_info['user_id'];
            $erpOrderInfo ['platforms_id']          = Platforms::RAKUTEN;
            $erpOrderInfo ['source_shop']           = $sale_info['id'];
            $erpOrderInfo ['order_number']          = $temp_ori_order['cw_code'];
            $erpOrderInfo ['plat_order_number']     = $order_rakuten_source;
            $erpOrderInfo ['type']                  = Orders::ORDERS_GETINFO_API;
            $erpOrderInfo ['platform_name']         = '乐天';
            //店铺名称
            $shopKey = array_search($sale_info['id'], $shopIds);
            $erpOrderInfo ['source_shop_name']      = $shopInfo[$shopKey]['shop_name'];
            //默认状态
            $erpOrderInfo ['picking_status']        = Orders::ORDER_PICKING_STATUS_UNMATCH;
            $erpOrderInfo ['deliver_status']        = Orders::ORDER_DELIVER_STATUS_UNFILLED;
            $erpOrderInfo ['intercept_status']      = Orders::ORDER_INTERCEPT_STATUS_INITIAL;
            $erpOrderInfo ['sales_status']          = Orders::ORDER_SALES_STATUS_INITIAL;
            $erpOrderInfo ['status']                = Orders::ORDER_STATUS_UNFINISH;
            $erpOrderInfo ['order_price']           = $temp_ori_order['totalPrice'];
            $erpOrderInfo ['currency_code']         = $erpOrderInfo['currency_freight'] = $currencyInfo['currency_code'];
            $erpOrderInfo ['rate']                  = $currencyInfo ['rate'];
            $erpOrderInfo ['payment_method']        = $temp_ori_order['pay_settlementName'];
            $erpOrderInfo ['freight']               = $temp_ori_order['deliveryPrice'];
            $erpOrderInfo ['postal_code']           = $consignee_zipcode;
            $erpOrderInfo ['country_id']            = $country_id;
            $erpOrderInfo ['country']               = $country;
            $erpOrderInfo ['province']              = $temp_ori_order['send_prefecture'];
            $erpOrderInfo ['city']                  = $temp_ori_order['send_city'];
            $erpOrderInfo ['mobile_phone']           = $buyer_phone;
            $erpOrderInfo ['phone']                 = $consignee_phone;
            $erpOrderInfo ['addressee_name']        = $consignee_name;
            $erpOrderInfo ['addressee_email']       = $temp_ori_order['buyer_emailAddress'];
            //仓库
            $erpOrderInfo ['warehouse_id']          = '';
            $erpOrderInfo ['warehouse']             = '';
            $erpOrderInfo ['logistics']             = $temp_ori_order['deliveryName'];
            $userLogisticsKey = array_search($erpOrderInfo ['logistics'], $userLogisticsCodes);
            if (is_bool($userLogisticsKey)) {
                $erpOrderInfo ['logistics_id'] = '';
            } else {
                $erpOrderInfo ['logistics_id'] = $userLogisticsInfo[$userLogisticsKey]['id'];
            }

            $erpOrderInfo ['addressee']     = $temp_ori_order['send_subAddress'];
            $erpOrderInfo ['addressee1']    = $erpOrderInfo ['addressee2'] = '';
            $erpOrderInfo ['order_time']    = $erpOrderInfo ['payment_time'] = $temp_ori_order['orderDate'];
            $erpOrderInfo ['created_at']    = $erpOrderInfo ['updated_at'] = $current_time;
            //订单id
            $erpOrderId = Orders::insertGetId($erpOrderInfo);

            //针对原始订单之前的退款单 付款单 写一份平台订单数据

            //乐天平台的指定id的 退款 付款单据 待优化
            $billOptions ['order_id']       = $orders_original_id;
            $billOptions ['order_type']     = OrdersBillPayments::ORDERS_ORIG_RAKUTEN;
            $billsDatas = OrdersBillPayments::getBillsByOptionss($billOptions);
            foreach ($billsDatas as $billsData) {
                unset($billsData ['id']);
                $billsData ['order_id']     = $erpOrderId;
                $billsData ['order_type']   = OrdersBillPayments::ORDERS_CWERP;
                if ($billsData ['type'] == OrdersBillPayments::BILLS_PAY) {
                    //目前一个订单只有一个付款单
                    $billsData ['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE,$erpOrderInfo ['order_number'],'1');
                } else {
                    $billsData ['bill_code'] = '';
                }
                OrdersBillPayments::postData(0,$billsData);
            }

            //原始乐天订单关联
            $orig_rakuten_update_data ['cw_order_id']       = $erpOrderId;
            $orig_rakuten_update_data ['is_system']         = OrdersRakuten::IS_SYSTEM_ORDER;//是系统订单
            $orig_rakuten_update_data ['match_status']      = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FINISHED;//匹配成功

            //关联原始订单
            $orders_original_update_data ['order_id']       = $erpOrderId;
            $orders_original_update_data ['match_status']   = OrdersAmazon::AMAZON_MAPPING_STATUS_FINISHED;//匹配成功

            //订单量逻辑
            $record_time = strtotime(date('Y-m-d'));
            OrdersQuantityRecord::orderQuantityLogics($sale_info,$record_time);
        } else {
            if ($temp_ori_order ['is_system'] == OrdersRakuten::UN_SYSTEM_ORDER && $temp_ori_order ['match_status'] == OrdersRakuten::RAKUTEN_MAPPING_STATUS_UNFINISH) {
                $orig_rakuten_update_data ['is_system']         = OrdersRakuten::UN_SYSTEM_ORDER;//是系统订单
                $orig_rakuten_update_data ['match_status']      = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FAIL;//匹配成功

                $orders_original_update_data ['match_status']   = OrdersAmazon::AMAZON_MAPPING_STATUS_FAIL;//匹配失败
            }
        }
        //写订单商品数据
        $rakuten_products = $orders_original_product_info = $order_product_info = [];
        foreach ($v['PackageModelList'][0]['ItemModelList'] as $key => $rakuten_product) {
            $two_sku = "";
            $result = array();
            preg_match_all("/(?<=\()[^()]\d*(?=\))/", $rakuten_product['selectedChoice'], $result);
            if (count($result[0]) === 1) {
                $two_sku = $result[0][0];
            }

            $rakuten_products[$key]['basketId']                         = $v['PackageModelList'][0]['basketId'];//目的地ID
            $rakuten_products[$key]['isIncludedCashOnDeliveryPostage']  = $rakuten_product['includeCashOnDeliveryPostageFlag'];//包括COD费用 0：货到付款 1：包含货到付款
            $rakuten_products[$key]['isIncludedPostage']                = $rakuten_product['includePostageFlag']; //：0运费  ：包括运费或免运费
            $rakuten_products[$key]['isIncludedTax']                    = $rakuten_product['includeTaxFlag'];//：0不含税 1：含税
            $rakuten_products[$key]['itemId']                           = $rakuten_product['itemId'];//产品编号
            $rakuten_products[$key]['itemName']                         = $rakuten_product['itemName'];//产品名称
            $rakuten_products[$key]['itemNumber']                       = $rakuten_product['itemNumber'];//物品编号 项目编号（商店注册的编号）+项目选项ID（横轴）+项目选项ID（垂直轴）
            $rakuten_products[$key]['Item_delvdateInfo']                = $rakuten_product['delvdateInfo'];//交货时间信息
            $rakuten_products[$key]['Item_inventoryType']               = $rakuten_product['inventoryType'];//0：无库存设置 1：正常库存设置 2：项目选择库存设置
            $rakuten_products[$key]['pageURL']                          = '';
            $rakuten_products[$key]['pointRate']                        = $rakuten_product['pointRate'];//点率
            $rakuten_products[$key]['pointType']                        = '';
            $rakuten_products[$key]['price']                            = $rakuten_product['price'];//价格
            $rakuten_products[$key]['selectedChoice']                   = $rakuten_product['selectedChoice'];//1：正常购买3：联合购买4：常规购买5：分发会议6：预留产品
            $rakuten_products[$key]['units']                            = $rakuten_product['units'];
            $rakuten_products[$key]['rakuten_order_id']                 = $temp_order_id;
            $rakuten_products[$key]['user_id']                          = $sale_info['user_id'];
            $rakuten_products[$key]['created_at']                       = $rakuten_products['updated_at'] = $current_time;

            //原始订单商品数据
            $orders_original_product_info[$key]['created_man']          = $sale_info['user_id'];
            $orders_original_product_info[$key]['user_id']              = $sale_info['user_id'];
            $orders_original_product_info[$key]['original_order_id']    = $orders_original_id;
            $orders_original_product_info[$key]['goods_id']             = 0;
            $orders_original_product_info[$key]['goods_img']             = 0;
            $orders_original_product_info[$key]['platform_id']          = Platforms::RAKUTEN;
            $orders_original_product_info[$key]['sku']                  = $rakuten_products[$key]['itemNumber'];
            $orders_original_product_info[$key]['price']                = $rakuten_products[$key]['price'];
            $orders_original_product_info[$key]['quantity']             = $rakuten_products[$key]['units'];
            $orders_original_product_info[$key]['goods_name']            = $rakuten_products[$key]['itemName'];
            $orders_original_product_info[$key]['rate']                 = $currencyInfo ['rate'];
            $orders_original_product_info[$key]['RMB']                  = bcmul(bcmul($currencyInfo ['rate'], $orders_original_product_info[$key]['price']), $orders_original_product_info[$key]['quantity']);
            $orders_original_product_info[$key]['created_at']           = $orders_original_product_info[$key]['updated_at'] = $current_time;

            //匹配成功之后 应用该数据
            if ($match_status) {
                $mappingKey =  array_search($rakuten_products['itemNumber'], $mappingInfoItemURL);
                $goods_id = GoodsMappingGoods::getGoodsIdByMappingid($mappingInfo[$mappingKey]['id']);
                $order_product_info[$key]['goods_id']                   = $goods_id['goods_id'];
                $rakuten_products[$key]['goods_id']        = $order_product_info[$key]['goods_id'] = $orders_original_product_info[$key]['goods_id']    = $goods_id['goods_id'];

                $order_product_info[$key]['created_man']                = $sale_info['user_id'];
                $order_product_info[$key]['order_id']                   = $erpOrderId;
                //原始表商品图片
                $orders_original_product_info[$key]['goods_img']         = $goods_id ['goods'] ? $goods_id ['goods'] ['goods_pictures'] : '';
                $order_product_info[$key]['order_type']                 = OrdersProducts::ORDERS_CWERP;
                $order_product_info[$key]['product_name']               = $rakuten_products['itemName'];
                $order_product_info[$key]['sku']                        = ($two_sku) ? $two_sku : $rakuten_products['itemNumber'];
                $order_product_info[$key]['currency']                   = $currencyInfo ['currency_code'];
                $order_product_info[$key]['number']                     = $rakuten_products['units'];
                $order_product_info[$key]['univalence']                 = $rakuten_products['price'];
                $order_product_info[$key]['rate']                       = $currencyInfo ['rate'];
                $order_product_info[$key]['RMB']                        = bcmul(bcmul($currencyInfo ['rate'], $rakuten_products['price']), $rakuten_products['units']);
                $order_product_info[$key]['created_at']                 = $order_product_info[$key]['updated_at'] = $current_time;
            }
        }

        //原始乐天商品表写数据
        if (!empty($rakuten_products)) {
            OrdersRakutenProducts::insert($rakuten_products);
        }

        //原始订单商品表写数据
        if (!empty($orders_original_product_info)) {
            OrdersOriginalProducts::insert($orders_original_product_info);
        }

        $orig_rakuten_update_data ['updated_at']   = $orders_original_update_data ['updated_at'] = $current_time;
        if ($match_status) {
            OrdersProducts::insert($order_product_info);
        }

        //原始乐天订单 匹配状态等数据更新
        OrdersRakuten::where('id',$temp_order_id)->update($orig_rakuten_update_data);
        //原始订单 匹配状态等数据更新
        OrdersOriginal::where('id',$orders_original_id)->update($orders_original_update_data);
    }


    /**
     * @param $v
     * @param $sale_info
     * @param $order_rakuten_source
     * @param $shopIds
     * @param $shopInfo
     * @param $currencyInfo
     * @param $country_id
     * @param $country
     * @param $userLogisticsCodes
     * @param $userLogisticsInfo
     * @param $current_time
     * Note: 匹配逻辑
     * Data: 2019/4/16 16:36
     * Author: zt7785
     */
    public function mappingLogic ($v,$sale_info,$order_rakuten_source,$shopIds,$shopInfo,$currencyInfo,$country_id,$country,$userLogisticsCodes,$userLogisticsInfo,$current_time,$orig_rakuten_info) {
        $match_status = false;
        //订单商品全部有映射关系才算匹配成功
        $skus = array_column($v['PackageModelList'][0]['ItemModelList'], 'itemNumber');
        $mappingInfo = GoodsMapping::getMappingInfoByUseridSku($sale_info['user_id'], $skus, Platforms::RAKUTEN);
        if ($mappingInfo->isEmpty()) {
            $match_status = false;
        }
        if (!empty($mappingInfo) && (count($skus) == count($mappingInfo))) {
            $match_status = true;
        }
        //匹配失败 返回
        if (empty($match_status)){
            if ($orig_rakuten_info ['is_system'] == OrdersRakuten::UN_SYSTEM_ORDER && $orig_rakuten_info ['match_status'] == OrdersRakuten::RAKUTEN_MAPPING_STATUS_FAIL) {
                return ;
            }
            $orig_rakuten_data ['is_system'] = OrdersRakuten::UN_SYSTEM_ORDER;//不是系统订单
            $orig_rakuten_data ['match_status'] = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FAIL;//匹配失败
            $orig_rakuten_data ['updated_at'] = $current_time;
            OrdersRakuten::where('id',$orig_rakuten_info['id'])->update($orig_rakuten_data);
            return ;
        }

            $erpOrderInfo = [];
            $buyer_phone = $v['OrdererModel']['phoneNumber1'] . '-' . $v['OrdererModel']['phoneNumber2'] . '-' . $v['OrdererModel']['phoneNumber3'];
            $consignee_name = $v['PackageModelList'][0]['SenderModel']['familyName'] . ' ' . $v['PackageModelList'][0]['SenderModel']['firstName'] . '[' . $v['PackageModelList'][0]['SenderModel']['familyNameKana'] . ' ' . $v['PackageModelList'][0]['SenderModel']['familyNameKana'] . ']';
            $consignee_phone = $v['PackageModelList'][0]['SenderModel']['phoneNumber1'] . '-' . $v['PackageModelList'][0]['SenderModel']['phoneNumber2'] . '-' . $v['PackageModelList'][0]['SenderModel']['phoneNumber3'];
            $consignee_zipcode = $v['PackageModelList'][0]['SenderModel']['zipCode1'] . '-' . $v['PackageModelList'][0]['SenderModel']['zipCode2'];
            //写订单表
            $erpOrderInfo ['created_man'] = $sale_info['user_id'];
            $erpOrderInfo ['user_id'] = $sale_info['user_id'];
            $erpOrderInfo ['platforms_id'] = Platforms::RAKUTEN;
            $erpOrderInfo ['source_shop'] = $sale_info['id'];
            $erpOrderInfo ['order_number'] = $orig_rakuten_info ['cw_code'];
            $erpOrderInfo ['plat_order_number'] = $order_rakuten_source;
            $erpOrderInfo ['type'] = Orders::ORDERS_GETINFO_API;
            $erpOrderInfo ['platform_name'] = '乐天';
            //店铺名称
            $shopKey = array_search($sale_info['id'], $shopIds);
            $erpOrderInfo ['source_shop_name'] = $shopInfo[$shopKey]['shop_name'];
            //默认状态
            $erpOrderInfo ['picking_status'] = Orders::ORDER_PICKING_STATUS_UNMATCH;
            $erpOrderInfo ['deliver_status'] = Orders::ORDER_DELIVER_STATUS_UNFILLED;
            $erpOrderInfo ['intercept_status'] = Orders::ORDER_INTERCEPT_STATUS_INITIAL;
            $erpOrderInfo ['sales_status'] = Orders::ORDER_SALES_STATUS_INITIAL;
            $erpOrderInfo ['status'] = Orders::ORDER_STATUS_UNFINISH;
            $erpOrderInfo ['order_price'] = $v['totalPrice'];
            $erpOrderInfo ['currency_code'] = $erpOrderInfo['currency_freight'] = $currencyInfo['currency_code'];
            $erpOrderInfo ['rate'] = $currencyInfo ['rate'];
            $erpOrderInfo ['payment_method'] = $this->settlementMethodNameArr[$v['SettlementModel']['settlementMethod']];
            $erpOrderInfo ['freight'] = $v['deliveryPrice'];
            $erpOrderInfo ['postal_code'] = $consignee_zipcode;
            $erpOrderInfo ['country_id'] = $country_id;
            $erpOrderInfo ['country'] = $country;
            $erpOrderInfo ['province'] = $v['PackageModelList'][0]['SenderModel']['prefecture'];
            $erpOrderInfo ['city'] = $v['PackageModelList'][0]['SenderModel']['city'];
            $erpOrderInfo ['mobile_phone'] = $buyer_phone;
            $erpOrderInfo ['phone'] = $consignee_phone;
            $erpOrderInfo ['addressee_name'] = $consignee_name;
            $erpOrderInfo ['addressee_email'] = $v['OrdererModel']['emailAddress'];
            //仓库
            $erpOrderInfo ['warehouse_id'] = '';
            $erpOrderInfo ['warehouse'] = '';
            $erpOrderInfo ['logistics'] = $v['DeliveryModel']['deliveryName'];//物流名称
            $userLogisticsKey = array_search($erpOrderInfo ['logistics'], $userLogisticsCodes);
            if (is_bool($userLogisticsKey)) {
                $erpOrderInfo ['logistics_id'] = '';
            } else {
                $erpOrderInfo ['logistics_id'] = $userLogisticsInfo[$userLogisticsKey]['id'];
            }

            $erpOrderInfo ['addressee'] = $v['PackageModelList'][0]['SenderModel']['subAddress'];
            $erpOrderInfo ['addressee1'] = $erpOrderInfo ['addressee2'] = '';
            $erpOrderInfo ['order_time'] = $erpOrderInfo ['payment_time'] = $v['orderDatetime'];//下单时间
            $erpOrderInfo ['created_at'] = $erpOrderInfo ['updated_at'] = $current_time;
            //订单id
            $erpOrderId = Orders::insertGetId($erpOrderInfo);

            //原始订单更新为已匹配
            $orig_rakuten_data ['cw_order_id'] = $erpOrderId;
            $orig_rakuten_data ['is_system'] = OrdersRakuten::IS_SYSTEM_ORDER;//是系统订单
            $orig_rakuten_data ['match_status'] = OrdersRakuten::RAKUTEN_MAPPING_STATUS_FINISHED;//匹配成功
            $orig_rakuten_data ['updated_at'] = $current_time;
            OrdersRakuten::where('id',$orig_rakuten_info['id'])->update($orig_rakuten_data);
            //针对原始订单之前的退款单 付款单 写一份平台订单数据

            //乐天平台的指定id的 退款 付款单据
            $billOptions ['order_id'] = $erpOrderId;
            $billOptions ['order_type'] = OrdersBillPayments::ORDERS_ORIG_RAKUTEN;
            $billsDatas = OrdersBillPayments::getBillsByOptionss($billOptions);
            foreach ($billsDatas as $billsData) {
                unset($billsData ['id']);
                $billsData ['order_id'] = $erpOrderId;
                $billsData ['order_type'] = OrdersBillPayments::ORDERS_CWERP;
                OrdersBillPayments::postData(0,$billsData);
            }
        //订单量逻辑
        $record_time = date('Y-m-d');
        OrdersQuantityRecord::orderQuantityLogics($sale_info,$record_time);
        //写订单商品数据
        foreach ($v['PackageModelList'][0]['ItemModelList'] as $key => $rakuten_product) {
            $two_sku = "";
            $result = array();
            preg_match_all("/(?<=\()[^()]\d*(?=\))/", $rakuten_product['selectedChoice'], $result);
            if (count($result[0]) === 1) {
                $two_sku = $result[0][0];
            }
            $rakuten_products = array();
            $rakuten_products['itemName'] = $rakuten_product['itemName'];//产品名称
            $rakuten_products['itemNumber'] = $rakuten_product['itemNumber'];//物品编号 项目编号（商店注册的编号）+项目选项ID（横轴）+项目选项ID（垂直轴）
            $rakuten_products['price'] = $rakuten_product['price'];//价格
            $rakuten_products['units'] = $rakuten_product['units'];
            //匹配成功之后 应用该数据
            $order_product_info[$key]['created_man'] = $sale_info['user_id'];
            $order_product_info[$key]['order_id'] = $erpOrderId;
            $goods_id = GoodsMappingGoods::getGoodsIdByMappingid($mappingInfo[$key]['id']);
            $order_product_info[$key]['goods_id'] = $goods_id;
            $order_product_info[$key]['order_type'] = OrdersProducts::ORDERS_CWERP;
            $order_product_info[$key]['product_name'] = $rakuten_products['itemName'];
            $order_product_info[$key]['sku'] = ($two_sku) ? $two_sku : $rakuten_products['itemNumber'];
            $order_product_info[$key]['currency'] = $currencyInfo ['currency_code'];
            $order_product_info[$key]['number'] = $rakuten_products['units'];
            $order_product_info[$key]['univalence'] = $rakuten_products['price'];
            $order_product_info[$key]['rate'] = $currencyInfo ['rate'];
            $order_product_info[$key]['RMB'] = bcmul(bcmul($currencyInfo ['rate'], $rakuten_products['price']), $rakuten_products['units']);
            $order_product_info[$key]['created_at'] = $order_product_info[$key]['updated_at'] = $current_time;
            OrdersProducts::insert($order_product_info);
        }
    }
}
