<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:57
 */

namespace App\Console\Commands;


use App\Common\Common;
use App\Http\Controllers\Exchange\ExchangeController;
use App\Models\SettingCurrencyExchange;
use App\Models\SettingCurrencyExchangeHistory;
use App\Models\SettingCurrencyExchangeMaintain;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Services\APIHelper;

class getSettingExchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getSettingExchange';

    private $inventory_info = [] ;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    /***
     * @note
     * 汇率同步
     * @since: 2019/3/27
     * @author: zt7387
     * @param:
     * @return: array
     */
    public function handle()
    {
        date_default_timezone_set('PRC');//设置时间为中国时区
        exec("chcp 65001");//设置命令行窗口为中文编码
        $task_name = $this->signature;
        $task_data ['start_time'] = time();
//        Task::setTask($task_name,$task_data);
        $urls = config('common.exchange_url');
        $url_arr = explode("\n" ,$urls[0] ) ;
        try {
            DB::beginTransaction();
            foreach ($url_arr as $value) {
                $API = new APIHelper($value);
                if (!$API->plate) {
                    DB::rollback();
                    echo $value.'数据有误'.date('Y-m-d H:i:s',time());
//                    info($value.'数据有误'.date('Y-m-d H:i:s',time())) ;
                }
                $re = $API->getAll();
                $exchangeArr = [];
                //组装数据
                if($re['description']){
                    foreach($re['description'] as $k=>$v){
                        if($v === '日元'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'JPY'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'JPY'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'JPY',
                                    'currency_form_name' => '日元',
                                    'ident_fier' => 'J￥',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }else if($v === '美元'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'USD'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'USD'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'USD',
                                    'currency_form_name' => '美元',
                                    'ident_fier' => '$',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }else if($v === '澳大利亚元'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'AUD'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'AUD'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'AUD',
                                    'currency_form_name' => '澳元',
                                    'ident_fier' => 'AUD',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }else if($v === '韩国元'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'KRW'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'KRW'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'KRW',
                                    'currency_form_name' => '韩元',
                                    'ident_fier' => '₩',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }else if($v === '欧元'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'EUR'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'EUR'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'EUR',
                                    'currency_form_name' => '欧元',
                                    'ident_fier' => 'EUR',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }else if($v === '港币'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'HKD'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'HKD'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'HKD',
                                    'currency_form_name' => '港币',
                                    'ident_fier' => 'HK$',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }else if($v === '英镑'){
                            $rate = $re['description'][$k+4] * 0.01;
                            $rate = $re['description'][$k+4] * 0.01;
                            $cnyRe = SettingCurrencyExchange::where(['currency_form_code'=>'GBP'])->first();
                            if($cnyRe){
                                SettingCurrencyExchange::where(['currency_form_code'=>'GBP'])->update(['exchange_rate'=>$rate]);
                            }else{
                                $exchangeArr = [
                                    'currency_to_code' => 'CNY',
                                    'currency_to_name' => '人民币',
                                    'currency_form_code' => 'GBP',
                                    'currency_form_name' => '英镑',
                                    'ident_fier' => '£',
                                    'is_show' => SettingCurrencyExchange::ISSHOW,
                                    'exchange_rate' => $rate
                                ];
                                SettingCurrencyExchange::insert($exchangeArr);
                            }
                        }
                    }
                    unset($API);
                    echo '汇率同步成功';
                }else{
                    DB::rollback();
//                    info('爬取汇率失败!'.date('Y-m-d H:i:s',time())) ;
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            echo $exception->getMessage();
            Common::mongoLog($exception);
//            info('同步汇率失败!'.$exception->getMessage().date('Y-m-d H:i:s',time())) ;
        };
    }
}