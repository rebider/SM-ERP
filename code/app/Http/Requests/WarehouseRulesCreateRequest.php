<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/11
     * Time: 18:01
     */

    namespace App\Http\Requests;

    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;

    class WarehouseRulesCreateRequest extends BaseRequest
    {
        protected $rules = [
        ];
        protected $messages = [
            'trouble_rules_name.unique' => '已经存在该条规则名称',
        ];

        function __construct(Request $request)
        {
            $data = $request->all();
            if (empty($data['id'])) {
                $this->rules['trouble_rules_name'] = ['trouble_rules_name' => 'unique:rules_warehouse_trouble'];
            } else {
                $this->rules['trouble_rules_name'] = ['required',Rule::unique('rules_warehouse_trouble')->ignore($data['id'])];
            }
        }
    }