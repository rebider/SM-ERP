<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/12
 * Time: 9:37
 */

namespace App\Http\Controllers\Logistics;


use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Common\Response;
use App\Auth\Models\Menus;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\RulesOrderTrouble;
use App\Models\SettingLogistics;
use App\Models\SettingLogisticsTypes;
use App\Models\SettingLogisticsWarehouses;
use App\Models\SettingWarehouse;
use App\Models\ShippingMethodJapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingLogisticsController extends Controller
{

    public function __construct()
    {
        $this->menusModel = new Menus();
    }

    public function index()
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
//        $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(RulesOrderTrouble::RULES_ORDER_MENUS_ID);

        //检测是否有速贸仓库,有责添加速贸物流
        $setting_wh['user_id'] = $user_id;
        $setting_wh['type'] = SettingWarehouse::SM_TYPE;
        $setting_wh['disable'] = SettingWarehouse::ON;
        $sm_warehouse = SettingWarehouse::where($setting_wh)->get()->toArray();

        //绑定仓库
        $wareHouse = SettingWarehouse::where(['disable'=>SettingWarehouse::ON,'user_id'=>$user_id])->get();
        $responseData['wareHouse'] = $wareHouse;
        $responseData['sm_warehouse'] = $sm_warehouse;
        return view('Logistics/index')->with($responseData);
    }

    /**
     * @note
     * 物流列表数据
     * @since: 2019/3/12
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function ajaxGetLogistics(Request $request)
    {
        $param = $request->all();
        $limit = $request->get('limit');
        $data = SettingLogistics::getSettingLogisticsInfo($param, $limit);
        $info = [
            'code' => '0',
            'msg' => '',
            'data' => $data['res'],
            'count' => $data['count']
        ];
        return parent::layResponseData($info);
    }

    /**
     * @note
     * 添加自定义物流
     * @since: 2019/3/25
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function addSelfDefineLogistics(Request $request)
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }

        if ($request->isMethod('post')) {
            $id = $request->input('id');
            $data['created_man'] = $CurrentUser->userId;
            $data['user_id'] = $CurrentUser->userId;
            $data['source'] = SettingLogistics::SOURCE_DEFINE;
            $data['disable'] = $request->input('disable') ? $request->input('disable') : SettingLogistics::LOGISTICS_STATUS_USING;
            $data['logistic_name'] = $request->input('logistic_name');
            $logisData['warehouse_id'] = $request->input('warehouse_name');

            if ($id) {
                self::editSelfDefineLogicValidator($data['logistic_name']);
            } else {
                $this->validate($request, [
                    'logistic_name' => 'required',
//                    'warehouse_name' => ['required'],
                ], [
                    'required' => ':attribute 为必填项'
                ], [
                    'logistic_name' => '物流名称',
//                    'warehouse_name' => '绑定仓库',
                ]);
            }

            if (!$id) {
                $logistic_name = SettingLogistics::with('Users')->where(['user_id' => $user_id, 'source' => SettingLogistics::SOURCE_DEFINE, 'logistic_name' => $data['logistic_name']])->select('logistic_name')->first();
                if ($logistic_name) {
                    return response()->json([
                        'code' => 201,
                        'msg' => '该物流名称已存在'
                    ]);
                }
            }
            //含有禁用的自定义仓库
            $hasDisabledWarehouse = SettingWarehouse::whereIn('id', explode(',', $request->input('warehouse_name')))
                ->where('disable', SettingWarehouse::OFF)
                ->where('type',SettingWarehouse::CUSTOM_TYPE)
                ->get()->toArray();
            if ($hasDisabledWarehouse) {
                return response()->json([
                    'code' => 201,
                    'msg' => '含有已禁用的仓库，请重新选择'
                ]);
            }

            DB::beginTransaction();
            try {
                if ($id) {
                    //编辑
                    $logisRe = SettingLogistics::updateLogistics($data, $id, $user_id);
                    $logisReId = $logisRe->id;
                    $settingLogicArr = SettingLogisticsWarehouses::where(['logistic_id' => $id, 'user_id' => $user_id])->get()->toArray();
                    $count = strpos($logisData['warehouse_id'], ',');
                    if ($count > 0) {
                        $wareHouseId = explode(',', $logisData['warehouse_id']);
                    } else {
                        $wareHouseId[] = $logisData['warehouse_id'];
                    }
                    $diffrentArr = array_filter(array_diff(array_column($settingLogicArr, 'warehouse_id'), $wareHouseId));
                    //取差集 删除原来绑定数据
                    if (!empty($diffrentArr)) {
                        $setelRe = SettingLogisticsWarehouses::whereIn('warehouse_id', $diffrentArr)->where(['user_id' => $user_id, 'logistic_id' => $id])->delete();
                        if (!$setelRe) {
                            $layData['code'] = 400;
                            $layData['msg'] = '操作失败';
                            DB::rollback();
                        }
                    }
                    $diffNewArr = array_diff($wareHouseId, array_column($settingLogicArr, 'warehouse_id'));
                    if (!empty($diffNewArr)) {
                        //v1.12 bug 速贸物流编辑了仓库 更新时间不刷新 创建时间不变
                        SettingLogistics::where(['id' => $id, 'user_id' => $user_id])->update(['updated_at'=>date('Y-m-d H:i:s')]);
                        SettingLogisticsWarehouses::postSetLogicWareHouse($diffNewArr, $logisReId, 0, $user_id);
                    }
                } else {
                    $settingModel = new SettingLogistics();
                    $logisRe = SettingLogistics::settingLogisticInsert($settingModel, $data);
                    $logisReId = $logisRe->id;
                    //绑定物流
                    $logistr['warehouse_id'] = $request->input('warehouse_name');
                    if (empty($logistr['warehouse_id'])) {
                        $logistr['warehouse_id'] = 0;
                    }
                    $wareHouseArr = explode(',', $logistr['warehouse_id']);
                    SettingLogisticsWarehouses::postSetLogicWareHouse($wareHouseArr, $logisReId, 0, $user_id);
                }
                if ($logisReId) {
                    $layData['code'] = 200;
                    $layData['msg'] = '操作成功';
                    DB::commit();
                } else {
                    $layData['code'] = 400;
                    $layData['msg'] = '操作失败';
                    DB::rollback();
                }
            } catch (Exception $exception) {
                $layData['code'] = 500;
                $layData['msg'] = '操作失败';
                DB::rollback();
            }
            return parent::layResponseData($layData);
        }
        $id = $request->input('id');
        //编辑
        if ((floor($id) - $id) == 0) {
            $id = $request->input('id');
            $logisData = SettingLogistics::with('WareHouse')->where(['id' => $id, 'user_id' => $user_id])->first();
            if ($logisData && $logisData->WareHouse) {
                $arr = $logisData->toArray();
                $wareHouseArr = array_column($arr['ware_house'], 'id');
            }
        } else {
            abort(404);
        }
        $ware_house = SettingWarehouse::where(['user_id' => $user_id])->where(['type' => SettingWarehouse::CUSTOM_TYPE, 'disable' => SettingWarehouse::ON])->get();
        return view('Logistics/addSelfDefineLogistics', ['wareHouse' => $ware_house, 'wareHouseArr' => isset($wareHouseArr) ? $wareHouseArr : [], 'logisData' => (isset($logisData) && $logisData) ? $logisData : (object)array()]);
    }

    /**
     * @note
     * 自定义物流验证器
     * @since: 2019/4/1
     * @author: zt7837
     * @param:
     * @return: array
     */
    public static function editSelfDefineLogicValidator($logistic_name)
    {
        $validator = Validator::make(
            array(
                'logistic_name' => $logistic_name,
            ),
            array(
                'logistic_name' => 'required',
            ),
            array(
                'required' => ':attribute不能为空！ ',
            ),
            array(
                'logistic_name' => '物流名称',
            )
        );
        return $validator;
    }

    /**
     * @note
     * 速贸物流启用 禁用
     * @since: 2019/4/11
     * @author: zt7837
     * @param:
     * @return: array
     */
    public static function editSmLogistics(Request $request)
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if (empty($CurrentUser)) {
            return AjaxResponse::isFailure('客户信息异常请重新登录');
        }
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $id = $request->input('id');
        if (!((floor($id) - $id) !== 0)) {
            abort(404);
        }
        if ($request->isMethod('post')) {
            $disable = $request->input('disable');
            $updateArr = ['disable' => $disable];
            if($request->input('logistic_name')){
                $updateArr['logistic_name'] = $request->input('logistic_name');
            }

            $logicRe = SettingLogistics::where(['id' => $id, 'user_id' => $user_id, 'source' => SettingLogistics::SOURCE_SM])->update($updateArr);
            if ($logicRe) {
                $layData['code'] = 200;
                $layData['msg'] = '编辑成功';

            } else {
                $layData['code'] = 201;
                $layData['msg'] = '编辑失败';
            }
            return Response()->json($layData);
        }
        $smLogicInfo = SettingLogistics::with('WareHouse')->where(['id' => $id, 'user_id' => $user_id])->first()->toArray();
        if (!$smLogicInfo) {
            abort(404);
        }
//        $wareStr = array_column($smLogicInfo['ware_house'],'warehouse_name');
//        $smLogicInfo['wareStr'] = $wareStr;
        return view('Logistics/editSmLogistics', ['smLogicInfo' => $smLogicInfo]);

    }

    /**
     * @note
     * 查看自定义物流
     * @since: 2019/3/26
     * @author: zt7387
     * @param:
     * @return: array
     */
    public static function checkSelfDefineLogistics(Request $request)
    {
        $id = $request->input('id');
        if (!$id || $id < 0 || !is_numeric($id)) {
            abort(404);
        }
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $logisticsRe = SettingLogistics::with('WareHouse')->where(['id' => $id, 'user_id' => $user_id])->first();

        $logisticsRe = $logisticsRe ? $logisticsRe->toArray() : '';
        if (!$logisticsRe) {
            abort(404);
        }
//        dd($logisticsRe->toArray());
        $warehouse_str = '';
        foreach ($logisticsRe['ware_house'] as $k => $v) {
            $warehouse_name = array_column($logisticsRe['ware_house'], 'warehouse_name');
            $warehouse_str = implode(',', $warehouse_name);
        }
        $logisticsRe['ware_house_str'] = $warehouse_str;

        return view('Logistics/detail', ['logisticsInfo' => $logisticsRe]);
    }

    /**
     * @note
     * 添加速贸物流
     * @since: 2019/3/25
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function addSmLogistics(Request $request)
    {
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }

        if ($request->isMethod('post')) {

            $id = $request->input('id');
            $data['created_man'] = $user_id;
            $data['user_id'] = $user_id;
            $data['source'] = SettingLogistics::SOURCE_SM;
            $layData['code'] = 201;
            $layData['msg'] = '添加失败';
            $chcekLogictics = $request->input('check') ?? '';
            $uncheckLogics = $request->input('uncheck') ?? '';
            $logicticsData = [];

            DB::beginTransaction();
            try {
                if (!empty($chcekLogictics)) {
                    //判空
                    if($uncheckLogics) {
                        //未选中的重置展示状态
                        foreach($uncheckLogics as $key => $val) {
                            SettingLogistics::where(['logistic_code'=>$val,'source'=>SettingLogistics::SOURCE_SM,'user_id'=>$user_id])->update(['is_show'=>SettingLogistics::LOGISTIC_UNSHOW]);
                        }
                    }

                    //中间表旧数据处理
                    $obj = SettingLogistics::where(['user_id'=>$user_id,'source'=>SettingLogistics::SOURCE_SM])->select('id')->get();
                    if(!$obj->isEmpty()) {
                        $old_logistic = $obj->toArray();
                        $ids = array_column($old_logistic,'id');
                        SettingLogisticsWarehouses::where(['user_id' => $user_id])->whereIn('logistic_id',$ids)->delete();
                    }

                    //历史数据清除暂时关闭 需要时打开
//                    SettingLogistics::getDiffLogic($user_id);
                    foreach ($chcekLogictics as $k => $v) {
                        //物流入库
                        $logicticsData['created_man'] = $user_id;
                        $logicticsData['user_id'] = $user_id;
                        $logicticsData['logistic_code'] = $k;
                        $logicticsData['logistic_name'] = $v['logicticsName'];
                        $logicticsData['source'] = SettingLogistics::SOURCE_SM;
                        $logicticsData['disable'] = SettingLogistics::LOGISTICS_STATUS_USING;
                        $logicticsData['is_show'] = SettingLogistics::LOGISTIC_SHOW;

                        $logicticsModel = new SettingLogistics();
                        $logicnNameObj = SettingLogistics::where(['logistic_code' => $k, 'source' => SettingLogistics::SOURCE_SM, 'user_id' => $user_id])->first();
                        if ($logicnNameObj) {
                            $logicInsertModel = SettingLogistics::settingLogisticInsert($logicnNameObj, $logicticsData, true);
                        } else {
                            $logicInsertModel = SettingLogistics::settingLogisticInsert($logicticsModel, $logicticsData);
                        }

                        $logicInsertId = $logicInsertModel ? $logicInsertModel->id : '';
                        //物流仓库 中间表 数据入库
                        if (strpos($v['warehouseName'], ',')) {
                            $wareArr = explode(',', $v['warehouseName']);

                            if (!empty($wareArr)) {
                                foreach ($wareArr as $key => $val) {
                                    $logicWareModel = new SettingLogisticsWarehouses();
                                    $logicWare[$key]['created_man'] = $user_id;
                                    $logicWare[$key]['user_id'] = $user_id;
                                    $logicWare[$key]['logistic_id'] = $logicInsertModel->id;
                                    $logicWare[$key]['created_at'] = date('Y-m-d H:i:s');
                                    $logicWare[$key]['updated_at'] = date('Y-m-d H:i:s');
                                    $wareId = SettingWarehouse::where(['warehouse_code' => $val,'type'=>SettingWarehouse::SM_TYPE,'user_id'=>$user_id])->first(['id']);
                                    if (!$wareId || !$wareId->id) {
//                                        continue;
                                    }
                                    $logicWare[$key]['warehouse_id'] = $wareId->id;
                                }
                                $logicInsertAll = DB::table('setting_logistics_warehouses')->insert($logicWare);
                                unset($logicWare);
                                if (!$logicInsertAll) {
                                    $layData['code'] = 201;
                                    $layData['msg'] = '添加失败';
                                    DB::rollback();
                                }
                            } else {
                                DB::commit();
                                continue;
                            }

                        } else {

                            $logicWare['created_man'] = $user_id;
                            $logicWare['user_id'] = $user_id;
                            $logicWare['logistic_id'] = $logicInsertModel->id;
                            $wareId = SettingWarehouse::where(['warehouse_code' => $v['warehouseName'],'user_id'=>$user_id])->select('id')->first();
                            if (!$wareId && !$wareId->id) {
                                $layData['code'] = 201;
                                $layData['msg'] = '添加异常';
                                return parent::layResponseData($layData);
//                                continue;
                            }

                            $logicWare['warehouse_id'] = $wareId->id;
                            $logicWareModel = new SettingLogisticsWarehouses();
                            //中间表 已经有关联物流仓库 则更新时间
                            $settingLogicFirstData = SettingLogisticsWarehouses::where(['logistic_id' => $logicInsertId, 'user_id' => $user_id, 'warehouse_id' => $wareId->id])->first();
                            if ($settingLogicFirstData) {
                                  SettingLogisticsWarehouses::where(['logistic_id' => $logicInsertId, 'user_id' => $user_id, 'warehouse_id' => $wareId->id])->update(['updated_at'=>date('Y-m-d H:i:s')]);
//                                SettingLogisticsWarehouses::logisticWareInsert($logicWareModel, $logicWare, true);
                                unset($logicWare);
                                continue;
                            }
                            $logicInsertAll = SettingLogisticsWarehouses::logisticWareInsert($logicWareModel, $logicWare);
                            unset($logicWare);
                            if (!$logicInsertAll) {
                                $layData['code'] = 201;
                                $layData['msg'] = '添加失败';
                                DB::rollback();
                            }
                        }
                    }

                    if ($logicInsertModel) {
                        $layData['code'] = 200;
                        $layData['msg'] = '添加成功';
                        DB::commit();
                    } else {
                        DB::rollback();
                        $layData['code'] = 201;
                        $layData['msg'] = '添加失败';
                    }
                }

            } catch (Exception $exception) {
                DB::rollback();
                $layData['code'] = 500;
                $layData['msg'] = '添加失败';
            }
            return parent::layResponseData($layData);
        }

        $id = $request->input('id');
        $smlogicArr = '';
        $logisticArr = '';
        //编辑
        if ((floor($id) - $id) == 0) {
            $id = $request->input('id');
            $logisData = SettingLogistics::with('WareHouse')->where(['id' => $id])->first();
            if ($logisData && $logisData->WareHouse) {
                $arr = $logisData->toArray();
                $wareHouseArr = array_column($arr['ware_house'], 'id');
            }
        } else {
            abort(404);
        }

        $smlogic= ShippingMethodJapan::where(['user_id'=>$user_id])->get();
        if(!$smlogic->isEmpty()) {
            $smlogicArr = $smlogic->toArray();
        }
        $logistic =  SettingLogistics::where(['source'=>SettingLogistics::SOURCE_SM,'user_id'=>$user_id,'is_show'=>SettingLogistics::LOGISTIC_SHOW])->get();
        if(!$logistic->isEmpty()) {
            $logisticArr = $logistic->toArray();
        }
        //是否展示
        $wareLogicArr =  SettingLogistics::logisticsCheck($logisticArr,$smlogicArr);
        return view('Logistics/addSmLogistics', [
            'wareLogicArr' => $wareLogicArr,
            'logisData' => (isset($logisData) && $logisData) ? $logisData : (object)array(),
        ]);
    }

}