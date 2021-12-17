<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;

class Lotte extends Model
{
    //
    protected $table = 'lotte' ;

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

    /**
     * @desc 关联商品
     * @author zt6650
     * CreateTime: 2019-04-12 09:20
     * @return mixed
     */
    public function Goods()
    {
        return $this->hasManyThrough(
            Goods::class //商品信息表
            ,PlatformsInformation::class //中间表的信息
            ,'goods_id' //
            ,'id' //
            ,'platform_information_id' //
            ,'id' //
        ) ;
    }
    /**
     * @desc 关联商品表
     * @author zt6650
     * CreateTime: 2019-04-12 09:37
     * @return mixed
     */
    public function Shop()
    {
        return $this->hasManyThrough(
            SettingShops::class
            ,PlatformsInformation::class
            ,'store_id'
            ,'id'
            ,'plat_information_id'
            ,'id'
        ) ;
    }


    public function procurement()
    {
        return $this->hasManyThrough(Procurements::class ,PlatformsInformation::class ,'goods_id' ,'goods_id' ,'plat_information_id' ,'id') ;
    }


    public function PlatformInformation()
    {
        return $this->hasOne(PlatformsInformation::class  ,'id','platform_information_id') ;
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

    public function LottePic()
    {
        return $this->hasMany(LottePic::class  ,'lotte_id','id') ;
    }


    public function getLotteCollect()
    {
        $collect = $this
            ->leftJoin('platforms_information' ,'lotte.platform_information_id' ,'=' ,'platforms_information.id' )
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
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser)) {
            abort(404);
        }
        if ($currentUser->userAccountType == AccountType::CHILDREN) {
            $user_id = $currentUser->userParentId;
        } else {
            $user_id = $currentUser->userId;
        }

        $collection = $this->getLotteCollect()
            ->select(
                'lotte.id                   as id'
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

        $collection = $collection->with('Category1','Category2','Category3')->where(['user_id'=>$user_id]) ;

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
     * @param $lotteId
     * @return Lotte|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getEditInfoById($lotteId,$user_id)
    {
        $collection = $this->getLotteCollect()
            ->select(
                 'lotte.id                  as id'
                ,'lotte.itemUrl             as itemUrl'
                ,'lotte.itemNumber          as itemNumber'
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
                ,'platforms_information.goods_title as goods_title' //商品状态
                ,'platforms_information.goods_name as goods_name'   //商品状态
                ,'platforms_information.goods_desc as goods_desc'   //商品状态
                ,'platforms_information.inventory as inventory'     //商品状态
                ,'platforms_information.goods_pictures as goods_pictures'     //商品主图
            ) ;

        $collection = $collection->with('Category1','Category2','Category3' ,'LottePic') ;
        $collection = $collection->where('lotte.id' ,$lotteId)->where('lotte.user_id',$user_id) ;

        return $collection ;
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
     * @param $lotteId
     * @return mixed
     */
    public function getPlatformInformationIdByLotteId($lotteId,$user_id)
    {
        return $this->where('id' ,$lotteId)->where('user_id',$user_id)->pluck('platform_information_id')->first() ;
    }
    
    public static function editInfo($user_id)
    {
//        $shops = SettingShops::where(['shop_type'=>SettingShops::SELF_LOTLE_TYPE,'remark'=>''])->get();//todo 所查即所用
//        if(!$shops->isEmpty()) {
//            $shopsArr = $shops->toArray();
//        }

        $currency = SettingCurrencyExchange::getAllCurrency($user_id);
        if(!$currency->isEmpty()) {
            $curArr = $currency->toArray();
        }
        $data['catArr'] = isset($catArr) ? $catArr : '';
        $data['currency'] = isset($curArr) ? $curArr : '';
        $data['first'] = isset($first_lv_arr) ? $first_lv_arr : '';
        $data['second'] = isset($second_lv_arr) ? $second_lv_arr : '';
        $data['third'] = isset($third_lv_arr) ? $third_lv_arr : '';
        $data['four'] = isset($foure_lv_arr) ? $foure_lv_arr : '';

        return $data;
    }
}
