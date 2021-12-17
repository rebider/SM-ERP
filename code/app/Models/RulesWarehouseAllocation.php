<?php

    namespace App\Models;

    use App\Common\Common;
    use App\Exceptions\DataNotFoundException;
    use Illuminate\Database\Eloquent\Model;
    use App\Auth\Models\Users;
    use Illuminate\Support\Facades\DB;

    /**
     * Class RulesOrderTrouble
     * Notes: 订单问题规则
     * @package App\Models
     * Data: 2019/3/7 15:19
     * Author: zt7785
     */
    class RulesWarehouseAllocation extends Model
    {
        protected $table = 'rules_warehouse_allocation';
        public $timestamps = true;
        public $primaryKey = 'id';
        public $fillable = ['id', 'created_man', 'user_id', 'warehouse_ids', 'trouble_type_id', 'opening_status', 'trouble_rules_name', 'trouble_desc', 'created_at', 'updated_at'];
        /**
         * @var 搞基设置id
         */
        const RULES_ORDER_MENUS_ID = 5;
        /**
         * @var 开启
         */
        const STATUS_OPENING = 1;
        /**
         * @var 关闭
         */
        const STATUS_CLOSEDING = 2;

        /**
         * @return $this
         * Note: 用户模型
         * Data: 2019/3/7 11:16
         * Author: zt7785
         */
        public function Users()
        {
            return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
        }

        /**
         * @return $this
         * Note: 问题条件设置
         * Data: 2019/3/7 11:16
         * Author: zt7785
         */
        public function RulesWarehouseCondition()
        {
            return $this->hasMany(RulesWarehouseCondition::class, 'trouble_rule_id', 'id');
        }

        /**
         * @param     $param
         * @param int $offset
         * @param int $limit
         * @return mixed
         * Note: 仓库规则列表数据搜索
         * Data: 2019/3/13 18:28
         * Author: zt7785
         */
        public static function getAllocationDatas($param, $offset = 1, $limit = 0)
        {
            $collection = self::query();
            //关联状态下的条件
            //        $collection->whereHas('RulesTroubleCondition',function ($query) {
            //            $query->where('is_used',RulesTroubleCondition::STATUS_OPENING);
            //        });
            $collection->with(['RulesWarehouseCondition' => function ($query) {
                $query->where('is_used', RulesWarehouseCondition::STATUS_OPENING);
            },
            ]);
            //用户id
            if (isset($param['user_id'])) {
                $param['user_id'] && $collection->where('user_id', $param['user_id']);
            }
            //开启状态
            $param['opening_status'] && $collection->where('opening_status', $param['opening_status']);
            //规则名称 暂未模糊 0402 V2.13 F20.0 模糊查询
            $param['trouble_rules_name'] && $collection->where('trouble_rules_name', 'like', '%' . $param['trouble_rules_name'] . '%');
            //创建时间
            if ($param['start_date'] && empty($param['end_date'])) {
                $param['end_date'] = date('Y-m-d H:i:s');
            }
            if (!empty($param['start_date']) && !empty($param['end_date'])) {
                $collection->whereBetween('created_at', [$param['start_date'], $param['end_date']]);
            }
            //表单列排列顺序：最新创建的规则，排列在前面； id排序一个意思
            if ($limit) {
                $result ['count'] = $collection->count();
                $result ['data'] = $collection->orderBy('id', 'desc')->skip(($offset - 1) * $limit)->take($limit)->get(['id', 'trouble_type_id', 'opening_status', 'trouble_rules_name', 'created_at', 'updated_at'])->toArray();
                return $result;
            } else {
                return $collection->orderBy('id','desc')->get()->toArray();
            }
        }

        /**
         * @param $params
         * @return \Illuminate\Database\Eloquent\Collection|Model|mixed|null|static|static[]
         * Note: 获取订单问题数据
         * Data: 2019/3/23 9:06
         * Author: zt7785
         */
        public static function getWarehouseAllocationByOpt($params, $option = [])
        {
            $collection = self::query();
            //关联状态下的条件
            $collection->with(['RulesWarehouseCondition' => function ($query) {
                $query->where('is_used', RulesWarehouseCondition::STATUS_OPENING);
            },
            ]);
            if ($option) {
                foreach ($option as $k => $v) {
                    $collection->where($k, $v);
                }
            }
            if ($params ['field'] == 'id') {
                return $collection->find($params['value']);
            } else {
                $collection->where($params['field'], $params['value']);
                return $collection->get();
            }
        }


        /**
         * Note: 仓库规则逻辑定时任务使用
         * Data: 2019/4/29 15:18
         * Author: zt8076
         */
        public static function warehouseAllocationMatching()
        {
            set_time_limit(0);
            $results = ['code' => -1, 'msg' => '', 'errAll' => []];
            do {
                try {
                    //1.获取所有规则配置
                    $param['opening_status'] = self::STATUS_OPENING;
                    $param['trouble_rules_name'] = $param['start_date'] = $param['user_id'] = $param['end_date'] = '';
                    $troublesDatas = self::getAllocationDatas($param);
                   // $troublesDatas = array_reverse($troublesDatas);
                    if (empty($troublesDatas)) throw new DataNotFoundException('没有匹配规则');
                    foreach ($troublesDatas as $k => $troublesData) {
                        //联表 关联字段 初始化
                        $tables = $relevance = '';
                        //关联模型数组
                        $relatedArr = [];
                        //2.规则条件逻辑
                        if ($troublesData['rules_warehouse_condition']) {
                            $relatedArr = array_column($troublesData['rules_warehouse_condition'], 'related');
                            $relatedArrCount = count($relatedArr);
                            $sql = '';
                            foreach ($troublesData['rules_warehouse_condition'] as $rules_trouble_condition_key => $rules_trouble_condition_val) {
                                if ($rules_trouble_condition_key == ($relatedArrCount - 1)) {
                                    $sql .= $rules_trouble_condition_val ['condition_sql'] . ' ';
                                } else {
                                    $sql .= $rules_trouble_condition_val ['condition_sql'] . ' AND ';
                                }
                            }
                            //模型权重逻辑
                            if (in_array('Orders', $relatedArr)) {
                                $tables = 'orders';
                                $relevance = ' WHERE ';
                            }
                            if (in_array('OrdersProducts', $relatedArr)) {
                                $tables = 'orders , orders_products ';
                                $relevance = ' WHERE orders.id = orders_products.order_id AND';
                            }
                            if (in_array('Goods', $relatedArr)) {
                                $tables = 'orders ,orders_products , goods ';
                                $relevance = ' WHERE orders.id = orders_products.order_id AND orders_products.goods_id = goods.id AND ';
                            }
                            //上级id 可能是子账号创建的规则
                            $user_id = $troublesData['user_id'];
                            if (!empty($tables)) {
                                //待获取字段 默认inner join 会有重复值 去重
                                $selectFiled = 'DISTINCT orders.id,orders.order_number,problem,warehouse_choose_status,warehouse_id';
                                //待查询sql
                                $querySql = str_replace('WHERE', '', $sql);
                                $permissionSql = 'AND orders.user_id = ' . $user_id . ' AND orders.problem = '. Orders::NO_PROBLEM .' AND orders.picking_status <> ' . Orders::ORDER_PICKING_STATUS_MATCHED_SUCC . ' AND orders.deliver_status <> ' . Orders::ORDER_DELIVER_STATUS_FILLED . ' AND orders.sales_status = ' . Orders::ORDER_SALES_STATUS_INITIAL . ' AND orders.status = ' . Orders::ORDER_STATUS_UNFINISH . '';
                                $orders = DB::select('SELECT ' . $selectFiled . ' FROM ' . $tables . $relevance . $querySql . $permissionSql);
                                if (!empty($orders)) {
                                    foreach ($orders as $order_item) {
                                        $order_number = $order_item->order_number;
                                        //查看是否指定仓库
                                             if($order_item->warehouse_choose_status == Orders::ORDER_CHOOSE_STATUS_CHECKED){
                                                //选中仓库查看是否可用
                                               $Warehouse = SettingWarehouse::with('Logistics')->where(['id'=>$order_item->warehouse_id,'disable'=>SettingWarehouse::ON])->first();
                                               if(empty($Warehouse)){
                                                   Orders::where('id', $order_item->id)->update(['problem' => Orders::C_PROBLEM]);
                                                   continue;
                                               }
                                               //有没有绑定的物流
                                               if(empty($Warehouse['logistics'])){
                                                   Orders::where('id', $order_item->id)->update(['problem' => Orders::D_PROBLEM]);
                                                   continue;
                                               }
                                             }
                                        //存在问题订单跳过
                                        $OrdersTroublesRecord = OrdersTroublesRecord::where(['order_id' => $order_item->id, 'dispose_status' => OrdersTroublesRecord::STATUS_DISPOSING])->exists();
                                        if ($OrdersTroublesRecord) {
                                            continue;
                                        }
                                        $OrdersProductsM = OrdersProducts::where('order_id', $order_item->id)->get(['id', 'goods_id', 'buy_number']);
                                        if ($OrdersProductsM->isEmpty()) {
                                            $results['errAll'][] = $order_item->id . "-未找到商品";
                                            echo '仓库规则-订单号：'.$order_number."未找到商品\r\n";
                                            continue;
                                        }
                                        $OrdersProducts = $OrdersProductsM->toArray();
                                        $warehouses = explode(',', $troublesData['warehouse_ids']);
                                        $checkWarehouse = null;
                                        foreach ($warehouses as $warehouse_item) {
                                            //查库存是否满足发货条件
                                            $checkCount = 0;
                                            foreach ($OrdersProducts as $product) {
                                                $stock = WarehouseTypeGoods::where('setting_warehouse_id', $warehouse_item)
                                                    ->where('goods_id', $product['goods_id'])
                                                    ->where('available_in_stock', '>=', $product['buy_number'])->exists();
                                                $checkCount += $stock;
                                            }
                                            if (count($OrdersProducts) === $checkCount) {
                                                $checkWarehouse = $warehouse_item;
                                                break;
                                            }
                                        }
                                        if ($checkWarehouse) {
                                            //更新仓库
                                            $SettingWarehouseM = SettingWarehouse::find($checkWarehouse);
                                            if (!empty($SettingWarehouseM)) {
                                                $SettingWarehouse = $SettingWarehouseM->toArray();
                                                Orders::where('id', $order_item->id)->update([
                                                    'warehouse_id' => $SettingWarehouse['id'],
                                                    'warehouse' => $SettingWarehouse['warehouse_name'],
                                                    'warehouse_choose_status' => Orders::ORDER_CHOOSE_STATUS_CHECKED //改变指定仓库 防止跑后续规则
                                                ]);
                                                echo '仓库规则-订单号：'.$order_number."匹配成功\r\n";
                                            } else {
                                                empty($order_item->problem) ? $problem = Orders::NO_PROBLEM : $problem = Orders::C_PROBLEM;
                                                Orders::where('id', $order_item->id)->update(['problem' => $problem]);
                                            }
                                        } else {
                                            //没有仓库
                                            empty($order_item->problem) ? $problem = $problem = Orders::NO_PROBLEM : $problem = Orders::C_PROBLEM;
                                            Orders::where('id', $order_item->id)->update(['problem' => $problem]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }catch (\Exception $e){
                    echo '仓库匹配规则任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                    Common::mongoLog($e,'仓库匹配规则','定时匹配仓库失败',__FUNCTION__);
                }catch (\Throwable $e){
                    echo '仓库匹配规则任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                    Common::mongoLog($e,'仓库匹配规则','定时匹配仓库失败',__FUNCTION__);
                }
            } while (0);
            return $results;
        }

        /**
         * @param int $id
         * @param     $data
         * @return Model
         * Note: 新增更新
         * Data: 2019/3/13 18:41
         * Author: zt7785
         */
        public static function postGoods($id = 0, $data)
        {
            return self::updateOrCreate(['id' => $id], $data);
        }
    }

