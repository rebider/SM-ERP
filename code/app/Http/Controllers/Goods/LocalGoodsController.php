<?php

namespace App\Http\Controllers\Goods;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Common\Common;
use App\Http\Controllers\Order\OrderController;
use App\Models\Category;
use App\Models\Goods;
use App\Models\GoodsAttribute;
use App\Models\GoodsDeclare;
use App\Models\GoodsDraftAmazon;
use App\Models\GoodsDraftAmazonPics;
use App\Models\GoodsDraftRakuten;
use App\Models\GoodsDraftRakutenPics;
use App\Models\GoodsLocalPic;
use App\Models\Procurements;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingWarehouse;
use App\Models\Suppliers;
use App\Models\WarehouseGoods;
use App\Models\WarehouseSecretkey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel;

/**
 * Class LocalGoodsController 本地商品控制器
 * @package App\Http\Controllers\Goods
 */
class LocalGoodsController extends Controller
{
    /**
     * @var Goods 本地商品
     */
    protected $Goods;

    /**
     * @var GoodsLocalPic 本地商品附图
     */
    protected $GoodsLocalPics;

    /**
     * @var Procurements  本地商品采购信息
     */
    protected $Procurement;

    /**
     * @var GoodsDeclare  本地商品申报信息
     */
    protected $GoodsDeclare;

    /**
     * @var Category      本地商品分类
     */
    protected $Category;

    /**
     * @var int 商品审核状态：草稿
     */
    const GoodsStatusPendingReview = 1;

    /**
     * @var int 商品审核状态：已通过审核
     */
    const GoodsStatusPassed = 2;

    /**
     * @var int 商城同步平台：亚马逊
     */
    const PlatformAmazon = 1;

    /**
     * @var int 商城同步平台：乐天
     */
    const PlatformRakuten = 2;

    /**
     * @var int 商品同步状态：草稿箱、未同步
     */
    const SynchronizeStatusInBox = 1;

    /**
     * @var int 商品同步状态：成功
     */
    const SynchronizeStatusSuccess = 2;

    public function __construct()
    {
        $this->Goods = new Goods();
        $this->GoodsLocalPics = new GoodsLocalPic();
        $this->Procurement = new Procurements();
        $this->GoodsDeclare = new GoodsDeclare();
        $this->Category = new Category();
        $this->WareHouse = new WarehouseGoods();
    }

    /**
     * @description 本地商品首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author zt7927
     * @date 2019/4/23 16:03
     */
    public function localIndex()
    {
        //侧边栏
//        $responseData['shortcutMenus'] = Goods::getGoodsShortcutMenu();
        //商品
        $goods = $this->Goods->all();
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
        $settingWareHouse = SettingWarehouse::where(['user_id' => $user_id, 'type' => SettingWarehouse::SM_TYPE, 'disable' => SettingWarehouse::ON])->first();
        if ($settingWareHouse) {
            $switch = $settingWareHouse->toArray();
        }
        $responseData['is_sucess'] = !empty($switch) ? $switch : '';
        return view('Goods.GoodsManage.LocalGoods.index', compact('goods'))->with($responseData);
    }

    /**
     * @note
     * 查看(同步仓库商品)
     * @return: array
     * @author: zt7837
     * @since: 2019/6/5
     */
    public function syncGoodsDetail(Request $request)
    {
        $id = $request->input();
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        WarehouseGoods::where(['id' => $id, 'user_id' => $user_id])->first();
        return view('goods.GoodsManage.LocalGoods.addGoods');
    }

    /**
     * @description 本地商品搜索
     * @param Request $request
     * @return array
     * @author zt7927
     * @date 2019/4/23 16:52
     */
    public function LocalGoodsSearch(Request $request)
    {
        $pageIndex = $request->get('page', 1);
        $pageSize = $request->get('limit', 20);
        $params = $request->get('data', []);
        $syn = $request->get('sync') ?? '';

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
        $collection = $this->Goods->search($params, $syn);
        $count = $collection->count();
        $data = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->orderBy('created_at', 'desc')->get()->toArray();

        $res = array(
            'code' => '0',
            'msg' => '',
            'count' => $count,
            'data' => $data
        );

        return $res;
    }

    /**
     * @description 添加商品页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author zt7927
     * @date 2019/4/23 18:00
     */
    public function addGoodsIndex(Request $request)
    {
        $goodsId = $request->input('id');
        $currentUser = CurrentUser::getCurrentUser();
        $goods_detail = '';
        $categoryArr = [];
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if ($goodsId) {
            $goods = Goods::with('declares', 'pictures')
                ->with(['procurement'=>function($query) use($user_id) {$query->where('user_id',$user_id);}])
                ->where(['id' => $goodsId, 'user_id' => $user_id])
                ->first();
            if (!$goods) {
                abort(404);
            }
            //产品分类
            if ($goods->category_id_1 && $goods->category_id_2 && $goods->category_id_3) {
                $id1 = $goods->category_id_1;
                $id2 = $goods->category_id_2;
                $id3 = $goods->category_id_3;
                $category_one = Category::where(['id' => $id1])->select('name')->first();
                $category_two = Category::where(['id' => $id2])->select('name')->first();
                $category_three = Category::where(['id' => $id3])->select('name')->first();
                if ($category_one && $category_two && $category_three) {
                    $category = $category_one->name . '>' . $category_two->name . '>' . $category_three->name;
                    $goods->category_str = $category;
                }

                $categoryArr['first'] = Category::where(['parent_id'=>0,'user_id'=>$user_id])->where('type', 0)->get()->toArray();
                $categoryArr['second'] = Category::where('parent_id', $goods->category_id_1)->get()->toArray();
                $categoryArr['third'] = Category::where('parent_id', $goods->category_id_2)->get()->toArray();
            }
            $goods_detail = $goods->toArray();
        }
        //商品属性
        $goods_attrs = new GoodsAttribute();
        $goods_attrs = $goods_attrs->getAllAttrs();
        //申报价格
        $currency = new SettingCurrencyExchange();
        $currency = $currency->getAllCurrency($user_id);
        //首选供应商
        $suppliers = new Suppliers();
        $suppliers = $suppliers->getAllSuppliers($user_id);
        return view('Goods.GoodsManage.LocalGoods.addGoods', compact('goods_attrs', 'suppliers', 'currency', 'goods_detail', 'categoryArr'));
    }

    /**
     * @note
     * 同步仓库编辑
     * @return: array
     * @author: zt7837
     * @since: 2019/6/4
     */
    public function editbGoods(Request $request)
    {
        $goodsId = $request->input('id');
        $select = '';
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (empty($goodsId) || !isset($goodsId)) {
            abort(404);
        }
        $goods = Goods::with('warehouseGoods')->with('declares')
            ->where(['id' => $goodsId, 'user_id' => $user_id])
            ->first();
        $goods_detail = $goods ? $goods->toArray() : '';
        $currency = new SettingCurrencyExchange();
        //仓库分类
        if ($goods_detail) {
            $cat1_id = $goods_detail['warehouse_goods']['warehouse_category1'];
            $cat2_id = $goods_detail['warehouse_goods']['warehouse_category2'];
            $cat3_id = $goods_detail['warehouse_goods']['warehouse_category3'];
            $select = $this->Category->getSelect($cat1_id, $cat2_id, $cat3_id, $user_id);
        }
        $currency = $currency->getAllCurrency($user_id)->toArray();
        return view('Goods.GoodsManage.LocalGoods.editGoods', compact('currency', 'goods_detail', 'select'));
    }

    /**
     * @note
     * 同步查看
     * @return: array
     * @author: zt7837
     * @since: 2019/6/11
     */
    public function synDetail(Request $request)
    {
        $id = $request->input('id');
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (empty($id) || !isset($id)) {
            abort(404);
        }
        $currency= new SettingCurrencyExchange();
        $currency= $currency->getAllCurrency($user_id);
        $goods   = Goods::with('warehouseGoods','warehouseGoods.category1','warehouseGoods.category2','warehouseGoods.category3')
                ->with('declares')
                ->where(['id' => $id, 'user_id' => $user_id])
                ->first();
        $goods_detail = $goods ? $goods->toArray() : '';
        return view('Goods.GoodsManage.LocalGoods.synDetail', compact('goods_detail', 'currency'));
    }

    /**
     * @note
     * 三级联动
     * @return: array
     * @author: zt7837
     * @since: 2019/6/4
     */
    public function selects(Request $request)
    {
        $pid = $request->input('id');
        $cats = Category::where(['parent_id' => $pid, 'type' => Category::TYPE_WAREHOUSE])
            ->select('id', 'name','category_id')
            ->get();
        $catsArr = !$cats->isEmpty() ? $cats->toArray() : [];
        return Response()->json($catsArr);
    }

    /**
     * @note
     * 商品同步保存
     * @return: array
     * @author: zt7837
     * @since: 2019/6/20
     */
    public function syncGoodsEdit(Request $request)
    {
        $param = $request->get('param', []);
        $responseData['code'] = 0;
        $responseData['msg'] = 'success';
        $currentUser = CurrentUser::getCurrentUser();

        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (empty($param['id']) || !isset($param['id'])) {
            abort(404);
        }
        $goods = Goods::with('declares')->where(['id' => $param['id'], 'user_id' => $user_id])->first();
        if (!$goods) {
            abort(404);
        }

        $this->validatetbGoods($request);
        $goodsInfo['created_man'] = $currentUser->userId;
        $goodsInfo['user_id'] = $user_id;
        $goodsInfo['goods_id'] = $param['id'];
        $goodsInfo['sku'] = $param['sku'];
        $goodsInfo['currency_id'] = $param['currency_id'] ?? 0;
        $goodsInfo['goods_name'] = $param['goods_name'];
        $goodsInfo['goods_weight'] = floatval($param['goods_weight']);
        $goodsInfo['goods_length'] = floatval($param['goods_length']);
        $goodsInfo['goods_height'] = floatval($param['goods_height']);
        $goodsInfo['goods_width'] = floatval($param['goods_width']);
        $goodsInfo['ch_name'] = $param['ch_name'];
        $goodsInfo['eh_name'] = $param['eh_name'];
        $goodsInfo['price'] = floatval($param['price']);
        $goodsInfo['isset_battery'] = $param['isset_battery'];
        $goodsInfo['bases'] = $param['bases'];
        $goodsInfo['warehouse_category1'] = $param['category1'] ?? 0;
        $goodsInfo['warehouse_category2'] = $param['category2'] ?? 0;
        $goodsInfo['warehouse_category3'] = $param['category3'] ?? 0;
        $res = WarehouseGoods::addWareHouseData($goodsInfo, $param['id']);

        if (!$res) {
            $responseData['msg'] = 'error';
            return $responseData;
        }
        return $responseData;
    }

    /**
     * @note
     * 同步商品
     * @return: array
     * @author: zt7837
     * @since: 2019/6/5
     */
    public function synchroGoods(Request $request)
    {
        $param = $request->get('param', []);
        $responseData['code'] = 0;
        $responseData['msg'] = 'success';
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (empty($param['id']) || !isset($param['id'])) {
            abort(404);
        }
        $goods = Goods::with('declares')->where(['id' => $param['id'], 'user_id' => $user_id])->first();
        if (!$goods) {
            abort(404);
        }

        $this->validatetbGoods($request);
        try {
            $goodsInfo['created_man'] = $currentUser->userId;
            $goodsInfo['user_id'] = $user_id;
            $goodsInfo['goods_id'] = $param['id'];
            $goodsInfo['sku'] = $param['sku'];
            $goodsInfo['currency_id'] = $param['currency_id'] ?? 0;
            $goodsInfo['goods_name'] = $param['goods_name'];
            $goodsInfo['goods_weight'] = floatval($param['goods_weight']);
            $goodsInfo['goods_length'] = floatval($param['goods_length']);
            $goodsInfo['goods_height'] = floatval($param['goods_height']);
            $goodsInfo['goods_width'] = floatval($param['goods_width']);
            $goodsInfo['ch_name'] = $param['ch_name'];
            $goodsInfo['eh_name'] = $param['eh_name'];
            $goodsInfo['price'] = floatval($param['price']);
            $goodsInfo['isset_battery'] = $param['isset_battery'];
            $goodsInfo['bases'] = $param['bases'];
            $goodsInfo['warehouse_category1'] = $param['category1'] ?? 0;
            $goodsInfo['warehouse_category2'] = $param['category2'] ?? 0;
            $goodsInfo['warehouse_category3'] = $param['category3'] ?? 0;
            $res = WarehouseGoods::addWareHouseData($goodsInfo, $param['id']);

            //商品同步接口请求
            $goodsData['goods_id'] = $param['id'];
            $goodsData['created_man'] = $user_id;
            $goodsData['product_sku'] = $param['sku'];
            $goodsData['product_title'] = $param['goods_name'];
            $goodsData['product_weight'] = floatval($param['goods_weight']);
            $goodsData['product_length'] = floatval($param['goods_length']);
            $goodsData['product_height'] = floatval($param['goods_height']);
            $goodsData['product_width'] = floatval($param['goods_width']);
            $goodsData['product_declared_name_zh'] = $param['ch_name'];
            $goodsData['product_declared_name'] = $param['eh_name'];
            $goodsData['product_declared_value'] = floatval($param['price']);
            $goodsData['contain_battery'] = $param['isset_battery'];
            $goodsData['cat_id_level0'] = $param['category1'] ?? 0;
            $goodsData['cat_id_level1'] = $param['category2'] ?? 0;
            $goodsData['cat_id_level2'] = $param['category3'] ?? 0;
            $goodsData['verify'] = 1;
            if (isset($param['bases']) && !empty($param['bases'])) {
                $goodsData['component'] = $param['bases'];
            }

            $common = new Common();
            //查询速贸仓库的秘钥信息
            $account['appKey'] = '';
            $account['appToken'] = '';
            $secret_wh['user_id'] = $user_id;
            $secret_wh['status'] = WarehouseSecretkey::STATUS_ON;
            $secret = WarehouseSecretkey::where($secret_wh)->select('appToken','appKey')->first();
            if($secret) {
                $secret_arr = $secret->toArray();
                $account['appKey'] = $secret_arr['appKey'];
                $account['appToken'] = $secret_arr['appToken'];
            }
            $response = $common->sendWarehouse('createProduct', $goodsData,$account);
            if ($res && ($response['ask'] == 'Success')) {
                //更新同步状态
                $data['sync'] = WarehouseGoods::SYNCING;
                WarehouseGoods::addWareHouseData($data, $param['id']);
                return $responseData;
            }

            $responseData['msg'] = 'fail';
            return $responseData;
        } catch (\Exception $e) {
            Common::mongoLog($e, '商品同步检测', '失败', __FUNCTION__);
            $responseData['msg'] = 'fail';
            return $responseData;
        }

    }

    public function validatetbGoods($request)
    {
        $validateArr =
            [
                'param.sku' => 'required|max:50',
                'param.category1' => 'required',
                'param.category2' => 'required',
                'param.category3' => 'required',
                'param.goods_name' => 'required|max:100',
                'param.goods_weight' => 'required|numeric',
                'param.goods_length' => 'required|numeric',
                'param.goods_width' => 'required|numeric',
                'param.goods_height' => 'required|numeric',
                'param.ch_name' => 'required|max:100',
                'param.eh_name' => 'required|max:100',
                'param.price' => 'required|numeric',
                'param.bases' => 'nullable'
            ];
        $rulesArr =
            [
                'required' => ':attribute 为必填项',
                'max' => ':attribute 超出最大值限制',
                'min' => ':attribute 超出最小值限制',
                'unique' => ':attribute 已经存在',
            ];
        $noteArr =
            [
                'param.sku' => '商品sku',
                'param.goods_name' => '产品名称',
                'param.goods_weight' => '商品重量',
                'param.goods_width' => '产品尺寸 宽',
                'param.goods_length' => '产品尺寸 长',
                'param.goods_height' => '产品尺寸 高',
                'param.ch_name' => '申报中文名',
                'param.eh_name' => '申报英文名',
                'param.price' => '申报价格',
                'param.category1' => '一级分类',
                'param.category2' => '二级分类',
                'param.category3' => '三级分类',
                'param.bases' => '主要成分'

            ];
        $this->validate($request, $validateArr, $rulesArr, $noteArr);
    }

    /**
     * @note
     * 商品列表 同步操作
     * @return: array
     * @author: zt7837
     * @since: 2019/6/5
     */
    public function syncGoodsList(Request $request)
    {
        $id = $request->input('id');
        $currentUser = CurrentUser::getCurrentUser();
        $responseData['code'] = 0;
        $responseData['msg'] = 'fail';
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $obj = WarehouseGoods::where('goods_id', $id)->first();
        if (!$obj) {
            abort(404);
        }
        $goods = $obj->toArray();
        //已同步跳过
        if ($goods['sync'] == 1) {
            return [
                'code' => 0,
                'msg' => 'fail'
            ];
        }
        $goodsInfo['goods_id'] = $goods['id'];
        $goodsInfo['created_man'] = $user_id;
        $goodsInfo['product_sku'] = $goods['sku'];//SKU Int
        $goodsInfo['product_title'] = $goods['goods_name'];//产品标题
        $goodsInfo['product_weight'] = floatval($goods['goods_weight']);//重量，单位KG
        $goodsInfo['product_length'] = floatval($goods['goods_length']);//长，单位CM
        $goodsInfo['product_height'] = floatval($goods['goods_height']);//宽，单位CM
        $goodsInfo['product_width'] = floatval($goods['goods_width']);//高，单位CM
        $goodsInfo['product_declared_name_zh'] = $goods['ch_name'];//申报名称 (中文)
        $goodsInfo['product_declared_name'] = $goods['eh_name'];//申报名称 (英文)
        $goodsInfo['product_declared_value'] = floatval($goods['price']);//申报价值，币种为USD
        $goodsInfo['contain_battery'] = $goods['isset_battery'];//是否含电池，0不含，1含电池
        $goodsInfo['cat_id_level0'] = $goods['warehouse_category1'] ?? 0;
        $goodsInfo['cat_id_level1'] = $goods['warehouse_category2'] ?? 0;
        $goodsInfo['cat_id_level2'] = $goods['warehouse_category3'] ?? 0;
        $goodsInfo['verify'] = 1;
        if (isset($goods['bases']) && !empty($goods['bases'])) {
            $goodsInfo['component'] = $goods['bases'];
        }
        //参数校验
        $checkInfo = $this->checkSyncData($goodsInfo);
        if (empty($checkInfo ['checkStatus'])) {
            return [
                'code' => 0,
                'msg' => $checkInfo ['msg']
            ];
        }
        $common = new Common();
        $account['appKey'] = '';
        $account['appToken'] = '';
        //查询速贸仓库的秘钥信息
        $secret_wh['user_id'] = $user_id;
        $secret_wh['status'] = WarehouseSecretkey::STATUS_ON;
        $secret = WarehouseSecretkey::where($secret_wh)->select('appToken','appKey')->first();
        if($secret) {
            $secret_arr = $secret->toArray();
            $account['appKey'] = $secret_arr['appKey'];
            $account['appToken'] = $secret_arr['appToken'];
        }
        $response = $common->sendWarehouse('createProduct', $goodsInfo, $account);
        if (($response['ask'] == 'Success')) {
            //更新同步状态
            $data['sync'] = WarehouseGoods::SYNCING;
            WarehouseGoods::addWareHouseData($data, $id);
            $responseData['msg'] = 'success';
            $responseData['code'] = 1;
            return $responseData;
        } else if ($response['ask'] == 'Failure') {
            if (isset($response ['errTip'])) {
                $errTip = implode('<br/>', $response ['errTip']);
                return [
                    'code' => 0,
                    'msg' => '仓库接口响应信息 : ' . $errTip
                ];
            }

        }
        return $responseData;
    }

    /**
     * todo 需要知道验证规则
     * @desc 认领商品的验证
     * @param $request
     * @param $request
     * @author zt6650
     * CreateTime: 2019-04-11 15:11
     */
    public function validateGoods($request, $id)
    {
        //取出所有的本地商品的分类id
//        $goods_attrs = GoodsAttribute::getAllAttrs();
        $goods_attrs = $this->Category->pluck('id')->toArray();
        $validateArr =
            [
                'param.sku' => 'required|max:50',
                'param.firstCategory' => ['required', Rule::in($goods_attrs),],
                'param.secondCategory' => ['required', Rule::in($goods_attrs),],
                'param.thirdCategory' => ['required', Rule::in($goods_attrs),],
                'param.goods_name' => 'required|max:100',
                'param.goods_attribute_id' => 'required',
                'param.goods_weight' => 'required|numeric',
                'param.goods_height' => 'required|numeric',
                'param.goods_width' => 'required|numeric',
                'param.goods_length' => 'required|numeric',
                'param.goods_title' => 'required|max:500',
                'param.description' => 'required|max:1000',
                'param.currency_id' => 'required|numeric',
                'param.ch_name' => 'required|max:100',
                'param.eh_name' => 'required|max:100',
                'param.price' => 'required|numeric',
                'param.goods_brand' => 'required|nullable|max:100',
                'param.manufacturers' => 'required|nullable|max:100',
                'param.custom_code' => 'required|nullable|max:20',
                'param.specifications' => 'nullable|max:100',
                'param.company' => 'nullable|max:20',
                //            'param.file' => 'nullable|max:400',
                'param.preferred_supplier_id' => 'nullable|numeric|max:100',
                'param.preferred_price' => 'required|numeric',
                'param.preferred_url' => 'nullable',
                'param.alternative_supplier_id' => 'nullable|numeric|max:100',
                'param.alternative_price' => 'nullable|numeric',
                'param.alternative_url' => 'nullable',
                'img_import' => 'nullable|max:500',
                'img_sup' => 'nullable|max:800',
            ];
        $rulesArr =
            [
                'required' => ':attribute 为必填项',
                'max' => ':attribute 超出最大值限制',
                'min' => ':attribute 超出最小值限制',
                'unique' => ':attribute 已经存在',
            ];
        $noteArr =
            [
                'param.firstCategory' => '一级分类',
                'param.secondCategory' => '二级分类',
                'param.thirdCategory' => '三级分类',
                'param.sku' => '自定义sku',
                'param.goods_name' => '产品名称',
                'param.goods_attribute_id' => '产品属性',
                'param.goods_weight' => '产品重量',
                'param.goods_height' => '产品高',
                'param.goods_width' => '产品宽',
                'param.goods_length' => '产品长',
                'param.ch_name' => '申报中文名',
                'param.eh_name' => '申报英文名',
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
        ];
        if ($id) {
            $validateArr['param.sku'] = 'required|max:50';
        }
        $this->validate($request, $validateArr, $rulesArr, $noteArr);
    }

    /**
     * @param $data
     * Note: 校验仓库接口数据
     * Data: 2019/6/24 14:15
     * Author: zt7785
     */
    public function checkSyncData($data)
    {
        $requieParam = ['product_sku' => '商品SKU', 'goods_name' => '产品名称', 'product_weight' => '重量', 'product_length' => '长度', 'product_width' => '宽度', 'product_height' => '高度', 'contain_battery' => '是否含电池', 'product_declared_value' => '申报价值', 'product_declared_name' => '申报名称(英文)', 'product_declared_name_zh' => '申报名称(中文)', 'cat_id_level0' => '一级品类', 'cat_id_level1' => '二级品类', 'cat_id_level2' => '三级品类'];
        $response ['checkStatus'] = true;
        $response ['msg'] = '';
        foreach ($requieParam as $key => $value) {
            if (isset($data [$key])) {
                if (is_numeric($data [$key]) && ($data [$key] == 0.00 || $data [$key] == 0)) {
                    $response ['checkStatus'] = false;
                    if (in_array($key, ['cat_id_level0', 'cat_id_level1', 'cat_id_level2'])) {
                        $response ['msg'] = $value . '不能空';
                        break;
                    } else {
                        if ($key != 'contain_battery') {
                            $response ['msg'] = $value . '不能为0或0.00';
                            break;
                        }
                    }

                }
                if (is_string($data [$key]) && $data [$key] == '') {
                    $response ['checkStatus'] = false;
                    $response ['msg'] = $value . '不能为空';
                    break;
                }
                $response ['checkStatus'] = true;
            }
        }
        return $response;
    }

    /**
     * @description 新增本地商品
     * @param Request $request
     * @return array
     * @author zt7927
     * @date 2019/4/24 14:16
     */
    public function addGoods(Request $request)
    {
        $param = $request->get('param', []);
        $img_sup = $request->get('img_up', []);
        $id = $request->input('param.id');
        $id = $id ? $id : 0;
        $this->validateGoods($request, $id);  //验证字段

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if ($id) {
            $goodsModel = Goods::where(['user_id' => $user_id, 'id' => $id])->first();
            if (!$goodsModel) {
                return response()->json(['status' => false, 'msg' => '编辑异常']);
            }
        }

        if (!$id) {
            //本地sku校验 不同的子账号
            $sku = $this->Goods->where(['user_id' => $user_id])->select('sku')->get();
            if (!$sku->isEmpty()) {
                $sku = $sku->toArray();
                $sku_arr = array_column($sku, 'sku');
                $key = array_search($param['sku'], $sku_arr);
                if ($key) {
                    return [
                        'status' => false,
                        'msg' => '自定义SKU已存在'
                    ];
                }
            }
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
//            $goodsInfo['synchronization'] = $param['firstCategory'];
            $goodsInfo['status'] = Goods::STATUS_DRAFT; //草稿状态
            $goodsInfo['goods_height'] = $param['goods_height'];
            $goodsInfo['goods_width'] = $param['goods_width'];
            $goodsInfo['goods_length'] = $param['goods_length'];
            $goodsInfo['goods_weight'] = $param['goods_weight'];
            $goodsInfo['description'] = $param['description'];
            $goodsInfo['goods_title'] = $param['goods_title'];
            $goodsInfo['created_man'] = $currentUser->userId;
            $goodsInfo['user_id'] = $user_id;
            $goodsInfo['goods_pictures'] = isset($param['goods_pictures']) ? $param['goods_pictures'] : '';
            $goodsObject = $this->Goods->insertArr($goodsInfo, $id);
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
            $declare['created_man'] = $currentUser->userId;
            $declare['user_id'] = $user_id;
            $declare['goods_id'] = $goodsId;
            $this->GoodsDeclare->addGetId($declare, $id, $user_id);

            //采购信息
            $procurement['preferred_supplier_id'] = $param['preferred_supplier_id'];
            $procurement['preferred_price'] = $param['preferred_price'];
            $procurement['preferred_url'] = $param['preferred_url'];
            $procurement['alternative_supplier_id'] = $param['alternative_supplier_id'];
            $procurement['alternative_price'] = $param['alternative_price'];
            $procurement['alternative_url'] = $param['alternative_url'];
            $procurement['goods_id'] = $goodsId;
            $procurement['created_man'] = $currentUser->userId;
            $procurement['user_id'] = $user_id;
            $this->Procurement->addGetId($procurement, $id, $user_id);

            //存入商品的图片
            $pic['user_id'] = $user_id;
            $pic['created_man'] = $currentUser->userId;
            $this->GoodsLocalPics->deleteImages($id, $user_id);
            foreach ($img_sup as $value) {
                $pic['goods_id'] = $goodsId;
                $pic['link'] = $value;
                $this->GoodsLocalPics->insertArr($pic);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
//            info($e);
            if ($id) {
                return [
                    'status' => false,
                    'msg' => '编辑失败!'
                ];
            } else {
                return [
                    'status' => false,
                    'msg' => '添加失败!'
                ];
            }
        }
        if ($id) {
            return [
                'status' => true,
                'msg' => '编辑成功!'
            ];
        } else {
            return [
                'status' => true,
                'msg' => '添加成功!'
            ];
        }
    }

    /**
     * @note
     * 本地商品审核
     * @return: array
     * @author: zt7837
     * @since: 2019/5/27
     */
    public function localGoodsCheck(Request $request)
    {
        $ids = $request->input('id');
        $goodsIds = explode(',',$ids);
        $responseData = ['code' => 0, 'msg' => 'fail', 'data' => ''];
        if (empty($goodsIds)) {
            return $responseData;
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $res = $this->Goods->checkStatus($goodsIds,$user_id,$currentUser->userId );
        if ($res ['code'] == 200) {
            $responseData['msg'] = 'success';
            return $responseData;
        }
        return $responseData;
    }

    /**
     * @note
     * 编辑页面的商品审核 审核的同时保存数据
     * @since: 2019/7/3
     * @author: zt7837
     * @return: array
     */
    public function localGoodsCheckOne(Request $request)
    {
        $data = $request->get('param', []);
        $id = $data['id'];
        $img_sup = $request->get('img_up', []);
        $responseData = ['code' => 0, 'msg' => '审核成功', 'data' => ''];
        if (empty($id)) {
            $responseData['code'] = -1;
            $responseData['msg'] = '审核失败';
            return $responseData;
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //验证字段
        $this->validateGoods($request, $id);
        $goodsModel = Goods::where(['user_id' => $user_id, 'id' => $id])->first();
        if (!$goodsModel) {
            return response()->json(['code' => -1, 'msg' => '审核异常']);
        }
        //保存数据
//        $this->checkInsertData($id,$user_id,$data,$img_sup);
        DB::beginTransaction();
        try {
            //商品主要信息
            $goodsInfo['goods_attribute_id'] = $data['goods_attribute_id'];
            $goodsInfo['category_id_1'] = $data['firstCategory'];
            $goodsInfo['category_id_2'] = $data['secondCategory'];
            $goodsInfo['category_id_3'] = $data['thirdCategory'];
            $goodsInfo['sku'] = $data['sku'];
            $goodsInfo['goods_name'] = $data['goods_name'];
//            $goodsInfo['synchronization'] = $data['firstCategory'];
            $goodsInfo['status'] = Goods::STATUS_DRAFT; //草稿状态
            $goodsInfo['goods_height'] = $data['goods_height'];
            $goodsInfo['goods_width'] = $data['goods_width'];
            $goodsInfo['goods_length'] = $data['goods_length'];
            $goodsInfo['goods_weight'] = $data['goods_weight'];
            $goodsInfo['description'] = $data['description'];
            $goodsInfo['goods_title'] = $data['goods_title'];
            $goodsInfo['created_man'] = $currentUser->userId;
            $goodsInfo['user_id'] = $user_id;
            $goodsInfo['goods_pictures'] = isset($data['goods_pictures']) ? $data['goods_pictures'] : '';
            $goodsObject = $this->Goods->insertArr($goodsInfo, $id);
            $goodsId = $goodsObject->id;

            //先存商品的本地信息-》商品的申报信息-》商品图片
            $declare['currency_id'] = $data['currency_id'];
            $declare['ch_name'] = $data['ch_name'];
            $declare['eh_name'] = $data['eh_name'];
            $declare['price'] = $data['price'];
            $declare['goods_brand'] = $data['goods_brand'];
            $declare['manufacturers'] = $data['manufacturers'];
            $declare['custom_code'] = $data['custom_code'];
            $declare['specifications'] = $data['specifications'];
            $declare['company'] = $data['company'];
            $declare['created_man'] = $currentUser->userId;
            $declare['user_id'] = $user_id;
            $declare['goods_id'] = $goodsId;
            $this->GoodsDeclare->addGetId($declare, $id, $user_id);

            //采购信息
            $procurement['preferred_supplier_id'] = $data['preferred_supplier_id'];
            $procurement['preferred_price'] = $data['preferred_price'];
            $procurement['preferred_url'] = $data['preferred_url'];
            $procurement['alternative_supplier_id'] = $data['alternative_supplier_id'];
            $procurement['alternative_price'] = $data['alternative_price'];
            $procurement['alternative_url'] = $data['alternative_url'];
            $procurement['goods_id'] = $goodsId;
            $procurement['created_man'] = $currentUser->userId;
            $procurement['user_id'] = $user_id;
            $this->Procurement->addGetId($procurement, $id, $user_id);

            //存入商品的图片
            $pic['user_id'] = $user_id;
            $pic['created_man'] = $user_id;
            $this->GoodsLocalPics->deleteImages($id, $user_id);
            foreach ($img_sup as $value) {
                $pic['goods_id'] = $goodsId;
                $pic['link'] = $value;
                $this->GoodsLocalPics->insertArr($pic);
            }

            //审核状态变更
            $res = $this->Goods->checkStatus([$id],$user_id,$currentUser->userId );
            if ($res ['code'] == 201) {
                DB::rollBack();
                $responseData['code'] = -1;
                $responseData['msg'] = '审核异常';
                return $responseData;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $responseData['code'] = -1;
            $responseData['msg'] = '审核异常';
            info($e);
            return $responseData;
        }

        return $responseData;

    }

    public function checkInsertData($id,$user_id,$data,$img_sup) {


    }

    /**
     * @note
     * 本地商品详情
     * @return: array
     * @author: zt7837
     * @since: 2019/5/28
     */
    public function ajaxGetGoodsDetail(Request $request)
    {
        $goodsId = $request->input('id');
        $responseData = ['code' => 0, 'msg' => 'fail'];

        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (!isset($goodsId) || empty($goodsId)) {
            $responseData['msg'] = '参数异常';
            return $responseData;
        }
        $goodsDetail = Goods::where(['id' => $goodsId, 'user_id' => $user_id])->first();
        if (!$goodsDetail) {
            $responseData['msg'] = '参数异常';
            return $responseData;
        }
        $responseData['msg'] = 'success';
        $responseData['data'] = $goodsDetail;
        return $responseData;
    }

    /**
     * @note
     * 本地商品删除
     * @return: bool
     * @author: zt7837
     * @since: 2019/5/28
     */
    public function localGoodsDel(Request $request)
    {
        $goodsId = $request->input('id');
        $responseData = ['code' => 0, 'msg' => 'fail'];
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        if (empty($goodsId) || !isset($goodsId)) {
            abort(404);
        }

        $goodsModel = new Goods();
        $obj = $goodsModel->where(['status' => Goods::STATUS_DRAFT, 'user_id' => $user_id])->find($goodsId);
        if (!$obj) {
            abort(404);
        }
        $re = $obj->delete();
//        $re = Goods::where(['id'=>$goodsId,'status'=>Goods::STATUS_DRAFT,'created_man'=>$user_id])->delete();
        //商品相关数据删除 采购信息 本地商品附图表 goods_local_pics 申报信息 goods_declare
        GoodsDeclare::where(['goods_id' => $goodsId, 'user_id' => $user_id])->delete();
        Procurements::where(['goods_id' => $goodsId, 'user_id' => $user_id])->delete();
        GoodsLocalPic::where(['goods_id' => $goodsId, 'user_id' => $user_id])->delete();
        if ($re) {
            $responseData['msg'] = 'success';
            return $responseData;
        }
        return $responseData;
    }

    /**
     * 导入本地产品
     * Author: zt12779
     * created at: 2019/5/29 13:54:45
     * @param Request $request
     * @param Excel $excel
     * @return \Illuminate\Http\JsonResponse
     */
    public function importProduct(Request $request, Excel $excel)
    {
        try {
            //成功录入数
            $success = 0;
            //错误信息
            $errMsg = [];
            //返回状态
            $result['code'] = -1;

            //获取上传的文件
            $file = $request->file('import');
            if (empty($file)) {
                $result['msg'] = '请选择上传文件';
                return parent::layResponseData($result);
            }
            $originalExtension = $file->getClientOriginalExtension();
            $allowedExtension = ['xls', 'xlsx', 'cvs'];
            if (!in_array($originalExtension, $allowedExtension)) {
                $result['msg'] = '只允许xls(x)和cvs格式的文件上传';
                return parent::layResponseData($result);
            }
            //账户类型
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }

            //获取上传文件的数据
            $originalData = $this->import($excel, $file->getRealPath());
            //提取字段提示
            $headLine = array_shift($originalData);

            $necessaryParams = [
                0 => ['name' => '自定义SKU', 'index' => 'sku', 'status' => 'required'],
                1 => ['name' => '产品名称', 'index' => 'goods_name', 'status' => 'required'],
                2 => ['name' => '产品分类', 'index' => 'category', 'status' => 'required', 'remove' => true],
                3 => ['name' => '产品重量(kg)', 'index' => 'goods_weight', 'status' => 'required'],
                4 => ['name' => '产品长(cm)', 'index' => 'goods_length', 'status' => 'required'],
                5 => ['name' => '产品宽(cm)', 'index' => 'goods_width', 'status' => 'required'],
                6 => ['name' => '产品高(cm)', 'index' => 'goods_height', 'status' => 'required'],
                7 => ['name' => '产品属性', 'index' => 'property', 'status' => 'required', 'remove' => true],
                8 => ['name' => '产品标题', 'index' => 'goods_title', 'status' => 'required'],
                9 => ['name' => '产品描述', 'index' => 'description', 'status' => 'required'],
                10 => ['name' => '申报中文名', 'index' => 'ch_name', 'status' => 'required'],
                11 => ['name' => '申报英文名', 'index' => 'eh_name', 'status' => 'required'],
                12 => ['name' => '申报币种', 'index' => 'currency_id', 'status' => 'required'],
                13 => ['name' => '申报价格', 'index' => 'price', 'status' => 'required'],
                14 => ['name' => '产品品牌', 'index' => 'goods_brand', 'status' => 'required'],
                15 => ['name' => '制造商', 'index' => 'manufacturers', 'status' => 'required'],
                16 => ['name' => '海关编码', 'index' => 'custom_code', 'status' => 'required'],
                17 => ['name' => '型号规格', 'index' => 'specifications', 'status' => 'null'],
                18 => ['name' => '申报单位', 'index' => 'company', 'status' => 'null'],
                19 => ['name' => '首选供应商', 'index' => 'supplement', 'status' => 'required', 'remove' => true],
                20 => ['name' => '采购价1', 'index' => 'preferred_price', 'status' => 'null'],
                21 => ['name' => '采购链接1', 'index' => 'preferred_url', 'status' => 'null'],
                22 => ['name' => '备选供应商', 'index' => 'alternative_supplier_id', 'status' => 'null'],
                23 => ['name' => '采购价格2', 'index' => 'alternative_price', 'status' => 'null'],
                24 => ['name' => '采购链接2', 'index' => 'alternative_url', 'status' => 'null'],
                25 => ['name' => '产品状态', 'index' => 'status', 'status' => 'null'],
            ];
            $negativeNotAllowed = [3, 4, 5, 6, 13, 20, 23];
            //可以为空的参数
            $skipParamsIndex = [17, 18, 20, 21, 22, 23, 24, 25];
            //需要删除的参数
            $shouldBeRemoved = array_column($necessaryParams, 'remove', 'index');
            //获取用户下所有的商品SKU
            $allGoods = Goods::where('user_id', $user_id)->get()->toArray();
            $allGoodsInSKU = array_column($allGoods, 'sku');

            $goodsData = [];
            $declareData = [];
            $procurement = [];

            DB::beginTransaction();

            foreach ($originalData as $originalKey => $originalRow) {
                $row = $originalKey + 1;

                //排除空行
                $notEmptyBlock = 0;
                foreach ($originalRow as $emptyCheckKey => $singleRow) {
                    $originalRow[$emptyCheckKey] = trim($singleRow);
                    $singleRow = trim($singleRow);
                    if (!empty($singleRow)) {
                        $notEmptyBlock++;
                    }
                }
                if ($notEmptyBlock <= 0) {
                    continue;
                }

                foreach ($necessaryParams as $fieldKey => $fieldValue) {

                    if (!isset($originalRow[$fieldKey]) && !in_array($fieldKey, $skipParamsIndex)) {
                        $errMsg[$row][] = "第{$row}行，缺少必要参数：【{$fieldValue['name']}】";
                        continue;
                    }
                    if (empty(trim($originalRow[$fieldKey])) && !in_array($fieldKey, $skipParamsIndex)) {
                        $errMsg[$row][] = "第{$row}行，缺少必要参数：【{$fieldValue['name']}】";
                        continue;
                    }

                    if ($fieldKey == 0 && in_array($originalRow[$fieldKey], $allGoodsInSKU)) {
                        $errMsg[$row][] = "第{$row}行，自定义SKU已存在：【{$originalRow[$fieldKey]}】";
                        continue;
                    }

                    if (!empty($originalRow[$fieldKey]) && in_array($fieldKey, $negativeNotAllowed)) {
                        if (!is_numeric($originalRow[$fieldKey])) {
                            $errMsg[$row][] = "第{$row}行，不允许的数字：【{$originalRow[$fieldKey]}】";
                            continue;
                        }

                        if ((float)$originalRow[$fieldKey] < 0) {
                            $errMsg[$row][] = "第{$row}行，不允许的数字：【{$originalRow[$fieldKey]}】";
                            continue;
                        }
                    }

                    if ($fieldKey == 2) {
//                        $categoryWhereMap = ['categories.created_man' => $user_id, 'categories.name' => $originalRow[$fieldKey]];
                        $categoryWhereMap = ['categories.name' => $originalRow[$fieldKey], 'categories.type' => 0];
                        $subCategory = Category::where($categoryWhereMap)
                            ->select('categories.id', 'categories.parent_id as pid', 'pc.parent_id as parent_id')
                            ->join('categories as pc', 'pc.id', '=', 'categories.parent_id')
                            ->first();
                        if (!$subCategory) {
                            $errMsg[$row][] = "第{$row}行，不存在的分类：【{$originalRow[$fieldKey]}】";
                            continue;
                        } else {
                            $goodsData['category_id_1'] = $subCategory['parent_id'];
                            $goodsData['category_id_2'] = $subCategory['pid'];
                            $goodsData['category_id_3'] = $subCategory['id'];
                            if ($subCategory['parent_id'] == 0) {
                                $goodsData['category_id_1'] = $subCategory['pid'];
                                $goodsData['category_id_2'] = $subCategory['id'];
                                $goodsData['category_id_3'] = 0;
                            }
                            if ($goodsData['category_id_3'] == 0) {
                                $errMsg[$row][] = "第{$row}行，不存在的分类：【{$originalRow[$fieldKey]}】";
                                continue;
                            }
                        }
                    }

                    if ($fieldKey == 7) {
                        $propertyWhereMap = [
//                            'user_id' => $user_id,
                            'attribute_name' => trim($originalRow[$fieldKey])
                        ];
                        $property = GoodsAttribute::where($propertyWhereMap)->first();
                        if (!$property) {
                            $errMsg[$row][] = "第{$row}行，不存在的属性：【{$originalRow[$fieldKey]}】";
                            continue;
                        } else {
                            $goodsData['goods_attribute_id'] = $property['id'];
                        }
                    }

                    if ($fieldKey >= 10 && $fieldKey <= 18) {
                        if ($fieldValue['status'] == 'required' && empty($originalRow[$fieldKey])) {
                            $errMsg[$row][] = "第{$row}行，缺少必要参数：【{$fieldValue['name']}】";
                            continue;
                        }
                        if ($fieldKey == 12) {
                            $currencyInput = strtoupper($originalRow[$fieldKey]);
                            $currency = SettingCurrencyExchange::Where(function ($query) use ($currencyInput) {
                                    $query->where('currency_form_code', $currencyInput)
                                        ->orWhere('currency_form_name', $currencyInput);
                                })->first();
                            if (!$currency) {
                                $errMsg[$row][] = "第{$row}行，不存在的货币类型：【{$originalRow[$fieldKey]}】";
                                continue;
                            }
                            $declareData[$fieldValue['index']] = $currency['id'];
                        }

                        $fieldKey != 12 && $declareData[$fieldValue['index']] = $originalRow[$fieldKey];
                    }

                    if ($fieldKey == 19) {
//                        $supplierWhereMap = ['user_id' => $user_id, 'name' => $originalRow[$fieldKey]];
                        $supplierWhereMap = ['name' => $originalRow[$fieldKey]];
                        $supplier = Suppliers::where($supplierWhereMap)->first();
                        if (!$supplier) {
                            $errMsg[$row][] = "第{$row}行，不存在的供应商：【{$originalRow[$fieldKey]}】";
                            continue;
                        }
                        $procurement['preferred_supplier_id'] = $supplier['id'];
                    }

                    if ($fieldKey == 22 && !empty(trim($originalRow[$fieldKey]))) {
//                        $supplierWhereMap = ['user_id' => $user_id, 'name' => $originalRow[$fieldKey]];
                        $supplierWhereMap = ['name' => $originalRow[$fieldKey]];
                        $supplier = Suppliers::where($supplierWhereMap)->first();
                        if (!$supplier) {
                            $errMsg[$row][] = "第{$row}行，不存在的备选供应商：【{$fieldValue['name']}】";
                            continue;
                        }
                        $procurement['alternative_supplier_id'] = $supplier['id'];
                    }

                    if ($fieldKey < 10 || $fieldKey == 25) {
                        $goodsData[$fieldValue['index']] = $originalRow[$fieldKey];
                    }

                    if ($fieldKey >= 19 && $fieldKey <= 24) {
                        if (!empty(trim($originalRow[$fieldKey]))) {
                            $procurement[$fieldValue['index']] = $originalRow[$fieldKey];
                        }
                    }
                }


                if (isset($errMsg[$row]) && !empty($errMsg)) {
                    continue;
                }

                $removeParams = function (&$targetParams) use ($shouldBeRemoved) {
                    foreach ($targetParams as $key => $val) {
                        if (isset($shouldBeRemoved[$key]) && $shouldBeRemoved[$key] == true) {
                            unset($targetParams[$key]);
                        }
                    }
                };

                $removeParams($goodsData);
                $removeParams($declareData);
                $removeParams($procurement);

                $goodsData['status'] == '草稿' && $goodsData['status'] = self::GoodsStatusPendingReview;
                $goodsData['status'] == '审核通过' && $goodsData['status'] = self::GoodsStatusPassed;
                empty($goodsData['status']) && $goodsData['status'] = self::GoodsStatusPendingReview;
                $goodsData['created_man'] = $user_id;
                $goodsData['user_id'] = $user_id;
                $goodsData['created_at'] = date('Y-m-d H:i:s');
                $goodsData['updated_at'] = date('Y-m-d H:i:s');
                $goodsId = Goods::insertGetId($goodsData);
                if (!$goodsId) {
                    $errMsg[$row][] = "第{$row}行，插入失败";
                    continue;
                }
                $allGoodsInSKU[] = $goodsData['sku'];

                $declareData['goods_id'] = $goodsId;
                $declareData['user_id'] = $user_id;
                $declareData['created_man'] = $user_id;
                $declareData['created_at'] = date('Y-m-d H:i:s');
                $declareData['updated_at'] = date('Y-m-d H:i:s');
                $declareId = GoodsDeclare::insertGetId($declareData);

                if (!isset($procurement['preferred_price'])) {
                    $procurement['preferred_price'] = 0.00;
                } elseif (empty(trim($procurement['preferred_price']))) {
                    $procurement['preferred_price'] = 0.00;
                }
                $procurement['goods_id'] = $goodsId;
                $procurement['created_man'] = $user_id;
                $procurement['user_id'] = $user_id;
                $procurement['created_at'] = date('Y-m-d H:i:s');
                $procurement['updated_at'] = date('Y-m-d H:i:s');
                $procurementId = Procurements::insertGetId($procurement);

                if (!$declareId && !$procurementId) {
                    $errMsg[$row][] = "第{$row}行，插入失败";
                    continue;
                }
                $success++;
                $procurement = [];
            }

            DB::commit();
            $err = [];
            foreach ($errMsg as $key => $val) {
                $err[] = implode('<br>', $val);
            }
            $err = implode('<br>', $err);
            $result['code'] = 0;
            $result['msg'] = "成功录入{$success}条<br>" . $err;
            return $this->layResponseData($result);
        } catch (\Exception $e) {
            $result['code'] = -1;
            $result['msg'] = "服务器正在开小差，请稍后重试";
            return $this->layResponseData($result);
        }

    }

    /**
     * 本地商品导出
     * Author: ZT12779
     * Created at: 2019/05/30
     * @param Request $request
     * @param Excel $excel
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exportLocalGoods(Request $request, Excel $excel)
    {
        $ids = $request->get('ids');
        $idToArray = explode(',', $ids);
        $check = $request->get('check', 0);
        if (empty($idToArray)) {
            if ($check) {
                return AjaxResponse::isFailure('请求参数异常');
            } else {
                abort(404);
            }
        }

        //账户类型
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            if ($check) {
                return AjaxResponse::isFailure('用户信息过期,请重新登录');
            } else {
                abort(404);
            }
        }

        $orderController = new OrderController();
        //可能没申报信息 和采购信息
//        $goods = Goods::whereIn('goods.id', $idToArray)
//            ->join('goods_attribute', 'goods_attribute.id', '=', 'goods.goods_attribute_id')
//            ->join('goods_declare', 'goods_declare.goods_id', '=', 'goods.id')
//            ->join('procurement', 'procurement.goods_id', '=', 'goods.id')
//                //2019年6月25日20:42:31 币种联表异常
//            ->join('setting_currency_exchange as currency', 'currency.id', '=', 'goods_declare.currency_id')
//            ->select('goods.*', 'goods_attribute.attribute_name', 'goods_declare.*', 'goods_declare.price as declare_price',
//                'procurement.*', 'currency.currency_form_name')
//            ->get()->toArray();
        $goods = Goods::getLocalExportGoods($idToArray);
        if (!$goods) {
            if ($check) {
                return AjaxResponse::isFailure('无导出商品数据');
            } else {
                abort(404);
            }
        } else {
            if ($check) {
                return AjaxResponse::isSuccess('正在导出请稍候');
            }
        }

        $setGoodsStatus = function ($status) {
            if ($status == 1) {
                return '草稿';
            }
            if ($status == 2) {
                return '审核通过';
            }
        };

        $currency = SettingCurrencyExchange::all()->toArray();
        $currencyInArray = array_column($currency, 'currency_form_name', 'id');

        $i = 0;
        $printInfo = [];
        $categoryModel = new Category();
        $supplierModel = new Suppliers();
        foreach ($orderController->exportYield($goods) as $key => $goodsValue) {
            if (empty($goodsValue)) {
                continue;
            }

            $printInfo[$i][] = $goodsValue['sku'];
            $printInfo[$i][] = $goodsValue['goods_name'];
            if ($goodsValue['category_id_3'] == 0) {
                $category = $categoryModel->getCategoriesById($goodsValue['category_id_2']);
                $printInfo[$i][] = $category['name'];
            } else {
                $category = $categoryModel->getCategoriesById($goodsValue['category_id_3']);
                $printInfo[$i][] = $category['name'];
            }
            $printInfo[$i][] = $goodsValue['goods_weight'];
            $printInfo[$i][] = $goodsValue['goods_length'];
            $printInfo[$i][] = $goodsValue['goods_width'];
            $printInfo[$i][] = $goodsValue['goods_height'];
            $printInfo[$i][] = $goodsValue['goods_attribute']['attribute_name'] ?? '';
            $printInfo[$i][] = $goodsValue['goods_title'];
            $printInfo[$i][] = $goodsValue['description'];
            //申报中文名
            $printInfo[$i][] = $goodsValue['declares']['ch_name'] ?? '';
            //申报英文名
            $printInfo[$i][] = $goodsValue['declares']['eh_name'] ?? '';
            //币种
            $printInfo[$i][] = $currencyInArray[$goodsValue['declares']['currency_id']] ?? '';
            //declare_price 申报价格字段错误 price 蔡义 调整异常
//            $printInfo[$i][] = $goodsValue['declares']['declare_price'] ?? '';
            $printInfo[$i][] = $goodsValue['declares']['price'] ?? '';
            //商品品牌
            $printInfo[$i][] = $goodsValue['declares']['goods_brand'] ?? '';
            //商品品牌
            $printInfo[$i][] = $goodsValue['declares']['manufacturers'] ?? '';
            //海关编码
            $printInfo[$i][] = $goodsValue['declares']['custom_code'] ?? '';
            //规格型号
            $printInfo[$i][] = $goodsValue['declares']['specifications'] ?? '';
            //申报单位
            $printInfo[$i][] = $goodsValue['declares']['company'] ?? '';
            //供应商信息
            $supplier1 = $supplierModel->getSupplierDataById($goodsValue['procurement']['preferred_supplier_id']);
            $printInfo[$i][] = $supplier1['name'];  //todo 首选供应商
            $printInfo[$i][] = $goodsValue['procurement']['preferred_price'] ?? '';
            $printInfo[$i][] = $goodsValue['procurement']['preferred_url'] ?? '';
            if (!isset($goodsValue['procurement']['alternative_supplier_id'])) {
                $printInfo[$i][] = '';//todo 首选供应商
                $printInfo[$i][] = '';
                $printInfo[$i][] = '';
            } else {
                $supplier2 = $supplierModel->getSupplierDataById($goodsValue['procurement']['alternative_supplier_id']);
                $printInfo[$i][] = $supplier2['name'] ?? '';//todo 首选供应商
                $printInfo[$i][] = $goodsValue['procurement']['alternative_price'] ?? '';
                $printInfo[$i][] = $goodsValue['procurement']['alternative_url'] ?? '';
            }
            $printInfo[$i][] = $setGoodsStatus($goodsValue['status']);
            $i++;
        }
        $arr = ['自定义SKU', '产品名称', '产品分类', '产品重量(kg)', '产品长(cm)', '产品宽(cm)', '产品高(cm)', '产品属性', '产品标题', '产品描述', '申报中文名',
            '申报英文名', '申报币种', '申报价格', '产品品牌', '制造商', '海关编码', '规格型号', '申报单位', '首选供应商', '采购价1',
            '采购链接1', '备选供应商', '采购价2', '采购链接2', '产品状态'];
        array_unshift($printInfo, $arr);
        $this->export($excel, $printInfo, '本地产品详情', false, true);
    }

    /**
     * 同步本地产品至指定的草稿箱
     * Author: zt12779
     * Created at: 2019/06/04
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function synchronizeGoods(Request $request)
    {
        DB::beginTransaction();
        try {
            //同步的平台
            $platform = $request->get('platform');
            //同步的产品数据，仅含ID
            $synchronizeData = $request->get('synchronizeData');
            //同步产品的详细数据，json、id和产品名组合
            $synchronizeGoodsName = $request->get('synchronizeGoodsName');

            //账户类型
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }

            //判断同步的平台
            if (empty($platform)) {
                return $this->layResponseData(['code' => -1, 'msg' => '请选择需要同步的平台']);
            }
            if (empty($platform != self::PlatformAmazon || $platform != self::PlatformRakuten)) {
                return $this->layResponseData(['code' => -1, 'msg' => '不存在的同步平台']);
            }

            //判断同步的产品
            if (empty($synchronizeData) || count($synchronizeData) <= 0) {
                return $this->layResponseData(['code' => -1, 'msg' => '请选择同步的产品']);
            }

            //获取产品详情
            $data = Goods::getGoodsByIdGroup($synchronizeData, $user_id);
            //未审核的不允许提交同步操作
            $checkStatus = array_column($data, 'status');
            if (in_array(Goods::STATUS_DRAFT, $checkStatus)) {
                return $this->layResponseData(['code' => -1, 'msg' => '存在的未审核商品']);
            }
            //对比获取的产品条数
            $dataInId = array_column($data, 'id');
            foreach ($dataInId as $key => $value) {
                if (in_array($value, $synchronizeData)) {
                    continue;
                }
                $msg = "‘{$synchronizeGoodsName[$value]}’的产品不存在，请重新选择数据进行同步";
                return $this->layResponseData(['code' => -1, 'msg' => $msg]);
            }
            //复制商品到相对应的平台
            foreach ($data as $key => $val) {
                $synchronizeParams = [
                    'category_id_1' => $val['category_id_1'],
                    'category_id_2' => $val['category_id_2'],
                    'category_id_3' => $val['category_id_3'],
                    'goods_attribute_id' => $val['goods_attribute_id'],
                    'created_man' => $currentUser->userId,
                    'title' => $val['goods_name'],
                    'goods_id' => $val['id'],
                    'goods_description' => $val['description'],
                    'img_url' => $val['goods_pictures'],
                    'synchronize_status' => self::SynchronizeStatusInBox, //todo 加入枚举
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'goods_weight' => $val['goods_weight'],
                    'goods_length' => $val['goods_length'],
                    'goods_height' => $val['goods_height'],
                    'goods_width' => $val['goods_width'],
                    'local_sku' => $val['sku'],
                    'currency_code' => 'JPY',
                    'user_id' => $user_id,
                    'sku' => $val['sku'],
                    'goods_name' => $val['goods_title']
                ];
                //插入数据
                if ($platform == self::PlatformAmazon) {
                    $synchronizeParams ['seller_sku'] = $val['sku'];
                    $title = $synchronizeParams['title'];
                    $synchronizeParams['title'] = $synchronizeParams['goods_name'];
                    $synchronizeParams['goods_name'] = $title;
                    unset($synchronizeParams ['sku']);
                    $newId = GoodsDraftAmazon::insertGetId($synchronizeParams);
                } else {
                    $newId = GoodsDraftRakuten::insertGetId($synchronizeParams);
                }
                if (!$newId) {
                    DB::rollback();
                    return $this->layResponseData(['code' => -1, 'msg' => '产品同步失败']);
                }

                //插入图片
                $pics = [];
//                foreach ($val['goods_pics'] as $picKey => $picVal) {
                foreach ($val['pictures'] as $picKey => $picVal) {
                    $pics[] = [
                        'goods_id' => $newId,
                        'created_man' => $currentUser->userId,
                        'link' => $picVal['link'],
                        'user_id' => $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
                if ($platform == self::PlatformAmazon) {
                    $picsInsert = GoodsDraftAmazonPics::insert($pics);
                } else {
                    $picsInsert = GoodsDraftRakutenPics::insert($pics);
                }
//            $picsInsert = GoodsDraftRakutenPics::insert($pics);
                if (!$picsInsert) {
                    DB::rollback();
                    return $this->layResponseData(['code' => -1, 'msg' => '产品图片同步失败']);
                }
            }
            DB::commit();
            return $this->layResponseData(['code' => 0, 'msg' => '同步成功']);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->layResponseData(['code' => -1, 'msg' => '同步失败成功:' . $e->getMessage()]);
        }
    }

    /**
     * @note
     * 获取本地分类
     * @return: array
     * @author: zt7837
     * @since: 2019/6/18
     */
    public function getCategory(Request $request)
    {
        $pid = $request->input('parentId');
        $responseData['code'] = 0;
        $responseData['data'] = '';
        $cate = Category::where('parentID', $pid)->where('type', Category::TYPE_LOCAL)->get();
        if ($cate->isEmpty()) {
            $responseData['code'] = -1;
            return Response()->json($responseData);
        }
        $responseData['data'] = $cate->toArray();
        return Response()->json($responseData);
    }
}
