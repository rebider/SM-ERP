<?php
/**
 * Created by OuYangWei.
 * User: zt12779
 * Date: 2019/4/18
 * Time: 13:16
 */

namespace App\Console\Commands;

use App\Common\Common;
use App\Models\SettingWarehouse;
use App\Models\WarehouseSecretkey;
use Illuminate\Console\Command;
use \Exception;

class UpdateWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateWarehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 更新仓库
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        print "开始执行更新";
        do {
            try {
                $keychains = WarehouseSecretkey::all();
                if (empty($keychains)) {
                    exit();
                }
                $common = new Common;
                $keychains = $keychains->toArray();
                foreach ($keychains as $key => $val) {
                    $account = [
                        'appToken' => $val['appToken'],
                        'appKey' => $val['appKey'],
                    ];
                    $data = [
                        'pageSize' => '',
                        'page' => 1,
                    ];
                    $get_warehouse_result = $common->sendWarehouse('getWarehouse', $data, $account);
                    if (empty($get_warehouse_result)) {
                        continue;
                    }
                    //appToken/appKey非法
                    if ($get_warehouse_result['ask'] == 'Failure') {
                        continue;
                    }
                    if (empty(count($get_warehouse_result['data']))) {
                        continue;
                    }
                    //获取已存在的仓库
                    $existWarehouse = SettingWarehouse::where('user_id', $val['user_id'])->get();
                    if (!empty($existWarehouse)) {
                        $existWarehouse = $existWarehouse->toArray();
                        $existWarehouse_inSet = array_column($existWarehouse,
                            'warehouse_name', 'warehouse_code');
                    }
                    //存储可用仓库
                    $warehouse = [];
                    foreach ($get_warehouse_result['data'] as $insideKey => $insideVal) {
                        //如果仓库已存在，但是名字变更了，那就更新这条数据；否则新增
                        if (isset($existWarehouse_inSet) && array_key_exists($insideVal['warehouse_code'], $existWarehouse_inSet)) {
                            if ($existWarehouse_inSet[$insideVal['warehouse_code']] != $insideVal['warehouse_name']) {
                                $whereMap = [
                                    'warehouse_code' => $insideVal['warehouse_code'],
                                    'user_id' => $val['user_id']
                                ];
                                $updateSingle = [
                                    'warehouse_name' => $insideVal['warehouse_name'],
                                ];
                                $updateSingleResult = SettingWarehouse::where($whereMap)->update($updateSingle);
                                if (!$updateSingleResult) {
                                    $results['msg'] = '同步仓库失败';
                                    break;
                                }
                            }
                        } else {
                            $warehouse[] = [
                                'created_man' => 1,
                                'type' => 1,
                                'warehouse_name' => $insideVal['warehouse_name'],
                                'warehouse_code' => $insideVal['warehouse_code'],
                                'user_id' => $val['user_id'],
                                'facilitator' => '株式会社 Dream Works',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                    if (!empty(count($warehouse))) {
                        $insertResult = SettingWarehouse::insert($warehouse);
                        if (!$insertResult) {
                            throw new Exception('系统更新仓库失败，用户user_id为' . $val['user_id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Common::mongoLog($e);
            } catch (\Error $e) {
                Common::mongoLog($e);
            }
        } while (0);

    }
}
