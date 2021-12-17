<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11
 * Time: 15:47
 */

namespace App\Http\Controllers\Shop;


use App\AmazonMWS\GithubMWS\MCS\MWSClient;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Platforms;
use App\Models\RulesOrderTrouble;
use App\Models\SettingShops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopManagerController extends Controller
{
    public  $shortcutMenus = [];
    public function __construct(){
        $this->menusModel = new Menus();
    }
    /**
     * @note
     * 店铺管理列表
     * @since: 2019/3/11
     * @author: zt7387
     * @param:  @param
     * @return: array
     */
    public function index(Request $request)
    {
        $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(RulesOrderTrouble::RULES_ORDER_MENUS_ID);
        $filterPriority = $request->input('priority');
        $responseData['plats'] =  Platforms::get();
        return view('Shop/index')->with($responseData);
    }

    /**
     * @note
     * 获取店铺列表数据
     * @since: 2019/3/11
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function ajaxGetSettingShopData(Request $request)
    {
        $limit = $request->get('limit');
        $param = $request->all();
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        $data = SettingShops::getSettingShopDatas($param, $limit,$user_id);
        $info = array(
            'code' => '0',
            'msg' => '',
            'count' => $data['count'],
            'data' => $data['res']
        );
        return Response()->json($info);
    }

    /**
     * @note
     *  添加自定义店铺
     * @since: 2019/3/14
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function addDefinedShop(Request $request)
    {
        $plats = Platforms::get();

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        if ($request->isMethod('post')) {
            $data['shop_name'] = $request->input('shop_name');
            $data['plat_id'] = $request->input('plat_id');
            $data['shop_type'] = SettingShops::SELF_DEFINED_TYPE;
//            $data['status'] = SettingShops::SHOP_STATUS_UNPOWER;
            $validator = Validator::make(
                array(
                    'shop_name' => $data['shop_name'],
                    'plat_id' => $data['plat_id']
                ),
                array(
                    'shop_name' => 'required',
                    'plat_id' => 'required'
                ),
                array(
                    'required' => ':attribute不能为空！ ',
                ),
                array(
                    'shop_name' => '店铺名称',
                    'plat_id' => '来源平台',
                )
            );

            if ($validator->fails()) {
                $message = $validator->messages();
                return [
                    'status' => false,
                    'msg' => $message
                ];
            }
            $shopModel = new SettingShops();
            if($request->input('id')){
                $id = $request->input('id');
                $result = SettingShops::editDefineShopData($data,$id);
            }else{
                $name = SettingShops::with('Users')->where(['user_id' => $user_id, 'shop_type' => SettingShops::SELF_DEFINED_TYPE,'shop_name'=>$data['shop_name'],'recycle'=>SettingShops::DEFINED_UNDELETE])->select('shop_name')->first();
                if ($name) {
                    return response()->json([
                        'code'=>201,
                        'msg'=>'该店铺名称已存在'
                    ]);
                }
                $result = SettingShops::addDefineShopData($shopModel, $data);
            }

            if ($result) {
                return response()->json([
                    'code'=>200,
                    'msg'=>'保存成功'
                ]);
            } else {
                return response()->json([
                    'code'=>201,
                    'msg'=>'保存失败'
                ]);
            }
        }

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        if($request->input('id')){
            $shopInfo = SettingShops::where(['id'=>$request->input('id'),'user_id'=>$user_id,'shop_type'=>SettingShops::SELF_DEFINED_TYPE])->first();
            if(!$shopInfo){
                abort(404);
            }
        }
        return view('Shop/addDefinedShop', ['plats' => $plats,'shopInfo'=>(isset($shopInfo) && $shopInfo) ? $shopInfo : '']);
    }

    /**
     * @note
     * 删除自定义店铺
     * @since: 2019/3/14
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function deleteDefinedShop(Request $request)
    {
        $shop_id = $request->input('id');

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        //有无历史订单
        $result = SettingShops::existHistoryOrders($shop_id,$user_id);
        if ($result > 0) {
            return response()->json([
                'code'=>201,
                'msg'=>'该店铺存在历史订单，不允许删除'
            ]);
        }
        $setingShopModel = new SettingShops();
        $re = $setingShopModel->where(['id' => $shop_id,'user_id'=>$user_id,'shop_type' => SettingShops::SELF_DEFINED_TYPE])->update(['recycle'=>SettingShops::DEFINED_DELETE]);
        if ($re) {
            return response()->json([
                'code'=>200,
                'msg'=>'删除成功'
            ]);
        } else {
            return response()->json([
                'code'=>201,
                'msg'=>'删除失败'
            ]);
        }
    }

    /**
     * @note
     * 查看自定义店铺
     * @since: 2019/3/14
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function checkDefinedShop(Request $request)
    {
        $shop_id = $request->input('id');
        $shop_type = $request->input('shop_tyle');
        if (!$shop_id || !is_numeric($shop_id )|| $shop_id < 0) {
            abort(404);
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        $res_data = SettingShops::getShopDatas($shop_id,$user_id,$shop_type);
        if(!$res_data){
            return json_encode(['没有权限']);
        }
        return view('Shop/selfDefineInfo',['checkInfo'=>$res_data]);
    }

    /**
     * @note
     * 添加亚马逊店铺
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function addAmazonShop(Request $request)
    {

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        $id = $request->input('id');
        if ($request->isMethod('post')) {
            $data['shop_name'] = $request->input('shop_name');
            $data['plat_id'] = SettingShops::PLAT_AMAZON;
            $data['amazon_accout'] = $request->input('amazon_accout');
            $data['service_secret'] = $request->input('secret');//授权令牌
            $data['license_key'] = $request->input('user_key');//卖家编号
            $data['shop_type'] = SettingShops::SELF_AMAZON_TYPE;
            $data['status'] = SettingShops::SHOP_STATUS_EMPOWER;
            $data['open_state'] = $request->input('open_state');
            $data['seller_id'] = $request->input('seller_id','');
            $validator = Validator::make(
                array(
                    'store_name' => $data['shop_name'],//亚马逊店铺名称
                    'amazon_accout' => $data['amazon_accout'],//Amazon账号
                    'open_state' => $data['open_state'],//开户站
                    'secret' => $data['service_secret'],//Secret Key
                    'user_key' => $data['license_key'],//AWSAccessKeyId
                    'seller_id' => $data['seller_id'],//Amazon卖家编号
                ),
                array(
                    'store_name' => 'required',
                    'amazon_accout' => 'required',
                    'open_state' => 'required',
                    'secret' => 'required',
                    'user_key' => 'required',
                    'seller_id' => 'required',
                ),
                array(
                    'required' => ':attribute不能为空！ ',
                    'unique' => ':attribute 需唯一'
                ),
                array(
                    'store_name' => '店铺名称',
                    'amazon_accout' => '亚马逊账号',
                    'open_state'=>'开户站',
                    'secret'=>'Secret Key',
                    'user_key'=>'AWSAccessKeyId',
                    'seller_id'=>'Amazon卖家编号',
                )
            );

         if ($validator->fails()) {
                $message = $validator->messages();
                return [
                    'status' => false,
                    'msg' => $message
                ];
            }

            $data['Marketplace_Id'] = SettingShops::AMAZON_STATION[$data['open_state']]['MarketplaceId'];
            if($id){
                $lotteShop = SettingShops::where(['id' => $id,'user_id' => $user_id,'shop_type' => SettingShops::SELF_AMAZON_TYPE,'recycle' => SettingShops::DEFINED_UNDELETE])->first();
                if(!$lotteShop){
                    return abort(404);
                }
                $data['status'] = SettingShops::SHOP_STATUS_EMPOWER;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['remark'] = '';
                $updateRe = SettingShops::where(['id' => $id,'user_id' => $user_id,'shop_type' => SettingShops::SELF_AMAZON_TYPE,'recycle' => SettingShops::DEFINED_UNDELETE])->update($data);

                if($updateRe){
                    return parent::layResponseData([
                        'code'=>200,
                        'msg'=>'保存成功'
                    ]);
                }else {
                    return parent::layResponseData([
                        'code'=>201,
                        'msg'=>'保存失败'
                    ]);
                }
            }
            //判断账号是否唯一
            $name = SettingShops::with('Users')->where(['user_id' => $user_id, 'shop_type' => SettingShops::SELF_AMAZON_TYPE,'shop_name'=>$data['shop_name'],'recycle'=>SettingShops::DEFINED_UNDELETE ])->select('shop_name')->first();
            if ($name) {
                return response()->json([
                    'code'=>201,
                    'msg'=>'该店铺名称已存在'
                ]);
            }
            //检测是否已经授权给其他账号 唯一性 查店铺表
            $shopModel = new SettingShops();
            $re = $shopModel->addDefineShopData($shopModel, $data);
            if ($re) {
                return response()->json([
                    'code'=>200,
                    'msg'=>'保存成功'
                ]);
            } else {
                return response()->json([
                    'code'=>201,
                    'msg'=>'保存失败'
                ]);
            }
        }
        if($id){
            $shopInfo = SettingShops::where(['id'=>$request->input('id'),'user_id'=>$user_id,'shop_type'=>SettingShops::SELF_AMAZON_TYPE])->first();
            if(!$shopInfo){
                abort(404);
            }
        }
        $openState = SettingShops::AMAZON_STATION;
        return view('Shop/addAmazonShop',['shopInfo'=>(isset($shopInfo) && $shopInfo) ? $shopInfo : '','openState'=>$openState]);
    }

    /**
     * @note
     * 取消授权
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function removeAuth(Request $request){

    }

    /**
     * @note
     * 重新授权
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function againAuth(Request $request){
        $shop_id = $request->input('shop_id');
        $authData = SettingShops::where(['id'=>$shop_id])->first();
        return view('Shop/againAuthView',['authData'=>$authData]);
    }

    /**
     * @note
     * 查看亚马逊店铺
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function checkAmazonData(Request $request){
        $shop_id = $request->input('id');
        if($shop_id < 0 || !is_numeric($shop_id) || !$shop_id){
            abort(404);
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        $shopData = SettingShops::where(['id'=>$shop_id,'user_id'=>$user_id])->first();
        if(!$shopData){
            abort(404);
        }
        return view('Shop/checkAmazonView',['shopData'=>$shopData]);
    }

    /**
     * @note
     * 添加乐天店铺
     * @since: 2019/3/20
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function addLotteShop(Request $request)
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        $id = $request->input('id');

        if ($request->isMethod('post')) {
            $data['shop_name'] = $request->input('shop_name');
            $data['user_name'] = $request->input('user_name');
            $data['created_man'] = $user_id;
            $data['shop_url'] = $request->input('shop_url');
            $data['service_secret'] = $request->input('secret');//授权令牌
            $data['license_key'] = $request->input('user_key');//卖家编号
            $data['shop_type'] = SettingShops::SELF_LOTLE_TYPE;
            $data['plat_id'] = SettingShops::PLAT_RAKUTEN;
            $data['status'] = SettingShops::SHOP_STATUS_EMPOWER;
            $data['ftp_pass'] = $request->input('ftp_pass');
            $data['ftp_user'] = $request->input('ftp_user');

            $validator = Validator::make(
                array(
                    'shop_name' => $data['shop_name'],
                    'user_name' => $data['user_name'],
                    'shop_url' => $data['shop_url'],
                    'secret' => $data['service_secret'],
                    'user_key' => $data['license_key'],
                ),
                array(
                    'shop_name' => 'required',
                    'user_name' => 'required',
                    'shop_url'=> 'required',
                    'secret' => 'required',
                    'user_key' => 'required'
                ),
                array(
                    'required' => ':attribute不能为空！ ',
                    'unique' => ':attribute 需唯一'
                ),
                array(
                    'shop_name' => '店铺名称',
                    'user_name' => '用户名称',
                    'secret'=>'授权令牌',
                    'user_key'=>'卖家编号',
                    'shop_url'=>'店铺URL'
                )
            );

            if ($validator->fails()) {
                $message = $validator->messages();
                return [
                    'status' => false,
                    'msg' => $message
                ];
            }
            //编辑
            if($id){
                $lotteShop = SettingShops::where(['id' => $id,'user_id' => $user_id,'shop_type' => SettingShops::SELF_LOTLE_TYPE,'recycle' => SettingShops::DEFINED_UNDELETE])->first();
                if(!$lotteShop){
                    return abort(404);
                }
                //乐天 亚马逊店铺授权失败的话需要清除异常信息
                $data['status'] = SettingShops::SHOP_STATUS_EMPOWER;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['remark'] = '';
                $updateRe = SettingShops::where(['id' => $id,'user_id' => $user_id,'shop_type' => SettingShops::SELF_LOTLE_TYPE,'recycle' => SettingShops::DEFINED_UNDELETE])->update($data);
                if($updateRe){
                    return parent::layResponseData([
                        'code'=>200,
                        'msg'=>'保存成功'
                    ]);
                }else {
                    return parent::layResponseData([
                        'code'=>201,
                        'msg'=>'保存失败'
                    ]);
                }
            }
            //判断账号是否唯一
            $name = SettingShops::with('Users')->where(['created_man' => $user_id, 'shop_type' => SettingShops::SELF_LOTLE_TYPE,'shop_name'=>$data['shop_name'],'recycle'=>SettingShops::DEFINED_UNDELETE])->select('shop_name')->first();
            if ($name) {
                return response()->json([
                    'code'=>201,
                    'msg'=>'该店铺名称已存在'
                ]);
            }
            //授权
            $shopModel = new SettingShops();
            $re = $shopModel->addDefineShopData($shopModel, $data);
            if ($re) {
                return response()->json([
                    'code'=>200,
                    'msg'=>'保存成功'
                ]);
            } else {
                return response()->json([
                    'code'=>201,
                    'msg'=>'保存失败'
                ]);
            }
        }
        if($id){
            $shopInfo = SettingShops::where(['id' => $id,'user_id' => $user_id,'shop_type' => SettingShops::SELF_LOTLE_TYPE,'recycle' => SettingShops::DEFINED_UNDELETE])->first(['shop_name','user_name','shop_url','service_secret','license_key','ftp_user','ftp_pass']);
        }
        return view('Shop/addLotteShop',['shopInfo' => isset($shopInfo) && !empty($shopInfo) ? $shopInfo : '']);
    }

    /**
     * @note
     * 取消授权 乐天
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function removeLotleAuth(Request $request){

    }

    /**
     * @note
     * 重新授权 乐天
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function againLotleAuth(Request $request){
        $shop_id = $request->input('shop_id');
        $authData = SettingShops::where(['id'=>$shop_id])->first();
        return view('Shop/againAuthView',['authData'=>$authData]);
    }

    /**
     * @note
     * 查看乐天店铺
     * @since: 2019/3/16
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function checkLotleData(Request $request){
        $shop_id = $request->input('id');
        if($shop_id < 0 || !is_numeric($shop_id) || !$shop_id){
            abort(404);
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
        $shopLotelData = SettingShops::where(['id'=>$shop_id,'user_id'=>$user_id])->first();
        if(!$shopLotelData){
            abort(404);
        }
        return view('Shop/checkLotleView',['shopData'=>$shopLotelData]);
    }

    /**
     * @note
     * 开户站
     * @since: 2019/4/23
     * @author: zt7837
     * @return: array
     */
    public function openSate(Request $request){
        $stateId = $request->input('stateId');
        $openState = SettingShops::AMAZON_STATION;
        foreach($openState as $k => $v){
            if($k == $stateId){
                return  ['url'=>$v['stateUrl']];
            }
        }
    }

    public function validateCredentail(){
        $shopInfo = SettingShops::find(10);
        $client = new MWSClient([
            'Marketplace_Id' => $shopInfo->Marketplace_Id,
            'Seller_Id' => $shopInfo->seller_id,
            'Access_Key_ID' =>$shopInfo->license_key,
            'Secret_Access_Key' => $shopInfo->service_secret,
//            'MWSAuthToken' => '' // Optional. Only use this key if you are a third party user/developer
        ]);
        return $client;
    }

}