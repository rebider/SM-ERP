<?php

namespace App\Http\Controllers\Goods;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Auth\Models\RolesShops;
use App\Http\Controllers\Order\OrderController;
use App\Http\Requests\CheckIDsRequest;
use App\Http\Services\Goods\RakutenGoodsHandle;
use App\Http\Services\Rakuten\InsertItem;
use App\Models\CategorysAmazon;
use App\Models\CategorysRakuten;
use App\Models\GoodsOnlineAmazon;
use App\Models\GoodsOnlineRakuten;
use App\Models\GoodsOnlineRakutenPics;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingCurrencyExchangeMaintain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;

class RakutenOnlineGoodsController extends Controller
{
    /**
     * @var 商品上架
     */
    const ON_SALE = 1;
    /**
     * @var 商品下架
     */
    const OFF_SALE = 2;
    /**
     * @var 商品更新失败
     */
    const SYNCHRONIZE_FAILED = 3;
    /**
     * @author zt12779
     * @var 商品目录ID
     */
    const ORDER_MENUS_ID = 2;


    /**
     * 首页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $menusModel = new Menus();
        $responseData ['shortcutMenus'] = $menusModel->getShortcutMenu(self::ORDER_MENUS_ID);
        return view("Goods.RakutenOnline.index")->with($responseData);
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

        $data = GoodsOnlineRakuten::getList($params, $user_id);

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
        $data = GoodsOnlineRakuten::getOne($id);
        if (!$data || empty($data)) {
            return '没有该数据';
        }
        $data = $data->toArray();
//        $categoryInArray = explode(',', $data['rakuten_category_id']);
        $data['rakuten_category_JP'] = CategorysRakuten::getCategoryStringInSort($data['rakuten_category_id']);
        $result['goods'] = $data;

        return view('Goods.RakutenOnline.detail')->with($result);
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

        $result = GoodsOnlineRakuten::getExportData($idToArray);

        foreach ($result as $key => $val) {
            if ($val['synchronize_status'] == 1) {
                $result[$key]['synchronize_status'] = '上架';
            }
            if ($val['synchronize_status'] == 2) {
                $result[$key]['synchronize_status'] = '下架';
            }
            if ($val['synchronize_status'] == 3) {
                $result[$key]['synchronize_status'] = '更新失败';
            }
//            $categoryInArray = explode(',', $val['rakuten_category_id']);
            $result[$key]['rakuten_category'] = CategorysRakuten::getCategoryStringInSort($val['rakuten_category_id']);
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
            $printInfo[$i][] = $goodsValue['rakuten_category'];

            $cataId = null;
            $noCataIdReason = [1 => 'セット商品', 2 => 'サービス商品', 3 => '店舗オリジナル商品', 4 => '項目選択肢在庫商品', 5 => '該当製品コードなし'];
            if (!empty($goodsValue['catalogId'])) {
                $cataId = $goodsValue['catalogId'];
            } else {
                $cataId = $noCataIdReason[$goodsValue['catalogIdExemptionReason']];
            }

            $printInfo[$i][] = $cataId;
            $printInfo[$i][] = $goodsValue['cmn'];
            $printInfo[$i][] = $goodsValue['sku'];
            $printInfo[$i][] = $goodsValue['sale_price'];
            $printInfo[$i][] = $goodsValue['currency_code'];
            $printInfo[$i][] = $goodsValue['goods_name'];
            $printInfo[$i][] = $goodsValue['title'];
            $printInfo[$i][] = $goodsValue['goods_description'];
            $printInfo[$i][] = $goodsValue['platform_in_stock'];
            $printInfo[$i][] = $goodsValue['synchronize_status'];
            $i++;

        }
        $arr = ['自定义SKU','店铺','乐天分类', '目录ID（无目录ID原因）' ,'商品管理番号','商品番号','销售价格','销售币种','商品名称','商品标题','商品描述',
            '平台库存','商品状态'];
        array_unshift($printInfo,$arr);
        $this->export($excel, $printInfo, '在线商品详情',false,true);
    }

    /**
     * 编辑在线商品页面
     * Author：zt12779
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPage(Request $request)
    {
        $id = $request->get('id');
        $row = GoodsOnlineRakuten::getOne($id);
        if (!$row) {
            abort(404);
        }
        $result['goodsInfo'] = $row->toArray();

        $result['goodsInfo']['rakuten_category_JP'] = CategorysRakuten::getCategoryStringInSort($row['rakuten_category_id']);
        $result['currency'] = SettingCurrencyExchange::all()->toArray();
//        $categoryNode = CategorysRakuten::where('genreId', $row['rakuten_category_id'])->first();
        if (!empty($row['rakuten_category_id'])) {
            $categoryInArray = explode(',', $row['rakuten_category_id']);
            $result['goodsInfo']['categoryInArray'] = $categoryInArray;
            $category['first'] = CategorysRakuten::where('categories_lv', 1)->get()->toArray();
            $category['second'] = CategorysRakuten::where('parentID', $categoryInArray[0])->get()->toArray();
            $category['third'] = CategorysRakuten::where('parentID', $categoryInArray[1])->get()->toArray();
            $category['four'] = CategorysRakuten::where('parentID', $categoryInArray[2])->get()->toArray();
            $result['category'] = $category;
        }

        return view('Goods.RakutenOnline.edit')->with($result);
    }

    /**
     * 编辑在线商品及上架
     * Author：zt12779
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try {
            $returnData = ['code' => -1, 'msg' => 'failed', 'data' => null];
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

            $allData = $request->all();

            if (empty($allData['id'])) {
                return $this->layResponseData(['code' => -1, 'msg' => '请选择编辑的产品' ]);
            }

            $rules = [
                'rakuten_category_id' => 'required',
                'itemUrl' => 'required',
                'sku' => 'required',
                'sale_price' => 'required',
                'currency_code' => 'required',
                'platform_in_stock' => 'required|int',
                'goods_name' => 'required',
                'goods_title' => 'required',
                'goods_description' => 'required',
//                'img_import' => 'required'
            ];
            $validatorMsg = [
                'rakuten_category_id.required' => '乐天分类ID不允许为空',
                'itemUrl.required' => '商品管理番号不允许为空',
                'sku.required' => '商品番号不允许为空',
                'sale_price.required' => '销售价格不允许为空',
                'currency_code.required' => '币种选择错误',
                'platform_in_stock.required' => '平台库存不允许为空',
                'platform_in_stock.int' => '平台库存不允许为数字以外的字符',
                'goods_name.required' => '商品名称不允许为空',
                'goods_title.required' => '商品标题不允许为空',
                'goods_description.required' => '商品描述',
//                'img_import.required' => '商品主图不允许为空'
            ];
            $validator = Validator::make($allData['param'], $rules, $validatorMsg);
            if ($validator->fails()) {
                $errs = $validator->errors()->first();
                return $this->layResponseData(['code' => -1, 'msg' => $errs ]);
            }

            if (empty($allData['param']['catalogId']) && empty($allData['param']['reason'])) {
                return $this->layResponseData(['code' => -1, 'msg' => '请选择目录ID']);
            }



            $category = CategorysRakuten::where('parentID', $allData['param']['rakuten_category_id'])
                ->orWhere('parentBrowsePathByID', $allData['param']['rakuten_category_id'])->first();
            if (!$category) {
                return $this->layResponseData(['code' => -1, 'msg' => '没有所选的菜单目录']);
            }

            DB::beginTransaction();
            $updateParam = [
                'rakuten_category_id' => $category['parentBrowsePathByID'],
                'sale_price' => $allData['param']['sale_price'],
                'currency_code' => $allData['param']['currency_code'],
                'platform_in_stock' => $allData['param']['platform_in_stock'],
                'title' => $allData['param']['goods_title'],
                'goods_name' => $allData['param']['goods_name'],
                'goods_description' => $allData['param']['goods_description'],
                'img_url' => $allData['param']['img_import'] ?? '',
//                'synchronize_status' =>self::ON_SALE,
                'catalogId' => $allData['param']['catalogId'],
                'catalogIdExemptionReason' => $allData['param']['reason'],
            ];

            $picsResult = true;
            if (!empty($allData['img_sup'])) {
                $updateImgs = [];
                foreach ($allData['img_sup'] as $key => $val) {
                    $updateImgs[] = [
                        'goods_id' => $allData['id'],
                        'created_man' => $user_id,
                        'link' => $val,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
                GoodsOnlineRakutenPics::where('goods_id', $allData['id'])->delete();
                $picsResult = GoodsOnlineRakutenPics::insert($updateImgs);
            }
            $updateResult = GoodsOnlineRakuten::where('id', $allData['id'])->update($updateParam);

            if (!$updateResult && !$picsResult) {
                return $this->layResponseData(['code' => -1, 'msg' => '商品更新失败' ]);
            }
            $item = GoodsOnlineRakuten::where('id', $allData['id'])->with('pictures')->first();
            $onSale = InsertItem::updateTreatmentProcess($item, $user_id);
            if ($onSale['code'] === 1) {
                $returnData['code'] = 1;
                $returnData['msg'] = '更新成功';
//                GoodsOnlineRakuten::where(['id' => $item['id']])->update(['synchronize_status' => self::ON_SALE]);
            } else {
                $returnData['code'] = -1;
                $returnData['msg'] = '更新失败，'.$onSale['msg'] ;
                GoodsOnlineRakuten::where(['id' => $item['id']])->update(['synchronize_status' => self::SYNCHRONIZE_FAILED, 'synchronize_info' => $onSale['msg']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->layResponseData(['code' => 0, 'msg' => $e->getMessage() . $e->getLine() ]);
        }
        DB::commit();
        return $this->layResponseData($returnData);
    }

    /**
     * 获取乐天分类节点
     * Author： zt12779
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategory(Request $request)
    {
        try {
            $parentId = (int)$request->get('parentId');
            $result = CategorysRakuten::getCategoryByParentId($parentId);
            return $this->layResponseData(['code' => 0, 'msg' => '', 'data' => $result]);
        } catch (\Exception $e) {
            return $this->layResponseData(['code' => 0, 'msg' => '服务器睡着了，刷一下~', 'data' => null]);
        }

    }

    /**
     * 乐天商品下架
     * Author： zt8067
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function obtained(CheckIDsRequest $request){
        set_time_limit(0);
        //无视请求断开
        ignore_user_abort();
        $ids = $request->ids;
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
        $results = (new RakutenGoodsHandle())->obtainedProcessing($ids, $user_id);
        return parent::layResponseData($results);


    }
}
