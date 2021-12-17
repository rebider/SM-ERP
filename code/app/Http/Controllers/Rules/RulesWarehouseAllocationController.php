<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/11
     * Time: 11:01
     */

    namespace App\Http\Controllers\Rules;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Http\Requests\IDNotEmpty;
    use App\Models\BaseModel;
    use App\Models\RulesWarehouseAllocation;
    use App\Http\Requests\WarehouseRulesCreateRequest;
    use App\Models\SettingWarehouse;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use App\Auth\Models\Menus;
    use Illuminate\Support\Facades\DB;

    class RulesWarehouseAllocationController extends Controller
    {
        public $menusModel = [];

        public function __construct()
        {
            $this->menusModel = new Menus();
        }

        /**
         * @return array
         * Note: 子菜单
         * Data: 2019/3/11 15:16
         * Author: zt8067
         */
        public function index(Request $request)
        {
            $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::RULES_ORDER_MENUS_ID);
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            $list = RulesWarehouseAllocation::getSummaryByPage($request,$user_id);
            return view('Rules.warehouseDispatch.index', compact($list))->with($responseData);
        }

        /**
         * @return array
         * Note: 获取仓库规则搜索列表
         * Data: 2019/3/13 11:00
         * Author: zt8067
         */
        public function lists(Request $request)
        {
            $msg = ['code' => 0, 'msg' => '', 'data' => '', 'count' => ''];
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            $lists = RulesWarehouseAllocation::getSummaryByPage($request,$user_id);
            if ($lists) {
                foreach($lists['data'] as &$item){

                    $warehouse_ids = $item['warehouse_ids'];
                    if (strpos($warehouse_ids, ',')) {
                        $warehouse_ids = explode(',',$warehouse_ids);
                    } else {
                        $warehouse_ids = (array)$warehouse_ids;
                    }
                    $selectWarehouse = SettingWarehouse::where('user_id',$user_id)->whereIn('id', $warehouse_ids)->get()->toArray();
                    $res = '';
                    foreach ($selectWarehouse as $v){
                        $res .= $v['warehouse_name'].",";
                    }
                    $res = rtrim($res,',');
                    unset($item['warehouse_ids']);
                    $item['warehouse'] = $res;


                }
                $msg = [
                    'msg'   => 'Success',
                    'data'  => $lists['data'],
                    'count' => $lists['total'],
                ];
            } else {
                $msg = [
                    'code' => '999',
                    'msg'  => 'Error',
                ];
            }
            return parent::layResponseData($msg);
        }

        /**
         * @return array
         * Note: 创建仓库规则
         * Data: 2019/3/11 17:35
         * Author: zt8067
         */
        public function createOrUpdate(WarehouseRulesCreateRequest $request)
        {
            $data = $request->all();
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            //新增规则
            $rulesWarehouseAllocation = new RulesWarehouseAllocation;
            if (empty($data['id'])) {
                $rulesWarehouseAllocation->created_man = $CurrentUser->userId;
                $rulesWarehouseAllocation->user_id = $user_id;
                $rulesWarehouseAllocation->name = $data['name'] ?? '';
                $rulesWarehouseAllocation->warehouse_ids = $data['ids'] ?? '';
                $rulesWarehouseAllocation->status = $data['status'] ?? RulesWarehouseAllocation::ON;
                $rulesWarehouseAllocation->save() ? $msg = ['code' => 1, 'msg' => '添加成功'] : $msg = ['code' => -1, 'msg' => '添加失败'];
            }//更新
            else {
                $up_data = [
                    'name'          => $data['name'] ?? '',
                    'warehouse_ids' => $data['ids'] ?? '',
                    'status'        => $data['status'] ?? '',
                ];
                $rulesWarehouseAllocation->where('user_id', $user_id)->where('id', $data['id'])->update($up_data) ? $msg = ['code' => 2, 'msg' => '更新成功'] : $msg = ['code' => -1, 'msg' => '更新失败'];
            }
            return parent::layResponseData($msg);
        }

        /**
         * @return array
         * Note: 创建仓库规则页面;
         * Data: 2019/3/13 15:35
         * Author: zt8067
         */
        public function createIndex(Request $request)
        {
            $data = $request->all();
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            if (!empty($data['id'])) {
                $RulesWarehouseAllocation = RulesWarehouseAllocation::where('user_id',$user_id)->find($data['id']);

                if (!empty($RulesWarehouseAllocation['warehouse_ids'])) {
                    if (strpos($RulesWarehouseAllocation['warehouse_ids'], ',')) {
                        $warehouse_ids = $RulesWarehouseAllocation['warehouse_ids'];
                        $warehouse_ids = explode(',',$warehouse_ids);
                    } else {
                        $warehouse_ids = (array)$RulesWarehouseAllocation['warehouse_ids'];
                    }


                    $warehouse = SettingWarehouse::where('user_id',$user_id)->whereNotIn('id', $warehouse_ids)->get()->toArray();
                    $selectWarehouse = SettingWarehouse::where('user_id',$user_id)->whereIn('id', $warehouse_ids)->get()->toArray();
                }
            }else {
                    $warehouse = SettingWarehouse::where('user_id',$user_id)->get()->toArray();
            }

                return view('Rules.warehouseDispatch.create', [
                    'RulesWarehouseAllocation' => $RulesWarehouseAllocation?? null,
                    'warehouse'                => $warehouse ?? '',
                    'selectWarehouse'          => $selectWarehouse?? null,
                    'id'                       => $data['id']?? ''
                ]);

        }
        /**
         * @return array
         * Note: 查看仓库规则
         * Data: 2019/3/16 15:35
         * Author: zt8067
         */
        public function read(IDNotEmpty $request)
        {

            $msg = ['code' => 0, 'msg' => '', 'data' => ''];
            $res = $request->all();
            $id = $res['id'];
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }

            $data = RulesWarehouseAllocation::where('user_id',$user_id)->find($id);
            if (empty($data)) {
                $msg =['code' => -1, 'msg' => 'Error'];
            }else{
                 strpos($data['warehouse_ids'],',') && $data['warehouse_ids'] = explode(',',$data['warehouse_ids']);
                 $msg =['code' => 1, 'msg' => 'Success','data' => $data];
            }
            return parent::layResponseData($msg);
        }

        /**
         * @return array
         * Note: 删除仓库规则
         * Data: 2019/3/12 10:00
         * Author: zt8067
         */
        public function delete(IDNotEmpty $request)
        {
            $msg = ['code' => 0, 'msg' => ''];
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            $res = RulesWarehouseAllocation::where(['user_id'=>$user_id,'id'=>$request->id])->delete();
            if ($res) {
                $msg['code'] = 1;
                $msg['msg'] = "删除成功！";
            } else {
                $msg['code'] = -1;
                $msg['msg'] = "删除失败！";
            }
            return parent::layResponseData($msg);
        }
    }