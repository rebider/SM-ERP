<?php

namespace App\Http\Controllers\Inventory;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Goods;
use App\Models\InventoryAllocation;
use App\Models\OrdersInvoices;
use App\Models\OrdersInvoicesProducts;
use App\Models\ProcurementPlans;
use App\Models\SettingWarehouse;
use App\Models\WarehouseTypeGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;

class InventoryController extends Controller
{
    /**
     * @var Inventory-库存管理
     */
    protected $Inventory;

    public function __construct()
    {

    }

    /**
     * @description 库存查询首页
     * @author zt7927
     * @date 2019/4/11 13:50
     * @return $this
     */
    public function inventoryIndex()
    {
        //侧边栏
        $responseData['shortcutMenus'] = ProcurementPlans::getProcurementShortcutMenu();
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //仓库
        $warehouse = SettingWarehouse::getWarehouseByStatus($user_id);
        //商品
        $goods = new Goods();
        $goods = $goods->getAllGoods($user_id);

        return view('InventoryManage.inventoryIndex', compact('warehouse','goods'))->with($responseData);
    }

    /**
     * @description 库存查询搜索
     * @author zt7927
     * @date 2019/4/10 16:59
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventoryIndexSearch(Request $request)
    {
        $pageIndex = $request->get('page', 1);
        $pageSize = $request->get('limit', 20);
        $params = $request->get('data', []);
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $warehouseTypeGoods = new WarehouseTypeGoods();
        $params ['user_id'] = $user_id;
        $collection = $warehouseTypeGoods->search($params);   //查询
        $re['count'] = $collection->count();
        $re['data'] = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->get()->toArray();
        $goodsModel = new Goods();
        $category = new Category();
        $OrdersInvoicesProducts = new OrdersInvoicesProducts();
        //可售库存 可用库存 - 待配货(未作废配货单)
        foreach ($re['data'] as $key => $value){
            $goods = $goodsModel->getGoodsDataById($value ->goods_id);
            if ($goods) {
                $category1 = $category->getCategoriesById($goods->category_id_1);
                if ($category1) {
                    $re['data'][$key]->category1 = $category1->name;
                } else {
                    $re['data'][$key]->category1 = '';
                }
                $category2 = $category->getCategoriesById($goods->category_id_2);
                if ($category2) {
                    $re['data'][$key]->category2 = $category2->name;
                } else {
                    $re['data'][$key]->category2 = '';
                }
                $category3 = $category->getCategoriesById($goods->category_id_3);
                if ($category3) {
                    $re['data'][$key]->category3 = $category3->name;
                } else {
                    $re['data'][$key]->category3 = '';
                }
            }
            $invoices_inv = $OrdersInvoicesProducts->with('OrdersInvoices')->whereHas('OrdersInvoices',function ($query) use($value) {
                //配货单状态正常 为指定仓库 且未发货
                        $query->where('invoices_status',OrdersInvoices::ENABLE_INVOICES_STATUS);
                        $query->where('warehouse_id',$value ->setting_warehouse_id);
                        $query->where('delivery_status',OrdersInvoices::DELIVERY_STATUS_NO);
            })->where(['user_id'=>$user_id,'goods_id'=>$value ->goods_id])->sum('already_stocked_number');
            if ($invoices_inv) {
                $warehouseTypeGoods::where([
                    'id'=>$value ->id,
                ])->update(['drop_shipping' => $invoices_inv]);
            }
//            if ($invoicesInfo->isEmpty()) {
//                $invoices_inv = 0 ;
//            } else {
//                $already_stocked_number = $buy_number = 0 ;
//                $invoicesInfo = $invoicesInfo->toArray();
//                foreach ($invoicesInfo as $invoicesInfoKey => $invoicesInfoVal) {
//                    //已发货累加
//                    $already_stocked_number += $invoicesInfoVal['already_stocked_number'];
//                    $buy_number = $invoicesInfoVal ['buy_number'];
//                }
//                //购买数量减去发货数量
//                $invoices_inv =  $buy_number - $already_stocked_number;
//                $warehouseTypeGoods::where([
//                    'id'=>$value->id,
//                ])->update(['drop_shipping' => $invoices_inv]);
//            }
            $re['data'][$key]->drop_shipping = $invoices_inv;
        }
        return parent::layResponseData($re);
    }

    /**
     * @description 库存查询--导出
     * @author zt7927
     * @date 2019/4/11 14:35
     * @param Request $request
     * @param Excel $excel
     */
    public function exportInventory(Request $request,Excel $excel)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        //获取要导出的数据
        $ids = explode(',', $_GET['ids']);             //仓库商品id
        $warehouseTypeGoods = new WarehouseTypeGoods();
        $warehouseTypeGoods = $warehouseTypeGoods->getWarehouseTypeGoodsByIds($ids);

        $i = 0;
        foreach ($this->exportYield($warehouseTypeGoods) as $key => $value) {
            $printInfo[$i][] = $value->warehouse_name;
            $printInfo[$i][] = $value->sku;
            $printInfo[$i][] = $value->goods_name;
            $printInfo[$i][] = $value->purchase_inventory;
            $printInfo[$i][] = $value->in_transit_inventory;
            $printInfo[$i][] = $value->drop_shipping;
            $printInfo[$i][] = $value->available_in_stock;
            $printInfo[$i][] = ($value->available_in_stock - $value->drop_shipping);
            $printInfo[$i][] = $value->updated_at;
            $i++;
        }

        $arr = ['所在仓库', '自定义SKU', '产品名称', '采购库存', '在途库存', '待发货', '可用库存', '可售库存', '最后入库时间'];
        if (empty($warehouseTypeGoods)) {
            $re = [];
            array_unshift($re, $arr);
            $this->export($excel, $re, '库存详情', false, false);
        }
        array_unshift($printInfo, $arr);
        $this->export($excel, $printInfo, '库存详情', false, false);
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

    /**
     * @description 库存分配首页
     * @author zt7927
     * @date 2019/4/11 15:12
     * @return $this
     */
    public function inventoryAllocation()
    {
        //侧边栏
        $responseData['shortcutMenus'] = ProcurementPlans::getProcurementShortcutMenu();
        //仓库
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //仓库
        $warehouse = SettingWarehouse::getWarehouseByStatus($user_id);
        //商品
        $goods = new Goods();
        $goods = $goods->getAllGoods($user_id);

        return view('InventoryManage.inventoryAllocation', compact('warehouse','goods'))->with($responseData);
    }

    /**
     * @description 库存分配搜索
     * @author zt7927
     * @date 2019/4/11 15:44
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventoryAllocationSearch(Request $request)
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
        $inventoryAllocation = new InventoryAllocation();
        $collection = $inventoryAllocation->search($params);   //查询
        $re['count'] = $collection->count();
        $re['data'] = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->get()->toArray();
        foreach ($re['data'] as $key => $value){
            $goods = new Goods();
            $category = new Category();
            $goods = $goods->getGoodsDataById($value->goods_id);
            $category1 = $category->getCategoriesById($goods->category_id_1);
            if ($category1) {
                $re['data'][$key]->category1 = $category1->name;
            } else {
                $re['data'][$key]->category1 = '';
            }
            $category2 = $category->getCategoriesById($goods->category_id_2);
            if ($category2) {
                $re['data'][$key]->category2 = $category2->name;
            } else {
                $re['data'][$key]->category2 = '';
            }
            $category3 = $category->getCategoriesById($goods->category_id_3);
            if ($category3) {
                $re['data'][$key]->category3 = $category3->name;
            } else {
                $re['data'][$key]->category3 = '';
            }
        }
        return parent::layResponseData($re);
    }

    /**
     * @description 新增库存分配页面
     * @author zt7927
     * @date 2019/4/12 9:35
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addAllocationIndex()
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
        //商品
        $goods = new Goods();
        $goods = $goods->getAllGoods($user_id);

        return view('InventoryManage.addInventoryAllocation',compact('goods','warehouse'));
    }

    /**
     * @description 新增库存分配
     * @author zt7927
     * @date 2019/4/12 9:34
     * @param Request $request
     * @return array
     */
    public function addAllocation(Request $request)
    {
        $params = $request->input('params',[]);
        $currentUser = CurrentUser::getCurrentUser();
        if($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        if ($params['sku'] && $params['warehouse_id'] && is_numeric($params['warehouse_id'])
            && is_numeric($params['lotte'])  && is_numeric($params['amazon'])){
            $inventoryAllocation = new InventoryAllocation();
            $goods = new Goods();
            $warehouseTypeGoods = new WarehouseTypeGoods();
            $goods = $goods->getGoodsIdBySku($params['sku'],$user_id);  //获取商品id
            if ($goods){
                $params['goods_id'] = $goods->id;
//                $warehouseTypeGoods = $warehouseTypeGoods->searchByGoodsIdAndWarehouseId($params['goods_id'], $params['warehouse_id'],$user_id); //查询仓库商品是否存在
                $warehouseTypeGoods = SettingWarehouse::where(['user_id'=>$user_id,'disable'=> SettingWarehouse::ON,'id'=>$params['warehouse_id']])->first(['id']);
                if (!$warehouseTypeGoods){
                    return [
                        'status' => 0,
                        'msg'    => '请填写正确的仓库'
                    ];
                }

                $allocation = $params['lotte'] + $params['amazon'];
                if ($allocation > 1){
                    return [
                        'status' => 0,
                        'msg'    => '分配比例之和不能大于1'
                    ];
                }

                $return = $inventoryAllocation->searchAllocation($params['goods_id'], $params['warehouse_id'],$user_id);   //查询分配比例是否存在
                if ($return){
                    return [
                        'status' => 0,
                        'msg'    => '该仓库下的SKU分配比例已经存在'
                    ];
                }
                //2019年7月6日21:12:37 无商品库存表数据也允许写入
                if ($allocation <= 1 ){
                    $params['created_man']  = $currentUser->userId;
                    $params['user_id']      = $user_id;
                    $re = $inventoryAllocation->insertArr($params);
                    if ($re){
                        return [
                            'status' => 1,
                            'msg'    => '分配成功'
                        ];
                    }
                    return [
                        'status' => 0,
                        'msg'    => '分配失败'
                    ];
                }

            } else {
                    return [
                        'status' => 0,
                        'msg'    => '商品信息异常'
                    ];
            }
            return [
                'status' => 0,
                'msg'    => '请填写正确的SKU'
            ];
        }
        return [
            'status' => 0,
            'msg'    => '分配失败'
        ];
    }

    /**
     * @description 编辑库存分配首页
     * @author zt7927
     * @date 2019/4/12 10:11
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editAllocationIndex($id)
    {
        if (empty($id)) {
            abort(404);
        }
        $inventoryAllocation = new InventoryAllocation();
        $allocation = $inventoryAllocation->getAllocationById($id);

        return view('InventoryManage.editInventoryAllocation',compact('allocation'));
    }

    /**
     * @description 编辑库存分配
     * @author zt7927
     * @date 2019/4/12 11:30
     * @param Request $request
     * @return array
     */
    public function editAllocation(Request $request)
    {
        $params = $request->input('params', []);
        if ($params['lotte'] && is_numeric($params['lotte']) && $params['amazon'] && is_numeric($params['amazon'])) {
            $inventoryAllocation = new InventoryAllocation();
            $allocation = $params['lotte'] + $params['amazon'];
            if ($allocation <= 1) {
                $re = $inventoryAllocation->updatedArr($params);
                if ($re) {
                    return [
                        'status' => 1,
                        'msg' => '分配成功'
                    ];
                }
                return [
                    'status' => 0,
                    'msg' => '分配失败'
                ];
            }
            if ($allocation > 1) {
                return [
                    'status' => 0,
                    'msg' => '分配比例之和不能大于1'
                ];
            }
        }
        return [
            'status' => 0,
            'msg' => '分配失败'
        ];
    }

    /**
     * @description 导入库存分配页面
     * @author zt7927
     * @date 2019/4/12 13:32
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function importAllocationIndex()
    {
        return view('InventoryManage.importAllocationIndex');
    }

    /**
     * @description 导入库存分配
     * @author zt7927
     * @date 2019/4/12 15:14
     * @param Request $request
     * @param Excel $excel
     * @return array
     */
    public function importAllocation(Request $request,Excel $excel)
    {
        $dataArr = [];   //存储需要批量保存的数据
        $checkUniqueBill_arr = array();
        $filePath = $this->postFileupload($request, 'inventory_allocation', ['xlsx', 'xls']);
        if ($filePath['status'] !== 1) {
            return [
                'status' => 0,
                'msg'    => $filePath['message']
            ];
        }

        $filePath = iconv('utf-8', 'gbk', $filePath['path']);

        $this->import($excel, $filePath);
        $i = 2;
        $currentUser = CurrentUser::getCurrentUser();

        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        foreach (self::$data as $k => $item) {
            if ($k == 0) {
                if (((($item[0] && $item[0] == '自定义SKU') &&
                    ($item[1] && $item[1] == '所在仓库') &&
                    ($item[2] && $item[2] == '乐天平台') &&
                    ($item[3] && $item[3] == '亚马逊平台')
                ))) {
                    continue;
                }

                    return [
                        'status' => 0,
                        'msg'    => '请使用官方提供模板'
                    ];

            } else {
                if (count($item) !== 4) {
                    return [
                        'status' => 0,
                        'msg'    => '请使用官方提供模板'
                    ];
                }

                if (array_unique($item)[0] == null) {
                    continue;
                };
                //验证个字段长度
                if (
                    strlen($item[0]) > 30 || strlen($item[1]) > 20 || strlen($item[2]) > 10 ||
                    strlen($item[3]) > 10
                ) {
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行有过长数据，核对后上传'
                    ];
                }

                if (empty($item[0]) || empty($item[1]) || empty($item[2]) || empty($item[3])){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行有空数据，核对后上传'
                    ];
                }

                $inventoryAllocation = new InventoryAllocation();
                if (!$inventoryAllocation->checkNumber(trim($item[2]))) {
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行乐天平台格式有误，核对后上传'
                    ];
                }

                if (!$inventoryAllocation->checkNumber(trim($item[3]))) {
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行亚马逊平台格式有误，核对后上传'
                    ];
                }

                if ((trim($item[2]) + trim($item[3])) > 1){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行平台分配比例之和不能大于1，核对后上传'
                    ];
                }

                $goods = new Goods();
                $goods = $goods->getGoodsIdBySku(trim($item[0]),$user_id);  //获取审核状态-商品id
                if (!$goods){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行SKU有误，核对后上传'
                    ];
                }
                $warehouse = new SettingWarehouse();
                //该客户下 启用的仓库
                $warehouse = $warehouse->where(['user_id'=>$user_id,'disable'=> SettingWarehouse::ON])->where(function ($query) use ($item) {
                    $query->where('warehouse_name', trim($item[1]))->orWhere('warehouse_code',trim($item[1]));
                })->first();
                if (!$warehouse){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行所在仓库有误，核对后上传'
                    ];
                }

                //判断仓库商品表是否存在对应warehouse goods
                $warehouseTypeGoods = new WarehouseTypeGoods();
                if (!$warehouseTypeGoods->searchByGoodsIdAndWarehouseId($goods->id, $warehouse->id,$user_id)){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行仓库下SKU不存在，核对后上传'
                    ];
                }

                if ($inventoryAllocation->searchAllocation($goods->id, $warehouse->id,$user_id)){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行仓库SKU已分配比例，核对后上传'
                    ];
                }

                $dataArr[$i]['created_man'] = $currentUser->userId;
                $dataArr[$i]['user_id'] = $user_id;
                $dataArr[$i]['goods_id'] = $goods->id;
                $dataArr[$i]['warehouse_id'] = $warehouse->id;
                $dataArr[$i]['lotte'] = trim($item[2]);
                $dataArr[$i]['amazon'] = trim($item[3]);
                $dataArr[$i]['created_at'] = date('Y-m-d H:i:s');
                $dataArr[$i]['updated_at'] = date('Y-m-d H:i:s');
                $checkUniqueBill_arr[] = $item[0] . '-' . $item[1] ;
                $i++;

            }
        }

        if (empty($checkUniqueBill_arr)) {
            return [
                'status' => 0,
                'msg'    => '上传文件为空'
            ];
        }
        // 获取去掉重复数据的数组
        $unique_arr = array_unique($checkUniqueBill_arr);
        // 获取重复数据的数组
        $repeat_arr = array_diff_assoc($checkUniqueBill_arr, $unique_arr);
        if ($repeat_arr) {
            return [
                'status' => 0,
                'msg'    => '上传文件有重复SKU+仓库'
            ];
        }

        if (!DB::table('inventory_allocation')->insert($dataArr)) {
            return [
                'status' => 0,
                'msg'    => '导入失败!'
            ];
        } else {
            return [
                'status' => 1,
                'msg'    => '导入成功!'
            ];
        }
    }
}
