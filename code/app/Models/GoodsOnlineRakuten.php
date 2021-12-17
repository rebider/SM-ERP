<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GoodsOnlineRakuten extends Model
{
    const SYNCHRONIZE_STATUS_NORMAL = 1;//上架成功
    const SYNCHRONIZE_STATUS_SUCCESS = 2;//下架成功
    const SYNCHRONIZE_STATUS_ERROR = 3;//上架失败 更新失败
    protected $table = 'goods_online_rakuten';
    public $timestamps = true;
    public $primaryKey = 'id';

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
        return $this->hasMany(GoodsOnlineRakutenPics::class, 'goods_id', 'id');
    }

    /**
     * 获取在线商品数据
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
        if (isset($params['cmn']) && !empty($params['cmn'])) {
            $collection->where('cmn', $params['cmn']);
        }
        if (isset($params['local_sku']) && !empty($params['local_sku'])) {
            $collection->where('local_sku', 'like', "%{$params['local_sku']}%");
        }
        $selection = ['sku', 'title', 'goods_id', 'img_url', 'goods_name',
            'goods_width', 'goods_height', 'goods_length', 'goods_weight',
            'sale_price', 'currency_code', 'belongs_shop', 'local_sku',
            'synchronize_status', 'id', 'synchronize_info'];

        $collection->where('synchronize_status', $params['synchronizeType'])
            ->where('user_id', $user_id);
        $collection->select($selection)->orderByDesc('created_at');
        return $collection->paginate($limit, ['*'], 'page', $page)->toArray();
    }

    public static function getOne($id)
    {
        $collection = self::with('Shops')->where('goods_online_rakuten.id', $id)
            ->with('pictures')
            ->join('goods_attribute', 'goods_attribute.id', '=', 'goods_online_rakuten.goods_attribute_id')
            ->select('goods_online_rakuten.*', 'goods_attribute.attribute_name')
            ->first();
        return $collection;

    }

    public static function getExportData($ids)
    {
        $collection = self::whereIn('goods_online_rakuten.id', $ids)
            ->select('goods_online_rakuten.local_sku', 'goods_online_rakuten.cmn', 'goods_online_rakuten.catalogIdExemptionReason',
                'goods_online_rakuten.sku', 'goods_online_rakuten.sale_price', 'goods_online_rakuten.currency_code',
                'goods_online_rakuten.title', 'goods_online_rakuten.goods_name', 'goods_online_rakuten.rakuten_category_id',
                'goods_online_rakuten.goods_description', 'goods_online_rakuten.platform_in_stock',
                'goods_online_rakuten.synchronize_status', 'setting_shops.shop_name')
            ->join('setting_shops', 'setting_shops.id', '=', 'goods_online_rakuten.belongs_shop')
            ->get()->toArray();
        return $collection;
    }
}
