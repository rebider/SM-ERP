<?php

namespace App\Http\Controllers\Procurement;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Models\ProcurementPlans;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * @var Suppliers 供应商
     */
    protected $Supplier;

    public function __construct()
    {
        $this->Supplier = new Suppliers();

    }

    /**
     * @description 供应商管理首页
     * @author zt7927
     * @date 2019/4/16 10:44
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        //侧边栏
        $responseData['shortcutMenus'] = ProcurementPlans::getProcurementShortcutMenu();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //供应商
        $suppliers = $this->Supplier->getAllSuppliers($user_id);

        return view('Procurement.Supplier.index', compact('suppliers'))->with($responseData);
    }

    /**
     * @description 供应商搜索
     * @author zt7927
     * @date 2019/4/16 11:38
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function supplierIndexSearch(Request $request)
    {
        $pageIndex = $request->get('page', 1);
        $pageSize = $request->get('limit', 20);
        $params = $request->get('data', []);
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $params ['user_id'] = $user_id;
        $collection = $this->Supplier->search($params);   //查询
        $re['count'] = $collection->count();
        $re['data'] = $collection->skip(($pageIndex - 1) * $pageSize)->take($pageSize)->get()->toArray();

        return parent::layResponseData($re);
    }

    /**
     * @description 新增供应商页面
     * @author zt7927
     * @date 2019/4/16 14:07
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addSupplier()
    {
        return view('Procurement.Supplier.addSupplier');
    }

    /**
     * @description 保存新增供应商
     * @author zt7927
     * @date 2019/4/16 14:38
     * @param Request $request
     * @return array
     */
    public function createSupplier(Request $request)
    {
        $params = $request->input('params', []);
        if ($params['name'] && ($params['name'] !== '') && is_numeric($params['tel_no']) && ($params['tel_no'] !== '')
            && $params['linkman'] && ($params['linkman'] !== '')){
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }
            $suppier = $this->Supplier->where('name', $params['name'])->where('user_id',$user_id)->first();
            if (!$suppier){
                $insertArr = [];
                $insertArr['created_man'] = $currentUser->userId;
                $insertArr['user_id']     = $user_id;
                $insertArr['name']        = $params['name'];
                $insertArr['linkman']     = $params['linkman'];
                $insertArr['tel_no']      = $params['tel_no'];
                $insertArr['email']       = $params['email'];
                $insertArr['address']     = $params['address'];
                $insertArr['created_at']  = date('Y-m-d H:i:s');
                $insertArr['updated_at']  = date('Y-m-d H:i:s');

                $re = $this->Supplier->insertGetId($insertArr);
                if ($re){
                    return [
                        'status' => 1,
                        'msg'    => '添加成功'
                    ];
                }
                return [
                    'status' => 0,
                    'msg'    => '添加失败'
                ];
            }
            return [
                'status' => 0,
                'msg'    => '供应商名称:'.$params['name'].' 已存在'
            ];
        }
        return [
            'status' => 0,
            'msg'    => '添加失败'
        ];
    }

    /**
     * @description 编辑供应商页面
     * @author zt7927
     * @date 2019/4/16 14:46
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editSupplier($id)
    {
        if (empty($id)) {
            abort(404);
        }
        $suppliers = $this->Supplier->getSupplierDataById($id);

        return view('Procurement.Supplier.editSupplier', compact('suppliers'));
    }

    /**
     * @description 保存编辑供应商
     * @author zt7927
     * @date 2019/4/16 15:08
     * @param Request $request
     * @return array
     */
    public function updateSupplier(Request $request)
    {
        $params = $request->input('params', []);
        if ($params['name'] && ($params['name'] !== '') && is_numeric($params['tel_no']) && ($params['tel_no'] !== '')
            && $params['linkman'] && ($params['linkman'] !== '')){
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                //主账号id
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }
            $suppierId = $this->Supplier->where('name', $params['name'])->where('user_id',$user_id)->pluck('id')->first();
            if (!$suppierId || ($suppierId == $params['supplier_id'])){
                $updateArr = [];
                $updateArr['created_man'] = $currentUser->userId;
                $insertArr['user_id']     = $user_id;
                $updateArr['name']        = $params['name'];
                $updateArr['linkman']     = $params['linkman'];
                $updateArr['tel_no']      = $params['tel_no'];
                $updateArr['email']       = $params['email'];
                $updateArr['address']     = $params['address'];
                $updateArr['updated_at']  = date('Y-m-d H:i:s');

                $re = $this->Supplier->updatedArr($updateArr, $params['supplier_id']);
                if ($re){
                    return [
                        'status' => 1,
                        'msg'    => '更新成功'
                    ];
                }
                return [
                    'status' => 0,
                    'msg'    => '更新失败'
                ];
            }
            return [
                'status' => 0,
                'msg'    => '供应商名称:'.$params['name'].' 已存在'
            ];
        }
        return [
            'status' => 0,
            'msg'    => '更新失败'
        ];
    }

    /**
     * @description 启用禁用供应商
     * @author zt7927
     * @date 2019/4/16 16:03
     * @param Request $request
     * @return array
     */
    public function changeStatus(Request $request)
    {
        $id = $request->input('id', '');
        if (isset($id) && $id > 0) {
            $re = $this->Supplier->changeStatus($id);
            return [
              'status' => $re['status'],
              'msg'    => $re['msg']
            ];
        }
        return [
          'status' => 0,
          'msg'    => '操作失败'
        ];
    }
}
