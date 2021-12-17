<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/5
 * Time: 12:26
 */
namespace App\Console\Commands;
use App\Common\Common;
use App\Models\Goods;
use App\Models\LogHelper;
use App\Models\WarehouseGoods;
use App\Models\WarehouseSecretkey;
use Illuminate\Console\Command;

class CheckWareHouseGoods extends Command
{
    protected $signature = 'command:checkWareHouseGoods';

    protected $description = '商品同步检测任务';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        date_default_timezone_set('PRC');//设置时间为中国时区
//        exec("chcp 65001");//设置命令行窗口为中文编码
        $param['page'] = 1;
        $param['pageSize'] = 100;
        $common = new Common();
        try {
                //拿到所有的账户秘钥
                $secret = WarehouseSecretkey::select('appToken','appKey')->get();
                if($secret->isEmpty()) {
                    echo '请先添加速贸仓库!';
                    return false;
                }
                $secret_arr = $secret->toArray();
                foreach($secret_arr as $key => $val) {
                    do {
                        $accout['appToken'] = $val['appToken'];
                        $accout['appKey'] = $val['appKey'];
                        $product = $common->sendWarehouse('getProductList', $param,$accout);
                        if (($product['ask'] == 'Success') && !empty($product['data'])) {
                            foreach ($product['data'] as $k => $v) {
                                if ($v['product_status'] == 'S') {
                                    $update['sync'] = WarehouseGoods::STATUS;
                                    $update['sync_time'] = date('Y-m-d H:i:s');
                                    $goods_update['synchronization'] = Goods::SYNCHRONIZATION_SUCCESS;
                                    $res = WarehouseGoods::where(['sku'=>$v['product_sku']])->where('sync','<>',WarehouseGoods::STATUS)->update($update);
                                    Goods::where(['sku'=>$v['product_sku']])->where('synchronization','<>',Goods::SYNCHRONIZATION_SUCCESS)->update($goods_update);
                                    if(!$res) {
                                        $exception_data = [
                                            'start_time'                => date('Y-m-d H:i:s'),
                                            'msg'                       => 'sku为' . $v['product_sku'].'商品同步失败',
                                        ];
                                        LogHelper::setExceptionLog($exception_data,'商品同步检测');
                                    }
                                }
                            }
                        } else {
                            $exception_data = [
                                'start_time'                => date('Y-m-d H:i:s'),
                                'msg'                       => '商品同步检测接口请求异常',
                            ];
                            LogHelper::setExceptionLog($exception_data,'商品同步检测');
                            echo '商品同步检测接口请求异常';
                        }
                    }while(($product['ask'] == 'Success') && (isset($product['nextPage'])) && ($product['nextPage'] == 'true') && ($param['page']++));
                }
            echo '商品同步检测成功';
        } catch (\Exception $e) {
            echo $e->getMessage();
            Common::mongoLog($e,'商品同步检测','失败',__FUNCTION__);
        }
    }

}