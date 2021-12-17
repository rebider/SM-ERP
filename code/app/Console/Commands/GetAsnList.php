<?php

namespace App\Console\Commands;

use App\Common\Common;
use App\Models\PurchaseOrders;
use App\Models\WarehouseSecretkey;
use App\Models\WarehouseTypeGoods;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetAsnList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetAsnList';  //获取入库单信息--物流

    private $parchaseOrder = [];
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function getAsnList_($params,$account)
    {
        $common = new Common();
        return $re = $common->sendWarehouse('getAsnList', $params,$account);       //接口请求

    }

    private function addStock()
    {
        echo "开始修改库存\r\n";
        DB::beginTransaction();
        try {
            $len = count($this->parchaseOrder);
//            for ($z = 0; $z < $len; $z++){
//                $parchaseArr = $this->parchaseOrder[$z];
//                $parchaseOrderInfo = PurchaseOrders::getPurchaseOrderId($parchaseArr[0]['receiving_code']);        //获取采购单id
//                if (empty($parchaseOrderInfo)) {
//                    continue;
//                }
//                $parchaseOrderId = $parchaseOrderInfo->id;
            //purchase_order 采购单的仓库 procurement_plan_goods 采购计划的商品id procurement_plan_goods采购计划的数量
            //$purchaseOrdersGoods = PurchaseOrders::getPurchaseGoodsByStatus($parchaseOrderId);                             //获取商品信息-采购数量 商品id 仓库id
//                for ($y = 0; $y < count($purchaseOrdersGoods); $y++){
//                    //清空 仓库 商品 下在途库存 感觉不对劲
//                    WarehouseTypeGoods::emptyArr($purchaseOrdersGoods[$y]);                                       //清空在途库存
//                }
//            }
            $parchaseArr = [];
            for ($i = 0; $i < $len; $i++) {
                $parchaseArr = $this->parchaseOrder[$i];
                $parchaseOrderInfo = PurchaseOrders::getPurchaseOrderId($parchaseArr[0]['receiving_code']);        //获取采购单id
                if (empty($parchaseOrderInfo)) {
                    continue;
                }
                $parchaseOrderId = $parchaseOrderInfo->id;
                $PurchaseOrdersGoodsSum = PurchaseOrders::getPurchaseGoodsByStatus($parchaseOrderId);                             //获取商品信息-采购数量 商品id 仓库id
                for ($j = 0; $j < count($PurchaseOrdersGoodsSum); $j++) {
                    if (($parchaseArr[0]['receiving_status'] === 'W') || ($parchaseArr[0]['receiving_status'] === 'P') ||
                        ($parchaseArr[0]['receiving_status'] === 'Z') || ($parchaseArr[0]['receiving_status'] === 'G')) {  //在途

                        WarehouseTypeGoods::updateArr($PurchaseOrdersGoodsSum[$j], PurchaseOrders::ON_THE_WAY, $parchaseOrderInfo);  //增加库存表的在途库存
                    } else if ($parchaseArr[0]['receiving_status'] === 'F') {                                       //目的仓收货完成

                        WarehouseTypeGoods::updateArr($PurchaseOrdersGoodsSum[$j], PurchaseOrders::COMPLETE, $parchaseOrderInfo);    //增加库存表的可用数量
                    }
                }
                PurchaseOrders::updateArr($parchaseArr[0]);                                            //更新采购单状态
            }
            DB::commit();
            echo '全部' . $len . "件采购单修改库存成功\r\n";
        } catch (\Exception $e) {
            DB::rollback();
            info(__CLASS__, [$e]);
            echo 'catch_step_2' . "\r\n";
            echo $e->getLine() . "\r\n";
            echo $e->getCode() . "\r\n";
            echo $e->getMessage() . "\r\n";
        }
    }
    //把获取的商品信息存入数组
    private function stockArray($datas)
    {
        $this->parchaseOrder[] = $datas;
    }


    public function getCode($code)
    {
        foreach ($code as $value) {
            yield $value;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        date_default_timezone_set('PRC');//设置时间为中国时区
        exec("chcp 65001");//设置命令行窗口为中文编码
//        $rs = json_decode('[[{"receiving_code":"RV4485-190706-0001","income_type":"0","shipping_method":"KACHEYUNSHU-TEST","tracking_number":"","reference_no":"D201907060001","warehouse_code":"JPHWC","transit_warehouse_code":"JPH
//WC","receiving_status":"Z","receiving_desc":"","receiving_add_time":"2019-07-06 09:32:05","receiving_modify_time":"2019-07-06 09:32:05","eta_date":"2019-07-14","region_id_level0":"","region_id_level1":""
//,"region_id_level2":"","street":"","contacter":"","contact_phone":"","box_total":"1","sku_total":"50","sku_species":"1","items":[{"product_sku":"WWXX00005","quantity":"50","box_no":"1","package_type":"ow
//","received_qty":"0","putaway_qty":"0"}]}]]',true);
//        $this->stockArray($rs);  //把获取的商品存入数组
        //仓库信息配置
        $warehouseConfigInfo = WarehouseSecretkey::where([
            'status'=>WarehouseSecretkey::STATUS_ON,
        ])->get(['user_id','appToken','appKey']);
        if ($warehouseConfigInfo ->isEmpty()) {
            echo "无仓库密钥配置信息\r\n";
            return ;
        }
        $warehouseConfigInfo = $warehouseConfigInfo->toArray();
        $code = PurchaseOrders::getReceivingCode();
        if (empty($code)){
            echo "无入库单信息\r\n";
            return ;
        }
//        $code = [
//            'RV4485-190706-0009'
//        ];
        $userIdArr = array_column($warehouseConfigInfo,'user_id');
        foreach ( $this->getCode($code) as $value ) {
            if (empty($value ['receiving_code'])) {
                echo '异常信息:采购单ID '.$value ['id'].'无入库单号'."\r\n";
                continue;
            }
            $userKey = array_search($value['user_id'], $userIdArr);
            if (is_bool($userKey)) {
                echo '异常信息:用户ID '.$value['user_id'].'无密钥配置'."\r\n";
                continue;
            }

            $accout['appToken'] = $warehouseConfigInfo [$userKey] ['appToken'];
            $accout['appKey'] = $warehouseConfigInfo [$userKey] ['appKey'];
            $params = array(
                'pageSize' => 200,
                'page' => 1,
                'receiving_code' => $value ['receiving_code']
            );
            $rs = $this->getAsnList_($params,$accout);
            try {
                if ($rs['ask'] != 'Success') {
                    echo  $rs['message']+$value ['receiving_code']+'<br/>'. "\r\n";
                    continue ;
                }
                echo '客户ID'.$value ['user_id'].'入库单号:'.$value ['receiving_code'].'请求数据成功;'. "\r\n";
                if (empty($rs['data'])) {
                    echo '客户ID'.$value ['user_id'].'响应数据为空;'. "\r\n";
                    continue;
                }
                $this->stockArray($rs['data']);  //把获取的商品存入数组
            }
            catch (\Exception $e) {
                Log::error($e) ;
                echo 'catch_step_1'. "\r\n";
                echo $e->getLine(). "\r\n";
                echo $e->getMessage(). "\r\n";
            }
        }
        if (empty($this->parchaseOrder)) {
            echo '无入库单响应数据;'. "\r\n";
            return;
        }
        $this->addStock();//数据录入
    }
}
