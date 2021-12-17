<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/10
     * Time: 17:09
     */

    namespace App\Console\Logic;

    use App\Common\Common;
    use App\Exceptions\DataNotFoundException;
    use App\Models\OrdersInvoices;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseSecretkey;

    class TrackingNo
    {
        private $fileLock = 'TrackingNo.lock';//文件锁

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
                echo '物流跟踪号任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                Common::mongoLog($e, '物流跟踪号', '仓库获取物流跟踪号', __FUNCTION__);
            } catch (\Throwable $e) {
                echo '物流跟踪号任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                Common::mongoLog($e, '物流跟踪号', '仓库获取物流跟踪号', __FUNCTION__);
            } finally{
                $this->end();
            }
        }

        /**
         * @return mixed
         * Note: 仓库获取物流跟踪号
         * $order_id  手动操作 订单部分退款
         * Date: 2019/4/16 14:00
         * Author: zt8067
         */
        public function handel($order_id = '')
        {
            do {

                $connection = OrdersInvoices::query();
                $connection->where('type', SettingWarehouse::SM_TYPE);
                $connection->where('warehouse_order_number', '<>', '');
                $connection->where('tracking_no', '');//物流为空
                $connection->where('delivery_status',OrdersInvoices::DELIVERY_STATUS_NO); //未发货
                $connection->where('intercept_status',OrdersInvoices::ORDER_INTERCEPT_STATUS_INITIAL);//未拦截
                $connection->where('invoices_status',OrdersInvoices::ENABLE_INVOICES_STATUS);//未作废
                 //未更新 因为物流同步存在api接口延迟返回物流号问题。只能消耗性能不停去抓取物流号
                    //->where('ware_number', '<', 3)
                if($order_id){
                    $connection->where('order_id',$order_id);
                }else{
                    $connection->limit(5000);
                }

                $OrdersInvoicesM = $connection->get();
                if (empty($OrdersInvoicesM)) {
                    throw new DataNotFoundException('');
                }
                $OrdersInvoices = $OrdersInvoicesM->toArray();

                //仓库信息配置
                $warehouseConfigInfo = WarehouseSecretkey::where([
                    'status'=>WarehouseSecretkey::STATUS_ON,
                ])->get(['user_id','appToken','appKey']);
                if ($warehouseConfigInfo ->isEmpty()) {
                    echo "无仓库密钥配置信息\r\n";
                    break ;
                }
                $warehouseConfigInfo = $warehouseConfigInfo->toArray();
                $userIdArr = array_column($warehouseConfigInfo,'user_id');
                foreach ($OrdersInvoices as $invoice_item) {
                    $arr['order_code'] = $invoice_item['warehouse_order_number'];
                    $userKey = array_search($invoice_item['user_id'], $userIdArr);
                    if (is_bool($userKey)) {
                        echo '异常信息:用户ID '.$invoice_item['user_id'].'无密钥配置'."\r\n";
                        continue;
                    }
                    $accout['appToken'] = $warehouseConfigInfo [$userKey] ['appToken'];
                    $accout['appKey'] = $warehouseConfigInfo [$userKey] ['appKey'];
                    $response = (new Common)->sendWarehouse('getOrderByCode', $arr,$accout);//已调整
                    if (empty($response)) {
                        throw new \Exception('仓库接口异常，或网络错误');
                    }
                    if ($response['ask'] == 'Success') {
                       $data = $response['data'];
                    /*   订单状态
                        C:待发货审核
                        W:待发货
                        D:已发货
                        H:暂存
                        N:异常订单
                        P:问题件
                        X:废弃
                    */
                        if($data['order_status']=='D'){
                            OrdersInvoices::where('id', $invoice_item['id'])->increment('ware_number');
                            OrdersInvoices::where('id', $invoice_item['id'])->update([
                                'tracking_no'     => $data['tracking_no'],
                                'delivery_status' => OrdersInvoices::DELIVERY_STATUS_YES,
                            ]);
                            echo '配货单号'.$invoice_item['invoices_number']."获取物流跟踪号成功！\r\n";
                        }
                    } else {
                        OrdersInvoices::where('id', $invoice_item['id'])->increment('ware_number');
                        echo  '配货单号'.$invoice_item['invoices_number'].$response['message']."\r\n";;
                    }
                }
            } while (0);
        }

        /**
         * @return bool
         * Note: cli模式
         * Data: 2019/5/31 14:17
         * Author: zt7785
         */
        public function is_cli()
        {
            return preg_match("/cli/i", php_sapi_name()) ? true : false;
        }
    }