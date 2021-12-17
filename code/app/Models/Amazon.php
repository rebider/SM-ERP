<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amazon extends Model
{
    //
    protected $table = 'amazon' ;

    /**
     * @author zt6650
     * @var PlatformsInformation
     */
    protected $platformInformation ;

    public function __construct()
    {
        parent::__construct() ;
        $this->platformInformation = new PlatformsInformation ;
    }
    
    public function Category1()
    {
        return $this->hasOne(Category::class ,'id' ,'platform_category1') ;
    }

    public function Category2()
    {
        return $this->hasOne(Category::class ,'id' ,'platform_category2') ;
    }

    public function Category3()
    {
        return $this->hasOne(Category::class ,'id' ,'platform_category3') ;
    }

    public function AmazonPic()
    {
        return $this->hasMany(AmazonPic::class  ,'amazon_id','id') ;
    }

    public function GoodsPics()
    {
        return $this->hasMany(GoodsDraftAmazonPics::class, 'goods_id', 'id');
    }


    public function getAmazonCollect()
    {
        $collect = $this
            ->leftJoin('platforms_information' ,'amazon.platform_information_id' ,'=' ,'platforms_information.id' )
            ->leftJoin('setting_currency_exchange' ,'platforms_information.currency_id' ,'=' ,'setting_currency_exchange.id' )
            ->leftJoin('goods' ,'platforms_information.goods_id' ,'=' ,'goods.id')
            ->leftJoin('setting_shops' ,'platforms_information.store_id' ,'=' ,'setting_shops.id')
            ->leftJoin('procurement' ,'goods.id' ,'=' ,'procurement.goods_id')
            ->leftJoin('goods_declare' ,'goods.id' ,'=' ,'goods_declare.goods_id')
        ;
        return $collect ;
    }

    public function search(array $params = [])
    {

        $collection = $this->getAmazonCollect()
            ->select(
                'amazon.id                   as id'
                ,'goods.id                  as goods_id'
                ,'goods.sku                 as sku' //商品的sku
                ,'goods.goods_pictures      as goods_pictures' //商品主图
                ,'goods.goods_name          as goods_name' //商品名称
                ,'goods.goods_weight        as goods_weight' //产品重量
                ,'goods.goods_length        as goods_length' //产品长
                ,'goods.goods_height        as goods_height' //产品宽
                ,'goods.goods_width         as goods_width' //产品高
                ,'setting_shops.shop_name   as shop_name' //店铺名称
                ,'procurement.preferred_price as preferred_price' //首选采购价
                ,'platforms_information.sale_price as sale_price' //销售价
                ,'setting_currency_exchange.currency_to_code as currency_to_code' //币种
                ,'setting_currency_exchange.currency_form_name as currency_form_name' //币种
                ,'platforms_information.platform_category1 as platform_category1' //乐天一级分类
                ,'platforms_information.platform_category2 as platform_category2' //乐天二级分类
                ,'platforms_information.platform_category3 as platform_category3' //乐天三级分类
                ,'platforms_information.status as platforms_information_status' //商品状态
            ) ;

        $collection = $collection->with('Category1','Category2','Category3') ;
//dd($collection->get()->toArray()) ;
        if (isset($params['sku']) && $params['sku']) { //自定义sku
            $collection = $collection->where('sku', $params['sku']);
        }

        if (isset($params['goods_name']) && $params['goods_name']) { //产品名称
            $collection = $collection->where('goods.goods_name', $params['goods_name']);
        }
//        if (isset($params['category']) && $params['category']) { //产品分类
//            $collection = $collection->where('category_id_1', $params['category_id_1']);
//        }

        return $collection;

    }

    /**
     * @desc 商品编辑时候的一些信息
     * @author zt6650
     * CreateTime: 2019-04-17 09:33
     * @param $AmazonId
     * @return Amazon|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getEditInfoById($AmazonId)
    {
        $collection = $this->getAmazonCollect()
            ->select(
                'Amazon.id                  as id'
                ,'Amazon.seller_sku         as seller_sku'      //sellerSKU
                ,'Amazon.promotion_price    as promotion_price' //促销价格
                ,'Amazon.product_code       as product_code'    //商品编码
                ,'Amazon.item_condition     as item_condition'  //物品状态
                ,'Amazon.keywords1           as keywords1'  //商品关键词1
                ,'Amazon.keywords2           as keywords2'  //商品关键词2
                ,'Amazon.keywords3           as keywords3'  //商品关键词3
                ,'Amazon.keywords4           as keywords4'  //商品关键词4
                ,'Amazon.keywords5           as keywords5'  //商品关键词5
                ,'Amazon.goods_label1       as goods_label1'  //商品标签1
                ,'Amazon.goods_label2       as goods_label2'  //商品标签2
                ,'Amazon.goods_label3       as goods_label3'  //商品标签3
                ,'Amazon.goods_label4       as goods_label4'  //商品标签4
                ,'Amazon.goods_label5       as goods_label5'  //商品标签5
                ,'Amazon.goods_brand        as goods_brand'   //商品品牌
                ,'Amazon.manufacturers      as manufacturers'  //制造商
                ,'Amazon.goods_color        as goods_color'  //商品颜色
                ,'Amazon.goods_model        as goods_model'  //商品型号
                ,'Amazon.wide               as wide'    //宽
                ,'Amazon.high               as high'    //高
                ,'Amazon.length             as length'  //长
                ,'Amazon.weight             as weight'  //重

                ,'goods.id                  as goods_id'
                ,'goods.sku                 as sku'                 //商品的sku
                // ,'goods.goods_pictures      as goods_pictures'      //商品主图
                ,'goods.goods_name          as goods_name'          //商品名称
                ,'goods.goods_weight        as goods_weight'        //产品重量
                ,'goods.goods_length        as goods_length'        //产品长
                ,'goods.goods_height        as goods_height'        //产品宽
                ,'goods.goods_width         as goods_width'         //产品高
                ,'setting_shops.shop_name   as shop_name'           //店铺名称
                ,'setting_shops.id     as shop_id'                  //店铺名称
                ,'procurement.preferred_price as preferred_price'   //首选采购价
                ,'platforms_information.sale_price as sale_price'   //销售价
                ,'setting_currency_exchange.currency_to_code as currency_to_code'       //币种
                ,'setting_currency_exchange.currency_form_name as currency_form_name'   //币种
                ,'platforms_information.platform_category1 as platform_category1' //乐天一级分类
                ,'platforms_information.platform_category2 as platform_category2' //乐天二级分类
                ,'platforms_information.platform_category3 as platform_category3' //乐天三级分类
                ,'platforms_information.goods_title as goods_title' //商品标题
                ,'platforms_information.goods_name as goods_name'   //商品名称
                ,'platforms_information.goods_desc as goods_desc'   //商品描述
                ,'platforms_information.inventory as inventory'     //平台库存
                ,'platforms_information.goods_pictures as goods_pictures'     //商品主图
            ) ;

        $collection = $collection->with('Category1','Category2','Category3' ,'AmazonPic') ;
        $collection = $collection->where('Amazon.id' ,$AmazonId) ;

        return $collection ;
    }

    /**
     * @desc 上架或者批量上架
     * @author zt6650
     * CreateTime: 2019-04-12 16:32
     * @param $id
     * @return bool
     */
    public function PutOnSaleById($id)
    {
        if (!is_array($id)){
            $id = [$id] ;
        }

        if (!is_array($id)){
            return false ;
        }

        //todo 上架接口的信息

        return $this->changeStatusByAmazonId($id ,PlatformsInformation::STATUS_PUT_ON) ;
    }

    /**
     * @desc 更改草稿箱的状态
     * @author zt6650
     * CreateTime: 2019-04-12 16:32
     * @param $id
     * @param $status
     * @return bool
     */
    public function changeStatusByAmazonId($id ,$status)
    {
        $platform_information_id = $this->whereIn('id',$id)->pluck('platform_information_id')->toArray() ;
        $re = $this->platformInformation->whereIn('id' ,$platform_information_id)->update([
            'status'=>$status ,
            'update_at'=>date('Y-m-d H:i:s')
        ]) ;
        if ($re === false) return false ;
        return true ;
    }

    /**
     * @desc 乐天草稿箱的编辑
     * @author zt6650
     * CreateTime: 2019-04-17 10:03
     * @param $id
     * @param $updateArr
     * @return bool
     */
    public function updateById($id ,$updateArr)
    {
        $updateArr['updated_at'] = date('Y-m-d H:i:s') ;
        $re = $this->where('id' ,$id)->update($updateArr) ;
        if ($re === false){
            return false ;
        }

        return true ;
    }


    /**
     * @desc
     * @author zt6650
     * CreateTime: 2019-04-17 09:41
     * @param $AmazonId
     * @return mixed
     */
    public function getPlatformInformationIdByAmazonId($AmazonId)
    {
        return $this->where('id' ,$AmazonId)->pluck('platform_information_id')->first() ;
    }

    public static function getOne($id)
    {
        return self::where('id', $id)->with('GoodsPics')->first();
    }
}
