<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseOrders
 * @description: 采购单表
 * @author: zt7927
 * @data: 2019/3/21 10:48
 * @package App\Models
 */
class PurchaseOrders extends Model
{
    protected $table = 'purchase_order';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","created_man","warehouse_id","logistics_id","tracking_no","freight","order_no","box_number","get_time","status","check_time","check_man","receiving_code","created_at","updated_at","user_id"] ;

    /**
     * @var 草稿-采购单
     */
    const DRAFT_STATUS = 1;
    /**
     * @var 审核-采购单
     */
    const CHECK_STATUS = 2;
    /**
     * @var 在途-采购单
     */
    const ON_THE_WAY = 3;
    /**
     * @var 完成-采购单
     */
    const COMPLETE = 4;
    /**
     * @var 作废-采购单
     */
    const INVALID = 5;

    /**
     * @description 关联采购计划  一对多
     * @author zt7927
     * @data 2019/3/27 13:59
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procurementPlan()
    {
        return $this->hasMany(ProcurementPlans::class, 'purchase_order_id', 'id');
    }

    /**
     * @description 关联目的仓库表
     * @author zt7927
     * @data 2019/3/27 15:21
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(SettingWarehouse::class, 'warehouse_id', 'id');
    }

    /**
     * @description 关联物流表
     * @author zt7927
     * @data 2019/3/27 15:21
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logistics()
    {
        return $this->belongsTo(SettingLogistics::class, 'logistics_id', 'id');
    }

    /**
     * @description 关联商品装箱表
     * @author zt7927
     * @date 2019/4/9 15:33
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseGoodsBox()
    {
        return $this->hasMany(PurchaseGoodsBox::class,'purchase_id','id');
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

    /**
     * @description 采购单查询
     * @author zt7927
     * @data 2019/3/27 13:56
     * @param array $params
     * @return $this
     */
    public function search(array $params = [])
    {
        $collection = new self();
        $collection = $collection->with('procurementPlan')->with('warehouse')->with('logistics');
        //采购单状态(草稿 审核 在途 完成 作废)
        if (isset($params['status']) && $params['status']) {
            $collection = $collection->where('status', $params['status']);
        }
        if (isset($params['user_id']) && $params['user_id']) {
            $collection = $collection->where('user_id', $params['user_id']);
        }
        //采购单号
        if (isset($params['order_no']) && $params['order_no']) {
            $collection = $collection->where('order_no', $params['order_no']);
        }
        //跟踪号
        if (isset($params['tracking_no']) && $params['tracking_no']) {
            $collection = $collection->where('tracking_no', $params['tracking_no']);
        }
        //目的仓库
        if (isset($params['warehouse_id']) && $params['warehouse_id']) {
            $collection = $collection->where('warehouse_id', $params['warehouse_id']);
        }
        //物流方式
        if (isset($params['logistics_id']) && $params['logistics_id']) {
            $collection = $collection->where('logistics_id', $params['logistics_id']);
        }

        return $collection->orderBy('created_at', 'DESC');
    }

    /**
     * @description 添加采购单数据（由采购计划转化过来）
     * @author zt7927
     * @data 2019/3/21 13:49
     * @param array $params
     * @return int
     */
    public function insertArr(array $params = [])
    {

        $data = [];
        $data['created_man']  = $params ['created_man'];
        $data['user_id']      = $params ['user_id'];
        $data['warehouse_id'] = $params['warehouse_id'];
        $data['get_time']     = $params['get_time'];
        $data['logistics_id'] = $params['logistics_id'];
        $data['tracking_no']  = $params['tracking_no'] ? $params['tracking_no'] : '';
        $data['freight']      = $params['freight'] ? $params['freight'] : '0.00';
        $data['status']       = self::DRAFT_STATUS;  //草稿
        $data['order_no']     = $this->produceOrderRules();  //生成订单规则
        $data['created_at']   = date('Y-m-d H:i:s');
        $data['updated_at']   = date('Y-m-d H:i:s');

        return $this->insertGetId($data);
    }

    /**
     * @description 获取采购单号
     * @author zt7927
     * @data 2019/3/21 16:41
     * @param $id
     * @return bool|\Illuminate\Support\Collection
     */
    public function getOrderNoById($id)
    {
        if ($id && ($id > 0)) {
            return self::find($id);
        }
        return false;
    }

    /**
     * @description 生成采购单号
     * @author zt7927
     * @date 2019/4/16 18:23
     * @return mixed
     */
    public function produceOrderRules()
    {
        $db = DB::table($this->table);
        $no = $db->whereDate('created_at', date('Y-m-d'))->select(DB::raw('right(10000+count(*)+1,4) as NO'))->first();  //后4位流水号

        return 'D'.date('Ymd').$no->NO;
    }

    /**
     * @description 采购单详情
     * @author zt7927
     * @data 2019/3/27 16:28
     * @param $id
     * @return array
     */
    public function purchaseOrderDetail($id)
    {
        $collection = self::with('procurementPlan')->with('warehouse')->with('logistics')->with('users')->with('checkUser');
        $collection = $collection->where('id', $id)->first()->toArray();
        return $collection;
    }

    /**
     * @description 作废采购单（对应采购计划状态变为审核,在采购计划表复制采购计划，原采购计划作废状态，采购计划商品表也要复制数据）
     * @author zt7927
     * @data 2019/3/28 9:47
     * @param $id
     * @return bool
     */
    public function delPurchaseOrder($id)
    {
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        if (is_numeric($id) && ($id > 0)) {
            $procurementPlan = new ProcurementPlans();
            $procurementPlans = $procurementPlan::with('purchaseOrders')->where('purchase_order_id', $id)->get()->toArray();
            DB::beginTransaction();
            try {
                $procurementPlanGood = new ProcurementPlanGoods();
                $warehouseTypeGoods = new WarehouseTypeGoods();
                //复制采购计划，采购计划商品
                for ($i = 0; $i < count($procurementPlans); $i++){
                    $data['created_man']    = $procurementPlans[$i]['created_man'];
                    $data['user_id']        = $procurementPlans[$i]['user_id'];
                    $data['warehouse_id']   = $procurementPlans[$i]['warehouse_id'];
                    $data['procurement_no'] = $procurementPlans[$i]['procurement_no'];
                    $data['Dec']            = $procurementPlans[$i]['Dec'];
                    $data['total_amount']   = $procurementPlans[$i]['total_amount'];
                    $data['total_price']    = $procurementPlans[$i]['total_price'];
                    $data['status']         = ProcurementPlans::CHECK_STATUS;  //审核
                    $data['created_at']     = $procurementPlans[$i]['created_at'];
                    $data['updated_at']     = $procurementPlans[$i]['updated_at'];
                    $data['check_man']      = $procurementPlans[$i]['check_man'];
                    $data['check_time']     = $procurementPlans[$i]['check_time'];

                    $re = $procurementPlan->insertGetId($data);

                    $procurementPlanGoods = $procurementPlanGood->where('procurement_plan_id', $procurementPlans[$i]['id'])->get()->toArray();

                    $data['check_man']  = $currentUser->userId;
                    $data['check_time'] = date('Y-m-d H:i:s');
                    //往仓库商品表写入采购库存

                    for ($j = 0; $j < count($procurementPlanGoods); $j++){
                        $arr['created_man']         = $procurementPlanGoods[$j]['created_man'];
                        $arr['procurement_plan_id'] = $re;
                        $arr['supplier_id']         = $procurementPlanGoods[$j]['supplier_id'];
                        $arr['goods_id']            = $procurementPlanGoods[$j]['goods_id'];
                        $arr['amount']              = $procurementPlanGoods[$j]['amount'];
                        $arr['price']               = $procurementPlanGoods[$j]['price'];
                        $arr['created_at']          = $procurementPlanGoods[$j]['created_at'];
                        $arr['updated_at']          = $procurementPlanGoods[$j]['updated_at'];

                        $insert = $procurementPlanGood->insertGetId($arr);

                        $insertArr['created_man']          = $currentUser->userId;
                        $insertArr['user_id']              = $user_id;
                        $insertArr['goods_id']             = $procurementPlanGoods[$j]['goods_id'];
                        $insertArr['setting_warehouse_id'] = $data['warehouse_id'];
                        $insertArr['purchase_inventory']   = $procurementPlanGoods[$j]['amount'];
                        $insertArr['created_at']           = date('Y-m-d H:i:s');
                        $insertArr['updated_at']           = date('Y-m-d H:i:s');


                        //仓库判断
                        if ($procurementPlans[$i] ['warehouse_id'] !== $procurementPlans[$i] ['purchase_orders'] ['warehouse_id']) {
                            $warehouseTypeGoodsMinusRe = $warehouseTypeGoods->where('goods_id',$insertArr['goods_id'])
                                ->where('setting_warehouse_id',$procurementPlans[$i] ['purchase_orders'] ['warehouse_id'])->where('user_id',$user_id)->first(['id','purchase_inventory']);
                            if ($warehouseTypeGoodsMinusRe){
                                $warehouseTypeGoods->where('id',$warehouseTypeGoodsMinusRe->id)
                                    ->update([
                                        'purchase_inventory' =>  $warehouseTypeGoodsMinusRe['purchase_inventory'] - $insertArr['purchase_inventory'] ,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                            }

                            $warehouseTypeGoodsRe = $warehouseTypeGoods->where('goods_id',$insertArr['goods_id'])
                                ->where('setting_warehouse_id',$insertArr['setting_warehouse_id'])->where('user_id',$user_id)->first(['id','purchase_inventory']);
                            if ($warehouseTypeGoodsRe){
                                $warehouseTypeGoods->where('id',$warehouseTypeGoodsRe->id)
                                    ->update([
                                        'purchase_inventory' => $insertArr['purchase_inventory'] + $warehouseTypeGoodsRe->purchase_inventory,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                            } else {
                                $warehouseTypeGoods->insertGetId($insertArr);                       //增加目的仓库中的可用库存数量
                            }
                        }


                    }
                }
                //把初始采购计划作废掉（提供采购单作废后查看历史的数据,查看的是作废后的采购计划）
                $procurementPlan = $procurementPlan->where('purchase_order_id', $id)->update([
                    'invalid' => 2,   //是否作废 1：否 ，2：是
                ]);
                //更新采购单状态 作废
                $re = self::where('id', $id)->update(['status' => 5]);
            } catch (\Exception $exception) {
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        }
        return false;
    }

    /**
     * @description 查询采购单
     * @author zt7927
     * @data 2019/3/28 10:55
     * @param $id
     * @return array|bool
     */
    public function getPurchaseOrderById($id)
    {
        if (is_numeric($id) && ($id > 0)) {
            return self::where('id', $id)->with([
                'procurementPlan', 'warehouse', 'logistics'
            ])->first()->toArray();
        }
        if (is_array($id) && (count($id) > 0)) {
            return self::whereIn('id', $id)->with([
                'procurementPlan', 'warehouse', 'logistics'
            ])->get()->toArray();
        }
        return false;
    }

    /**
     * @description 查询采购单
     * @author zt7927
     * @data 2019/3/29
     * @param $id
     * @return mixed
     */
    public function getPurchaseOrderByIdDB($id)
    {
        $db = DB::table($this->table);
        $db = $db->where('purchase_order.id', $id)
                 ->leftjoin('procurement_plan','purchase_order.id','=','procurement_plan.purchase_order_id')
                 ->leftjoin('procurement_plan_goods','procurement_plan.id','=','procurement_plan_goods.procurement_plan_id')
                 ->leftjoin('goods','goods.id','=','procurement_plan_goods.goods_id')
                 ->leftjoin('setting_warehouse','purchase_order.warehouse_id','=','setting_warehouse.id')
                 ->leftjoin('setting_logistics','purchase_order.logistics_id','=','setting_logistics.id')
                 ->select('purchase_order.order_no','setting_warehouse.warehouse_code','setting_logistics.logistic_code',
                          'purchase_order.tracking_no','purchase_order.get_time','goods.sku','procurement_plan_goods.amount',
                          'setting_warehouse.type','procurement_plan_goods.goods_id','purchase_order.warehouse_id','purchase_order.user_id')
                 ->get();
        return $db;
    }

    /**
     * @description 返回状态名称
     * @author zt7927
     * @data 2019/3/28
     * @param $status
     * @return mixed
     */
    public function getStatusName(int $status)
    {
        $re = [
            1 => '草稿',
            2 => '审核',
            3 => '在途',
            4 => '完成',
            5 => '作废',
        ];
        return $re[$status];
    }

    /**
     * @description 编辑采购单状态-审核
     * @author zt7927
     * @date 2019/4/9 18:21
     * @param $id
     * @param $status
     * @param $receiving_code -入库单号
     * @return bool
     */
    public function updateStatus($id, $status, $receiving_code = '')
    {
        $currentUser = CurrentUser::getCurrentUser();
        return self::where('id', $id)->update([
           'status'         =>$status,
           'receiving_code' => $receiving_code,
           'check_man'      =>$currentUser->userId,
           'check_time'     => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @description 获取采购单下的商品信息
     * @author zt7927
     * @date 2019/4/9 17:43
     * @param $id 采购单-id
     * @return mixed
     */
    public function getPurchaseGoods($id)
    {
        $db = DB::table($this->table);
        $db = $db->leftjoin('procurement_plan','procurement_plan.purchase_order_id','=','purchase_order.id')
            ->leftjoin('procurement_plan_goods','procurement_plan.id','=','procurement_plan_goods.procurement_plan_id')
            ->leftjoin('goods','procurement_plan_goods.goods_id','=','goods.id')
            ->where('purchase_order.id',$id)
            ->select('goods.sku',DB::raw('sum(procurement_plan_goods.amount) as amount'),'purchase_order.warehouse_id','procurement_plan_goods.goods_id',
                     'procurement_plan.id as procurement_plan_id')
            ->groupBy('goods.sku','purchase_order.warehouse_id','procurement_plan_goods.goods_id','procurement_plan.id')
            ->get()->toArray();
        return $db;
    }

    /**
     * @description 获取入库单号(审核、在途状态下的)
     * @author zt7927
     * @date 2019/4/15 14:51
     * @return array
     */
    public static function getReceivingCode()
    {
        return self::where('status', self::CHECK_STATUS)   //审核
            ->orWhere('status', self::ON_THE_WAY)          //在途
            ->get(['receiving_code','user_id','id'])->toArray();
    }

    /**
     * @description 更新采购单状态
     * @author zt7927
     * @date 2019/4/15 15:12
     * @param $data
     * @return bool
     */
    public static function updateArr($data)
    {
        return self::where('receiving_code', $data['receiving_code'])->update([
            'status' => self::getStatus($data['receiving_status'])
        ]);
    }

    /**
     * @description 获取采购单状态
     * @author zt7927
     * @date 2019/4/15 15:12
     * @param $status
     * @return int
     */
    public static function getStatus($status)
    {
        switch ($status){
            case 'C':
                return self::CHECK_STATUS;  //审核
                break;
            case 'W':
                return self::ON_THE_WAY;    //在途
                break;
            case 'P':
                return self::ON_THE_WAY;    //在途
                break;
            case 'Z':
                return self::ON_THE_WAY;   //在途
                break;
            case 'G':
                return self::ON_THE_WAY;   //在途
                break;
            case 'F':
                return self::COMPLETE;     //完成
                break;
            default:
                return self::CHECK_STATUS; //审核
        }
    }

    /**
     * @description 获取采购单id
     * @author zt7927
     * @date 2019/4/15 15:33
     * @param $receiving_code
     * @return \Illuminate\Support\Collection
     */
    public static function getPurchaseOrderId($receiving_code)
    {
        return self::where('receiving_code', $receiving_code)->first();
    }

    /**
     * @description 获取商品信息-采购数量
     * @author zt7927
     * @date 2019/4/15 15:53
     * @param $id
     * @return array
     */
    public static function getPurchaseGoodsByStatus($id)
    {
        $db = self::leftjoin('procurement_plan','procurement_plan.purchase_order_id','=','purchase_order.id')
            ->leftjoin('procurement_plan_goods','procurement_plan.id','=','procurement_plan_goods.procurement_plan_id')
            ->leftjoin('goods','procurement_plan_goods.goods_id','=','goods.id')
            ->where('purchase_order.id',$id)
            ->select('goods.sku',DB::raw('sum(procurement_plan_goods.amount) as amount'),'purchase_order.warehouse_id','procurement_plan_goods.goods_id')
            ->groupBy('goods.sku','purchase_order.warehouse_id','procurement_plan_goods.goods_id')
            ->get()->toArray();
        return $db;
    }
}
