<?php

namespace App\Http\Controllers\Goods;


use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Auth\Models\RolesShops;
use App\Http\Services\Goods\AmazonSaleFeed;
use App\Models\Category;
use App\Models\CategorysAmazon;
use App\Models\Goods;
use App\Models\GoodsDraftAmazon;
use App\Models\GoodsDraftAmazonPics;
use App\Models\GoodsDraftRakuten;
use App\Models\GoodsDraftRakutenPics;
use App\Models\GoodsOnlineAmazon;
use App\Models\GoodsOnlineAmazonPics;
use App\Models\Orders;
use App\Models\Platforms;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingCurrencyExchangeMaintain;
use App\Models\Upc;
use App\Models\Amazon;
use App\Models\AmazonPic;
use App\Models\GoodsAttribute;
use App\Models\PlatformsInformation;
use App\Models\SettingShops;
use App\Models\WarehouseTypeGoods;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Mockery\Exception;

class AmazonController extends Controller
{
    /**
     * @var Upc
     */
    protected $Upc;

    /**
     * @author zt6650
     * @var Amazon
     */
    protected $Amazon ;


    /**
     * @author zt6650
     * @var SettingShops
     */
    protected $shop ;


    /**
     * @author zt6650
     * @var PlatformsInformation ;
     */
    protected $platformInformation ;

    /**
     * @author zt6650
     * @var GoodsAttribute
     */
    protected $goodsAttribute ;

    /**
     * @author zt6650
     * @var AmazonPic
     */
    protected $AmazonPic ;


    /**
     * @author zt12779
     * @var 商品目录ID
     */
    const ORDER_MENUS_ID = 2;

    public function __construct()
    {
        $this->Amazon = new Amazon ;
        $this->goodsAttribute = new GoodsAttribute ;
        $this->shop  = new SettingShops ;
        $this->AmazonPic = new GoodsDraftAmazonPics() ;
        $this->platformInformation = new PlatformsInformation ;
        $this->Upc = new Upc();
        $this->GoodsDraftAmazon = new GoodsDraftAmazon;
        $this->Goods = new Goods();
    }

    /**
     * @desc 亚马逊草稿箱首页
     * @author zt6650
     * CreateTime: 2019-04-11 16:53
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $menusModel = new Menus();
        $responseData ['shortcutMenus'] = $menusModel->getShortcutMenu(self::ORDER_MENUS_ID);
//        $responseData ['shortcutMenus'] = Orders::getOrderShortcutMenu();
        return view('Goods.Amazon.index')->with($responseData) ;
    }

    /**
     * @desc 亚马逊草稿箱商品查询
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
        $data = GoodsDraftAmazon::getList($params, $user_id);
        $res = array(
            'code' => '0',
            'msg' =>'',
            'count' => $data['total'],
            'data'  => $data['data']
        );
        return $res ;
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

        $re = GoodsDraftAmazon::putOnSaleById($id,$user_id) ;
        if ($re ['exception_status']){
            return [
                'status'=>false ,
                'msg'=>$re ['exception_info']
            ] ;
        }
        return [
            'status'=>true ,
            'msg'=>'上架请求提交成功'
        ] ;
    }

    /**
     * @desc 亚马逊草稿箱商品的编辑
     * @author zt6650
     * CreateTime: 2019-04-16 17:04
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $id = $request->get('id','' ) ;
        $sku = $request->get('sku');
        $currentUser = CurrentUser::getCurrentUser();
        if($currentUser->userAccountType == AccountType::CHILDREN) {
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
            $shops = $this->shop->getShopByPlatId(Platforms::AMAZON,$user_id) ;
        }

        $goods_attribute = $this->goodsAttribute::getAllAttrs() ;

//        $currency = SettingCurrencyExchangeMaintain::where('user_id', $user_id)
//            ->get();
        $currency = SettingCurrencyExchange::get();
        if(!$currency->isEmpty()) {
            $currency = $currency->toArray();
        }

        //新增时需sku是否为本地审核通过
        if(!$id) {
            $goods = $this->Goods->getGoodsIdBySku($sku,$user_id);
            if(!$goods) {
                return abort(404);
            }
            $goods_id = $goods->id;
            unset($goods->id);
            $goods = $goods->toArray();
        }
        //编辑页 amazon分类 关键词
        if($id) {
            $draft = GoodsDraftAmazon::getOne($id,$user_id);
            if(!$draft) {
                abort(404);
            }
            $goods = $draft->toArray();
            $goods_id = $goods ['goods_id'];
            $goods = $this->GoodsDraftAmazon->getAmazomCat($goods,$user_id);
        }

        $categorys['first'] = CategorysAmazon::getCategoryByOpt(['categories_lv'=>1]);
        if (isset($goods['amazon_category_id']) && !empty($goods['amazon_category_id']) ) {
            $categoryInArray = explode(',', $goods['amazon_category_id']);
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

        $goods_quantity = WarehouseTypeGoods::getAllocationQuantity($goods_id,Platforms::AMAZON,$user_id);
        return view('Goods.Amazon.edit')->with([
            'goods'=>isset($goods) ? $goods : ''
            ,'shops'=>$shops
            ,'currency'=>$currency
            ,'goods_attribute'=>$goods_attribute
            ,'sku'=>$sku
            ,'goods_id'=>isset($goods_id) ? $goods_id : ''
            ,'id'=>$id
            ,'categoryInfo'=>$categoryInfo
            ,'goods_quantity'=>$goods_quantity
        ]) ;

    }

    public function valideAmazon($request)
    {
        $this->validate($request, [
            'param.firstCategory' => 'required|max:10',
            'param.secondCategory' => ['required'],
            'param.thirdCategory' => 'required',
            'param.seller_sku' => 'required',
            'param.product_code' => 'required',
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
            'param.platform_in_stock' => 'required|min:1|max:999999999',
//            'param.promotion_price' => 'numeric|max:999999999',
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
            'param.platform_in_stock' => '平台库存',
            'param.product_code' => '商品编码',
            'param.brand' => '商品品牌',
            'param.manufacturer' => '制造商',
        ]);
    }

    /**
     * @desc 亚马逊草稿箱编辑
     * @author zt6650
     * CreateTime: 2019-04-17 11:23
     * @param Request $request
     * @return array
     */
    public function editSave(Request $request)
    {
        $this->valideAmazon($request) ;
        $param   = $request->get('param' ,[]) ;
        $amazonId = $param['draft_amazon_goods_id'] ?? 0 ;
        $img_sup = $request->get('pics' ,[]) ;
        $label_arr = $request->get('label','');
        $label = $label_arr ? implode(',',$label_arr) : '';
        $currentUser = CurrentUser::getCurrentUser();
        $keywords_arr = $request->get('keywords','');
        $type = $request->input('type','');
        if(!empty($keywords_arr)) {
            $keywords = implode(',',$keywords_arr);
        }
        if(empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        if($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'],true);
            } else {
                $shopsId = [];
            }
        } else {
            $user_id = $currentUser->userId;
        }
        if($amazonId && $param['id']) {
            $amazon = GoodsDraftAmazon::getOne($amazonId,$user_id);
            if(!$amazon) {
                abort(404);
            }
        }

        if ($param['promotion_price'] > 0 && empty($param['promotion_time'])) {
            return [
                'status'=>false ,
                'msg'=>'设置促销价格时请设置促销时间',
            ] ;
        }

        if ($param['promotion_time'] > 0 && empty($param['promotion_price'])) {
            return [
                'status'=>false ,
                'msg'=>'设置促销时间时请设置促销价格',
            ] ;
        }

        //亚马逊节点逻辑
        $nodeInfo = CategorysAmazon::checkNode($param ['category_id']);
        if ($nodeInfo ['exception_status']) {
            return [
                'status'=>false ,
                'msg'=>$nodeInfo ['exception_info'] ,
            ] ;
        }
        $platformInformation['product_code_type'] = $param['product_code_type'] ;
        if ($platformInformation['product_code_type'] == 1) {
            $platformInformation['upc'] = $param['product_code'] ;
            $checkUpc = Upc::where(
                [
                    'user_id'=>$user_id,
                    'upc'=>$platformInformation['upc'],
                ]
            )->first(['id','status']);
            if (empty($checkUpc)) {
                return [
                    'status'=>false ,
                    'msg'=>'UPC信息异常' ,
                ] ;
            }
            if ($checkUpc->status == Upc::HAVE_BEEN_USED) {
                return [
                    'status'=>false ,
                    'msg'=>'UPC已使用' ,
                ] ;
            }
        }

        if (isset($shopsId) && !in_array($param['store_id'],$shopsId)) {
            return [
                'status'=>false ,
                'msg'=>'店铺权限异常' ,
            ] ;
        }
        DB::beginTransaction();
        try{
            $platformInformation['AmazonBrowseNodeID'] = $param ['category_id'];
            $platformInformation['amazon_category_id'] = $nodeInfo ['data'] ['parentBrowsePathByID'];
            $platformInformation['parentBrowseName'] = htmlspecialchars_decode($param ['category_info']);
            //先存取主表信息
            $platformInformation['belongs_shop'] = $param['store_id'] ; //店铺
            $platformInformation['category_id_1']= $param['firstCategory'] ; //一级分类的id
            $platformInformation['category_id_2']= $param['secondCategory'] ; //二级分类的id
            $platformInformation['category_id_3']= $param['thirdCategory'] ; //三级分类的id
            $platformInformation['sale_price']   =  $param['sale_price']??0.00 ;  //销售价格
            $platformInformation['platform_in_stock'] = $param['platform_in_stock']  ;  //平台库存
            $platformInformation['goods_name'] = $param['goods_name']??'' ; //商品标题
            $platformInformation['title'] = $param['title'] ;  //商品名称
            $platformInformation['seller_sku'] = $param['seller_sku'] ;
            $platformInformation['product_code'] = $param['product_code'] ;
            $platformInformation['goods_status'] = $param['goods_status'] ;
            $platformInformation['img_url'] = isset($param['img_url'])?$param['img_url']:'';//商品主图
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
            $platformInformation['synchronize_status'] = GoodsDraftAmazon::STATUS_DRAFT ;
            if(!$amazonId) {
                $platformInformation['local_sku'] = $param['local_sku'];
                $platformInformation['goods_id'] = $param['goods_id'] ;
            }
            if($param['promotion_time']) {
                $promotion = explode(' - ',$param['promotion_time']);
                $platformInformation['promotion_start_time'] = $promotion[0];
                $platformInformation['promotion_end_time'] = $promotion[1];
            }
            $insert_id = $this->GoodsDraftAmazon->updateById($amazonId,$platformInformation,$user_id) ;
            if(!$insert_id) {
                DB::rollback() ;
                return [
                    'status'=>false ,
                    'msg'=>'保存失败' ,
                ] ;
            }
            //UPC绑定
            if (isset($checkUpc)) {
                UPC::where('id',$checkUpc['id'])->update(['seller_sku'=>$platformInformation ['seller_sku'] ,'status'=>UPC::HAVE_BEEN_USED ,'updated_at'=>date('Y-m-d H:i:s')]);
            }
            //清除旧数据
            $this->AmazonPic->delPicsById($amazonId,$user_id);
            if(!empty($img_sup)) {
                foreach($img_sup as $k => $v) {
                    if(!$v) {
                        continue;
                    }
                    $pics['goods_id'] = $amazonId ? $amazonId  : $insert_id;
                    $pics['created_man'] = $user_id??0 ;
                    $pics['link'] = $v;
                    $pics['user_id'] = $user_id??0;
                    $pics['updated_at'] = date('Y-m-d H:i:s');
                    $pics['created_at'] = date('Y-m-d H:i:s');
                    $this->AmazonPic->updateById($pics) ;
                }
            }

            if($type === 'putOnBatch'){
                $putOnBatchRe = GoodsDraftAmazon::putOnSaleById([$amazonId],$user_id,true) ;
                if ($putOnBatchRe ['exception_status']){
                    DB::rollback() ;
                    return [
                        'status'=>false ,
                        'msg'=>'上架失败','失败原因:'.$putOnBatchRe ['exception_info']
                    ] ;
                }
                DB::commit();
                return [
                    'status'=>true ,
                    'msg'=>'保存成功,上架请求提交成功'
                ] ;
            } else {
                DB::commit();
                return [
                    'status'=>true ,
                    'msg'=>'保存成功',
                ] ;
            }
        }catch (Exception $exception){
            DB::rollback() ;
            return [
                'status'=>false ,
                'msg'=>'保存失败' ,
            ] ;
        }
    }


    /**
     * @desc 草稿箱的商品删除
     * @author zt6650
     * CreateTime: 2019-04-18 09:48
     * @param Request $request
     * @return array
     */
    public function deleteAmazonDraft(Request $request)
    {
        $id = $request->get('id' ,0) ;
        $currentUser = CurrentUser::getCurrentUser();
        if($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if(empty($id) || (floor($id - $id) != 0)) {
            abort(404);
        }
        $re = $this->GoodsDraftAmazon->deleteById($id,$user_id,$this->AmazonPic) ;
        if(!$re) {
            return parent::layResponseData(['code'=>0, 'msg'=>'删除失败']);

        }

       return parent::layResponseData(['code'=>200, 'msg'=>'删除成功']);
    }

    public function add(Request $request)
    {
        return view('Goods.Amazon.add');
    }

    /**
     * @note
     * 检查亚马逊商品是否存在
     * @since: 2019/6/29
     * @author: zt7837
     * @return: array
     */
    public function checkAmazonGoods(Request $request) {

        $sku = $request->input('sku');
        $response_data['code'] = -1;
        $response_data['msg'] = '';
        $currentUser = CurrentUser::getCurrentUser();

        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $goods = Goods::where('sku', $sku)->where('user_id',$user_id)->first();
        if(!$goods) {
            $response_data['msg'] = '商品不存在!';
            return $response_data;
        }
        $goods_check = $this->Goods->getGoodsIdBySku($sku,$user_id);
        if(!$goods_check) {
            $response_data['msg'] = '商品未审核!';
            return parent::layResponseData($response_data);
        }
        $response_data['code'] = 0;
        $response_data[''] = '';
        return  parent::layResponseData($response_data);
    }

    /**
     * @description UPC码首页
     * @author zt7927
     * @date 2019/4/18 9:58
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function upcIndex()
    {
        //侧边栏
        $responseData['shortcutMenus'] = Upc::getGoodsShortcutMenu();

        $currentUser = CurrentUser::getCurrentUser();

        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        //获取所有UPC码
        $upc = $this->Upc->getAllUpc($user_id);

        return view('Goods.Amazon.upcIndex', compact('upc'))->with($responseData);
    }

    /**
     * @description UPC码-搜索
     * @author zt7927
     * @date 2019/4/18 10:05
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upcSearch(Request $request)
    {
        $pageIndex = $request->get('page', 1);
        $pageSize  = $request->get('limit', 20);
        $params    = $request->get('data', []);
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $collection  = $this->Upc->search($params,$user_id);   //查询
        $re['count'] = $collection->count();
        $re['data']  = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->get()->toArray();
        return parent::layResponseData($re);
    }

    /**
     * @description upc使用页面
     * @author zt7927
     * @date 2019/4/18 11:21
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function useUpcIndex($id)
    {
        if (empty($id)) {
            abort(404);
        }

        return view('Goods.Amazon.useUpcIndex', compact('id'));
    }

    /**
     * @description 使用UPC码
     * @author zt7927
     * @date 2019/4/18 11:41
     * @param Request $request
     * @return array
     */
    public function useUpc(Request $request)
    {
        $params = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        if ($params['data']['upc_id'] && ($params['data']['upc_id'] > 0) && $params['data']['seller_sku']){
            $params['data']['user_id'] = $user_id;
            $re = $this->Upc->updateArr($params['data']);
            if ($re){
                return [
                    'status' => 1,
                    'msg'    => '保存成功'
                ];
            }
            return [
                'status' => 0,
                'msg'    => '保存失败'
            ];
        }
        return [
            'status' => 0,
            'msg'    => '保存失败'
        ];
    }

    /**
     * @description 导入UPC-页面
     * @author zt7927
     * @date 2019/4/18 10:25
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function upcImportIndex()
    {
        return view('Goods.Amazon.upcImportIndex');
    }

    /**
     * @description 导入UPC码
     * @author zt7927
     * @date 2019/4/18 10:45
     * @param Request $request
     * @param Excel $excel
     * @return array
     */
    public function upcImport(Request $request, Excel $excel)
    {
        $dataArr = [];   //存储需要批量保存的数据
        $checkUniqueBill_arr = array();
        $filePath = $this->postFileupload($request, 'inventory_allocation', ['xlsx', 'xls']);
        if ($filePath['status'] !== 1) {
            return [
                'status' => 0,
                'msg'    => $filePath['message']
            ];
        }

        $CurrentUser = CurrentUser::getCurrentUser();
        if (empty($CurrentUser)) {
            return [
                'status' => 0,
                'msg'    => '用户信息过期,请重新登录'
            ];
        }
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        $filePath = iconv('utf-8', 'gbk', $filePath['path']);
        $this->import($excel, $filePath);
        $upcModel = new Upc();
        $i = 2;
        foreach (self::$data as $k => $item) {
            if ($k == 0) {
                if ($item[0] == null) {
                    return [
                        'status' => 0,
                        'msg'    => '请使用官方提供模板'
                    ];
                }
                if ($item[0] && $item[0] != 'UPC码') {
                    return [
                        'status' => 0,
                        'msg'    => '请使用官方提供模板'
                    ];
                }
                continue;
            } else {
                //验证个字段长度
                if (
                    strlen($item[0]) > 50
                ) {
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行有过长数据，核对后上传'
                    ];
                }

                if (empty($item[0])){
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行有空数据，核对后上传'
                    ];
                }
                $upc = trim($item[0]);
                $upcStatus = $upcModel->where('upc',$upc)->where('user_id',$user_id)->first(['id']);
                if ($upcStatus) {
                    return [
                        'status' => 0,
                        'msg'    => '第' . $i . '行UPC数据已存在，核对后上传'
                    ];
                }
                $dataArr[$i]['user_id'] = $user_id;
                $dataArr[$i]['created_man'] = $CurrentUser->userId;
                $dataArr[$i]['upc'] = $upc;
                $dataArr[$i]['status'] = $upcModel::UNUSED;
                $dataArr[$i]['created_at'] = date('Y-m-d H:i:s');
                $dataArr[$i]['updated_at'] = date('Y-m-d H:i:s');
                $checkUniqueBill_arr[] = $item[0];
                $i++;

            }
        }
        // 获取去掉重复数据后的数组
        $unique_arr = array_unique($checkUniqueBill_arr);
        // 获取重复数据的数组
        $repeat_arr = array_diff_assoc($checkUniqueBill_arr, $unique_arr);
        if ($repeat_arr) {
            return [
                'status' => 0,
                'msg'    => '上传文件有重复UPC码'
            ];
        }
        if (empty($dataArr)) {
            return [
                'status' => 0,
                'msg'    => '导入失败!上传文件为空'
            ];
        }
        if (!DB::table('upc')->insert($dataArr)) {
            return [
                'status' => 0,
                'msg'    => '导入失败!'
            ];
        } else {
            return [
                'status' => 1,
                'msg'    => '导入成功!'
            ];
        }
    }



    /**
     * @param Request $request
     * @return array
     * Note: 亚马逊上架
     * Data: 2019/6/17 20:02
     * Author: zt7785
     */
    public function amazonGoodsPutOn(Request $request)
    {
        //V1 数据校验 数据处理
        $this->valideAmazon($request) ;
        $param   = $request->get('param' ,[]) ;
        //亚马逊草稿箱id
        $amazonId = $param['draft_amazon_goods_id'] ?? 0 ;
        $img_sup = $request->get('pics' ,[]) ;
        $label_arr = $request->get('label','');
        $label = $label_arr ? implode(',',$label_arr) : '';
        $currentUser = CurrentUser::getCurrentUser();
        $keywords_arr = $request->get('keywords','');
        $type = $request->input('type','');
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
        if($amazonId && $param['id']) {
            $amazon = GoodsDraftAmazon::getOne($amazonId,$user_id);
            if(!$amazon) {
                abort(404);
            }
        }
        if ($param['promotion_price'] > 0 && empty($param['promotion_time'])) {
            return [
                'status'=>false ,
                'msg'=>'设置促销价格时请设置促销时间',
            ] ;
        }

        if ($param['promotion_time'] > 0 && empty($param['promotion_price'])) {
            return [
                'status'=>false ,
                'msg'=>'设置促销时间时请设置促销价格',
            ] ;
        }

        //亚马逊节点逻辑
        $nodeInfo = CategorysAmazon::checkNode($param ['category_id']);
        if ($nodeInfo ['exception_status']) {
            return [
                'status'=>false ,
                'msg'=>$nodeInfo ['exception_info'] ,
            ] ;
        }
        $param ['sale_price'] = number_format($param ['sale_price'], 2, '.', '');

        //草稿箱 在线商品公共字段
        //基础信息
        $current_time = date('Y-m-d H:i:s');
        $draftData ['created_man']      = $platformInformation['created_man']   = $currentUser->userId ;
        $draftData ['user_id']          = $platformInformation['user_id']       = $user_id ;
        $draftData ['created_at']       = $platformInformation['created_at']    = $current_time ;
        $draftData ['updated_at']       = $platformInformation['updated_at']    = $current_time ;

        $draftData ['category_id_1']        = $platformInformation['category_id_1']         = $param['firstCategory'] ; //一级分类的id
        $draftData ['category_id_2']        = $platformInformation['category_id_2']         = $param['secondCategory'] ; //二级分类的id
        $draftData ['category_id_3']        = $platformInformation['category_id_3']         = $param['thirdCategory'] ; //三级分类的id
        $draftData ['belongs_shop']         = $platformInformation['belongs_shop']          = $param['store_id'] ; //店铺
        $draftData ['amazon_category_id']   = $platformInformation['amazon_category_id']    = $nodeInfo ['data'] ['parentBrowsePathByID'];
        $draftData ['local_sku']    = $platformInformation['local_sku']     = $param['local_sku'];
        $draftData ['goods_id']     = $platformInformation['goods_id']      = $param['goods_id'] ;
        $draftData ['parentBrowseName']     = $platformInformation['parentBrowseName']        = htmlspecialchars_decode($param ['category_info']);
        $draftData ['AmazonBrowseNodeID']   = $platformInformation['AmazonBrowseNodeID']      = $param ['category_id'];
        //上架信息
        $draftData ['title']        = $platformInformation['title']         = $param['title']??'' ;  //商品标题：
        $draftData ['seller_sku']   = $platformInformation['seller_sku']    = $param['seller_sku'] ;//销售sku

        $draftData ['product_code_type']    = $param['product_code_type'];
        $draftData ['product_code']         = $param['product_code'];
        if ($param['product_code_type']  == 2) {
            $platformInformation['ASIN'] = $param['product_code'] ;
        } else {
            $draftData ['upc']  = $platformInformation['upc'] = $param['product_code'] ;
        }

        $draftData ['goods_keywords']   = $platformInformation['goods_keywords']    = $keywords ;//关键词
        $draftData ['goods_label']      = $platformInformation['goods_label']       = $label ;//标签
        $draftData ['brand']            = $platformInformation['brand']             = $param['brand'] ;//品牌信息
        $draftData ['manufacturer']     = $platformInformation['manufacturer']      = $param['manufacturer'] ;//制造商
        $draftData ['color']            = $platformInformation['color']             = $param['color'] ;//颜色
        $draftData ['goods_size']       = $platformInformation['goods_size']        = $param['goods_size'] ;//型号尺寸
        $draftData ['goods_status']     = $platformInformation['goods_status']      = $param['goods_status'] ;//condition_type
        //商品信息
        $draftData ['goods_attribute_id']   = $platformInformation['goods_attribute_id']    = $param['goods_attribute_id'] ;//属性id 带电不带电
        $draftData ['goods_weight']         = $platformInformation['goods_weight']          = $param['goods_weight'] ;
        $draftData ['goods_length']         = $platformInformation['goods_length']          = $param['goods_length'] ;
        $draftData ['goods_width']          = $platformInformation['goods_width']           = $param['goods_width'] ;
        $draftData ['goods_height']         = $platformInformation['goods_height']          = $param['goods_height'] ;
        $draftData ['goods_name']           = $platformInformation['goods_name']            = $param['goods_name']??'' ; //商品名称：
        $draftData ['goods_description']    = $platformInformation['goods_description']     = $param['goods_description'] ;  //商品描述
        //价格信息
        $draftData ['currency_code']        = $platformInformation['currency_code']         = $param['currency_code'] ;//币种
        $draftData ['platform_in_stock']    = $platformInformation['platform_in_stock']     = $param['platform_in_stock'] ;//平台库存
        $draftData ['sale_price']           = $platformInformation['sale_price']            =  $param['sale_price']??0.00 ;  //销售价格
        $draftData ['promotion_price']      = $platformInformation['promotion_price']       = $param['promotion_price'] ??0.00 ;//促销价格
        if($param['promotion_time']) {
            $promotion = explode(' - ',$param['promotion_time']);
            $draftData ['promotion_start_time']     =    $platformInformation['promotion_start_time']  = $promotion[0];
            $draftData ['promotion_end_time']       = $platformInformation['promotion_end_time']       = $promotion[1];
        }
        //图片信息
        $draftData ['img_url']  = $platformInformation['img_url'] = $param['img_url']; //商品主图
        //状态信息
        $platformInformation['put_on_status']           = GoodsOnlineAmazon::PUTON_INIT ;
        $platformInformation['put_off_status']          = GoodsOnlineAmazon::PUTOFF_INIT ;
        $draftData['synchronize_status']                = GoodsDraftAmazon::STATUS_DRAFT ;
        $draftData['synchronize_info']                  = '' ;

        //先存取主表信息

        DB::beginTransaction();
        try{
            //V2 写草稿箱数据
            $draft_insert_id = $this->GoodsDraftAmazon->updateById($amazonId,$draftData,$user_id) ;
            if(!$draft_insert_id) {
                DB::rollback() ;
                return [
                    'status'=>false ,
                    'msg'=>'保存失败' ,
                ] ;
            }
            //清除旧数据
            if (!empty($amazonId)){
                $this->AmazonPic->delPicsById($amazonId,$user_id);
            }
            if(!empty($img_sup)) {
                foreach($img_sup as $k => $v) {
                    if(!$v) {
                        continue;
                    }
                    $pics['goods_id'] = $amazonId ? $amazonId  : $draft_insert_id;
                    $pics['created_man'] = $user_id??0 ;
                    $pics['link'] = $v;
                    $pics['user_id'] = $user_id??0;
                    $pics['updated_at'] = date('Y-m-d H:i:s');
                    $pics['created_at'] = date('Y-m-d H:i:s');
                    $this->AmazonPic->updateById($pics) ;
                }
            }
            if($type === 'putOnBatch'){
                //V3 预处理上架商品表数据
                $platformInformation ['goods_draft_amazon_id'] =  $draft_insert_id;
                $online_insert_id = GoodsOnlineAmazon::updateById($amazonId,$platformInformation,$user_id) ;

                if(!$online_insert_id) {
                    DB::rollback() ;
                    return [
                        'status'=>false ,
                        'msg'=>'保存失败' ,
                    ] ;
                }
                //清除旧数据
//            if (!empty($amazonId)){
//                GoodsOnlineAmazonPics::delPicsById($amazonId,$user_id);
//            }
                $feedImageParam = [];
                $amazonProductI = 1;
                if(!empty($img_sup)) {
                    foreach($img_sup as $k => $v) {
                        if(!$v) {
                            continue;
                        }
                        $pics['goods_id'] = $amazonId ? $amazonId  : $online_insert_id;
                        $pics['created_man'] = $user_id??0 ;
                        $pics['link'] = $v;
                        $pics['user_id'] = $user_id??0;
                        $pics['updated_at'] = date('Y-m-d H:i:s');
                        $pics['created_at'] = date('Y-m-d H:i:s');
                        GoodsOnlineAmazonPics::insert($pics);
                        if ($amazonProductI > 5 ) {
                            continue ;
                        }
                        $feedImageParam [$platformInformation ['seller_sku'].'_'.$amazonProductI] = [
                            'type'=>'Alternate',
                            'url'=>url('showImage').'?path='.$pics['link']
                        ];
                        $amazonProductI ++ ;
                    }
                }
                //V4 上架接口逻辑

                //商品上架队列写数据
                $feedParam ['sku'] = $platformInformation ['seller_sku'];
                //不涉及币种 币种根据站点信息
                $feedParam ['price'] = $platformInformation ['sale_price'];
                $feedParam ['product_id'] = $draftData ['product_code'];
                $feedParam ['product_id_type'] = $draftData ['product_code_type'] == 1 ? 'UPC' : 'ASIN';
                $feedParam ['condition_type'] = 'New';
                $feedParam ['quantity'] = $platformInformation ['platform_in_stock'];
                if (empty($feedParam ['quantity'])) {
                    DB::rollback() ;
                    return [
                        'status'=>false ,
                        'msg'=>'保存失败,亚马逊平台库存不能为0',
                    ] ;
                }
                $feedParam ['title'] = $platformInformation ['title'];
                $feedParam ['brand'] = $platformInformation ['brand'];
                $feedParam ['recommended_browse_nodes'] = $platformInformation ['AmazonBrowseNodeID'];
                //草稿id 用于处理上架失败
                $feedParam ['goods_draft_id'] = $draft_insert_id;
                //图片
//            if ($platformInformation ['img_url']) {
//                $feedParam ['image'] = [$platformInformation['img_url']];
//            }
                //重量
                if ($platformInformation ['goods_weight'] > 0 ) {
                    $feedParam ['weight'] = $platformInformation ['goods_weight'];
                }
                $listData['method'] = 'putOn';//上架
                $listData['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                $listData['request_pk'] = $online_insert_id;//亚马逊商品模型主键
                $listData['request_user_id'] = $user_id;
                $listData['request_shop_id'] = $platformInformation ['belongs_shop'];//亚马逊店铺
                $listData ['params'] = $feedParam;
                //上架请求队列
                $AmazonSaleFeed = new AmazonSaleFeed ();
                //写上架基础数据
                $AmazonSaleFeed-> putOnListPush($listData);

                //促销价格
                if ($platformInformation ['promotion_price'] > 0 ) {
                    //促销时间
                    $standardPrice [$platformInformation ['seller_sku']] = $platformInformation['sale_price'];
                    $feedSalePrice [$platformInformation ['seller_sku']] ['StartDate']= $platformInformation['promotion_start_time'];
                    $feedSalePrice [$platformInformation ['seller_sku']] ['EndDate']= $platformInformation['promotion_end_time'];
                    $feedSalePrice [$platformInformation ['seller_sku']] ['SalePrice']=
                        number_format($platformInformation['promotion_price'], 2, '.', '');
                    $editPrice ['method'] = 'editPrice';
                    $editPrice['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                    $editPrice['request_pk'] = $online_insert_id;//亚马逊商品模型主键
                    $editPrice['request_user_id'] = $user_id;
                    $editPrice['request_shop_id'] = $platformInformation ['belongs_shop'];//亚马逊店铺
                    $editPrice ['params'] = ['standardprice'=>$standardPrice,'saleprice'=>$feedSalePrice];
                    //写上架商品价格数据
                    $AmazonSaleFeed-> putOnListPush($editPrice);
                }

                //图片信息
                if ($platformInformation ['img_url']) {
                    $feedImageParam [$platformInformation ['seller_sku']] = [
                        'type'=>'Main',
                        'url'=>url('showImage').'?path='.$platformInformation['img_url']
                    ];
                    $editImage ['method'] = 'editGoodsImage';
                    $editImage['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                    $editImage['request_pk'] = $online_insert_id;//亚马逊商品模型主键
                    $editImage['request_user_id'] = $user_id;
                    $editImage['request_shop_id'] = $platformInformation ['belongs_shop'];//亚马逊店铺
                    $editImage ['params'] = ['standardImage'=>$feedImageParam];
                    //写上架商品价格数据
                    $AmazonSaleFeed-> putOnListPush($editImage);
                }
                DB::commit();
                return [
                    'status'=>true ,
                    'msg'=>'保存成功,上架请求提交成功'
                ] ;
            } else {
                DB::commit();
                return [
                    'status'=>true ,
                    'msg'=>'保存成功',
                ] ;
            }

        }catch (Exception $exception){
            DB::rollback() ;
            return [
                'status'=>false ,
                'msg'=>'保存失败',
            ] ;
        }


    }

    public function amazonGoodsPutOns(Request $request)
    {
        //V1 数据校验 数据处理
        $param   = $request->get('param' ,[]) ;
        $amazonId = $param['amazon_id'] ?? 0 ;
        $img_sup = $request->get('pics' ,[]) ;
        $label_arr = $request->get('label','');
        $label = $label_arr ? implode(',',$label_arr) : '';
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
        if($amazonId && $param['id']) {
            $amazon = GoodsDraftAmazon::getOne($amazonId,$user_id);
            if(!$amazon) {
                abort(404);
            }
        }
        if ($param['promotion_price'] > 0 && empty($param['promotion_time'])) {
            return [
                'status'=>false ,
                'msg'=>'设置促销价格时请设置促销时间',
            ] ;
        }

        if ($param['promotion_time'] > 0 && empty($param['promotion_price'])) {
            return [
                'status'=>false ,
                'msg'=>'设置促销时间时请设置促销价格',
            ] ;
        }

        //亚马逊节点逻辑
        $nodeInfo = CategorysAmazon::checkNode($param ['category_id']);
        if ($nodeInfo ['exception_status']) {
            return [
                'status'=>false ,
                'msg'=>$nodeInfo ['exception_info'] ,
            ] ;
        }
        //草稿箱 在线商品公共字段
        //V2 写草稿箱数据
        $draftData ['category_id_1']  = $platformInformation['category_id_1'] = $param['firstCategory'] ; //一级分类的id
        $draftData ['category_id_2']  = $platformInformation['category_id_2'] = $param['secondCategory'] ; //二级分类的id
        $draftData ['category_id_3']  = $platformInformation['category_id_3'] = $param['thirdCategory'] ; //三级分类的id
        $draftData ['goods_attribute_id']  = $platformInformation['goods_attribute_id'] = $param['thirdCategory'] ; //三级分类的id


        //V3 预处理上架商品表数据

        //V4 上架接口逻辑

        $platformInformation['AmazonBrowseNodeID'] = $param ['category_id'];
        $platformInformation['amazon_category_id'] = $nodeInfo ['data'] ['parentBrowsePathByID'];
        $platformInformation['parentBrowseName'] = htmlspecialchars_decode($param ['category_info']);

        //先存取主表信息
        $platformInformation['created_man'] = $currentUser->userId ;
        $platformInformation['belongs_shop'] = $param['store_id'] ; //店铺
        $platformInformation['category_id_1']= $param['firstCategory'] ; //一级分类的id
        $platformInformation['category_id_2']= $param['secondCategory'] ; //二级分类的id
        $platformInformation['category_id_3']= $param['thirdCategory'] ; //三级分类的id
        $platformInformation['sale_price']   =  $param['sale_price']??0.00 ;  //销售价格
        $platformInformation['platform_in_stock'] = $param['platform_in_stock']  ;  //平台库存
        $platformInformation['goods_name'] = $param['goods_name']??'' ; //商品名称：
        $platformInformation['title'] = $param['title'] ;  //商品标题：
        $platformInformation['seller_sku'] = $param['seller_sku'] ;//销售sku
        if ($param['product_code_type']  == 2) {
            $platformInformation['ASIN'] = $param['product_code'] ;
        } else {
            $platformInformation['upc'] = $param['product_code'] ;
        }
        $platformInformation['goods_status'] = $param['goods_status'] ;//condition_type
        $platformInformation['img_url'] = $param['img_url'];            //商品主图
        $platformInformation['goods_description'] = $param['goods_description'] ;  //商品描述
        $platformInformation['goods_keywords'] = $keywords ;//关键词
        $platformInformation['goods_label'] = $label ;//标签
        $platformInformation['brand'] = $param['brand'] ;//品牌信息
        $platformInformation['manufacturer'] = $param['manufacturer'] ;//制造商
        $platformInformation['color'] = $param['color'] ;//颜色
        $platformInformation['goods_size'] = $param['goods_size'] ;//型号尺寸
        $platformInformation['goods_attribute_id'] = $param['goods_attribute_id'] ;//属性id 带电不带电
        $platformInformation['goods_weight'] = $param['goods_weight'] ;
        $platformInformation['goods_length'] = $param['goods_length'] ;
        $platformInformation['goods_width'] = $param['goods_width'] ;
        $platformInformation['goods_height'] = $param['goods_height'] ;
        $platformInformation['currency_code'] = $param['currency_code'] ;//币种
        $platformInformation['platform_in_stock'] = $param['platform_in_stock'] ;//上架库存
        $platformInformation['promotion_price'] = $param['promotion_price'] ??'0.00' ;//促销价格
        $platformInformation['synchronize_status'] = GoodsDraftAmazon::STATUS_DRAFT ;
        $platformInformation['put_on_status'] = GoodsOnlineAmazon::PUTON_INIT ;
        $platformInformation['put_off_status'] = GoodsOnlineAmazon::PUTOFF_INIT ;
        if(!$amazonId) {
            $platformInformation['local_sku'] = $param['local_sku'];
            $platformInformation['goods_id'] = $param['goods_id'] ;
        }
        if($param['promotion_time']) {
            $promotion = explode(' - ',$param['promotion_time']);
            $platformInformation['promotion_start_time'] = $promotion[0];
            $platformInformation['promotion_end_time'] = $promotion[1];
        }
        DB::beginTransaction();
        try{
            $insert_id = GoodsOnlineAmazon::updateById($amazonId,$platformInformation,$user_id) ;
            if(!$insert_id) {
                DB::rollback() ;
                return [
                    'status'=>false ,
                    'msg'=>'保存失败' ,
                ] ;
            }

            //清除旧数据
            GoodsOnlineAmazonPics::delPicsById($amazonId,$user_id);
            if(!empty($img_sup)) {
                foreach($img_sup as $k => $v) {
                    if(!$v) {
                        continue;
                    }
                    $pics['goods_id'] = $amazonId ? $amazonId  : $insert_id;
                    $pics['created_man'] = $user_id??0 ;
                    $pics['link'] = $v;
                    $pics['user_id'] = $user_id??0;
                    $pics['updated_at'] = date('Y-m-d H:i:s');
                    $pics['created_at'] = date('Y-m-d H:i:s');
                    GoodsOnlineAmazonPics::insert($pics);
                }
            }
            //商品上架队列写数据

            $feedParam ['sku'] = $platformInformation ['sku'];
            //不涉及币种 币种根据站点信息
            $feedParam ['price'] = $platformInformation ['sku'];
            $feedParam ['product_id'] = $platformInformation ['product_code'];
            $feedParam ['product_id_type'] = $platformInformation ['product_code_type'] == 1 ? 'UPC' : 'ASIN';
            $feedParam ['condition_type'] = 'New';
            $feedParam ['quantity'] = $platformInformation ['platform_in_stock'];
            if (empty($feedParam ['quantity'])) {
                DB::rollback() ;
                return [
                    'status'=>false ,
                    'msg'=>'亚马逊平台库存不能为0' ,
                ] ;
            }
            $feedParam ['title'] = $platformInformation ['title'];
            $feedParam ['brand'] = $platformInformation ['brand'];
            $feedParam ['recommended_browse_nodes'] = $platformInformation ['AmazonBrowseNodeID'];
            //图片
            if ($platformInformation ['img_url']) {
//                $feedParam ['image'] = ['img_url'];
            }
            //重
            if ($platformInformation ['goods_weight'] > 0 ) {
                $feedParam ['weight'] = $platformInformation ['brand'];
            }
            $listData['method'] = 'putOn';//上架
            $listData['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
            $listData['request_pk'] = $insert_id;//亚马逊商品模型主键
            $listData['request_user_id'] = $user_id;
            $listData['request_shop_id'] = $platformInformation ['belongs_shop'];//亚马逊店铺
            $listData ['params'] = $feedParam;
            (new AmazonSaleFeed () ) -> putOnListPush($listData);

            DB::commit();
        }catch (Exception $exception){
            DB::rollback() ;
            return [
                'status'=>false ,
                'msg'=>'保存失败' ,
            ] ;
        }

        return [
            'status'=>true ,
            'msg'=>'保存成功' ,
        ] ;
    }

    /**
     * @param Request $request
     * Note: 上架 本地商品已审核才允许上架
     * Data: 2019/6/18 9:58
     * Author: zt7785
     */
    public function amazonGoodsSaleOn (Request $request) {
        $sku = $request->get('sku');
        $currentUser = CurrentUser::getCurrentUser();
        if($currentUser->userAccountType == AccountType::CHILDREN) {
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
            $shops = $this->shop->getShopByPlatId(Platforms::AMAZON,$user_id) ;
        }

        $goods_attribute = $this->goodsAttribute::getAllAttrs() ;

//        $currency = SettingCurrencyExchangeMaintain::where('user_id', $user_id)
//            ->get();
        $currency = SettingCurrencyExchange::get();

        if(!$currency->isEmpty()) {
            $currency = $currency->toArray();
        }

        //新增时需sku是否为本地审核通过
        $goods = $this->Goods->getGoodsIdBySku($sku,$user_id);
        if(!$goods) {
            return abort(404);
        }
        $goods_id = $goods->id;
        unset($goods->id);
        $goods = $goods->toArray();
        $goods_quantity = WarehouseTypeGoods::getAllocationQuantity($goods_id,Platforms::AMAZON,$user_id);
        return view('Goods.Amazon.addAmazonGoods')->with([
            'goods'=>isset($goods) ? $goods : ''
            ,'shops'=>$shops
            ,'currency'=>$currency
            ,'goods_attribute'=>$goods_attribute
            ,'sku'=>$sku
            ,'goods_id'=>isset($goods_id) ? $goods_id : ''
            ,'goods_quantity'=>$goods_quantity
        ]) ;
    }
}
