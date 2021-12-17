<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/6/3
     * Time: 14:52
     */

    namespace App\Http\Services\Goods;

    use App\Exceptions\DataNotFoundException;
    use App\Models\Goods;
    use App\Models\GoodsMapping;
    use App\Models\GoodsMappingGoods;
    use App\Models\SettingShops;
    use Illuminate\Support\Facades\DB;
    use Excel;

    class MappingHandle
    {
        protected $_err = [];
        protected $input = null;

        public function getErrs()
        {
            return $this->_err;
        }

        public function setInput($input)
        {
            return $this->input = $input;
        }

        public static function getSummaryByPage($request, $user_id,$shopsPermission = [] )
        {

            $data = $request->get('data', []);
            $limit = $request->get('limit', 20);
            //DB::enableQueryLog();
            $collection = GoodsMapping::query();
            $params = [];
            $params['platform_id'] = (isset($data['platform_id']) && !empty($data['platform_id'])) ? $data['platform_id'] : '';
            $params['platform_id'] && $collection->where('platform_id', $params['platform_id']);
            isset($data['status']) && $collection->where('status', $data['status']);
            $params['setting_shops_id'] = isset($data['setting_shops_id']) ? $data['setting_shops_id'] : '';
            $params['setting_shops_id'] && $collection->where('setting_shops_id', $params['setting_shops_id']);
            if (!empty($data['type']) && !empty($data['type_name']) && in_array($data['type'], ['seller_sku', 'upc', 'asin', 'itemURL', 'item_number'])) {
                $collection->where(trim($data['type']), trim($data['type_name']));
            } else if (!empty($data['type']) && !empty($data['type_name']) && in_array($data['type'], ['sku'])) {
                $collection->with('mapping_goods')->whereHas('mapping_goods', function ($query) use ($data) {
                    $query->where(trim($data['type']), trim($data['type_name']));
                });
            }
            if (!empty($shopsPermission)) {
                $collection->whereIn('setting_shops_id',$shopsPermission);
            }
            $pagingData = $collection->with('mapping_goods')->with('platforms', 'shop')->where('goods_mapping.user_id', $user_id)->orderByRaw('status , id DESC')->paginate($limit)->toArray();
            foreach ($pagingData['data'] as &$val) {
                if (is_array($val['mapping_goods'])) {
                    $num = 0;
                    foreach ($val['mapping_goods'] as $mapping_good) {
                        $num += $mapping_good['goods_number'];
                    }
                    $val['goods_number'] = $num;
                }
            }
            //dd(DB::getQueryLog());
            return $pagingData;
        }

        /**
         * @return array
         * Note: 创建关联商品
         * Date: 2019/6/5 10:00
         * Author: zt8067
         */
        public static function createProductProcessing($user_id, $params)
        {
            $results = ['code' => -1, 'msg' => '映射失败', 'data' => '', 'errAll' => []];
            DB::beginTransaction();
            do {
                try {
                    $data = $params['data'];
                    $id = $params['id'];
                    if ($id && is_numeric($id)) {
                        GoodsMappingGoods::where('goods_mapping_id', $id)->delete();
                    }
                    foreach ($data as $k => $item) {
                        $Goods = Goods::where([
                            'id'     => $item['id'],
                            'status' => Goods::STATUS_PASS,
                        ])->first();
                        if (empty($Goods)) {
                            $results['msg'] = '序号' . ($item['LAY_TABLE_INDEX'] + 1) . '：问题商品，请核实！';
                            break 2;
                        }
                        if (empty($item['goods_number'])) {
                            $results['msg'] = '序号' . ($item['LAY_TABLE_INDEX'] + 1) . '：映射数量不能为空或0';
                            break 2;
                        }
                        if (!is_numeric($item['goods_number'])) {
                            $results['msg'] = '序号' . ($item['LAY_TABLE_INDEX'] + 1) . '：映射数量必须为整数';
                            break 2;
                        }
                        $tmp['user_id'] = $user_id;
                        $tmp['created_man'] = $user_id;
                        $tmp['goods_mapping_id'] = $params['id'];
                        $tmp['goods_id'] = $item['id'];
                        $tmp['goods_sku'] = $Goods->sku;
                        $tmp['goods_number'] = $item['goods_number'];
                        GoodsMappingGoods::insert($tmp);
                    }
                    GoodsMapping::where(['id' => $params['id'], 'user_id' => $user_id])->update(['status' => GoodsMapping::MAPPING_YES]);
                    DB::commit();
                    $results['code'] = 1;
                    $results['msg'] = '映射成功';
                } catch (\Exception $e) {
                    DB::rollBack();
                    $results['errAll'] = $e->getMessage();
                }
            } while (0);
            return $results;
        }

        /**
         * @return array
         * Note: 导入映射
         * Date: 2019/6/10 14:10
         * Author: zt8067
         */
        public function updateMapping($request, $user_id)
        {

            $results = ['code' => -1, 'msg' => ''];
            do {
                DB::beginTransaction();
                try {

                    $file = $request->file('file');
                    $suffix = $file->getClientOriginalExtension();
                    $realpath = $file->getRealPath();
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
                    $AmazonTemp = ['店铺', 'SellerSKU', 'ASIN', 'UPC'];
                    $RakutenTemp = ['店铺', '商品管理番号', '商品番号'];
                    for ($i = 1; $i <= 5; $i++) {
                        array_push($AmazonTemp, '自定义SKU' . $i, '数量' . $i);
                        array_push($RakutenTemp, '自定义SKU' . $i, '数量' . $i);
                    }
                    $AmazonTemp0 = array_slice($AmazonTemp, 0, 4);
                    $AmazonCount0 = count($AmazonTemp0);
                    $AmazonTemp1 = array_slice($AmazonTemp, 0, 6);
                    $AmazonTemp2 = array_slice($AmazonTemp, 0, 8);
                    $AmazonTemp3 = array_slice($AmazonTemp, 0, 10);
                    $AmazonTemp4 = array_slice($AmazonTemp, 0, 12);
                    $AmazonTemp5 = array_slice($AmazonTemp, 0, count($AmazonTemp));//14
                    $AmazonTotalCount = count($AmazonTemp5);
                    $RakutenTemp0 = array_slice($RakutenTemp, 0, 3);
                    $RakutenCount0 = count($RakutenTemp0);
                    $RakutenTemp1 = array_slice($RakutenTemp, 0, 5);
                    $RakutenTemp2 = array_slice($RakutenTemp, 0, 7);
                    $RakutenTemp3 = array_slice($RakutenTemp, 0, 9);
                    $RakutenTemp4 = array_slice($RakutenTemp, 0, 11);
                    $RakutenTemp5 = array_slice($RakutenTemp, 0, count($RakutenTemp));//13
                    $RakutenTotalCount = count($RakutenTemp5);
                    Excel::load($realpath, function ($reader) {
                        $this->setInput($reader->getSheet(0)->toArray());
                    });
                    $title = array_filter($this->input[0]);
                    if ($title != $AmazonTemp0 && $title != $AmazonTemp1 && $title != $AmazonTemp2 && $title != $AmazonTemp3 && $title != $AmazonTemp4 && $title != $AmazonTemp5 && $title != $RakutenTemp0 && $title != $RakutenTemp1 && $title != $RakutenTemp2 && $title != $RakutenTemp3 && $title != $RakutenTemp4 && $title != $RakutenTemp5) {
                        $results['code'] = 1;
                        $results['msg'] = '上传文件模板不是系统指定的模板';
                        break;
                    }
                    array_shift($this->input);
                    //判断模板
                    if ($title == $AmazonTemp0 || $title == $AmazonTemp1 || $title == $AmazonTemp2 || $title == $AmazonTemp3 || $title == $AmazonTemp4 || $title == $AmazonTemp5) {
                        $platforms = 'Amazon';
                        $skuColumns = array_column($this->input, 1);
                        foreach ($skuColumns as $kskuColumns => &$skuColumnVal) {
                            if (!empty($skuColumnVal) && is_float($skuColumnVal)) {
                                $skuColumnVal = intval($skuColumnVal);
                            }
                            if(empty($skuColumnVal)){
                                unset($skuColumns[$kskuColumns]);
                            }
                        }
                        $itemUniqueTmp = array_count_values($skuColumns);
                        if (is_array($itemUniqueTmp)) {
                            $skuTmp = [];
                            foreach ($itemUniqueTmp as $key => $itemUniqueValue) {
                                if ($itemUniqueValue > 1) {
                                    array_push($skuTmp, $key);
                                }
                            }
                            if (!empty($skuTmp)) {
                                $results['msg'] = 'SellerSKU：（' . implode(",", $skuTmp) . '） 存在相同重复数据，请核实！';
                                break;
                            }
                        }
                    } else {
                        $platforms = 'Rakuten';
                        $skuColumns = array_column($this->input, 1);
                        foreach ($skuColumns as $kskuColumns => &$skuColumnVal) {
                            if (!empty($skuColumnVal) && is_float($skuColumnVal)) {
                                $skuColumnVal = intval($skuColumnVal);
                            }
                            if(empty($skuColumnVal)){
                                unset($skuColumns[$kskuColumns]);
                            }
                        }
                        $itemUniqueTmp = array_count_values($skuColumns);
                        if (is_array($itemUniqueTmp)) {
                            $skuTmp = [];
                            foreach ($itemUniqueTmp as $key => $itemUniqueValue) {
                                if ($itemUniqueValue > 1) {
                                    array_push($skuTmp, $key);
                                }
                            }
                            if (!empty($skuTmp)) {
                                $results['msg'] = '商品管理番号：（' . implode(",", $skuTmp) . '） 存在相同重复数据，请核实！';
                                break;
                            }
                        }
                    }
                    array_walk($this->input, function (&$v, $k) {
                        foreach ($v as &$vv) {
                            $vv = trim($vv);
                        }
                    });
                    if (!empty($this->input)) {
                        if (!empty($this->_err)) break;
                        $shops = null;
                        $itemURL = null;
                        $item_number = null;
                        $seller_sku = null;
                        $asin = null;
                        $upc = null;
                        $mappingStatus = GoodsMapping::MAPPING_YES;
                        $errAll = [];
                        foreach ($this->input as $k => $v) {
                            $column = count($v);
                            if ($platforms == 'Amazon') {
                                $shops = trim($v[0]);//店铺
                                $seller_sku = trim($v[1]);//SellerSKU
                                $asin = trim($v[2]);//ASIN
                                $upc = trim($v[3]);//UPC
                                $goods_sku = [];//自定义SKU
                                $goods_number = [];//数量
                                $totalCount = 0;
                                for ($z = $AmazonCount0; $z <$column-1; $z++) {
                                    if (!empty(trim($v[$z])) || !empty(trim($v[$z+1]))) {
                                        $totalCount++;
                                    }
                                }
                                if ($totalCount===0) {
                                    $mappingStatus = GoodsMapping::MAPPING_ON;
                                } else {
                                    $mappingStatus = GoodsMapping::MAPPING_YES;
                                }
                                if (empty($shops)) {
                                    $errAll[] = "第" . ($k + 2) . "行：店铺不能为空！";
                                } else {
                                    $SettingShops = SettingShops::where([
                                        'user_id'   => $user_id,
                                        'shop_name' => trim($shops),
                                        'plat_id'   => SettingShops::PLAT_AMAZON,
                                        'status'    => SettingShops::SHOP_STATUS_EMPOWER,
                                        'recycle'   => SettingShops::SHOP_RECYCLE_UNDEL,
                                    ])->select(['shop_name', 'id'])->first();
                                    if (empty($SettingShops)) {
                                        $errAll[] = "第" . ($k + 2) . "行：店铺有误或不可用，请核实！";
                                    }
                                }
                                if (empty($seller_sku)) {
                                    $errAll[] = "第" . ($k + 2) . "行：SellerSKU不能为空！";
                                }
                                if (empty($upc)) {
                                    $errAll[] = "第" . ($k + 2) . "行：UPC不能为空！";
                                }
                                if (empty($asin)) {
                                    $errAll[] = "第" . ($k + 2) . "行：ASIN不能为空！";
                                }
                                if ($AmazonCount0 < $column) {
                                    $goods_sku = [];
                                    $goods_number = [];
                                    for ($i = 0; $i < $totalCount; $i += 2) {
                                        $Index = $AmazonCount0 + $i;
                                        array_push($goods_sku, trim($v[$Index]));
                                        array_push($goods_number, trim($v[$Index + 1]));
                                    }
                                    for ($j = 0; $j < count($goods_sku); $j++) {
                                        if (empty($goods_sku[$j])) {
                                            $errAll[] = "第" . ($k + 2) . "行：自定义SKU" . ($j + 1) . "不能为空！";
                                        }
                                        if (empty($goods_number[$j])) {
                                            $errAll[] = "第" . ($k + 2) . "行：数量" . ($j + 1) . "不能为空或0且必须为整数！";
                                        }
                                        if (preg_match('/[\x80-\xff]+/', $goods_sku[$j])) {
                                            $errAll[] = "第" . ($k + 2) . "行：自定义SKU" . ($j + 1) . "不能存在中文字符！";
                                        }
                                        if (!empty($goods_sku[$j])) {
                                            $GoodsExists = Goods::where([
                                                'user_id'   => $user_id,
                                                'sku'    => $goods_sku[$j],
                                                'status' => Goods::STATUS_PASS,
                                            ])->exists();
                                            if (!$GoodsExists) {
                                                $errAll[] = "第" . ($k + 2) . "行：自定义SKU" . ($j + 1) . "问题商品，请核实！";
                                            }
                                        }
                                    }
                                    $goodsUnique = array_count_values($goods_sku);
                                    foreach ($goodsUnique as $goodsUnique_item) {
                                        if ($goodsUnique_item > 1) {
                                            $errAll[] = "第" . ($k + 2) . "行：存在重复sku，请核实！";
                                            break;
                                        }
                                    }
                                }
                                if (!empty($errAll)) {
                                    continue;
                                }
                            }
                            else {
                                $shops = trim($v[0]);//店铺
                                $itemURL = trim($v[1]);//商品管理番号
                                $item_number = trim($v[2]);//商品番号
                                $goods_sku = [];//自定义SKU
                                $goods_number = [];//数量
                                $totalCount = 0;
                                for ($z = $RakutenCount0; $z <$column-1; $z++) {
                                    if (!empty(trim($v[$z])) || !empty(trim($v[$z+1]))) {
                                        $totalCount++;
                                    }
                                }
                                if ($totalCount===0) {
                                    $mappingStatus = GoodsMapping::MAPPING_ON;
                                } else {
                                    $mappingStatus = GoodsMapping::MAPPING_YES;
                                }
                                if (empty($shops)) {
                                    $errAll[] = "第" . ($k + 2) . "行：店铺不能为空！";
                                } else {
                                    $SettingShops = SettingShops::where([
                                        'user_id'   => $user_id,
                                        'shop_name' => trim($shops),
                                        'plat_id'   => SettingShops::PLAT_RAKUTEN,
                                        'status'    => SettingShops::SHOP_STATUS_EMPOWER,
                                        'recycle'   => SettingShops::SHOP_RECYCLE_UNDEL,
                                    ])->select(['shop_name', 'id'])->first();
                                    if (empty($SettingShops)) {
                                        $errAll[] = "第" . ($k + 2) . "行：店铺有误或不可用，请核实！";
                                    }
                                }
                                if (empty($itemURL)) {
                                    $errAll[] = "第" . ($k + 2) . "行：商品管理番号不能为空！";
                                }
                                if (empty($item_number)) {
                                    $errAll[] = "第" . ($k + 2) . "行：商品番号不能为空！";
                                }
                                if ($RakutenCount0 < $column) {
                                    $goods_sku = [];
                                    $goods_number = [];
                                    for ($i = 0; $i < $totalCount; $i += 2) {
                                        $Index = $RakutenCount0 + $i;
                                        array_push($goods_sku, trim($v[$Index]));
                                        array_push($goods_number, trim($v[$Index + 1]));

                                    }
                                    for ($j = 0; $j < count($goods_sku); $j++) {
                                        if (empty($goods_sku[$j])) {
                                            $errAll[] = "第" . ($k + 2) . "行：自定义SKU" . ($j + 1) . "不能为空！";
                                        }
                                        if (empty($goods_number[$j])) {
                                            $errAll[] = "第" . ($k + 2) . "行：数量" . ($j + 1) . "不能为空或0且必须为整数！";
                                        }
                                        if (preg_match('/[\x80-\xff]+/', $goods_sku[$j])) {
                                            $errAll[] = "第" . ($k + 2) . "行：自定义SKU" . ($j + 1) . "不能存在中文字符！";
                                        }
                                        if (!empty($goods_sku[$j])) {
                                            $GoodsExists = Goods::where([
                                                'user_id'   => $user_id,
                                                'sku'    => $goods_sku[$j],
                                                'status' => Goods::STATUS_PASS,
                                            ])->exists();
                                            if (!$GoodsExists) {
                                                $errAll[] = "第" . ($k + 2) . "行：自定义SKU" . ($j + 1) . "问题商品，请核实！";
                                            }
                                        }
                                    }
                                    $goodsUnique = array_count_values($goods_sku);
                                    foreach ($goodsUnique as $goodsUnique_item) {
                                        if ($goodsUnique_item > 1) {
                                            $errAll[] = "第" . ($k + 2) . "行：存在重复sku，请核实！";
                                            break;
                                        }
                                    }
                                }
                                if (!empty($errAll)) {
                                    $this->_err = array_merge($this->_err, $errAll);
                                    continue;
                                }
                            }
                            $platformsId = $platforms == 'Amazon' ? 1 : 2;
                            $connection = GoodsMapping::query();
                            if ($platformsId === 1) {
                                $connection->where('seller_sku', $seller_sku);
                            } else {
                                $connection->where('itemURL', $itemURL);
                            }
                            $GoodsMapping = $connection->where('user_id', $user_id)->select(['id'])->first();
                            if ($GoodsMapping) {
                                $GoodsMappingOldId = $GoodsMapping['id'];
                                GoodsMappingGoods::where('goods_mapping_id', $GoodsMappingOldId)->delete();//删除原有关联商品
                            } else {
                                $GoodsMappingOldId = 0;
                            }
                            $GoodsMappingValues = [
                                'user_id'          => $user_id,
                                'created_man'      => $user_id,
                                'platform_id'      => $platformsId,
                                'setting_shops_id' => $SettingShops->id,
                                'itemURL'          => $itemURL,
                                'item_number'      => $item_number,
                                'seller_sku'       => $seller_sku,
                                'asin'             => $asin,
                                'upc'              => $upc,
                                'status'           => $mappingStatus,
                            ];
                            if ($GoodsMappingOldId) {
                                $status = GoodsMapping::where('id', $GoodsMappingOldId)->update($GoodsMappingValues);
                                $GoodsMappingId = $GoodsMappingOldId;
                            } else {
                                $status = $GoodsMappingId = GoodsMapping::insertGetId($GoodsMappingValues);
                            }
                            if ($RakutenCount0 < $column) {
                                for ($y = 0; $y < count($goods_sku); $y++) {
                                    $Goods = Goods::where('sku', $goods_sku[$y])->first();
                                    $goodsValues = [
                                        'user_id'          => $user_id,
                                        'created_man'      => $user_id,
                                        'goods_mapping_id' => $GoodsMappingId,
                                        'goods_id'         => $Goods->id,
                                        'goods_sku'        => $Goods->sku,
                                        'goods_number'     => $goods_number[$y],
                                    ];
                                    $status = GoodsMappingGoods::insert($goodsValues);
                                }
                                if ($status) {
                                    $this->_err[] = "第" . ($k + 2) . "行：导入映射成功！";
                                }
                            }
                        }
                    } else {
                        $results['code'] = 1;
                        $results['msg'] = '上传文件内容为空！';
                        break;
                    }
                    if (!empty($errAll)) {
                        break;
                    }
                    DB::commit();
                    $results['code'] = 1;
                    $results['msg'] = '导入商品映射成功！';
                } catch (\Exception $e) {
                    $results['err'] = $e->getMessage() . $e->getLine();
                    DB::rollBack();
                    //                    Common::mongoLog($e,'配货单','导入物流跟踪号',__FUNCTION__);
                } catch (\Error $e) {
                    DB::rollBack();
                    //                    Common::mongoLog($e,'配货单','导入物流跟踪号',__FUNCTION__);
                } finally {
                    if (!empty($errAll)) {
                        $this->_err = $errAll;
                    }
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
         * Note: 导出列表方法
         * Date: 2019/6/11 16:00
         * Author: zt8067
         */
        public function exportProcessing($request, $user_id)
        {
            $ids = $request->input('ids');
            $connection = GoodsMapping::query();
            if ($ids != 'undefined') {
                if (!is_numeric($ids)) {
                    $ids = explode(',', $ids);
                    $connection->whereIn('id', $ids);
                } else {
                    $connection->where('id', $ids);
                }
            }
            $GoodsMappingM = $connection->with('platforms', 'shop', 'mapping_goods')->where('user_id', $user_id)->orderByRaw('status , id DESC')->get();
            if ($GoodsMappingM->isEmpty()) {
                throw new DataNotFoundException();
            }
            $data = $GoodsMappingM->toArray();
            foreach ($data as &$val) {
                if (is_array($val['mapping_goods'])) {
                    $num = 0;
                    foreach ($val['mapping_goods'] as $mapping_good) {
                        $num += $mapping_good['goods_number'];
                    }
                    $val['goods_number'] = $num;
                }
            }
            $title = ['平台', '店铺', '商品管理番号', '商品番号', 'SellerSKU', 'ASIN', 'UPC', '自定义SKU', '映射数量', '映射状态'];
            foreach ($this->exportYield($data) as $k => $item) {
                $mapping_goods = null;
                if (isset($item['mapping_goods']) && is_array($item['mapping_goods'])) {
                    $mapping_goods = array_column($item['mapping_goods'], 'goods_sku');
                }
                $cellData[$k][] = $item['platforms']['name_CN'] ?? '';
                $cellData[$k][] = $item['shop']['shop_name'] ?? '';
                $cellData[$k][] = $item['itemURL'] ?? '';
                $cellData[$k][] = $item['item_number'] ?? '';
                $cellData[$k][] = $item['seller_sku'] ?? '';
                $cellData[$k][] = $item['asin'] ?? '';
                $cellData[$k][] = $item['upc'] ?? '';
                $cellData[$k][] = implode(',', $mapping_goods) ?? '';
                $cellData[$k][] = empty($item['goods_number']) ? '' : $item['goods_number'];
                $cellData[$k][] = $item['status'] == GoodsMapping::MAPPING_ON ? '未映射' : '已映射';
            }
            array_unshift($cellData, $title);
            $name = iconv('UTF-8', 'GBK', date('Y-m-d') . ' 商品信息');
            Excel::create($name, function ($excel) use ($cellData) {
                $excel->sheet('score', function ($sheet) use ($cellData) {
                    $Letter = [
                        'A' => '20',
                        'B' => '30',
                        'C' => '20',
                        'D' => '20',
                        'E' => '20',
                        'F' => '20',
                        'G' => '20',
                        'H' => '50',
                        'I' => '10',
                        'J' => '10',
                    ];
                    $sheet->rows($cellData);
                    $sheet->setWidth($Letter);
                    $Letters = array_keys($Letter);
                    foreach ($Letters as $val) {
                        $sheet->cells("{$val}1:{$val}" . count($cellData), function ($cells) {
                            $cells->setAlignment('center');
                        });
                    }
                });
            })->store('xls')->export('xls');
        }

        //        /**  */  废弃
        //         * @return array
        //         * Note: 导出列表商品映射方法
        //         * Date: 2019/6/11 15:58
        //         * Author: zt8067
        //         */
        //        public function exportMappingProcessing($request, $user_id)
        //        {
        //
        //            $ids = $request->input('ids');
        //            $connection = GoodsMapping::query();
        //            if ($ids != 'undefined') {
        //                if (!is_numeric($ids)) {
        //                    $ids = explode(',', $ids);
        //                    $connection->whereIn('id', $ids);
        //                } else {
        //                    $connection->where('id', $ids);
        //                }
        //            }
        //            $GoodsMappingM = $connection->with('platforms', 'shop', 'mapping_goods')->where('user_id', $user_id)->get();
        //            if ($GoodsMappingM->isEmpty()) {
        //                throw new DataNotFoundException();
        //            }
        //            $data = $GoodsMappingM->toArray();
        //            $title = ['平台', '店铺', '商品管理番号/SellerSKU'];
        //            foreach ($this->exportYield($data) as $k => $item) {
        //                $mapping_goods = null;
        //                if (isset($item['mapping_goods']) && is_array($item['mapping_goods'])) {
        //                    $mapping_goods = array_column($item['mapping_goods'], 'goods_sku');
        //                }
        //                $cellData[$k][] = $item['platforms']['name_CN'] ?? '';
        //                $cellData[$k][] = $item['shop']['shop_name'] ?? '';
        //                if ($item['platform_id'] == 1) {
        //                    $cellData[$k][] = $item['seller_sku'] ?? '';
        //                } else {
        //                    $cellData[$k][] = $item['itemURL'] ?? '';
        //                }
        //                if (!empty($item['mapping_goods'])) {
        //                    $j = 1;
        //                    foreach ($item['mapping_goods'] as $product) {
        //                        $sku = '自定义SKU' . $j;
        //                        $num = $j . '数量';
        //                        if (!in_array($sku, $title) && !in_array($num, $title)) {
        //                            array_push($title, $sku, $num);
        //                        }
        //                        $cellData[$k][] = $product['goods_sku'];
        //                        $cellData[$k][] = $product['goods_number'];
        //                        $j++;
        //                    }
        //                }
        //            }
        //            array_unshift($cellData, $title);
        //            $name = iconv('UTF-8', 'GBK', date('Y-m-d') . ' 商品映射信息');
        //            Excel::create($name, function ($excel) use ($cellData) {
        //                $excel->sheet('score', function ($sheet) use ($cellData) {
        //                    $Letter = [
        //                        'A' => '20',
        //                        'B' => '30',
        //                        'C' => '30',
        //                        'D' => '15',
        //                        'E' => '10',
        //                        'F' => '15',
        //                        'G' => '10',
        //                        'H' => '15',
        //                        'I' => '10',
        //                        'J' => '15',
        //                        'K' => '10',
        //                        'L' => '15',
        //                        'M' => '10',
        //                    ];
        //                    $sheet->rows($cellData);
        //                    $sheet->setWidth($Letter);
        //                    $Letters = array_keys($Letter);
        //                    foreach ($Letters as $val) {
        //                        $sheet->cells("{$val}1:{$val}" . count($cellData), function ($cells) {
        //                            $cells->setAlignment('center');
        //                        });
        //                    }
        //                });
        //            })->store('xls')->export('xls');
        //        }
        /**
         * @author zt8067
         * 导出信息迭代器去
         * @param $yield_arr
         * @return \Generator
         */
        public function exportYield($yield_arr)
        {
            for ($i = 0; $i < count($yield_arr); $i++) {
                yield $yield_arr[$i];
            }
        }
    }