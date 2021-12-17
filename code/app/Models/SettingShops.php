<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;
use Illuminate\Support\Facades\Auth;


/**
 * Class SettingShops
 * Notes: 店铺设置
 * @package App\Models
 * Data: 2019/3/7 11:36
 * Author: zt7785
 */
class SettingShops extends Model
{
    protected $table = 'setting_shops';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','user_id','plat_id','shop_name','shop_url','remark','service_secret','license_key','status','shop_type','created_at','updated_at','seller_id','Marketplace_Id','ftp_pass','ftp_user'];


    /**
     * @var 店铺授权状态授权成功
     */
    const SHOP_STATUS_EMPOWER = 1;
    /**
     * @var 店铺授权状态授权失败
     */
    const SHOP_STATUS_POWER_FAILED = 2;

    /**
     * @var 店铺授权状态授权过期
     */
    const SHOP_STATUS_POWER_EXPIRED = 4;
    /**
     * @var 店铺授权状态无授权有区别吗
     */
    const SHOP_STATUS_POWER_WITHOUT = 5;
    /**
     * @var 店铺类型自定义
     */
    const SELF_DEFINED_TYPE = 3;
    /**
     * @var 店铺类型乐天
     */
    const SELF_LOTLE_TYPE = 2;
    /**
     * @var 店铺类型amazon
     */
    const SELF_AMAZON_TYPE = 1;
    /**
     * @var 来源平台 亚马逊
     */
    const PLAT_AMAZON = 1;
    /**
     * @var 来源平台 乐天
     */
    const PLAT_RAKUTEN = 2;
    /**
     * @var 店铺未删除
     */
    const DEFINED_UNDELETE = 1;
    /**
     * @var 店铺已删除
     */
    const DEFINED_DELETE = 2;


    /**
     * @var 未删除
     */
    const SHOP_RECYCLE_UNDEL = 1;
    /**
     * @var 已删除
     */
    const SHOP_RECYCLE_DEL = 2;

    /**
     * @var 亚马逊站点
     * 1:日本 2:美国 3:加拿大 4:德国 5:西班牙 6:法国
     * 7:印度 8:意大利 9:英国 10:中国 11:澳大利亚 12:墨西哥',
     */
    const AMAZON_STATION = [
        //1:日本
        '1'=>[
          'Country_code'=>'Japan',
          'Amazon_Marketplace'=>'JP',
          'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.jp',
          'MarketplaceId'=>'A1VC38T7YXB528',
          'stateUrl'=>'https://developer.amazonservices.jp/'
      ],
        //2:美国
        '2'=>[
            'Country_code'=>'US',
            'Amazon_Marketplace'=>'US',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.com',
            'MarketplaceId'=>'ATVPDKIKX0DER',
            'stateUrl'=> 'https://developer.amazonservices.com/'
        ],
        //3:加拿大
        '3'=>[
            'Country_code'=>'Canada',
            'Amazon_Marketplace'=>'CA',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.ca',
            'MarketplaceId'=>'A2EUQ1WTGCTBG2',
            'stateUrl' => 'https://developer.amazonservices.ca/'
        ],
        //4:德国
        '4'=>[
            'Country_code'=>'Germany',
            'Amazon_Marketplace'=>'DE',
            'Amazon_MWS_Endpoint'=>'https://mws-eu.amazonservices.com',
            'MarketplaceId'=>'A1PA6795UKMFR9',
            'stateUrl' => 'https://developer.amazonservices.de/'
        ],
        //5:西班牙
        '5'=>[
            'Country_code'=>'Spain',
            'Amazon_Marketplace'=>'ES',
            'Amazon_MWS_Endpoint'=>'https://mws-eu.amazonservices.com',
            'MarketplaceId'=>'A1RKKUPIHCS9HS',
            'stateUrl'=>'https://developer.amazonservices.es/'
        ],
        //6:法国
        '6'=>[
            'Country_code'=>'France',
            'Amazon_Marketplace'=>'FR',
            'Amazon_MWS_Endpoint'=>'https://mws-eu.amazonservices.com',
            'MarketplaceId'=>'A13V1IB3VIYZZH',
            'stateUrl' => 'https://developer.amazonservices.fr/'
        ],
        //7:印度
        '7'=>[
            'Country_code'=>'India',
            'Amazon_Marketplace'=>'IN',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.in',
            'MarketplaceId'=>'A21TJRUUN4KGV',
            'stateUrl'=>'https://developer.amazonservices.in/'
        ],
        //8:意大利
        '8'=>[
            'Country_code'=>'Italy',
            'Amazon_Marketplace'=>'IT',
            'Amazon_MWS_Endpoint'=>'https://mws-eu.amazonservices.com',
            'MarketplaceId'=>'APJ6JRA9NG5V4',
            'stateUrl'=>'https://developer.amazonservices.it/'
        ],
        //9:英国
        '9'=>[
            'Country_code'=>'UK',
            'Amazon_Marketplace'=>'GB',
            'Amazon_MWS_Endpoint'=>'https://mws-eu.amazonservices.com',
            'MarketplaceId'=>'A1F83G8C2ARO7P',
            'stateUrl'=>'https://developer.amazonservices.co.uk/'
        ],
        //10:中国
        '10'=>[
            'Country_code'=>'China',
            'Amazon_Marketplace'=>'CN',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.com.cn',
            'MarketplaceId'=>'AAHKV2X7AFYLW',
            'stateUrl'=>'https://developer.amazonservices.com.cn/'
        ],
        //11:澳大利亚
        '11'=>[
            'Country_code'=>'Australia',
            'Amazon_Marketplace'=>'AU',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.com.au',
            'MarketplaceId'=>'A39IBJ37TRP1C6',
            'stateUrl'=>'https://developer.amazonservices.com.au/'
        ],
        //12:墨西哥
        '12'=>[
            'Country_code'=>'Mexico',
            'Amazon_Marketplace'=>'MX',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.com.mx',
            'MarketplaceId'=>'A1AM78C64UM0Y8',
            'stateUrl'=>'https://developer.amazonservices.com.mx/'
        ],
        //13:巴西
        '13'=>[
            'Country_code'=>'Brazil',
            'Amazon_Marketplace'=>'BR',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.com',
            'MarketplaceId'=>'A2Q3Y263D00KWC',
            'stateUrl' => 'https://www.amazon.com.br/',
        ],
        //14:阿联酋
        '14'=>[
            'Country_code'=>'United Arab Emirates (U.A.E.)',
            'Amazon_Marketplace'=>'AE',
            'Amazon_MWS_Endpoint'=>'https://mws.amazonservices.ae',
            'MarketplaceId'=>'A2VIGQ35RCS4UG',
            'stateUrl'=>'https://developer.amazonservices.com.mx/'
        ],
        //15:土耳其
        '15'=>[
            'Country_code'=>'Turkey',
            'Amazon_Marketplace'=>'TR',
            'Amazon_MWS_Endpoint'=>'https://mws-eu.amazonservices.com',
            'MarketplaceId'=>'A33AVAJ2PDY3EV',
        ],
    ];

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    public function Plat(){
        return $this->belongsTo(Platforms::class,'plat_id','id')->select(['id','name_CN','name_EN']);
    }

    /**
     * @note
     * 店铺数据获取
     * @since: 2019/3/11
     * @author: zt7387
     * @param:
     * @return: array
     */
    public static function getSettingShopDatas($data,$limit,$user_id)
    {
        $query = self::with('Users')->with('Plat');

        if (isset($data['data']['source_plat']) && !empty($data['data']['source_plat']) && ($data['data']['source_plat']!='all')) {
                $query->where('plat_id', $data['data']['source_plat']);
        }

        if (isset($data['data']['status']) && !empty($data['data']['status']) && ($data['data']['status']!='all')) {
                $query->where('status', $data['data']['status']);
        }

        if (isset($data['data']['shop_name']) && (!empty($data['data']['shop_name']) || $data['data']['shop_name'] == 0)) {
                $query->where('shop_name', 'like','%'.$data['data']['shop_name'].'%');
        }

        if (isset($data['data']['shop_type']) && !empty($data['data']['shop_type'])) {
            $query->where('shop_type', $data['data']['shop_type']);
        }

        if (isset($data['data']['create_time'])) {
            $query->where('created_at', $data['data']['create_time']);
        }
        $res = $query->where(['recycle'=>self::DEFINED_UNDELETE,'user_id'=>$user_id])->orderBy('created_at','desc')->paginate($limit);
//        dd($query->toSql(),$res);
        $count = $res->total();

        return array(
            'res' => $res->items(),
            'count' => $count
        );
    }

    /**
     * @note
     * 店铺添加
     * @since: 2019/3/14
     * @author: zt7387
     * @param: $model $data
     * @return: array
     */
    public static function addDefineShopData($model,$data){
        $currentUser = CurrentUser::getCurrentUser();
        if($currentUser->userAccountType == AccountType::CHILDREN){
            //主账号id
            $model->user_id = $currentUser->userParentId;
        }else{
            $model->user_id = $currentUser->userId;
        }
        //当前id
        $model->created_man = $currentUser->userId;
        $model->plat_id = isset($data['plat_id']) && $data['plat_id'] ? $data['plat_id'] :'';
        $model->amazon_accout = isset($data['amazon_accout']) && $data['amazon_accout'] ? $data['amazon_accout'] :'';
        $model->shop_name = isset($data['shop_name']) && $data['shop_name'] ? $data['shop_name'] : '';
        $model->service_secret = isset($data['service_secret']) && $data['service_secret'] ? $data['service_secret'] : '';
        $model->open_state = isset($data['open_state']) && $data['open_state'] ? $data['open_state'] : 0;
        $model->license_key = isset($data['license_key']) && $data['license_key'] ? $data['license_key'] : '';
        $model->status = isset($data['status']) && $data['status'] ? $data['status'] : SettingShops::SHOP_STATUS_EMPOWER;
        $model->user_name = isset($data['user_name']) && $data['user_name'] ? $data['user_name'] : '';
        $model->shop_url = isset($data['shop_url']) && $data['shop_url'] ? $data['shop_url'] : '';
        $model->shop_type = isset($data['shop_type']) && $data['shop_type'] ? $data['shop_type'] : '';
        $model->Marketplace_Id = isset($data['Marketplace_Id']) && $data['Marketplace_Id'] ? $data['Marketplace_Id'] : '';
        $model->seller_id = isset($data['seller_id']) && $data['seller_id'] ? $data['seller_id'] : '';
        $model->ftp_pass = isset($data['ftp_pass']) && $data['ftp_pass'] ? $data['ftp_pass'] : '';
        $model->ftp_user = isset($data['ftp_user']) && $data['ftp_user'] ? $data['ftp_user'] : '';
        return $model->save();
}
    //添加或编辑
    public static function postShopData($id = 0,$data){
        self::updateOrCreate(['id'=>$id],$data);
    }

    /**
     * @note
     * 自定义店铺编辑
     * @since: 2019/3/22
     * @author: zt7387
     * @param:
     * @return: array
     */
    public static function editDefineShopData($data,$id){
        return self::where(['id'=>$id])->update($data);
    }

    /**
     * @note
     * 店铺是否有历史订单
     * @since: 2019/3/14
     * @author: zt7387
     * @param:
     * @return: array
     */
    public static function existHistoryOrders($id,$user_id){
        if(!$id || ($id < 0) || !is_numeric($id) || !intval($id)){
            return abort(404);
        }
        return self::join('orders as or','setting_shops.id','or.source_shop')->where(['setting_shops.id'=>$id,'setting_shops.user_id'=>$user_id])->count();
    }

    public static function getShopDatas($id,$user_id,$shop_type){
        return self::with('Users','Plat')->where(['id'=>$id,'user_id'=>$user_id,'shop_type'=>$shop_type])->orderBy('created_at','desc')->first();
    }

    /**
     * @param $user_id
     * @return array
     * Note: 通过客户id 获取店铺信息
     * Data: 2019/3/16 11:37
     * Author: zt7785
     */
    public static function getShopsByUserId($user_id)
    {
        $result = self::where('user_id',$user_id)->where('recycle',self::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id','user_id','created_man'])->toArray();
        return $result;
    }

    public static function getShopsByShopsId($shops_id,$plat_id = 0 )
    {
        $result = self::whereIn('id',$shops_id);
        if ($plat_id > 0) {
            $result->where('plat_id',$plat_id);
        }
        $result = $result->where('recycle',self::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
        return $result;
    }

    /**
     * @param $shop_type
     * @return \Illuminate\Support\Collection
     * Note: 根据类型获取店铺信息
     * Data: 2019/4/10 10:11
     * Author: zt7785
     */
    public static function getShopInfoByType($shop_type){
        //获取未删除 授权成功的ak 14 amazon:26
        return self::where(['shop_type'=>$shop_type,'recycle'=>self::DEFINED_UNDELETE,'status'=>self::SHOP_STATUS_EMPOWER])->get(['id','plat_id','service_secret','license_key','amazon_accout','user_id','open_state','shop_name','seller_id','Marketplace_Id','ftp_user','ftp_pass']);
    }

    public function getAllShop()
    {
        $re = $this->with('Users' ,'plat')->get() ;
        return $re ;
    }

    /**
     * 根据平台ID获取平台下所有的店铺
     * @param $plat_id int 平台ID
     * @param $user_id int 父级账户ID
     * @return array
     */
    public static function getShopByPlatId($plat_id, $user_id)
    {
        $whereMap = [
            'user_id' => $user_id
        ];
        if (!empty($plat_id)) {
            $whereMap['plat_id'] = $plat_id;
        }
        $result = self::where($whereMap)->where('recycle',self::SHOP_RECYCLE_UNDEL)->orderBy('id','ASC')->get(['shop_name','id','plat_id'])->toArray();
        return $result;
    }
}
