<?php

namespace App\Http\Controllers\Rules;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Models\GoodsAttribute;
use App\Models\Platforms;
use App\Models\RulesOrderTrouble;
use App\Models\RulesOrderTroubleCondition;
use App\Models\RulesOrderTroubleType;
use App\Models\RulesTroubleCondition;
use App\Models\SettingCountry;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingLogistics;
use App\Models\SettingShops;
use App\Models\SettingWarehouse;
use App\Validates\TroubleConditionsValidate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class OrdersTroublesController extends Controller
{
    public $menusModel = [];

    public function __construct()
    {
        $this->menusModel = new Menus();
    }

    /**
     * @param Request $request
     * Note: 订单问题规则首页
     * Data: 2019/3/12 19:38
     * Author: zt7785
     */
    public function orderTroublesIndex (Request $request) {
        //.layui-table-cell  去掉height: 28px;  嵌套一个div 绑定事件 事件删除该样式高度
        //便捷菜单
        $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(RulesOrderTrouble::RULES_ORDER_MENUS_ID);
        return view('Rules/orderTroubles/index')->with($responseData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 订单规则问题列表搜索
     * Data: 2019/3/13 10:38
     * Author: zt7785
     */
    public function orderTroublesSearch (Request $request) {
        $data = $request->all();
        $offset = isset($data['page']) ? $data['page'] : 1 ;
        $limit = isset($data['limit']) ? $data['limit'] : 20;
        //开启状态
        $param['opening_status'] = (isset($data['data']['opening_status']) &&  !empty($data['data']['opening_status']))
            ? $data['data']['opening_status'] : '';
        //规则名称
        $param['trouble_rules_name'] = (isset($data['data']['trouble_rules_name']) &&  !empty($data['data']['trouble_rules_name']))
            ? $data['data']['trouble_rules_name'] : '';
        //开始时间
        $param['start_date'] = (isset($data['data']['start_date']) &&  !empty($data['data']['start_date']))
            ? $data['data']['start_date'] : '';

        //结束时间
        $param['end_date'] = (isset($data['data']['end_date']) &&  !empty($data['data']['end_date']))
            ? $data['data']['end_date'] : '';

        //用戶id 或者上級id
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $param ['user_id'] = $currentUser->userParentId;
        } else {
            $param ['user_id'] = $currentUser->userId;
        }
        $result = RulesOrderTrouble::getTroublesDatas($param,$offset,$limit);
        return parent::layResponseData($result);
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     * Note: 规则详页
     * Data: 2019/3/13 11:17
     * Author: zt7785
     */
    public function orderTroublesDetail (Request $request,$id) {
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
        $responseData['data'] = RulesOrderTrouble::getOrderTroublebyOpt(['field'=>'id','value'=>$id],$optionTrouble);
        $optionTrouble ['created_man'] = $currentUser->userId;
        if (empty($responseData['data'])) {
            abort(404);
        }
        $responseData['data'] = $responseData['data']->toArray();
        //问题类型
        $responseData ['troubles'] = RulesOrderTroubleType::getTroubleType();
        $rules_trouble_condition_arr = [];
        $conditions = [];
        if (!empty($responseData['data'] ['rules_trouble_condition'])) {
            //condition id 做默认选中
            $rules_trouble_condition_arr = array_column($responseData['data'] ['rules_trouble_condition'],'condition_id');

            //条件id升序
            array_multisort(array_column($responseData['data'] ['rules_trouble_condition'], 'condition_id'), SORT_ASC, $responseData['data'] ['rules_trouble_condition']);

            $conditions = $responseData['data'] ['rules_trouble_condition'];
        }
        $responseData ['conditionIds'] = $rules_trouble_condition_arr;
        $responseData ['conditions'] = $conditions;
        //规则条件
        $responseData ['rules'] = $rules;

        //国家逻辑
        if (in_array(3,$rules_trouble_condition_arr)) {
            $countryIds = $conditions [array_search('3',array_column($conditions,'condition_id'))] ['cond_val'];
            $countryIdsArr = explode(',',$countryIds);
            $countrys = SettingCountry::getAllCountryExclud($countryIdsArr);
        } else {
            $countrys = SettingCountry::getAllCountry();
        }
        //物流逻辑
        if (in_array(13,$rules_trouble_condition_arr)) {
            $logisIds = $conditions [array_search('13',array_column($conditions,'condition_id'))] ['cond_val'];
            $logisIdsArr = explode(',',$logisIds);
            $logistics = SettingLogistics::getAllCountryExclud($logisIdsArr);
        } else {
            //物流
            $logistics = SettingLogistics::getAllLogisticsByUserId($optionTrouble ['user_id']);
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

        $responseData ['currencyInfos'] = SettingCurrencyExchange::getAllCurrency($optionTrouble ['user_id'])->toArray();

        //币种
        if (in_array(15,$rules_trouble_condition_arr)) {
            $responseData ['currency_unit'] = $conditions [array_search('15',array_column($conditions,'condition_id'))] ['cond_unit'];
        }
        //查看
        if (empty($datas['edit'])) {
            //查看
            //根据condition id 做选中
            //渲染ul标签
            return view('Rules/orderTroubles/detail')->with($responseData);
        } else if ($datas['edit'] == 1){
            //编辑
            if ($request->isMethod('post')) {
                //编辑数据提交
                //数据处理
                $troubleConditionDatas = RulesTroubleCondition::troubleDatasLogics($datas,$conditionDatas,$optionTrouble);

                $validator = Validator::make(
                    $troubleConditionDatas['troubleData'],
                    TroubleConditionsValidate::getTroubleConditionsRules(false,$id),
                    TroubleConditionsValidate::getTroubleConditionsMessages(),
                    TroubleConditionsValidate::getTroubleConditionsAttributes()
                );
                if ($validator->fails()) {
                    return AjaxResponse::isFailure('', $validator->errors()->all());
                }

                //S3. 数据存储
                DB::beginTransaction();
                try {
                    $ruluRe = RulesOrderTrouble::postDatas($id,$troubleConditionDatas['troubleData']);
                    if ($ruluRe) {
                        foreach ($troubleConditionDatas ['conditionData'] as $condVal) {
                            $condVal ['trouble_rule_id'] = $ruluRe->id;
                            //更新
                            if (in_array($condVal ['condition_id'],$responseData ['conditionIds'])) {
                                $countryIds = $conditions [array_search($condVal ['condition_id'],array_column($conditions,'condition_id'))];
                                RulesTroubleCondition::postGoods($countryIds['id'],$condVal);
                                unset($responseData ['conditionIds'] [array_search($condVal ['condition_id'],$responseData ['conditionIds'])]);
                            } else {
                                //新增
                                RulesTroubleCondition::postGoods(0,$condVal);
                            }
                        }

                        //编辑取消的条件处理
                        if (!empty($responseData ['conditionIds'])) {
                            foreach ($responseData ['conditionIds'] as $cancleId) {
                                $countryIds = $conditions [array_search($cancleId,array_column($conditions,'condition_id'))];
                                //取消状态
                                $cancleData ['is_used'] = RulesTroubleCondition::STATUS_CLOSEDING;
                                //当前客户 操作人
                                $cancleData ['created_man'] = $currentUser->userId;
                                RulesTroubleCondition::postGoods($countryIds['id'],$cancleData);
                            }
                        }
                    } else {
                        DB::rollback();
                        return AjaxResponse::isFailure('编辑规则失败');
                    }
                    DB::commit();
                    return AjaxResponse::isSuccess('编辑规则成功');
                } catch (Exception $exception) {
                    DB::rollback();
                    return AjaxResponse::isFailure('编辑规则失败');
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
                $responseData ['logistics'] = $logistics;
                $responseData ['attrs'] = $attrArrs;
                //币种
                $responseData ['currencyInfos'] = SettingCurrencyExchange::getAllCurrency($optionTrouble ['user_id'])->toArray();
                $checkedData = RulesTroubleCondition::getCheckedDatas($conditions);
                $responseData ['checkedDatas'] = $checkedData;
                return view('Rules/orderTroubles/add_edit')->with($responseData);
            }
        }
    }

    /**
     * @param Request $request
     * Note: 订单问题规则添加
     * Data: 2019/3/16 10:20
     * Author: zt7785
     */
    public function orderTroublesCreated (Request $request) {
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
            //问题类型
            $problems  = RulesOrderTroubleType::getTroubleType();
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
            $currencyInfos = SettingCurrencyExchange::getAllCurrency($user_id)->toArray();

            return view('Rules/orderTroubles/order_rules_dialog')->with(['rules'=>$responseData,'problems'=>$problems,'platforms'=>$platforms,'shops'=>$shopsArr,'countrys'=>$countrys,'attrs'=>$attrArrs,'warehouses'=>$warehouses,'logistics'=>$logistics,'currencyInfos'=>$currencyInfos]);
        } else if ($request->isMethod('post')) {
            $datas = $request->all();
            //数据处理
            //S1. 规则校验
            //S2. 参数组装
            $troubleConditionDatas = RulesTroubleCondition::troubleDatasLogics($datas,$conditionDatas,$userInfo);
            //数据验证
            $validator = Validator::make(
                $troubleConditionDatas['troubleData'],
                TroubleConditionsValidate::getTroubleConditionsRules(true),
                TroubleConditionsValidate::getTroubleConditionsMessages(),
                TroubleConditionsValidate::getTroubleConditionsAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }
            //S3. 数据存储
            DB::beginTransaction();
            try {
                $ruluRe = RulesOrderTrouble::postDatas(0,$troubleConditionDatas['troubleData']);
                if ($ruluRe) {
                    foreach ($troubleConditionDatas ['conditionData'] as $condVal) {
                        $condVal ['trouble_rule_id'] = $ruluRe->id;
                        RulesTroubleCondition::postGoods(0,$condVal);
                    }
                } else {
                    DB::rollback();
                    return AjaxResponse::isFailure('添加规则失败');
                }
                DB::commit();
                return AjaxResponse::isSuccess('添加规则成功');
            } catch (Exception $exception) {
                DB::rollback();
                return AjaxResponse::isFailure('添加规则失败');
            }
        }
    }



    /**
     * @param $conditionDatas
     * @return mixed
     * Note: 获取问题选项 右边li
     * Data: 2019/3/28 14:11
     * Author: zt7785
     */
    public function getConditionOpt ($conditionDatas) {
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
     * @return \Illuminate\Http\JsonResponse
     * Note: 问题规则删除
     * Data: 2019/6/3 17:00
     * Author: zt7785
     */
    public function orderTroublesDelete($id)
    {
        if (!is_numeric($id)) {
            return AjaxResponse::isFailure('参数异常');
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            return AjaxResponse::isFailure('用户信息过期,请重新登录');
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $troubles_info = RulesOrderTrouble::where('user_id',$user_id)->where('id',$id)->first(['is_deleted']);
        if (empty($troubles_info)) {
            return AjaxResponse::isFailure('订单问题信息异常');
        }
        
        if ($troubles_info['is_deleted'] == RulesOrderTrouble::IS_DELETED) {
            return AjaxResponse::isSuccess('订单问题规则删除成功');
        }
        $updateData ['is_deleted'] = RulesOrderTrouble::IS_DELETED;
        RulesOrderTrouble::postDatas($id,$updateData);
        return AjaxResponse::isSuccess('订单问题规则删除成功');
    }
}
