<?php

namespace App\Http\Controllers\Goods;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Auth\Models\RolesShops;
use App\Http\Controllers\Order\OrderController;
use App\Models\CategorysAmazon;
use App\Models\CategorysRakuten;
use App\Models\GoodsOnlineAmazon;
use App\Models\GoodsOnlineAmazonPics;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingCurrencyExchangeMaintain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Excel;
use App\Models\GoodsAttribute;
use App\Models\Platforms;
use App\Models\SettingShops;
use Illuminate\Support\Facades\DB;

class AmazonOnlineGoodsController extends Controller
{
    /**
     * @var 商品上架
     */
    const ON_SALE = 1;
    /**
     * @var 商品上架初始化
     */
    const ON_SALE_INITIALIZE = 0;
    /**
     * @var 商品下架
     */
    const OFF_SALE = 2;
    /**
     * @var 商品下架
     */
    const OFF_SALE_INITIALIZE = 0;
    /**
     * @var 商品更新失败
     */
    const SYNCHRONIZE_FAILED = 3;
    /**
     * @author zt12779
     * @var 商品目录ID
     */
    const ORDER_MENUS_ID = 2;


    public function index(Request $request)
    {
        $menusModel = new Menus();
        $responseData ['shortcutMenus'] = $menusModel->getShortcutMenu(self::ORDER_MENUS_ID);
        return view("Goods.AmazonOnline.index")->with($responseData);
    }

    /**
     * @desc 亚马逊在线商品查询
     * @author zt6650
     * CreateTime: 2019-04-11 16:54
     * @param Request $request
     * @return array
     */
    public function ajaxGetAllByParams(Request $request)
    {
        $pageIndex  = $request->get('page' ,1) ;
        $pageSize   = $request->get('limit' ,20) ;
        $params     = $request->get('data' ,[]) ;
        $params['page'] = $pageIndex;
        $params['limit'] = $pageSize;

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if (empty($shopsId)) {
                    $res = array(
                        'code' => '0',
                        'msg' =>'未配置店铺权限',
                    );
                    return parent::layResponseData($res);
                }
                //店铺id
                $params ['source_shop'] = $shopsId;
            } else {
                //未配置店铺 直接响应空
                $res = array(
                    'code' => '0',
                    'msg' =>'未配置店铺权限',
                );
                return parent::layResponseData($res);
            }
        } else {
            $user_id = $currentUser->userId;
        }

//        $collection = $this->Amazon->search($params) ;
//        $count = $collection->count() ;
//        $data = $collection->skip(($pageIndex-1)*$pageSize)->take($pageSize)->get()->toArray();

        $data = GoodsOnlineAmazon::getList($params, $user_id);

        $res = array(
            'code' => '0',
            'msg' =>'',
            'count' => $data['total'],
            'data'  => $data['data']
        );
        return $this->layResponseData($res) ;
    }

    /**
     * 查看在线商品详情
     * Auth zt12779
     * Created at
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function detail(Request $request)
    {
        $id = $request->get('id');
        $data = GoodsOnlineAmazon::getOne($id);
        if (!$data || empty($data)) {
            return $this->layResponseData(['code' => -1, 'msg' => '没有该数据']);
        }
        $categoryInArray = explode(',', $data['amazon_category_id']);

        $data['amazon_category_JP'] = CategorysAmazon::getCategoryStringInSort($categoryInArray);
        $data['goods_keywords'] = explode(',', $data['goods_keywords']);
        $data['goods_label'] = explode(',', $data['goods_label']);
        $result['goods'] = $data->toArray();
        return view('Goods.AmazonOnline.detail')->with($result);
    }

    /**
     * 导出在线商品
     * Author: zt12779
     * @param Request $request
     * @param Excel $excel
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exportData(Request $request, Excel $excel)
    {
        $ids = $request->get('ids');
        $idToArray = explode(',', $ids);
        if (empty($idToArray)) {
            return redirect()->back()->with('errors','请求参数异常');
        }

        foreach ($idToArray as $key => $val) {
            if (!is_numeric($val)) {
                return redirect()->back()->with('errors','请求参数异常');
            }
        }
        $result = GoodsOnlineAmazon::getExportData($idToArray);

        foreach ($result as $key => $val) {
            if ($val['put_on_status'] == 1) {
                $result[$key]['synchronize_status'] = '上架';
            }
            if ($val['put_off_status'] == 1) {
                $result[$key]['synchronize_status'] = '下架';
            }
            if ($val['put_on_status'] == 1 && !empty($val['synchronize_info'])) {
                $result[$key]['synchronize_status'] = '更新失败';
            }
            $categoryInArray = explode(',', $val['amazon_category_id']);
            $result[$key]['amazon_category'] = CategorysAmazon::getCategoryStringInSort($categoryInArray);
        }

        $i = 0;
        $printInfo = [];
        $orderController = new OrderController();
        foreach ($orderController->exportYield($result) as $key => $goodsValue) {
            if (empty($goodsValue)) {
                continue;
            }

            $printInfo[$i][] = $goodsValue['local_sku'];
            $printInfo[$i][] = $goodsValue['shop_name'];
            $printInfo[$i][] = $goodsValue['amazon_category'];
            $printInfo[$i][] = $goodsValue['seller_sku'];
            $printInfo[$i][] = $goodsValue['upc'];
            $printInfo[$i][] = $goodsValue['ASIN'];
            $printInfo[$i][] = $goodsValue['brand'];
            $printInfo[$i][] = $goodsValue['manufacturer'];
            $printInfo[$i][] = $goodsValue['color'];
            $printInfo[$i][] = $goodsValue['goods_size'];
            $printInfo[$i][] = $goodsValue['currency_code'];
            $printInfo[$i][] = $goodsValue['sale_price'];
            $printInfo[$i][] = $goodsValue['promotion_price'];
            $printInfo[$i][] = $goodsValue['promotion_start_time'];
            $printInfo[$i][] = $goodsValue['promotion_end_time'];
            $printInfo[$i][] = $goodsValue['goods_name'];
            $printInfo[$i][] = $goodsValue['title'];
            $printInfo[$i][] = $goodsValue['goods_description'];
            $printInfo[$i][] = $goodsValue['platform_in_stock'];
            foreach (explode(',', $goodsValue['goods_keywords']) as $val) {
                $printInfo[$i][] = $val;
            }
            foreach (explode(',', $goodsValue['goods_label']) as $val) {
                $printInfo[$i][] = $val;
            }
            $printInfo[$i][] = $goodsValue['goods_status'];
            $printInfo[$i][] = $goodsValue['synchronize_status'];
            $i ++;
        }
        $arr = ['自定义SKU','店铺','亚马逊分类','Seller SKU','UPC','ASIN','商品品牌','制造商','商品颜色','商品型号',
            '销售价格','销售币种', '促销价格', '促销开始时间', '促销结束时间', '商品名称', '商品标题', '商品描述',
            '平台库存', '关键词1', '关键词2', '关键词3', '关键词4', '关键词5', '商品标签1', '商品标签2', '商品标签3'
            , '商品标签4', '商品标签5', '物品状态', '商品状态'];
        array_unshift($printInfo,$arr);
        $this->export($excel, $printInfo, '在线商品详情',false,true);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 亚马逊三级分类
     * Data: 2019/6/17 18:17
     * Author: zt7785
     */
    public function getCategory(Request $request)
    {
        try {
            $parentId = $request->get('parentId');
            //39335
            if ($parentId == 0 ) {
                $result = CategorysAmazon::getCategoryByOpt(['categories_lv'=>1]);
            } else {
                $result = CategorysAmazon::getCategoryByOpt(['parentID'=>$parentId]);
            }
            return $this->layResponseData(['code' => 0, 'msg' => '', 'data' => $result]);
        } catch (\Exception $e) {
            return $this->layResponseData(['code' => 0, 'msg' => '服务器睡着了，刷一下~', 'data' => null]);
        }
    }

    /**
     * @param Request $request
     * @return $this
     * Note: 在线商品编辑
     * Data: 2019/6/19 16:41
     * Author: zt7785
     */
    public function editPage(Request $request)
    {
        $id = $request->get('id');
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser) || empty($id)) {
            abort(404);
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($user_id);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
                if (!empty($shopsId)) {
                    $shops = SettingShops::getShopsByShopsId($shopsId,Platforms::AMAZON);
                } else {
                    $shops = [];
                }
            } else {
                $shops = [];
            }
        } else {
            $user_id = $currentUser->userId;
            $shops = SettingShops::getShopByPlatId(Platforms::AMAZON,$user_id) ;
        }
        //获取商品信息
        $goodsOnlineInfo = GoodsOnlineAmazon::getGoodsInfoByIdUserId($id,$user_id);
        if (empty($goodsOnlineInfo)) {
            abort(404);
        }

        $goods_attribute = GoodsAttribute::getAllAttrs() ;
//        $currency = SettingCurrencyExchangeMaintain::where('user_id', $user_id)->get();
        $currency = SettingCurrencyExchange::get();
        if(!$currency->isEmpty()) {
            $currency = $currency->toArray();
        }
        $categorys['first'] = CategorysAmazon::getCategoryByOpt(['categories_lv'=>1]);
        if (isset($goodsOnlineInfo['amazon_category_id']) && !empty($goodsOnlineInfo['amazon_category_id']) ) {
            $categoryInArray = explode(',', $goodsOnlineInfo['amazon_category_id']);
            $categoryInfo['categoryInArray'] = $categoryInArray;
            unset($categoryInArray [0]);
            $categoryInArrayCount = count($categoryInArray);
            if ($categoryInArrayCount == 3) {
                $categorys['second'] = CategorysAmazon::getCategoryByOpt(['parentID'=>$categoryInArray[$categoryInArrayCount - 2]]);
                $categorys['third'] = CategorysAmazon::getCategoryByOpt(['parentID'=>$categoryInArray[$categoryInArrayCount - 1]]);
            } else if ($categoryInArrayCount == 2) {
                $categorys['second'] = CategorysAmazon::getCategoryByOpt(['parentID'=>$categoryInArray[$categoryInArrayCount - 1]]);
            }
        } else {
            $categoryInfo['categoryInArray'] = $categorys['second']  = $categorys['third'] = [];
        }
        $categoryInfo['category'] = $categorys;

        //关键词处理
        if (!empty($goodsOnlineInfo['goods_keywords'])) {
            $keywords = explode(',', $goodsOnlineInfo['goods_keywords']);
            foreach ($keywords as $key => $val) {
                $index = $key+1;
                $goodsOnlineInfo["goods_keywords{$index}"] = $val;
            }
        }
        //标签处理
        if (!empty($goodsOnlineInfo['goods_label'])) {
            $label = explode(',', $goodsOnlineInfo['goods_label']);
            foreach ($label as $key => $val) {
                $index = $key+1;
                $goodsOnlineInfo["goods_label{$index}"] = $val;
            }
        }
        return view('Goods.AmazonOnline.edit')->with(
            [
            'goods'=>$goodsOnlineInfo,
            'shops'=>$shops,
            'currency'=>$currency,
            'goods_attribute'=>$goods_attribute,
            'id'=>$id,
            'categoryInfo'=>$categoryInfo,
        ]);
        return view('Goods.AmazonOnline.edit')->with($result);
    }

    /**
     * @param Request $request
     * Note: 在线商品编辑保存
     * Data: 2019/6/19 16:44
     * Author: zt7785
     */
    public function editSave (Request $request) {
        if($request->isMethod('post')) {
            $this->valideAmazon($request) ;
            DB::beginTransaction();
            try{
                $param   = $request->get('param' ,[]) ;
                $amazonId = $param['online_amazon_goods_id'];
                $img_sup = $request->get('pics' ,[]) ;
                $label_arr = $request->get('label','');
                $label = $label_arr ? implode(',',$label_arr) : '';
                if (empty($amazonId)) {
                    return AjaxResponse::isFailure('在线商品信息异常');
                }
                $currentUser = CurrentUser::getCurrentUser();
                $keywords_arr = $request->get('keywords','');
                if(!empty($keywords_arr)) {
                    $keywords = implode(',',$keywords_arr);
                }
                if(empty($currentUser)) {
                    return AjaxResponse::isFailure('用户信息过期,请重新登录');
                }
                if($currentUser->userAccountType == AccountType::CHILDREN) {
                    $user_id = $currentUser->userParentId;
                } else {
                    $user_id = $currentUser->userId;
                }

                //获取商品信息
                $goodsOnlineInfo = GoodsOnlineAmazon::getGoodsInfoByIdUserId($amazonId,$user_id);
                if (empty($goodsOnlineInfo)) {
                    abort(404);
                }

                if ($param['promotion_price'] > 0 && empty($param['promotion_time'])) {
                    return AjaxResponse::isFailure('设置促销价格时请设置促销时间');
                }

                if ($param['promotion_time'] > 0 && empty($param['promotion_price'])) {
                    return AjaxResponse::isFailure('设置促销时间时请设置促销价格');
                }

                //亚马逊节点逻辑
                $nodeInfo = CategorysAmazon::checkNode($param ['category_id']);
                if ($nodeInfo ['exception_status']) {
                    return AjaxResponse::isFailure($nodeInfo ['exception_info'] );
                }

                $platformInformation['AmazonBrowseNodeID'] = $param ['category_id'];
                $platformInformation['amazon_category_id'] = $nodeInfo ['data'] ['parentBrowsePathByID'];
                $platformInformation['parentBrowseName'] = htmlspecialchars_decode($param ['category_info']);
                //先存取主表信息
                $platformInformation['sale_price']   =  $param['sale_price']??0.00 ;  //销售价格
                $platformInformation['platform_in_stock'] = $param['platform_in_stock']  ;  //平台库存
                $platformInformation['goods_name'] = $param['goods_name']??'' ; //商品标题
                $platformInformation['title'] = $param['title'] ;  //商品名称
//                $platformInformation['seller_sku'] = $param['seller_sku'] ;
//                $platformInformation['product_code_type'] = $param['product_code_type'] ;
                $platformInformation['goods_status'] = $param['goods_status'] ;
                $platformInformation['img_url'] = isset($param['img_url']) ? $param['img_url'] : '';            //商品主图
                $platformInformation['goods_description'] = $param['goods_description'] ;  //商品描述
                $platformInformation['goods_keywords'] = $keywords ;
                $platformInformation['goods_label'] = $label ;
                $platformInformation['brand'] = $param['brand'] ;
                $platformInformation['manufacturer'] = $param['manufacturer'] ;
                $platformInformation['color'] = $param['color'] ;
                $platformInformation['goods_size'] = $param['goods_size'] ;
                $platformInformation['goods_attribute_id'] = $param['goods_attribute_id'] ;
                $platformInformation['goods_weight'] = $param['goods_weight'] ;
                $platformInformation['goods_length'] = $param['goods_length'] ;
                $platformInformation['goods_width'] = $param['goods_width'] ;
                $platformInformation['goods_height'] = $param['goods_height'] ;
                $platformInformation['currency_code'] = $param['currency_code'] ;
                $platformInformation['platform_in_stock'] = $param['platform_in_stock'] ;
                $platformInformation['promotion_price'] = $param['promotion_price'] ;

                if($param['promotion_time']) {
                    $promotion = explode(' - ',$param['promotion_time']);
                    $platformInformation['promotion_start_time'] = $promotion[0];
                    $platformInformation['promotion_end_time'] = $promotion[1];
                }
                $updateRe = GoodsOnlineAmazon::postData($amazonId,$platformInformation);
                if(!$updateRe) {
                    DB::rollback() ;
                    return AjaxResponse::isFailure('编辑失败' );
                }

                //清除旧数据
                GoodsOnlineAmazonPics::delPicsById($amazonId,$user_id);
                if(!empty($img_sup)) {
                    foreach($img_sup as $k => $v) {
                        if(!$v) {
                            continue;
                        }
                        $pics['goods_id'] = $amazonId;
                        $pics['created_man'] = $currentUser->userId;
                        $pics['link'] = $v;
                        $pics['user_id'] = $user_id;
                        $pics['updated_at'] = date('Y-m-d H:i:s');
                        $pics['created_at'] = date('Y-m-d H:i:s');
                        GoodsOnlineAmazonPics::insert($pics);
                    }
                }

                //上架成功的商品编辑将触发接口
                $msg = '编辑成功';
                if ($goodsOnlineInfo ['put_on_status'] == GoodsOnlineAmazon::PUTON_SUCC) {
                    GoodsOnlineAmazon::updateOnlineProduct($amazonId);
                    $msg = '编辑成功等待亚马逊处理数据';
                }
                DB::commit();
                return AjaxResponse::isSuccess($msg);
            }catch (\Exception $exception){
                DB::rollback() ;
                return AjaxResponse::isFailure('编辑失败' );
            }
        }
    }


    public function valideAmazon($request)
    {
        $this->validate($request, [
            'param.online_amazon_goods_id' => 'required|integer',
//            'param.seller_sku' => 'required',
//            'param.product_code' => 'required',
            'param.goods_status' => 'required',
//            'param.title' => 'required',
            'param.brand' => 'required|between:0,100',
            'param.manufacturer' => 'required|between:0,100',
//            'param.color' => 'required',
//            'param.goods_size' => 'required',
            'param.title' => 'required|between:0,500',
            'param.goods_name' => 'required|between:0,500',
            'param.goods_weight' => 'required',
            'param.goods_length' => 'required',
            'param.goods_width' => 'required',
            'param.goods_height' => 'required',
            'param.goods_description' => 'required',
            'param.sale_price' => 'required|numeric|max:9999999999',
            'param.platform_in_stock' => 'required|numeric|min:1|max:999999999',
            'param.promotion_price' => 'nullable|numeric|max:999999999',
        ], [
            'required' => ':attribute 为必填项',
            'max' => ':attribute 超出最大值限制',
            'min' => ':attribute 超出最小值限制',
            'unique' => ':attribute 已经存在',
        ], [
            'param.firstCategory' => '一级分类',
            'param.secondCategory' => '二级分类',
            'param.thirdCategory' => '三级分类',
            'param.sku' => 'SellerSKU',
            'param.goods_name' => '商品名称',
            'param.goods_attribute_id' => '',
            'param.goods_weight' => '商品重量',
            'param.goods_height' => '商品尺寸',
            'param.goods_length' => '商品尺寸',
            'param.goods_width' => '商品尺寸',
            'param.description' => '商品描述',
            'param.sale_price' => '销售价格',
            'param.promotion_price' => '促销价格',
            'param.title' => '商品标题',
        ]);
    }

    /**
     * @desc 上架
     * @author zt6650
     * CreateTime: 2019-04-15 10:48
     * @param Request $request
     * @return array
     */
    public function PutOnSaleById(Request $request)
    {
        $ids = $request->get('id' ,0) ;
        $ids = str_replace('，',',',$ids) ;
        $id = explode(',' ,$ids) ;
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $re = GoodsOnlineAmazon::putOnSaleById($id,$user_id) ;
        if ($re ['exception_status']){
            return [
                'status'=>false ,
                'msg'=>$re ['exception_status']
            ] ;
        }

        return [
            'status'=>true ,
            'msg'=>'上架请求提交成功'
        ] ;
    }

    /**
     * 下架商品
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function PutOffSaleById(Request $request)
    {
        $ids = $request->get('id' ,0) ;
        if (empty($ids)) {
            return [
                'status'=>false ,
                'msg'=>'参数异常'
            ] ;
        }
        $ids = str_replace('，',',',$ids) ;
        $id = explode(',' ,$ids) ;
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        //todo: 对接亚马逊下架接口
        $re = GoodsOnlineAmazon::updateOnlineProductPutOffById($id,$user_id) ;
        if ($re ['exception_status']){
            return [
                'status'=>false ,
                'msg'=>$re ['exception_status']
            ] ;
        }
        return [
            'status'=>true ,
            'msg'=>'下架请求提交成功'
        ] ;
    }
}
