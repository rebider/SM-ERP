<?php

namespace App\Http\Controllers;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Controllers\BaseAuthController;
use App\Auth\Models\Menus;
use App\Http\Controllers\Goods\AmazonOnlineGoodsController;
use App\Http\Controllers\Goods\RakutenOnlineGoodsController;
use App\Models\GoodsOnlineAmazon;
use App\Models\GoodsOnlineRakuten;
use App\Models\Orders;
use App\Models\OrdersQuantityRecord;
use App\Models\OrdersTroublesRecord;
use App\Models\PurchaseOrders;
use App\Models\SettingNotices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HomeController extends BaseAuthController
{

    /**
     * 主页
     * @author zt6768
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $currentUser = CurrentUser::getCurrentUser();
        $userNavigation = $currentUser->userNavigation;
        $home = reset($userNavigation);
        $url = $request->get('url');

        if($url && $url != $home) {
            $request->session()->put('url', $url);
            return redirect('/');
        }
        return view('new_base', [
            'userNavigation' => $userNavigation,
            'home' => $home,
            'userName' => $currentUser->userName,
        ]);
    }

    /**
     * 获取便捷菜单
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserMenu()
    {
        $currentUser = CurrentUser::getCurrentUser();
        $userNavigation = $currentUser->userNavigation;
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $troubleOrder = Orders::getOrderShortcutMenu($user_id);
        foreach ($userNavigation as $key => $val) {
            if ($val['id'] == 4) {
                $userNavigation[$key]['_child'] = array_merge(array($troubleOrder), $val['_child']);
            }
        }
        return $this->layResponseData(['code' => 1, 'msg' => '', 'data' => $userNavigation]);
    }

    /**
     * @param Request $request
     * @return $this
     * Note: 首页数据
     * Data: 2019/6/17 10:17
     */
    public function home (Request $request) {
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        //公告
        $notices = SettingNotices::where('status',SettingNotices::ON_STATUS)
            ->select('id','title','created_at','important')->orderByDesc('id')->paginate(100);
        if (empty($notices)){
            $notices = null;
        }else{
            $notices = $notices->toArray();
        }

        //图表
        $orderSummary = OrdersQuantityRecord::getOrderSummary($user_id);
        //待处理问题订单
        $orderProblemCounts = OrdersTroublesRecord::getOrderProblemCount($user_id);
        //配货无物流
        $logisticsMissing = Orders::where('problem', Orders::D_PROBLEM)->where('user_id',$user_id)->count();
        //配货无仓库
        $warehouseMissing = Orders::where('problem', Orders::C_PROBLEM)->where('user_id',$user_id)->count();
        //在途采购
        $purchase = PurchaseOrders::where('status', PurchaseOrders::ON_THE_WAY)->where('user_id',$user_id)->count();

        return view('new_home')->with([
            'userName' => $currentUser->userName,
            'company_name' => $currentUser->companyName,
            'notices' => $notices,
            'orderSummary' => $orderSummary,
            'orderProblemCounts' => $orderProblemCounts,
            'currentMonth' => date('m'),
            'logisticsMissing' => $logisticsMissing,
            'warehouseMissing' => $warehouseMissing,
            'purchase' => $purchase
        ]);
    }
}
