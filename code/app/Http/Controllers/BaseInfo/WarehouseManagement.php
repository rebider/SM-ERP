<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/15
     * Time: 15:37
     */

    namespace App\Http\Controllers\BaseInfo;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\Menus;
    use App\Common\Common;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\AuthorizationRequest;
    use App\Http\Requests\IDNotEmpty;
    use App\Http\Requests\WarehouseCreateRequest;
    use App\Http\Services\Warehouse\WarehouseHandle;
    use App\Models\BaseModel;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseSecretkey;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;

    class warehouseManagement extends Controller
    {
        public $menusModel = [];

        public function __construct()
        {
            $this->menusModel = new Menus();
        }

        /**
         * @return array
         * Note: 子菜单
         * Data: 2019/3/15 15:35
         * Author: zt8067
         */
        public function index()
        {
            $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::RULES_ORDER_MENUS_ID);
            return view('BaseInfo.warehouseManagement.index', $responseData);
        }

        /**
         * @return array
         * Note: 获取仓库规则搜索列表
         * Data: 2019/3/15 16:00
         * Author: zt8067
         */
        public function lists(Request $request)
        {
            $data = ['code' => 0, 'msg' => '', 'data' => '', 'count' => ''];
            $list = SettingWarehouse::getSummaryByPage($request);
            if ($list) {
                $data = [
                    'msg'   => 'Success',
                    'data'  => $list['data'],
                    'count' => $list['total'],
                ];
            } else {
                $data = [
                    'code' => '999',
                    'msg'  => 'Error',
                ];
            }


            return parent::layResponseData($data);
        }

        /**
         * @return array
         * Note: 获取仓库列表
         * Data: 2019/3/11 15:35
         * Author: zt8067
         */
        public function getSettingWarehouseList()
        {
            $data = ['code' => 0, 'msg' => '', 'data' => ''];
            $hides = ['created_man', 'type', 'facilitator', 'charge_person', 'phone_number', 'qq', 'address', 'disable'];
            $warehouses = SettingWarehouse::getCompleteWarehouse($hides)->toArray();
            if (empty($warehouses)) {
                $data = ['code' => -1, 'msg' => 'Error'];
            } else {
                $data = ['code' => 1, 'msg' => 'Success', 'data' => $warehouses];
            }
            return response()->json($data);
        }

        /**
         * @return array
         * Note: 创建仓库页面
         * Data: 2019/3/15 16:00
         * Author: zt8067
         */
        public function createIndex(IDNotEmpty $request)
        {
            $id = $request->get('id','');
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            $id && $warehouse = SettingWarehouse::where(['id'=>$id,'user_id'=>$user_id])->firstOrFail()->toArray();
            return view('BaseInfo.warehouseManagement.create',[
                'warehouse' => $warehouse ?? ''
                ]);
        }

        /**
         * @return boolean
         * Note: 创建仓库
         * Data: 2019/3/16 08:00
         * Author: zt8067
         */
        public function createOrUpdate(Request $request)
        {
            $data = $request->all();
            if ($data['type'] == 2) {
                $rules = [
                    'type'         => 'required|integer',
                    'facilitator'  => 'required',
                    'disable'      => 'required|integer',
                ];
                $messages = [
                    'warehouse_name.unique' => '已经存在该仓库名',
                    'phone_number.regex'    => '联系电话必须为手机号格式',
                    'qq.regex'              => '必须为QQ格式',
                ];
                $attr = [
                    'warehouse_name' => '仓库名称',
                    'facilitator' => '服务商名称',
                    'charge_person' => '负责人',
                    'address' => '地址',
                    'phone_number'   => '联系电话',
                    'qq.regex'       => 'QQ',
                ];
                $CurrentUser = CurrentUser::getCurrentUser();
                if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                    //主账号id
                    $user_id = $CurrentUser->userParentId;
                } else {
                    $user_id = $CurrentUser->userId;
                }

                if (!empty($data['id'])) {
                    $rules['warehouse_name'] = ['warehouse_name' => Rule::unique('setting_warehouse')->where(function($query) use ($user_id,$data){
                        $query->where(['user_id' => $user_id,'warehouse_name'=>trim($data['warehouse_name']),'type' => SettingWarehouse::CUSTOM_TYPE]);
                        $query->where('id','<>',$data['id']);
                    })];
                } else {
                    $rules['warehouse_name'] = ['required', Rule::unique('setting_warehouse')->where(function ($query) use ($user_id,$data) {
                        $query->where(['user_id' => $user_id,'warehouse_name'=>trim($data['warehouse_name']),'type' => SettingWarehouse::CUSTOM_TYPE]);
                    })];
                }
                if (!empty($data['phone_number'])) {
                    $rules['phone_number'] = 'regex:/^1[34578][0-9]{9}$/';
                }
                if (!empty($data['qq'])) {
                    $rules['qq'] = 'regex:/[1-9]([0-9]{5,11})/';
                }

                $validator = Validator::make($data, $rules, $messages,$attr);
                if ($validator->fails()) {
                    $errors = $validator->errors()->first();
                    $results = ['code' => -1, 'msg' => $errors];
                    return parent::layResponseData($results);
                }
            }
            
            $params = $request->all();
            $results = WarehouseHandle::createOrUpdate($params);
            return parent::layResponseData($results);
        }


        /**
         * @return boolean
         * Note: 添加仓库授权token key
         * Data: 2019/4/2 15:00
         * Author: zt8067
         * Update time: 2019/4/19 10:28
         * editor zt12779
         */
        public function authorization(AuthorizationRequest $request)
        {
            $params = $request->all();
            $WarehouseHandle =new WarehouseHandle;
            $results = $WarehouseHandle->getWarehouse($params);
            return parent::layResponseData($results);
        }

        }