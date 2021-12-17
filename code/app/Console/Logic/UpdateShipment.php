<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/10
     * Time: 17:09
     */

    namespace App\Console\Logic;

    use App\Common\Common;
    use App\Http\Services\Order\OrdersService;

    class UpdateShipment
    {
        private $fileLock = 'UpdateShipment.lock';//文件锁

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
                echo '更新状态任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                Common::mongoLog($e,'更新发货状态','更新发货状态失败',__FUNCTION__);
            } catch (\Throwable $e) {
                echo '更新状态任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                Common::mongoLog($e,'更新发货状态','更新发货状态失败',__FUNCTION__);
            } finally{
                $this->end();
            }
        }

        /**
         * @return mixed
         * Note: 更新物流号 更新发货状态 发货数量
         * Date: 2019/5/31 9:00
         * Author: zt8067
         */
        public function handel()
        {

            (new TrackingNo)->handel();
            sleep(10);
            OrdersService::updateShipment();
        }
    }