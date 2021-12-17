<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/5
 * Time: 12:26
 */
namespace App\Console\Commands;
use App\Common\Common;
use App\Models\Category;
use App\Models\LogHelper;
use App\Models\WarehouseGoods;
use App\Models\WarehouseSecretkey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetProductCategory extends Command
{
    protected $signature = 'command:GetProductCategory';

    protected $description = '速贸商品分类同步';

    protected $catArr = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function packageData($data) {
        foreach($data as $key => $val) {
            $category['category_id'] = $val['category_id'];
            $category['parent_id'] = $val['parent_category_id'];
            $category['name'] = $val['category_name'];
            $category['type'] = Category::TYPE_WAREHOUSE;
            $category['created_at'] = date('Y-m-d H:i:s');
            $category['updated_at'] = date('Y-m-d H:i:s');
            $this->catArr[] = $category;
        }
    }

    public function addCategory() {
        Category::where(['type'=>Category::TYPE_WAREHOUSE])->delete();
        DB::beginTransaction();
        $len = count($this->catArr)/100;
        try {
            for($i=0;$i<$len;$i++) {
                $tempData = array_slice($this->catArr,100*$i,100);
                Category::insert($tempData);
            }
            DB::commit();
            echo '共'.count($this->catArr).'条数据录入完成'."\r\n";
        } catch (\Exception $e) {
            DB::rollback();
            Common::mongoLog($e,'速贸商品分类同步','失败',__FUNCTION__);
            echo '录入异常'.$e->getMessage();
        }
    }

    public function handle()
    {
        date_default_timezone_set('PRC');//设置时间为中国时区
        exec("chcp 65001");//设置命令行窗口为中文编码
        ignore_user_abort(true);
        set_time_limit(0);
        $param['page'] = 1;
        $param['pageSize'] = 100;
        try {
            $common = new Common();
            do {
                $re = $common->sendWarehouse('getCategory',$param);
                if($re['ask'] != 'Success') {
                    break;
                }
                if(isset($re['data']) && !empty($re['data'])) {
                    $this->packageData($re['data']);
                    if( $param['page'] == 1) {

                    }
                }
            }while(($re['ask'] == 'Success') && isset($re['nextPage']) &&  ($re['nextPage'] == 'true') && ($param['page']++));
            $this->addCategory();
        } catch (\Exception $e) {
            Common::mongoLog($e,'速贸商品分类同步','失败',__FUNCTION__);
            DB::rollback();
            echo $e->getMessage();
        }
    }

}