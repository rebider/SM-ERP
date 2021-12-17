<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/3/22
     * Time: 13:57
     */

    namespace App\Http\Controllers;

    use App\Common\Common;
    use App\Common\FTP;
    use App\Console\Commands\UpdateShipmentProcessing;
    use App\Console\Logic\TrackingNo;
    use App\Console\Logic\UpdateShipment;
    use App\Exceptions\DataNotFoundException;
    use App\Http\Services\Goods\RakutenGoodsHandle;
    use App\Http\Services\Order\DistrbutionHandle;
    use App\Http\Services\Order\OrdersService;
    use App\Http\Services\Order\PendingHandle;
    use App\Http\Services\Rakuten\GetItem;
    use App\Http\Services\Rakuten\InsertItem;
    use App\Models\HttpCurl;
    use App\Models\Orders;
    use App\Models\OrdersInvoices;
    use App\Models\OrdersProducts;
    use App\Models\OrdersTroublesRecord;
    use App\Models\RakutenService;
    use App\Models\RulesLogisticAllocation;
    use App\Models\RulesOrderTrouble;
    use App\Models\RulesWarehouseAllocation;
    use App\Models\SettingLogistics;
    use App\Models\SettingLogisticsWarehouses;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseTypeGoods;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class testController extends Controller
    {
        public function index()
        {

            //异步断点上传商品图片
//            $url = 'http://127.0.0.1:9055/api/ratuken_img';
//            $params['link'] = array(1,8,0);
//            $params['dir'] = __FILE__;
//            $params['token'] = config('api.token');
//            $header = [
//                "Content-Type:application/json",
//            ];
//            (new Common())->curl_send($url, $params,$header,'POST',false,1);
//
//            file_put_contents('./storage/2.txt','2222');

           //RulesWarehouseAllocation::warehouseAllocationMatching();
           //RulesLogisticAllocation::logisticAllocationMatching();
            //RulesOrderTrouble::orderTroubleFilter();
            //没有问题的订单或者问题已经处理了 匹配上了仓库和物流的订单跑自动配货

           // (new UpdateShipment())->handel();
            $params['link'][] = '201907050257157154.jpg';
            $params['dir'] = '19070573';
            $params['ftp']['ftp_user'] = 'dream789';
            $params['ftp']['ftp_pass'] = 'Dream618';
            (new RakutenGoodsHandle)->asynFtpImageProcessing($params);
        }
    }
