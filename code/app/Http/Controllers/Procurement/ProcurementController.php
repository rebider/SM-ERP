<?php

namespace App\Http\Controllers\Procurement;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Models\ProcurementPlanGoods;
use App\Models\ProcurementPlans;
use App\Models\PurchaseGoodsBox;
use App\Models\PurchaseOrders;
use App\Models\SettingLogistics;
use App\Models\SettingLogisticsWarehouses;
use App\Models\SettingWarehouse;
use App\Models\Suppliers;
use App\Models\WarehouseTypeGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcurementController extends Controller
{
    /**
     * @var ProcurementPlans 采购计划
     */
    protected $ProcurementPlans;

    public function __construct()
    {
        $this->ProcurementPlans = new ProcurementPlans();
    }

    /**
     * @description 采购计划初始页面
     * @author zt7927
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        //侧边栏
        $responseData['shortcutMenus'] = ProcurementPlans::getProcurementShortcutMenu();
        //供应商
        $suppliers = new Suppliers();
        //目的仓库
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $suppliers = $suppliers->getAllSuppliers($user_id);
        //仓库
        $warehouse = SettingWarehouse::getWarehouseByStatus($user_id);

        return view('Procurement.ProcurementPlan.index', compact('suppliers', 'warehouse'))
            ->with($responseData);
    }

    /**
     * @description 采购计划-搜索
     * @author zt7927
     * @data 2019/3/14 14:38
     * @param Request $request
     * @return object
     */
    public function procurementPlanIndexSearch(Request $request)
    {
        $pageIndex = $request->get('page', 1);
        $pageSize  = $request->get('limit', 20);
        $params    = $request->get('data', []);
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $params ['user_id'] = $user_id;
        $collection  = $this->ProcurementPlans->search($params);   //查询
        $re['count'] = $collection->count();
        $re['data']  = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->get()->toArray();
        return parent::layResponseData($re);
    }

    /**
     * @description 新增-采购计划页面
     * @author zt7927
     * @data 2019/3/18 10:27
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        //仓库
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //仓库
        $warehouse = SettingWarehouse::getWarehouseByStatus($user_id);
        //供应商
        $suppliers = new Suppliers();
        $suppliers = $suppliers->getAllSuppliers($user_id);
        //商品
        $goods = new Goods();
        $goods = $goods->getAllGoods($user_id);
        return view('Procurement.ProcurementPlan.add', compact('warehouse', 'suppliers','goods'));
    }

    /**
     * @description 新增-采购计划
     * @author zt7927
     * @data 2019/3/14 15:25
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        //验证数据
        $validator = Validator::make($request->all(),
            array(
                'warehouse_id' => 'required|integer',
            ),
            array(
                'required' => ':attribute 为必填项',
                'integer' => ':attribute 必须是数字',
            ),
            array(
                'warehouse_id' => '目的仓库',
            )
        );
        if ($validator->fails()) {
            return [
                'status' => 0,
                'msg' => '目的仓库为必填项'
            ];
        }
        $goods = $request->get('goods', '');
        if (empty($goods)) {
            return [
                'status' => 0,
                'msg' => '请选择商品sku'
            ];
        }
        $re = $this->ProcurementPlans->insertArr($request->all());
        if ($re) {
            return [
                'status' => 1,
                'msg' => '添加成功'
            ];
        }

        return [
            'status' => 0,
            'msg' => '添加失败'
        ];
    }

    /**
     * @description 新增-根据sku获取商品信息
     * @author zt7927
     * @data 2019/3/19 15:11
     * @param Request $request
     * @return mixed
     */
    public function getGoodsBySku(Request $request)
    {
        $sku = $request->get('sku', '');
        $warehouse_id = $request->get('warehouse_id', '');
        if (isset($sku) && ($sku !== '') && isset($warehouse_id) && ($warehouse_id !== '')) {
            $goods = new Goods();
            $goods = $goods->getGoodsBySku($sku, $warehouse_id);
            if ($goods) {
                $goods->amount = 1;                               //给商品数量默认值1
                $goods->in_transit_inventory = $goods->in_transit_inventory ?? 0;
                $goods->available_in_stock = $goods->available_in_stock ?? 0;
                if ($goods->status === Goods::STATUS_DRAFT) {      //商品为草稿状态
                    return [
                        'status' => 2,
                        'msg' => 'SKU不是已审核状态，请确认'
                    ];
                }
                return [
                    'status' => 1,
                    'msg' => '',
                    'data' => $goods,
                ];
            }
            return [
                'status' => 0,
                'msg' => 'SKU不存在，请确认'
            ];
        } else {
            return false;
        }
    }

    /**
     * @description 编辑-根据sku获取商品信息
     * @author zt7927
     * @data 2019/3/19 15:11
     * @param Request $request
     * @return mixed
     */
    public function getGoodsBySkuEdit(Request $request)
    {
        $sku = $request->get('sku', '');
        if (isset($sku) && ($sku !== '')) {
            $goods = new Goods();
            $goods = $goods->getGoodsBySkuEdit($sku);
            if ($goods) {
                $goods->amount = 1;                                //给商品数量默认值1
                $goods->totalPrice = $goods->preferred_price;      //给总采购金额默认值
                if ($goods->status === Goods::STATUS_DRAFT) {      //商品为草稿状态
                    return [
                        'status' => 2,
                        'msg' => 'SKU不是已审核状态，请确认'
                    ];
                }
                return [
                    'status' => 1,
                    'msg' => '',
                    'data' => $goods,
                ];
            }
            return [
                'status' => 0,
                'msg' => 'SKU不存在，请确认'
            ];
        } else {
            return false;
        }
    }

    /**
     * @description 改变采购计划状态--审核
     * @author zt7927
     * @data 2019/3/19 14:09
     * @param Request $request
     * @return array
     */
    public function checkProcurementPlan(Request $request)
    {
        $id = $request->get('id', 0);
        if (is_numeric($id) && ($id > 0)) {
            DB::beginTransaction();
            try{
                $currentUser = CurrentUser::getCurrentUser();
                if ($currentUser->userAccountType == AccountType::CHILDREN) {
                    $user_id = $currentUser->userParentId;
                } else {
                    $user_id = $currentUser->userId;
                }
                $re = $this->ProcurementPlans->changeProcurementPlanStatus($id);
                $goods = $this->ProcurementPlans->getProcurementDetail($id);
                $warehouseTypeGoods = new WarehouseTypeGoods();
                for ($i = 0; $i < count($goods['procurement_plan_goods']); $i++){
                    $insertArr['created_man']          = $currentUser->userId;
                    $insertArr['user_id']              = $user_id;
                    $insertArr['goods_id']             = $goods['procurement_plan_goods'][$i]['goods_id'];
                    $insertArr['setting_warehouse_id'] = $goods['warehouse_id'];
                    $insertArr['purchase_inventory']   = $goods['procurement_plan_goods'][$i]['amount'];
                    $insertArr['created_at']           = date('Y-m-d H:i:s');
                    $insertArr['updated_at']           = date('Y-m-d H:i:s');

                    $warehouseTypeGoodsInfo = $warehouseTypeGoods->where('goods_id',$insertArr['goods_id'])
                        ->where('setting_warehouse_id',$insertArr['setting_warehouse_id'])->where('user_id',$user_id)->first(['id','purchase_inventory']);
                    if ($warehouseTypeGoodsInfo){
                        $update = $warehouseTypeGoods->where('id',$warehouseTypeGoodsInfo->id)
                            ->update([
                                'purchase_inventory' => $insertArr['purchase_inventory'] + $warehouseTypeGoodsInfo->purchase_inventory,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        $insert = $warehouseTypeGoods->insertGetId($insertArr);                       //增加目的仓库中的采购库存数量
                    }
                }
            }catch(\Exception $exception){
                DB::rollback();
                return [
                    'status' => 0,
                    'msg' => '审核失败'
                ];
            };
            DB::commit();
            return [
                'status' => 1,
                'msg' => '审核通过'
            ];
        }
        return [
            'status' => 0,
            'msg' => '数据异常'
        ];
    }

    /**
     * @description 编辑采购计划
     * @author zt7927
     * @data 2019/3/25 16:50
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editProcurementPlan($id)
    {
        if (empty($id)) {
            abort(404);
        }
        $procurementPlan = $this->ProcurementPlans->getProcurementData($id);
        if (empty($procurementPlan)) {
            abort(404);
        }
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //仓库
        $warehouse = SettingWarehouse::getWarehouseByStatus($user_id);
        $suppliers = new Suppliers();
        $suppliers = $suppliers->getAllSuppliers($user_id);
        $goods = new Goods();
        $goods = $goods->getAllGoods($user_id);
        return view('Procurement.ProcurementPlan.edit', compact('procurementPlan', 'warehouse', 'suppliers','goods'));
    }

    /**
     * @description 保存编辑采购计划
     * @author zt7927
     * @data 2019/3/25
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function updateProcurementPlan(Request $request)
    {
        //验证数据
        $validator = Validator::make($request->all(),
            array(
                'warehouse_id' => 'required|integer',
            ),
            array(
                'required' => ':attribute 为必填项',
                'integer' => ':attribute 必须是数字',
            ),
            array(
                'warehouse_id' => '目的仓库',
            )
        );
        if ($validator->fails()) {
            return [
                'status' => 0,
                'msg' => '目的仓库为必填项'
            ];
        }
        $goods = $request->get('goods', '');
        if (empty($goods)) {
            return [
                'status' => 0,
                'msg' => '请选择商品sku'
            ];
        }
        $re = $this->ProcurementPlans->updateArr($request->all());
        if ($re) {
            return [
                'status' => 1,
                'msg' => '添加成功'
            ];
        }

        return [
            'status' => 0,
            'msg' => '添加失败'
        ];
    }

    /**
     * @description 采购计划详情
     * @author zt7927
     * @data 2019/3/19 10:00
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function procurementDetail($id)
    {
        if (empty($id)) {
            abort(404);
        }
        $procurementPlan = $this->ProcurementPlans->getProcurementDetail($id);       //采购计划信息
        return view('Procurement.ProcurementPlan.detail', compact('procurementPlan'));
    }

    /**
     * @description 采购计划详情-关联商品信息
     * @author zt7927
     * @data 2019/3/20 15:20
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function procurementPlanGoods($id)
    {
        if (empty($id)) {
            abort(404);
        }
        $goods = new Goods();
        $suppliers = new Suppliers();
        $procurementPlan = $this->ProcurementPlans->getProcurementData($id);              //采购计划信息
        if (empty($procurementPlan)) {
            abort(404);
        }
        foreach ($procurementPlan['procurement_plan_goods'] as $k => $v) {
            $goods     = $goods->getGoodsDataById($v['goods_id']);                        //商品信息
            $suppliers = $suppliers->getSupplierDataById($v['supplier_id']);              //供应商信息
            $procurementPlan['procurement_plan_goods'][$k]['goods_name']     = $goods['goods_name'];
            $procurementPlan['procurement_plan_goods'][$k]['goods_pictures'] = $goods['goods_pictures'];
            $procurementPlan['procurement_plan_goods'][$k]['goods_sku']      = $goods['sku'];
            $procurementPlan['procurement_plan_goods'][$k]['supplier_name']  = $suppliers['name'];
            //编辑时需要下列信息
            $procurementPlan['procurement_plan_goods'][$k]['sku'] = $goods['sku'];
            $procurementPlan['procurement_plan_goods'][$k]['preferred_price'] = $v['price'];
            $procurementPlan['procurement_plan_goods'][$k]['preferred_supplier_id'] = $v['supplier_id'];
            $procurementPlan['procurement_plan_goods'][$k]['id'] = $v['goods_id'];
            $procurementPlan['procurement_plan_goods'][$k]['totalPrice'] = sprintf("%.2f", $v['price'] * $v['amount']);
        }
        $data['data'] = $procurementPlan['procurement_plan_goods'];
        $data['count'] = count($data['data']);
        return parent::layResponseData($data);
    }

    /**
     * @description 删除采购计划
     * @author zt7927
     * @data 2019/3/19 11:41
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function delProcurementPlan(Request $request)
    {
        $id = $request->get('id', 0);
        if (isset($id) && ($id > 0)) {
            $re = $this->ProcurementPlans->delProcurementPlan($id);
            if ($re) {
                return [
                    'status' => 1,
                    'msg' => '删除成功',
                ];
            }
            return [
                'status' => 0,
                'msg' => '删除失败'
            ];
        }
        return [
            'status' => 0,
            'msg' => '数据异常'
        ];
    }

    /**
     * @description 采购计划转采购单页面
     * @author zt7927
     * @data 2019/3/20 17:43
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function procurementPlanToOrder(Request $request)
    {
        $ids = $request->get('ids');
//        $idArr = explode(',',$ids);
//        $checkStatus = $this->ProcurementPlans->whereIn('id',$idArr)->where ('status',$this->ProcurementPlans::CHECK_STATUS)->count();
//        if ($checkStatus != count($idArr)) {
//            abort(404);
//        }
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //仓库
        $warehouses = SettingWarehouse::getWarehouseByStatus($user_id);
        $logistics = SettingLogistics::getLogisticsByStatus($user_id);
        return view('Procurement.ProcurementPlan.order', compact('ids', 'warehouses', 'logistics'));
    }

    /**
     * @description 获取采购商品
     * @author zt7927
     * @date 2019/4/4 17:28
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProcurementGoods(Request $request)
    {
        $ids = $request->input('ids','');
        //查出采购商品
        $procurementPlanGoods = new ProcurementPlanGoods();
        $goods = $procurementPlanGoods->getProcurementGoodsByIds($ids);
        foreach ($goods as &$v){
            $v->box_no = 1;
        }

        $data['data'] = $goods;
        $data['count'] = count($data['data']);
        return parent::layResponseData($data);
    }

    /**
     * @description 添加采购单数据（由采购计划转变过来）
     * @author zt7927
     * @data 2019/3/21 13:49
     * @param Request $request
     * @return array
     */
    public function createProcurementOrder(Request $request)
    {
        $params = $request->all();
        if (empty($params['ids'])) {
            return [
                'status' => 0,
                'msg' => '请选择采购计划'
            ];
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'status' => 0,
                'msg' => '用户信息过期,请重新登录'
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $procurementPlanGoods = new ProcurementPlanGoods();
        $goods = $procurementPlanGoods->getProcurementGoodsByIds($params['ids']);
        $ids = explode(',', $params['ids']);                      //采购计划ids
        $procurementPlansInfos = $this->ProcurementPlans->getProcurementPlan($ids);         //查询采购计划状态
        if (empty($procurementPlansInfos)) {
            return [
                'status' => 0,
                'msg' => '采购计划信息异常'
            ];
        }
        $statusArr = array_column($procurementPlansInfos,'status');
        if (in_array(ProcurementPlans::DRAFT_STATUS, $statusArr) ||
            in_array(ProcurementPlans::WAIT_PROCUREMENT, $statusArr)) {   //草稿状态 转采购状态
            return [
                'status' => 0,
                'msg' => '包含非审核状态下的采购计划，请确认'
            ];
        }
        $arrRes = [];
        foreach ($params['box'] as $key => $value){
            if (array_key_exists($value['sku'] , $arrRes)) {
                $arrRes[$value['sku']] += $value['amount'];
            } else {
                $arrRes[$value['sku']] = (int)$value['amount'];
            }
        }
        $data = array_column($goods,'amount','sku');
        $re = array_diff_assoc($data,$arrRes);  //比对两个数组返回差异
        $key = array_keys($re);
        if (count($key) > 0){
            $msg = implode(',',$key);
            return[
              'status' => 0,
              'msg' => $msg.',装箱数量不等于采购数量！'
            ];
        }

        //验证数据
        $validator = Validator::make($request->all(),
            array(
                'warehouse_id' => 'required|integer',
                'box_number' => 'required|integer',
                'get_time' => 'required|date',
                'logistics_id' => 'required|integer',
                'freight' => 'nullable|numeric'
            ),
            array(
                "required" => ":attribute不能为空",
                "integer" => ":attribute不正确",
                "date" => ":attribute格式不正确",
                "numeric" => ":attribute请输入数字"
            ),
            array(
                'warehouse_id' => '目的仓库',
                'get_time' => '预计到达日期',
                'logistics_id' => '物流方式',
                'freight' => '运费',
                'box_number' => '箱数',
            )
        );
        if ($validator->fails()) {
            $message = $validator->messages();
            return [
                'status' => 1,
                'msg' => $message
            ];
        }

        DB::beginTransaction();
        try {
            $purchaseOrders = new PurchaseOrders();
            $purchaseGoodsBox = new PurchaseGoodsBox();
            //todo
            //2019年7月2日17:50:00
            //检测到仓库变更 将重新写一份warehouse_type_goods数据
            $params ['created_man'] = $currentUser->userId;
            $params ['user_id'] = $user_id;
            $re = $purchaseOrders->insertArr($params);  //新增采购单后 采购计划状态变为转采购 写入采购单号 采购单id  仓库商品的采购库存归零
            $order_no = $purchaseOrders->getOrderNoById($re);
            $procurementPlan = $this->ProcurementPlans->updatePurchaseToProcurementPlan($ids, $re, $order_no->order_no);//采购计划-仓库商品的采购库存归零
            $box = $purchaseGoodsBox->insertData($params['box'],$re);

            //事务内可能没数据
            foreach ($procurementPlansInfos as $procurementPlansInfo) {
                if ($procurementPlansInfo ['warehouse_id'] != $params ['warehouse_id'] ) {
                    foreach ($procurementPlansInfo ['procurement_plan_goods'] as $procurement_plan_goods ) {
                        $wareInsertData = [];
                        $wareGoodsInfo = WarehouseTypeGoods::where([
                            'user_id'=>$procurementPlansInfo ['user_id'],
                            'goods_id'=>$procurement_plan_goods ['goods_id'],
                            'setting_warehouse_id'=>$params ['warehouse_id'],
                        ])->first(['purchase_inventory','id']);
                        if ($wareGoodsInfo) {
                            WarehouseTypeGoods::where('id',$wareGoodsInfo->id)->update(['purchase_inventory'=>$wareGoodsInfo->purchase_inventory + $procurement_plan_goods ['amount'] ,'updated_at'=>date('Y-m-d H:i:s')]);
                        } else {
                            $wareInsertData ['created_man'] = $currentUser->userId;
                            $wareInsertData ['user_id'] = $user_id;
                            $wareInsertData ['setting_warehouse_id'] = $params ['warehouse_id'];
                            $wareInsertData ['goods_id'] = $procurement_plan_goods ['goods_id'];
                            $wareInsertData ['purchase_inventory'] = $procurement_plan_goods ['amount'];
                            $wareInsertData ['in_transit_inventory'] = 0;
                            $wareInsertData ['drop_shipping'] = 0;
                            $wareInsertData ['available_in_stock'] = 0;
                            $wareInsertData ['updated_at'] = $wareInsertData ['created_at'] = date('Y-m-d H:i:s');
                            WarehouseTypeGoods::insert($wareInsertData);
                        }
                        //更换仓库之后 源仓库商品采购计划 减采购库存
                        WarehouseTypeGoods::where('goods_id', $procurement_plan_goods ['goods_id'])
                            ->where('setting_warehouse_id', $procurementPlansInfo ['warehouse_id'])->where('user_id',$user_id)->decrement('purchase_inventory',$procurement_plan_goods ['amount']);
                    }

                }
            }

            if($re && $procurementPlan && $box){
                DB::commit();
                return [
                    'status' => 2,
                    'msg' => '添加成功'
                ];
            }else{
                DB::rollback();
                return[
                    'status' => 0,
                    'msg' => '添加失败'
                ];
            }
        } catch (\Exception $exception) {
            dd($exception);
            DB::rollback();
            return[
              'status' => 0,
              'msg' => '添加失败'
            ];
        }
    }

    public function getLogistics(Request $request)
    {
        $id = $request->get('id');
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $rows = SettingLogisticsWarehouses::where('setting_logistics_warehouses.warehouse_id', $id)
            ->where('setting_logistics_warehouses.user_id', $user_id)
            ->where('setting_logistics.is_show', SettingLogistics::LOGISTIC_SHOW)
            ->where('setting_logistics.disable', SettingLogistics::LOGISTICS_STATUS_USING)
            ->join('setting_logistics', 'setting_logistics.id', '=', 'setting_logistics_warehouses.logistic_id')
            ->select('setting_logistics.*')
            ->get()->toArray();
        return $this->layResponseData(['code' => 0, 'msg' => 'succeed', 'data' => $rows]);
    }
}
