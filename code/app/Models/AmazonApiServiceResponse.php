<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class AmazonApiServiceResponse extends Model
{
    protected $table = 'amazon_api_service_response';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","api_name","RequestId","FeedSubmissionId","FeedType","SubmittedDate","FeedProcessingStatus","request_user_id","request_shop_id","is_finished","param","created_at","updated_at"];
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
    public function feedSubmitResultLogic ($feedInfos,$current_time)
    {
        $ordersInvoicesModel = new OrdersInvoices();
        //需要用list处理的数据类型 _POST_PRODUCT_DATA_ _POST_FLAT_FILE_LISTINGS_DATA_
        $listArr = ['_POST_PRODUCT_DATA_','_POST_FLAT_FILE_LISTINGS_DATA_'];
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
                $cusSaleInfo ['user_id'] = $feedInfo ['request_user_id'];
                $cusSaleInfo ['shop_name'] = $feedInfo ['setting_shops'] ['shop_name'];
                $cusSaleInfo ['shop_id'] = $feedInfo ['setting_shops'] ['id'];
                $this->feedLists [$feedInfo ['request_shop_id']] ['sale_info'] = $cusSaleInfo ;
                $this->feedLists [$feedInfo ['request_shop_id']] ['feedInfo'] = [];
            }

            if (in_array($feedInfo ['FeedType'],$listArr)) {
                $feedInfo ['requestMethod'] = 'getFeedSubmissionList';
            } else {
                $feedInfo ['requestMethod']  = 'getFeedSubmissionResult';
            }
            $this->feedLists [$feedInfo ['request_shop_id']] ['feedInfo'] [] = $feedInfo;
        }
        //上传数据处理
        foreach ($this->feedLists as $feedList) {
                $amazonService = new AmazonServices($feedList ['sale_info'],true);
                DB::beginTransaction();
                try {
                    foreach ($feedList ['feedInfo'] as $feed) {
                        if (!method_exists($amazonService, $feed['requestMethod'])) {
                            continue;
                        }
                        $method = $feed['requestMethod'];
                        $FeedSubmissionResponse = $amazonService->$method($feed ['FeedSubmissionId']);
                        $apiFeedData = [];
//                     模拟数据
//                    $FeedSubmissionResponse = ["RequestId" => "08c9e808-4818-4fed-a3f9-46274e76f57d",
//                                  "FeedSubmissionInfo" =>  [
//                                    0 =>  [
//                                      "FeedSubmissionId" => "58657018065",
//                                      "FeedType" => "_POST_FLAT_FILE_LISTINGS_DATA_",
//                                      "SubmittedDate" => "2019-06-18T12:44:56Z",
//                                      "FeedProcessingStatus" => "_DONE_",
//                                      "StartedProcessingDate" => "2019-06-18T12:45:02Z",
//                                      "CompletedProcessingDate" => "2019-06-18T12:45:42Z",
//                                    ]
//                            ]
//                    ];

                        if ($method == 'getFeedSubmissionList') {
                            if (isset($FeedSubmissionResponse ['exception_Info'])) {
                                //抛异常
                                $apiFeedData ['exception_info'] = json_encode($FeedSubmissionResponse ['exception_Info']);
                                //上架
                                if ($feed ['FeedType'] == '_POST_FLAT_FILE_LISTINGS_DATA_') {
                                    //csv文件上传
                                    $this->putonDone($feed, GoodsOnlineAmazon::PUTON_FAIL);
                                }
                                //商品下架
                                if ($feed ['FeedType'] == '_POST_PRODUCT_DATA_') {
                                    $OperationType = json_decode($feed ['param'], true);
                                    if ($OperationType ['OperationType'] == 'Delete') {
                                        $this->putoffDone($OperationType, GoodsOnlineAmazon::PUTOFF_FAIL);
                                    }
                                }
                            } else {
                                //FeedSubmissionInfo 不为空
                                if (!empty($FeedSubmissionResponse ['FeedSubmissionInfo'])) {
                                    $apiFeedData ['is_finished'] = $ordersInvoicesModel->getFeedStatus($FeedSubmissionResponse ['FeedSubmissionInfo'] [0] ['FeedProcessingStatus']);
                                    //已完成
                                    if ($FeedSubmissionResponse ['FeedSubmissionInfo'] [0] ['FeedProcessingStatus'] == '_DONE_') {
                                        //上架
                                        if ($feed ['FeedType'] == '_POST_FLAT_FILE_LISTINGS_DATA_') {
                                            //csv文件上传
                                            $this->putonDone($feed);
                                        }
                                        //商品下架
                                        if ($feed ['FeedType'] == '_POST_PRODUCT_DATA_') {
                                            $OperationType = json_decode($feed ['param'], true);
                                            if ($OperationType ['OperationType'] == 'Delete') {
                                                $this->putoffDone($OperationType);
                                            }
                                        }
                                    } else if ($FeedSubmissionResponse ['FeedSubmissionInfo'] [0] ['FeedProcessingStatus'] == '_CANCELLED_') {
                                        //失败 取消
                                        //上架
                                        if ($feed ['FeedType'] == '_POST_FLAT_FILE_LISTINGS_DATA_') {
                                            //csv文件上传
                                            $this->putonDone($feed, GoodsOnlineAmazon::PUTON_FAIL);
                                        }
                                        //商品下架
                                        if ($feed ['FeedType'] == '_POST_PRODUCT_DATA_') {
                                            $OperationType = json_decode($feed ['param'], true);
                                            if ($OperationType ['OperationType'] == 'Delete') {
                                                $this->putoffDone($OperationType, GoodsOnlineAmazon::PUTOFF_FAIL);
                                            }
                                        }
                                    }
                                }
                            }
                        } else if ($method == 'getFeedSubmissionResult') {
                            if (isset($FeedSubmissionResponse ['ProcessingSummary'])) {
                                if (empty($FeedSubmissionResponse ['ProcessingSummary'] ['MessagesWithError'] || empty($FeedSubmissionResponse ['ProcessingSummary'] ['MessagesProcessed']))) {
                                    if ($FeedSubmissionResponse ['ProcessingSummary'] ['MessagesSuccessful'] > 0) {
                                        $apiFeedData ['is_finished'] = self::IS_FINISHED;
                                        //物流跟踪号回传 成功
                                        if ($feed ['FeedType'] == '_POST_ORDER_FULFILLMENT_DATA_') {
                                            $this->shippingDone($feed);
                                        } else if ($feed ['FeedType'] == '_POST_PRODUCT_PRICING_DATA_') {
                                            //清除异常信息
                                            $this->updateResultException($feed,'' );
                                        }
                                    }
                                } else {
                                    //异常信息
                                    if (isset($FeedSubmissionResponse ['Result'])) {
                                        $apiFeedData ['exception_info'] = json_encode($FeedSubmissionResponse ['Result']);
                                    }
                                    //物流跟踪号回传 成功
                                    if ($feed ['FeedType'] == '_POST_ORDER_FULFILLMENT_DATA_') {
                                        $this->shippingDone($feed, OrdersInvoices::PASS_BACK_STATUS_FAIL);
                                    } else if ($feed ['FeedType'] == '_POST_PRODUCT_PRICING_DATA_') {
                                        foreach ($FeedSubmissionResponse ['Result'] as $Result) {
                                            if ($Result ['ResultMessageCode'] == 90057) {
                                                $this->updateResultException($feed,$Result ['ResultDescription'] );
                                            }
                                        }
                                    }

                                }
                            }
                        }
                        if (!empty($apiFeedData)) {
                            self::where('id', $feed ['id'])->update($apiFeedData);
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
                    $dingPushData ['task'] = 'AmazonFeed响应数据处理结果查询任务';
                    $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
                    DingRobotWarn::robot($exception,$dingPushData);
                    LogHelper::info($exception_data,null,$exception ['type']);
                }
        }
    }

    /**
     * @param $feed
     * Note: 物流跟踪号回传成功
     * Data: 2019/6/13 15:51
     * Author: zt7785
     */
    public function shippingDone ($feed,$pass_back_status = OrdersInvoices::PASS_BACK_STATUS_SUCC) {
        $invoicesInfo = json_decode($feed ['param'],true);
        if ($invoicesInfo) {
            $invoicesData ['pass_back_status'] = $pass_back_status;
            $invoicesData ['updated_at'] = date('Y-m-d H:i:s');
            OrdersInvoices::where('id',$invoicesInfo ['invoice_id'])->update($invoicesData);
        }
    }

    /**
     * @param $feed
     * Note: 上架成功
     * Data: 2019/6/13 16:19
     * Author: zt7785
     */
    public function putonDone ($feed,$put_on_status = GoodsOnlineAmazon::PUTON_SUCC) {
        $putawayInfo = json_decode($feed ['param'],true);
        if ($putawayInfo) {
            if (isset($putawayInfo ['OperationType']) && $putawayInfo ['OperationType']) {
                //更新成功
                //失败
                if ($put_on_status == GoodsOnlineAmazon::PUTON_FAIL) {
                    $updateData ['synchronize_info'] = '未知异常';
                    $updateData ['updated_at'] = date('Y-m-d H:i:s');
                    GoodsOnlineAmazon::where('id',$putawayInfo ['goods_online_id'])->update($updateData);
                }
            } else {
                //上架成功
                //S1 草稿箱表数据上架状态
                $draftData ['updated_at'] = $putawayData ['updated_at'] = date('Y-m-d H:i:s');
                if (isset($putawayInfo ['goods_draft_id']) && $putawayInfo ['goods_draft_id']) {
                    $draftData ['synchronize_status'] = $put_on_status == GoodsOnlineAmazon::PUTON_SUCC ? GoodsDraftAmazon::STATUS_PUTON_SUCC : GoodsDraftAmazon::STATUS_PUTON_FAIL;
                    if ($draftData ['synchronize_status'] == GoodsDraftAmazon::STATUS_PUTON_FAIL) {
                        $draftData ['synchronize_info'] = '未知异常';
                    }
                    $draftRe = GoodsDraftAmazon::where('id',$putawayInfo ['goods_draft_id'])->update($draftData);
                }
                //S2 在线表数据状态
                $putawayData ['put_on_status'] = $put_on_status;
                $putawayData ['synchronize_info'] = '';
                $OnlineRe = GoodsOnlineAmazon::where('id',$putawayInfo ['goods_online_id'])->update($putawayData);
            }
        }
    }

    /**
     * @param $feed
     * Note: 商品下架
     * Data: 2019/6/13 16:27
     * Author: zt7785
     */
    public function putoffDone ($feed,$put_off_status = GoodsOnlineAmazon::PUTOFF_SUCC) {
            $putawayData ['put_off_status'] = $put_off_status;
            $putawayData ['updated_at'] = date('Y-m-d H:i:s');
            //下架失败
            if ($put_off_status == GoodsOnlineAmazon::PUTOFF_FAIL ) {
                $putawayData ['synchronize_info'] = '店铺信息异常或商品信息异常';
            } else {
                $putawayData ['synchronize_info'] = '';
            }

            GoodsOnlineAmazon::where('id',$feed ['goods_online_id'])->update($putawayData);
    }

    /**
     * Note: 上架结果处理
     * Data: 2019/7/6 15:09
     * Author: zt7785
     */
    public function updateResultException  ($feed,$exceptionInfo) {
        $onlineInfo = json_decode($feed ['param'],true);
        $putawayData ['synchronize_info'] = $exceptionInfo;
        $putawayData ['updated_at'] = date('Y-m-d H:i:s');
        GoodsOnlineAmazon::where('id',$onlineInfo ['goods_online_id'])->update($putawayData);
    }
}
