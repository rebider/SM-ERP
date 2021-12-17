<?php
namespace App\Http\Controllers\Logistics;


use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Common\Response;
use App\Auth\Models\Menus;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Platforms;
use App\Models\RulesOrderTrouble;
use App\Models\SettingLogistics;
use App\Models\SettingLogisticsMapping;
use App\Models\SettingLogisticsTypes;
use App\Models\SettingLogisticsWarehouses;
use App\Models\SettingWarehouse;
use App\Models\ShippingMethodJapan;
use App\Validates\LogisticsMappingValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class LogisticsMappingController
 * Notes: 物流映射
 * @package App\Http\Controllers\Logistics
 * Data: 2019/6/3 18:02
 * Author: zt7785
 */
class LogisticsMappingController extends Controller
{

    public function __construct()
    {
        $this->menusModel = new Menus();
    }

    /**
     * @return $this
     * Note: 物流映射首页
     * Data: 2019/6/4 9:09
     * Author: zt7785
     */
    public function index()
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(RulesOrderTrouble::RULES_ORDER_MENUS_ID);
        //绑定仓库
        $responseData ['platforms']  = Platforms::getAllPlat();
        //物流方式
        $responseData ['logistics']  = SettingLogistics::getAllLogisticsByUserId($user_id);
        return view('Logistics/Mapping/index')->with($responseData);
    }

    /**
     * @note
     * 物流列表数据
     * @since: 2019/3/12
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function LogisticsMappingSearch(Request $request)
    {
        $data = $request->all();
        $offset = isset($data['page']) ? $data['page'] : 1 ;
        $limit = isset($data['limit']) ? $data['limit'] : 20;
        //平台id
        $param['plat_id'] = (isset($data['data']['plat_id']) &&  !empty($data['data']['plat_id']))
            ? $data['data']['plat_id'] : '';
        //物流id
        $param['logistic_id'] = (isset($data['data']['logistic_id']) &&  !empty($data['data']['logistic_id']))
            ? $data['data']['logistic_id'] : '';

        //物流id
        $param['plat_logistic_name'] = (isset($data['data']['plat_logistic_name']) &&  $data['data']['plat_logistic_name'] !== '')
            ? $data['data']['plat_logistic_name'] : '';

        //用戶id 或者上級id
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $param ['user_id'] = $currentUser->userParentId;
        } else {
            $param ['user_id'] = $currentUser->userId;
        }
        $info = SettingLogisticsMapping::getLogisticsMappingDatas($param,$offset,$limit);
        return parent::layResponseData($info);
    }


    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse
     * Note: 添加物流映射
     * Data: 2019/6/4 9:27
     * Author: zt7785
     */
    public function addLogisticsMapping(Request $request)
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        //绑定仓库
        $responseData ['platforms']  = Platforms::getAllPlat();
        //物流方式
        $responseData ['logistics']  = SettingLogistics::getAllLogisticsByUserId($user_id);
        $param = $request->all();
        if ($request->isMethod('post')) {
            $validator = Validator::make(
                $param,
                LogisticsMappingValidate::getLogisticsMappingRules(),
                LogisticsMappingValidate::getLogisticsMappingMessages(),
                LogisticsMappingValidate::getLogisticsMappingAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }
            //规则“同一个平台、系统物流”有且只能对应一个“电商物流名称+电商物流承运商
            $plat_logistic_exist = SettingLogisticsMapping::where(
                [
                    ['is_deleted',SettingLogisticsMapping::UN_DELETED],
                    ['plat_id',$param['plat_id']],
                    ['logistic_id',$param['logistic_id']],
                ]
            )->first(['id']);
            if ($plat_logistic_exist) {
                return AjaxResponse::isFailure('', ['系统物流与电商物流映射关系已存在']);
            }
            $insertData ['created_man'] = $CurrentUser->userId;
            $insertData ['user_id'] = $user_id;

            $insertData ['plat_id'] = $param['plat_id'];
            $insertData ['logistic_name'] = $param['logistic_name'];
            $insertData ['logistic_id'] = $param['logistic_id'];
            $insertData ['plat_logistic_name'] = $param['plat_logistic_name'];
            $insertData ['carrier_name'] = $param['carrier_name'];
            $insertData ['is_deleted'] = SettingLogisticsMapping::UN_DELETED;
            $result = SettingLogisticsMapping::postDatas(0,$insertData);
            if ($result) {
                return AjaxResponse::isSuccess('添加物流映射成功');
            } else {
                return AjaxResponse::isFailure('添加物流映射失败');
            }
        }
        return view('Logistics/Mapping/addLogisticsMapping')->with($responseData);
    }

    /**
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\JsonResponse
     * Note: 编辑查看物流映射
     * Data: 2019/6/4 10:09
     * Author: zt7785
     */
    public function editLogisticsMapping(Request $request,$id)
    {
        if (empty($id)) {
            abort(404);
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        //指定id 指定客户 未被删除
        $logisticsMappingInfo = SettingLogisticsMapping::getlogisticsMappingInfoById($id,$user_id);
        if (empty($logisticsMappingInfo)) {
            abort(404);
        }

        //绑定仓库
        $responseData ['platforms']  = Platforms::getAllPlat();
        //物流方式
        $responseData ['logistics']  = SettingLogistics::getAllLogisticsByUserId($user_id);
        $param = $request->all();
        $responseData ['edit']  = isset($param ['edit']) ? $param ['edit'] : 0;
        $responseData ['logisticsMapping']  = $logisticsMappingInfo;
        if ($request->isMethod('post')) {
            $validator = Validator::make(
                $param,
                LogisticsMappingValidate::getLogisticsMappingRules(),
                LogisticsMappingValidate::getLogisticsMappingMessages(),
                LogisticsMappingValidate::getLogisticsMappingAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }

            //规则“同一个平台、系统物流”有且只能对应一个“电商物流名称+电商物流承运商
            $plat_logistic_exist = SettingLogisticsMapping::where(
                [
                    ['id','!=',$id],
                    ['is_deleted',SettingLogisticsMapping::UN_DELETED],
                    ['plat_id',$param['plat_id']],
                    ['logistic_id',$param['logistic_id']],
                ]
            )->first(['id']);
            if ($plat_logistic_exist) {
                return AjaxResponse::isFailure('', ['系统物流与电商物流映射关系已存在']);
            }
            $insertData ['created_man'] = $CurrentUser->userId;
            $insertData ['user_id'] = $user_id;

            $insertData ['plat_id'] = $param['plat_id'];
            $insertData ['logistic_name'] = $param['logistic_name'];
            $insertData ['logistic_id'] = $param['logistic_id'];
            $insertData ['plat_logistic_name'] = $param['plat_logistic_name'];
            $insertData ['carrier_name'] = $param['carrier_name'];
            $result = SettingLogisticsMapping::postDatas($id,$insertData);
            if ($result) {
                return AjaxResponse::isSuccess('编辑物流映射成功');
            } else {
                return AjaxResponse::isFailure('编辑物流映射失败');
            }
        }
        return view('Logistics/Mapping/editLogisticsMapping')->with($responseData);
    }


    /**
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\JsonResponse
     * Note: 删除物流映射
     * Data: 2019/6/4 10:29
     * Author: zt7785
     */
    public function deleteLogisticsMapping(Request $request,$id)
    {
        if (empty($id)) {
            abort(404);
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        //指定id 指定客户 未被删除
        $logisticsMappingInfo = SettingLogisticsMapping::getlogisticsMappingInfoById($id,$user_id);
        if (empty($logisticsMappingInfo)) {
            abort(404);
        }

        $updateData ['is_deleted'] = SettingLogisticsMapping::IS_DELETED;
        $insertData ['created_man'] = $CurrentUser->userId;
        $result = SettingLogisticsMapping::postDatas($id,$updateData);
        if ($result) {
            return AjaxResponse::isSuccess('删除物流映射成功');
        } else {
            return AjaxResponse::isFailure('删除物流映射失败');
        }
    }

}