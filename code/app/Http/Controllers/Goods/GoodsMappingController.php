<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/6/3
     * Time: 11:28
     */

    namespace App\Http\Controllers\Goods;

    use App\Auth\Common\AjaxResponse;
    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\Menus;
    use App\Auth\Models\RolesShops;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\CheckIDsRequest;
    use App\Http\Requests\IDNotEmpty;
    use App\Http\Services\Goods\MappingHandle;
    use App\Models\BaseModel;
    use App\Models\Goods;
    use App\Models\GoodsMapping;
    use App\Models\GoodsMappingGoods;
    use App\Models\SettingShops;
    use App\Validates\MappingGoodsValidate;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;

    class GoodsMappingController extends Controller
    {
        public $menusModel = [];

        public function __construct()
        {
            $this->menusModel = new Menus();
        }

        /**
         * @return array
         * Note: 映射首页
         * Data: 2019/06/3 10:35
         * Author: zt8067
         */
        public function mappingIndex()
        {
            //店铺 获取客户 状态正常的店铺
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $param ['user_id'] = $currentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (empty($shopsId)) {
                        $responseData ['shops'] = [];
                    } else {
                        $responseData ['shops'] = SettingShops::whereIn('id',$shopsId)->whereIn('plat_id', [1, 2])->where('recycle',SettingShops::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
                    }
                } else {
                    $responseData ['shops'] = [];
                }
            } else {
                $param ['user_id'] = $currentUser->userId;
                $responseData ['shops'] = SettingShops::where('user_id',$param ['user_id'])->whereIn('plat_id', [1, 2])->where('recycle',SettingShops::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
            }
            $responseData ['sku'] = Goods::where(['user_id'=>$param ['user_id'],'status'=>Goods::STATUS_PASS])->get()->toArray();
            $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::GOODS_MENUS_ID);
            return view('Goods.Mapping.index', $responseData);
        }

        /**
         * @return array
         * Note: 映射列表
         * Data: 2019/06/3 14:35
         * Author: zt8067
         */
        public function mappingLists(Request $request)
        {
            $data = ['code' => 0, 'msg' => '', 'data' => '', 'count' => ''];
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($CurrentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                } else {
                    $shopsId = [];
                }
                $list = MappingHandle::getSummaryByPage($request, $user_id,$shopsId);
            } else {
                $user_id = $CurrentUser->userId;
                $list = MappingHandle::getSummaryByPage($request, $user_id);
            }
            if ($list) {
                $data = [
                    'msg'   => 'Success',
                    'data'  => $list['data'],
                    'count' => $list['total'],
                ];
            } else {
                $data = [
                    'code' => '999',
                    'msg'  => 'Error',
                ];
            }
            return parent::layResponseData($data);
        }

        /**
         * @return array
         * Note: 获取单一商品
         * Data: 2019/06/4 14:00
         * Author: zt8067
         */
        public function getProduct(Request $request)
        {
            $data = ['code' => 0, 'msg' => '', 'data' => '', 'count' => 1];
            $goods_id = $request->get('goods_id');
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            $productM = Goods::where(['user_id' => $user_id, 'id' => $goods_id,'status'=>Goods::STATUS_PASS])->get(['id', 'sku as goods_sku']);
            if ($productM) {
                $product = $productM->toArray();
                $data['data'] = $product;
            } else {
                $data = [
                    'code' => '999',
                    'msg'  => 'Error',
                ];
            }
            return parent::layResponseData($data);
        }

        /**
         * @return array
         * Note: 获取已映射商品列表
         * Data: 2019/06/5 19:10
         * Author: zt8067
         */
        public function getGoodsMapping(IDNotEmpty $request)
        {
            $results = ['code' => 1, 'msg' => 'Error', 'data' => ''];
            $id = $request->get('id');
            $GoodsMappingM = GoodsMapping::with('mapping_goods')->where('id', $id)->first();
            if ($GoodsMappingM) {
                $GoodsMapping = $GoodsMappingM->toArray();
                $results['code'] = 1;
                $results['msd'] = 'Success';
                $results['data'] = $GoodsMapping['mapping_goods'];

            }
            return parent::layResponseData($results);
        }

        /**
         * @return array
         * Note: 商品映射
         * Data: 2019/06/5 15:00
         * Author: zt8067
         */
        public function createProduct(Request $request)
        {
            $results = ['code' => -1, 'msg' => '映射失败', 'data' => ''];
            $params = $request->all();
            $validator = Validator::make(
                $params,
                MappingGoodsValidate::getRules(false, $params['id']),
                MappingGoodsValidate::getMessages(),
                MappingGoodsValidate::getAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            $results = MappingHandle::createProductProcessing($user_id, $params);
            return parent::layResponseData($results);
        }

        /**
         * @return array
         * Note: 商品取消映射
         * Data: 2019/06/5 19:00
         * Author: zt8067
         */
        public function cancelMapping(Request $request)
        {
            $results = ['code' => -1, 'msg' => '取消失败'];
            do {
                try {
                    $id = $request->input('id');
                    $ids = $request->input('ids');
                    $CurrentUser = CurrentUser::getCurrentUser();
                    if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                        //主账号id
                        $user_id = $CurrentUser->userParentId;
                    } else {
                        $user_id = $CurrentUser->userId;
                    }
                    $res = false;
                    if (!empty($id)) {
                        if (!isset($id)) {
                            $results['msg'] = '参数不能为空';
                            break;
                        }
                        if (!is_numeric($id)) {
                            $results['msg'] = '参数错误，必须是整型';
                            break;
                        }
                        $updateOK = GoodsMapping::where(['user_id' => $user_id, 'id' => $id, 'status' => GoodsMapping::MAPPING_YES])->update(['status' => GoodsMapping::MAPPING_ON]);
                        if ($updateOK) {
                            $res = GoodsMappingGoods::where(['goods_mapping_id' => $id])->delete();
                        }
                    } else {
                        if (!isset($ids)) {
                            $results['msg'] = '参数不能为空';
                            break;
                        }
                        $ids = explode(',', $ids);
                        foreach ($ids as $idItem) {
                            if (!is_numeric($idItem)) {
                                $results['msg'] = '参数错误，必须是整型';
                                break 2;
                            }
                            $updateOK = GoodsMapping::where(['user_id' => $user_id, 'id' => $idItem, 'status' => GoodsMapping::MAPPING_YES])->update(['status' => GoodsMapping::MAPPING_ON]);
                            if ($updateOK) {
                                $res = GoodsMappingGoods::where('goods_mapping_id', $idItem)->delete();
                            }
                        }
                    }
                    $results = ['code' => 1, 'msg' => '取消成功'];
                } catch (\Exception $e) {

                }
            } while (0);
            return parent::layResponseData($results);
        }

        /**
         * @return array
         * Note: 删除商品映射
         * Data: 2019/06/5 20:00
         * Author: zt8067
         */
        public function delGoodsMapping(IDNotEmpty $request)
        {
            $results = ['code' => -1, 'msg' => '删除失败','status'=>-1];
            $id = $request->get('id');
            $GoodsMappingGoods = GoodsMappingGoods::where('id', $id)->first(['goods_mapping_id']);
            $GoodsMappingGoodsM = GoodsMappingGoods::where('id', $id)->delete();
            if ($GoodsMappingGoodsM) {
                $check = GoodsMappingGoods::where('goods_mapping_id', $GoodsMappingGoods->goods_mapping_id)->exists();
                if(!$check){
                  GoodsMapping::where('id',$GoodsMappingGoods->goods_mapping_id)->update(['status'=>GoodsMapping::MAPPING_ON]);
                  $results['status'] = 1;
                }
                $results['code'] = 1;
                $results['msg'] = '删除成功';
            }
            return response()->json($results);
        }

        /**
         * @return array
         * Note: 导入商品映射
         * Data: 2019/06/10 14:00
         * Author: zt8067
         */
        public function importGoodsMapping(Request $request)
        {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            $reluts = (new MappingHandle)->updateMapping($request, $user_id);
            return Response()->json($reluts);
        }

        /**
         * @return array
         * Note: 导出列表
         * Data: 2019/06/11 14:00
         * Author: zt8067
         */
        public function exportLists(CheckIDsRequest $request)
        {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            $reluts = (new MappingHandle)->exportProcessing($request, $user_id);
            return Response()->json($reluts);
        }

//        /**  废弃 */
//         * @return array
//         * Note: 导出列表商品映射
//         * Data: 2019/06/11 14:10
//         * Author: zt8067
//         */
//        public function exportMapping(CheckIDsRequest $request)
//        {
//            set_time_limit(0);
//            ini_set('memory_limit', '1024M');
//            $CurrentUser = CurrentUser::getCurrentUser();
//            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
//                //主账号id
//                $user_id = $CurrentUser->userParentId;
//            } else {
//                $user_id = $CurrentUser->userId;
//            }
//            $reluts = (new MappingHandle)->exportMappingProcessing($request, $user_id);
//            return Response()->json($reluts);
//        }
    }