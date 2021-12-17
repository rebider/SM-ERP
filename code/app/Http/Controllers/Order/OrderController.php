<?php

namespace App\Http\Controllers\Order;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Common\Response;
use App\Auth\Models\Menus;
use App\Auth\Models\RolesShops;
use App\Http\Services\Order\OrdersService;
use App\Models\Goods;
use App\Models\Orders;
use App\Models\OrdersAmazon;
use App\Models\OrdersBillPayments;
use App\Models\OrdersInvoices;
use App\Models\OrdersLogs;
use App\Models\OrdersOriginal;
use App\Models\OrdersProducts;
use App\Models\OrdersRakuten;
use App\Models\OrdersTroublesRecord;
use App\Models\Platforms;
use App\Models\RulesOrderTrouble;
use App\Models\RulesOrderTroubleType;
use App\Models\SettingCountry;
use App\Models\SettingLogistics;
use App\Models\SettingLogisticsWarehouses;
use App\Models\SettingShops;
use App\Models\SettingWarehouse;
use App\Validates\OrderAddressValidate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Excel;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class OrderController
 * Notes: 平台订单控制器
 * @package App\Http\Controllers\Order
 * Data: 2019/3/11 13:46 * Author: zt7785
 */
class OrderController extends Controller
{
    /**
     * @var 便捷菜单
     */
    public  $shortcutMenus = [];

    public $orderFileName = '订单详情';

    public $addressFieldInterpreter = [
        'addressee_name'=>'收件人',
        'addressee_email'=>'买家email',
        'addressee'=>'地址1',
        'addressee1'=>'地址2',
        'mobile_phone'=>'电话',
        'phone'=>'手机',
        'warehouse_id'=>'仓库',
        'logistics_id'=>'物流方式',
        'country_id'=>'国家',
        'city'=>'城市',
        'province'=>'州/省',
        'postal_code'=>'邮编',
    ];
    public function __construct()
    {
        //便捷菜单
//        $this->shortcutMenus = Orders::getOrderShortcutMenu();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * Note: 订单列表
     * Data: 2019/3/11 13:46
     * Author: zt7785
     */
    public function orderIndex (Request $request) {

        //需要展示 左侧数据 1.异常数据条数 2.快捷菜单
        //1.客户信息
        //2.问题类型
        //3.来源平台：
        //4.来源平台：
        //5.物流方式:
        //6.仓库
        //便捷菜单
//        $responseData ['shortcutMenus'] = Orders::getOrderShortcutMenu();
        //问题类型
        $responseData ['troubles'] = RulesOrderTroubleType::getTroubleType(0);

        //店铺 获取客户 状态正常的店铺
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $param ['user_id'] = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if (!empty($shopsId)) {
                    $responseData ['shops'] = SettingShops::getShopsByShopsId($shopsId);
                } else {
                    $responseData ['shops'] = [];
                }
            } else {
                $responseData ['shops'] = [];
            }
        } else {
            $param ['user_id'] = $currentUser->userId;
            $responseData ['shops'] = SettingShops::getShopsByUserId($param ['user_id']);
        }
        //平台
        $responseData ['platforms']  = Platforms::getAllPlat();
        //物流方式
        $responseData ['logistics']  = SettingLogistics::getAllLogisticsByUserId($param ['user_id']);
        //仓库
        $responseData ['warehouses']  = SettingWarehouse::getAllWarehousesByUserId($param ['user_id']);
        $responseData ['is_problem'] = '';
        if ($request->has('is_problem') && $request->has('is_problem') == 'true') {
            $responseData ['is_problem'] = 'true';
        }

        $responseData ['unableToFindWarehouse'] = '';
        if ($request->has('unableToFindWarehouse') && $request->has('unableToFindWarehouse') == 'true') {
            $responseData ['unableToFindWarehouse'] = 'true';
        }

        $responseData ['unableToFindLogistics'] = '';
        if ($request->has('unableToFindLogistics') && $request->has('unableToFindLogistics') == 'true') {
            $responseData ['unableToFindLogistics'] = 'true';
        }

        return view('Order/orderIndex')->with($responseData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 订单列表搜索
     * Data: 2019/3/11 18:35
     * Author: zt7785
     */
    public function orderIndexSearch (Request $request) {

        // 仓库 跟踪号 派送运费 物流方式 配货成功才有记录
        $data = $request->all();
        $currentUser = CurrentUser::getCurrentUser();

        $offset = isset($data['page']) ? $data['page'] : 1 ;
        $limit = isset($data['limit']) ? $data['limit'] : 20;

        //配货状态
        $param['picking_status'] = (isset($data['data']['picking_status']) &&  !empty($data['data']['picking_status']))
            ? $data['data']['picking_status'] : '';

        //发货状态
        $param['deliver_status'] = (isset($data['data']['deliver_status']) &&  !empty($data['data']['deliver_status']))
            ? $data['data']['deliver_status'] : '';

        //问题类型
        $param['question_type'] = (isset($data['data']['question_type']) &&  !empty($data['data']['question_type']))
            ? $data['data']['question_type'] : '';

        //平台id
        $param['platforms_id'] = (isset($data['data']['platforms_id']) &&  !empty($data['data']['platforms_id']))
            ? $data['data']['platforms_id'] : '';

        //店铺id
        $param['source_shop'] = (isset($data['data']['source_shop']) &&  !empty($data['data']['source_shop']))
            ? $data['data']['source_shop'] : '';
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $permissionParam ['user_id'] = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if ($param['source_shop']) {
                    if (!in_array($param['source_shop'],$shopsId)) {
                        //未配置店铺 直接响应空
                        $result ['code'] = 400;
                        $result ['msg'] = '请求参数异常';
                        return parent::layResponseData($result);
                    }
                }
                //店铺id
                $permissionParam ['source_shop'] = $shopsId;
            } else {
                //未配置店铺 直接响应空
                $result ['code'] = 0;
                $result ['msg'] = '未配置店铺权限';
                return parent::layResponseData($result);
            }
        } else {
            $permissionParam ['user_id'] = $currentUser->userId;
        }

        //物流方式
        $param['logistics_id'] = (isset($data['data']['logistics_id']) &&  !empty($data['data']['logistics_id']))
            ? $data['data']['logistics_id'] : '';

        //仓库id
        $param['warehouse_id'] = (isset($data['data']['warehouse_id']) &&  !empty($data['data']['warehouse_id']))
            ? $data['data']['warehouse_id'] : '';

        //订单号
        $param['order_number'] = (isset($data['data']['order_number']) &&  !empty($data['data']['order_number']))
            ? $data['data']['order_number'] : '';


        //搜索类型 1:电商单号 2:物流跟踪号 3:收件人 4:收件人邮箱
        $param['search_type'] = (isset($data['data']['search_type']) &&  !empty($data['data']['search_type']))
            ? $data['data']['search_type'] : '';

        //搜索类型字段条件
        $param['search_type_code'] = (isset($data['data']['search_type_code']) &&  !empty($data['data']['search_type_code']))
            ? $data['data']['search_type_code'] : '';

        //时间类型 1: 下单时间 2:创建时间 3:付款时间 3:发货时间
        $param['times_type'] = (isset($data['data']['times_type']) &&  !empty($data['data']['times_type']))
            ? $data['data']['times_type'] : '';

        //开始时间
        $param['start_date'] = (isset($data['data']['start-date']) &&  !empty($data['data']['start-date']))
            ? $data['data']['start-date'] : '';

        //结束时间
        $param['end_date'] = (isset($data['data']['end-date']) &&  !empty($data['data']['end-date']))
            ? $data['data']['end-date'] : '';

        $param['is_problem'] = (isset($data['is_problem']) &&  !empty($data['is_problem']))
            ? $data['is_problem'] : '';

        if ((isset($data['unableToFindWarehouse']) &&  !empty($data['unableToFindWarehouse']))) {
            $param['problem'] = 3;
        } elseif ((isset($data['unableToFindLogistics']) &&  !empty($data['unableToFindLogistics']))) {
            $param['problem'] = 4;
        }

        $result = Orders::getOrdersDatas($param,$permissionParam,$offset,$limit);
        return parent::layResponseData($result);
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Note: 订单详情
     * Data: 2019/3/12 9:11
     * Author: zt7785
     */
    public function orderDetails(Request $request,$id)
    {
        if (empty($id))
        {
            abort(404);
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if (!in_array('order/orderIndex',$currentUser->userPermissions)) {
            abort(404);
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $orderInfo = Orders::getorderDetails($id,$user_id);

        if (empty($orderInfo))
        {
            abort(404);
        }

        //组装合单数据
        if(!empty($orderInfo['orders_troubles_record'])){
            $trouble_ids = array_column($orderInfo['orders_troubles_record'],'trouble_type_id');
            $trouble_key = array_search(4,$trouble_ids);
                $trouble_arr = explode(',',$orderInfo['orders_troubles_record'][$trouble_key]['trouble_desc']);
                $waitingMergeOrders = Orders::with('OrdersProducts')->whereIn('id',$trouble_arr)->where('status','<>',Orders::ORDER_STATUS_OBSOLETED)->get(['id','order_number','plat_order_number','platform_name','source_shop_name','order_price']);
                if(!$waitingMergeOrders->isEmpty()){
                    $waitingMergeOrders = $waitingMergeOrders->toArray();
                  $descStr = implode(array_column($waitingMergeOrders,'order_number'),',');
                    $orderInfo['waitDesc'] = $descStr;
                    //产品重量
                    foreach($waitingMergeOrders as $waitk => $waitv){
                        if(!empty($waitv['orders_products'])){
                            $weight = array_sum(array_column($waitv['orders_products'],'weight'));
                            $waitingMergeOrders[$waitk]['weight'] = $weight;
                        }
                    }
                    $orderInfo['merge_order'] = $waitingMergeOrders;
                } else {
                    $orderInfo['merge_order'] = [];
                }
            }

        if (is_bool(strpos($request->header('referer'),'orderIndex'))) {
            $edit = 0;
        } else {
            $edit = 1;
        }
        $result ['data'] = [];
        //处理问题
        $finishedProblem  = [];
        $waitingProblem  = [];
        if ($orderInfo ['orders_troubles_record']) {
            $orders_troubles_record = $orderInfo ['orders_troubles_record'];
            unset($orderInfo ['orders_troubles_record']);
            array_multisort(array_column($orders_troubles_record ,'dispose_status'),SORT_ASC,$orders_troubles_record);
            $dispose_status = array_column($orders_troubles_record ,'dispose_status');
            //因只有两种状态 所以升序找到最大的出现的key 截取
            //已处理
            $disposed_status_key = array_search(2,$dispose_status);
            //未处理
            $undispose_status_key = array_search(1,$dispose_status);

            $dispose_status = false;
            if (is_bool($disposed_status_key)) {
                $result ['data'] ['waitingProblems'] = $orders_troubles_record;
                $result ['data'] ['finishedProblems'] = [];
                $dispose_status = true;
            }
            if (is_bool($undispose_status_key)) {
                $result ['data'] ['waitingProblems'] = [];
                $result ['data'] ['finishedProblems'] = $orders_troubles_record;
                $dispose_status = true;
            }
            if (empty($dispose_status))
            {
                //0
                if (empty($disposed_status_key))
                {
                    $result ['data'] ['waitingProblems'] = [];
                    $result ['data'] ['finishedProblems'] = $orders_troubles_record ;
                } else {
                    $result ['data'] ['waitingProblems'] = array_slice($orders_troubles_record ,0,$disposed_status_key);
                    $result ['data'] ['finishedProblems'] = array_slice($orders_troubles_record , $disposed_status_key,count($orders_troubles_record) - 1);
                }
            }
        }
        //商品尺寸信息

        //根据平台 获取原始订单信息
        $orgInfo = OrdersOriginal::getOrgOrderInfoByOpt('order_id',$id);
        $result ['data'] ['orgOrder'] = $orgInfo;
        //日志
        $logs = [];
        if ($orderInfo ['orders_logs']) {
            $logs = $orderInfo ['orders_logs'];
            array_multisort(array_column($logs ,'id'),SORT_DESC,$logs);
            unset($orderInfo ['orders_logs']);
        }
        $result ['data'] ['logs'] = $logs;

        //商品
        $goods = [];
        if ($orderInfo ['orders_products']) {
            $goods = $orderInfo ['orders_products'];
            unset($orderInfo ['orders_products']);
        }
        $result ['data'] ['goods']  = $goods;
        //配货单
        $invoices = [];
        if ($orderInfo ['orders_invoices_value']) {
            $invoices = $orderInfo ['orders_invoices_value'];
            unset($orderInfo ['orders_invoices_value']);
        }

        $result ['data'] ['invoices']  = $invoices;

        $orders_invoices_products = array_column($invoices,'orders_invoices_product');
        $orders_invoices_product_sku = $orders_goods_already_picked = [];
        foreach ($orders_invoices_products as $orders_invoices_product) {
            $orders_invoices_product_sku  =  array_merge($orders_invoices_product_sku,array_column($orders_invoices_product,'goods'));
            foreach ($orders_invoices_product as $goods_already_picked) {
                $orders_goods_already_picked [$goods_already_picked ['goods']['sku']] = isset($orders_goods_already_picked [$goods_already_picked ['goods']['sku']]) ? $orders_goods_already_picked [$goods_already_picked ['goods']['sku']] + $goods_already_picked ['already_stocked_number'] : $goods_already_picked ['already_stocked_number'] ;
            }
        }
        $result ['data'] ['goods_already_picked']   =  $orders_goods_already_picked;
        $result ['data'] ['invoices_already']       = array_unique(array_column($orders_invoices_product_sku,'sku'));
        //订单状态逻辑
        $result ['data'] ['action']                 = Orders::orderStatusOpeLogic($orderInfo);
        //国家
        $result ['data'] ['countrys']               = SettingCountry::getAllCountry();
        //仓库
        $result ['data'] ['warehouses']             = SettingWarehouse::getAllWarehousesByUserId($user_id);
        //物流
        $result ['data'] ['logistics']              = SettingLogistics::getAllLogisticsByUserId($user_id);

        //国家转换
        $result ['data'] ['orderCountry'] = '';
        if (!empty($orderInfo['country_id'])) {
            $country_id_key = array_search($orderInfo ['country_id'],array_column($result ['data'] ['countrys'],'id'));
            if (!is_bool($country_id_key)) {
                $result ['data'] ['orderCountry'] = $result ['data'] ['countrys'] [$country_id_key] ['country_name'];
            }
        }
        return view('Order/orderDetails')->with(['data'=>$result ['data'],'orderInfo'=>$orderInfo,'edit'=>$edit]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * Note: 订单导出
     * Data: 2019/3/23 13:22
     * Author: zt7785
     */
    public function exportOrdersInfo (Request $request,Excel $excel) {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        // 仓库 跟踪号 派送运费 物流方式 配货成功才有记录
        $data = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        //配货状态
        $param['picking_status'] = (isset($data['picking_status']) &&  !empty($data['picking_status']))
            ? $data['picking_status'] : '';

        //发货状态
        $param['deliver_status'] = (isset($data['deliver_status']) &&  !empty($data['deliver_status']))
            ? $data['deliver_status'] : '';

        //问题类型
        $param['question_type'] = (isset($data['question_type']) &&  !empty($data['question_type']))
            ? $data['question_type'] : '';

        //平台id
        $param['platforms_id'] = (isset($data['platforms_id']) &&  !empty($data['platforms_id']))
            ? $data['platforms_id'] : '';

        //店铺id
        $param['source_shop'] = (isset($data['source_shop']) &&  !empty($data['source_shop']))
            ? $data['source_shop'] : '';

        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $permissionParam ['user_id'] = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if ($param['source_shop']) {
                    if (!in_array($param['source_shop'],$shopsId)) {
                        //未配置店铺 直接响应空
                        return redirect()->back()->with('errors','请求参数异常');
                    }
                }
                //店铺id
                $permissionParam ['source_shop'] = $shopsId;
            } else {
                return redirect()->back()->with('errors','未配置店铺权限');
            }
        } else {
            $permissionParam ['user_id'] = $currentUser->userParentId;
        }

        //物流方式
        $param['logistics_id'] = (isset($data['logistics_id']) &&  !empty($data['logistics_id']))
            ? $data['logistics_id'] : '';

        //仓库id
        $param['warehouse_id'] = (isset($data['warehouse_id']) &&  !empty($data['warehouse_id']))
            ? $data['warehouse_id'] : '';

        //订单号
        $param['order_number'] = (isset($data['order_number']) &&  !empty($data['order_number']))
            ? $data['order_number'] : '';


        //搜索类型 1:电商单号 2:物流跟踪号 3:收件人 4:收件人邮箱
        $param['search_type'] = (isset($data['search_type']) &&  !empty($data['search_type']))
            ? $data['search_type'] : '';

        //搜索类型字段条件
        $param['search_type_code'] = (isset($data['search_type_code']) &&  !empty($data['search_type_code']))
            ? $data['search_type_code'] : '';

        //时间类型 1: 下单时间 2:创建时间 3:付款时间 3:发货时间
        $param['times_type'] = (isset($data['times_type']) &&  !empty($data['times_type']))
            ? $data['times_type'] : '';

        //开始时间
        $param['start_date'] = (isset($data['start-date']) &&  !empty($data['start-date']))
            ? $data['start-date'] : '';

        //结束时间
        $param['end_date'] = (isset($data['end-date']) &&  !empty($data['end-date']))
            ? $data['end-date'] : '';

        //组装权限参数
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $permissionParam ['user_id'] = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if ($param['source_shop']) {
                    if (!in_array($param['source_shop'],$shopsId)) {
                        //未配置店铺 直接响应空
                        return redirect()->back()->with('errors','导出失败!请求参数异常');
                    }
                }
                //店铺id
                $permissionParam ['source_shop'] = $shopsId;
            } else {
                //未配置店铺 直接响应空
                return redirect()->back()->with('errors','导出失败!未配置店铺权限');
            }
        } else {
            $permissionParam ['user_id'] = $currentUser->userId;
        }

        $ordersInfo = Orders::getOrdersDatas($param,$permissionParam);
        if (empty($ordersInfo)) {
            return redirect()->back()->with('errors','无数据导出');
        }
        //2019年3月23日13:42:47 产品并不想要动态头
        $i = 0;

        foreach ($this->exportYield($ordersInfo) as $key => $goodsValue) {
            if ($goodsValue['orders_products']) {
                foreach ($goodsValue['orders_products'] as $value) {
                    //订单号 导入原始订单号
                    $printInfo[$i][] = $goodsValue['order_number'];
                    //电商单号 ERP生成
                    $printInfo[$i][] = $goodsValue['plat_order_number'];
                    //平台来源
                    $printInfo[$i][] = !empty($goodsValue['platforms']) ? $goodsValue['platforms']['name_CN'] :'';
                    //来源店铺 可能会删除店铺
                    $printInfo[$i][] = !empty($goodsValue['shops']) ? $goodsValue['shops']['shop_name'] :'';
                    //付款方式
//                    $printInfo[$i][] = $goodsValue['payment_method'];
                    //币种
                    $printInfo[$i][] = $goodsValue['currency_code'];
                    //订单总金额
                    $printInfo[$i][] = $goodsValue['order_price'];
                    //客户运费
//                    $printInfo[$i][] = !empty($goodsValue['orders_invoices']) ? $goodsValue['orders_invoices'] ['taotla_value'] : '';
//                    $printInfo[$i][] = !empty($goodsValue['orders_invoices_value']) ? array_sum(array_column($goodsValue['orders_invoices_value'],'taotla_value')) : '';
                    $printInfo[$i][] = !empty($goodsValue['freight']) ? $goodsValue['freight'] : '0.00';
                    //退款金额
//                    $printInfo[$i][] = !empty($goodsValue['orders_bill_payments']) ? $goodsValue['orders_bill_payments']['amount'] :'';
                    $printInfo[$i][] = !empty($goodsValue['orders_bill_payments']) ? array_sum(array_column($goodsValue['orders_bill_payments'],'amount')) : '0.00';
                    //TODO
                    //计费重量
                    //什么鬼
//                    $printInfo[$i][] = '';
                    //收件人手机
                    $printInfo[$i][] = $goodsValue['mobile_phone'];
//                    $printInfo[$i][] = $goodsValue['addressee_name'];
                    //下单时间
                    $printInfo[$i][] = $goodsValue['order_time'];
                    //付款时间
                    $printInfo[$i][] = $goodsValue['payment_time'];
                    //配货时间
                    $printInfo[$i][] = !empty($goodsValue['orders_invoices_many']) ? $goodsValue['orders_invoices_many'] [0] ['created_at'] : '';
                    //发货时间 上传物流跟踪号时间
                    $printInfo[$i][] = !empty($goodsValue['orders_invoices_many']) ? $goodsValue['orders_invoices_many'] [0] ['delivered_at'] : '';
                    //订单备注
                    $printInfo[$i][] = $goodsValue['mark'];
                    //订单类型 指的是
                    $orderType = Orders::modifier('type',$goodsValue['type']);
                    $printInfo[$i][] = $orderType;
                    //订单状态
                    $orderStatus = Orders::modifier('status',$goodsValue['status']);
                    $printInfo[$i][] = $orderStatus;
                    //配货状态
                    $picking_status = Orders::modifier('picking_status',$goodsValue['picking_status']);
                    $printInfo[$i][] = $picking_status;
                    //发货状态
                    $deliver_status = Orders::modifier('deliver_status',$goodsValue['deliver_status']);
                    $printInfo[$i][] = $deliver_status;
                    //拦截状态
                    $intercept_status = Orders::modifier('intercept_status',$goodsValue['intercept_status']);
                    $printInfo[$i][] = $intercept_status;
                    //退款状态
                    $sales_status = Orders::modifier('sales_status',$goodsValue['sales_status']);
                    $printInfo[$i][] = $sales_status;
                    //商品SKU
                    $printInfo[$i][] = $value['sku'];
                    //商品名称
                    $printInfo[$i][] = $value['product_name'];
                    //商品数量
                    $printInfo[$i][] = $value['buy_number'];
                    //商品售价
                    $printInfo[$i][] = $value['univalence'];
                    //商品总金额
                    $printInfo[$i][] = bcmul($value['buy_number'], $value['univalence'], 2);
                    $i++;
                }
            }
        }

//        $arr = ['订单号','电商单号','平台来源','来源店铺','付款方式','币种','订单总金额','客户运费','退款金额','计费重量','收件人手机','下单时间','付款时间','配货时间','发货时间','订单备注','订单类型','订单状态','配货状态','发货状态','拦截状态','退款状态','商品SKU','商品名称','商品数量','商品售价','商品总金额'];
//        $arr = ['订单号','电商单号','平台来源','来源店铺','付款方式','币种','订单总金额','客户运费','退款金额','收件人手机','下单时间','付款时间','配货时间','发货时间','订单备注','订单类型','订单状态','配货状态','发货状态','拦截状态','退款状态','商品SKU','商品名称','商品数量','商品售价','商品总金额'];
        $arr = ['订单号','电商单号','平台来源','来源店铺','币种','订单总金额','客户运费','退款金额','收件人手机','下单时间','付款时间','配货时间','发货时间','订单备注','订单类型','订单状态','配货状态','发货状态','拦截状态','退款状态','商品SKU','商品名称','商品数量','商品售价','商品总金额'];
        array_unshift($printInfo,$arr);
        $this->export($excel, $printInfo, $this->orderFileName,false,true);
    }


    /**
     * @author zt6650
     * 导出信息迭代器去
     * @param $yield_arr
     * @return \Generator
     */
    public function exportYield($yield_arr)
    {
        for ($i=0 ;$i<count($yield_arr) ; $i++) {
            yield $yield_arr[$i] ;
        }
    }
    /**
     * @author zt8067
     * 拦截订单
     * @param id
     * @return mixed
     */
    public function intercept(Request $request)
    {
        $params = $request->all();
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $results = (new OrdersService())->interceptOrderProcess($params, $user_id);
        return $results;
    }

    /**
     * @author zt8067
     * 取消订单
     * @param id
     * @return mixed
     */
    public function cancelOrder(Request $request)
    {
        $params = $request->all();
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $results = (new OrdersService())->cancelOrderProcess($params, $user_id);
        return $results;
    }


    /**
     * @author zt8067
     * 部分退款
     * @param id
     * @return mixed
     */
    public function partialRefund(Request $request)
    {
        set_time_limit(0);
        $params = $request->all();
        //商品数组整合
        if (!empty($params['goods'])) {
            $arr = [];
            foreach ($params['goods'] as $column => $v) {
                foreach ($v as $kk => $vv) {
                        $arr[$kk][$column] = $vv;
                }
            }
            unset($params['goods']);
            $params['goods'] = $arr;
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $results = (new OrdersService())->partialRefundProcess($params, $user_id);
        return $results;
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 结束问题
     * Data: 2019/5/16 8:56
     * Author: zt7785
     */
    public function finishProblem ($id,Request $request) {
        $problemid = $request->input('problem_id');
        if (empty($id) || empty($problemid)) {
            return AjaxResponse::isFailure('参数异常');
        }
        $orderProblemInfo = OrdersTroublesRecord::where('order_id',$id)->where('id',$problemid)->first(['question_type','dispose_status','order_id']);
        if (empty($orderProblemInfo)) {
            return AjaxResponse::isFailure('订单问题数据异常');
        }
        if ($orderProblemInfo->dispose_status == OrdersTroublesRecord::STATUS_DISPOSED) {
            return AjaxResponse::isFailure('订单问题数据异常');
        }
        try {
            //拦截类型
            if ($orderProblemInfo ['question_type'] == OrdersTroublesRecord::QUESTION_TYPE_INTERCEPT) {
                Orders::postDatas($orderProblemInfo['order_id'],['intercept_status'=>Orders::ORDER_INTERCEPT_STATUS_INITIAL]);
            }
            OrdersTroublesRecord::postDatas($problemid,['dispose_status'=>OrdersTroublesRecord::STATUS_DISPOSED,'manage_id'=>CurrentUser::getCurrentUser()->userId]);
            return AjaxResponse::isSuccess('结束问题成功');
        } catch (Exception $e) {
            return AjaxResponse::isFailure('结束问题失败');
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 订单编辑保存
     * Data: 2019/5/16 17:35
     * Author: zt7785
     */
    public function saveOrder ($id,Request $request) {
        if (empty($id))
        {
            return AjaxResponse::isFailure('参数异常');
        }

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

//        $orderInfo = Orders::with('OrdersProducts')->where('id',$id)->where('user_id',$user_id)->select(['picking_status','deliver_status','intercept_status','sales_status','status','rate','currency_code','id'])->first();
        $orderInfo = Orders::with('OrdersProducts')->where('id',$id)->where('user_id',$user_id)->first();
        if (empty($orderInfo)) {
            return AjaxResponse::isFailure('订单信息异常');
        }
        $orderInfo = $orderInfo->toArray();
        if ($orderInfo ['status'] == Orders::ORDER_STATUS_OBSOLETED) {
            return AjaxResponse::isFailure('已作废订单不允许修改');
        }
        if ($orderInfo ['status'] == Orders::ORDER_STATUS_FINISHED) {
            return AjaxResponse::isFailure('已完成订单不允许修改');
        }
        $pickArr = [Orders::ORDER_PICKING_STATUS_UNMATCH,Orders::ORDER_PICKING_STATUS_MATCHED_PART];
        if (!in_array($orderInfo['picking_status'],$pickArr) ) {
            return AjaxResponse::isFailure('订单状态异常');
        }
        $readly_pick_goods_id = $orders_goods_already_picked = [];
        if ($orderInfo ['picking_status'] == Orders::ORDER_PICKING_STATUS_MATCHED_PART) {
            //获取部分配货商品id
            $readly_pick_goods_info = OrdersInvoices::getPickGoodsByOrderId($id);
            foreach ($readly_pick_goods_info as $readly_pick) {
                $readly_pick_goods_id  =  array_merge($readly_pick_goods_id,array_column($readly_pick['orders_invoices_product'],'goods_id'));
                foreach ($readly_pick  ['orders_invoices_product'] as $goods_already_picked) {
                    $orders_goods_already_picked [$goods_already_picked ['goods_id']] = isset($orders_goods_already_picked [$goods_already_picked ['goods_id']]) ? $orders_goods_already_picked [$goods_already_picked ['goods_id']] + $goods_already_picked ['already_stocked_number'] : $goods_already_picked ['already_stocked_number'] ;
                }
            }
            $readly_pick_goods_id = array_unique($readly_pick_goods_id);
        }
        $param = $request->all();
        if (!empty($readly_pick_goods_id)) {
            $intersectArr = array_intersect ($param ['orderGoodsId'] ,$readly_pick_goods_id);
            if (count($intersectArr) != count($readly_pick_goods_id)) {
                //异常操作 已配货商品被删除
                return AjaxResponse::isFailure('商品信息异常');
            }
        }
        $current_time = date('Y-m-d H:i:s');
        $addresseeInfo = $orderUpdateData = $order_product_info = [];
        DB::beginTransaction();
        try {
            //未配货的情况下 允许编辑收货地址
            if ($orderInfo ['picking_status'] == Orders::ORDER_PICKING_STATUS_UNMATCH) {
                //收货信息处理
                $addresseeInfo ['addressee_name'] = $param ['addressee_name'];
                $addresseeInfo ['addressee_email'] = $param ['addressee_email'];
                $addresseeInfo ['addressee'] = $param ['addressee'];
                $addresseeInfo ['addressee1'] = $param ['addressee1'];
                $addresseeInfo ['mobile_phone'] = $param ['mobile_phone'];
                $addresseeInfo ['phone'] = $param ['phone'];

                $addresseeInfo ['warehouse_id'] = $param ['warehouse_id'];
                $addresseeInfo ['logistics_id'] = $param ['logistics_id'];

                $addresseeInfo ['country_id'] = $param ['country_id'];
                $addresseeInfo ['city'] = $param ['city'];
                $addresseeInfo ['province'] = $param ['province'];
                $addresseeInfo ['postal_code'] = $param ['postal_code'];

                //表单验证
                $validator = Validator::make(
                    $addresseeInfo,
                    OrderAddressValidate::getAddrRules(),
                    OrderAddressValidate::getAddrMessages(),
                    OrderAddressValidate::getAddrAttributes()
                );
                if ($validator->fails()) {
                    return AjaxResponse::isFailure('', $validator->errors()->all());
                }
                //客户运费
                if (!is_numeric($param ['freight'])) {
                    return AjaxResponse::isFailure('客户运费格式错误');
                }
                //选中仓库
                if (empty($addresseeInfo ['warehouse_id'])) {
                    $addresseeInfo ['warehouse_choose_status'] = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;
                } else {
                    $addresseeInfo ['warehouse_choose_status'] = $param ['warehouse_choose_status'] ? Orders::ORDER_CHOOSE_STATUS_CHECKED : Orders::ORDER_CHOOSE_STATUS_UNCHECKED ;
                }
                //选中物流
                if (empty($addresseeInfo ['logistics_id'])) {
                    $addresseeInfo ['logistics_choose_status'] = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;
                } else {
                    $addresseeInfo ['logistics_choose_status'] = $param ['logistics_choose_status'] ? Orders::ORDER_CHOOSE_STATUS_CHECKED : Orders::ORDER_CHOOSE_STATUS_UNCHECKED ;
                    //选中物流 但未选择仓库
                    if (empty($addresseeInfo ['warehouse_id'])) {
                        return AjaxResponse::isFailure('已选择物流方式,请选择仓库');
                    }
                }

                //仓库 物流关联关系
                if (!empty($addresseeInfo ['logistic_id']) && !empty($addresseeInfo ['warehouse_id'])) {
                    $logistics_warehouses = SettingLogisticsWarehouses::where([
                        ['logistic_id',$addresseeInfo ['logistic_id']],
                        ['warehouse_id',$addresseeInfo ['warehouse_id']],
                        ['user_id',$user_id],
                    ])->first(['id']);
                    if (empty($logistics_warehouses)) {
                        return AjaxResponse::isFailure('仓库物流关系异常');
                    }
                }
            }
            //商品数据处理
            $totalOrdersPrice = 0.00;
            $origOrdersGoodsId = array_column($orderInfo['orders_products'],'goods_id');
            //差集 删除
            $diff = array_diff ($origOrdersGoodsId,$param['orderGoodsId']);
            //日志
            $goods_base_str = "产品信息<br/>";
            $order_base_str = '订单信息<br/>';
            $address_base_str = '地址信息<br/>';
            $goods_str = $address_str = $order_str = $behavior_desc = '';
            if (!empty($diff)) {
                OrdersProducts::where('order_id',$id)->whereIn('goods_id',$diff)->where('is_deleted',OrdersProducts::ORDERS_PRODUCT_UNDELETED)->update(['is_deleted'=>OrdersProducts::ORDERS_PRODUCT_DELETED,'created_man'=>$currentUser->userId,'updated_at'=>$current_time]);
                $diffGoods = Goods::whereIn('id',$diff)->get(['sku'])->toArray();
                foreach ($diffGoods as $diffVal) {
                    $goods_str .= 'SKU:'.$diffVal ['sku'].'商品删除'."<br/>";
                }
            }

            bcscale(2);
            foreach ($param ['orderGoodsId'] as $k => $orderGoods) {
                $currentGoodsAmountPrice = 0.00;
                $orderGoodsUpdateData = [];
                if (!is_numeric($param['goods_nums'][$k]) || $param['goods_nums'][$k] <= 0 ) {
                    return AjaxResponse::isFailure('商品数量格式异常');
                }
                if (!is_numeric($param['goods_price'][$k]) || $param['goods_price'][$k] <= 0 ) {
                    return AjaxResponse::isFailure('商品价格格式异常');
                }
                //buy_number 购买数量更新 用于配货
                if (!empty($readly_pick_goods_id)) {
                    if (in_array($orderGoods,$readly_pick_goods_id)) {
                        if (isset($orders_goods_already_picked [$orderGoods])) {
                            if ($param['goods_nums'][$k] < $orders_goods_already_picked [$orderGoods]) {
                                return AjaxResponse::isFailure('商品数量小于已配货数量');
                            }
                        }
                        $orderGoodsUpdateData['buy_number']           = $param['goods_nums'][$k];
                        $orderGoodsUpdateData['univalence']           = $param['goods_price'][$k];
                        $currentGoodsAmountPrice                      = bcmul($orderGoodsUpdateData['buy_number'], $orderGoodsUpdateData['univalence']);
                        $orderGoodsUpdateData ['RMB']                 =   bcmul($currentGoodsAmountPrice, $orderInfo ['rate']);
                        if ($param['goods_nums'][$k] == $orders_goods_already_picked [$orderGoods]) {
                            continue;
                        }
                        $totalOrdersPrice = bcadd($totalOrdersPrice,$currentGoodsAmountPrice);
                        $orderGoodsUpdateData ['updated_at']          =   $current_time;
                        OrdersProducts::where('order_id',$id)->where('goods_id',$orderGoods)->where('is_deleted',OrdersProducts::ORDERS_PRODUCT_UNDELETED)->update($orderGoodsUpdateData);
                        $goods_str .= 'SKU:'.$param ['orderGoodsSKU'] [$k].'商品编辑,数量:由 '.$orders_goods_already_picked [$orderGoods].'变更为 '.$orderGoodsUpdateData['buy_number'].',单价:'.$orderGoodsUpdateData['univalence']."<br/>";
                        continue ;
                    }
                }

                if (in_array($orderGoods,$origOrdersGoodsId)) {
                    $orderGoodsUpdateData ['buy_number']            = $param['goods_nums'][$k];
                    $orderGoodsUpdateData ['univalence']            = $param['goods_price'][$k];
                    $currentGoodsAmountPrice                        = bcmul($orderGoodsUpdateData['buy_number'], $orderGoodsUpdateData['univalence']);
                    $orderGoodsUpdateData ['RMB']                   =   bcmul($currentGoodsAmountPrice, $orderInfo ['rate']);
                    $totalOrdersPrice = bcadd($totalOrdersPrice,$currentGoodsAmountPrice);
                    $trueKey = array_search($orderGoods,$origOrdersGoodsId);
                    if ($param['goods_nums'][$k] == $orderInfo['orders_products'] [$trueKey] ['buy_number'] && $param['goods_price'][$k] == $orderInfo['orders_products'] [$trueKey] ['univalence']) {
                        continue;
                    }
                    $orderGoodsUpdateData ['univalence']   = number_format($orderGoodsUpdateData['univalence'], 2, '.', '');
                    $orderGoodsUpdateData ['updated_at']            =   $current_time;
                    OrdersProducts::where('order_id',$id)->where('goods_id',$orderGoods)->where('is_deleted',OrdersProducts::ORDERS_PRODUCT_UNDELETED)->update($orderGoodsUpdateData);

                    if ($param['goods_nums'][$k] != $orderInfo['orders_products'] [$trueKey] ['buy_number'] && $param['goods_price'][$k] != $orderInfo['orders_products'] [$trueKey] ['univalence'] ) {
                        $goods_str .= 'SKU:'.$param ['orderGoodsSKU'] [$k].'商品编辑,数量:由 '.$orderInfo['orders_products'] [$trueKey] ['buy_number'].'变更为 '.$orderGoodsUpdateData['buy_number'].',单价:由'.$orderInfo['orders_products'] [$trueKey] ['univalence'].'变更为 '.$orderGoodsUpdateData['univalence']."<br/>";
                    } else if ($param['goods_nums'][$k] != $orderInfo['orders_products'] [$trueKey] ['buy_number'] && $param['goods_price'][$k] == $orderInfo['orders_products'] [$trueKey] ['univalence'] ) {
                        $goods_str .= 'SKU:'.$param ['orderGoodsSKU'] [$k].'商品编辑,数量:由 '.$orderInfo['orders_products'] [$trueKey] ['buy_number'].'变更为 '.$orderGoodsUpdateData['buy_number']."<br/>";
                    } else if ($param['goods_price'][$k] != $orderInfo['orders_products'] [$trueKey] ['univalence']  && $param['goods_nums'][$k] == $orderInfo['orders_products'] [$trueKey] ['buy_number']) {
                        $goods_str .= 'SKU:'.$param ['orderGoodsSKU'] [$k].'商品编辑,单价:由 '.$orderInfo['orders_products'] [$trueKey] ['univalence'].'变更为 '.$orderGoodsUpdateData['univalence']."<br/>";
                    }
                    continue ;
                }
                $goodsInfo = Goods::select('goods_name')->where('id',$orderGoods)->first();
                $order_product_info[$k]['created_man']          = $currentUser->userId;
                $order_product_info[$k]['user_id']              = $user_id;
                $order_product_info[$k]['order_id']             = $id;
                $order_product_info[$k]['goods_id']             = $orderGoods;
                $order_product_info[$k]['order_type']           = OrdersProducts::ORDERS_CWERP;
                $order_product_info[$k]['product_name']         = $goodsInfo['goods_name'];
                $order_product_info[$k]['is_deleted']           = OrdersProducts::ORDERS_PRODUCT_UNDELETED;
                $order_product_info[$k]['sku']                  = $param['orderGoodsSKU'][$k];
                $order_product_info[$k]['currency']             = $orderInfo['currency_code'];
                $order_product_info[$k]['buy_number']           = $param['goods_nums'][$k];
                $order_product_info[$k]['univalence']           = $param['goods_price'][$k];
                $order_product_info[$k]['rate']                 = $orderInfo ['rate'];
                $currentGoodsAmountPrice                        = bcmul($order_product_info[$k]['buy_number'], $order_product_info[$k]['univalence']);
                $order_product_info[$k]['RMB']                  = bcmul($orderInfo ['rate'], $currentGoodsAmountPrice);
                $order_product_info[$k]['created_at']           = $order_product_info[$k]['updated_at'] = $current_time;
                $totalOrdersPrice = bcadd($totalOrdersPrice,$currentGoodsAmountPrice);
            }
            //订单总金额 等于商品总金额 加客户运费
            $totalOrdersPrice = bcadd($totalOrdersPrice,$param ['freight']);
            $ordersData ['freight'] = $param ['freight'];
            $ordersData ['order_price'] = $totalOrdersPrice;
            $orderUpdateData = array_merge($ordersData,$addresseeInfo);
            //地址信息
            $deep_dark_arr = ['warehouse_id','logistics_id','country_id'];
            $warehouseModel = new SettingWarehouse();
            $logisticsModel = new SettingLogistics();
            $countryModel   = new SettingCountry();
            unset($addresseeInfo ['warehouse_choose_status'] ,$addresseeInfo ['logistics_choose_status']);
            foreach ($addresseeInfo as $addresseeInfoKey => $addresseeInfoVal) {
                if ($addresseeInfoVal != $orderInfo [$addresseeInfoKey]) {
                    if (in_array($addresseeInfoKey,$deep_dark_arr)) {
                        if (!empty($addresseeInfoVal)) {
                            if (!is_bool(strpos($addresseeInfoKey,'warehouse'))) {
                                $warehouse_name = $warehouseModel->where([
                                    ['user_id',$user_id],
                                    ['disable',$warehouseModel::ON],
                                    ['id',$addresseeInfoVal],
                                ])->first(['warehouse_name']);
                                if (empty($warehouse_name)) {
                                    return AjaxResponse::isFailure('仓库信息异常');
                                }
                                $address_str .= $this->addressFieldInterpreter [$addresseeInfoKey].' 由 :'.$orderInfo ['warehouse'].' 变更为 :'.$warehouse_name['warehouse_name']."<br/>";
                                $orderUpdateData ['warehouse'] = $warehouse_name['warehouse_name'];
                            }
                            if (!is_bool(strpos($addresseeInfoKey,'logistics'))) {
                                $logistic_name = $logisticsModel->where([
                                    ['user_id',$user_id],
                                    ['disable',$logisticsModel::LOGISTICS_STATUS_USING],
                                    ['id',$addresseeInfoVal],
                                ])->first(['logistic_name']);
                                if (empty($logistic_name)) {
                                    return AjaxResponse::isFailure('物流信息异常');
                                }
                                $address_str .= $this->addressFieldInterpreter [$addresseeInfoKey].' 由 :'.$orderInfo ['logistics'].' 变更为 :'.$logistic_name['logistic_name']."<br/>";
                                $orderUpdateData ['logistics'] = $logistic_name['logistic_name'];
                            }
                            if (!is_bool(strpos($addresseeInfoKey,'country'))) {
                                $country_name = $countryModel->where('id',$addresseeInfoVal)->first(['country_name']);
                                if (empty($country_name)) {
                                    return AjaxResponse::isFailure('国家信息异常');
                                }
                                $address_str .= $this->addressFieldInterpreter [$addresseeInfoKey].' 由 :'.$orderInfo ['country'].' 变更为 :'.$country_name['country_name']."<br/>";
                                $orderUpdateData ['country'] = $country_name['country_name'];
                            }
                        }
                    } else {
                        $address_str .= $this->addressFieldInterpreter [$addresseeInfoKey].' 由 :'.$orderInfo [$addresseeInfoKey].' 变更为 :'.$addresseeInfoVal."<br/>";
                    }
                }
            }
            //商品信息

            //商品价格
            Orders::postDatas($id,$orderUpdateData);
            if (!empty($order_product_info)) {
                OrdersProducts::insert($order_product_info);
                foreach ($order_product_info as $order_product_info_val) {
                    $order_product_info_val ['univalence']   = number_format($order_product_info_val['univalence'], 2, '.', '');
                    $goods_str .= 'SKU:'.$order_product_info_val ['sku'] .'商品添加,数量:'.$order_product_info_val['buy_number'].',单价:'.$order_product_info_val['univalence']."<br/>";
                }
            }
            if ($orderInfo ['freight'] != $param ['freight']) {
                $goods_str  .= '客户运费由: '.$orderInfo ['freight'].'变更为: '.$param ['freight'].'<br/>';
            }

            if ($orderInfo ['order_price'] != $totalOrdersPrice) {
                $order_str  = '订单总价由: '.$orderInfo ['order_price'].'变更为: '.$totalOrdersPrice.'<br/>';
            }

            if (!empty($goods_str)) {
                $behavior_desc .= $goods_base_str.$goods_str.'<br/>';
            }

            if (!empty($address_str)) {
                $behavior_desc .= $address_base_str.$address_str.'<br/>';
            }
            if (!empty($order_str)) {
                $behavior_desc .= $order_base_str.$order_str.'<br/>';
            }
            //日志
            if (!empty($behavior_desc)) {
                $orderLogsData ['created_man'] = $user_id;
                $orderLogsData ['order_id'] = $id;
                $orderLogsData ['behavior_types'] = OrdersLogs::LOGS_ORDERS_EDITED;
                $orderLogsData ['behavior_type_desc'] = OrdersLogs::ORDERS_LOGS_TYPE_DESC[$orderLogsData ['behavior_types']];
                $orderLogsData ['behavior_desc'] = $behavior_desc;
                $orderLogsData ['updated_at'] = $orderLogsData ['created_at'] = date('Y-m-d H:i:s');
                OrdersLogs::postDatas(0,$orderLogsData);
            }
            DB::commit();
            return AjaxResponse::isSuccess('保存成功');
        } catch (Exception $e) {
            DB::rollback();
            return AjaxResponse::isFailure('保存失败');
        }
    }

    /**
     * @note
     * 合并订单
     * @since: 2019/5/16
     * @author: zt7837
     * @return: array
     */
    public function orderTroublesMerge(Request $request){
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $responseData = ['code'=>0,'msg'=>'fail','data'=>''];
        $ids = $request->input('order_id');
        $cur_id = $request->input('cur_order_id');
        $problem_id = $request->input('problem_id');
        $orderProblem = OrdersTroublesRecord::where(['id'=>$problem_id])->first();
        if(!empty($ids)){
            $result = Orders::addMergeOrders($ids,$cur_id,$user_id,$problem_id);
            //问题已处理
            if(!$result){
                return parent::layResponseData($responseData);
            } else {
                if($result == 'error'){
                    $responseData['msg'] = 'error';
                    return parent::layResponseData($responseData);
                }
                $responseData['msg'] = 'success';
                $responseData['data'] = ['number'=>$result->order_number,'problem'=>$orderProblem];
                return parent::layResponseData($responseData);
            }
        }
    }

    /**
     * @note
     * 取消订单合并
     * 作废新 恢复旧订单
     * @since: 2019/5/22
     * @author: zt7837
     * @return: array
     */
    public function removeOrderMerge(Request $request){
        $order_id = $request->input('cur_order_id');
        $orderInfo = Orders::where('status','<>',Orders::ORDER_STATUS_OBSOLETED)->find(['id'=>$order_id]);
        if($orderInfo->isEmpty()){
            return parent::layResponseData(['code'=>0,'msg'=>'fail']);
        }
        $mergeIds = $orderInfo[0]['merge_orders_id'];
        //恢复旧订单
        $idsArr = explode(',',$mergeIds);
        $oldRe = Orders::whereIn('id',$idsArr)->where('status','=',Orders::ORDER_STATUS_OBSOLETED)->update(['status'=>Orders::ORDER_STATUS_UNFINISH]);
        //作废新订单
        $newRe = Orders::where('status','<>',Orders::ORDER_STATUS_OBSOLETED)->where(['id'=>$order_id])->update(['status'=> Orders::ORDER_STATUS_OBSOLETED]);
        //处理订单问题数据
        OrdersTroublesRecord::where(['order_id'=>$order_id])->delete();
        OrdersTroublesRecord::whereIn('order_id',$idsArr)->delete();
        if($newRe && $oldRe){
            return parent::layResponseData(['code'=>0,'msg'=>'success']);
        }
        return parent::layResponseData(['code'=>0,'msg'=>'fail']);
    }

    /**
     * @note
     * 无需合并
     * @since: 2019/5/23
     * @author: zt7837
     * @return: array
     */
    public function cancelOrderMerge(Request $request){
        $order_id = $request->input('cancel_order_id');
        if(!$order_id){
            abort(404);
        }

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $merge_order = OrdersTroublesRecord::where(['order_id'=>$order_id,'dispose_status'=>OrdersTroublesRecord::STATUS_DISPOSING,'trouble_type_id'=>OrdersTroublesRecord::STATUS_MERGE])->first();

        //删除相关 问题订单
        $desc = $merge_order->trouble_desc;
        $re = false;
        //以逗号分隔 做处理
        //判断空
        if($desc) {
            $desc_arr = explode(',',$desc);
            //订单号是唯一的
            $order_wh['user_id'] = $user_id;
            $order_wh['picking_status'] = Orders::ORDER_PICKING_STATUS_UNMATCH;
            $ids = Orders::whereIn('id',$desc_arr)->where($order_wh)->select('id')->get();
            if(!$ids->isEmpty()) {
                $ids_arr = $ids->toArray();
                $re = OrdersTroublesRecord::whereIn('order_id',$ids_arr)->where('trouble_type_id',OrdersTroublesRecord::STATUS_MERGE)->delete();
                Orders::whereIn('id',$ids_arr)->where(['user_id'=>$user_id,'merge_orders_id'=>''])->update(['merge_orders_id'=>'0,0']);
            }
        }

        if($re){
            return parent::layResponseData(['code'=>0,'msg'=>'success']);
        }
        return parent::layResponseData(['code'=>0,'msg'=>'fail']);
    }

}
