<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/16
     * Time: 8:59
     */

    namespace App\Http\Requests;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Models\SettingWarehouse;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;

    class WarehouseCreateRequest extends BaseRequest
    {
        protected $rules = [
            'type'         => 'required|integer',
            'facilitator'  => 'required',
            'disable'      => 'required|integer',
//            'phone_number' => 'regex:/^1[34578][0-9]{9}$/',
//            'qq'           => 'regex:/[1-9]([0-9]{5,11})/',
        ];
        protected $messages = [
            'warehouse_name.unique' => '已经存在该仓库名',
            'phone_number.regex'    => '必须为手机号格式',
            'qq.regex'              => '必须为QQ格式',
        ];

        function __construct(Request $request)
        {
            $data = $request->all();
            $CurrentUser = CurrentUser::getCurrentUser();
            if ($CurrentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $CurrentUser->userParentId;
            } else {
                $user_id = $CurrentUser->userId;
            }
            if (empty($data['id'])) {
                $this->rules['warehouse_name'] = ['warehouse_name' => 'unique:setting_warehouse'];
            } else {
                $this->rules['warehouse_name'] = ['required', Rule::unique('setting_warehouse')->where(function ($query) use ($user_id) {
                    $query->where(['user_id' => $user_id, 'type' => SettingWarehouse::CUSTOM_TYPE]);
                })->ignore($data['id']),
                ];
            }
            if (!empty($data['phone_number'])) {
                $this->rules['phone_number'] = 'regex:/^1[34578][0-9]{9}$/';
            }
            if (!empty($data['qq'])) {
                $this->rules['qq'] = 'regex:/[1-9]([0-9]{5,11})/';
            }
        }
    }