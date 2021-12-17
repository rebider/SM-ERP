<?php

    namespace App\Http\Controllers\Goods;

    use App\Auth\Common\AjaxResponse;
    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\RolesShops;
    use App\Http\Requests\CheckIDsRequest;
    use App\Http\Services\Goods\RakutenGoodsHandle;
    use App\Models\CategorysRakuten;
    use App\Models\Goods;
    use App\Auth\Models\Menus;
    use App\Models\GoodsDraftRakuten;
    use App\Models\GoodsDraftRakutenPics;
    use App\Models\Lotte;
    use App\Models\LottePic;
    use App\Models\Platforms;
    use App\Models\PlatformsInformation;
    use App\Models\SettingCurrencyExchange;
    use App\Models\SettingShops;
    use App\Models\WarehouseTypeGoods;
    use http\Env\Response;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Mockery\Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class LotteController extends Controller
    {
        //
        /**
         * @author zt6650
         * @var Lotte
         */
        protected $Lotte;
        /**
         * @author zt6650
         * @var SettingShops
         */
        protected $shop;
        /**
         * @author zt6650
         * @var PlatformsInformation ;
         */
        protected $platformInformation;
        /**
         * @author zt6650
         * @var SettingCurrencyExchange
         */
        protected $settingCurrency;
        /**
         * @author zt6650
         * @var LottePic
         */
        protected $LottePic;
        /**
         * @author zt12779
         * @var 商品目录ID
         */
        const ORDER_MENUS_ID = 2;

        public function __construct()
        {
            $this->Lotte = new Lotte;
            $this->shop = new SettingShops;
            $this->LottePic = new GoodsDraftRakutenPics();
            $this->platformInformation = new PlatformsInformation;
            $this->Goods = new Goods();
        }

        /**
         * @desc 乐天草稿箱首页
         * @author zt6650
         * CreateTime: 2019-04-11 16:53
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         */
        public function index()
        {
            $menusModel = new Menus();
            $responseData ['shortcutMenus'] = $menusModel->getShortcutMenu(self::ORDER_MENUS_ID);
            return view('Goods.Lotte.index')->with($responseData);
        }

        /**
         * @desc 乐天草稿箱商品查询
         * @author zt6650
         * CreateTime: 2019-04-11 16:54
         * @param Request $request
         * @return array
         */
        public function ajaxGetAllByParams(Request $request)
        {
            $pageIndex = $request->get('page', 1);
            $pageSize = $request->get('limit', 20);
            $params = $request->get('data', []);
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

            $data = GoodsDraftRakuten::getList($params, $user_id);
            $res = [
                'code'  => '0',
                'msg'   => '',
                'count' => $data['total'],
                'data'  => $data['data'],
            ];
            return $res;
        }

        /**
         * @desc 上架
         * @author zt8067
         * CreateTime: 2019-06-17 10:30
         * @param Request $request
         * @return array
         */
        public function putOnSale(CheckIDsRequest $request)
        {
            set_time_limit(0);
            //无视请求断开
            ignore_user_abort();
            $ids = $request->ids;
            $type = $request->type;
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
            $results = (new RakutenGoodsHandle())->putOnSaleProcessing($ids, $user_id,$type);
            return parent::layResponseData($results);
        }

        /**
         * @note
         * 商品上架
         * @since: 2019/6/12
         * @author: zt7837
         * @return: array
         */
        public function add(Request $request)
        {
            return view('Goods.Lotte.add');
        }

        /**
         * @note
         * 乐天编辑
         * @since: 2019/6/6
         * @author: zt7837
         * @return: array
         */
        public function edit(Request $request)
        {
            $id = $request->input('id');
            $sku = $request->input('sku');
            $currentUser = CurrentUser::getCurrentUser();
            if (empty($currentUser)) {
                abort(404);
            }
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
                $shopsPermission = RolesShops::getShopPermissionByUserid($user_id);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (!empty($shopsId)) {
                        $shops = SettingShops::getShopsByShopsId($shopsId,Platforms::RAKUTEN);
                    } else {
                        $shops = [];
                    }
                } else {
                    $shops = [];
                }
            } else {
                $user_id = $currentUser->userId;
                $shops = $this->shop->getShopByPlatId(Platforms::RAKUTEN,$user_id) ;
            }

            $data = Lotte::editInfo($user_id);
            $data['shopsArr'] = $shops;
            $data['sku'] = $sku;
            $data['id'] = $id;
            //给定变量初始值
            $goods_id = '';
            $selectedCategory = [];

            //新增 已审核的本地sku
            if (!$id) {
                $goods = $this->Goods->getGoodsIdBySku($sku,$user_id);
                if (!$goods) {
                    return abort(404);
                }
                $goods_id = $goods->id;
                unset($goods->category_id_1);
                unset($goods->category_id_2);
                unset($goods->category_id_3);
                unset($goods->id);
            } else {
                //编辑 乐天分类
                $goods = GoodsDraftRakuten::with('pictures')->where(['id' => $id, 'user_id' => $user_id])->first();
                if($goods) {
                    $rakuten_cate = GoodsDraftRakuten::getLotteCat($goods->rakuten_category_id);

                    if($goods->rakuten_category_id) {
                        $categoryInArray = explode(',',$goods->rakuten_category_id);
                        foreach ($categoryInArray as $key => $val) {
                            $index = $key + 1;
                            $selectedCategory["category_id_{$index}"] = $val;
                        }
                    }
                    $data['category']['first'] = $rakuten_cate['first'];
                    $data['category']['second'] = $rakuten_cate['second'];
                    $data['category']['third'] = $rakuten_cate['third'];
                    $data['category']['four'] = $rakuten_cate['four'];
                }
            }

            //给定变量初始值
            if($goods) {
                $goodsInfo = $goods ? $goods->toArray() : '';
                $goodsInfo['goods_id'] = isset($goodsInfo['goods_id']) && !empty($goodsInfo['goods_id']) ? $goodsInfo['goods_id'] : $goods_id;
                $data['goodsInfo'] = $goodsInfo;
            }
            $data['goodsInfo']['category'] = isset($rakuten_cate['str']) ? $rakuten_cate['str'] : '';
            $data['goodsInfo']['categoryInArray'] = isset($categoryInArray) ? $categoryInArray : '';
            $data['goodsInfo']['selectedCategory'] = $selectedCategory;

            if(isset($goodsInfo['goods_id'])) {
                $data['goods_quantity'] = WarehouseTypeGoods::getAllocationQuantity($goodsInfo ['goods_id'],Platforms::RAKUTEN,$user_id);
            }

            $data['fromLocal'] = true;
            return view('Goods.Lotte.edit')->with($data);
        }

        /**
         * @note
         * 乐天编辑保存
         * @since: 2019/6/6
         * @author: zt7837
         * @return: array
         */
        public function editSave(Request $request)
        {
            $param = $request->input('param', []);
            $img_sup = $request->input('img_sup');
            $rakuten_id = $request->input('rakuten_id');
            $type = $request->input('type','');
            $responseData['code'] = 0;
            $responseData['msg'] = 'fail';

            $currentUser = CurrentUser::getCurrentUser();
            if (empty($currentUser)) {
                abort(404);
            }
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
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

            $this->valideLotte($request);
            $goodsInfo['belongs_shop'] = $param['store_id'];
            $goodsInfo['catalogId'] = $param['catalogId'];
            $goodsInfo['cmn'] = $param['itemUrl'] ?? '';
            $goodsInfo['sku'] = $param['itemNumber'] ?? '';
            $goodsInfo['sale_price'] = $param['sale_price'] ?? 0.00;
            $goodsInfo['platform_in_stock'] = $param['inventory'] ?? 0;
            $goodsInfo['title'] = $param['goods_name'] ?? '';
            $goodsInfo['goods_name'] = $param['goods_title'] ?? '';
            $goodsInfo['goods_description'] = $param['goods_desc'] ?? '';
            $goodsInfo['currency_code'] = $param['currency_code']??'';
            $goodsInfo['user_id'] = $user_id;
            $goodsInfo['catalogIdExemptionReason'] = $param['reason'] ?? 0;
            $goodsInfo['img_url'] = $param['img_import'] ?? '';
            $goodsInfo['rakuten_category_id'] = $param['rakuten_category_id']??'';
            //本地商品的长宽高
            $goodsInfo['goods_length'] = $param['goods_length']??'';
            $goodsInfo['goods_width'] = $param['goods_width']??'';
            $goodsInfo['goods_height'] = $param['goods_height']??'';
            $goodsInfo['goods_weight'] = $param['goods_weight']??'';


            if (isset($shopsId) && !in_array($goodsInfo['belongs_shop'],$shopsId)) {
                return [
                    'status'=>false ,
                    'msg'=>'店铺权限异常' ,
                ] ;
            }
            DB::beginTransaction();
            try {
                $insert_id = (new GoodsDraftRakuten)->updatedById($goodsInfo, $param['lotte_id'], $user_id, $param['sku']);
                if (!$insert_id) {
                    DB::rollback();
                    return $responseData;
                }

                //附图
                if ($img_sup) {
                    GoodsDraftRakutenPics::where(['goods_id' => $param['lotte_id'], 'user_id' => $user_id])->delete();
                    foreach ($img_sup as $key => $val) {
                        $pics[$key]['goods_id'] = $param['lotte_id'] ? $param['lotte_id'] : $insert_id;
                        $pics[$key]['user_id'] = $user_id;
                        $pics[$key]['created_man'] = $user_id;
                        $pics[$key]['link'] = $val;
                    }
                    $re = DB::table('goods_draft_rakuten_pics')->insert($pics);
                    if (!$re) {
                        DB::rollback();
                        return $responseData;
                    }
                }
                DB::commit();
                //成功后调用上架接口
                if($type === 'putOnBatch'){
                    if(empty($param['lotte_id'])){
                        $lotte_id = $insert_id;
                    }else{
                        $lotte_id = $param['lotte_id'];
                    }
                    $results =(new RakutenGoodsHandle())->putOnSaleProcessing($lotte_id, $user_id,'local');
                    if ($results['code'] === 1){
                        $responseData['msg'] = 'success';
                    }else{
                        (new GoodsDraftRakuten)->where('id', $lotte_id)->update(['synchronize_status' => GoodsDraftRakuten::SYNCHRONIZE_STATUS_ERROR,'synchronize_info'=>$results['errAll'][0]]);
                        $responseData['msg'] = "上架失败！草稿信息已保存。<br>".$results['errAll'][0];
                    }
                }else{
                    $responseData['msg'] = 'success';
                }
                return $responseData;
            } catch (\Exception $e) {
                DB::rollback();
                return $responseData;
            }
        }

        public function valideLotte($request)
        {
            $param = $request->input('param');
            $rule = [
                'param.store_id'       => 'required|numeric',
                'param.itemUrl'        => 'required|between:0,255',
                'param.itemNumber'     => 'nullable|between:0,32',
                'param.sale_price'     => 'required|numeric|max:9999999999',
                'param.inventory' => 'required|numeric|min:1|max:9999999999',
                'param.goods_name'     => 'required|max:100',
                'param.goods_title'    => 'required|nullable|max:400',
                'param.goods_desc'     => 'required|nullable|max:400',
                'param.rakuten_category_id'     => '',
                'param.firstCategory' => 'required',
                'param.secondCategory' => 'required',
                'param.thirdCategory' => 'required',

            ];
            //因为有可能没有四级分类 所以这里做可选判断
            $thirdCategory = $request->input('param.thirdCategory');
            //查询三级分类下面是否还有子分类 有责校验四级分类
            $children = CategorysRakuten::where(['parentID'=>$thirdCategory])->get();
            if(!$children->isEmpty()) {
                $rule['param.fourCategory'] = 'required';
            }
            if(($param['catalogId']) && !$param['reason']) {
                $rule['param.catalogId'] = 'required|numeric';
            }
            if($param['reason'] && !$param['catalogId']) {
                $rule['param.reason'] = 'required';
            }
            if(!$param['reason'] && !$param['catalogId']) {
                $rule['param.catalog'] = 'required';
            }
            $this->validate($request,$rule, [
                'required' => ':attribute 为必填项',
                'max'      => ':attribute 超出最大值限制',
                'min'      => ':attribute 超出最小值限制',
                'unique'   => ':attribute 已经存在',
            ], [
                'param.store_id'       => '店铺',
                'param.goods_name'     => '商品名称',
                'param.itemUrl'        => '商品管理番号',
                'param.itemNumber'     => '商品番号',
                'param.sale_price'     => '销售价格',
                'param.inventory'      => '平台库存',
                'param.goods_title'    => '商品标题',
                'param.goods_desc'     => '商品描述',
                'param.rakuten_category_id'     => '目录编号',
                'param.reason'         => '没有目录ID的原因',
                'param.catalog' => '目录ID',
                'param.catalogId' => 'JAN代码',
                'param.firstCategory' => '一级分类',
                'param.secondCategory' => '二级分类',
                'param.thirdCategory' => '三级分类',
                'param.fourCategory' => '四级分类',
            ]);
        }

        /**
         * @desc 草稿箱的商品删除
         * @author zt6650
         * CreateTime: 2019-04-18 09:48
         * @param Request $request
         * @return array
         */
        public function deleteLotte(Request $request)
        {
            $id = $request->get('id', 0);
            $currentUser = CurrentUser::getCurrentUser();
            if (empty($id) || !is_numeric($id)) {
                abort(404);
            }
            if (empty($currentUser)) {
                abort(404);
            }
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }
            $re = (new GoodsDraftRakuten)->deleteById($user_id, $id, $this->LottePic);
            if (!$re) {
                return parent::layResponseData(['code' => 0, 'msg' => '删除失败']);
            }
            return parent::layResponseData(['code' => 200, 'msg' => '删除成功']);
        }

        /**
         * @note
         * 获取乐天分类
         * @since: 2019/6/18
         * @author: zt7837
         * @return: array
         */
        public function getCategory(Request $request)
        {
            $pid = $request->input('parentId');
            $responseData['code'] = 0;
            $responseData['data'] = '';
            $cate = CategorysRakuten::where('parentID',$pid)->get();
            if($cate->isEmpty()) {
                $responseData['code'] = -1;
                return Response()->json($responseData);
            }
            $responseData['data'] = $cate->toArray();
            return Response()->json($responseData);
        }

        /**
         * @note
         * 检测乐天商品
         * @since: 2019/6/29
         * @author: zt7837
         * @return: array
         */
        public function checkGoods(Request $request) {
            $sku = $request->input('sku');
            $response['code'] = -1;
            $response['msg'] = '';
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
                $response['msg'] = '商品不存在!';
                return $response;
            }
            $goods = $this->Goods->getGoodsIdBySku($sku,$user_id);
            if(!$goods) {
                $response['msg'] = '商品未审核!';
                return parent::layResponseData($response);
            }
            $response['code'] = 0;
            $response['msg'] = '';
            return parent::layResponseData($response);
        }
    }
