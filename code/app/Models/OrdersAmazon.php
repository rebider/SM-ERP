<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class OrdersAmazon
 * Notes: 亚马逊订单同步表
 * @package App\Models
 * Data: 2019/3/7 16:09
 * Author: zt7785
 */
class OrdersAmazon extends Model
{
    protected $table = 'orders_amazon';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','AmazonOrderId','SellerOrderId','created_man','cw_order_id','is_system','cw_code','RequestId','MarketplaceId','OrderStatus','MatchStatus','Amount','CurrencyCode','PaymentMethodDetails','OrderType','Name','Phone','PostalCode','CountryCode','StateOrRegion','City','AddressLine1','AddressLine2','AddressLine3','SalesChannel','BuyerEmail','ShipServiceLevel','FulfillmentChannel','NumberOfItemsShipped','NumberOfItemsUnshipped','PaymentMethod','BuyerName','ShipmentServiceLevelCategory','freight','goods_amount','excise','IsReplacementOrder','IsBusinessOrder','IsPremiumOrder','IsPrime','is_update','update_count','PurchaseDate','PaymentDate','LastUpdateDate','LocalPurchaseDate','EarliestShipDate','LatestShipDate','update_time','created_at','updated_at'];



    /**
     * @var 是系统订单
     */
    const IS_SYSTEM_ORDER = 1;
    /**
     * @var 不是系统订单
     */
    const UN_SYSTEM_ORDER = 0;


    /**
     * @var 未匹配
     */
    const AMAZON_MAPPING_STATUS_UNFINISH = 1;
    /**
     * @var 已匹配
     */
    const AMAZON_MAPPING_STATUS_FINISHED = 2;
    /**
     * @var 匹配失败
     */
    const AMAZON_MAPPING_STATUS_FAIL = 3;
    
    
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 订单表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function Orders()
    {
        return $this->belongsTo(Orders::class, 'cw_order_id', 'id');
    }

    public static function getOrgOrderInfoByOpt($option,$value)
    {
        $result = self::where($option,$value)->first(['id']);
        if (empty($result)) {
            return [];
        }
        return $result ->toArray() ;
    }
}
