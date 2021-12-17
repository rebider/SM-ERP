<?php

namespace App\Models;

use App\Auth\Models\Menus;
use App\Auth\Models\Users;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Upc 亚马逊日本站 UPC表
 * @package App\Models
 */
class Upc extends Model
{
    protected $table = 'upc';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'created_man', 'user_id', 'upc', 'seller_sku', 'status', 'created_at', 'updated_at'];

    /**
     * @var upc状态-未使用
     */
    const UNUSED = 1;

    /**
     * @var upc状态-已使用
     */
    const HAVE_BEEN_USED = 2;

    /**
     * @var 商品菜单-id
     */
    const GOODS_ID = 2;

    /**
     * @description 关联用户表
     * @author zt7927
     * @date 2019/4/18 10:12
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id')->select(['user_id','username']);
    }

    /**
     * @description: 便捷菜单
     * @author: zt7927
     * @data: 2019/3/14 9:40
     * @return array
     */
    public static function getGoodsShortcutMenu()
    {
        $menusModel = new Menus();
        $menusList = $menusModel->getShortcutMenu(self::GOODS_ID);
        return $menusList;
    }

    /**
     * @description 获取所有UPC码
     * @author zt7927
     * @date 2019/4/18 9:57
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllUpc($user_id)
    {
        return self::where('user_id',$user_id)->get()->toArray();
    }

    /**
     * @description UPC码-搜索
     * @author zt7927
     * @date 2019/4/18 10:04
     * @param array $params
     * @return $this|Upc
     */
    public function search($params = [],$user_id)
    {
        $collection = new self();
        $collection = $collection->with('users')->where('user_id',$user_id);
        //UPC状态(使用 未使用)
        if (isset($params['status']) && $params['status']) {
            $collection = $collection->where('status', $params['status']);
        }
        //UPC码
        if (isset($params['upc']) && $params['upc']) {
            $collection = $collection->where('upc', $params['upc']);
        }
        $collection->orderBy('updated_at','DESC');
        return $collection;
    }

    /** 更新数据
     * @description
     * @author zt7927
     * @date 2019/4/18 11:40
     * @param $params
     * @return bool
     */
    public function updateArr($params)
    {
        return self::where('id', $params['upc_id'])->where('user_id',$params ['user_id'])->update([
            'status'     => self::HAVE_BEEN_USED,   //已使用
            'seller_sku' => $params['seller_sku'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

}
