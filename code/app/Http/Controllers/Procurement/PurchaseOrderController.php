<?php

namespace App\Http\Controllers\Procurement;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Users;
use App\Console\Commands\GetAsnList;
use App\Console\Commands\GetInventory\SvcCall;
use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Models\ProcurementPlanGoods;
use App\Models\ProcurementPlans;
use App\Models\PurchaseGoodsBox;
use App\Models\PurchaseOrders;
use App\Models\SettingLogistics;
use App\Models\SettingWarehouse;
use App\Models\Suppliers;
use App\Models\WarehouseSecretkey;
use App\Models\WarehouseTypeGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * @var PurchaseOrders 采购单
     */
    protected $PurchaseOrder;

    /**
     * @var string
     */
    protected $purchaseOrderFileName = '采购单详情';

    public function __construct()
    {
        $this->PurchaseOrder = new PurchaseOrders();

    }

    /**
     * @description 采购单页面
     * @author zt7927
     * @data 2019/3/27 13:44
     * @return $this
     */
    public function index()
    {
        //侧边栏
        $responseData['shortcutMenus'] = ProcurementPlans::getProcurementShortcutMenu();

        //店铺 获取客户 状态正常的店铺
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        //物流方式
        $logistics = SettingLogistics::getAllLogisticsByUserId($user_id);
        //仓库
        $warehouse = SettingWarehouse::getAllWarehousesByUserId($user_id);

        return view('Procurement.PurchaseOrder.index', compact('warehouse', 'logistics'))
            ->with($responseData);
    }

    /**
     * @description 采购单查询
     * @author zt7927
     * @data 2019/3/27 15:31
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseOrderIndexSearch(Request $request)
    {
        $pageIndex = $request->get('page', 1);
        $pageSize = $request->get('limit', 20);
        $params = $request->get('data', []);
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $params ['user_id'] = $user_id;
        $collection = $this->PurchaseOrder->search($params);   //查询
        $re['count'] = $collection->count();
        $re['data'] = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->get()->toArray();
        return parent::layResponseData($re);

    }

    /**
     * @description 采购单详情
     * @author zt7927
     * @data 2019/3/27 16:35
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function purchaseOrderDetail($id)
    {
        if (empty($id)) {
            abort(404);
        }
        $purchaseOrder = $this->PurchaseOrder->purchaseOrderDetail($id);
        $procurementPlan = new ProcurementPlans();
        $supplier = new Suppliers();
        $goods = new Goods();
        $totalAmount = 0;
        $totalPrice = 0;
        foreach ($purchaseOrder['procurement_plan'] as $key => &$value){
            $totalAmount += $value['total_amount'];
            $totalPrice  += $value['total_price'];
            $procurementGoods = $procurementPlan->getProcurementData($value['id']);
            if (empty($procurementPlan)) {
                abort(404);
            }
            $purchaseOrder['procurementPlan'][$key] =  $procurementGoods;
            foreach ($procurementGoods['procurement_plan_goods'] as $k => $v) {
                $supplier = $supplier->getSupplierDataById($v['supplier_id']); //供应商
                $goods = $goods->getGoodsDataById($v['goods_id']);             //商品sku
                $procurementGoods['procurement_plan_goods'][$k]['sku'] = $goods['sku'];
                $procurementGoods['procurement_plan_goods'][$k]['supplier_name'] = $supplier['name'];
            }
            $purchaseOrder['goods'][$key] = $procurementGoods['procurement_plan_goods'];
        }
        //将采购单下的所有商品信息放入采购单下
        $re = [];
        foreach ($purchaseOrder['goods'] as $key => $value) {
            foreach ($value as $k => $v) {
                $re[] = $v;
            }
        }
        $purchaseOrder['goods'] = $re;

        return view('Procurement.PurchaseOrder.detail', compact('purchaseOrder','totalAmount','totalPrice'));
    }

    /**
     * @description 作废采购单（对应采购计划状态变为审核）
     * @author zt7927
     * @data 2019/3/28 9:47
     * @param Request $request
     * @return array
     */
    public function delPurchaseOrder(Request $request)
    {
        $id = $request->input('id', '');
        $re = $this->PurchaseOrder->delPurchaseOrder($id);
        if ($re) {
            return [
                'status' => 1,
                'msg' => '作废成功'
            ];
        }
        return [
            'status' => 0,
            'msg' => '作废失败'
        ];
    }

    /**
     * @description 审核采购单
     * @author zt7927
     * @data 2019/3/29 17:10
     * @param Request $request
     * @return array|bool
     */
    public function checkPurchaseOrder(Request $request)
    {
        $id = $request->input('id','');
        if (is_numeric($id) && $id > 0){
            $purchaseOrder = $this->PurchaseOrder->getPurchaseOrderByIdDB($id);
            if ($purchaseOrder->isEmpty()) {
                return [
                    'status' => 0,
                    'msg'  => '采购单数据异常'
                ];
            }
            $purchaseOrder = $purchaseOrder->toArray();
            if ($purchaseOrder[0]->type === 1){     //如果目的仓库是 速贸仓库 需要调用接口创建入库单
                $params['reference_no'] = $purchaseOrder[0]->order_no;    //采购单号
                $params['transit_type'] = 1;     //自发头程
                $params['warehouse_code'] = $purchaseOrder[0]->warehouse_code;  //仓库代码
                $params['shipping_method'] = $purchaseOrder[0]->logistic_code; //物流方式
                $params['tracking_number'] = $purchaseOrder[0]->tracking_no; //跟踪号
                $params['eta_date'] = $purchaseOrder[0]->get_time;       //预计到达日期
                //todo
                //2019年7月2日21:06:56 新增字段
                //verify 是否审核
                $params ['verify'] = 1;
                $purchaseGoodsBox = new PurchaseGoodsBox();
                $box = $purchaseGoodsBox->getGoodsBox($id);
                if (empty($box)) {
                    return [
                        'status' => 0,
                        'msg'  => '商品信息异常'
                    ];
                }
                foreach ($box as $key => &$value){
                    $params['items'][$key]['product_sku'] = $value['sku']; //sku
                    $params['items'][$key]['quantity'] = $value['quantity'];  //数量
                    $params['items'][$key]['box_no'] = $value['box_no'];   //箱号
                }
                $userWareInfo = WarehouseSecretkey::where('user_id', $purchaseOrder[0]->user_id)->first();
                if (empty($userWareInfo)) {
                    return [
                        'status' => 0,
                        'msg'  => '速贸仓库配置异常'
                    ];
                }
                $config['appToken'] = $userWareInfo->appToken ;
                $config['appKey'] = $userWareInfo->appKey ;
                $config['appUrl']= config('api.app_model') ? config('api.createAsn.appUrl') : config('api.createAsn.testAppUrl');

                try {  //调用接口创建入库单
                    $re = SvcCall::remoteCommand('createAsn', $params, $config);
                } catch (\Exception $e) {
                    Log::error($e);
                    return [
                        'status' => 0,
                        'msg'  => '接口异常'
                    ];
                }
                if ($re && $re['ask'] === 'Success'){
                    //更新采购单状态-审核  写入入库单号
                    $return = $this->PurchaseOrder->updateStatus($id, PurchaseOrders::CHECK_STATUS, $re['receiving_code']);
                    // 更改采购计划的仓库id
                    $goods = $this->PurchaseOrder->getPurchaseGoods($id);
                    for ($i = 0; $i < count($goods); $i++){
                        $procurement_plan = new ProcurementPlans();
                        $procurement_plan = $procurement_plan->where('id', $goods[$i]->procurement_plan_id)->update([
                            'warehouse_id' => $goods[$i]->warehouse_id
                        ]);
                    }
                    return [
                        'status' => 1,
                        'msg'  => '审核成功'
                    ];
                } elseif ($re && $re['ask'] === 'Failure'){
                    log::error($re['message']);
                    return [
                        'status' => 0,
                        'msg'  => '采购单审核失败：仓库接口请求异常!'.$re['message']
                    ];
                }
            } else {
                DB::beginTransaction();
                try{
                    $status= $this->PurchaseOrder->updateStatus($id, PurchaseOrders::COMPLETE);  //更新采购单状态-完成
                    $warehouseTypeGoods = new WarehouseTypeGoods();                                    //增加目的仓库中的可用库存数量
                    $goods = $this->PurchaseOrder->getPurchaseGoods($id);
                    $currentUser = CurrentUser::getCurrentUser();
                    if ($currentUser->userAccountType == AccountType::CHILDREN) {
                        $user_id = $currentUser->userParentId;
                    } else {
                        $user_id = $currentUser->userId;
                    }
                    $insertArr = [];
                    for ($j = 0; $j < count($goods); $j++){
                        $insertArr['created_man']          = $currentUser->userId;
                        $insertArr['user_id']              = $user_id;
                        $insertArr['goods_id']             = $goods[$j]->goods_id;
                        $insertArr['setting_warehouse_id'] = $goods[$j]->warehouse_id;
                        $insertArr['available_in_stock']   = $goods[$j]->amount;
                        $insertArr['purchase_inventory']   = 0;
                        $insertArr['created_at']           = date('Y-m-d H:i:s');
                        $insertArr['updated_at']           = date('Y-m-d H:i:s');

                        $warehouseTypeGoodsInfo = $warehouseTypeGoods->where('goods_id',$insertArr['goods_id'])
                            ->where('setting_warehouse_id',$insertArr['setting_warehouse_id'])->where('user_id',$user_id)->first(['id','available_in_stock','purchase_inventory']);
                        if ($warehouseTypeGoodsInfo){
                            //转采购后已经 减了 不需要再减 理解错误
                            $purchase_inventory = $warehouseTypeGoodsInfo->purchase_inventory - $insertArr['available_in_stock'];
                            $update = $warehouseTypeGoods->where('id',$warehouseTypeGoodsInfo->id)
                                ->update([
                                    'available_in_stock' => $insertArr['available_in_stock'] + $warehouseTypeGoodsInfo->available_in_stock,
                                    'purchase_inventory' =>  $purchase_inventory > 0 ? $purchase_inventory : 0 ,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                        } else {
                            $insert = $warehouseTypeGoods->insertGetId($insertArr);                       //增加目的仓库中的可用库存数量
                        }
                        //检测到采购单 采购计划仓库不一致的时候 需要同步仓库

                        //审核通过之后 采购库存未累加

                        $procurement_plan = new ProcurementPlans();                                       // 更改采购计划的仓库id
                        $procurement_plan = $procurement_plan->where('id', $goods[$j]->procurement_plan_id)->update([
                            'warehouse_id' => $goods[$j]->warehouse_id
                        ]);
                    }
                }catch (\Exception $exception){
                    DB::rollback();
                    return [
                        'status' => 0,
                        'msg'  => '审核失败'
                    ];
                };
                DB::commit();
                return [
                    'status' => 1,
                    'msg'  => '审核成功'
                ];
            }
        }
        return false;
    }

    /**
     * @description 导出采购单
     * @@author zt7927
     * @data 2019/3/28 16:00
     * @param Request $request
     * @param Excel $excel
     */
    public function exportPurchaseOrder(Request $request, Excel $excel)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        //获取要导出的数据
        $ids = explode(',', $_GET['ids']);
        $procurementPlanGoods = new ProcurementPlanGoods();
        $purchaseOrderGoods = $procurementPlanGoods->getPurchaseOrderGoods($ids); //以商品为维度获取要导出的数据
        $arr = ['自定义SKU', '采购单号','采购计划编号', '目的仓库', '物流方式', '跟踪号', '商品数量', '商品金额', '运费', '采购单状态'];
        if (empty($purchaseOrderGoods)) {
            abort(404);
        }
        $i = 0;
        foreach ($this->exportYield($purchaseOrderGoods) as $key => $value) {
            $printInfo[$i][] = $value->sku;
            $printInfo[$i][] = $value->order_no;
            $printInfo[$i][] = $value->procurement_no;
            $printInfo[$i][] = $value->warehouse_name;
            $printInfo[$i][] = $value->logistic_name;
            $printInfo[$i][] = $value->tracking_no;
            $printInfo[$i][] = $value->amount;
            $printInfo[$i][] = sprintf("%.2f", $value->price * $value->amount);
            $printInfo[$i][] = $value->freight;
            $printInfo[$i][] = $this->PurchaseOrder->getStatusName($value->status);
            $i++;
        }


        array_unshift($printInfo, $arr);
        $this->export($excel, $printInfo, $this->purchaseOrderFileName, false, false);
    }

    /**
     * @author zt6650
     * 导出信息迭代器去
     * @param $yield_arr
     * @return \Generator
     */
    public function exportYield($yield_arr)
    {
        for ($i = 0; $i < count($yield_arr); $i++) {
            yield $yield_arr[$i];
        }
    }

}