<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/6/12
     * Time: 14:13
     */

    namespace App\Http\Services\Goods;

    use App\Common\Common;
    use App\Exceptions\DataNotFoundException;
    use App\Http\Services\Rakuten\DeleteItem;
    use App\Http\Services\Rakuten\InsertItem;
    use App\Models\GoodsDraftRakuten;
    use App\Models\GoodsOnlineRakuten;
    use App\Models\GoodsOnlineRakutenPics;
    use App\Models\SettingShops;
    use Illuminate\Support\Facades\DB;

    class RakutenGoodsHandle
    {
        /**
         * @return array
         * Note: 乐天商品上架方法
         * Date: 2019/6/12 14:10
         * Author: zt8067
         */
        public function putOnSaleProcessing($ids, $user_id, $type)
        {
            $results = ['code' => -1, 'msg' => '', 'errAll' => []];
            do {
                DB::beginTransaction();
                try {
                    if ($type == 'local') {
                        $connection = GoodsDraftRakuten::query();
                    } else {
                        $connection = GoodsOnlineRakuten::query();
                    }
                    $connection->where('user_id', $user_id);
                    if (is_numeric($ids)) {
                        $connection->where('id', $ids);
                    } else {
                        $ids = explode(',', $ids);
                        $connection->whereIn('id', $ids);
                    }
                    $GoodsDraftRakutenM = $connection->with(['pictures'])->get();
                    if ($GoodsDraftRakutenM->isEmpty()) {
                        throw new DataNotFoundException();
                    }
                    $GoodsDraftRakuten = $GoodsDraftRakutenM->toArray();
                    foreach ($GoodsDraftRakuten as $item) {
                        //必填项
                        if (empty($item['goods_name']) || empty($item['sale_price']) || (empty($item['rakuten_category_id']) && empty($item['catalogIdExemptionReason']))) {
                            $results['errAll'][] = "自定义SKU ：{$item['local_sku']} 未完善信息，不能上架。";
                            continue;
                        }
                        $onSale = InsertItem::treatmentProcess($item, $user_id);

                        if ($onSale['code'] === 1) {
                            //上架成功修改状态，复制数据到在线数据库
                            if ($type == 'local') {
                                GoodsDraftRakuten::where(['id' => $item['id']])->update(['synchronize_status' => GoodsDraftRakuten::SYNCHRONIZE_STATUS_SUCCESS, 'synchronize_to_rakuten_time' => date("Y-m-d H:i:s")]);
                                $this->copyToOnlineRatuken($item, $type);
                            } else {
                                GoodsOnlineRakuten::where(['id' => $item['id']])->update(['synchronize_status' => GoodsOnlineRakuten::SYNCHRONIZE_STATUS_NORMAL, 'synchronize_to_rakuten_time' => date("Y-m-d H:i:s")]);
                            }
                            $results['code'] = 1;
                        }
                        else {
                            if ($type == 'local') {
                                GoodsDraftRakuten::where(['id' => $item['id']])->update(['synchronize_status' => GoodsDraftRakuten::SYNCHRONIZE_STATUS_ERROR, 'synchronize_info' => $onSale['msg'], 'synchronize_to_rakuten_time' => date("Y-m-d H:i:s")]);
                            } else {
                                GoodsOnlineRakuten::where(['id' => $item['id']])->update(['synchronize_status' => GoodsOnlineRakuten::SYNCHRONIZE_STATUS_ERROR, 'synchronize_info' => $onSale['msg'], 'synchronize_to_rakuten_time' => date("Y-m-d H:i:s")]);
                            }
                            $results['errAll'][] = "自定义SKU ：" . $item['local_sku'] . $onSale['msg'];
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Common::mongoLog($e,'乐天商品上架','乐天商品上架失败',__FUNCTION__);
                    $results['msg'] = $e->getMessage();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Common::mongoLog($e,'乐天商品上架','乐天商品上架失败',__FUNCTION__);
                }
            } while (0);
            return $results;
        }

        /**
         * @return array
         * Note: 复制草稿箱信息到在线商品表
         * Date: 2019/6/18 10:10
         * Author: zt8067
         */
        protected function copyToOnlineRatuken($data)
        {
            //新增在线商品表数据
            $GoodsOnlineRakutenM = new GoodsOnlineRakuten();
            $GoodsOnlineRakutenM->category_id_1 = $data['category_id_1'];
            $GoodsOnlineRakutenM->category_id_2 = $data['category_id_2'];
            $GoodsOnlineRakutenM->category_id_3 = $data['category_id_3'];
            $GoodsOnlineRakutenM->created_man = $data['created_man'];
            $GoodsOnlineRakutenM->goods_attribute_id = $data['goods_attribute_id'];
            $GoodsOnlineRakutenM->cmn = $data['cmn'];
            $GoodsOnlineRakutenM->sku = $data['sku'];
            $GoodsOnlineRakutenM->title = $data['title'];
            $GoodsOnlineRakutenM->sale_price = $data['sale_price'];
            $GoodsOnlineRakutenM->img_url = $data['img_url'];
            $GoodsOnlineRakutenM->currency_code = $data['currency_code'];
            $GoodsOnlineRakutenM->platform_in_stock = $data['platform_in_stock'];
            $GoodsOnlineRakutenM->synchronize_status = GoodsOnlineRakuten::SYNCHRONIZE_STATUS_NORMAL;
            $GoodsOnlineRakutenM->synchronize_info = $data['synchronize_info'];
            $GoodsOnlineRakutenM->goods_id = $data['goods_id'];
            $GoodsOnlineRakutenM->rakuten_category_id = $data['rakuten_category_id'];
            $GoodsOnlineRakutenM->synchronize_from_local_date = date("Y-m-d H:i:s");//从草稿箱同步过来的时间
            $GoodsOnlineRakutenM->synchronize_to_rakuten_time = date("Y-m-d H:i:s");
            $GoodsOnlineRakutenM->belongs_shop = $data['belongs_shop'];
            $GoodsOnlineRakutenM->goods_description = $data['goods_description'];
            $GoodsOnlineRakutenM->goods_weight = $data['goods_weight'];
            $GoodsOnlineRakutenM->goods_length = $data['goods_length'];
            $GoodsOnlineRakutenM->goods_width = $data['goods_width'];
            $GoodsOnlineRakutenM->user_id = $data['user_id'];
            $GoodsOnlineRakutenM->goods_name = $data['goods_name'];
            $GoodsOnlineRakutenM->local_sku = $data['local_sku'];
            $GoodsOnlineRakutenM->catalogId = $data['catalogId'];
            $GoodsOnlineRakutenM->catalogIdExemptionReason = $data['catalogIdExemptionReason'];
            if ($GoodsOnlineRakutenM->save()) {
                if (isset($data['pictures']) && !empty($data['pictures'])) {
                    foreach ($data['pictures'] as $picture) {
                        $GoodsOnlineRakutenPics = new GoodsOnlineRakutenPics();
                        $GoodsOnlineRakutenPics->goods_id = $picture['goods_id'];
                        $GoodsOnlineRakutenPics->created_man = $picture['created_man'];
                        $GoodsOnlineRakutenPics->link = $picture['link'];
                        $GoodsOnlineRakutenPics->user_id = $picture['user_id'];
                        $GoodsOnlineRakutenPics->save();
                    }
                }
            }
        }

        /**
         * @return array
         * Note: 乐天商品图片asyn上传处理方法
         * Date: 2019/6/17 11:10
         * Author: zt8067
         */
        public function asynFtpImageProcessing($data)
        {
            (new InsertItem())->eachProcessing($data);
        }

        /**
         * @return array
         * Note: 乐天商品下架处理方法
         * Date: 2019/6/19 10:10
         * Author: zt8067
         */
        public function obtainedProcessing($ids, $user_id)
        {
            $results = ['code' => -1, 'msg' => '', 'errAll' => []];
            do {
                DB::beginTransaction();
                try {
                    $connection = GoodsOnlineRakuten::query();
                    $connection->with('pictures');
                    if (is_numeric($ids)) {
                        $connection->where('id', $ids);
                    } else {
                        $ids = explode(',', $ids);
                        $connection->whereIn('id', $ids);
                    }
                    $GoodsOnlineRakutenM = $connection->where('user_id', $user_id)->get(['id','cmn', 'belongs_shop', 'local_sku']);
                    if ($GoodsOnlineRakutenM->isEmpty()) {
                        throw new DataNotFoundException();
                    }
                    $GoodsOnlineRakuten = $GoodsOnlineRakutenM->toArray();
                    foreach ($GoodsOnlineRakuten as $value) {
                        $SettingShops = SettingShops::where(['id' => $value['belongs_shop'], 'user_id' => $user_id])->first();
                        if (empty($SettingShops)) {
                            $results['errAll'][] = '关联店铺不存在！';
                            continue;
                        }
                        if (empty($value['local_sku'])) {
                            $results['errAll'][] = '自定义SKU不存在！';
                            continue;
                        }
                        $access = [];
                        $access['appKey'] = $SettingShops->service_secret;
                        $access['appSecret'] = $SettingShops->license_key;
                        if (empty($access['appKey']) || empty($access['appSecret'])) {
                            throw new DataNotFoundException('乐天API秘钥缺失！');
                        }
                        $res = DeleteItem::treatmentProcess($value, $access);
                        if ($res['code'] === 1) {
                            $results['code'] = 1;
                            $results['errAll'][] = "自定义SKU ：" . $value['local_sku'] . "下架成功！";
                            GoodsOnlineRakuten::where(['id' => $value['id']])->update(['synchronize_status' => GoodsOnlineRakuten::SYNCHRONIZE_STATUS_SUCCESS]);
                        } else {
                            $results['errAll'][] = "自定义SKU ：" . $value['local_sku'] . $res['msg'];
                            GoodsOnlineRakuten::where(['id' => $value['id']])->update(['synchronize_status' => GoodsOnlineRakuten::SYNCHRONIZE_STATUS_ERROR, 'synchronize_info' => implode(',', $res['msg'])]);
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $results['msg'] = $e->getMessage();
                    Common::mongoLog($e,'乐天下架商品','下架商品失败',__FUNCTION__);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Common::mongoLog($e,'乐天下架商品','下架商品失败',__FUNCTION__);
                }
            } while (0);
            return $results;
        }
    }