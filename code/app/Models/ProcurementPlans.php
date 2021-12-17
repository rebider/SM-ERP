<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Auth\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ProcurementPlans
 * @description: 采购计划主表
 * @author: zt7927
 * @data: 2019/3/14 9:20
 * @package App\Models
 */
class ProcurementPlans extends Model
{
    protected $table = 'procurement_plan';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","created_man","warehouse_id","purchase_order_id","procurement_no","order_no","total_amount","total_price","Dec","status","invalid","check_time","check_man","created_at","updated_at","user_id"];


    /**
     * @var 采购菜单-id
     */
    const PROCUREMENT_MENU_ID = 3;

    /**
     * @var 采购计划-草稿
     */
    const DRAFT_STATUS = 1;

    /**
     * @var 采购计划-审核
     */
    const CHECK_STATUS = 2;

    /**
     * @var 采购计划-转采购
     */
    const WAIT_PROCUREMENT = 3;

    /**
     * @description 关联采购计划和商品中间表
     * @author zt7927
     * @data 2019/3/14 15:06
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function procurementPlanGoods()
    {
        return $this->hasMany(ProcurementPlanGoods::class, 'procurement_plan_id', 'id');
    }

    /**
     * @description 关联目的仓库表
     * @author zt7927
     * @data 2019/3/18 15:21
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(SettingWarehouse::class, 'warehouse_id', 'id');
    }

    /**
     * @description 关联用户表获取创建人信息
     * @author zt7927
     * @date 2019/4/10 13:39
     * @return mixed
     */
    public function users()
    {
        return $this->belongsTo(Users::class,'created_man','user_id')->select(['user_id','username']);
    }

    /**
     * @description 关联用户表获取审核人信息
     * @author zt7927
     * @date 2019/4/10 13:40
     * @return mixed
     */
    public function checkUser()
    {
        return $this->belongsTo(Users::class,'check_man','user_id')->select(['user_id','username']);
    }

    public function purchaseOrders()
    {
        return $this->belongsTo(PurchaseOrders::class,'purchase_order_id','id');
    }

    /**
     * @description: 便捷菜单
     * @author: zt7927
     * @data: 2019/3/14 9:40
     * @return array
     */
    public static function getProcurementShortcutMenu()
    {
        $menusModel = new Menus();
        $menusList = $menusModel->getShortcutMenu(self::PROCUREMENT_MENU_ID);
        return $menusList;
    }

    /**
     * @description 采购计划搜索
     * @author zt7927
     * @data 2019/3/14 15:05
     * @param array $params
     * @return self
     */
    public function search(array $params = [])
    {
        $collection = new self();
        $collection = $collection->with('procurementPlanGoods')->with('warehouse')->with('users')->with('checkUser');
        //采购计划状态(草稿 审核 转采购)
        if (isset($params['status']) && $params['status']) {
            $collection = $collection->where('status', $params['status']);
        }
        if (isset($params['user_id']) && $params['user_id']) {
            $collection = $collection->where('user_id', $params['user_id']);
        }
        //采购计划编号
        if (isset($params['procurement_no']) && $params['procurement_no']) {
            $collection = $collection->where('procurement_no', $params['procurement_no']);
        }
        //供应商
        if (isset($params['supplier_id']) && $params['supplier_id']) {
            $collection->whereHas('procurementPlanGoods', function ($query) use ($params) {
                $query->where('supplier_id', $params['supplier_id']);
            });
        }
        //目的仓库
        if (isset($params['warehouse_id']) && $params['warehouse_id']) {
            $collection = $collection->where('warehouse_id', $params['warehouse_id']);
        }

        return $collection->orderBy('created_at','DESC')->where('invalid',1);  //是否作废 1：否 ，2：是
    }

    /**
     * @description 新增采购计划
     * @author zt7927
     * @data 2019/3/14 15:28
     * @param array $params
     * @return bool/int
     */
    public function insertArr(array $params = [])
    {
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        DB::beginTransaction();
        try {
            //组装采购计划表字段数据插入
            $data = [];
            $data['created_man']    = $currentUser->userId;
            $data['user_id']    = $user_id;
            $data['warehouse_id']   = $params['warehouse_id'];
            $data['procurement_no'] = $this->produceProcurementPlanOrderRules(); //生成订单规则
            $data['Dec']            = $params['Dec'];
            $data['total_amount']   = $params['total_amount'];
            $data['total_price']    = $params['total_price'];
            $data['status']         = $params['status'];
            $data['created_at']     = date('Y-m-d H:i:s');
            $data['updated_at']     = date('Y-m-d H:i:s');

            if ($params['status'] == self::CHECK_STATUS) {                   //审核状态
                $data['check_man']  = $currentUser->userId;
                $data['check_time'] = date('Y-m-d H:i:s');
                //往仓库商品表写入采购库存
                $warehouseTypeGoods = new WarehouseTypeGoods();
                for ($j = 0; $j < count($params['goods']); $j++){
                    $insertArr['created_man']          = $currentUser->userId;
                    $insertArr['user_id']              = $user_id;
                    $insertArr['goods_id']             = $params['goods'][$j]['id'];
                    $insertArr['setting_warehouse_id'] = $params['warehouse_id'];
                    $insertArr['purchase_inventory']   = $params['goods'][$j]['amount'];
                    $insertArr['created_at']           = date('Y-m-d H:i:s');
                    $insertArr['updated_at']           = date('Y-m-d H:i:s');

                    $warehouseTypeGoodsInfo = $warehouseTypeGoods->where('goods_id',$insertArr['goods_id'])
                        ->where('setting_warehouse_id',$insertArr['setting_warehouse_id'])->where('user_id',$user_id)->first(['id','purchase_inventory']);
                    if ($warehouseTypeGoodsInfo){
                        $update = $warehouseTypeGoods->where('id',$warehouseTypeGoodsInfo['id'])
                            ->update([
                                'purchase_inventory' => $insertArr['purchase_inventory'] + $warehouseTypeGoodsInfo['purchase_inventory'],
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        $insert = $warehouseTypeGoods->insertGetId($insertArr);                       //增加目的仓库中的可用库存数量
                    }
                }
            }
            $re = $this->insertGetId($data); //写入采购计划

            //组装采购计划与商品中间表字段插入
            $arr = [];
            $procurementPlanGood = new ProcurementPlanGoods();
            $procurementPlanGoods = $params['goods'];
            for ($i = 0; $i < count($procurementPlanGoods); $i++) {
                $arr['created_man']         = $data['created_man'];
                $arr['procurement_plan_id'] = $re;
                $arr['supplier_id']         = $procurementPlanGoods[$i]['preferred_supplier_id'];
                $arr['goods_id']            = $procurementPlanGoods[$i]['id'];
                $arr['amount']              = $procurementPlanGoods[$i]['amount'];
                $arr['price']               = $procurementPlanGoods[$i]['preferred_price'];
                $arr['created_at']          = date('Y-m-d H:i:s');
                $arr['updated_at']          = date('Y-m-d H:i:s');

                $insert = $procurementPlanGood->insertGetId($arr);
                if (!$insert) {
                    DB::rollback();
                    return false;
                }
            }
        } catch (\Exception $exception) {
            DB::rollback();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @description 生成采购计划单号
     * @author zt7927
     * @date 2019/4/16 18:23
     * @return mixed
     */
    public function produceProcurementPlanOrderRules()
    {
        $db = DB::table($this->table);
        $no = $db->whereDate('created_at', date('Y-m-d'))->select(DB::raw('right(10000+count(*)+1,4) as NO'))->first();  //后4位流水号

        return 'C'.date('Ymd').$no->NO;
    }

    /**
     * @description 编辑采购计划
     * @author zt7927
     * @data 2019/3/25 15:28
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function updateArr(array $params = [])
    {
        DB::beginTransaction();
        try {
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }
            //删除采购计划关联的商品信息
            $procurement_plan_id = $params['procurement_plan_id'];
            $procurementPlanGood = new ProcurementPlanGoods();
            $re = $procurementPlanGood->where('procurement_plan_id', $procurement_plan_id)->delete();
            //组装采购计划表字段数据插入
            $data = [];
            $data['created_man']    = $currentUser->userId;
            $data['warehouse_id']   = $params['warehouse_id'];
            $data['Dec']            = $params['Dec'];
            $data['total_amount']   = $params['total_amount'];
            $data['total_price']    = $params['total_price'];
            $data['status']         = $params['status'];
            $data['updated_at']     = date('Y-m-d H:i:s');

            if ($params['status'] == self::CHECK_STATUS) {                     //审核状态
                $data['check_man']  = $currentUser->userId;
                $data['check_time'] = date('Y-m-d H:i:s');
                //往仓库商品表写入采购库存
                $warehouseTypeGoods = new WarehouseTypeGoods();
                for ($j = 0; $j < count($params['goods']); $j++){
                    $insertArr['created_man']          = $currentUser->userId;
                    $insertArr['user_id']              = $user_id;
                    $insertArr['goods_id']             = $params['goods'][$j]['id'];
                    $insertArr['setting_warehouse_id'] = $params['warehouse_id'];
                    $insertArr['purchase_inventory']   = $params['goods'][$j]['amount'];
                    $insertArr['created_at']           = date('Y-m-d H:i:s');
                    $insertArr['updated_at']           = date('Y-m-d H:i:s');

                    $warehouseTypeGoodsRe = $warehouseTypeGoods->where('goods_id',$insertArr['goods_id'])
                        ->where('setting_warehouse_id',$insertArr['setting_warehouse_id'])->where('user_id',$user_id)->first(['id','purchase_inventory']);
                    if ($warehouseTypeGoodsRe){
                        $update = $warehouseTypeGoods->where('goods_id', $insertArr['goods_id'])
                            ->where('setting_warehouse_id', $insertArr['setting_warehouse_id'])
                            ->update([
                                'purchase_inventory' => $insertArr['purchase_inventory'] + $warehouseTypeGoodsRe['purchase_inventory'],
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        $insert = $warehouseTypeGoods->insertGetId($insertArr);                       //增加目的仓库中的可用库存数量
                    }
                }
            }
            $re = $this->where('id', $procurement_plan_id)->update($data);

            //组装采购计划与商品中间表字段插入
            $arr = [];
            $procurementPlanGoods = $params['goods'];
            for ($i = 0; $i < count($procurementPlanGoods); $i++) {
                $arr['created_man']         = $data['created_man'];
                $arr['procurement_plan_id'] = $procurement_plan_id;
                $arr['supplier_id']         = $procurementPlanGoods[$i]['preferred_supplier_id'];
                $arr['goods_id']            = $procurementPlanGoods[$i]['id'];
                $arr['amount']              = $procurementPlanGoods[$i]['amount'];
                $arr['price']               = $procurementPlanGoods[$i]['preferred_price'];
                $arr['created_at']          = date('Y-m-d H:i:s');
                $arr['updated_at']          = date('Y-m-d H:i:s');

                $insert = $procurementPlanGood->insertGetId($arr);
                if (!$insert) {
                    return false;
                }
            }
        } catch (\Exception $exception) {
            DB::rollback();
            return false;
        }

        DB::commit();
        return true;
    }

    /**
     * @description 采购计划详情
     * @author zt7927
     * @data 2019/3/19 10:03
     * @param $id
     * @return array
     */
    public function getProcurementDetail($id)
    {
        $collection = self::with('procurementPlanGoods')->with('warehouse')->with('users')->with('checkUser');
        $collection = $collection->where('id', $id)->first()->toArray();
        return $collection;
    }

    /**
     * @description 删除采购计划
     * @author zt7927
     * @data 2019/3/19 11:39
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function delProcurementPlan($id)
    {
        if (is_numeric($id) && ($id > 0)) {
            self::where('id', $id)->delete();      //单个删除
            $procurementPlanGoods = new ProcurementPlanGoods();
            return $procurementPlanGoods->where('procurement_plan_id', $id)->delete();
        } elseif (is_array($id)) {
            return self::whereIn('id', $id)->delete();    //todo 目前没有批量删除
        }
        return false;
    }

    /**
     * @description 改变采购计划状态--审核
     * @author zt7927
     * @data 2019/3/19 14:06
     * @param $id
     * @return bool
     */
    public function changeProcurementPlanStatus($id)
    {
        $currentUser = CurrentUser::getCurrentUser();
        $re = self::where('id', $id)->update([
            'status' => self::CHECK_STATUS,
            'check_man' => $currentUser->userId,
            'check_time' => date('Y-m-d H:i:s', time())
        ]);
        if ($re) {
            return $re;
        }
        return false;
    }

    /**
     * @description 采购计划转采购单后更新采购计划信息
     * @param array $ids
     * @param $purchase_order_id
     * @param $order_no
     * @return bool
     */
    public function updatePurchaseToProcurementPlan(array $ids = [], $purchase_order_id, $order_no)
    {
//        $procurementPlan = self::with('procurementPlanGoods')->whereIn('id', $ids)->get()->toArray();
//        $warehouseTypeGoods = new WarehouseTypeGoods();
        //清除了仓库下的采购库存
//        for ($i = 0; $i < count($procurementPlan); $i++){
//            $warehouse_id = $procurementPlan[$i]['warehouse_id'];
//            $goods = $procurementPlan[$i]['procurement_plan_goods'];
//            for ($j = 0; $j < count($goods); $j++){
//                $goods_id = $goods[$j]['goods_id'];
//                $warehouseGoodsInfo = $warehouseTypeGoods->where('goods_id', $goods_id)
//                    ->where('setting_warehouse_id', $warehouse_id)->where('user_id',$procurementPlan[$i] ['user_id'])->first(['purchase_inventory']);
//                //创建采购单之后 该商品的采购库存应该仅仅只是减去转换的商品数量
//                $warehouseTypeGoods->where('goods_id', $goods_id)
//                    ->where('setting_warehouse_id', $warehouse_id)
//                    ->update([
//                        'purchase_inventory' => $warehouseGoodsInfo->purchase_inventory - $goods[$j] ['amount'],
//                        'updated_at' => date('Y-m-d H:i:s')
//                    ]);
//            }
//        }
        $re = self::whereIn('id', $ids)->update([
            'purchase_order_id' => $purchase_order_id,
            'order_no' => $order_no,
            'status' => self::WAIT_PROCUREMENT          //转采购
        ]);
        if ($re) {
            return $re;
        }
        return false;
    }

    /**
     * @description 查询采购计划状态
     * @author zt7927
     * @data 2019/3/21 14:10
     * @param array $ids
     * @return array
     */
    public function getProcurementPlan(array $ids = [])
    {
        return self::with('procurementPlanGoods')->whereIn('id', $ids)->get()->toArray();
    }

    /**
     * @description 编辑-获取采购计划信息
     * @author zt7927
     * @data 2019/3/26 9:10
     * @param $id
     * @return array
     */
    public function getProcurementData($id)
    {
        $collection = self::with('procurementPlanGoods')->with('warehouse')->with('users')->with('checkUser');
        $collection = $collection->where('id', $id)->first();
        if (empty($collection)) {
            return [];
        }
        return $collection->toArray();
    }

}
