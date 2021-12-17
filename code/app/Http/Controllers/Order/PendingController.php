<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/8
     * Time: 14:23
     */

    namespace App\Http\Controllers\Order;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\Menus;
    use App\Auth\Models\RolesShops;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\ManualDistributionRequest;
    use App\Http\Services\Order\PendingHandle;
    use App\Models\BaseModel;
    use App\Models\Orders;
    use App\Models\OrdersTroublesRecord;
    use App\Models\SettingWarehouse;
    use Illuminate\Http\Request;

    class PendingController extends Controller
    {
        public $menusModel = [];
        protected static $PendingHandle;

        public function __construct()
        {
            $this->menusModel = new Menus();
            self::$PendingHandle = new PendingHandle();
        }

        /**
         * @return array
         * Note: 子菜单
         * Data: 2019/3/11 15:16
         * Author: zt8067
         */
        public function pendingIndex(Request $request)
        {
            $responseData['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::ORDER_MENUS_ID);
            $responseData ['problem'] = $request->get('problem', 0);
            return view('Order.pendingIndex')->with($responseData);
        }

        /**
         * @return array
         * Note: 商品详情页
         * Data: 2019/3/19 15:16
         * Author: zt8067
         */
        public function goodsDescIndex(Request $request)
        {
            $params = $request->all();
            $collection = self::$PendingHandle;
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }

            $OrdersDesc = $collection->getOrdersDesc($params, $user_id);

            $OrdersDesc = $OrdersDesc['data'];
            $WarehouseM = SettingWarehouse::where('user_id', $user_id)->where('disable', SettingWarehouse::ON)->orderBy('type')->get(['id', 'type', 'warehouse_name', 'warehouse_code']);
            if ($WarehouseM->isNotEmpty()) {
                $Warehouse = $WarehouseM->toArray();
                $OrdersDesc['warehouse_type'] = null;
                if(empty($OrdersDesc['warehouse_id'])){
                    $OrdersDesc['warehouse_id'] = 0;
                }
                if(!empty($OrdersDesc['warehouse_id'])){
                    foreach ($Warehouse as $item) {
                        if ($item['id'] == $OrdersDesc['warehouse_id']) {
                            $OrdersDesc['warehouse_type'] = $item['type'];
                        }
                    }
                    $OrdersDesc['logistics_id'] = null;
                }
            }
            return view('Order.goodsDescIndex', compact('OrdersDesc', 'Warehouse'));
        }

        /**
         * @return array
         * Note: 待配货单
         * Data: 2019/3/20 10:16
         * Author: zt8067
         */
        public function lists(Request $request)
        {
            $res = ['code' => 0, 'msg' => '', 'data' => '', 'count' => ''];
            $CurrentUser = CurrentUser::getCurrentUser();
            //匹配状态
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($CurrentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (empty($shopsId)) {
                        //未配置店铺 直接响应空
                        $result ['code'] = 0;
                        $result ['msg'] = '未配置店铺权限';
                        return parent::layResponseData($result);
                    }
                    //店铺id
                    $permissionParam ['source_shop'] = $shopsId;
                    $lists = PendingHandle::getSummaryByPage($request, $user_id,$permissionParam);
                } else {
                    //未配置店铺 直接响应空
                    $result ['code'] = 0;
                    $result ['msg'] = '未配置店铺权限';
                    return parent::layResponseData($result);
                }
            } else {
                $user_id = $CurrentUser->userId;
                $lists = PendingHandle::getSummaryByPage($request, $user_id);
            }
            if ($lists) {
                //剔除有问题的订单
                foreach ($lists['data'] as $k => &$data) {
                    if (!empty($data['orders_troubles_record']) && is_array($data['orders_troubles_record'])) {
                        foreach ($data['orders_troubles_record'] as $troubles_record) {
                            if ($troubles_record['dispose_status'] == OrdersTroublesRecord::STATUS_DISPOSING) {
                                unset($lists['data'][$k]);
                            }
                        }
                    }
                }
                $res = [
                    'msg'   => 'Success',
                    'data'  => $lists['data'],
                    'count' => $lists['total'],
                ];
            } else {
                $res = [
                    'code' => -1,
                    'msg'  => 'Error',
                ];
            }
            return parent::layResponseData($res);
        }

        /**
         * @return array
         * Note: 人工生成配货单
         * Data: 2019/3/27 15:10
         * Author: zt8067
         */
        public function generateDistributionOrder(ManualDistributionRequest $request)
        {
            $params = $request->all();
            //商品数组整合
            if (!empty($params['goods'])) {
                $arr = [];
                foreach ($params['goods'] as $column => $v) {
                    foreach ($v as $kk => $vv) {
                        $arr[$kk][$column] = $vv;
                    }
                }
                unset($params['goods']);
                $params['goods'] = $arr;
            }
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($CurrentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (empty($shopsId)) {
                        //未配置店铺 直接响应空
                        $result ['code'] = -1;
                        $result ['msg'] = '未配置店铺权限';
                        return parent::layResponseData($result);
                    }
                    //店铺id
                    $params ['source_shop'] = $shopsId;
                } else {
                    //未配置店铺 直接响应空
                    $result ['code'] = -1;
                    $result ['msg'] = '未配置店铺权限';
                    return parent::layResponseData($result);
                }
            } else {
                $user_id = $CurrentUser->userId;
            }
            $results = self::$PendingHandle->distributionOrderProcess($params, $user_id);
            return response()->json($results);
        }

        /**
         * @return array
         * Note: 获取仓库
         * Data: 2019/3/21 9:30
         * Author: zt8067
         */
        public function getShippingMethod(Request $request)
        {
            $params = $request->all();
            $weight = $request->total_weight;
            $collection = self::$PendingHandle;
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            $OrdersDesc = $collection->getOrdersDesc($params, $user_id);
            if ($OrdersDesc['code'] != 1) {
                return response()->json($OrdersDesc);
            }
            $params['country'] = $OrdersDesc['data']['country_id'];
            $params['postal_code'] = $OrdersDesc['data']['postal_code'];
            $params['total_weight'] = empty($weight)?$OrdersDesc['data']['total_weight']:$weight;
            $results = $collection->freightTrial($OrdersDesc['data'], $params, $user_id);
            $results['goods'] = $OrdersDesc['data']['orders_products'];
            return response()->json(array_reverse($results));
        }
    }