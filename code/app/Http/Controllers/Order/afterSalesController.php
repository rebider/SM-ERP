<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/15
 * Time: 10:26
 */

namespace App\Http\Controllers\Order;


use App\Auth\Models\RolesShops;
use App\Common\Common;
use App\Http\Controllers\Controller;
use App\Models\CodeInfo;
use App\Models\Goods;
use App\Models\Orders;
use App\Models\OrdersAfterSales;
use App\Models\OrdersAfterSalesProducts;
use App\Models\OrdersBillPayments;
use App\Models\OrdersInvoices;
use App\Models\OrdersProducts;
use App\Models\OrdersQuantityRecord;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingCurrencyExchangeMaintain;
use App\Models\SettingShops;
use App\Models\SettingWarehouse;
use App\Models\WarehouseTypeGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use phpDocumentor\Reflection\Types\Resource;

class afterSalesController extends Controller
{
    /**
     * @var int 订单类型为售后
     */
    const ORDER_TYPE = 3;

    /**
     * @val int 订单产品表的订单类型，1为平台订单
     */
    const ORDER_PRODUCT_TYPE = 1;

    /**
     * @var int 付款单类型：付款
     */
    const BILL_PAYMENT_PAY = 1;

    /**
     * @var int 付款单类型：退款
     */
    const BILL_PAYMENT_REFUND = 2;

    /**
     * @var int 售后类型：退货
     */
    const AFTER_SALES_RETURN = 1;
    /**
     * @var int 售后类型：换货
     */
    const AFTER_SALES_EXCHANGE = 2;
    /**
     * @var int 售后类型：退款
     */
    const AFTER_SALES_REFUND = 3;
    /**
     * @var int 售后单取消
     */
    const AF_ORDER_CANCELED = 2;
    /**
     * @var int 付款单取消
     */
    const BILL_CANCEL = 2;
    /**
     * @var int 订单取消
     */
    const ORDER_CANCEL = 3;

    /**
     * @note
     *
     * @param: 售后单首页
     * @return: array
     * @since: 2019/4/15
     * @author: zt7837
     */
    public function afterSalesIndex()
    {
//        $responseData ['shortcutMenus'] = Orders::getOrderShortcutMenu();
        return view('Order/afterSales/index');
    }

    /**
     * @note
     * 创建订单
     * @param:
     * @return: array
     * @since: 2019/4/15
     * @author: zt7837
     */
    public function addOrder(Request $request)
    {
        return view('Order/afterSales/addOrder');
    }

    /**
     * @note
     * 订单是否存在
     * @since: 2019/5/28
     * @author: zt7837
     * @return: array
     */
    public function getOrderNumber(Request $request){
        $number = $request->input('order_num');
        $responseData = ['code'=>0,'msg'=>''];
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($CurrentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if (empty($shopsId)) {
                    $result['msg'] = '未配置店铺权限';
                    return parent::layResponseData($result);
                }
                $re = Orders::getOrdersByordernum($number,$user_id,$shopsId);
            } else {
                $result['msg'] = '未配置店铺权限';
                return parent::layResponseData($result);
            }
        } else {
            $user_id = $CurrentUser->userId;
            $order = Orders::where(['order_number'=>$number,'user_id'=>$user_id])->first();
            if(!$order) {
                $responseData['msg'] = '订单不存在!';
                return $responseData;
            }
            $re = Orders::getOrdersByordernum($number,$user_id);
            if($re) {
                $re = $re->toArray();
                if($re['orders_products']){
                    $responseData ['code'] = 200;
                    return parent::layResponseData($responseData);
                }
            }
            $responseData['msg'] = '订单未发货!';
        }
        return parent::layResponseData($responseData);
    }

    /**
     * @note
     * 创建售后单
     * @return: array
     * @author: zt7837
     * @since: 2019/4/15
     * @alter by: zt12779
     * @alter date: 2019/05/05
     */
    public function createPaymentOrder(Request $request)
    {
        $orderNum = $request->input('order_num');
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }

        if ($request->isMethod('post')) {
            $data = $request->all();
            try {
                if (!empty($data['goods'])) {
                    DB::beginTransaction();
                    //todo validate
                    foreach ($data['goods'] as $keys => $vals) {
                        if ($vals['after_number'] == 0) {
                            unset($data['goods'][$keys]);
                        }
                    }

                    //售后单数据录入开始
                    $afterOrder['created_man'] = $user_id = $CurrentUser->userId;
                    $afterOrder['user_id'] = $user_id;
                    $afterOrder['id'] = $data['order_id'];
                    $afterOrder['invoice_id'] = $data['invoice_id'];
                    $afterOrder['refund'] = $data['after_money_total'];
                    $afterOrder['order_number'] = $data['order_num'];
                    $curencyInfo['currency_code'] = isset($data['currency_code']) && !empty($data['currency_code']) ? $data['currency_code'] : '';
                    $curencyInfo['rate'] = $data['rate'];

                    //如果操作不再区间内
                    if ($data['operation'] < 0 && $data['operation'] > 4) {
                        $layData = [
                            'code' => 201,
                            'msg' => '添加失败',
                        ];
                        DB::rollback();
                    }

                    if ($data['operation'] == self::AFTER_SALES_RETURN && empty($data['warehouse_id'])) {
                        $layData = [
                            'code' => 201,
                            'msg' => '添加失败，请选择退回的仓库',
                        ];
                        DB::rollback();
                        return $this->layResponseData($layData);
                    } else {
                        $afterOrder['warehouse_id'] = $data['warehouse_id'];
                    }
                    $afterOrder['supplement'] = $data['supplement'];
                    //售后类型
                    $data['operation'] == 1 && $type = OrdersAfterSales::AFTERSALE_TYPE_RETURN;
                    $data['operation'] == 2 && $type = OrdersAfterSales::AFTERSALE_TYPE_EXCHANGE;
                    $data['operation'] == 3 && $type = OrdersAfterSales::AFTERSALE_TYPE_REFUND;
                    //创建售后单
                    $orderObj = OrdersAfterSales::creatAfterOrder($afterOrder, $type, $curencyInfo);
                    //需要退货的那条订单
                    $order = Orders::where('id', $data['order_id'])->first();

                    $billResult = true;
                    if ($data['operation'] == self::AFTER_SALES_REFUND) {
                        //创建付款单
                        $billResult = $this->createPayBill($CurrentUser, $data['order_id'], $data['currency_code'], $data['after_money_total'], $orderObj, self::BILL_PAYMENT_REFUND);
                    }

                    //换货以及退货，此部分由于‘退款’和‘补款’都是可选，所以增加状态判断
                    if ($data['operation'] == self::AFTER_SALES_EXCHANGE || $data['operation'] == self::AFTER_SALES_RETURN) {
                        //如果选择了补款，创建付款单
                        if ($data['supplement'] > 1) {
                            $billResult = $this->createPayBill($CurrentUser, $data['order_id'], $data['currency_code'], $data['after_money_total'], $orderObj, self::BILL_PAYMENT_PAY);
                        }

                        //售后的退款金额
                        if ($data['after_money_total'] > 1) {
                            $billResult = $this->createPayBill($CurrentUser, $data['order_id'], $data['currency_code'], $data['after_money_total'], $orderObj, self::BILL_PAYMENT_REFUND);
                        }
                        if (!$billResult) {
                            $layData = [
                                'code' => 201,
                                'msg' => '添加失败',
                            ];
                            DB::rollback();
                            return $this->layResponseData($layData);
                        }
                    }

                    //如果是换货部分，则创建订单
                    if ($data['operation'] == self::AFTER_SALES_EXCHANGE) {
                        //检查是否提交了换货的产品
                        if (empty($data['sku'])) {
                            $layData = ['code' => 201, 'msg' => '请选择换货商品'];
                            return $this->layResponseData($layData);
                        }
                        //创建换货产品的订单
                        $msg = '';
                        $orderResult = $this->createExchangeOrder($order, $data['sku'], $data['quantity'], $user_id, $CurrentUser, $msg);
                            if (!$orderResult) {
                            $layData = ['code' => 201, 'msg' => ''];
                            return $this->layResponseData($layData);
                        }
                        OrdersAfterSales::where('id', $orderObj)->update(['swap_order_id' => $orderResult]);


                    }

                    foreach ($data['goods'] as $k => $v) {
                        //售后单产品
                        if ($orderObj) {
                            $afterProductModel = new OrdersAfterSalesProducts();
                            $afterOrderProduct['created_man'] = $user_id;
                            $afterOrderProduct['after_sale_id'] = $orderObj;
                            $afterOrderProduct['attribute'] = isset($data['attribute']) && !empty($data['attribute']) ? $data['attribute'] : '';
                            $afterOrderProduct['number'] = $v['after_number'];
                            $afterOrderProduct['already_stocked_number'] = isset($v['already_stocked_number']) && !empty($v['already_stocked_number']) ? $v['already_stocked_number'] : 0;
                            $afterOrderProduct['cargo_distribution_number'] = isset($data['cargo_distribution_number']) && !empty($data['cargo_distribution_number']) ? $data['cargo_distribution_number'] : 0;
                            $afterOrderProduct['univalence'] = isset($v['univalence']) && !empty($v['univalence']) ? $v['univalence'] : '0.0000';
                            $afterOrderProduct['rate'] = $curencyInfo['rate'];
                            $afterOrderProduct['currency'] = $curencyInfo['currency_code'];
                            $afterOrderProduct['goods_id'] = $k;
                            $afterOrderProduct['product_name'] = isset($v['product_name']) && !empty($v['product_name']) ? $v['product_name'] : '';
                            $afterOrderProduct['sku'] = $v['sku'];

                            $afterProductObj = OrdersAfterSalesProducts::createAfterSaleProducts($afterOrderProduct, $afterProductModel);

                            //蔡义 查询语句错误 调整
                            $orderProduct = OrdersProducts::where(['goods_id' => $k,'order_id' => $data['order_id'],'is_deleted' => OrdersProducts::ORDERS_PRODUCT_UNDELETED])->first();
                            if (empty($orderProduct)) {
                                $layData['code'] = 201;
                                $layData['msg'] = 'SKU:'.$v['sku'].'商品信息异常';
                                DB::rollback();
                                return parent::layResponseData($layData);
                            }

                            $afterSaleTotal = bcsub(($orderProduct['delivery_number'] - $orderProduct ['aftersale_refund_number']), $v['after_number']);
                            if ($afterSaleTotal < 0) {
                                $layData['code'] = 201;
                                $layData['msg'] = '添加失败,预售后商品数量不能超过可售后数量';
                                DB::rollback();
                                return parent::layResponseData($layData);
                            }

//                            $updateNumber = OrdersProducts::updateDeliveryNumber($k,$data['order_id'],$v['after_number']);
                            //蔡义 重复查询 多余
                            $updateNumber = OrdersProducts::where('id',$orderProduct['id'])->update(['aftersale_refund_number'=>$orderProduct ['aftersale_refund_number'] + $v['after_number'],'updated_at'=>date('Y-m-d H:i:s')]);
                            if(!$updateNumber) {
                                $layData['code'] = 201;
                                $layData['msg'] = '添加失败';
                                DB::rollback();
                            }
                        } else {
                            $layData['code'] = 201;
                            $layData['msg'] = '添加失败';
                            DB::rollback();
                        }
                    }
                    if ($orderObj && $afterProductObj) {
                        DB::commit();
                        $layData = [
                            'code' => 200,
                            'msg' => '添加成功',
                        ];

                    } else {
                        $layData = [
                            'code' => 201,
                            'msg' => '添加失败',
                        ];
                        DB::rollback();
                    }
                    return parent::layResponseData($layData);
                }
            } catch (\Exception $exception) {
                $layData['code'] = 201;
                $layData['msg'] = '添加失败';
                DB::rollback();
                //什么烂代码
                return parent::layResponseData($layData);
                Common::mongoLog($exception, '售后单', '新建售后单出错', 'createPaymentOrder()', 'api');
            }
        }

        $afterObj = Orders::getOrdersByordernum($orderNum,$user_id);
        $afterOrders = $afterObj ? $afterObj->toArray() : '';
        if(!$afterOrders){
           abort(404);
        }
        $afterProduct = [];
        $orderId = '';
        $currencyExchange = SettingCurrencyExchange::getSettingExchange();
        $curencyInfo = ['currency_code' => $afterOrders['currency_code'], 'rate' => $afterOrders['rate']];

        if ($afterOrders) {
            if (isset($afterOrders['orders_products']) && !empty($afterOrders['orders_products'])) {
                foreach ($afterOrders['orders_products'] as $k => $v) {

                    if(isset($v['goods']) && !empty($v['goods'])){
                        $afterProduct[$v['goods_id']]['goods_pictures'] = $v['goods']['goods_pictures'];
                        $afterProduct[$v['goods_id']]['sku'] = $v['goods']['sku'];
                        $afterProduct[$v['goods_id']]['product_name'] = $v['goods']['goods_name'];
                        $afterProduct[$v['goods_id']]['univalence'] = $v['univalence'];
                        $afterProduct[$v['goods_id']]['currency_code'] = $v['currency'];
                        $allow_refund_number = $v['delivery_number'] - $v['aftersale_refund_number'];
                        $afterProduct[$v['goods_id']]['delivery_number'] = $allow_refund_number??0;//可售后商品数量
//                        $afterProduct[$v['goods_id']]['delivery_number'] = $v['delivery_number'];//发货数量
//                        $afterProduct[$v['goods_id']]['aftersale_refund_number'] = $v['aftersale_refund_number'];//售后商品数量
                    }

                }
                $orderId = $v['order_id'];
            } else {
                return "该订单还未发货或者无可退货商品，无法申请售后";
            }
        }
        //获取仓库
        $warehouses = SettingWarehouse::getAllWarehousesByUserId($user_id);
        return view('Order/afterSales/addPaymentOrder', [
            'orderNum' => $orderNum,
            'currencyExchange' => $currencyExchange,
            'curencyInfo' => isset($curencyInfo) && !empty($curencyInfo) ? $curencyInfo : '',
            'orderId' => $orderId, 'afterProducts' => !empty($afterProduct) ? $afterProduct : '',
            'warehouse' => $warehouses]);
    }

    /**
     * @note
     * 售后单详情页
     * @return: array
     * @author: zt7837
     * @since: 2019/4/19
     */
    public function afterOrderDetail(Request $request)
    {
        $id = $request->input('id');

        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $detailAfterOrder = OrdersAfterSales::getAfterOrderDetail($id, $user_id);
        $detailAfterOrder = $detailAfterOrder ? $detailAfterOrder->toArray() : '';
        return view('Order/afterSales/afterSalesDetail', ['detailAfterOrder' => $detailAfterOrder]);
    }

    /**
     * @note
     * 售后单列表
     * @return: array
     * @author: zt7837
     * @since: 2019/4/17
     */
    public function ajaxGetAfterInfo(Request $request)
    {
        $data = isset($request->all()['info']) && !empty($request->all()['info']) ? $request->all()['info'] : '';
        $param['is_cancel'] = isset($data['is_cancel']) && !empty($data['is_cancel']) ? $data['is_cancel'] : '';//todo 是否取消
        $param['type'] = isset($data['type']) && !empty($data['type']) ? $data['type'] : '';
        $param['after_sale_code'] = isset($data['after_sale_code']) && !empty($data['after_sale_code']) ? $data['after_sale_code'] : '';
        $param['order_number'] = isset($data['order_number']) && !empty($data['order_number']) ? $data['order_number'] : '';
        $param['start_time'] = isset($data['start_time']) && !empty($data['start_time']) ? $data['start_time'] : '';
        $param['end_time'] = isset($data['end_time']) && !empty($data['end_time']) ? $data['end_time'] : '';
        $limit = $request->input('limit');
        $page = $request->input('page');

        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($CurrentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if (empty($shopsId)) {
                    $result ['code'] = 0;
                    $result ['msg'] = '未配置店铺权限';
                    return parent::layResponseData($result);
                }
                //店铺id
                $param ['source_shop'] = $shopsId;
            } else {
                $result ['code'] = 0;
                $result ['msg'] = '未配置店铺权限';
                return parent::layResponseData($result);
            }
        } else {
            $user_id = $CurrentUser->userId;
        }
        $afterInfo = OrdersAfterSales::getAfterOrder($param, $limit, $page, $user_id);
        return parent::layResponseData($afterInfo);
    }

    /**
     * @note
     * 售后单取消
     * @return: array
     * @author: zt7837
     * @since: 2019/4/22
     */
    public function cancel(Request $request)
    {
        DB::beginTransaction();
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $id = $request->input('id');
        $orderAfterObj = OrdersAfterSales::where(['user_id' => $user_id, 'id' => $id])->first();
        if (!$orderAfterObj || (floor($id) - $id) != 0) {
            return abort(404);
        }
        $orderAfterObj->is_cancel = OrdersAfterSales::AFTER_CANCEL;
        $updateRe = $orderAfterObj->save();
        $responseData ['code'] = 201;
        $responseData ['msg'] = '取消失败';
        if ($updateRe) {
            $responseData ['code'] = 200;
            $responseData ['msg'] = '取消成功';

            OrdersBillPayments::where('bill_code', $orderAfterObj->payments_code)->update(['status' => self::BILL_CANCEL]);

            if ($orderAfterObj->type == self::AF_ORDER_CANCELED) {
                Orders::where('id', $orderAfterObj->swap_order_id)->update(['status' => self::ORDER_CANCEL]);

            }
            //恢复退货数量
            $orderProduct = OrdersAfterSalesProducts::where('after_sale_id', $id)->first();
            $product = OrdersProducts::where(['order_id' => $orderAfterObj->order_id, 'goods_id' => $orderProduct->goods_id])->first();
            $restoreSum = $product['delivery_number'] + $orderProduct['number'];
            $result = OrdersProducts::where(['order_id' => $orderAfterObj->order_id, 'goods_id' => $orderProduct->goods_id])->update(['delivery_number' => $restoreSum]);
        }
        DB::commit();
        return parent::layResponseData($responseData);
    }

    /**
     * 确认换货物品已签收
     * Auth 12779
     * date 2019/05/06
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmReturnReceive(Request $request)
    {
        try {
            $id = $request->get('id');
            $result = OrdersAfterSales::where('id', $id)->update(['sales_return_status' => 3]);
            if (!$result) {
                $layData = ['code' => 201, 'msg' => '确认收货失败'];
                return $this->layResponseData($layData);
            }

            //库存管理
            $returnProducts = OrdersAfterSalesProducts::where('after_sale_id', $id)->get()->toArray();
            $returnProductsId = array_column($returnProducts, 'goods_id');
            $inStockProduct = WarehouseTypeGoods::whereIn('goods_id', $returnProductsId)->get()->toArray();
            $inStockSet = array_column($inStockProduct, 'available_in_stock', 'goods_id');
            $inStockIdSet = array_column($inStockProduct, 'id', 'goods_id');

            foreach ($returnProducts as $key => $val) {
                $stock = $val['number'] + $inStockSet[$val['goods_id']];
                WarehouseTypeGoods::where('id', $inStockIdSet[$val['goods_id']])->update(['available_in_stock' => $stock]);
            }

            $layData = ['code' => 0, 'msg' => '确认收货成功'];
            return $this->layResponseData($layData);
        } catch (\Exception $e) {
            $layData = ['code' => -1, 'msg' => '服务器出小差了'];
            return $this->layResponseData($layData);
        }

    }

    /**
     * 创建付款单
     * Auth 12779
     * date 2019/05/06
     * @param $CurrentUser object 当前用户的对象
     * @param $orderId string 订单ID
     * @param $currencyCode string 货币代码
     * @param $amount float 金额
     * @param $afterOrderId int 售后单ID
     * @param $type int 售后单类型
     * @return bool
     * @throws \League\Flysystem\Exception
     */
    private function createPayBill($CurrentUser, $orderId, $currencyCode, $amount, $afterOrderId, $type)
    {
        $order_nums = OrdersAfterSales::where('order_id', $afterOrderId)->count();
        if (empty($order_nums)) {
            $order_nums = 1;
        }

        $order = Orders::where('id', $orderId)->first();
        $billOption['created_man'] = $CurrentUser->userId;
        $billOption['order_id'] = $orderId;
        $billOption['amount'] = $amount;
        $billOption['currency_code'] = $currencyCode;
        $billOption['order_type'] = 3;
        $billOption['bill_code'] = CodeInfo::getACode(CodeInfo::AFTER_SALES_CODE, $order->order_number, $order_nums);
        $billOption['created_at'] = date('Y-m-d H:i:s');
        $billOption['updated_at'] = date('Y-m-d H:i:s');
        $billOption['type'] = $type;
        $bill = OrdersBillPayments::insert($billOption);
        if (!$bill) {
            return false;
        }
        return OrdersAfterSales::where('id', $afterOrderId)->update(['payments_code' => $billOption['bill_code']]);
    }

    /**
     * 创建换货单的订单
     * @param $order object 订单模型
     * @param $sku array 换货商品的SKU
     * @param $quantity array 换货商品的数量
     * @param $user_id string 用户父级ID
     * @param $current_user object 当前用户
     * @param $msg string 返回的错误信息
     * @return bool
     */
    private function createExchangeOrder($order, $sku, $quantity, $user_id, $current_user, &$msg)
    {
        $skuProduct = Goods::whereIn('sku', $sku)->where('user_id', $user_id)->get()->toArray();
        if (!$skuProduct) {
            $msg = '抱歉，查找商品失败';
            return false;
        }

        $skuOfSearchResult = array_column($skuProduct, 'sku');
        foreach ($sku as $key => $val) {
            if (!in_array($val, $skuOfSearchResult)) {
                $msg = 'SKU为' . $val . '的商品不存在，请检查SKU正确性';
                return false;
            }
        }

//        $rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($order->currency_code, $user_id);
        $rate = SettingCurrencyExchange::where('currency_form_code', $order->currency_code)->first();
        $rate = $rate['exchange_rate'];

        $orderParams['user_id'] = $user_id;
        $orderParams['created_man'] = $current_user->userId;
        $orderParams['platforms_id'] = $order->platforms_id;
        $orderParams['source_shop'] = $order->source_shop;
        $orderParams['order_number'] = 'S' . date('YmdHis') . rand(000, 999);
        $orderParams['plat_order_number'] = 'RMA' . $order->plat_order_number;
        $orderParams['type'] = self::ORDER_TYPE;
        $orderParams['platform_name'] = $order->platform_name;
        $orderParams['source_shop_name'] = $order->source_shop_name;
        $orderParams['picking_status'] = $order->picking_status;
        //根据需求文档描述，换货单总金额为0
        $orderParams['order_price'] = 0;
        $orderParams['currency_code'] = $order->currency_code;
        $orderParams['rate'] = $rate;
        $orderParams['freight'] = 0;
        $orderParams['currency_freight'] = $order->currency_freight;
        $orderParams['postal_code'] = $order->postal_code;
        $orderParams['country_id'] = $order->country_id;
        $orderParams['country'] = $order->country;
        $orderParams['province'] = $order->province;
        $orderParams['city'] = $order->city;
        $orderParams['mobile_phone'] = $order->mobile_phone;
        $orderParams['addressee_name'] = $order->addressee_name;
        $orderParams['addressee_email'] = $order->addressee_email;
        $orderParams['warehouse_id'] = $order->warehouse_id;
        $orderParams['warehouse'] = $order->warehouse;
        $orderParams['logistics_id'] = $order->logistics_id;
        $orderParams['logistics'] = $order->logistics;
        $orderParams['addressee'] = $order->addressee;
        $orderParams['addressee1'] = $order->addressee1;
        $orderParams['addressee2'] = $order->addressee2;
        $orderParams['mark'] = $order->mark;
        $orderParams['created_at'] = date('Y-m-d H:i:s');
        $orderParams['updated_at'] = date('Y-m-d H:i:s');
        $orderId = Orders::insertGetId($orderParams);

        //handle product
        $productParams = [];
//        var_dump($skuProduct);die;
        foreach ($skuProduct as $key => $val) {
            $productParams[] = [
                'created_man' => $current_user->userId,
                'user_id' => $user_id,
                'order_id' => $orderId,
                'goods_id' => $val['id'],
                'order_type' => self::ORDER_PRODUCT_TYPE,
                'product_name' => $val['goods_name'],
                'sku' => $val['sku'],
                'currency' => $orderParams['currency_code'],
                'buy_number' => $quantity[$key],
                'univalence' => 0,
                'rate' => $rate,
                'RMB' => 0,
                'created_at' => date('YmdHis'),
                'updated_at' => date('YmdHis')
            ];
        }
        $productResult = OrdersProducts::insert($productParams);
        if (!$productResult) {
            $msg = '生成订单失败，请重试';
            return false;
        }
        return $orderId;
    }

}