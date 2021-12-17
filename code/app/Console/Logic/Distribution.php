<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/10
     * Time: 17:09
     */

    namespace App\Console\Logic;

    use App\Common\Common;
    use App\Http\Services\Order\PendingHandle;
    use App\Models\Orders;
    use App\Models\OrdersTroublesRecord;
    use App\Models\SettingLogistics;
    use App\Models\SettingLogisticsWarehouses;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseTypeGoods;

    class Distribution
    {
        private $fileLock = 'Distribution.lock';//文件锁

        public function __construct()
        {
        }

        //执行前
        public function before()
        {
            $dir = storage_path();
            $flagFile = $dir . DIRECTORY_SEPARATOR . $this->fileLock;
            if (file_exists($flagFile)) {
                //如果锁文件存在时间过长删除锁文件
                if (time() - filemtime($flagFile) > 7200) {
                    @unlink($flagFile);
                }
            }
            //如果锁文件存在,程序已经运行.
            if (file_exists($flagFile)) {
                echo "Is already running,please unlock! \n";
                exit(0);
            }
            //加锁,创建锁文件
            touch($flagFile);
            if (preg_match('/linux/i', PHP_OS) || preg_match('/Unix/i', PHP_OS)) {
                chmod($flagFile, 0777);
            }
        }

        public function end()
        {
            $dir = storage_path();
            $flagFile = $dir . DIRECTORY_SEPARATOR . $this->fileLock;
            //解锁,删除锁文件
            unlink($flagFile);
        }

        //用户信息同步的入口
        public function run()
        {
            try {
                $this->before();
                $this->handel();
            } catch (\Exception $e) {
                echo '定时配货单任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                Common::mongoLog($e,'定时配货单','定时配货单添加失败',__FUNCTION__);
            } catch (\Throwable $e) {
                echo '定时配货单任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                Common::mongoLog($e,'定时配货单','定时配货单添加失败',__FUNCTION__);
            } finally{
                $this->end();
            }
        }
        /**
         * @return array
         * Note: 仓库创建入库单
         * Date: 2019/3/22 14:00
         * Author: zt8067
         */
        public function handel()
        {
            do {
                //没有问题的订单或者问题已经处理了 匹配上了仓库和物流的订单跑自动配货
                $OrdersM = Orders::where('picking_status', '<>', Orders::ORDER_PICKING_STATUS_MATCHED_SUCC)//配货匹配状态 不等于 匹配成功
                ->where('deliver_status', '<>', Orders::ORDER_DELIVER_STATUS_FILLED)//发货状态 不等于 发货成功
                ->where('intercept_status', Orders::ORDER_INTERCEPT_STATUS_INITIAL)//拦截状态 未拦截
                ->where('status', Orders::ORDER_STATUS_UNFINISH)//订单状态 未完结
                ->where('warehouse_id', '<>', 0)//关联的仓库
                ->where('logistics_id', '<>', 0)//关联的物流
                ->where('problem', 0)//配货单问题 无
                ->orderBy('payment_time')
                //->orderBy('id','asc')
                    ->limit(5000)
                    ->get(['id', 'warehouse_id', 'user_id', 'problem', 'order_number']);
                if ($OrdersM->isNotEmpty()) {
                    $orders = array_reverse($OrdersM->toArray());
                    $PendingHandle = new PendingHandle();
                    foreach ($orders as $k => $order_item) {
                        //判断订单是否存在未处理订单问题存在则跳过
                        $Troubles = OrdersTroublesRecord::where([ 'order_id' => $order_item['id'],'dispose_status' => OrdersTroublesRecord::STATUS_DISPOSING])->exists();
                        if ($Troubles) continue;
                        $params['order_id'] = $order_item['id'];
                        $params['warehouse_id'] = $order_item['warehouse_id'];
                        $user_id = $order_item['user_id'];
                        $products = $PendingHandle->getOrdersDesc($params, $user_id);
                        if ($products['code']!==1){
                            echo '定时配货订单号：'.$order_item['order_number'].$products['msg']."\r\n";
                            //仓库不可用
                            Orders::where('id', $order_item['id'])->update(['problem' => Orders::C_PROBLEM]);
                            continue;
                        }
                        $distributionOrderParams = [
                            'order_id'     => $order_item['id'],
                            'warehouse_id' => $order_item['warehouse_id'],
                            'logistic_id'  => $products['data']['logistics']['id'],
                        ];
                        $orders_products = $products['data']['orders_products'];
                        if ($orders_products && is_array($orders_products)) {
                            $clock = false;
                            $available_in_stock = 0;
                            foreach ($orders_products as $kk => $product_item) {
                                $WarehouseTypeGoods = WarehouseTypeGoods::where(['setting_warehouse_id' => $order_item['warehouse_id'], 'goods_id' => $product_item['goods_id']])->first(['available_in_stock']);
                                $available_in_stock += $WarehouseTypeGoods['available_in_stock'];
                                $dispensable_number = $product_item['buy_number'] - $product_item['already_stocked_number'];
                                $temp_goods[$kk]['id'] = $product_item['id'];
                                $temp_goods[$kk]['sku'] = $product_item['sku'];
                                $temp_goods[$kk]['buy_number'] = $product_item['buy_number'];
                                $temp_goods[$kk]['already_stocked_number'] = $product_item['already_stocked_number'];
                                $temp_goods[$kk]['cargo_distribution_number'] = $WarehouseTypeGoods['available_in_stock'];
                                unset($temp_goods[$kk]['dispensable_number']);//第二次大循环会取第一次值过来
                                //仓库库存不足
                                if ($WarehouseTypeGoods['available_in_stock'] < $dispensable_number) {
                                    $clock = true;
                                    $temp_goods[$kk]['dispensable_number'] = $WarehouseTypeGoods['available_in_stock'];
                                } else if ($WarehouseTypeGoods['available_in_stock'] >= $dispensable_number) {
                                    $temp_goods[$kk]['dispensable_number'] = $dispensable_number;
                                } else {
                                    $temp_goods[$kk]['dispensable_number'] = $dispensable_number;
                                }
                            }
                            if ($available_in_stock <= 0) {
                                $info[] = $order_item['order_number'] . '仓库库存不足';
                                echo '定时配货订单号：'.$order_item['order_number']."仓库库存不足\r\n";
                                Orders::where('id', $order_item['id'])->update(['problem' => Orders::C_PROBLEM]);
                                continue;
                            }


                            $distributionOrderParams['goods'] = $temp_goods;
                            //仓库可配货数量不足
                            if ($clock) {
                                Orders::where('id', $order_item['id'])->update(['problem' => Orders::A_PROBLEM]);
                            }
                            //再次校验仓库物流是否可用
                            $SettingWarehouseExists = SettingWarehouse::where(['id'=>$distributionOrderParams['warehouse_id'],'disable'=>SettingWarehouse::ON])->exists();
                            if($SettingWarehouseExists)
                            {
                                $SettingLogisticsExists = SettingLogistics::where(['id'=>$distributionOrderParams['logistic_id'],'disable'=>SettingLogistics::LOGISTICS_STATUS_USING])->exists();
                                if($SettingLogisticsExists)
                                {
                                    $SettingLogisticsWarehousesExists =  SettingLogisticsWarehouses::where(['logistic_id'=>$distributionOrderParams['logistic_id'],'warehouse_id'=>$distributionOrderParams['warehouse_id']])->exists();
                                    $SettingLogisticsWarehousesExists || Orders::where('id', $order_item['id'])->update(['problem' => Orders::O_PROBLEM]);//其它
                                }else
                                {
                                    Orders::where('id', $order_item['id'])->update(['problem' => Orders::D_PROBLEM]);//无法找到物流
                                    echo '定时配货订单号：'.$order_item['order_number']."无法找到物流\r\n";
                                }
                            }
                            else
                            {
                                Orders::where('id', $order_item['id'])->update(['problem' => Orders::C_PROBLEM]);//无法找到仓库
                                echo '定时配货订单号：'.$order_item['order_number']."无法找到仓库\r\n";
                            }
                            $info[] = $PendingHandle->distributionOrderProcess($distributionOrderParams, $user_id,false);
                        }
                    }
                }
            } while (0);
        }
    }