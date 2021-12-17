<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/11
     * Time: 13:42
     */

    namespace App\Http\Services\Warehouse;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Common\Common;
    use App\Models\Orders;
    use App\Models\OrdersProducts;
    use App\Models\OrdersTroublesRecord;
    use App\Models\RulesWarehouseAllocation;
    use App\Models\SettingLogisticsWarehouses;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseSecretkey;
    use App\Models\WarehouseTypeGoods;

    class WarehouseHandle
    {
        /**
         * @return boolean
         * Note: 创建创库
         * Data: 2019/3/16 10:00
         * Author: zt8067
         */
        public static function createOrUpdate($params)
        {
            $results = ['code' => -1, 'msg' => 'error'];
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            //自定义仓库
            if ($params['type'] == SettingWarehouse::CUSTOM_TYPE) {

                $settingWarehouse = new SettingWarehouse;
                if (empty($params['id'])) {
                    $settingWarehouse->created_man = $CurrentUser->userId;
                    $settingWarehouse->user_id = $user_id;
                    $settingWarehouse->type = SettingWarehouse::CUSTOM_TYPE;
                    $settingWarehouse->warehouse_name = $params['warehouse_name'];
                    $settingWarehouse->facilitator = $params['facilitator'];
                    $settingWarehouse->charge_person = $params['charge_person'] ?? '';
                    $settingWarehouse->phone_number = $params['phone_number'] ?? '';
                    $settingWarehouse->qq = $params['qq'] ?? '';
                    $settingWarehouse->address = $params['address'] ?? '';
                    $settingWarehouse->disable = $params['disable'] ?? SettingWarehouse::ON;
                    $settingWarehouse->save() && $results = ['code' => 1, 'msg' => '添加成功！'];
                } else {
                    $data = [
                        'created_man'    => $CurrentUser->userId,
                        'user_id'    => $user_id,
                        'type' => SettingWarehouse::CUSTOM_TYPE,
                        'warehouse_name' => $params['warehouse_name'],
                        'facilitator'    => $params['facilitator'],
                        'charge_person'  => $params['charge_person'] ?? '',
                        'phone_number'   => $params['phone_number'] ?? '',
                        'qq'             => $params['qq'] ?? '',
                        'address'        => $params['address'] ?? '',
                        'disable'        => $params['disable'] ?? '',
                    ];
                    $settingWarehouse->where('user_id', $CurrentUser->userId)->where('id', $params['id'])->update($data) && $results = ['code' => 1, 'msg' => '保存成功！'];
                }
            } //TODO  速贸仓储
            elseif ($params['type'] == SettingWarehouse::SM_TYPE) {

                $result = SettingWarehouse::where('id', $params['id'])
                    ->update(['disable' => $params['disable']]);
                if ($result) {
                    $results = ['code' => 0, 'msg' => '保存成功！'];
                }
            }
            return $results;
        }

        /**
         * @return array
         * Note: 获取仓库
         * Data: 2019/4/2 15:30
         * Author: zt8067
         * Update time: 2019/4/19 10:28
         * editor zt12779
         */
        public static function getWarehouse($params)
        {
            $results = ['code'=>-1];
            do {
                try {
                    $updateCount = 0;
                    $account = [
                        'appToken' =>$params['appToken'],
                        'appKey' =>$params['appKey'],
                    ];
                    //检查凭证是否正确
                    $data = [
                        'pageSize' => '',
                        'page' => '',
                    ];
                    $Common = new Common;
                    $Warehouse_results = $Common->sendWarehouse('getWarehouse', $data, $account);
                    if (empty($Warehouse_results)){
                        $results['msg'] = '接口故障，网络异常';
                        break;
                    }
                    //appToken/appKey非法
                    if ($Warehouse_results['ask']=='Failure'){
//                        $results['msg'] = $Warehouse_results['ask']['errMessage'];
//                        $results['msg'] = $Warehouse_results['ask']['message'];
                        $results['msg'] = '添加失败，无法为您添加该服务仓';
                        break;
                    }

                    //保存appToken&appKey
                    $currentUser = CurrentUser::getCurrentUser();
                    if ($currentUser->userAccountType == AccountType::CHILDREN) {
                        $user_id = $currentUser->userParentId;
                    } else {
                        $user_id =$currentUser->userId;
                    }

                    $hasOne = WarehouseSecretkey::where('user_id', $user_id)->first();
                    if (!$hasOne) {
                        $warehouseSecretkey = new WarehouseSecretkey;
                        $warehouseSecretkey->created_man = $currentUser->userId;
                        $warehouseSecretkey->appToken = $params['appToken'];
                        $warehouseSecretkey->appKey = $params['appKey'];
                        $warehouseSecretkey->user_id = $user_id;

                        if (!$warehouseSecretkey->save()) {
                            $results['msg'] = '保存凭证失败';
                            break;
                        }
                    }

                    $existWarehouse = SettingWarehouse::where('user_id', $user_id)->get();
                    if (!empty($existWarehouse)) {
                        $existWarehouse = $existWarehouse->toArray();
                        $existWarehouse_inSet = array_column($existWarehouse,
                            'warehouse_name', 'warehouse_code');
                    }
                    //存储可用仓库
                    $warehouse = [];
                    foreach ($Warehouse_results['data'] as $key => $val) {
                        //如果仓库已存在，但是名字变更了，那就更新这条数据；否则新增
                        if (isset($existWarehouse_inSet) && array_key_exists($val['warehouse_code'], $existWarehouse_inSet)) {
                            if ($existWarehouse_inSet[$val['warehouse_code']] != $val['warehouse_name']) {
                                $whereMap = [
                                    'warehouse_code' => $val['warehouse_code'],
                                    'user_id' => $user_id
                                ];
                                $updateSingle = [
                                    'warehouse_name' => $val['warehouse_name'],
                                    'created_man' => $currentUser->userId
                                ];
                                $updateSingleResult = SettingWarehouse::where($whereMap)->update($updateSingle);
                                if (!$updateSingleResult) {
                                    $results['msg'] = '同步仓库失败';
                                    break;
                                }
                                $updateCount++;
                            }
                        } else {
                            $warehouse[] = [
                                'created_man' => $currentUser->userId,
                                'type' => 1,
                                'warehouse_name' => $val['warehouse_name'],
                                'warehouse_code' => $val['warehouse_code'],
                                'user_id' => $user_id,
                                'facilitator' => '株式会社 Dream Works',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                        }

                    }
                    //含有新增的仓库
                    if (count($warehouse) > 1) {
                        $insertResult = SettingWarehouse::insert($warehouse);
                        if (!$insertResult) {
                            $results['msg'] = '保存仓库失败';
                            break;
                        }
                    }

                    $results['code'] = 0;
                    $results['msg'] = '成功更新' . $updateCount . '个仓库，新增' . count($warehouse) . '个仓库';

                } catch (\Exception $e) {
                    $results['msg'] = '接口故障，网络异常：' . $e->getMessage();
                } catch (\Error $e) {
                    $results['msg'] = '接口故障，程序错误异常：' . $e->getMessage();
                }
            } while (0);
            return $results;
        }

        /**
         * @return array
         * Note: 仓库分派规则定时任务
         * Data: 2019/4/23 15:30
         * Author: zt8067
         */
        public function warehouseTimingProcessing()
        {
            set_time_limit(3500);
            //30天内订单
            $create_date = date('Y-m-d H:i:s', time()-2592000);
            $start_time = time();
            //找到没有问题或并且已经处理的订单
            $Orders = Orders::with(['OrdersTroublesRecord'=> function ($query) {
                        $query->where('dispose_status', OrdersTroublesRecord::STATUS_DISPOSED);
                            }])->where('picking_status','<>',Orders::ORDER_PICKING_STATUS_MATCHED_SUCC)
                               ->where('intercept_status',Orders::ORDER_INTERCEPT_STATUS_INITIAL)
                               ->where('status',Orders::ORDER_STATUS_UNFINISH)
                               ->where('created_at','>',$create_date)->get()->toArray();
             foreach ($Orders as $order_item){
                 if(empty($order_item['warehouse']) && empty($order_item['logistics'])){
                     $OrdersProducts = OrdersProducts::where('order_id',$order_item['id'])->get()->toArray();
                       //取出商品查看是否存在可用库存
                     $RulesWarehouseAllocation =  RulesWarehouseAllocation::where(['user_id' => $order_item['user_id'],'status'=>RulesWarehouseAllocation::ON])->get()->toArray();
                        foreach ($RulesWarehouseAllocation as $Allocation){
                           $warehouse_ids = explode(',',$Allocation['warehouse_ids']);
                           $SettingWarehouse = SettingWarehouse::whereIn('id',$warehouse_ids)->get()->toArray();
                            foreach($SettingWarehouse as $Warehouse){
                              if ($Warehouse['type'] == SettingWarehouse::SM_TYPE){
                                  foreach($OrdersProducts as $Products){
                                      $exists =  WarehouseTypeGoods::where(['goods_id'=>$Products['goods_id'],'setting_warehouse_id'=>$Warehouse['id'],'user_id' => $order_item['user_id']])
                                          ->where('available_in_stock','>=',$Products['buy_number'])->exists();

                                  }
                              }else{


                              }
                            }
                        }
                 }
             }

        }

    }