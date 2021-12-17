<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/3/19
     * Time: 13:21
     */

    namespace App\Http\Controllers\Order;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\Menus;
    use App\Auth\Models\RolesShops;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\IDNotEmpty;
    use App\Http\Requests\ManualDistributionRequest;
    use App\Http\Services\Order\DistrbutionHandle;
    use App\Http\Services\Order\PendingHandle;
    use App\Models\BaseModel;
    use App\Models\OrdersInvoices;
    use App\Models\Platforms;
    use App\Models\SettingShops;
    use App\Models\SettingWarehouse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Excel;

    class DistributionController extends Controller
    {
        public $menusModel = [];
        protected static $DistrbutionHandle;

        public function __construct()
        {
            $this->menusModel = new Menus();
            self::$DistrbutionHandle = new DistrbutionHandle();
        }

        /**
         * @return array
         * Note: 子菜单
         * Data: 2019/3/11 15:16
         * Author: zt8067
         */
        public function distributionIndex()
        {
            //店铺 获取客户 状态正常的店铺
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $param ['user_id'] = $currentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (!empty($shopsId)) {
                        $responseData ['shops'] = SettingShops::getShopsByShopsId($shopsId);
                    } else {
                        $responseData ['shops'] = [];
                    }
                } else {
                    $responseData ['shops'] = [];
                }
            } else {
                $param ['user_id'] = $currentUser->userId;
                $responseData ['shops'] = SettingShops::getShopsByUserId($param ['user_id']);
            }
            $responseData['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::ORDER_MENUS_ID);
            $responseData['platforms'] = Platforms::getAllPlat();//平台
//            $responseData['shops'] = SettingShops::where(['user_id'=>$user_id])->get()->toArray();//店铺
            return view('Order.distributionIndex')->with($responseData);
        }



        /**
         * @return array
         * Note: 获取商店
         * Data: 2019/3/20 15:00
         * Author: zt8067
         */
        public function getShops(IDNotEmpty $request)
        {
            $data = $request->all();
            //店铺 获取客户 状态正常的店铺
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $param ['user_id'] = $currentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (!empty($shopsId)) {
                        if (empty($data['plat_id'])){
                            $responseData ['shops'] = SettingShops::whereIn('id',$shopsId)->where('recycle',SettingShops::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
                        }else{
                            $responseData ['shops'] = SettingShops::whereIn('id',$shopsId)->where(['plat_id'=>$data['plat_id']])->where('recycle',SettingShops::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
                        }
                    } else {
                        $responseData ['shops'] = [];
                    }
                } else {
                    $responseData ['shops'] = [];
                }
            } else {
                $param ['user_id'] = $currentUser->userId;
                if (empty($data['plat_id'])){
                    $responseData ['shops'] = SettingShops::where(['recycle'=>SettingShops::SHOP_RECYCLE_UNDEL,'user_id'=>$param['user_id']])->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
                }else{
                    $responseData ['shops'] = SettingShops::where(['plat_id'=>$data['plat_id'],'user_id'=>$param['user_id']])->where('recycle',SettingShops::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();

                }
            }


            if ($responseData ['shops']) {
                $res = ['code' => 1, 'msg' => 'Success', 'data' => $responseData ['shops']];
            } else {
                $res = ['code' => -1, 'msg' => 'Error'];
            }
            return parent::layResponseData($res);
        }

        /**
         * @return array
         * Note: 配货单
         * Data: 2019/3/20 10:16
         * Author: zt8067
         */
        public function lists(Request $request)
        {
            $res = ['code' => 0, 'msg' => '', 'data' => '', 'count' => ''];
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($CurrentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (empty(['$shopsId'])) {
                            //未配置店铺 直接响应空
                        $res = [
                            'code' => '999',
                            'msg'  => '未配置店铺权限',
                        ];
                    }
                    //店铺id
                    $permissionParam ['source_shop'] = $shopsId;
                    $lists = self::$DistrbutionHandle->getSummaryByPage($request,$user_id,$permissionParam);
                } else {
                    $res = [
                        'code' => '999',
                        'msg'  => '未配置店铺权限',
                    ];
                }
            } else {
                $user_id = $CurrentUser->userId;
                $lists = self::$DistrbutionHandle->getSummaryByPage($request,$user_id);
            }
            if ($lists) {
                $res = [
                    'msg'   => 'Success',
                    'data'  => $lists['data'],
                    'count' => $lists['total'],
                ];
            } else {
                $res = [
                    'code' => '999',
                    'msg'  => 'Error',
                ];
            }
            return parent::layResponseData($res);
        }

        /**
         * @return array
         * Note: 导入单号
         * Data: 2019/3/21 9:30
         * Author: zt8067
         */
        public function importTacking(Request $request)
        {
            set_time_limit(0);
            ini_set('memory_limit','1024M');
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            $reluts = self::$DistrbutionHandle->updateTrack($request,$user_id);
            return Response()->json($reluts);
        }
        /**
         * @return excel
         * Note: 导出配货单
         * Data: 2019/4/2 17:30
         * Author: zt8067
         */
        public function explode()
        {
            set_time_limit(0);
            ini_set('memory_limit','1024M');
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            self::$DistrbutionHandle->explodeDistrbution($user_id);
        }

        /**
         * @return array
         * Note: 查看配货单
         * Data: 2019/4/9 14:30
         * Author: zt8067
         */
        public function readGoodsDescIndex(Request $request){
            $params = $request->all();
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            $OrdersDesc = self::$DistrbutionHandle->getOrdersInvoicesDesc($params, $user_id);
//            dd($OrdersDesc);
            return view('Order.readGoodsDescIndex', compact('OrdersDesc'));
        }



    }