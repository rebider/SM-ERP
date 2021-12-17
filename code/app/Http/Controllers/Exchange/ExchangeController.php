<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 16:20
 */

namespace App\Http\Controllers\Exchange;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Auth\Models\Users;
use App\Common\Common;
use App\Http\Services\APIHelper;
use App\Models\Goods;
use App\Models\GoodsCollection;
use App\Models\GoodsPic;
use App\Models\RulesOrderTrouble;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingCurrencyExchangeHistory;
use App\Models\SettingCurrencyExchangeMaintain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;


class ExchangeController extends Controller
{
    //

    /**
     * @var Goods;
     */
    protected $Goods ;

    /**
     * @var GoodsCollection
     */
    protected $GoodsCollection ;

    /**
     * @var GoodsPic
     */
    protected $GoodsPic ;

    private $currencyTitle = '历史汇率';

    public function __construct()
    {
        $this->menusModel = new Menus();
    }

    /**
     * @description 默认初始页面
     * @author zt6650
     * @creteTime 2019/3/11 17:10
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('Goods.GoodsManage.index') ;
    }

    /**
     * @description 商品采集的首页
     * @author zt6650
     * @creteTime 2019/3/12 16:54
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function goodsCollect(Request $request)
    {
        return view('Goods.GoodsManage.goodsCollect') ;
    }

    /**
     * @note
     * 汇率更新
     * @since: 2019/3/28
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function collectionExchange(Request $request)
    {
        $urls = $request->get('urls' ,'') ;
        $url_arr = explode("\n" ,$urls ) ;

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        try {
            DB::beginTransaction();
            foreach ($url_arr as $value) {
                $API = new APIHelper($value);
                if (!$API->plate) {
                    DB::rollback();
                    return [
                        'status' => false,
                        'msg' => $value . '数据有误!',
                    ];
                }
                $re = $API->getAll();
//                $exchangeArr = [];
                //组装数据
                foreach($re['description'] as $k=>$v){
                    if($v === '日元'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'JPY'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'JPY'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'JPY',
                                'currency_form_name' => '日元',
                                'ident_fier' => 'J￥',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'currency_form_ename' => 'JPY',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }else if($v === '美元'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'USD'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'USD'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'USD',
                                'currency_form_name' => '美元',
                                'ident_fier' => '$',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'currency_form_ename' => 'US doller',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }else if($v === '澳大利亚元'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'AUD'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'AUD'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'AUD',
                                'currency_form_name' => '澳大利亚元',
                                'ident_fier' => 'AUD',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'currency_form_ename' => 'AUD',
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }else if($v === '韩国元'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'KRW'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'KRW'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'KRW',
                                'currency_form_name' => '韩元',
                                'ident_fier' => '₩',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'currency_form_ename' => 'KRW',
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }else if($v === '欧元'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'EUR'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'EUR'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'EUR',
                                'currency_form_name' => '欧元',
                                'ident_fier' => 'EUR',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'currency_form_ename' => 'EUR',
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }else if($v === '港币'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'HKD'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'HKD'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'HKD',
                                'currency_form_name' => '港币',
                                'ident_fier' => 'HK$',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'currency_form_ename' => 'HKD',
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }else if($v === '英镑'){
                        $rate = $re['description'][$k+4] * 0.01;
                        $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'GBP'])->first();
                        if($cnyRe){
                            SettingCurrencyExchange::where(['currency_form_code'=>'GBP'])->update(['exchange_rate'=>$rate,'updated_at'=>date('Y-m-d H:i:s')]);
                        }else{
                            $exchangeArr = [
                                'currency_to_code' => 'CNY',
                                'currency_to_name' => '人民币',
                                'currency_form_code' => 'GBP',
                                'currency_form_name' => '英镑',
                                'ident_fier' => '£',
                                'created_man' => $user_id,
                                'user_id' => $user_id,
                                'exchange_rate' => $rate,
                                'is_show' => SettingCurrencyExchange::ISSHOW,
                                'currency_form_ename' => 'GB Pound',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            SettingCurrencyExchange::insert($exchangeArr);
                        }
                    }
                }
                unset($API);
            }
            DB::commit();
        } catch (\Exception $exception) {
            Common::mongoLog($exception,'汇率管理','汇率更新',__FUNCTION__);
            return [
                'status' => false,
                'msg' => '数据有误!',
            ] ;
        };
        return [
            'status'=>true ,
            'msg'=>'同步汇率成功!'
        ] ;
    }

    /**
     * @note
     * 组装汇率数据 //todo
     * @since: 2019/3/27
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function packAgeRateArr($rateDes,$k,$exchangeArr,$code,$name){
        $rate = $rateDes['description'][$k+4] * 0.01;
        array_push($exchangeArr,[
            'currency_to_code' => 'CNY',
            'currency_to_name' => '人民币',
            'currency_form_code' => $code,
            'currency_form_name' => $name,
            'exchange_rate' => $rate
        ]);
        return $exchangeArr;
    }

    /**
     * @note
     * 汇率首页
     * @since: 2019/3/28
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function exchangeIndex(){
        $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(RulesOrderTrouble::RULES_ORDER_MENUS_ID);
        return view('Exchange/exchangeIndex')->with($responseData);
    }

    /**
     * @note
     * 汇率列表数据获取
     * @since: 2019/3/28
     * @author: zt7387
     * @param:  $request
     * @return: array
     */
    public function ajaxGetExchangeData(Request $request){
        $param = $request->all();

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        $currency_code = SettingCurrencyExchange::where(['is_show'=>SettingCurrencyExchange::ISSHOW])->get(['currency_form_code'])->toArray();
        if(!empty($currency_code)){
            $param['data']['currency_form_code'] =  array_column($currency_code,'currency_form_code');
        }
        //默认展示日 韩 美
        $data['currency_form_code'] = isset($param['data']['currency_form_code']) && !empty($param['data']['currency_form_code']) ? $param['data']['currency_form_code'] : '';
        $data['currency_form_name'] = isset($param['data']['currency_form_name']) && !empty($param['data']['currency_form_name']) ? $param['data']['currency_form_name'] : '';
        $limit = $request->input('limit') ? $request->input('limit') : 10;
        $page = $request->input('page') ? $request->input('page') : 1;
        $settingData = SettingCurrencyExchange::ajaxGetExchangeData($data,$limit,$page,$user_id);
        if(!$settingData['data']){
            return parent::layResponseData($settingData);
        }else{
            return parent::layResponseData($settingData);
        }
    }

    /**
     * @note
     * 汇率添加
     * @since: 2019/3/28
     * @author: zt7837
     * @param: $request
     * @return: array
     */
    public function exchangeAdd(Request $request){
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }
       if($request->isMethod('post')){
           $param = $request->all();
           $exchangeArr = explode(',',$param['exchange_name']);
           $oldArr = SettingCurrencyExchange::where(['is_show'=>SettingCurrencyExchange::ISSHOW])->get()->toArray();
           $exchangeInsertArr = array_diff($exchangeArr,array_column($oldArr,'currency_form_code'));
           $exchangeDifftArr = array_diff(array_column($oldArr,'currency_form_code'),$exchangeArr);

           //取差集做状态更新 只更新指定用户的汇率
           $re = SettingCurrencyExchange::whereIn('currency_form_code',$exchangeInsertArr)->update(['is_show'=>SettingCurrencyExchange::ISSHOW]);
           $oldRe =  SettingCurrencyExchange::whereIn('currency_form_code',$exchangeDifftArr)->update(['is_show'=>SettingCurrencyExchange::UNSHOW]);
           //历史表同步
           /*SettingCurrencyExchangeHistory::whereIn('currency_form_code',$exchangeInsertArr)->where(['user_id'=>$user_id])->update(['is_show'=>SettingCurrencyExchange::ISSHOW]);
           SettingCurrencyExchangeHistory::whereIn('currency_form_code',$exchangeDifftArr)->where(['user_id'=>$user_id])->update(['is_show'=>SettingCurrencyExchange::UNSHOW]);*/
           if($re || $oldRe){
               return parent::layResponseData([
                   'code' => 200,
                   'msg'  => '添加成功'
               ]);
           }else{
               return parent::layResponseData([
                   'code' => 201,
                   'msg' => '添加失败'
               ]);
           }
       }
//        $currencyInfo = SettingCurrencyExchange::get();
        $currencyArr = '';
        $currencyInfo = SettingCurrencyExchange::get();
        if(!$currencyInfo->isEmpty()) {
            $currencyArr = $currencyInfo->toArray();
        }

        return view('Exchange/exchangeAdd',['currencyInfo'=>$currencyArr,'user_id'=>$user_id]);
    }

    /**
     * @note
     * 同步我的汇率
     * @since: 2019/3/28
     * @author: zt7837
     * @param:
     * @return: array
     */
    public function addSettingCurrencyExchangeMain($regis_user = 0){
        $currencyArr = SettingCurrencyExchange::get()->toArray();
        $currencyMainArr = [];
        $historyArr = [];

        //注册转存数据
        if($regis_user) {
            $user_id = $regis_user;
        } else {
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
        }

       try{

           DB::beginTransaction();
           foreach($currencyArr as $k => $v){
               $currencyMainArr[$k]['created_man'] = $user_id;
               $currencyMainArr[$k]['user_id'] = $user_id;
               $currencyMainArr[$k]['currency_exchange_id'] = $v['id'];
               $currencyMainArr[$k]['currency_form_name'] = $v['currency_form_name'];
               $currencyMainArr[$k]['currency_form_code'] = $v['currency_form_code'];
               $currencyMainArr[$k]['exchange_rate'] = $v['exchange_rate'];
               $currencyMainArr[$k]['created_at'] = date('Y-m-d H:i:s');
               $currencyMainArr[$k]['updated_at'] = date('Y-m-d H:i:s');

               $historyArr[$k]['currency_to_code'] = $v['currency_to_code'];
               $historyArr[$k]['currency_to_name'] = $v['currency_to_name'];
               $historyArr[$k]['currency_form_code'] = $v['currency_form_code'];
               $historyArr[$k]['currency_form_name'] = $v['currency_form_name'];
               $historyArr[$k]['cur_exchange_id'] = $v['id'];
               $historyArr[$k]['exchange_rate'] = $v['exchange_rate'];
               $historyArr[$k]['ident_fier'] = $v['ident_fier'];
               $historyArr[$k]['created_man'] = $user_id;
               $historyArr[$k]['user_id'] = $user_id;
               $historyArr[$k]['created_at'] = date('Y-m-d H:i:s');
               $historyArr[$k]['updated_at'] = date('Y-m-d H:i:s');
           }

           DB::table('setting_currency_exchange_maintain')->where('user_id',$user_id)->delete();
           //批量插入
           $mainRe = DB::table('setting_currency_exchange_maintain')->insert($currencyMainArr);
           //历史表
           $hisRe = DB::table('setting_currency_exchange_history')->insert($historyArr);

           if($mainRe && $hisRe){
               DB::commit();
               return [
                   'status' => true,
                   'msg' => '同步汇率成功'
               ];
           }
           DB::rollback();
           return [
               'status' => false,
               'msg' => '同步汇率失败'
           ];

       } catch (\Exception $exception) {
           Common::mongoLog($exception,'同步到我的汇率','同步到我的汇率',__FUNCTION__);
           DB::rollback();
           return [
               'status' => false,
               'msg' => '同步汇率失败'
           ];
       }
    }

    /**
     * @note
     * 下载历史汇率
     * @since: 2019/3/28
     * @author: zt7837
     * @param:
     * @return: array
     */
    public function exportCurrencyHistory(Request $request,Excel $excel){
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $param = $request->all();
        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        $userName = Users::where(['user_id' => $user_id])->select('username')->first();
        $exportData = SettingCurrencyExchangeHistory::getHistoryData($param['currency_form_name'],$user_id);
        if($exportData->isEmpty()){
            return redirect()->back()->with('success','无数据');
        }
        $exportData = $exportData->toArray();
        foreach($exportData as $key => $val){
            $printInfo[$key]['currency_form_code'] = $val['currency_form_code'];
            $rate = SettingCurrencyExchangeHistory::where(['currency_form_code'=>$val['currency_form_code'],'user_id'=>$user_id])->orderBy('created_at','desc')->get(['created_at','exchange_rate']);
            $printInfo[$key]['old_rate'] = '';
            if(!$rate->isEmpty()) {
                $rate = $rate->toArray();
                //发生了旧汇率数据的覆盖 找到新汇率下标 k+1
                $cur_time = $val['created_at'];
                $k = array_search($cur_time,array_column($rate,'created_at'));
                if(($k !== false) && (isset($rate[$k+1]['exchange_rate']))) {
                    $printInfo[$key]['old_rate'] = $rate[$k+1]['exchange_rate'];
                }
            }
            $printInfo[$key]['exchange_rate'] = $val['exchange_rate'];
            $printInfo[$key]['created_man'] = $userName->username;
            $printInfo[$key]['updated_at'] = $val['updated_at'];

        }
        $arr = ['币种','原汇率','新汇率','操作人员','更新时间'];
        array_unshift($printInfo,$arr);
        $this->export($excel,$printInfo,$this->currencyTitle);
    }
}