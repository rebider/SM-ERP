<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;
use Illuminate\Http\Request;

/**
 * Class SettingLogistics
 * Notes: 物流表
 * @package App\Models
 * Data: 2019/3/7 15:19
 * Author: zt7785
 */
class SettingLogistics extends Model
{
    protected $table = 'setting_logistics';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','user_id','logistic_type','logistic_name','disable','is_show','created_at','updated_at'];

    /**
     * @var 物流状态启用
     */
    const LOGISTICS_STATUS_USING = 1;
    /**
     * @var 物流状态禁用
     */
    const LOGISTICS_STATUS_FORBID = 2;
    /**
     * 自定义
     */
    const SOURCE_DEFINE = 2;
    /**
     * 速贸
     */
    const SOURCE_SM = 1;
    /**
     * 是否展示
     */
    const LOGISTIC_UNSHOW = 0;
    /**
     * 展示
     */
    const LOGISTIC_SHOW = 1;

    /**
     * @var array 乐天物流 deliveryCompany 映射信息
     */
    public $RakutenLogisticsDeliveryCompany = [
        "1000"=>"その他",
        "1001"=>"ヤマト運輸",
        "1002"=>"佐川急便",
        "1003"=>"日本郵便",
        "1004"=>"西濃運輸",
        "1005"=>"西部運輸",
        "1006"=>"福山通運",
        "1007"=>"名鉄運輸",
        "1008"=>"トナミ運輸",
        "1009"=>"第一貨物",
        "1010"=>"新潟運輸",
        "1011"=>"中越運送",
        "1012"=>"岡山県貨物運送",
        "1013"=>"久留米運送",
        "1014"=>"山陽自動車運送",
        "1015"=>"日本トラック",
        "1016"=>"エコ配",
        "1017"=>"EMS",
        "1018"=>"DHL",
        "1019"=>"FedEx",
        "1020"=>"UPS",
        "1021"=>"日本通運",
        "1022"=>"TNT",
        "1023"=>"OCS",
        "1024"=>"USPS",
        "1025"=>"SFエクスプレス",
        "1026"=>"Aramex",
        "1027"=>"SGHグローバル?ジャパン",
        "1028"=>"Rakuten EXPRESS",
    ];

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }
    
    /**
     * @note
     * 绑定物流仓库
     * @since: 2019/3/12
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function WareHouse(){
        return $this->belongsToMany(SettingWarehouse::class,'setting_logistics_warehouses','logistic_id','warehouse_id')->select('setting_warehouse.id','setting_warehouse.warehouse_name','setting_warehouse.created_man','setting_warehouse.type');
    }

    /**
     * @note
     * 物流列表数据获取
     * @since: 2019/3/12
     * @author: zt7387
     * @return: array
     */
    public static function getSettingLogisticsInfo($param,$limit){
        $collection = self::with('WareHouse');

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        if(isset($param['data']['logic_source']) && !empty($param['data']['logic_source'])){
            $collection->where(['source'=>$param['data']['logic_source']]);
        }
        if(isset($param['data']['disable']) && !empty($param['data']['disable'])){
            $collection->where(['disable'=>$param['data']['disable']]);
        }
        if(isset($param['data']['logistic_name']) && !empty($param['data']['logistic_name'])){
            $collection->where('logistic_name','like','%'.$param['data']['logistic_name'].'%');
        }
        if(isset($param['data']['logic_house']) && !empty($param['data']['logic_house'])){
           //根据仓库拿到指定用户下面的物流id
            $logis_wh['warehouse_id'] = $param['data']['logic_house'];
            $logis_wh['user_id'] = $user_id;
            $logis_ware = SettingLogisticsWarehouses::where($logis_wh)->select('logistic_id','user_id')->get();
            if(!$logis_ware->isEmpty()) {
                $logis_arr = $logis_ware->toArray();
                //拿到物流id
                $ids = array_column($logis_arr,'logistic_id');
                //条件重用
                unset($logis_wh['warehouse_id']);
                $collection->whereIn('id',$ids);
            }
        }



        $count = $collection->where(['user_id'=>$user_id,'is_show'=>SettingLogistics::LOGISTIC_SHOW])->count();
        $result = $collection->where(['user_id'=>$user_id,'is_show'=>SettingLogistics::LOGISTIC_SHOW])->orderBy('created_at','desc')->paginate($limit)->toArray();
        return [
            'res'=>$result['data'],
            'count'=>$count
        ];
    }

    /**
     * @param $user_id
     * @return array
     * Note: 获取客户所有启用中的物流
     * Data: 2019/3/23 15:27
     */
    public static function getAllLogisticsByUserId($user_id)
    {
        return self::where('user_id',$user_id)->where('disable',self::LOGISTICS_STATUS_USING)->where('is_show',self::LOGISTIC_SHOW)->orderBy('id','ASC')->get(['id','logistic_type','logistic_name','user_id','created_man'])->toArray();
    }

    public static function getAllCountryExclud($ids)
    {
        return self::whereNotIn('id',$ids)->where('disable',self::LOGISTICS_STATUS_USING)->orderBy('id','ASC')->where('is_show',self::LOGISTIC_SHOW)->get(['id','logistic_type','logistic_name','user_id','created_man'])->toArray();
    }

    public static function getAllCountryInclud($ids)
    {
        return self::whereIn('id',$ids)->where('disable',self::LOGISTICS_STATUS_USING)->where('is_show',self::LOGISTIC_SHOW)->orderBy('id','ASC')->get(['id','logistic_type','logistic_name','user_id','created_man'])->toArray();
    }

    /**
     * @note
     * 物流新增
     * @since: 2019/3/25
     * @author: zt7387
     * @return: array
     */
    public static function addLogisticsData($data){
          return self::postLogistics($data);
    }
    public static function updateLogistics($data,$id,$user_id){
        return self::postLogistics($data,$id,$user_id);
    }
    //更新或者新增
    public static function postLogistics($data,$id = 0,$user_id = 0)
    {
        return self::updateOrCreate(['id' => $id,'user_id'=>$user_id], $data);
    }

    /**
     * @note
     * 物流 orm
     * @since: 2019/4/10
     * @author: zt7837
     * @return: array
     */
    public static function settingLogisticInsert($logicticsModel,$param,$update=false){
        if($update){
            $logicticsModel->updated_at = date('Y-m-d H:i:s');
            //再次选中重置为展示
            $logicticsModel->is_show = self::LOGISTIC_SHOW;
            $logicticsModel->save();
        }else{
            $logicticsModel->created_man = $param['created_man'];
            $logicticsModel->user_id = $param['user_id'];
            $logicticsModel->logistic_code = $param['logistic_code'] ?? '';
            $logicticsModel->logistic_name = str_replace(' ','',$param['logistic_name']);
            $logicticsModel->source = $param['source'];
            $logicticsModel->disable = $param['disable'];
            $logicticsModel->is_show = self::LOGISTIC_SHOW;
            $logicticsModel->created_at = date('Y-m-d H:i:s');
            $logicticsModel->updated_at = date('Y-m-d H:i:s');
            $logicticsModel->save();
        }
        return $logicticsModel;

    }

    public static function getLogisticInfoByUserId($userId)
    {
        $collection = self::with('WareHouse')->whereHas('WareHouse',function($query){
            $query->orWhere(['disable'=>SettingWarehouse::ON]);
        });
        return $collection->where('user_id',$userId)->get(['id','logistic_code']);
    }
    
    /**
     * @description 获取启用中的物流
     * @author zt7927
     * @date 2019/4/16 17:26
     * @return array
     */
    public static function getLogisticsByStatus($user_id)
    {
        return self::where('disable', self::LOGISTICS_STATUS_USING)->where('is_show',self::LOGISTIC_SHOW)->where('user_id',$user_id)->get()->toArray();
    }

    /**
     * @note
     * 物流定时任务
     * @since: 2019/4/23
     * @author: zt7837
     * @return: array
     */
    public static function taskLogisticRules(){
        $CurrentUser = CurrentUser::getCurrentUser();
        if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $CurrentUser->userParentId;
        } else {
            $user_id = $CurrentUser->userId;
        }
        $collection = new Orders();//Orders::select(['id','logistics_id']);
        $rulesLogisData = RulesLogisticAllocation::where(['user_id'=>$user_id,'status'=>RulesLogisticAllocation::STATUS_USED])->get();
        $orderIds = [];
        foreach ($rulesLogisData as $k=>$v) {
            $logisticId = explode(',',$v ['logistic_id']);
            $collection ->where (['user_id'=>$v ['user_id']]);

            $orderArrObj = $collection ->whereIn ('logistics_id',$logisticId)->get(['id','logistics_id','warehouse_id']);
            if($orderArrObj->isEmpty()){
                continue;
            }
            $orderArr = $orderArrObj->toArray();
            //订单物流是否绑定仓库
            foreach($orderArr as $key => $val){

//                $settingLogicObj = self::with(['WareHouse'=>function ($query) use($val) {
//                    $query->where(['logistic_id'=>$val['logistics_id'],'warehouse_id'=>$val['warehouse_id']]);
//                }])->toSql();

                $settingLogicObj =self::with(['WareHouse' => function ($query) use($val) {
                    $query->where(['logistic_id'=>$val['logistics_id'],'warehouse_id'=>$val['warehouse_id']]);
                }])->first();
                $settingLogicArr = $settingLogicObj ? $settingLogicObj->toArray() : '';

                if(empty($settingLogicArr) ||  (isset($settingLogicArr) &&  empty($settingLogicArr['ware_house']))){
                    unset($orderArr[$key]);
                }
            }
            if(!empty($orderArr)){
                $orderId = array_column($orderArr,'id');
                $logicId = array_column($orderArr,'logistics_id');
                $warehouse_id = array_column($orderArr,'warehouse_id');
                $orderIds[$k]['order_id'] = $orderId;
                $orderIds[$k]['logic_id'] = $logicId;
                $orderIds[$k]['warehouse_id'] = $warehouse_id;
            }
        }

    }

    /**
     * @note
     * 速贸物流选中状态
     * @since: 2019/6/19
     * @author: zt7837
     * @return: array
     */
    public static function logisticsCheck($logisticArr,$wareLogicArr) {
        if(!empty($logisticArr) && !empty($wareLogicArr)) {
            $logistic_code =  array_column($logisticArr,'logistic_code');
            $ware_code =  array_column($wareLogicArr,'code');

            foreach($logistic_code as $k => $v) {
                $k = array_search($v,$ware_code);
                if($k !== false) {
                    $wareLogicArr[$k]['show'] = 1;
                }
            }


        }

        return $wareLogicArr;
    }

    /**
     * @note
     * 接口同步速贸物流与系统物流同步
     * @since: 2019/6/28
     * @author: zt7837
     * @return: array
     */
    public static function getDiffLogic($user_id) {
        $sm_wh['user_id'] = $user_id;
        $sm_wh['source'] = self::SOURCE_SM;
        $shipp = ShippingMethodJapan::get();
        $sm_logic = SettingLogistics::where($sm_wh)->get();

        if(!$sm_logic->isEmpty() && !$shipp->isEmpty()) {
            $sm_arr = $sm_logic->toArray();
            $ship_arr = $shipp->toArray();
            $sm_code = array_column($sm_arr,'logistic_code');
            $ship_code = array_column($ship_arr,'code');
            //取系统与接口同步的差集物流
            $diff_code = array_diff($sm_code,$ship_code);
            foreach($diff_code as $k => $v) {
                $logic_wh['logistic_code'] = $v;
                $logic_wh['source'] = self::SOURCE_SM;
                $logic_wh['user_id'] = $user_id;
                SettingLogistics::where($logic_wh)->delete();
            }
        }

    }

}
