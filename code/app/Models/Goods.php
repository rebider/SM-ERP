<?php

namespace App\Models;

use App\Auth\Models\Menus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Integer;

class Goods extends Model
{
    //
    protected $table = 'goods';

    public $timestamps = true;

    public $primaryKey = 'id';

    protected $fillable = ['id','goods_attribute_id','category_id_1','category_id_2','category_id_3','created_man','user_id','sku','goods_name','goods_pictures','synchronization','status','goods_title','goods_height','goods_width','goods_length','goods_weight','description','plat','from_url'];

    /**
     * @var 商品菜单-id
     */
    const GOODS_MENU_ID = 2;

    /**
     * @var 草稿状态
     */
    const STATUS_DRAFT = 1;

    /**
     * @var 审核通过
     */
    const STATUS_PASS = 2;

    /**
     * @var 未同步
     */
    const SYNCHRONIZATION_NO = 1;

    /**
     * @var 同步失败
     */
    const SYNCHRONIZATION_FAIL = 2;

    /**
     * @var 同步成功
     */
    const SYNCHRONIZATION_SUCCESS = 3;

    /**
     * @description 一级分类
     * @author zt6650
     * @creteTime 2019/3/28 17:35
     */
    public function category1()
    {
      return  $this->hasOne(Category::class ,'id' ,'category_id_1') ;
    }

    /**
     * @description 二级分类
     * @author zt6650
     * @creteTime 2019/3/28 17:35
     */
    public function category2()
    {
       return $this->hasOne(Category::class ,'id' ,'category_id_2')->where('type' ,0) ;
    }

    /**
     * @description 3级分类
     * @author zt6650
     * @creteTime 2019/3/28 17:35
     */
    public function category3()
    {
       return $this->hasOne(Category::class ,'id' ,'category_id_3')->where('type' ,0) ;
    }


    public function procurement()
    {
        return $this->hasOne(Procurements::class ,'goods_id' ,'id') ;
    }

    public function goodsPics()
    {
        return $this->hasMany(GoodsPic::class ,'goods_id' ,'id') ;
    }


    public function rakuten()
    {
        return $this->hasMany(GoodsDraftRakuten::class,'goods_id','id');
    }

    /**
     * @note
     * 申报信息
     * @since: 2019/5/28
     * @author: zt7837
     * @return: obj
     */
    public function declares()
    {
        return $this->hasOne(GoodsDeclare::class,'goods_id','id');
    }

    public function goodsAttribute()
    {
        return $this->hasOne(GoodsAttribute::class, 'id', 'goods_attribute_id');
    }

    public function currency()
    {
        return $this->hasOne(SettingCurrencyExchange::class, 'id', 'currency_id');
    }

    /**
     * @note
     * 商品附图
     * @since: 2019/5/30
     * @author: zt7837
     * @return: obj
     */
    public function pictures(){
        return $this->hasMany(GoodsLocalPic::class,'goods_id','id');
    }

    /**
     * @note
     * 商品映射
     * @since: 2019/5/30
     * @author: zt8076
     * @return: Object
     */
    public function mapping()
    {
        return $this->hasMany(GoodsMapping::class,'goods_id','id');
    }



    /**
     * @description 根据条件查询
     * @author zt6650
     * @creteTime 2019/3/11 15:01
     * @param array $params
     * @return self ;
     */
    public function search(array $params = [],$syn = '')
    {
        $collection = $this->with('category1' ,'category2','category3' ,'procurement','warehouseGoods','warehouseGoods.category1','warehouseGoods.category2','warehouseGoods.category3') ;
        $collection = $collection->where('user_id', $params['user_id']);
        if ((isset($params['synchronization']) && $params['synchronization']) || (isset($params['synchronization']) && $params['synchronization'] == 0)) {
            //同步到仓库栏位的同步状态搜索
            if($syn) {
                $collection = $collection->whereHas('warehouseGoods',function($query) use ($params){
                    $query->where('sync',$params['synchronization']);
                });
            }
        }
        if($syn) {
                $collection = $collection->where('status', Goods::STATUS_PASS);
        } else {
            if (isset($params['status']) && $params['status']) { //产品状态
                $collection = $collection->where('status', $params['status']);
            }
        }
        if (isset($params['sku']) && $params['sku']) { //自定义sku
            $collection = $collection->where('sku','like', '%'.$params['sku'].'%');
        }
        if (isset($params['goods_name']) && $params['goods_name']) { //产品名称
            if($syn) {
                $collection = $collection->whereHas('warehouseGoods',function($query) use($params) {
                    $query->where('goods_name','like','%'.$params['goods_name'].'%');
                });
            } else {
                $collection = $collection->where('goods_name', 'like','%'. $params['goods_name'] .'%');
            }
        }


        return $collection;

    }

    /**
     * @description 存入商品
     * @author zt6650
     * @creteTime 2019/3/11 15:20
     * @param array $insert
     * @return bool|int
     */
    public function insertArr(array $insert = [],$id = 0)
    {
        return self::updateOrCreate(['id'=>$id],$insert);
    }

    /**
     * @description 更新商品的信息
     * @author zt6650
     * @creteTime 2019/3/11 17:16
     * @param array $updateArr
     * @param int $goods_id
     * @return bool
     */
    public function updateById(array $updateArr = [], $goods_id = 0)
    {
        $updateArr['updated_at'] = date('Y-m-d H:i:s');
        $re = $this->where('id', $goods_id)->update($updateArr);
        if ($re === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @description
     * @author zt6650
     * @creteTime 2019/3/12 14:58
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function delById($id)
    {
        if (is_numeric($id) && $id > 0) {           //单个删除
            return $this->where('id', $id)->delete();
        } elseif (is_array($id)) {                 //批量删除
            return $this->whereIn('id', $id)->delete();
        }

        return false;
    }

    /**
     * @description 根据id获取商品信息
     * @author zt7927
     * @data 2019/3/19 10:51
     * @param $id
     * @return mixed
     */
    public function getGoodsDataById($id)
    {
        if (is_numeric($id) && $id > 0) {
            return self::where('id', $id)->first(['category_id_1','category_id_2','category_id_3','sku','goods_pictures','goods_name']);
        }
        return false;
    }

    /**
     * @description 新增-根据sku获取商品信息
     * @author zt7927
     * @data 2019/3/19 15:11
     * @param $sku
     * @param $warehouse_id
     * @return mixed
     */
    public function getGoodsBySku($sku, $warehouse_id)
    {
        $db = DB::table($this->table);
        $db = $db->where('goods.sku', $sku)
            ->leftjoin('warehouse_type_goods', 'goods.id', '=', 'warehouse_type_goods.goods_id')
            ->where('warehouse_type_goods.setting_warehouse_id', $warehouse_id)
            ->leftjoin('procurement', 'goods.id', '=', 'procurement.goods_id')
            ->leftjoin('suppliers', 'procurement.preferred_supplier_id', '=', 'suppliers.id')
            ->select('goods.status', 'goods.sku', 'warehouse_type_goods.in_transit_inventory',
                     'warehouse_type_goods.available_in_stock', 'procurement.preferred_price',
                     'procurement.preferred_supplier_id', 'goods.id')
            ->first();
        //如果本地商品未跟仓库商品关联，只查询本地商品信息
        if ($db == null){
            return DB::table($this->table)->where('goods.sku', $sku)
                ->leftjoin('procurement', 'goods.id', '=', 'procurement.goods_id')
                ->leftjoin('suppliers', 'procurement.preferred_supplier_id', '=', 'suppliers.id')
                ->select('goods.status', 'goods.sku',   'procurement.preferred_price',
                         'procurement.preferred_supplier_id', 'goods.id')
                ->first();
        }
        return $db;
    }

    /**
     * @description 编辑-根据sku获取商品信息
     * @author zt7927
     * @data 2019/3/19 15:11
     * @param $sku
     * @return mixed
     */
    public function getGoodsBySkuEdit($sku)
    {
        $db = DB::table($this->table);
        $db = $db->where('goods.sku', $sku)
            ->leftjoin('procurement', 'goods.id', '=', 'procurement.goods_id')
            ->leftjoin('suppliers', 'procurement.preferred_supplier_id', '=', 'suppliers.id')
            ->select('goods.status', 'goods.sku', 'goods.goods_pictures', 'goods.goods_name',
                     'procurement.preferred_price', 'procurement.preferred_supplier_id', 'goods.id')
            ->first();
        return $db;
    }

    /**
     * @description 关联仓库商品表
     * @author zt7927
     * @data 2019/3/19 15:41
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseGoods()
    {
        return $this->belongsTo(WarehouseGoods::class, 'id', 'goods_id');
    }

    /**
     * @description 查询所有审核通过的商品
     * @author zt7927
     * @data 2019/3/29 9:59
     * @return array
     */
    public function getAllGoods($user_id)
    {
        return self::where('status',Goods::STATUS_PASS)->where('user_id',$user_id)->get()->toArray();
    }

    /**
     * @description 根据sku获取商品
     * @author zt7927
     * @date 2019/4/11 17:20
     * @param $sku
     * @return mixed
     */
    public function getGoodsIdBySku($sku,$user_id)
    {
        return self::with(['procurement','declares','pictures'=>function ($query)  {
              $query->select(['link','id','goods_id']);
              }])->where('sku', $sku)->where('user_id',$user_id)->where('status', self::STATUS_PASS)->first();
    }

    /**
     * @description 根据三级分类id查找商品
     * @author zt7927
     * @date 2019/4/23 15:18
     * @param $category_id
     * @return Model|null|static
     */
    public function getGoodsByCategoryId($category_id,$user_id)
    {
        return self::where('category_id_3', $category_id)->where('user_id', $user_id)->first(['id']);
    }

    /**
     * @description 便捷菜单
     * @author zt7927
     * @date 2019/4/23 16:26
     * @return mixed
     */
    public static function getGoodsShortcutMenu()
    {
        $menusModel = new Menus();
        $menusList = $menusModel->getShortcutMenu(self::GOODS_MENU_ID);

        return $menusList;
    }

    public static function getGoodsByIdGroup($idGroup, $user_id)
    {
        return Goods::where('user_id', $user_id)
            ->whereIn('id', $idGroup)
            ->with('pictures')
            ->get()->toArray();
    }

    /**
     * @note
     * 商品审核
     * @since: 2019/6/11
     * @author: zt7837
     * @return: array
     */
    public function checkStatus($goodsIds,$user_id,$created_man) {
        $transactionStatus = true;
        $response ['code'] = 200;
        $response ['msg'] = '';
        $warehouseGoods = new WarehouseGoods();
        DB::beginTransaction();
        try {
            foreach ($goodsIds as $goodsId) {
                $goods_wh['user_id'] = $user_id;
                $goods_wh['id'] = $goodsId;
                $data['status'] = self::STATUS_PASS;
                $goods = $this->with('declares')->where($goods_wh)->first();
                if(!$goods) {
                    $transactionStatus = false;
                    $response ['code'] = 201;
                    $response ['msg'] = '存在异常商品信息';
                    break;
                }
                if($goods ['status'] == self::STATUS_PASS) {
                    //直接跳过
                    $transactionStatus = false;
                    $response ['code'] = 201;
                    $response ['msg'] = '存在已审核商品';
                    break;
//                    continue;
                }
                $res = $goods->update($data);
                if(!$res) {
                    $transactionStatus = false;
                    $response ['code'] = 201;
                    $response ['msg'] = '商品审核失败';
                    break;
                }
                //新建一个同步商品 副本
                $ware['goods_id'] = $goodsId;
                $ware['user_id'] = $user_id;
                $warehouse = $warehouseGoods->where($ware)->first();
                $wareInfo['sku'] = $goods['sku'];
                $wareInfo['user_id'] = $user_id;
                $wareInfo['created_man'] = $created_man;
                $wareInfo['goods_id'] = $goodsId;
                $wareInfo['goods_height'] = $goods['goods_height'];
                $wareInfo['goods_width'] = $goods['goods_width'];
                $wareInfo['goods_length'] = $goods['goods_length'];
                $wareInfo['goods_weight'] = $goods['goods_weight'];
                $wareInfo['goods_name'] = $goods['goods_name'];
                $wareInfo['product_title'] = $goods['goods_title'];
                $wareInfo['ch_name'] = $goods['declares']['ch_name'];
                $wareInfo['eh_name'] = $goods['declares']['eh_name'];
                $wareInfo['price'] = $goods['declares']['price'];

                if($goods['goods_attribute_id'] == 1) {
                    $wareInfo['isset_battery'] = WarehouseGoods::UNBATTERY;
                } else {
                    $wareInfo['isset_battery'] = WarehouseGoods::BATTERY;
                }
                if($warehouse) {
                    $wareInfo['updated_at'] = date('Y-m-d H:i:s');
                    $wareRe = $warehouseGoods->where($ware)->update($wareInfo);
                } else {
                    $wareInfo['updated_at'] = date('Y-m-d H:i:s');
                    $wareInfo['created_at'] = date('Y-m-d H:i:s');
                    $wareRe =  $warehouseGoods->insert($wareInfo);
                }
                if (empty($wareRe)) {
                    $transactionStatus = false;
                    $response ['code'] = 201;
                    $response ['msg'] = '商品审核失败';
                    break;
                }
            }
            if ($transactionStatus) {
                DB::commit();
                return $response;
            } else {
                DB::rollBack();
                return $response;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response ['code'] = 201;
            $response ['msg'] = '商品审核失败';
            return $response;
        }
    }

    /**
     * 获取本地导出数据的信息
     * Author: ZT12779
     * @param array $ids
     * @return array
     */
    public static function getLocalExportGoods(array $ids)
    {
        $goods = Goods::whereIn('id', $ids)
            ->with('goodsAttribute')
            ->with('declares')
            ->with('procurement')
            ->with('currency')
            ->select('*')
            ->get()->toArray();
        return $goods;
    }
}
