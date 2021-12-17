<?php

namespace App\Http\Controllers\Goods;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Http\Controllers\PhotoController;
use App\Http\Services\APIHelper;
use App\Http\Services\Collection;
use App\Models\Category;
use App\Models\Goods;
use App\Models\GoodsAttribute;
use App\Models\GoodsCollection;
use App\Models\GoodsDeclare;
use App\Models\GoodsLocalPic;
use App\Models\GoodsPic;
use App\Models\Procurements;
use App\Models\SettingCurrencyExchange;
use App\Models\Suppliers;
use App\Models\Upc;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class GoodsManage extends Controller
{
    //

    /**
     * @var Goods;
     */
    protected $Goods ;

    /**
     * @var GoodsCollection
     */
    protected $GoodsCollection ;

    /**
     * @var GoodsPic
     */
    protected $GoodsPic ;

    /**
     * @author zt6650
     * @var Category
     */
    protected $Category ;

    /**
     * @author zt6650
     * @var GoodsLocalPic
     */
    protected $GoodsLocalPics ;

    /**
     * @author zt6650
     * @var Procurements
     */
    protected $Procurement ;

    /**
     * @author zt6650
     * @var GoodsDeclare
     */
    protected $GoodsDeclare ;

    /**
     * 侧边菜单栏
     * @author zt12779
     * @var
     */
    protected $shortCutMenus;

    public function __construct()
    {
        $this->GoodsCollection = new GoodsCollection ;
        $this->GoodsPic = new GoodsPic ;
        $this->Goods = new Goods ;
        $this->Category = new Category ;
        $this->GoodsDeclare = new GoodsDeclare ;
        $this->Procurement = new Procurements ;
        $this->GoodsLocalPics = new GoodsLocalPic ;
        $this->shortCutMenus = Goods::getGoodsShortcutMenu();
    }

    /**
     * @description 默认初始页面
     * @author zt6650
     * @creteTime 2019/3/11 17:10
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $responseData['shortcutMenus'] = $this->shortCutMenus;
        return view('Goods.GoodsManage.index')->with($responseData);
    }

    /**
     * @description 商品采集的首页
     * @author zt6650
     * @creteTime 2019/3/12 16:54
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function goodsCollect1(Request $request)
    {
        $responseData['shortcutMenus'] = $this->shortCutMenus;
        return view('Goods.GoodsManage.goodsCollect1')->with($responseData);
    }

    public function goodsCollect(Request $request)
    {
        $responseData['shortcutMenus'] = $this->shortCutMenus;
        return view('Goods.GoodsManage.goodsCollect')->with($responseData);
    }

    public function localIndex(Request $request)
    {
        $responseData['shortcutMenus'] = $this->shortCutMenus;
        return view('Goods.GoodsManage.localIndex')->with($responseData);
    }

    /**
     * @description
     * @author zt6650
     * @creteTime 2019/3/13 16:44
     * @param Request $request
     * @return  array
     */
    public function collectionGoods(Request $request)
    {
        set_time_limit(0);
        $urls = $request->get('urls' ,'') ;
        $url_arr = explode("\n" ,$urls ) ;
        if (count($url_arr) > 10) {
            return [
                'status'=>false ,
                'msg'=>'单次最多十个商品' ,
            ];
        }

        try {
            DB::beginTransaction();
            foreach ($url_arr as $value) {
                $API = new APIHelper($value);
                if (!$API->plate) {
                    DB::rollback();
                    return [
                        'status' => false,
                        'msg' => $value . '数据有误!',
                    ];
                }
                $re = $API->getAll();
                //首先要数据下载到本地
                $photo = new PhotoController();
                $imgMain = $photo->getImage($re['imgMain']);
                foreach ($re['imgSup'] as $val) {
                    $dir = $photo->getImage($val);
                    $imgSup[] = $dir['save_path'];
                }
                $re['imgSup'] = $imgSup ?? '';
                $re['imgMain'] = $imgMain['save_path'];
                $re['url'] = $value;
                $this->createGoods($re);
                unset($API);
            }

            DB::commit();
        } catch (\Exception $exception) {
            info($exception->getMessage()) ;
            return [
                'status' => false,
                'msg' => '未知错误!',
            ] ;
        };
        return [
            'status'=>true ,
            'msg'=>'获取商品成功!'
        ] ;
    }

    /**
     * @description 创建信息
     * @author zt6650
     * @creteTime 2019/3/13 19:43
     * @param $re
     */
    public function createGoods($re)
    {
        $goods =  [
            'goods_pictures' => (string)$re['imgMain'] ,
            'title' => (string)$re['title'] ,
            'description' => (string)$re['description'] ,
            'plat' => (string)$re['plat'] ,
            'url' => (string)$re['url'] ,
        ] ;
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
        $userInfo['created_man'] = $currentUser->userId ;
        $userInfo['user_id'] = $user_id ;
        $goodsId = $this->GoodsCollection->insertArr($goods,$userInfo) ;
        $imgSup = $re['imgSup'] ;
        if ($imgSup) {
            foreach ($imgSup as $value){
                $in = [
                    'goods_id'=>$goodsId ,
                    'link'=>$value ,
                ] ;
                $f = $this->GoodsPic->insertArr($in,$userInfo) ;
            }
        }
    }

    /**
     * @description 查询
     * @author zt6650
     * @creteTime 2019/3/11 17:11
     * @param Request $request
     * @return array
     */
    public function ajaxGetAllGoodsByParams(Request $request)
    {
        $pageIndex  = $request->get('page' ,1) ;
        $pageSize   = $request->get('limit' ,20) ;
        $params     = $request->get('data' ,[]) ;
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'code' => '0',
                'msg' =>'用户信息过期,请重新登录',
                'count' => 0,
                'data'  => []
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $params ['user_id'] = $user_id;
        $collection = $this->GoodsCollection->search($params) ;
        $count = $collection->count() ;
        $data = $collection->skip(($pageIndex-1)*$pageSize)->take($pageSize)->get()->toArray();

        $res = array(
            'code' => '0',
            'msg' =>'',
            'count' => $count,
            'data'  => $data
        );

        return $res ;
    }

    /**
     * @description 本地商品的查询
     * @author zt6650
     * @creteTime 2019/3/29 16:28
     * @param Request $request
     * @return array
     */
    public function ajaxGetAllLocaGoodsByParams(Request $request)
    {
        $pageIndex  = $request->get('page' ,1) ;
        $pageSize   = $request->get('limit' ,20) ;
        $params     = $request->get('data' ,[]) ;

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        //2019年5月21日13:42:43 后面完善权限
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $params ['user_id'] = $user_id;

        $collection = $this->Goods->search($params) ;
        $count = $collection->count() ;
        $data = $collection->skip(($pageIndex-1)*$pageSize)->take($pageSize)->get()->toArray();

        $res = array(
            'code' => '0',
            'msg' =>'',
            'count' => $count,
            'data'  => $data
        );

        return $res ;
    }

    /**
     * @description 新增一条数据
     * @author zt6650
     * @creteTime 2019/3/11 17:12
     * @param Request $request
     */
    public function created(Request $request)
    {
        $param = $request->get('param' ,[]) ;
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
        $userInfo['created_man'] = $currentUser->userId ;
        $userInfo['user_id'] = $user_id ;
        $this->GoodsCollection->insertArr($param,$userInfo) ;
    }

    /**
     * @description
     * @author zt6650
     * @creteTime 2019/3/22 11:38
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function del(Request $request)
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

        $re = $this->GoodsCollection->delById($id,$user_id) ;
        if ($re){
            return [
                'status'=>true ,
                'msg'=>'删除成功!'
            ] ;
        }

        return [
            'status'=>false ,
            'msg'=>'删除失败!'
        ] ;
    }

    /**
     * @description 认领商品
     * @author zt6650
     * @creteTime 2019/3/21 16:38
     * @param Request $request
     * @return  array
     */
    public function claimById(Request $request)
    {
        // todo 认领逻辑
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
        //组装权限参数
        $id = $request->get('id', 0);
        $collectGoods = $this->GoodsCollection->getInfoById($id);
        if (empty($collectGoods)) {
            abort(404);
        }
        $goods_attrs = GoodsAttribute::getAllAttrs();
        $currency = SettingCurrencyExchange::getAllCurrency($user_id)->toArray();
        $supplier = Suppliers::where('status' ,Suppliers::ON)->where('user_id',$user_id)->get()->toArray() ;
//        dd([
//            'goodsInfo'=>$collectGoods ,
//            'goods_attrs'=>$goods_attrs,
//            'currency'=>$currency,
//            'suppliers'=>$supplier
//        ]);
        return view('Goods.GoodsManage.goodsClaim')->with([
            'goodsInfo'=>$collectGoods ,
            'goods_attrs'=>$goods_attrs,
            'currency'=>$currency,
            'suppliers'=>$supplier
        ]);
//        $re = $this->GoodsCollection->claimById($id) ;
//        if ($re){
//            return [
//                'status'=>true ,
//                'msg'=>'认领成功!'
//            ] ;
//        }
//
//        return [
//            'status'=>false ,
//            'msg'=>'认领失败!'
//        ] ;
    }


    /**
     * todo 需要知道验证规则
     * @desc 认领商品的验证
     * @author zt6650
     * CreateTime: 2019-04-11 15:11
     * @param $request
     */
    public function validateGoods($request)
    {
        //取出所有的本地商品的分类id
//        $goods_attrs = GoodsAttribute::getAllAttrs();
        $goods_attrs = $this->Category->pluck('id')->toArray() ;
        $this->validate($request, [
            'param.sku' => 'required|unique:goods,sku|max:50',
            'param.firstCategory' => ['required', Rule::in($goods_attrs),],
            'param.secondCategory' => ['required', Rule::in($goods_attrs),],
            'param.thirdCategory' => ['required', Rule::in($goods_attrs),],
            'param.goods_name' => 'required|max:100',
            'param.goods_attribute_id' => 'required|max:100',
            'param.goods_weight' => 'required|max:100',
            'param.goods_height' => 'required|max:100',
            'param.goods_title' => 'required|max:500',
            'param.description' => 'required|max:1000',
            'param.currency_id' => 'required|numeric',
            'param.ch_name' => 'required|max:100',
            'param.eh_name' => 'required|max:100',
            'param.price' => 'required|numeric',
            'param.goods_brand' => 'required|max:400',
            'param.manufacturers' => 'required|max:400',
            'param.custom_code' => 'required|max:400',
            'param.specifications' => 'nullable|max:400',
            'param.company' => 'nullable|max:400',
//            'param.file' => 'nullable|max:400',
            'param.preferred_supplier_id' => 'required|numeric',
            'param.preferred_price' => 'required|numeric|max:400',
            'param.preferred_url' => 'nullable|max:400',
            'param.alternative_supplier_id' => 'nullable|numeric',
            'param.alternative_price' => 'nullable|numeric|max:400',
            'param.alternative_url' => 'nullable|max:400',
            'img_import' => 'nullable|max:400',
            'img_sup' => 'nullable|max:800',
        ], [
            'required' => ':attribute 为必填项',
            'max' => ':attribute 超出最大值限制',
            'min' => ':attribute 超出最小值限制',
            'unique' => ':attribute 已经存在',
        ], [
            'param.firstCategory' => '一级分类',
            'param.secondCategory' => '二级分类',
            'param.thirdCategory' => '三级分类',
            'param.sku' => '自定义sku',
            'param.goods_name' => '产品名称',
            'param.goods_attribute_id' => '产品属性',
            'param.goods_weight' => '产品尺寸 宽',
            'param.goods_height' => '产品尺寸 高',
            'param.goods_length' => '产品尺寸 长',
            'param.ch_name' => '申报中文名',
            'param.en_name' => '申报英文名',
            'param.goods_title' => '产品标题',
            'param.description' => '产品描述',
            'param.currency_id' => '币种',
            'param.price' => '申报价格',
            'param.goods_brand' => '产品品牌',
            'param.manufacturers' => '制造商',
            'param.custom_code' => '海关编码',
            'param.specifications' => '规格型号',
            'param.company' => '申报单位',
//            'param.file' => '',
            'param.preferred_supplier_id' => '首选供应商',
            'param.preferred_price' => '采购价1',
            'param.preferred_url' => '采购链接1',
            'param.alternative_supplier_id' => '备选供应商',
            'param.alternative_price' => '采购价2',
            'param.alternative_url' => '采购链接2',
            'img_import' => '主图',
            'img_sup' => '附图',
        ]);
    }

    /**
     * @desc 验证商品的保存
     * @author zt6650
     * CreateTime: 2019-04-11 15:12
     * @param Request $request
     */
    public function claimByIdPost(Request $request)
    {
        $param = $request->get('param' ,[]) ;
        $img_import = $request->get('img_import' ,'') ;
        $img_sup = $request->get('img_sup' ,[]) ;
        $this->validateGoods($request) ;
        $CurrentUser = CurrentUser::getCurrentUser();
        if (empty($CurrentUser)) {
            return [
                'status'=>false ,
                'msg'=>'用户信息过期,请重新登录'
            ] ;
        }
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        //认领状态
        $collectInfo = GoodsCollection::where('id',$param['goods_collect_id'])->first(['id','status']);
        if (empty($collectInfo)) {
            return [
                'status'=>false ,
                'msg'=>'认领失败!采集信息异常'
            ] ;
        }
        if ($collectInfo ['status'] == GoodsCollection::STATUS_PASS) {
            return [
                'status'=>true ,
                'msg'=>'认领成功!'
            ] ;
        }
        DB::beginTransaction();
        try {
            //商品主要信息
            $goodsInfo['goods_attribute_id'] = $param['goods_attribute_id'];
            $goodsInfo['category_id_1'] = $param['firstCategory'];
            $goodsInfo['category_id_2'] = $param['secondCategory'];
            $goodsInfo['category_id_3'] = $param['thirdCategory'];

            $goodsInfo['sku'] = $param['sku'];
            $goodsInfo['goods_name'] = $param['goods_name'];
            $goodsInfo['goods_title'] = $param['goods_title'];
            $goodsInfo['synchronization'] = $param['firstCategory'];
            $goodsInfo['status'] = Goods::STATUS_DRAFT; //状态
            $goodsInfo['goods_height'] = $param['goods_height'];
            $goodsInfo['goods_width'] = $param['goods_width'];
            $goodsInfo['goods_length'] = $param['goods_length'];
            $goodsInfo['goods_weight'] = $param['goods_weight'];
            $goodsInfo['description'] = $param['description'];
            $goodsInfo['plat'] = $param['plat'] ?? 0;
            $goodsInfo['from_url'] = $param['from_url'] ?? "";

            $goodsInfo['created_man'] = $CurrentUser->userId;
            $goodsInfo['user_id'] = $user_id;
            $goodsInfo['goods_pictures'] = isset($param['goods_pictures']) ? $param['goods_pictures'] : '';

            $goodsObject = $this->Goods->insertArr($goodsInfo);

            $goodsId = $goodsObject->id;

            //先存商品的本地信息-》商品的申报信息-》商品图片
            $declare['currency_id'] = $param['currency_id'];
            $declare['ch_name'] = $param['ch_name'];
            $declare['eh_name'] = $param['eh_name'];
            $declare['price'] = $param['price'];
            $declare['goods_brand'] = $param['goods_brand'];
            $declare['manufacturers'] = $param['manufacturers'];
            $declare['custom_code'] = $param['custom_code'];
            $declare['specifications'] = $param['specifications'];
            $declare['company'] = $param['company'];
            $declare['created_man'] = $CurrentUser->userId;
            $declare['user_id'] = $user_id;
            $declare['goods_id'] = $goodsId;
            $this->GoodsDeclare->addGetId($declare, $goodsId, $user_id);

            //采购信息
            $procurement['preferred_supplier_id'] = $param['preferred_supplier_id'];
            $procurement['preferred_price'] = $param['preferred_price'];
            $procurement['preferred_url'] = $param['preferred_url'];
            $procurement['alternative_supplier_id'] = $param['alternative_supplier_id'];
            $procurement['alternative_price'] = $param['alternative_price'];
            $procurement['alternative_url'] = $param['alternative_url'];
            $procurement['goods_id'] = $goodsId;
            $procurement['created_man'] = $CurrentUser->userId;
            $procurement['user_id'] = $user_id;
            $this->Procurement->addGetId($procurement, $goodsId, $user_id);

            //存入商品的图片
            $pic['user_id'] = $user_id;
            $pic['created_man'] = $user_id;
            foreach ($img_sup as $value) {
                $pic['goods_id'] = $goodsId;
                $pic['link'] = $value;
                $this->GoodsLocalPics->insertArr($pic);
            }

            //改变采集商品的状态
            $this->GoodsCollection->claimById($param['goods_collect_id'],$goodsId);
            DB::commit();
            return [
                'status'=>true ,
                'msg'=>'认领成功!'
            ] ;
        } catch (\Exception $e) {
            DB::rollBack();
            info($e) ;
            return [
                'status'=>false ,
                'msg'=>'认领失败!'
            ] ;
        }
    }

    public function ajaxGetFirstCategory(Request $request)
    {
        $category = new Category() ;
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $type = $request->get('type' ,Category::TYPE_LOCAL) ;
        $categoryInfo = $category->getAllFirstCategoryByType($type,$user_id) ;

        if (!$categoryInfo){
            return [
                'status'=>false ,
                'data'=>[] ,
                'msg'=>''
            ] ;
        }

        $categoryInfo = $categoryInfo->toArray() ;

        return [
            'status'=>true ,
            'data'=>$categoryInfo ,
            'msg'=>''
        ] ;
    }

    /**
     * @desc 获取所有的下级分类
     * @author zt6650
     * CreateTime: 2019-04-09 10:43
     * @param Request $request
     * @return array
     */
    public function ajaxGetChildren(Request $request)
    {
        $pid = $request->get('parentId' ,0) ;
//        if (!$pid){
//            return [
//                'status'=>false,
//                'data'=>[] ,
//                'msg'=>'请求信息有误!'
//            ] ;
//        }

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'status' => 0,
                'data'    => '添加失败!用户信息过期,请重新登录'
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $category = new Category ;
        $categoryInfo = $category->getAllChildrenById($pid,$user_id) ;
        $categoryInfo = $categoryInfo->toArray() ;

        return [
            'code'=>0 ,
            'data'=>$categoryInfo ,
            'msg'=>''
        ] ;
    }

    /**
     * @description 商品分类-首页
     * @author zt7927
     * @date 2019/4/19 10:17
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function categoryIndex()
    {
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //产品分类
        $categories = new Category();
        $categories = $categories->getAllFirstCategoryByType(Category::TYPE_LOCAL,$user_id)->toArray();
        //侧边栏
        $responseData['shortcutMenus'] = Upc::getGoodsShortcutMenu();

        return view('Goods.GoodsManage.Category.index', compact('categories'))->with($responseData);
    }

    /**
     * @description 根据父级id获取子集分类
     * @author zt7927
     * @date 2019/4/19 16:27
     * @param Request $request
     * @return array|bool
     */
    public function getChildCategoryById(Request $request)
    {
        $params = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'status' => 0,
                'data'    => '添加失败!用户信息过期,请重新登录'
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if(is_numeric($params['category_id']) && ($params['category_id'] > 0)){
            $categories = new Category();
            $categories = $categories->getAllChildrenById($params['category_id'],$user_id)->toArray();
            return [
                'status' => 1,
                'data'   => $categories
            ];
        }
        return [
            'status' => 0,
            'data'    => []
        ];
    }

    /**
     * @description 新增分类
     * @author zt7927
     * @date 2019/4/23 13:48
     * @param Request $request
     * @return array|bool
     */
    public function addCategory(Request $request)
    {
        $params = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'status' => 0,
                'msg'    => '添加失败!用户信息过期,请重新登录'
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (is_numeric($params['parent_id']) && !empty($params['category_name'])){
            $categories = new Category();
            $category = $categories->getCategoryByName($params['category_name'],$user_id);
            if (!$category){
                $params ['created_man'] =  $currentUser->userId;
                $params ['user_id'] =  $user_id;
                $re = $categories->insertArr($params);
                if ($re){
                    return [
                        'status' => 1,
                        'msg'    => '添加成功',
                        'id'     => $re,
                        'category_name' => $params['category_name']
                    ];
                }
                return [
                    'status' => 0,
                    'msg'    => '添加失败'
                ];
            }
            return [
                'status' => 0,
                'msg'  => '分类名称：'.$params['category_name'].' 已存在'
            ];
        }
        return false;
    }

    /**
     * @description 编辑分类-保存
     * @author zt7927
     * @date 2019/4/19 18:03
     * @param Request $request
     * @return array|bool
     */
    public function editCategoryById(Request $request)
    {
        $params = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'status' => 0,
                'msg'    => '保存失败!用户信息过期,请重新登录'
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if(is_numeric($params['category_id']) && ($params['category_id'] > 0) && !empty($params['category_name'])){
            $categories = new Category();
            $category = $categories->getCategoryByName($params['category_name'],$user_id);
            if (!$category){
                $re = $categories->updateArr($params,$user_id);
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
                'msg'  => '分类名称：'.$params['category_name'].' 已存在'
            ];
        }
        return false;
    }

    /**
     * @description 删除分类
     * @author zt7927
     * @date 2019/4/23 15:19
     * @param Request $request
     * @return array|bool
     */
    public function delCategoryById(Request $request)
    {
        $params = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return [
                'status' => 0,
                'msg'    => '删除失败!用户信息过期,请重新登录'
            ];
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if(is_numeric($params['category_id']) && ($params['category_id'] > 0)){
            $categories = new Category();
            $category = $categories->getChildrenById($params['category_id'],$user_id);
            $goods = $this->Goods->getGoodsByCategoryId($params['category_id'],$user_id);
            if (!$category && !$goods){
                $re = $categories->delCategoryById($params['category_id'],$user_id);
                if ($re){
                    return [
                        'status' => 1,
                        'msg'    => '删除成功'
                    ];
                }
                return [
                    'status' => 0,
                    'msg'    => '删除失败'
                ];
            } else {
                if ($category){
                    return [
                        'status' => 0,
                        'msg'  => '此分类下存在子分类，不允许删除'
                    ];
                }
                if ($goods){
                    return [
                        'status' => 0,
                        'msg'  => '此分类下已存在商品纪录，不允许删除'
                    ];
                }
            }
        }
        return false;
    }
}
