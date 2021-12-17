<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/3/20
     * Time: 11:04
     */

    namespace App\Http\Services\Order;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use App\Auth\Models\RolesShops;
    use App\Common\Common;
    use App\Exceptions\DataNotFoundException;
    use App\Models\OrdersInvoices;
    use App\Models\SettingWarehouse;
    use App\Models\WarehouseGoods;
    use App\Models\WarehouseSecretkey;
    use Illuminate\Support\Facades\DB;
    use Excel;

    class DistrbutionHandle
    {
        protected $_err = [];
        protected $input = null;
        protected static $COMMON;

        public function getErrs()
        {
            return $this->_err;
        }

        public function setInput($input)
        {
            return $this->input = $input;
        }

        public function __construct()
        {

            self::$COMMON = new Common();
        }

        /**
         * @return array
         * Note: 配货单 搜索数据
         * Date: 2019/3/20 11:00
         * Author: zt8067
         */
        public static function getSummaryByPage($params, $user_id,$permissionParam = [])
        {
            $data = $params->all();
            $limit = $params->get('limit', 20);
            $type = $params->get('type', 1);
            // DB::enableQueryLog();
            $collection = OrdersInvoices::query();
            /*
            *配货参数
            */
            if (isset($permissionParam ['source_shop'])) {
                $collection->whereIn('orders_invoices.source_shop', $permissionParam['source_shop']);
            }
            //同步状态
            $param['sync_status'] = (isset($data['data']['sync_status']) && !empty($data['data']['sync_status']))
                ? $data['data']['sync_status'] : '';
            $param['sync_status'] && $collection->where('orders_invoices.sync_status', $param['sync_status']);
            //是否作废
            $param['invoices_status'] = (isset($data['data']['invoices_status']) && !empty($data['data']['invoices_status']))
                ? $data['data']['invoices_status'] : '';
            $param['invoices_status'] && $collection->where('orders_invoices.invoices_status', $param['invoices_status']);
            //来源平台
            $param['platforms_id'] = (isset($data['data']['platforms_id']) && !empty($data['data']['platforms_id']))
                ? $data['data']['platforms_id'] : '';
            $param['platforms_id'] && $collection->where('orders_invoices.platforms_id', $param['platforms_id']);
            //来源店铺
            $param['source_shop'] = (isset($data['data']['source_shop']) && !empty($data['data']['source_shop']))
                ? $data['data']['source_shop'] : '';
            $param['source_shop'] && $collection->where('orders_invoices.source_shop', $param['source_shop']);
            //下单时间
            $param['place_an_order_start_time'] = (isset($data['data']['place_an_order_start_time']) && !empty($data['data']['place_an_order_start_time']))
                ? $data['data']['place_an_order_start_time'] : '';
            $param['place_an_order_end_time'] = (isset($data['data']['place_an_order_end_time']) && !empty($data['data']['place_an_order_end_time']))
                ? $data['data']['place_an_order_end_time'] : '';
            $param['place_an_order_start_time'] && $param['place_an_order_end_time'] && $collection->with('orders')->select('orders_invoices.*', 'orders_invoices.id as tid', 'orders.order_number', 'orders.plat_order_number')->leftJoin('orders', 'orders.id', '=', 'orders_invoices.order_id')->where('orders.order_time', '>=', $param['place_an_order_start_time'])->where('orders.order_time', '<=', $param['place_an_order_end_time']);
            //配货单号
            $param['invoices_number'] = (isset($data['data']['invoices_number']) && !empty($data['data']['invoices_number']))
                ? $data['data']['invoices_number'] : '';
            $param['invoices_number'] && $collection->where('orders_invoices.invoices_number', trim($param['invoices_number']));
            //订单单号
            $param['order_number'] = (isset($data['data']['order_number']) && !empty($data['data']['order_number']))
                ? $data['data']['order_number'] : '';
            $param['order_number'] && $collection->with('orders')->select('orders_invoices.*', 'orders_invoices.id as tid', 'orders.order_number', 'orders.plat_order_number')->leftJoin('orders', 'orders.id', '=', 'orders_invoices.order_id')->where('orders.order_number', $param['order_number']);

            //电商单号
            $param['plat_order_number'] = (isset($data['data']['plat_order_number']) && !empty($data['data']['plat_order_number']))
                ? $data['data']['plat_order_number'] : '';
            $param['plat_order_number'] && $collection->with('orders')->select('orders_invoices.*', 'orders_invoices.id as tid', 'orders.order_number', 'orders.plat_order_number')->leftJoin('orders', 'orders.id', '=', 'orders_invoices.order_id')->where('orders.plat_order_number', $param['plat_order_number']);
            if (empty($param['place_an_order_start_time']) && empty($param['order_number']) && empty($param['plat_order_number'])) {
                $collection->with('orders')->select('orders_invoices.*', 'orders_invoices.id as tid', 'orders.order_number', 'orders.plat_order_number')->leftJoin('orders', 'orders.id', '=', 'orders_invoices.order_id')->orderByDesc('orders_invoices.id')->orderBy('orders.payment_time');
            }
            //            dd($collection->orderByDesc('id')->get()->toArray());
            $collection->where('orders_invoices.invoices_status', '<>',OrdersInvoices::DEL_INVOICES_STATUS);
            $pagingData = $collection->where('orders_invoices.user_id', $user_id)->paginate($limit)->toArray();
//                        dd(DB::getQueryLog());
            return $pagingData;
        }

        /**
         * @return array
         * Note: 更新导入单号
         * Date: 2019/3/21 10:00
         * Author: zt8067
         */
        public function updateTrack($request, $user_id)
        {

            $results = ['code' => -1, 'msg' => ''];
            //账户类型
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $user_id = $currentUser->userParentId;
                $shopsPermission = RolesShops::getShopPermissionByUserid($currentUser->userId);
                if ($shopsPermission) {
                    $shopsId = json_decode($shopsPermission['shops_id'],true);
                    if (empty($shopsId)) {
                        $result['msg'] = '未配置店铺权限';
                        return $result;
                    }
                } else {
                    $result['msg'] = '未配置店铺权限';
                    return $result;
                }
            } else {
                $user_id = $currentUser->userId;
            }
            do {
                DB::beginTransaction();
                try {
                    $file = $request->file('file');
                    $suffix = $file->getClientOriginalExtension();
                    $realpath = $file->getRealPath();
                    $trackTemp = ['配货单号', '跟踪号'];
                    if (!in_array($suffix, ['xlsx', 'xls'])) {
                        $results['code'] = 1;
                        $results['msg'] = '上传格式不正确，请上传一个xlsx文件';
                        break;
                    }
                    if ($file->isValid() === false) {
                        $results['code'] = 1;
                        $results['msg'] = '上传文件失败';
                        break;
                    }
                    Excel::load($realpath, function ($reader) {
                        $this->setInput($reader->getSheet(0)->toArray());
                    });
                    if ($this->input[0] != $trackTemp) {
                        $results['code'] = 1;
                        $results['msg'] = '上传文件模板不是系统指定的模板';
                        break;
                    }
                    array_shift($this->input);
                    array_walk($this->input, function (&$v, $k) {
                        foreach ($v as &$vv) {
                            $vv = trim($vv);
                        }
                    });
                    if (!empty($this->input)) {
                        if (!empty($this->_err)) break;
                        foreach ($this->input as $k => $v) {
                            if (empty($v)) continue;
                            $invoices_number = trim($v[0]);
                            $track = trim($v[1]);
                            //自定义仓库才可更新跟踪号
                            $exists = OrdersInvoices::whereHas('SettingWarehouse', function ($query) {
                                $query->where('type', SettingWarehouse::CUSTOM_TYPE);
                            })->where('invoices_number', $invoices_number)->first(['id','order_id','source_shop']);

                            if (empty($exists)) {
                                $this->_err[] = "第" . ($k + 2) . "行未找到或不是自定义仓库配货单，请核对！";
                                continue;
                            }

                            if (isset($shopsId) && !in_array($exists->source_shop,$shopsId)) {
                                $this->_err[] = "第" . ($k + 2) . "行订单店铺权限异常！";
                                continue;
                            }
                            if (empty($v[0])) {
                                $this->_err[] = "第" . ($k + 2) . "行请输入配货单号！";
                                continue;
                            }
                            if (empty($v[1])) {
                                $this->_err[] = "第" . ($k + 2) . "行请输入跟踪号！";
                                continue;
                            }
                            if (!preg_match('/^[A-Za-z0-9\_\-]/u', $v[1])) {
                                $this->_err[] = "第" . ($k + 2) . "行请输入正确的跟踪号！";
                                continue;
                            }
                            if (preg_match('/[ \'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/', $v[1])) {
                                $this->_err[] = "第" . ($k + 2) . "行跟踪号存在特殊字符！";
                                continue;
                            }

                            if (empty($v[1])) {
                                $this->_err[] = "第" . ($k + 2) . "行跟踪单号为空";
                                continue;
                            }
                            $OrdersInvoices = OrdersInvoices::where('user_id', $user_id)->where('invoices_number', $invoices_number)->update(['tracking_no' => $track,'delivery_status'=> OrdersInvoices::DELIVERY_STATUS_YES]);
                            if ($OrdersInvoices) {
                                //更新发货状态 并且更新发货数量
                                OrdersService::updateShipment($exists['order_id'],$user_id,true);
                                $this->_err[] = "第" . ($k + 2) . "行导入成功！";
                            }
                        }
                    } else {
                        $results['code'] = 1;
                        $results['msg'] = '上传文件内容为空';
                        break;
                    }
                    DB::commit();
                    $results['code'] = 1;
                    $results['msg'] = '导入物流跟踪号成功';
                } catch (\Exception $e) {
                    DB::rollBack();
                    Common::mongoLog($e,'配货单','导入物流跟踪号',__FUNCTION__);
                } catch (\Error $e) {
                    DB::rollBack();
                    Common::mongoLog($e,'配货单','导入物流跟踪号',__FUNCTION__);
                } finally {
                    if (!empty($this->_err)) {
                        DB::rollBack();
                        $results['code'] = -1;
                        $results['err'] = implode("<br>", $this->_err);
                    }
                }
            } while (0);
            return $results;
        }

        /**
         * @return array
         * Note: 获取配货单信息
         * Date: 2019/3/26 10:00
         * Author: zt8067
         */
        public function getOrdersInvoicesDesc($data = [], $user_id)
        {
            $OrdersInvoices = OrdersInvoices::with('OrdersInvoicesProduct.Goods')->with(['Orders' => function ($query) {
                $query->with('Platforms')->with('Shops');
            }, 'SettingLogistics', 'SettingWarehouse',
            ])->where('user_id', $user_id)->where('id', $data['id'])->first();
            if (empty($OrdersInvoices)) {
                throw new DataNotFoundException();
            }
            $results = $OrdersInvoices->toArray();
            $invoices_product = &$results['orders_invoices_product'];
            if (!empty($invoices_product)) {
                $total_weight = 0;
                if (!empty($data['total_weight'])) {
                    $total_weight = $data['total_weight'];
                } else {
                    foreach ($invoices_product as &$item) {
                        $WarehouseGoods = WarehouseGoods::with('warehouseHasGoods')->where('goods_id', $item['goods_id'])->first();
                        unset($item['cargo_distribution']);
                        if (!empty($WarehouseGoods)) {
                            $WarehouseGoods = $WarehouseGoods->toArray();
                            $item['cargo_distribution'] = $WarehouseGoods['available_in_stock'];
                        } else {
                            $item['cargo_distribution'] = null;
                        }
                        $total_weight += self::$COMMON->PriceCalculate($item['already_stocked_number'], '*', $item['weight'], 3);
                    }
                }
                $results['total_weight'] = $total_weight;
            }
            return $results;
        }

        /**
         * @return array
         * Note: 获取物流方式 废弃
         * Date: 2019/3/26 11:00
         * Author: zt8067
         */
        public function sendLogistics($params)
        {
            $results = ['code' => -1];
            $data['warehouseCode'] = $params;
            $results = self::$COMMON->sendWarehouse('getShippingMethod', $data);
            $results && $results['code'] = 1;
            return $results;
        }

        /**
         * @return array
         * Note: 获取费用试算
         * DESC: $params ①$params['orders']国家  ②
         * Date: 2019/3/26 20:40
         * Author: zt8067
         */
        public function freightTrial($params, $WarehouseId, $user_id)
        {
            if (empty($WarehouseId)) return false;
            $freight = false;
            //获取用户开通的物流方式
            //            DB::enableQueryLog();

            //客户仓库密钥信息
            $warehouseConfigInfo = WarehouseSecretkey::where([
                'status'=>WarehouseSecretkey::STATUS_ON,
                'user_id'=>$user_id,
            ])->first(['user_id','appToken','appKey']);
            if (empty($warehouseConfigInfo)) {
                return $freight;
            }
            $warehouseConfigInfo = $warehouseConfigInfo->toArray();
            $account ['appToken'] = $warehouseConfigInfo ['appToken'];
            $account ['appKey'] = $warehouseConfigInfo ['appKey'];

            $Warehouse = SettingWarehouse::with(['Logistics' => function ($query) use ($user_id) {
                $query->where('setting_logistics_warehouses.user_id', $user_id);
            },
            ])->where('user_id', $user_id)->where('id', $WarehouseId)->first()->toArray();
            //            dd(DB::getQueryLOg());
            if (!empty($Warehouse['logistics'])) {
                foreach ($Warehouse['logistics'] as $k => $item) {
                    $arr['warehouse_code'] = $Warehouse['warehouse_code'];//仓库
                    $arr['country_code'] = (isset($params['orders']['country']) && !empty($params['orders']['country'])) ? $params['orders']['country'] : $params['country'];//国家
                    $arr['postcode'] = (isset($params['orders']['postal_code']) && !empty($params['orders']['postal_code'])) ? $params['orders']['postal_code'] : $params['postal_code'];//邮编
                    $arr['shipping_method'] = $item['logistic_code'];//物流方式
                    $arr['weight'] = $params['total_weight'];//订单重量
                    if (empty($arr['warehouse_code']) || empty($arr['country_code']) || empty($arr['shipping_method']) || empty($arr['weight'])) continue;
                    $results = self::$COMMON->sendWarehouse('getCalculateFee', $arr ,$account);
                    $freight[$k]['id'] = $item['id'];
                    $freight[$k]['logistic_code'] = $item['logistic_code'];
                    $freight[$k]['logistic_name'] = $item['logistic_name'];
                    $freight[$k]['totalFee'] = '';
                    $freight[$k]['error'] = '';
                    $results['ask'] == 'Success' && $freight[$k]['totalFee'] = $results['data']['totalFee'];
                    $results['ask'] == 'Failure' && $freight[$k]['error'] = $results['Error']['errMessage'];
                }
            }
            return $freight;
        }

        /**
         * @return array
         * Note: 导出配货单方法
         * Date: 2019/4/2 17:58
         * Author: zt8067
         */
        public function explodeDistrbution($user_id)
        {

            $dataM = OrdersInvoices::with(['Orders', 'SettingLogistics', 'SettingWarehouse', 'OrdersInvoicesProduct', 'Platforms', 'SettingShops'])->where(['user_id'=> $user_id])->where('invoices_status','<>',OrdersInvoices::DEL_INVOICES_STATUS)->get();
            if ($dataM->isEmpty()) {
                throw new DataNotFoundException();
            }
            $data = $dataM->toArray();
            $title = ['配货单号', '订单号', '电商单号', '来源平台', '来源店铺', '收件人姓名', '收件人手机', '收件人国家', '收件人城市', '收件人州/省', '买家指定物流', '发货仓库', '发货物流', '物流单号'];
            foreach ($this->exportYield($data) as $k => $item) {
                $cellData[$k][] = $item['invoices_number']??'';
                $cellData[$k][] = $item['orders']['order_number']??'';
                $cellData[$k][] = $item['orders']['plat_order_number']??'';
                $cellData[$k][] = $item['platforms']['name_CN']??'';
                $cellData[$k][] = $item['setting_shops']['shop_name']??'';
                $cellData[$k][] = $item['orders']['addressee_name']??'';
                $cellData[$k][] = $item['orders']['mobile_pone']??'';
                $cellData[$k][] = $item['orders']['country']??'';
                $cellData[$k][] = $item['orders']['city']??'';
                $cellData[$k][] = $item['orders']['province']??'';
                $cellData[$k][] = $item['setting_logistics']['logistic_name']??'';
                $cellData[$k][] = $item['setting_warehouse']['warehouse_name']??'';
                $cellData[$k][] = $item['setting_logistics']['logistic_name']??'';
                $cellData[$k][] = $item['tracking_no']??'';
                if (!empty($item['orders_invoices_product'])) {
                    $j = 1;
                    foreach ($item['orders_invoices_product'] as $product) {
                        $sku = 'sku' . $j;
                        $num = 'SKU' . $j . '数量';
                        if (!in_array($sku, $title) && !in_array($num, $title)) {
                            array_push($title, $sku, $num);
                        }
                        $cellData[$k][] = $product['sku'];
                        $cellData[$k][] = $product['already_stocked_number'];//已配货数量
                        $j++;
                    }
                }
            }
            array_unshift($cellData, $title);
            $name = iconv('UTF-8', 'GBK', date('Y-m-d') . ' 配货单信息');
            Excel::create($name, function ($excel) use ($cellData) {
                $excel->sheet('score', function ($sheet) use ($cellData) {
                    $sheet->rows($cellData);
                    $sheet->setWidth([
                        'A' => '20',
                        'B' => '20',
                        'C' => '20',
                        'D' => '10',
                        'E' => '10',
                        'F' => '10',
                        'G' => '15',
                        'H' => '10',
                        'I' => '15',
                        'J' => '15',
                        'K' => '20',
                        'L' => '20',
                        'M' => '20',
                        'N' => '20',
                        'O' => '20',
                        'P' => '10',
                        'Q' => '20',
                        'R' => '10',
                    ]);
                });
            })->store('xls')->export('xls');
        }
        /**
         * @author zt6650
         * 导出信息迭代器去
         * @param $yield_arr
         * @return \Generator
         */
        public function exportYield($yield_arr)
        {
            for ($i=0 ;$i<count($yield_arr) ; $i++) {
                yield $yield_arr[$i] ;
            }
        }
    }