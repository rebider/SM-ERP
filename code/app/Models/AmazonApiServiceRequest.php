<?php

namespace App\Models;

use App\AmazonMWS\GithubMWS\MCS\AmazonMarketPlaceProduct;
use App\AmazonMWS\GithubMWS\MCS\MWSClient;
use App\Http\Services\Goods\AmazonSaleFeed;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class AmazonApiServiceRequest extends Model
{
    protected $table = 'amazon_api_service_request';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","method","params","request_table","request_pk","request_user_id","request_shop_id","is_finished","exception_info","FeedSubmissionId","created_at","updated_at"];

    /**
     * @var 已完成
     */
    const IS_FAILED = 1;
    /**
     * @var 已完成
     */
    const IS_FINISHED = 1;
    /**
     * @var 未完成
     */
    const UN_FINISHED = 0;

    /**
     * @var array 待处理的上传数据
     */
    public $feedLists = [];

    public $mothedParam = [
        'postProducts'=>['MWSProduct'],
        'deleteProductBySKU'=>['deleteProducts'],
        'updatePrice'=>['standardprice','saleprice'],
        'updateStock'=>['productStock'],
        'updateGoodsImage'=>['standardImage'],
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 平台
     * Data: 2019/3/22 18:23
     * Author: zt8076
     */
    public function SettingShops(){

        return $this->belongsTo(SettingShops::class,'request_shop_id','id');
    }

    /**
     * Note: 亚马逊上传数据结果处理
     * Data: 2019/6/13 14:53
     * Author: zt7785
     */
    public function feedSubmitRequestLogic ($feedInfos,$current_time)
    {
        $ordersInvoicesModel = new OrdersInvoices();
        //需要用list处理的数据类型 _POST_PRODUCT_DATA_ _POST_FLAT_FILE_LISTINGS_DATA_
        //1.以店铺维度分组 避免多次初始化请求类
        $MarketplaceIds = (new OrdersInvoices())->MarketplaceIds;
        foreach ($feedInfos as &$feedInfo) {
            if (!isset($this->feedLists [$feedInfo ['request_shop_id']])) {
                $this->feedLists [$feedInfo ['request_shop_id']] = [];
                $cusSaleInfo ['MarketplaceId'] = $feedInfo ['setting_shops']['Marketplace_Id'];
                $cusSaleInfo ['Amazon_MWS_Endpoint'] = 'https://' .$MarketplaceIds [$cusSaleInfo ['MarketplaceId']];
                $cusSaleInfo ['sellerId'] = $feedInfo ['setting_shops'] ['seller_id'];
                $cusSaleInfo ['license_key'] = $feedInfo ['setting_shops'] ['license_key'];
                $cusSaleInfo ['service_secret'] = $feedInfo ['setting_shops'] ['service_secret'];
                $cusSaleInfo ['user_id'] = $feedInfo['request_user_id'];
                $cusSaleInfo ['shop_name'] = $feedInfo ['setting_shops'] ['shop_name'];
                $cusSaleInfo ['shop_id'] = $feedInfo ['setting_shops'] ['id'];

                $cusSaleInfo ['Marketplace_Id'] = $feedInfo ['setting_shops']['Marketplace_Id'];
                $cusSaleInfo ['Amazon_MWS_Endpoint'] = 'https://' .$MarketplaceIds [$cusSaleInfo ['MarketplaceId']];
                $cusSaleInfo ['Seller_Id'] = $feedInfo ['setting_shops'] ['seller_id'];
                $cusSaleInfo ['Access_Key_ID'] = $feedInfo ['setting_shops'] ['license_key'];
                $cusSaleInfo ['Secret_Access_Key'] = $feedInfo ['setting_shops'] ['service_secret'];
                $cusSaleInfo ['user_id'] = $feedInfo['request_user_id'];
                $cusSaleInfo ['Application_Name'] = $feedInfo ['setting_shops'] ['shop_name'];

                $this->feedLists [$feedInfo ['request_shop_id']] ['sale_info'] = $cusSaleInfo ;
                $this->feedLists [$feedInfo ['request_shop_id']] ['feedInfo'] = [];
            }

            $this->feedLists [$feedInfo ['request_shop_id']] ['feedInfo'] [] = $feedInfo;
        }
            //上传数据处理
            $amazonSaleFeed = new AmazonSaleFeed();
            foreach ($this->feedLists as $feedList) {
                $amazonService = new MWSClient($feedList ['sale_info'],true);
                DB::beginTransaction();
                try {
                    foreach ($feedList ['feedInfo'] as $feed) {
                        $apiFeedData = [];
                        //判断方法是否存在
                        if (!method_exists ($amazonSaleFeed,$feed['method'])) {
                            continue;
                        }
                        $method = $feed['method'];
                        $param = json_decode($feed['params'],true);
                        $requestInfo = $amazonSaleFeed->$method($param);
                        //param逻辑
                        $paramInfo = $this->mothedParam[$requestInfo ['method']];
                        $firstOpt = $requestInfo ['param'] [$paramInfo [0]];
                        $requestMethod = $requestInfo['method'];
                        if ($method == 'updatePrice') {
                            if (isset($param [$paramInfo [1]])) {
                                $secondOpt = $requestInfo ['param'] [$paramInfo [0]];
                                $FeedSubmissionResponse = $amazonService->$requestMethod($firstOpt,$secondOpt);
                            }
                        } else {
                            if ($method == 'putOn') {
                                $products = [];
                                $product = new AmazonMarketPlaceProduct();
                                foreach ($param as $paramKey => $paramVal) {
                                    if (property_exists($product, $paramKey)) {
                                        $product->$paramKey = $paramVal;
                                    }
                                }
                                array_push($products, $product);
                                $FeedSubmissionResponse = $amazonService->$requestMethod($products);
                            } else {
                                $FeedSubmissionResponse = $amazonService->$requestMethod($firstOpt);
                            }

                        }
//                    dd($paramInfo,$firstOpt,$method,$requestMethod,$response_param = array_merge($param,['goods_online_id'=>$feed ['request_pk']]));
//                    $FeedSubmissionResponse = [
//                        "FeedSubmissionId" => "58414018058",
//                        "FeedType" => "_POST_FLAT_FILE_LISTINGS_DATA_",
//                        "SubmittedDate" => "2019-06-11T05:43:45+00:00",
//                        "FeedProcessingStatus" => "_SUBMITTED_",
//                        "RequestId" => "3dd2fc6e-cf55-436d-911f-4ab913feed45"
//                    ];
                        //请求成功 _SUBMITTED_
                        if (isset($FeedSubmissionResponse ['FeedSubmissionId'])) {
                            $amazonServiceApiData ['api_name'] = 'SubmitFeed';
                            $amazonServiceApiData ['RequestId'] = $FeedSubmissionResponse ['RequestId'];
                            $amazonServiceApiData ['FeedSubmissionId'] = $FeedSubmissionResponse ['FeedSubmissionId'];
                            $amazonServiceApiData ['FeedType'] = $FeedSubmissionResponse ['FeedType'];
                            $amazonServiceApiData ['SubmittedDate'] = $FeedSubmissionResponse ['SubmittedDate'];
                            $amazonServiceApiData ['FeedProcessingStatus'] = $FeedSubmissionResponse ['FeedProcessingStatus'];
                            $amazonServiceApiData ['is_finished'] = $ordersInvoicesModel->getFeedStatus($FeedSubmissionResponse ['FeedProcessingStatus']);
//                            $amazonServiceApiData ['request_user_id'] = 2;
//                            $amazonServiceApiData ['request_shop_id'] = 26;
                            $amazonServiceApiData ['request_user_id'] = $feed ['request_user_id'];
                            $amazonServiceApiData ['request_shop_id'] = $feed ['request_shop_id'];
                            //上架
                            $response_param = [];
                            if ($method == 'putOn') {
                                $response_param = array_merge($param,['goods_online_id'=>$feed ['request_pk']]);
                            } else if ($method == 'putOff') {
                                $response_param = array_merge($param,['goods_online_id'=>$feed ['request_pk'],'OperationType'=>'Delete']);
                            } else if ($method == 'editPrice') {
                                $response_param = ['goods_online_id'=>$feed ['request_pk']];
                            }
                            $amazonServiceApiData ['param'] = json_encode($response_param);
                            $amazonServiceApiData ['created_at'] = $amazonServiceApiData ['updated_at'] = $current_time;
                            AmazonApiServiceResponse::insert($amazonServiceApiData);
                            $apiFeedData ['is_finished'] = self::IS_FINISHED;
                            $apiFeedData ['FeedSubmissionId'] = $FeedSubmissionResponse ['FeedSubmissionId'];
                            $apiFeedData ['updated_at'] = $current_time;
                        }
                        if (!empty($apiFeedData)) {
                            self::where('id',$feed ['id'])->update($apiFeedData);
                        }
                        sleep(30);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $exception_data = [
                        'start_time'                => $current_time,
                        'msg'                       => '失败信息：' . $e->getMessage(),
                        'line'                      => '失败行数：' . $e->getLine(),
                        'file'                      => '失败文件：' . $e->getFile(),
                        'params'                    => '异常参数：' .json_encode($feed),
                    ];
                    $exception ['path'] = __FUNCTION__;
                    LogHelper::setExceptionLog($exception_data,$exception ['path']);
                    $exception ['type'] = 'task';
                    $dingPushData ['task'] = 'AmazonFeed接口请求任务';
                    $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
                    DingRobotWarn::robot($exception,$dingPushData);
                    LogHelper::info($exception_data,null,$exception ['type']);
                }
            }
    }


    public static function postData($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }
}
