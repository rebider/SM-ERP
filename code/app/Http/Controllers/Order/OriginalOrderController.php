<?php
/**
 * Note: 原始订单控制器
 * Author: zt12779
 * Date: 2019/04/22
 * @package App\Http\Controllers\Order
 */

namespace App\Http\Controllers\Order;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Models\RolesShops;
use App\Common\Common;
use App\Models\CodeInfo;
use App\Models\Goods;
use App\Models\Orders;
use App\Models\OrdersBillPayments;
use App\Models\OrdersLogs;
use App\Models\OrdersOriginal;
use App\Models\OrdersOriginalProducts;
use App\Models\OrdersProducts;
use App\Models\OrdersQuantityRecord;
use App\Models\Platforms;
use App\Models\RulesOrderTroubleType;
use App\Models\SettingCountry;
use App\Models\SettingCurrencyExchange;
//use App\Models\SettingCurrencyExchangeMaintain;
use App\Models\SettingCurrencyExchangeMaintain;
use App\Models\SettingLogistics;
use App\Models\SettingLogisticsWarehouses;
use App\Models\SettingShops;
use App\Models\SettingWarehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;

class OriginalOrderController extends Controller
{
    /**
     * @var 便捷菜单
     */
    public $shortcutMenus = [];

    public $orderFileName = '订单详情';
    /**
     * @var (付款单)原始订单乐天
     */
    const ORDERS_ORIG_RAKUTEN = 1;
    /**
     * @var (付款单)原始订单亚马逊
     */
    const ORDERS_ORIG_AMAZON = 2;
    /**
     * @var 平台订单的订单类型，2为手工创建
     */
    const ORDERS_TYPE = 2;
    /**
     * @var order表的订单类型，1为平台订单
     */
    const PLATFORM_SELF = 1;
    /**
     * @var 关联平台表ID、亚马逊
     */
    const PLATFORM_AMAZON = 1;
    /**
     * @var 关联平台表ID、乐天
     */
    const PLATFORM_RAKUTEN = 2;
    /**
     * @var 订单类型，平台
     */
    const PLATFORM_ORDER_SELF = 3;

    /**
     * @var 订单匹配失败
     */
    const ORDER_MATCH_FAIL = 3;

    /**
     * @var 订单来源：手工订单
     */
    const ORDER_SOURCE_MANUAL = 2;

    /**
     * @var 订单匹配状态：成功
     * OriginalOrderController constructor.
     */
    const ORDER_MATCH_SUCCESS = 2;

    /**
     * @var 订单编号生成：CW_ERP_订单
     * OriginalOrderController constructor.
     */
    const CODE_INFO_ORDER = 1;

    public function __construct()
    {
        if (!isset($currentUser->userAccountType)) {
            redirect('/logout');
        }
    }

    /**
     * 原始订单列表页
     * Author: zt12779
     * Date: 2019/04/22
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function originalOrderIndex(Request $request)
    {
        //需要展示 左侧数据 1.异常数据条数 2.快捷菜单
        //1.客户信息
        //2.问题类型
        //3.来源平台：
        //4.来源平台：
        //5.物流方式:
        //6.仓库
        //便捷菜单
//        $responseData ['shortcutMenus'] = json_encode(Orders::getOrderShortcutMenu());
        $responseData ['mapping_status'] = $request->get('mapping_status', 0);
        //问题类型
        $responseData ['troubles'] = RulesOrderTroubleType::getTroubleType();

        //店铺 获取客户 状态正常的店铺
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $param ['user_id'] = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'], true);
                $responseData ['shops'] = SettingShops::getShopsByShopsId($shopsId);
            } else {
                $responseData ['shops'] = [];
            }
        } else {
            $param ['user_id'] = $currentUser->userId;
            $responseData ['shops'] = SettingShops::getShopsByUserId($param ['user_id']);
        }

        //平台
        $responseData ['platforms'] = Platforms::getAllPlat();
        //物流方式
        $responseData ['logistics'] = SettingLogistics::getAllLogisticsByUserId($param ['user_id']);
        //仓库
        $responseData ['warehouses'] = SettingWarehouse::getAllWarehousesByUserId($param ['user_id']);
        return view('Order/originalOrderIndex')->with($responseData);
    }

    /**
     * 原始订单数据列表检索
     * Author: zt12779
     * Date: 2019/04/22
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderIndexSearch(Request $request)
    {
        // 仓库 跟踪号 派送运费 物流方式 配货成功才有记录
        $data = $request->all();
        $currentUser = CurrentUser::getCurrentUser();

        $offset = isset($data['page']) ? $data['page'] : 1;
        $limit = isset($data['limit']) ? $data['limit'] : 20;

        //匹配状态
        if (isset($data ['match_status']) && !empty($data ['match_status'])) {
            $param['match_status'] = $data ['match_status'];
        } else {
            $param['match_status'] = (isset($data['data']['match_status']) && !empty($data['data']['match_status']))
                ? $data['data']['match_status'] : '';
        }

        //订单来源
        $param['order_source'] = (isset($data['data']['order_source']) && !empty($data['data']['order_source']))
            ? $data['data']['order_source'] : '';

        //平台名称
        $param['platform_name'] = (isset($data['data']['platform_name']) && !empty($data['data']['platform_name']))
            ? $data['data']['platform_name'] : '';

        //来源店铺
        $param['source_shop'] = (isset($data['data']['source_shop']) && !empty($data['data']['source_shop']))
            ? $data['data']['source_shop'] : '';

        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $permissionParam ['user_id'] = $currentUser->userParentId;
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'], true);
                if ($param['source_shop']) {
                    if (!in_array($param['source_shop'], $shopsId)) {
                        //未配置店铺 直接响应空
                        $result ['code'] = 400;
                        $result ['msg'] = '请求参数异常';
                        return parent::layResponseData($result);
                    }
                }
                //店铺id
                $permissionParam ['source_shop'] = $shopsId;
            } else {
                //未配置店铺 直接响应空
                $result ['code'] = 0;
                $result ['msg'] = '未配置店铺权限';
                return parent::layResponseData($result);
            }
        } else {
            $permissionParam ['user_id'] = $currentUser->userId;
        }

        //仓库id
        $param['warehouse_id'] = (isset($data['data']['warehouse_id']) && !empty($data['data']['warehouse_id']))
            ? $data['data']['warehouse_id'] : '';

        //订单号
        $param['order_number'] = (isset($data['data']['order_number']) && !empty($data['data']['order_number']))
            ? $data['data']['order_number'] : '';

        //来源订单号
        $param['platform_order'] = (isset($data['data']['platform_order']) && !empty($data['data']['platform_order']))
            ? $data['data']['platform_order'] : '';

        //时间类型 1: 下单时间 2:创建时间 3:付款时间 3:发货时间
//        $param['times_type'] = (isset($data['data']['times_type']) &&  !empty($data['data']['times_type']))
//            ? $data['data']['times_type'] : '';

        //下单开始时间
        $param['start_date'] = (isset($data['data']['start-date']) && !empty($data['data']['start-date']))
            ? $data['data']['start-date'] : '';

        //下单结束时间
        $param['end_date'] = (isset($data['data']['end-date']) && !empty($data['data']['end-date']))
            ? date('Y-m-d 23:59:59', strtotime($data['data']['end-date'])) : '';
        $result = OrdersOriginal::getOrdersDatas($param, $permissionParam, $offset, $limit);
        return parent::layResponseData($result);
    }

    /**
     * 导入Excel数据
     * Author: zt12779
     * Date: 2019/04/22
     * @param Request $request
     * @param Excel $excel
     * @return \Illuminate\Http\JsonResponse
     * @throws \League\Flysystem\Exception
     */
    public function importOrder(Request $request, Excel $excel)
    {
        DB::beginTransaction();
        try {
            //成功录入数
            $success = 0;
            //错误信息
            $errMsg = [];
            //返回状态
            $result['code'] = -1;

            //获取上传的文件
            $file = $request->file('import');
            if (empty($file)) {
                $result['msg'] = '请选择上传文件';
                return parent::layResponseData($result);
            }
            $originalExtension = $file->getClientOriginalExtension();
            $allowedExtension = ['xls', 'xlsx', 'cvs'];
            if (!in_array($originalExtension, $allowedExtension)) {
                $result['msg'] = '只允许xls(x)和cvs格式的文件上传';
                return parent::layResponseData($result);
            }
            //账户类型
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
                $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'], true);
                    if (empty($shopsId)) {
                        $result['msg'] = '未配置店铺权限';
                        return parent::layResponseData($result);
                    }
                } else {
                    $result['msg'] = '未配置店铺权限';
                    return parent::layResponseData($result);
                }
            } else {
                $user_id = $currentUser->userId;
            }


            //获取上传文件的数据
            $originalData = $this->import($excel, $file->getRealPath());
            //首行字段提示
            $requiredField = [
                0 => ['name' => '电商单号', 'status' => 'required', 'default' => null, 'field' => 'platform_order'],
                1 => ['name' => '运费', 'status' => 'required', 'default' => null, 'field' => 'freight'],
                2 => ['name' => '订单总金额', 'status' => 'required', 'default' => null, 'field' => 'order_price'],
                3 => ['name' => '币种', 'status' => null, 'default' => 'JPY', 'field' => 'currency'],
                4 => ['name' => '来源平台', 'status' => 'required', 'default' => null, 'field' => 'platform_name'],
                5 => ['name' => '来源店铺', 'status' => 'required', 'default' => null, 'field' => 'source_shop_name'],
                6 => ['name' => '收件人', 'status' => 'required', 'default' => null, 'field' => 'addressee_name'],
                7 => ['name' => '国家二字码', 'status' => 'required', 'default' => null, 'field' => 'country'],
                8 => ['name' => '城市', 'status' => 'required', 'default' => null, 'field' => 'city'],
                9 => ['name' => '省/州', 'status' => 'required', 'default' => null, 'field' => 'province'],
                10 => ['name' => '地址1', 'status' => 'required', 'default' => null, 'field' => 'addressee1'],
                11 => ['name' => '地址2', 'status' => null, 'default' => null, 'field' => 'addressee2'],
                12 => ['name' => '邮编', 'status' => 'required', 'default' => null, 'field' => 'zip_code'],
                13 => ['name' => '电话', 'status' => null, 'default' => null, 'field' => 'phone'],
                14 => ['name' => '手机', 'status' => null, 'default' => null, 'field' => 'mobile_phone'],
                15 => ['name' => 'E-mail', 'status' => null, 'default' => null, 'field' => 'addressee_email'],
                16 => ['name' => '订单备注', 'status' => null, 'default' => null, 'field' => 'mark'],
            ];
            //提取字段提示
            $headLine = array_shift($originalData);
            //总必要字段长为20列，缺一不可
            if (count($headLine) < 20) {
                $errMsg = "表格字段不对，请按照模板给出的字段进行上传";
            }

            $countries = SettingCountry::all()->toArray();
            $country = array_column($countries, 'id', 'country_code');
            $countryInName = array_column($countries, 'country_name', 'id');

            //国家二字码信息
            $countryArr = SettingCountry::get()->pluck('country_code')->toArray();
            //币种编码信息
            $currencyArr = SettingCurrencyExchange::get()->pluck('currency_form_code')->toArray();

            //循环处理提交上来的参数
            foreach ($originalData as $key => $val) {
                $row = $key + 1;
                //产品参数
                $productParam = [];
                //原始订单参数
                $orderParam = [];

                $emptyBlock = 0;
                foreach ($val as $singleRow) {
                    if (empty($singleRow)) {
                        $emptyBlock++;
                    }
                }
                if ($emptyBlock >= 262) {
                    continue;
                }

                //以参数模板为基准循环处理数据
                foreach ($requiredField as $fieldKey => $fieldValue) {

                    //如果不是非必要的字段空缺了
                    if (!isset($val[$fieldKey]) && !in_array($fieldKey, [3, 11, 13, 14, 15, 16])) {
                        $errMsg[$row][] = "第{$row}行，缺少必要参数：【{$fieldValue['name']}】";
                        continue;
                    }
                    //如果是币种、跳过；
                    if ($fieldKey != 3) {
                        $orderParam[$fieldValue['field']] = $val[$fieldKey];
                    }
                    //如果是币种，判断是否有缺省值
                    if ($fieldKey == 3) {
                        $orderParam[$fieldValue['field']] = empty(trim($val[$fieldKey])) ? 'JPY' : $val[$fieldKey];
                    }

                }
                if (isset($errMsg[$row])) {
                    unset($orderParam);
                    continue;
                }

                strtoupper($orderParam['platform_name']) == 'AMAZON' && $plat_id = self::PLATFORM_AMAZON;
                strtoupper($orderParam['platform_name']) == 'RAKUTEN' && $plat_id = self::PLATFORM_RAKUTEN;
                if (!isset($plat_id)) {
                    $errMsg[$row][] = "第{$row}行，不存在的平台";
                    unset($orderParam);
                    continue;
                }

                //查找店铺ID
                $shopWhereMap = [
                    'user_id' => $user_id,
                    'plat_id' => $plat_id,
                    'shop_name' => $orderParam['source_shop_name']
                ];
                $shop = SettingShops::where($shopWhereMap)->first();
                if (!$shop) {
                    $errMsg[$row][] = "第{$row}行，未找到该店铺";
                    unset($orderParam);
                    continue;
                }
                if (isset($shopsId) && !in_array($shopsId)) {
                    $errMsg[$row][] = "第{$row}行，店铺权限异常";
                    unset($orderParam);
                    continue;
                }
                $platformOrderWhereMap = [
                    'user_id' => $user_id,
//                    'platform' => $plat_id,
                    'platform_order' => $orderParam['platform_order']
                ];
                $platformOrder = OrdersOriginal::where($platformOrderWhereMap)->first();
                if ($platformOrder) {
                    $errMsg[$row][] = "第{$row}行，电商单号已存在";
                    unset($orderParam);
                    continue;
                }

                if ((float)$orderParam['order_price'] < 0) {
                    $errMsg[$row][] = "第{$row}行，订单总金额（{$orderParam['order_price']}）非法";
                    continue;
                }
                if ((float)$orderParam['freight'] < 0) {
                    $errMsg[$row][] = "第{$row}行，订单运费（{$orderParam['freight']}）非法";
                    continue;
                }
                if (isset($orderParam['country']) && !preg_match('/^[a-zA-Z]*$/', $orderParam['country'])) {
                    $errMsg[$row][] = "第{$row}行，请填写国家英文简称，如：CN（中国）。该行的国家（{$orderParam['country']}）不符合要求";
                    continue;
                }

                if (!in_array($orderParam['country'],$countryArr)) {
                    $errMsg[$row][] = "第{$row}行，不存在的国家代码（{$orderParam['country']}）";
                    continue;
                }

                if (!in_array($orderParam['currency'],$currencyArr)) {
                    $errMsg[$row][] = "第{$row}行，不存在的币种代码（{$orderParam['currency']}）";
                    continue;
                }

                $orderParam['country'] = strtoupper($orderParam['country']);
                $orderParam['order_number'] = CodeInfo::getACode(self::CODE_INFO_ORDER);
                $orderParam['source_shop'] = $shop->id;
                $orderParam['created_man'] = $currentUser->userId;
                $orderParam['platform'] = $plat_id;
                $orderParam['currency_freight'] = $orderParam['currency'];
                $orderParam['order_time'] = date('Y-m-d H:i:s');
                $orderParam['payment_time'] = date('Y-m-d H:i:s');
                $orderParam['created_at'] = date('Y-m-d H:i:s');
                $orderParam['updated_at'] = date('Y-m-d H:i:s');
                $orderParam['user_id'] = $user_id;
                $orderParam['match_status'] = self::ORDER_MATCH_SUCCESS;
                $orderParam['order_source'] = self::ORDER_SOURCE_MANUAL;
                //币种逻辑错误
//                $rate = SettingCurrencyExchange::where('currency_form_code', $orderParam['currency'])->first();
                //如果客户配置相关币种信息取该客户币种
                $exchange_rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($orderParam['currency'],$user_id);
                $orderParam['rate'] = $exchange_rate;
                if (isset($orderParam['mark']) && empty($orderParam['mark'])) {
                    unset($orderParam['mark']);
                }

                $newLineId = OrdersOriginal::insertGetId($orderParam);
                if (!$newLineId) {
                    $errMsg[$row][] = "第{$row}行，数据插入失败";
                    continue;
                }

                //付款单
                $billOption['created_man'] = $user_id;
                $billOption['order_id'] = $newLineId;
                $billOption['amount'] = $orderParam['order_price'];
                $billOption['currency_code'] = $orderParam['currency'];
                $billOption['order_type'] = $plat_id == 1 ? self::ORDERS_ORIG_AMAZON : self::ORDERS_ORIG_RAKUTEN;
                $billOption['bill_code'] = CodeInfo::getACode(CodeInfo::PAYMENTS_CODE, $orderParam['order_number'], '1');
                $billOption['created_at'] = date('Y-m-d H:i:s');
                $billOption['updated_at'] = date('Y-m-d H:i:s');
                $billId = OrdersBillPayments::insertGetId($billOption);
                if (!$billId) {
                    $errMsg[$row][] = "第{$row}行，数据插入失败";
                    OrdersBillPayments::where('id', $billId)->delete();
                    continue;
                }


                //处理商品部分
                $actualProducts = 0;
                $product = array_slice($val, 17);
                $productGroup = array_chunk($product, 3);

                $currentOrderAmount = 0;
                //对每条订单包含的产品进行处理
                foreach ($productGroup as $PGKey => $PGValue) {
                    $PGKeyInRow = $PGKey + 1;
                    //去除没有任何信息的产品
                    $countValue = array_sum($PGValue);
                    if ($countValue == 0 && $PGKey == 0) {
                        $errMsg[$row][] = "第{$row}行，产品{$PGKeyInRow}缺少必要参数，请检查相关的参数：SKU、价格、数量";
                        OrdersOriginal::where('id', $newLineId)->delete();
                        OrdersBillPayments::where('id', $billId)->delete();
                        continue;
                    } elseif ($countValue == 0) {
                        continue;
                    }
                    foreach ($PGValue as $key => $val) {
                        $PGValue[$key] = trim($val);
                    }
                    //缺少必要参数则加入错误提示
                    if (in_array(null, $PGValue) || in_array(0, $PGValue, true)) {
                        $errMsg[$row][] = "第{$row}行，产品{$PGKeyInRow}缺少必要参数，请检查相关的参数：SKU、价格、数量";
                        OrdersOriginal::where('id', $newLineId)->delete();
                        OrdersBillPayments::where('id', $billId)->delete();
                        continue;
                    } else {
                        if (preg_match('/\D/', $PGValue[2]) == true) {
                            $errMsg[$row][] = "第{$row}行，产品{$PGKeyInRow}数量非法";
                            OrdersOriginal::where('id', $newLineId)->delete();
                            OrdersBillPayments::where('id', $billId)->delete();
                            continue;
                        }

                        if ((float)$PGValue[1] < 0) {
                            $errMsg[$row][] = "第{$row}行，产品{$PGKeyInRow}价格非法";
                            OrdersOriginal::where('id', $newLineId)->delete();
                            OrdersBillPayments::where('id', $billId)->delete();
                            continue;
                        }

                        if ((float)$PGValue[2] < 0) {
                            $errMsg[$row][] = "第{$row}行，产品{$PGKeyInRow}数量非法";
                            OrdersOriginal::where('id', $newLineId)->delete();
                            OrdersBillPayments::where('id', $billId)->delete();
                            continue;
                        }

                        //查找本地仓库对应的商品
                        $goodsWhereMap = [
                            'sku' => $PGValue[0],
                            'user_id' => $user_id,
                            'status' => Goods::STATUS_PASS
                        ];
                        $goodInfo = Goods::where($goodsWhereMap)->select('id', 'goods_name', 'goods_pictures')->first();
                        //没有该商品则该订单转为匹配失败
                        if (!$goodInfo) {
                            OrdersOriginal::where('id', $newLineId)->delete();
                            OrdersBillPayments::where('id', $billId)->delete();
                            $errMsg[$row][] = "第{$row}行，订单{$orderParam['platform_order']}的产品SKU匹配失败，请确保该商品存在或已通过审核";
                            continue;
                        }
                        $addedProduct = array_column($productParam, 'sku');
                        if (in_array($PGValue[0], $addedProduct)) {
                            OrdersOriginal::where('id', $newLineId)->delete();
                            OrdersBillPayments::where('id', $billId)->delete();
                            $errMsg[$row][] = "第{$row}行，订单{$orderParam['platform_order']}的产品SKU重复";
                            continue;
                        }
                        //记录产品名称
                        //组装单条产品记录
                        $productParam[] = [
                            'user_id' => $user_id,
                            'created_man' => $currentUser->userId,
                            'platform_id' => $plat_id,
                            'sku' => $PGValue[0],
                            'price' => $PGValue[1],
                            'quantity' => $PGValue[2],
                            'original_order_id' => $newLineId,
                            'goods_id' => $goodInfo->id,
                            'rate' => $orderParam['rate'],
                            'goods_name' => $goodInfo->goods_name,
                            'goods_img' => $goodInfo->goods_pictures,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $currentItemAmount = bcmul($PGValue[1], $PGValue[2]);
                        $currentOrderAmount = bcadd($currentItemAmount, $currentOrderAmount);
                        $actualProducts++;
                    }
                }

                if (empty($productParam)) {
                    OrdersOriginal::where('id', $newLineId)->delete();
                    OrdersBillPayments::where('id', $billId)->delete();
//                    $errMsg[$row][] = "第{$row}行，无插入的商品";
                    continue;
                }


                if (count($productParam) == $actualProducts) {
                    $actualAmount = $currentOrderAmount + $orderParam['freight'];
                    if ($actualAmount != $orderParam['order_price']) {
                        $errMsg[$row][] = "第{$row}行，订单总金额与系统统计的金额（{$actualAmount}）不一致";
                        OrdersOriginal::where('id', $newLineId)->delete();
                        OrdersBillPayments::where('id', $billId)->delete();
                        continue;
                    }
                }


                //插入产品
                $productResult = OrdersOriginalProducts::insert($productParam);

                //记录销售量
//                $currentShopQuantityId = OrdersQuantityRecord::where(['user_id' => $user_id, 'shop_id' => $shop->id, 'platforms_id' => $plat_id])
//                    ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
//                    ->first();
//                if (!$currentShopQuantityId) {
//                    OrdersQuantityRecord::create([
//                        'user_id' => $user_id,
//                        'shop_id' => $shop->id,
//                        'platforms_id' => $plat_id,
//                        'quantity' => 1,
//                        'record_times' => time()
//                    ]);
//                } else {
//                    OrdersQuantityRecord::where('id', $currentShopQuantityId->id)->increment('quantity');
//                }
                $orderParam['platforms_id'] = $orderParam['platform'];
                $orderParam['shop_id'] = $orderParam['source_shop'];
                OrdersQuantityRecord::orderQuantityLogics($orderParam, strtotime(date('Y-m-d')));
                unset($orderParam['shop_id']);
                unset($orderParam['platforms_id']);

                //平台订单
                $sysOrder = $orderParam;
                $sysOrder['addressee'] = $sysOrder['addressee1'];
                $sysOrder['addressee1'] = $sysOrder['addressee2'];
                unset($sysOrder['addressee1']);
                unset($sysOrder['addressee2']);
                $sysOrder['country_id'] = $country[$sysOrder['country']];
                $sysOrder['country'] = $countryInName[$sysOrder['country_id']];
                $sysOrder['type'] = self::ORDERS_TYPE;
                $sysOrder['currency_code'] = $sysOrder['currency'];
                $sysOrder['postal_code'] = $sysOrder['zip_code'];
                $sysOrder['platforms_id'] = $sysOrder['platform'];
                $sysOrder['plat_order_number'] = $sysOrder['platform_order'];
                $sysOrder['logistics_choose_status'] = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;
                $sysOrder['warehouse_choose_status'] = Orders::ORDER_CHOOSE_STATUS_UNCHECKED;
                unset($sysOrder['platform_order']);
                unset($sysOrder['platform']);
                unset($sysOrder['zip_code']);
                unset($sysOrder['currency']);
                unset($sysOrder['order_source']);
                unset($sysOrder['match_status']);
                $orderId = Orders::insertGetId($sysOrder);
                if (!$orderId) {
                    OrdersOriginal::where('id', $newLineId)->delete();
                    OrdersBillPayments::where('id', $billId)->delete();
                    OrdersOriginalProducts::where('original_order_id', $newLineId)->delete();
                }
                OrdersOriginal::where('id', $newLineId)->update(['order_id' => $orderId, 'bill_payments' => $billId]);

                if (!$productResult) {
                    $errMsg[$row][] = "第{$row}行，订单{$orderParam['platform_order']}的产品插入失败";
                    Orders::where('id', $orderId)->delete();
                    OrdersOriginal::where('id', $newLineId)->delete();
                    OrdersBillPayments::where('id', $billId)->delete();
                    OrdersOriginalProducts::where('original_order_id', $newLineId)->delete();
                    continue;
                } else {
                    $sysProduct = [];
                    foreach ($productParam as $productKey => $productVal) {
                        $sysProduct[] = [
                            'created_man' => $currentUser->userId,
                            'user_id' => $user_id,
                            'order_id' => $orderId,
                            'goods_id' => $productVal['goods_id'],
                            'order_type' => self::PLATFORM_SELF,
                            'product_name' => $productVal['goods_name'],
                            'sku' => $productVal['sku'],
                            'currency' => $orderParam['currency'],
                            'buy_number' => $productVal['quantity'],
                            'is_deleted' => OrdersProducts::ORDERS_PRODUCT_UNDELETED,
                            'univalence' => $productVal['price']
                        ];
                    }
                    $orderProductResult = OrdersProducts::insert($sysProduct);
                    if (!$orderProductResult) {
                        Orders::where('id', $orderId);
                        OrdersOriginal::where('id', $newLineId)->delete();
                        OrdersBillPayments::where('id', $billId)->delete();
                        OrdersOriginalProducts::where('original_order_id', $newLineId)->delete();
                    }
                }
                $success++;
            }

            DB::commit();

            $err = [];
            foreach ($errMsg as $key => $val) {
                $err[] = implode('<br>', $val);
            }
            $err = implode('<br>', $err);
            $result['code'] = 0;
            $result['msg'] = "成功录入{$success}条<br>" . $err;
            return $this->layResponseData($result);
        } catch (\Exception $e) {
            DB::rollback();
            Common::mongoLog($e, '原始订单', '导入原始订单出错', 'importOrder()', 'api');
            return $this->layResponseData(['code' => -1, 'msg' => '订单导入失败，请重试']);
        }

    }

    /**
     * 新建原始订单
     * Author: zt12779
     * Date: 2019/04/22
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOne(Request $request)
    {
        do {
            try {
                DB::beginTransaction();
                $result['code'] = 0;
                //表单提交规则与缺少提示
                $rules = [
                    'platform_order' => 'required',
                    'plat_id' => 'required',
                    'shop_id' => 'required',
                    'sku' => 'required|array',
                    'currency_freight' => 'required',
                    'currency' => 'required',
                    'address_name' => 'required',
                    'country_id' => 'required',
                    'province' => 'required',
                    'city' => 'required',
                    'mobile_phone' => 'required',
                    'zip_code' => 'required',
                    'addressee1' => 'required',
                ];
                $rulesMsg = [
                    'platform_order.required' => '请输入电商单号',
                    'plat_id.required' => '请选择来源平台',
                    'shop_id.required' => '请选择来源店铺',
                    'sku.required' => '至少需要有一件商品',
                    'currency_freight.required' => '请输入运费',
                    'currency.required' => '请选择币种',
                    'address_name.required' => '请输入收件人',
                    'country_id.required' => '请选择国家',
                    'province.required' => '请输入城市',
                    'city.required' => '请输入省/州',
                    'mobile_phone.required' => '请输入联系人电话',
                    'zip_code.required' => '请输入邮编',
                    'addressee1.required' => '请输入地址1',
                ];
                $validation = Validator::make($request->all(), $rules, $rulesMsg);
                if ($validation->fails()) {
                    $result['code'] = -1;
                    $result['msg'] = implode('<br>', $validation->errors()->all());
                    return $this->layResponseData($result);
                }

                //原始订单数据
                $data = [
                    'platform_order' => $request->get('platform_order'),
                    'platform' => $request->get('plat_id'),
                    'source_shop' => $request->get('shop_id'),
                    'sku' => $request->get('sku'),
                    'currency_freight' => $request->get('currency'),
                    'freight' => $request->get('currency_freight'),
                    'currency' => $request->get('currency'),
                    'addressee_name' => $request->get('address_name'),
                    'country_id' => $request->get('country_id'),
                    'province' => $request->get('province'),
                    'city' => $request->get('city'),
                    'mobile_phone' => $request->get('mobile_phone'),
                    'zip_code' => $request->get('zip_code'),
                    'addressee1' => $request->get('addressee1'),
                    'warehouse_id' => $request->get('warehouse_id'),
                    'logistics_id' => $request->get('logistics_id'),
                    'goods_nums' => $request->get('goods_nums'),
                    'goods_price' => $request->get('goods_price'),
                    'addressee2' => $request->get('addressee2'),
                    'addressee_email' => $request->get('address_email'),
                    'order_source' => self::ORDER_SOURCE_MANUAL
                ];
                if (preg_match('/\s/', $data['platform_order'])) {
                    $result = ['code' => -1, 'msg' => '原始单号含有空格'];
                    return $this->layResponseData($result);
                }

                if (!empty($request->get('mark'))) {
                    $data['mark'] = $request->get('mark');

                }

                //用户的父级ID
                $currentUser = CurrentUser::getCurrentUser();
                if ($currentUser->userAccountType == AccountType::CHILDREN) {
                    $user_id = $currentUser->userParentId;
                } else {
                    $user_id = $currentUser->userId;
                }

                //查看电商单号的唯一性
                $platformOrderNumWhereMap = [
                    'user_id' => $user_id,
                    'platform_order' => $data['platform_order']
                ];
                $uniqueOrder = OrdersOriginal::where($platformOrderNumWhereMap)->first(['id']);
                if ($uniqueOrder) {
                    $result = ['code' => -1, 'msg' => '电商单号已存在'];
                    return $this->layResponseData($result);
                }

                //查找用户所选的店铺信息
                $shopWhereMap = [
                    'user_id' => $user_id,
                    'plat_id' => $data['platform'],
                    'id' => $data['source_shop']
                ];
                $shop = SettingShops::where($shopWhereMap)->first(['shop_name']);
                if (!$shop) {
                    $result = ['code' => -1, 'msg' => '无该门店'];
                    return $this->layResponseData($result);
                }

                if (!empty($data['warehouse_id'])) {
                    //查找用户所选的仓库信息
                    $warehouseMap = [
                        'user_id' => $user_id,
                        'id' => $data['warehouse_id']
                    ];
                    $warehouse = SettingWarehouse::where($warehouseMap)->first();
                    if (!$warehouse) {
                        $result['code'] = -1;
                        $result['msg'] = "没有该指定仓库";
                        return $this->layResponseData($result);
                    }
                }

                if (!empty($data['logistics_id'])) {
                    //查找用户所选的物流信息
                    $logisticsMap = [
                        'user_id' => $user_id,
                        'id' => $data['logistics_id']
                    ];
                    $logistics = SettingLogistics::where($logisticsMap)->first();
                    if (!$logistics) {
                        $result['code'] = -1;
                        $result['msg'] = "没有该指定物流";
                        return $this->layResponseData($result);
                    }
                }


                //获取汇率
//                $rate = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($data['currency'], $user_id);
                $rate = SettingCurrencyExchange::where('currency_form_code', $data['currency'])->first();
                $rate = $rate['exchange_rate'];

                //原始产品
                $originalGood = $goods_weight_arr = [];
                $goodMatchFailure = false;
                $goodWhereMap = [
                    'user_id' => $user_id,
//                    'plat' => $data['platform']
                ];
                //产品总价
                $orderAmount = 0;
                //检查所提交的产品
                foreach ($data['sku'] as $key => $val) {
                    if ($data['goods_nums'][$key] <= 0) {
                        $goodMatchFailure = true;
                        $result['msg'][] = "SKU为{$val}的产品购买数量不允许低于1";
                        continue;
                    }

                    $goodWhereMap['sku'] = $val;
                    $goodRow = Goods::where($goodWhereMap)->first(['id', 'goods_name', 'goods_pictures', 'goods_weight']);
                    if (!$goodRow) {
                        $goodMatchFailure = true;
                        $result['msg'][] = "SKU为{$val}的产品不存在，请检查SKU是否正确";
                        continue;
                    }
                    //组装产品信息
                    $originalGood[] = [
                        'created_man' => $currentUser->userId,
                        'user_id' => $user_id,
                        'platform_id' => $data['platform'],
                        'sku' => $val,
                        'goods_id' => $goodRow->id,
                        'price' => $data['goods_price'][$key],
                        'quantity' => $data['goods_nums'][$key],
                        'goods_name' => $goodRow->goods_name,
                        'goods_img' => $goodRow->goods_pictures,
                        'rate' => $rate,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $goods_weight_arr [$goodRow->id] = $goodRow->goods_weight;

                    //统计总金额
                    $currentItemAmount = bcmul($data['goods_price'][$key], $data['goods_nums'][$key]);
                    $orderAmount = bcadd($orderAmount, $currentItemAmount);
                }
                //如果有产品查找失败，返回提示用户
                if ($goodMatchFailure === true) {
                    $result['code'] = -1;
                    $result['msg'] = implode("<br>", $result['msg']);
                    return $this->layResponseData($result);
                }
                $country = SettingCountry::where('id', $data['country_id'])->first();
                $platform = Platforms::where('id', $data['platform'])->first();
                //原始订单数据
                $data['match_status'] = self::ORDER_MATCH_SUCCESS;
                $data['order_price'] = bcadd($orderAmount, $data['freight']);
                $data['source_shop_name'] = $shop->shop_name;
                $data['order_number'] = CodeInfo::getACode(self::CODE_INFO_ORDER);
                $data['country'] = $country->country_name;
                $data['user_id'] = $user_id;
                $data['platform_name'] = $platform->name_EN;
                $data['created_man'] = $currentUser->userId;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['payment_time'] = date('Y-m-d H:i:s');
                $data['order_time'] = date('Y-m-d H:i:s');
//                $data['rate'] = SettingCurrencyExchangeMaintain::getExchangeByCodeUserid($data['currency'], $user_id);
                $rate = SettingCurrencyExchange::where('currency_form_code', $data['currency'])->first();
                $data['rate'] = $rate['exchange_rate'];


                !empty($data['logistics_id']) && $data['logistics'] = $logistics->logistic_name;
                !empty($data['warehouse_id']) && $data['warehouse'] = $warehouse->warehouse_name;

                unset($data['sku']);
                unset($data['goods_nums']);
                unset($data['goods_price']);
                $originalOrderId = OrdersOriginal::insertGetId($data);
                if (!$originalOrderId) {
                    $result['code'] = -1;
                    $result['msg'] = "新建原始订单失败";
                    DB::rollback();
                    return $this->layResponseData($result);
                }
                //付款单信息
                $billOrder = [
                    'created_man' => $user_id,
                    'order_id' => $originalOrderId,
                    'order_type' => $data['platform'] == self::PLATFORM_SELF ? self::ORDERS_ORIG_AMAZON : self::ORDERS_ORIG_RAKUTEN,
                    'type' => self::ORDERS_ORIG_RAKUTEN,
                    'amount' => $orderAmount,
                    'currency_code' => $data['currency'],
                    'rate' => $rate,
                    'bill_code' => CodeInfo::getACode(CodeInfo::PAYMENTS_CODE, $data['order_number'], '1'),
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at']
                ];
                $billId = OrdersBillPayments::insertGetId($billOrder);
                if (!$billId) {
                    $result['code'] = -1;
                    $result['msg'] = "新建原始订单的付款单失败";
                    DB::rollback();
                    return $this->layResponseData($result);
                }
                //补上产品缺少的订单ID
                foreach ($originalGood as $key => $val) {
                    $originalGood[$key]['original_order_id'] = $originalOrderId;
                }
                //插入原始订单商品
                OrdersOriginalProducts::insert($originalGood);
                //更新原始订单的付款单ID
                if (!OrdersOriginal::where('id', $originalOrderId)->update(['bill_payments' => $billId])) {
                    if (!$originalOrderId) {
                        $result['code'] = -1;
                        $result['msg'] = "新建原始订单失败";
                        DB::rollback();
                        return $this->layResponseData($result);
                    }
                }

                $orderParams = [
                    'user_id' => $user_id,
                    'created_man' => $currentUser->userId,
                    'platforms_id' => $data['platform'],
                    'source_shop' => $data['source_shop'],
                    'order_number' => $data['order_number'],
                    'plat_order_number' => $data['platform_order'],
                    'type' => Orders::ORDERS_GETINFO_MANUAL,
                    'platform_name' => $data['platform_name'],
                    'source_shop_name' => $data['source_shop_name'],
                    'order_price' => $data['order_price'],
                    'freight' => $data['freight'],
                    'currency_freight' => $data['currency_freight'],
                    'country' => $data['country'],
                    'province' => $data['province'],
                    'city' => $data['city'],
                    'mobile_phone' => $data['mobile_phone'],
                    'addressee_email' => $data['addressee_email'],
                    'warehouse' => isset($data['warehouse']) ? $data['warehouse'] : '',
                    'logistics' => isset($data['logistics']) ? $data['logistics'] : '',
                    'addressee_name' => $data['addressee_name'],
                    'addressee' => $data['addressee1'],
                    'addressee1' => $data['addressee2'],
                    'mark' => !isset($data['mark']) ? '' : $data['mark'],
                    'order_time' => $data['created_at'],
                    'payment_time' => $data['created_at'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                    'postal_code' => $data['zip_code'],
                    'country_id' => $data['country_id'],
                    'currency_code' => $data['currency'],
                    'rate' => $rate,
                    'warehouse_id' => $data['warehouse_id'],
                    'logistics_id' => $data['logistics_id'],
                    'logistics_choose_status' => !empty($data['logistics_id']) ? Orders::ORDER_CHOOSE_STATUS_CHECKED : Orders::ORDER_CHOOSE_STATUS_UNCHECKED,
                    'warehouse_choose_status' => !empty($data['warehouse_id']) ? Orders::ORDER_CHOOSE_STATUS_CHECKED : Orders::ORDER_CHOOSE_STATUS_UNCHECKED
                ];
                $orderId = Orders::insertGetId($orderParams);
                $orderGood = [];
                foreach ($originalGood as $key => $val) {
                    $orderGood[] = [
                        'created_man' => $currentUser->userId,
                        'user_id' => $user_id,
                        'order_id' => $orderId,
                        'goods_id' => $val['goods_id'],
                        'order_type' => OrdersProducts::ORDERS_CWERP,
                        'product_name' => $val['goods_name'],
                        'sku' => $val['sku'],
                        'currency' => $data['currency'],
                        'buy_number' => $val['quantity'],
                        'univalence' => $val['price'],
                        'rate' => $rate,
                        'weight' => $goods_weight_arr [$val['goods_id']],
                        'already_stocked_number' => 0,
                        'cargo_distribution_number' => 0,
                        'delivery_number' => 0,
                        'partial_refund_number' => 0,
                        'RMB' => bcmul($rate, $val['price']),
                        'is_deleted' => OrdersProducts::ORDERS_PRODUCT_UNDELETED,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                //插入一份订单表的付款单
                $billOrder['order_id'] = $orderId;
                $billOrder['order_type'] = self::PLATFORM_ORDER_SELF;

                OrdersProducts::insert($orderGood);
                OrdersOriginal::where('id', $originalOrderId)->update(['order_id' => $orderId]);
                OrdersBillPayments::insertGetId($billOrder);
                $orderParams['shop_id'] = $orderParams['source_shop'];
                OrdersQuantityRecord::orderQuantityLogics($orderParams, strtotime(date('Y-m-d')));
                //日志
                $orderLogsData ['created_man'] = $currentUser->userId;
                $orderLogsData ['order_id'] = $orderId;
                $orderLogsData ['behavior_types'] = OrdersLogs::LOGS_ORDERS_CREATED;
                $orderLogsData ['behavior_desc'] = OrdersLogs::ORDERS_LOGS_DESC[$orderLogsData['behavior_types']];
                $orderLogsData ['behavior_type_desc'] = OrdersLogs::ORDERS_LOGS_TYPE_DESC[$orderLogsData ['behavior_types']];
                $orderLogsData ['updated_at'] = $orderLogsData ['created_at'] = date('Y-m-d H:i:s');
                OrdersLogs::postDatas(0, $orderLogsData);
                DB::commit();
                $result['msg'] = "新建原始订单成功";
                return $this->layResponseData($result);
            } catch (\Exception $e) {
                $result['code'] = -1;
                $result['msg'] = "服务器出错啦：{$e->getMessage()}";
                DB::rollback();
                Common::mongoLog($e, '原始订单', '创建原始订单出错', 'addOne()', 'api');
                return $this->layResponseData($result);
            }
        } while (0);
    }

    /**
     * 创建原始订单详情页
     * Author: zt12779
     * Date: 2019/04/28
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createOriginalOrderPage()
    {
        try {
            //需要展示 左侧数据 1.异常数据条数 2.快捷菜单
            //1.客户信息
            //2.问题类型
            //3.来源平台：
            //4.来源平台：
            //5.物流方式:
            //6.仓库
            //便捷菜单
//            $responseData ['shortcutMenus'] = Orders::getOrderShortcutMenu();
            //问题类型
            $responseData ['troubles'] = RulesOrderTroubleType::getTroubleType();

            //店铺 获取客户 状态正常的店铺
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $param ['user_id'] = $currentUser->userParentId;
                //子账户
                $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'], true);
                    $responseData ['shops'] = SettingShops::getShopsByShopsId($shopsId);
                } else {
                    $responseData ['shops'] = [];
                }
            } else {
                $param ['user_id'] = $currentUser->userId;
                $responseData ['shops'] = SettingShops::getShopsByUserId($param ['user_id']);
            }

            //平台
            $responseData ['platforms'] = Platforms::getAllPlat();
            //物流方式
            $responseData ['logistics'] = SettingLogistics::getAllLogisticsByUserId($param ['user_id']);
            //仓库
            $responseData ['warehouses'] = SettingWarehouse::getAllWarehousesByUserId($param ['user_id']);

            $responseData ['country'] = SettingCountry::getAllCountry();
            $responseData ['currency'] = SettingCurrencyExchange::get()->toArray();
            return view('Order/originalOrderCreate')->with($responseData);
        } catch (\Exception $e) {
            Common::mongoLog($e, '原始订单', '原始订单创建页', 'createOriginalOrderPage()', 'api');
        }
    }

    /**
     * 根据SKU进行商品搜索
     * Author: zt12779
     * Date: 2019/04/28
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSku(Request $request)
    {
        $sku = $request->get('sku');
        $plat_id = $request->get('platform');
        $currentUser = CurrentUser::getCurrentUser();
        //组装权限参数
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //主账号id
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }
        $goodsWhereMap = [
            'goods.sku' => $sku,
            'goods.user_id' => $user_id,
            'status' => 2
        ];

//        if (!empty($plat_id)) {
//            $goodsWhereMap['goods.plat'] = $plat_id;
//        }

        $product = Goods::where($goodsWhereMap)
            ->join('goods_attribute', 'goods_attribute.id', '=', 'goods.goods_attribute_id')
            ->select('goods.*', 'goods_attribute.attribute_name')
            ->first();
        if (!$product) {
            $result = [
                'code' => -1,
                'msg' => '没有该商品'
            ];
            return $this->layResponseData($result);
        }
        $result = ['code' => 0, 'data' => $product];
        return $this->layResponseData($result);
    }

    /**
     * 根据平台获取店铺
     * Author: zt12779
     * Date: 2019/04/28
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlatformShop(Request $request)
    {
        $plat_id = $request->get('platform');
        $currentUser = CurrentUser::getCurrentUser();
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            //子账户
            $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
            if ($shopsPermission) {
                $shopsId = json_decode($shopsPermission['shops_id'], true);
                $responseData = SettingShops::getShopsByShopsId($shopsId,$plat_id);
            } else {
                $responseData = [];
            }
        } else {
            $user_id = $currentUser->userId;
            $responseData = SettingShops::getShopByPlatId($plat_id, $user_id);
        }
        $result = ['code' => 0, 'data' => $responseData];
        return $this->layResponseData($result);
    }

    /**
     * 订单详情
     * Author: zt12779
     * Date: 2019/04/28
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function orderDetail(Request $request)
    {
        $orderNumber = $request->get('order_number');
        $orderInfo = OrdersOriginal::getOrderDetail($orderNumber);
        if (!$orderInfo) {
            die('没有该订单');
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if (!in_array('order/originalOrder', $currentUser->userPermissions)) {
            abort(404);
        }
        is_object($orderInfo) && $orderInfo = $orderInfo->toArray();
        $country = SettingCountry::where(function ($query) use ($orderInfo) {
            $query->where('country_code', strtoupper($orderInfo['country']))
                ->orWhere('country_name', $orderInfo['country']);
        })->first();
        $orderInfo['country'] = $country->country_name;
        if (is_bool(strpos($request->header('referer'), 'originalOrder'))) {
            $edit = 0;
        } else {
            $edit = 1;
        }
        return view('Order/originalOrderDetails')->with(['orderInfo' => $orderInfo, 'edit' => $edit]);
    }

    /**
     * 根据仓库获取快递信息
     * Author: zt12779
     * Date: 2019/04/28
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogistics(Request $request)
    {
        try {
            $warehouseId = $request->get('warehouseId');

            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
            } else {
                $user_id = $currentUser->userId;
            }

            $whereMap['user_id'] = $user_id;
            !empty($warehouseId) && $whereMap['warehouse_id'] = $warehouseId;

            $logisticsIds = SettingLogisticsWarehouses::where($whereMap)->get()->toArray();

            $logisticsIdsArr = array_column($logisticsIds, 'logistic_id');

            //            `is_show` tinyint(2) DEFAULT '1' COMMENT '是否展示 0不展示 1展示',
            //  `disable` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否启用 1 启用 2 禁用',
            $logistics = SettingLogistics::whereIn('id', $logisticsIdsArr)->where(['is_show' => SettingLogistics::LOGISTIC_SHOW, 'disable' => SettingLogistics::LOGISTICS_STATUS_USING])->get()->toArray();

            return $this->layResponseData(['code' => 0, 'data' => $logistics]);
        } catch (\Exception $e) {
            Common::mongoLog($e, '物流仓库表', '获取物流方式出错', 'getLogistics()', 'api');
            return $this->layResponseData(['code' => -1, 'msg' => '服务器开小差']);
        }
    }
}