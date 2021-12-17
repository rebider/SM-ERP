<?php

    namespace App\Http\Controllers\Rules;

    use App\Auth\Common\AjaxResponse;
    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\Menus;
    use App\Common\Common;
    use App\Exceptions\DataNotFoundException;
    use App\Models\GoodsAttribute;
    use App\Models\Platforms;
    use App\Models\RulesLogisticCondition;
    use App\Models\RulesOrderTroubleCondition;
    use App\Models\RulesLogisticAllocation;
    use App\Models\SettingCountry;
    use App\Models\SettingCurrencyExchange;
    use App\Models\SettingLogistics;
    use App\Models\SettingShops;
    use App\Models\SettingWarehouse;
    use App\Validates\LogisticAllocationConditionsValidate;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Validator;
    use Mockery\Exception;
    use Illuminate\Support\Facades\DB;

    class LogisticRulesController extends Controller
    {
        public $menusModel = [];

        public function __construct()
        {
            $this->menusModel = new Menus();
        }

        /**
         * @param Request $request
         * Note: 订单问题规则首页
         * Data: 2019/4/25 11:38
         * Author: zt8076
         */
        public function logisticAllocationIndex(Request $request)
        {
            //.layui-table-cell  去掉height: 28px;  嵌套一个div 绑定事件 事件删除该样式高度
            //便捷菜单
            $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(RulesLogisticAllocation::RULES_ORDER_MENUS_ID);
            return view('Rules/logisticAllocation/index')->with($responseData);
        }

        /**
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         * Note: 订单规则问题列表搜索
         * Data: 2019/4/25 10:38
         * Author: zt8076
         */
        public function logisticAllocationSearch(Request $request)
        {
            $data = $request->all();
            $offset = isset($data['page']) ? $data['page'] : 1;
            $limit = isset($data['limit']) ? $data['limit'] : 20;
            //开启状态
            $param['opening_status'] = (isset($data['data']['opening_status']) && !empty($data['data']['opening_status']))
                ? $data['data']['opening_status'] : '';
            //规则名称
            $param['trouble_rules_name'] = (isset($data['data']['trouble_rules_name']) && !empty($data['data']['trouble_rules_name']))
                ? $data['data']['trouble_rules_name'] : '';
            //开始时间
            $param['start_date'] = (isset($data['data']['start_date']) && !empty($data['data']['start_date']))
                ? $data['data']['start_date'] : '';
            //结束时间
            $param['end_date'] = (isset($data['data']['end_date']) && !empty($data['data']['end_date']))
                ? $data['data']['end_date'] : '';
            //用戶id 或者上級id
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $param ['user_id'] = $currentUser->userParentId;
            } else {
                $param ['user_id'] = $currentUser->userId;
            }
            $result = RulesLogisticAllocation::getAllocationDatas($param, $offset, $limit);
            return parent::layResponseData($result);
        }

        /**
         * @param Request $request
         * @param         $id
         * @return array
         * Note: 规则详页
         * Data: 2019/4/26 11:17
         * Author: zt8076
         */
        public function logisticAllocationDetail(Request $request, $id)
        {
            $datas = $request->all();
            if (empty($id)) {
                abort(404);
            }
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $optionTrouble ['user_id'] = $currentUser->userParentId;
            } else {
                $optionTrouble ['user_id'] = $currentUser->userId;
            }
            $conditionParam ['is_show'] = 1;
            //获取所有的条件数据
            $conditionDatas = RulesOrderTroubleCondition::getConditionsData($conditionParam);
            $rules = $this->getConditionOpt($conditionDatas);
            //获取所有问题类型
            $responseData['data'] = RulesLogisticAllocation::getLogisticAllocationByOpt(['field' => 'id', 'value' => $id], $optionTrouble);
            $optionTrouble ['created_man'] = $currentUser->userId;
            if (empty($responseData['data'])) {
                abort(404);
            }
            $responseData['data'] = $responseData['data']->toArray();
            $rules_trouble_condition_arr = [];
            $conditions = [];
            $logistic_ids = [];
            if (!empty($responseData['data'] ['rules_logistic_condition'])) {
                //condition id 做默认选中
                $rules_trouble_condition_arr = array_column($responseData['data'] ['rules_logistic_condition'], 'condition_id');
                //条件id升序
                array_multisort(array_column($responseData['data'] ['rules_logistic_condition'], 'condition_id'), SORT_ASC, $responseData['data'] ['rules_logistic_condition']);
                $conditions = $responseData['data'] ['rules_logistic_condition'];
            }
            if (!empty($responseData['data']['logistic_ids'])) {
                if (strpos($responseData['data']['logistic_ids'], ',')) {
                    $logistic_ids = $responseData['data']['logistic_ids'];
                    $logistic_ids = explode(',', $logistic_ids);
                } else {
                    $logistic_ids = (array)$responseData['data']['logistic_ids'];
                }
                $sortId = implode(',',$logistic_ids);
                $selectLogistics = SettingLogistics::whereIn('id', $logistic_ids)->orderByRaw(DB::raw("FIELD(id,$sortId)"))->get()->toArray();
            }
            $responseData ['conditionIds'] = $rules_trouble_condition_arr;
            $responseData ['conditions'] = $conditions;
            //规则条件
            $responseData ['rules'] = $rules;
            //国家逻辑
            if (in_array(3, $rules_trouble_condition_arr)) {
                $countryIds = $conditions [array_search('3', array_column($conditions, 'condition_id'))] ['cond_val'];
                $countryIdsArr = explode(',', $countryIds);
                $countrys = SettingCountry::getAllCountryExclud($countryIdsArr);
            } else {
                $countrys = SettingCountry::getAllCountry();
            }
            //仓库逻辑
            if (in_array(12,$rules_trouble_condition_arr)) {
                $wareIds = $conditions [array_search('12',array_column($conditions,'condition_id'))] ['cond_val'];
                $wareIdsArr = explode(',',$wareIds);
                $warehouses = SettingWarehouse::getAllCountryExclud($wareIdsArr);
            } else {
                //仓库
                $warehouses = SettingWarehouse::getAllWarehousesByUserId($optionTrouble ['user_id']);
            }
            //物流逻辑
            if (in_array(13, $rules_trouble_condition_arr)) {
                $logisIds = $conditions [array_search('13', array_column($conditions, 'condition_id'))] ['cond_val'];
                $logisIdsArr = explode(',', $logisIds);
                $logistics = SettingLogistics::getAllCountryExclud($logisIdsArr);
            } else {
                //物流
                $logistics = SettingLogistics::where(['user_id'=>$optionTrouble ['user_id'],'disable'=>SettingLogistics::LOGISTICS_STATUS_USING])->whereNotIn('id', $logistic_ids)->get()->toArray();
            }
            $responseData ['currencyInfos'] = SettingCurrencyExchange::all(['id','currency_form_code as code','currency_form_name as name'])->toArray();

            //查看
            if (empty($datas['edit'])) {
                $responseData ['logistics'] = $logistics;
                $responseData ['selectLogistics'] = $selectLogistics;
                //查看
                //根据condition id 做选中
                //渲染ul标签
                return view('Rules/logisticAllocation/detail')->with($responseData);
            } else if ($datas['edit'] == 1) {
                //编辑
                if ($request->isMethod('post')) {
                    //编辑数据提交
                    //数据处理
                    $troubleConditionDatas = RulesLogisticCondition::troubleDatasLogics($datas, $conditionDatas, $optionTrouble);
                    $validator = Validator::make(
                        $troubleConditionDatas['troubleData'],
                        LogisticAllocationConditionsValidate::getAllocationConditionsRules(false, $id),
                        LogisticAllocationConditionsValidate::getAllocationConditionsMessages(),
                        LogisticAllocationConditionsValidate::getAllocationConditionsAttributes()
                    );
                    if ($validator->fails()) {
                        return AjaxResponse::isFailure('', $validator->errors()->all());
                    }
                    //S3. 数据存储
                    DB::beginTransaction();
                    try {
                        $ruluRe = RulesLogisticAllocation::postGoods($id, $troubleConditionDatas['troubleData']);
                        if ($ruluRe) {
                            foreach ($troubleConditionDatas ['conditionData'] as $condVal) {
                                $condVal ['trouble_rule_id'] = $ruluRe->id;
                                //更新
                                if (in_array($condVal ['condition_id'], $responseData ['conditionIds'])) {
                                    $countryIds = $conditions [array_search($condVal ['condition_id'], array_column($conditions, 'condition_id'))];
                                    RulesLogisticCondition::postGoods($countryIds['id'], $condVal);
                                    unset($responseData ['conditionIds'] [array_search($condVal ['condition_id'], $responseData ['conditionIds'])]);
                                } else {
                                    //新增
                                    RulesLogisticCondition::postGoods(0, $condVal);
                                }
                            }
                            //编辑取消的条件处理
                            if (!empty($responseData ['conditionIds'])) {
                                foreach ($responseData ['conditionIds'] as $cancleId) {
                                    $countryIds = $conditions [array_search($cancleId, array_column($conditions, 'condition_id'))];
                                    //取消状态
                                    $cancleData ['is_used'] = RulesLogisticCondition::STATUS_CLOSEDING;
                                    //当前客户 操作人
                                    $cancleData ['created_man'] = $currentUser->userId;
                                    RulesLogisticCondition::postGoods($countryIds['id'], $cancleData);
                                }
                            }
                        } else {
                            DB::rollback();
                            return AjaxResponse::isFailure('修改规则失败');
                        }
                        DB::commit();
                        return AjaxResponse::isSuccess('修改规则成功');
                    } catch (Exception $e) {
                        DB::rollback();
                        Common::mongoLog($e,'物流规则','物流规则修改',__FUNCTION__);
                        return AjaxResponse::isFailure('修改规则失败');
                    } catch(\Error $e){
                        Common::mongoLog($e,'物流规则','物流规则修改',__FUNCTION__);
                    }
                } else {
                    //平台
                    $platforms = Platforms::getAllPlat();
                    //店铺 主账号下所有店铺
                    $user_id = $optionTrouble ['user_id'];
                    $shops = SettingShops::getShopsByUserId($user_id);
                    $shopsArr['amazon'] = [];
                    $shopsArr['rakuten'] = [];
                    $shopsArr['else'] = [];
                    foreach ($shops as $shop) {
                        if ($shop ['plat_id'] == 1) {
                            $shopsArr ['amazon'] [] = $shop;
                        } else if ($shop ['plat_id'] == 2) {
                            $shopsArr ['rakuten'] [] = $shop;
                        } else if ($shop ['plat_id'] == 3) {
                            $shopsArr ['else'] [] = $shop;
                        }
                    }
                    $attrArrs = GoodsAttribute::getAllAttrs();
                    $responseData ['shops'] = $shopsArr;
                    $responseData ['platforms'] = $platforms;
                    $responseData ['countrys'] = $countrys;
                    $responseData ['warehouses'] = $warehouses;
                    $responseData ['attrs'] = $attrArrs;
                    $checkedData = RulesLogisticCondition::getCheckedDatas($conditions);
                    $responseData ['checkedDatas'] = $checkedData;
                    $responseData ['logistics'] = $logistics;
                    $responseData ['selectLogistics'] = $selectLogistics;
                    return view('Rules/logisticAllocation/add_edit')->with($responseData);
                }
            }
        }

        /**
         * @param Request $request
         * Note: 订单问题规则添加
         * Data: 2019/4/25 10:20
         * Author: zt8076
         */
        public function logisticAllocationCreated(Request $request)
        {
            //所有条件
            $param ['is_show'] = 1;
            $conditionDatas = RulesOrderTroubleCondition::getConditionsData($param);
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $userInfo ['user_id'] = $currentUser->userParentId;
            } else {
                $userInfo ['user_id'] = $currentUser->userId;
            }
            $userInfo ['created_man'] = $currentUser->userId;


            if ($request->isMethod('get')) {
                $responseData = $this->getConditionOpt($conditionDatas);
                //平台
                $platforms = Platforms::getAllPlat();
                //店铺
                $user_id = $userInfo['user_id'];
                $shops = SettingShops::getShopsByUserId($user_id);
                $shopsArr['amazon'] = [];
                $shopsArr['rakuten'] = [];
                $shopsArr['else'] = [];
                foreach ($shops as $shop) {
                    if ($shop ['plat_id'] == 1) {
                        $shopsArr ['amazon'] [] = $shop;
                    } else if ($shop ['plat_id'] == 2) {
                        $shopsArr ['rakuten'] [] = $shop;
                    } else if ($shop ['plat_id'] == 3) {
                        $shopsArr ['else'] [] = $shop;
                    }
                }
                //国家
                $countrys = SettingCountry::getAllCountry();
                //仓库
                $warehouses = SettingWarehouse::getAllWarehousesByUserId($user_id);
                //物流
                $logistics = SettingLogistics::getAllLogisticsByUserId($user_id);
                //属性
                $attrArrs = GoodsAttribute::getAllAttrs();
                //币种
                $currencyInfos = SettingCurrencyExchange::all(['id','currency_form_code as code','currency_form_name as name'])->toArray();
                return view('Rules/logisticAllocation/logistic_rules_dialog')->with(['rules' => $responseData, 'platforms' => $platforms, 'shops' => $shopsArr, 'countrys' => $countrys, 'attrs' => $attrArrs, 'warehouses' => $warehouses, 'logistics' => $logistics, 'currencyInfos' => $currencyInfos]);
            } else if ($request->isMethod('post')) {
                $datas = $request->all();
                //数据处理
                //S1. 规则校验
                //S2. 参数组装
                $troubleConditionDatas = RulesLogisticCondition::troubleDatasLogics($datas, $conditionDatas, $userInfo);
                //数据验证
                $validator = Validator::make(
                    $troubleConditionDatas['troubleData'],
                    LogisticAllocationConditionsValidate::getAllocationConditionsRules(true),
                    LogisticAllocationConditionsValidate::getAllocationConditionsMessages(),
                    LogisticAllocationConditionsValidate::getAllocationConditionsAttributes()
                );
                if ($validator->fails()) {
                    return AjaxResponse::isFailure('', $validator->errors()->all());
                }
                //S3. 数据存储
                DB::beginTransaction();
                try {
                    $ruluRe = RulesLogisticAllocation::postGoods(0, $troubleConditionDatas['troubleData']);
                    if ($ruluRe) {
                        foreach ($troubleConditionDatas ['conditionData'] as $condVal) {
                            $condVal ['trouble_rule_id'] = $ruluRe->id;
                            RulesLogisticCondition::postGoods(0, $condVal);
                        }
                    } else {
                        DB::rollback();
                        return AjaxResponse::isFailure('添加规则失败');
                    }
                    DB::commit();
                    return AjaxResponse::isSuccess('添加规则成功');
                } catch (\Exception $e) {
                    DB::rollback();
                    Common::mongoLog($e,'物流规则','物流规则创建',__FUNCTION__);
                    return AjaxResponse::isFailure('添加规则失败');
                }catch(\Error $e){
                    Common::mongoLog($e,'物流规则','物流规则创建',__FUNCTION__);
                }
            }
        }


        /**
         * @param $conditionDatas
         * @return mixed
         * Note: 获取问题选项 右边li
         * Data: 2019/4/26 14:11
         * Author: zt8076
         */
        public function getConditionOpt($conditionDatas)
        {
            $responseData ['orders'] = [];
            $responseData ['logistics'] = [];
            $responseData ['products'] = [];
            $responseData ['deliver'] = [];
            foreach ($conditionDatas as $conditionData) {
                if ($conditionData ['condition_type'] == 1) {
                    //订单
                    $responseData ['orders'] [] = $conditionData;
                } else if ($conditionData ['condition_type'] == 2) {
                    //物流
                    $responseData ['logistics'] [] = $conditionData;
                } else if ($conditionData ['condition_type'] == 3) {
                    //商品
                    $responseData ['products'] [] = $conditionData;
                } else if ($conditionData ['condition_type'] == 4) {
                    //发货
                    $responseData ['deliver'] [] = $conditionData;
                }
            }
            return $responseData;
        }

        /**
         * @param $id
         * @return mixed
         * Note: 删除仓库规则
         * Data: 2019/4/30 14:00
         * Author: zt8076
         */
        public function delete($id)
        {
         if (!is_numeric($id)) throw new DataNotFoundException('参数非法');
         $results = ['code' => -1, 'msg' => '删除失败!'];
         $RulesWarehouseTrouble = RulesLogisticAllocation::destroy($id);
         if ($RulesWarehouseTrouble){
           RulesLogisticCondition::where('trouble_rule_id',$id)->delete();
             $results = ['code' => 1, 'msg' => '删除成功!'];
         }
         return response()->json($results);
        }
    }
