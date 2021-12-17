<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/10
     * Time: 17:09
     */

    namespace App\Console\Logic;

    use App\Common\Common;
    use App\Console\Commands\GetmergeRulesOrder;
    use App\Models\RulesLogisticAllocation;
    use App\Models\RulesOrderTrouble;
    use App\Models\RulesWarehouseAllocation;
    use Illuminate\Support\Facades\DB;

    class OrderRules
    {
        private $fileLock = 'TrackingNo.lock';//文件锁

        public function __construct($comand)
        {
            $this->comandObj = $comand;
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
                echo $e->getMessage() . $e->getLine();
            } catch (\Throwable $e) {
                echo $e->getMessage() . $e->getLine();
            } finally{
                $this->end();
            }
        }

        /**
         * @return mixed
         * Note: 订单规则处理
         * Date: 2019/5/9 14:00
         * Author: zt8067
         */
        public function handel()
        {
            DB::connection()->disableQueryLog();
            //订单问题过滤
            echo "订单问题规则任务开始" . "\r\n";
            RulesOrderTrouble::orderTroubleFilter();
            echo "订单问题规则任务结束" . "\r\n";
            sleep(6);
            $GetmergeRulesOrder = new GetmergeRulesOrder();
            $GetmergeRulesOrder->handle();
            sleep(6);
            //仓库匹配
            echo "仓库匹配规则任务开始" . "\r\n";
            RulesWarehouseAllocation::warehouseAllocationMatching();
            echo "仓库匹配规则任务结束" . "\r\n";
            sleep(6);
            //物流匹配
            echo "物流匹配规则任务开始" . "\r\n";
            RulesLogisticAllocation::logisticAllocationMatching();
            echo "物流匹配规则任务结束" . "\r\n";
            //配货单匹配
            echo "配货单任务开始" . "\r\n";
            (new Distribution())->run();
            echo "配货单任务结束" . "\r\n";
        }
    }