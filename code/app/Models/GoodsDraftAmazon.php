<?php

namespace App\Models;

use App\Http\Services\Goods\AmazonSaleFeed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class GoodsDraftAmazon extends Model
{
    protected $table = 'goods_draft_amazon';
    public $timestamps = true;
    public $primaryKey = 'id';
    /**
     * @var 草稿状态
     */
    const STATUS_DRAFT = 1;

    /**
     * @var 上架失败
     */
    const STATUS_PUTON_FAIL = 2;
    /**
     * @var 上架成功
     */
    const STATUS_PUTON_SUCC = 3;

    public function Shops()
    {
        return $this->hasOne(SettingShops::class, 'id', 'belongs_shop');
    }

    public function Procurement()
    {
        return $this->hasOne(Procurements::class, 'goods_id', 'goods_id');
    }

    public function pictures()
    {
        return $this->hasMany(GoodsDraftAmazonPics::class, 'goods_id', 'id');
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
        if (isset($params['local_sku']) && !empty($params['local_sku'])) {
            $collection->where('goods_draft_amazon.local_sku', 'like', "%{$params['local_sku']}%");
        }
        if (isset($params['goods_name']) && !empty($params['goods_name'])) {
            $collection->where('goods_draft_amazon.title', 'like', "%{$params['goods_name']}%");
        }
        $collection->where('user_id', $user_id);
        $selection = ['goods_draft_amazon.local_sku', 'goods_draft_amazon.title', 'goods_id',
            'goods_width', 'goods_height', 'goods_length', 'goods_weight',
            'goods_draft_amazon.sale_price', 'goods_draft_amazon.currency_code', 'goods_draft_amazon.belongs_shop',
            'goods_draft_amazon.synchronize_status', 'goods_draft_amazon.id','img_url','goods_name'];

        if (isset($params['synchronizeType']) && $params['synchronizeType'] == 3) {
            $collection->where('goods_draft_amazon.synchronize_status', $params['synchronizeType']);
            $selection[] = 'goods_draft_amazon.synchronize_info';
        } else {
            $collection->where('goods_draft_amazon.synchronize_status', '!=', 2)
            ->where('goods_draft_amazon.synchronize_status', '!=', 3);
        }
        $collection->select($selection)->orderByDesc('goods_draft_amazon.created_at');
        return $collection->paginate($limit, ['*'], 'page', $page)->toArray();
    }

    public static function getOne($id,$user_id)
    {
        return self::with('pictures')->where('id', $id)->where('user_id',$user_id)->first();
    }

    public function updateById($id,$data,$user_id) {
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
     * @note
     * 获取分类
     * @since: 2019/6/10
     * @author: zt7837
     * @return: array
     */
    public function getAmazomCat($goods,$user_id) {
        $cat = Category::where('user_id',$user_id)->where('type',Category::TYPE_AMAZON)->get();
        if(!$cat->isEmpty()) {
            $cat = $cat->toArray();
            $key = array_search($goods['category_id_1'],array_column($cat,'id'));
            $key2 = array_search($goods['category_id_2'],array_column($cat,'id'));
            $key3 = array_search($goods['category_id_3'],array_column($cat,'id'));
            $str = $cat[$key]['name'].'>'. $cat[$key2]['name'].'>'.$cat[$key3]['name'];
            $goods['cate_str'] = $str ? $str : '';
        }
        if($goods['goods_keywords']) {
            $keywords = explode(',',$goods['goods_keywords']);
            $goods['goods_keywords1'] = $keywords[0];
            $goods['goods_keywords2'] = $keywords[1];
            $goods['goods_keywords3'] = $keywords[2];
            $goods['goods_keywords4'] = $keywords[3];
            $goods['goods_keywords5'] = $keywords[4];
        }
        if($goods['goods_label']) {
            $label = explode(',',$goods['goods_label']);
            $goods['goods_label1'] = $label[0];
            $goods['goods_label2'] = $label[1];
            $goods['goods_label3'] = $label[2];
            $goods['goods_label4'] = $label[3];
            $goods['goods_label5'] = $label[4];
        }
        return $goods;
    }

    /**
     * @note
     * 亚马逊草稿箱 删除
     * @since: 2019/6/11
     * @author: zt7837
     * @return: array
     */
    public function deleteById($id,$user_id,$amazon_pic) {
        $draft = $this->where(['id'=>$id,'user_id'=>$user_id])->first();
        //商品附图
        $amazon_pic->where(['goods_id'=>$id,'user_id'=>$user_id])->delete();
        if(!$draft) {
            return abort(404);
        }
        return $draft->delete();
    }

    /**
     * @param $ids
     * @param $user_id
     * Note: 上架或批量上架
     * Data: 2019/6/19 10:47
     * Author: zt7785
     */
    public static function putOnSaleById($ids,$user_id,$is_beginTransaction = false)
    {
        $respone ['exception_status'] = true;
        $respone ['exception_info'] = '';
        $respone ['data'] = [];
        $field = ["id","upc","title","sale_price","img_url","platform_in_stock","currency_code","synchronize_status","goods_id","amazon_category_id","brand","goods_status","promotion_price","promotion_start_time","promotion_end_time","goods_weight","belongs_shop","product_code","seller_sku","product_code_type","goods_name","local_sku","user_id","AmazonBrowseNodeID"];
        $field = ['*'];
        $goodsDraftInfos = self::with('pictures','Shops')->where([
            ['user_id',$user_id],
            ['synchronize_status',self::STATUS_DRAFT],
        ])->whereIn('id',$ids)->whereHas('Shops',function ($query) {
            $query->where('plat_id',Platforms::AMAZON);
            $query->where('recycle',SettingShops::DEFINED_UNDELETE);
        })->get($field);
        if ($goodsDraftInfos->isEmpty()) {
            $respone ['exception_info'] = '上架商品信息异常';
            return $respone;
        }
        $current_time = date('Y-m-d H:i:s');
        $goodsDraftInfos = $goodsDraftInfos->toArray();
        //上架请求队列
        $AmazonSaleFeed = new AmazonSaleFeed();
        if (empty($is_beginTransaction)) {
            DB::beginTransaction();
        }
        try {
            foreach ($goodsDraftInfos as $goodsDraftInfoKey => $goodsDraftInfoVal) {
                //店铺信息
                if (empty($goodsDraftInfoVal ['belongs_shop'])) {
                    $respone ['exception_info'] = '存在未选择店铺商品';
                    break;
                }
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
                        if (!$v) {
                            continue;
                        }
                        $pics['goods_id'] = '$online_insert_id';
                        $pics['created_man'] = $user_id;
                        $pics['link'] = $v ['link'];
                        $pics['user_id'] = $user_id;
                        $pics['updated_at'] = $pics['created_at'] = $current_time;
                        $temGoodsDraftInfoPic [] = $pics;
                        if ($amazonProductI > 5) {
                            continue;
                        }
                        $feedImageParam [$goodsDraftInfoVal ['seller_sku'] . '_' . $amazonProductI] = [
                            'type' => 'Alternate',
                            'url' => url('showImage') . '?path=' . $pics['link']
                        ];
                        $amazonProductI++;
                    }
                }
                unset($goodsDraftInfoVal ['pictures']);
                $temGoodsDraftInfo = $goodsDraftInfoVal;
                //S1 给在线商品表写一条临时数据
                $onlinGoodsData ['goods_draft_amazon_id'] = $goodsDraftInfoVal ['id'];
                $onlinGoodsData ['put_on_status'] = GoodsOnlineAmazon::PUTON_INIT;
                $onlinGoodsData ['put_off_status'] = GoodsOnlineAmazon::PUTOFF_INIT;
                $onlinGoodsData ['created_man'] = $user_id;
                //ASIN
                if ($goodsDraftInfoVal ['product_code_type'] == 2) {
                    $onlinGoodsData['ASIN'] = $goodsDraftInfoVal['product_code'];
                }
                //拿公用数据
                unset($temGoodsDraftInfo ['id'], $temGoodsDraftInfo ['product_code'], $temGoodsDraftInfo ['product_code_type'], $temGoodsDraftInfo ['synchronize_status'],$temGoodsDraftInfo ['shops']);
                $onlinGoodsData = array_merge($onlinGoodsData, $temGoodsDraftInfo);
                $online_insert_id = GoodsOnlineAmazon::insertGetId($onlinGoodsData);
                if (empty($online_insert_id)) {
                    $respone ['exception_info'] = '未知错误';
                    break;
                }
                if (!empty($temGoodsDraftInfoPic)) {
                    $picJsonStr = str_replace('$online_insert_id', $online_insert_id, json_encode($temGoodsDraftInfoPic));
                    $picJsonArr = json_decode($picJsonStr, true);
                    GoodsOnlineAmazonPics::insert($picJsonArr);
                }
                //商品上架队列写数据
                $feedParam ['sku'] = $goodsDraftInfoVal ['seller_sku'];
                //不涉及币种 币种根据站点信息
                $feedParam ['price'] = $goodsDraftInfoVal ['sale_price'];
                $feedParam ['product_id'] = $goodsDraftInfoVal ['product_code'];
                $feedParam ['product_id_type'] = $goodsDraftInfoVal ['product_code_type'] == 1 ? 'UPC' : 'ASIN';
                $feedParam ['condition_type'] = 'New';
                $feedParam ['quantity'] = $goodsDraftInfoVal ['platform_in_stock'];
                if (empty($feedParam ['quantity'])) {
                    $respone ['exception_info'] = '亚马逊平台库存不能为0';
                    break;
                }
                $feedParam ['title'] = $goodsDraftInfoVal ['title'];
                $feedParam ['brand'] = $goodsDraftInfoVal ['brand'];
                $feedParam ['recommended_browse_nodes'] = $goodsDraftInfoVal ['AmazonBrowseNodeID'];
                //草稿id 用于处理上架失败
                $feedParam ['goods_draft_id'] = $goodsDraftInfoVal ['id'];
                //图片
//            if ($platformInformation ['img_url']) {
//                $feedParam ['image'] = [$platformInformation['img_url']];
//            }
                //重量
                if ($goodsDraftInfoVal ['goods_weight'] > 0) {
                    $feedParam ['weight'] = $goodsDraftInfoVal ['goods_weight'];
                }
                $listData['method'] = 'putOn';//上架
                $listData['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                $listData['request_pk'] = $online_insert_id;//亚马逊商品模型主键
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
                    $editPrice['request_pk'] = $online_insert_id;//亚马逊商品模型主键
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
                    $editImage ['method'] = 'editGoodsImage';
                    $editImage['request_table'] = 'GoodsOnlineAmazon';//亚马逊商品模型
                    $editImage['request_pk'] = $online_insert_id;//亚马逊商品模型主键
                    $editImage['request_user_id'] = $user_id;
                    $editImage['request_shop_id'] = $goodsDraftInfoVal ['belongs_shop'];//亚马逊店铺
                    $editImage ['params'] = ['standardImage' => $feedImageParam];
                    //写上架商品价格数据
                    $AmazonSaleFeed->putOnListPush($editImage);
                }
            }
            if ($respone ['exception_info'] == '') {
                $respone ['exception_status'] = false;
                if (empty($is_beginTransaction)) {
                    DB::commit();
                }
            } else {
                if (empty($is_beginTransaction)) {
                    DB::rollback();
                }
            }
        } catch (Exception $exception) {
            if (empty($is_beginTransaction)) {
                DB::rollback();
            }
            $respone ['exception_info'] = '未知错误';
        }
        return $respone;
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
        $requiredField = ["seller_sku","sale_price","platform_in_stock","product_code","product_code_type","title"];
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
        return $checkStatus;
    }


}
