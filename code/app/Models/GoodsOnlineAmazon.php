<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Goods\AmazonSaleFeed;
use Mockery\Exception;

class GoodsOnlineAmazon extends Model
{
    protected $table = 'goods_online_amazon';
    public $timestamps = true;
    public $primaryKey = 'id';

    protected $fillable = ["id","user_id","created_man","category_id_1","category_id_2","category_id_3","goods_attribute_id","amazon_category_id","AmazonBrowseNodeID","parentBrowseName","belongs_shop","seller_sku","ASIN","upc","goods_id","local_sku","title","goods_name","sale_price","img_url","currency_code","platform_in_stock","synchronize_from_local_date","synchronize_to_rakuten_time","brand","manufacturer","color","goods_size","goods_status","goods_keywords","promotion_price","promotion_start_time","promotion_end_time","goods_weight","goods_height","goods_length","goods_width","goods_label","goods_description","put_on_status","put_off_status","synchronize_info","created_at","updated_at","goods_draft_amazon_id"];
    /**
     * @var 上架状态初始化
     */
    const PUTON_INIT = 0;
    /**
     * @var 上架成功
     */
    const PUTON_SUCC = 1;
    /**
     * @var 上架失败
     */
    const PUTON_FAIL = 2;

    /**
     * @var 下架状态初始化
     */
    const PUTOFF_INIT = 0;
    /**
     * @var 下架成功
     */
    const PUTOFF_SUCC = 1;
    /**
     * @var 下架失败
     */
    const PUTOFF_FAIL = 2;

    /**
     * @var 商品上架
     */
    const ON_SALE = 1;
    /**
     * @var 商品上架初始化
     */
    const ON_SALE_INITIALIZE = 0;
    /**
     * @var 商品下架
     */
    const OFF_SALE = 2;
    /**
     * @var 商品下架
     */
    const OFF_SALE_INITIALIZE = 0;
    /**
     * @var 商品更新失败
     */
    const SYNCHRONIZE_FAILED = 3;

    public function Shops()
    {
        return $this->hasOne(SettingShops::class, 'id', 'belongs_shop');
    }

    public function Procurement()
    {
        return $this->hasOne(Procurements::class, 'goods_id', 'goods_id');
    }

    public function GoodsPics()
    {
        return $this->hasMany(GoodsOnlineAmazonPics::class, 'goods_id', 'id');
    }



    /**
     * 获取草稿箱数据
     * Auth: zt12779
     * create at:2019/06/03
     * @param $params
     * @return mixed
     */
    public static function getList($params, $user_id)
    {
        $page = (isset($params['page']) && !empty((int)$params['page'])) ? $params['page'] : 1;
        $limit = (isset($params['limit']) && !empty((int)$params['limit'])) ? $params['limit'] : 20;
        $collection = self::with('Shops')->with('Procurement');
        if (isset($params['source_shop']) && !empty($params['source_shop'])) {
            $collection->whereIn('belongs_shop', $params['source_shop']);
        }
        if (isset($params['sku']) && !empty($params['sku'])) {
            $collection->where('sku', $params['sku']);
        }
        if (isset($params['goods_name']) && !empty($params['goods_name'])) {
            $collection->where('title', 'like', "%{$params['goods_name']}%");
        }
        if (isset($params['ASIN']) && !empty($params['ASIN'])) {
            $collection->where('product_code', $params['ASIN']);
        }
        if (isset($params['local_sku']) && !empty($params['local_sku'])) {
            $collection->where('local_sku', 'like', "%{$params['local_sku']}%");
        }

        $collection->where('user_id', $user_id);

        $selection = ['local_sku', 'title', 'goods_id', 'ASIN',
            'goods_width', 'goods_height', 'goods_length', 'goods_weight', 'seller_sku',
            'sale_price', 'currency_code', 'belongs_shop', 'put_on_status', 'put_off_status', 'id','img_url','synchronize_info'];

        if ($params['synchronizeType'] == 1) {
            $collection->where('put_on_status', 1);
        } elseif ($params['synchronizeType'] == 2) {
            $collection->where('put_off_status', 1);
        } else {
            $collection->where('synchronize_info', '!=', '')->where('put_on_status', 1);
        }
        $collection->select($selection)->orderByDesc('created_at');
        return $collection->paginate($limit, ['*'], 'page', $page)->toArray();
    }

    public static function getOne($id)
    {
        $collection = self::with('Shops')->where('goods_online_amazon.id', $id)
            ->with('GoodsPics')
            ->join('goods_attribute', 'goods_attribute.id', '=', 'goods_online_amazon.goods_attribute_id')
            ->select('goods_online_amazon.*', 'goods_attribute.attribute_name')
            ->first();
        return $collection;

    }

    public static function getExportData(array $ids)
    {
        $collection = self::whereIn('goods_online_amazon.id', $ids)
            ->select('goods_online_amazon.*', 'setting_shops.shop_name')
            ->join('setting_shops', 'setting_shops.id', '=', 'goods_online_amazon.belongs_shop')
            ->get()->toArray();
        return $collection;
    }

    public static function updateById($id,$data,$user_id) {
        if($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return self::where('id',$id)->where('user_id',$user_id)->update($data);
        }
        $data['user_id'] = $user_id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        return self::insertGetId($data);
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     * Note: 获取在线商品信息
     * Data: 2019/6/19 15:53
     * Author: zt7785
     */
    public static function getGoodsInfoByIdUserId($id, $user_id)
    {
        $collection = self::with('GoodsPics')->where('user_id',$user_id)->where('id',$id)->first();
        if (empty($collection)) {
            return [];
        }
        return $collection->toArray();
    }

    public static function postData($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param $ids
     * @param $user_id
     * Note: 上架或批量上架及更新
     * Data: 2019/6/19 10:47
     * Author: zt7785
     */
    public static function putOnSaleById($ids,$user_id)
    {
        $respone ['exception_status'] = true;
        $respone ['exception_info'] = '';
        $respone ['data'] = [];
        $field = ['*'];
        //下架成功的商品才允许上架
        $goodsDraftInfos = self::with('GoodsPics')->where([
            ['user_id',$user_id],
            ['put_off_status',self::PUTOFF_SUCC],
        ])->whereIn('id',$ids)->get($field);
        if ($goodsDraftInfos->isEmpty()) {
            $respone ['exception_info'] = '上架商品信息异常';
            return $respone;
        }
        $goodsDraftInfos = $goodsDraftInfos->toArray();
        //上架请求队列
        $AmazonSaleFeed = new AmazonSaleFeed();
        $current_time = date('Y-m-d H:i:s');
        DB::beginTransaction();
        try {
            foreach ($goodsDraftInfos as $goodsDraftInfoKey => $goodsDraftInfoVal) {
                //必要参数校验
                $paramsChecked = self::checkPutOnRequireddata($goodsDraftInfoVal);
                if (empty($paramsChecked)) {
                    $respone ['exception_info'] = '上架商品信息异常';
                    break;
                }
                $onlinGoodsData = [];
                $temGoodsDraftInfoPic = [];
                $feedImageParam = [];
                //如果有附图
                if (isset($goodsDraftInfoVal ['pictures']) && !empty($goodsDraftInfoVal ['pictures'])) {
                    //附图
                    $amazonProductI = 1;
                    foreach ($goodsDraftInfoVal['pictures'] as $k => $v) {
                        if ($amazonProductI > 5) {
                            continue;
                        }
                        $feedImageParam [$goodsDraftInfoVal ['seller_sku'] . '_' . $amazonProductI] = [
                            'type' => 'Alternate',
                            'url' => url('showImage') . '?path=' . $v['link']
                        ];
                        $amazonProductI++;
                    }
                }
                unset($goodsDraftInfoVal ['pictures']);
                //商品上架队列写数据
                $feedParam ['sku'] = $goodsDraftInfoVal ['seller_sku'];
                //不涉及币种 币种根据站点信息
                $feedParam ['price'] = $goodsDraftInfoVal ['sale_price'];
                if ($goodsDraftInfoVal ['ASIN']) {
                    $feedParam ['product_id'] = $goodsDraftInfoVal ['ASIN'];
                    $feedParam ['product_id_type'] = 'ASIN';
                } else if ($goodsDraftInfoVal ['upc']) {
                    $feedParam ['product_id'] = $goodsDraftInfoVal ['upc'];
                    $feedParam ['product_id_type'] = 'UPC';
                }
                $feedParam ['condition_type'] = 'New';
                $feedParam ['quantity'] = $goodsDraftInfoVal ['platform_in_stock'];
                if (empty($feedParam ['quantity'])) {
                    $respone ['exception_info'] = '亚马逊平台库存不能为0';
                    break;
                }
                $feedParam ['title'] = $goodsDraftInfoVal ['title'];
                $feedParam ['brand'] = $goodsDraftInfoVal ['brand'];
                $feedParam ['recommended_browse_nodes'] = $goodsDraftInfoVal ['AmazonBrowseNodeID'];
                //重量
                if ($goodsDraftInfoVal ['goods_weight'] > 0) {
                    $feedParam ['weight'] = $goodsDraftInfoVal ['goods_weight'];
                }
                $listData['method'] = 'putOn';//上架
                $listData['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                $listData['request_pk'] = $goodsDraftInfoVal ['id'];//亚马逊商品模型主键
                $listData['request_user_id'] = $user_id;
                $listData['request_shop_id'] = $goodsDraftInfoVal ['belongs_shop'];//亚马逊店铺
                $listData ['params'] = $feedParam;

                //写上架基础数据
                $AmazonSaleFeed->putOnListPush($listData);

                //促销价格
                if ($goodsDraftInfoVal ['promotion_price'] > 0) {
                    //促销时间
                    $standardPrice [$goodsDraftInfoVal ['seller_sku']] = $goodsDraftInfoVal['sale_price'];
                    $feedSalePrice [$goodsDraftInfoVal ['seller_sku']] ['StartDate'] = $goodsDraftInfoVal['promotion_start_time'];
                    $feedSalePrice [$goodsDraftInfoVal ['seller_sku']] ['EndDate'] = $goodsDraftInfoVal['promotion_end_time'];
                    $feedSalePrice [$goodsDraftInfoVal ['seller_sku']] ['SalePrice'] =
                        number_format($goodsDraftInfoVal['promotion_price'], 2, '.', '');
                    $editPrice ['method'] = 'editPrice';
                    $editPrice['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                    $editPrice['request_pk'] = $goodsDraftInfoVal ['id'];//亚马逊商品模型主键
                    $editPrice['request_user_id'] = $user_id;
                    $editPrice['request_shop_id'] = $goodsDraftInfoVal ['belongs_shop'];//亚马逊店铺
                    $editPrice ['params'] = ['standardprice' => $standardPrice, 'saleprice' => $feedSalePrice];
                    //写上架商品价格数据
                    $AmazonSaleFeed->putOnListPush($editPrice);
                }

                //图片信息
                if ($goodsDraftInfoVal ['img_url']) {
                    $feedImageParam [$goodsDraftInfoVal ['seller_sku']] = [
                        'type' => 'Main',
                        'url' => url('showImage') . '?path=' . $goodsDraftInfoVal['img_url']
                    ];
                    $editPrice ['method'] = 'editGoodsImage';
                    $editPrice['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                    $editPrice['request_pk'] = $goodsDraftInfoVal ['id'];//亚马逊商品模型主键
                    $editPrice['request_user_id'] = $user_id;
                    $editPrice['request_shop_id'] = $goodsDraftInfoVal ['belongs_shop'];//亚马逊店铺
                    $editPrice ['params'] = ['standardImage' => $feedImageParam];
                    //写上架商品价格数据
                    $AmazonSaleFeed->putOnListPush($editPrice);
                }
            }
            $updateData = [
                'put_on_status' => self::ON_SALE_INITIALIZE,
                'put_off_status' => self::OFF_SALE_INITIALIZE,
                'updated_at' => $current_time
            ];
            self::whereIn('id' ,$ids)->update($updateData);
            if ($respone ['exception_info'] == '') {
                $respone ['exception_status'] = false;
                DB::commit();
            } else {
                DB::rollback();
                $respone ['exception_info'] = '上架失败';
            }
        } catch (Exception $exception) {
            DB::rollback();
            $respone ['exception_info'] = '未知错误';
        }
        return $respone;
    }

    /**
     * @param $online_goods_id
     * @return mixed
     * Note: 编辑在线商品触发接口请求
     * Data: 2019/6/19 17:49
     * Author: zt7785
     */
    public static function updateOnlineProduct($online_goods_id)
    {
        $respone ['exception_status'] = true;
        $respone ['exception_info'] = '';
        $respone ['data'] = $feedImageParam = [];
        $onlineProductInfo = self::with('GoodsPics')->where('id',$online_goods_id)->first();
        if (empty($onlineProductInfo)) {
            $respone ['exception_info'] = '上架商品信息异常';
            return $respone;
        }

        $onlineProductInfo = $onlineProductInfo->toArray();
        $checkStatus = self::checkPutOnRequireddata($onlineProductInfo);
        if (empty($checkStatus)) {
            $respone ['exception_info'] = '上架商品信息异常';
            return $respone;
        }
        $AmazonSaleFeed = new AmazonSaleFeed();
        //商品上架队列写数据
        $feedParam ['sku'] = $onlineProductInfo ['seller_sku'];
        //不涉及币种 币种根据站点信息
        $feedParam ['price'] = $onlineProductInfo ['sale_price'];
        if ($onlineProductInfo ['ASIN']) {
            $feedParam ['product_id'] = $onlineProductInfo ['ASIN'];
            $feedParam ['product_id_type'] = 'ASIN';
        } else if ($onlineProductInfo ['upc']) {
            $feedParam ['product_id'] = $onlineProductInfo ['upc'];
            $feedParam ['product_id_type'] = 'UPC';
        }
        $feedParam ['condition_type'] = 'New';
        $feedParam ['quantity'] = $onlineProductInfo ['platform_in_stock'];
        if (empty($feedParam ['quantity'])) {
            $respone ['exception_info'] = '亚马逊平台库存不能为0';
            return $respone;
        }
        $feedParam ['title'] = $onlineProductInfo ['title'];
        $feedParam ['brand'] = $onlineProductInfo ['brand'];
        $feedParam ['recommended_browse_nodes'] = $onlineProductInfo ['AmazonBrowseNodeID'];
        $feedParam['OperationType'] = 'Update';//更新

        //重量
        if ($onlineProductInfo ['goods_weight'] > 0) {
            $feedParam ['weight'] = $onlineProductInfo ['goods_weight'];
        }
        $listData['method'] = 'putOn';//上架
        $listData['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
        $listData['request_pk'] = $onlineProductInfo ['id'];//亚马逊商品模型主键
        $listData['request_user_id'] = $onlineProductInfo ['user_id'];
        $listData['request_shop_id'] = $onlineProductInfo ['belongs_shop'];//亚马逊店铺
        $listData ['params'] = $feedParam;

        //写上架基础数据
        $AmazonSaleFeed->putOnListPush($listData);

        //促销价格
        if ($onlineProductInfo ['promotion_price'] > 0) {
            //促销时间
            $standardPrice [$onlineProductInfo ['seller_sku']] = $onlineProductInfo['sale_price'];
            $feedSalePrice [$onlineProductInfo ['seller_sku']] ['StartDate'] = $onlineProductInfo['promotion_start_time'];
            $feedSalePrice [$onlineProductInfo ['seller_sku']] ['EndDate'] = $onlineProductInfo['promotion_end_time'];
            $feedSalePrice [$onlineProductInfo ['seller_sku']] ['SalePrice'] =
                number_format($onlineProductInfo['promotion_price'], 2, '.', '');
            $editPrice ['method'] = 'editPrice';
            $editPrice['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
            $editPrice['request_pk'] = $onlineProductInfo ['id'];//亚马逊商品模型主键
            $editPrice['request_user_id'] = $onlineProductInfo ['user_id'];
            $editPrice['request_shop_id'] = $onlineProductInfo ['belongs_shop'];//亚马逊店铺
            $editPrice ['params'] = ['standardprice' => $standardPrice, 'saleprice' => $feedSalePrice];
            //写上架商品价格数据
            $AmazonSaleFeed->putOnListPush($editPrice);
        }

        //处理附图
        if (!empty($onlineProductInfo ['goods_pics'])) {
            $amazonProductI = 1;
            foreach($onlineProductInfo ['goods_pics'] as $k => $v) {
                if(!$v) {
                    continue;
                }
                $feedImageParam [$onlineProductInfo ['seller_sku'].'_'.$amazonProductI] = [
                    'type'=>'Alternate',
                    'url'=>url('showImage').'?path='.$v['link']
                ];
                $amazonProductI ++ ;
            }
        }
        //图片信息
        if ($onlineProductInfo ['img_url']) {
            $feedImageParam [$onlineProductInfo ['seller_sku']] = [
                'type' => 'Main',
                'url' => url('showImage') . '?path=' . $onlineProductInfo['img_url']
            ];
            $editImage ['method'] = 'editGoodsImage';
            $editImage['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
            $editImage['request_pk'] = $onlineProductInfo ['id'];//亚马逊商品模型主键
            $editImage['request_user_id'] = $onlineProductInfo ['user_id'];
            $editImage['request_shop_id'] = $onlineProductInfo ['belongs_shop'];//亚马逊店铺

            $editImage ['params'] = ['standardImage' => $feedImageParam];
            //写上架商品价格数据
            $AmazonSaleFeed->putOnListPush($editImage);
        }

        $respone ['exception_status'] = false;
        return  $respone;
    }

    /**
     * @param array $param
     * @return bool
     * Note: 商品上架必要参数
     * Data: 2019/6/19 14:24
     * Author: zt7785
     */
    public static function checkPutOnRequireddata(array $param)
    {
        $requiredField = ["seller_sku","sale_price","platform_in_stock","title"];
        $checkStatus = false;
        foreach ($requiredField as $field) {
            if (isset($param [$field])) {
                if ($field == 'sale_price') {
                    if ($param [$field] == 0.00) {
                        break;
                    }
                } else {
                    if ($param [$field] == '') {
                        break;
                    }
                }
            } else {
                break;
            }
            $checkStatus = true;
        }
        if (empty($param ['ASIN']) && $param ['upc'] ) {
            $checkStatus = false;
        }
        return $checkStatus;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * Note: 在线商品下架
     * Data: 2019/6/19 19:32
     * Author: zt7785
     */
    public static function updateOnlineProductPutOffById($ids,$user_id)
    {
        $respone ['exception_status'] = true;
        $respone ['exception_info'] = '';
        $respone ['data'] = [];
        $field = ['seller_sku','id','belongs_shop'];
        //上架成功的商品才允许下架
        $goodsDraftInfos = self::where([
            ['user_id',$user_id],
            ['put_on_status',self::PUTOFF_SUCC],
        ])->whereIn('id',$ids)->get($field);
        if ($goodsDraftInfos->isEmpty()) {
            $respone ['exception_info'] = '上架商品信息异常';
            return $respone;
        }
        $goodsDraftInfos = $goodsDraftInfos->toArray();
        //上架请求队列
        $AmazonSaleFeed = new AmazonSaleFeed();
        $current_time = date('Y-m-d H:i:s');
        DB::beginTransaction();
        try {
            foreach ($goodsDraftInfos as $goodsDraftInfoKey => $goodsDraftInfoVal) {
                    //促销时间
                    $editPrice ['method'] = 'putOff';
                    $editPrice['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                    $editPrice['request_pk'] = $goodsDraftInfoVal ['id'];//亚马逊商品模型主键
                    $editPrice['request_user_id'] = $user_id;
                    $editPrice['request_shop_id'] = $goodsDraftInfoVal ['belongs_shop'];//亚马逊店铺
                    $editPrice ['params'] = [$goodsDraftInfoVal ['seller_sku']];
                    //写上架商品价格数据
                    $AmazonSaleFeed->putOnListPush($editPrice);
            }
            $updateData = [
                'put_on_status' => self::ON_SALE_INITIALIZE,
                'put_off_status' => self::OFF_SALE_INITIALIZE,
                'updated_at' => $current_time
            ];
            self::whereIn('id' ,$ids)->update($updateData);
            if ($respone ['exception_info'] == '') {
                $respone ['exception_status'] = false;
                DB::commit();
            } else {
                DB::rollback();
                $respone ['exception_info'] = '下架失败';
            }
        } catch (Exception $exception) {
            DB::rollback();
            $respone ['exception_info'] = '下架失败!未知错误';
        }
        return $respone;
    }
}
