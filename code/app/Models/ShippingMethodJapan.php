<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class SettingWarehouseTypes
 * Notes: 仓库类型表
 * @package App\Models
 * Data: 2019/3/7 15:19
 * Author: zt7785
 */
class ShippingMethodJapan extends Model
{
    protected $table = 'shipping_method_japan';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','code','name','name_en','warehouse_code','created_at','updated_at'];


}