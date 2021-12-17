<?php
namespace App\Http\Controllers\Rules;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\Menus;
use App\Http\Controllers\Controller;
use App\Models\BaseModel;
use App\Models\Platforms;
use App\Models\RulesOrderSplitMerge;
use Illuminate\Http\Request;

class setMergeRulesController extends Controller
{
    public $menusModel = [];
    public function __construct()
    {
        $this->menusModel = new Menus();
    }

    /**
     * @note
     * 合并订单规则首页
     * @since: 2019/4/9
     * @author: zt7837
     * @param:
     * @return: array
     */
    public function index(){
        $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::RULES_ORDER_MENUS_ID);
        $platforms = Platforms::get();
        $responseData['platform'] = $platforms;
        return view('Rules/setMergeRules/index')->with($responseData);
    }

    /**
     * @note
     * 设置合并订单规则
     * @since: 2019/4/9
     * @author: zt7837
     * @param:
     * @return: array
     */
    public function addSetMergeRules(Request $request){
        $param = $request->all();

        $CurrentUser = CurrentUser::getCurrentUser();
        if($CurrentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $user_id = $CurrentUser->userParentId;
        }else{
            $user_id = $CurrentUser->userId;
        }

        $rulesOrderModel = new RulesOrderSplitMerge();
        if(!isset($param['value']) || !$param['value']){
            abort(404);
        }
        $rulesMergeObj = $rulesOrderModel->where(['type'=>$param['value'],'user_id'=>$user_id])->first();
        if($rulesMergeObj){
            $rulesMergeObj->status = $param['checked'];
            $result = $rulesMergeObj->save();
        }else{
            $rulesOrderModel->created_man = $user_id;
            $rulesOrderModel->type = $param['value'];
            $rulesOrderModel->status = $param['checked'];
            $rulesOrderModel->user_id = $user_id;
            $result = $rulesOrderModel->save();
        }
        if($result){
            return response()->json([
                'code' => 200,
                'msg' => '操作成功'
            ]);
        }else{
            return response()->json([
                'code' => 201,
                'msg' => '操作失败'
            ]);
        }
    }



}