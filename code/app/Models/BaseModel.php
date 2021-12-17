<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/13
     * Time: 10:36
     */

    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class BaseModel extends Model
    {
        /**enum type
         *
         *RULES_ORDER_MENUS_ID @var 高级设置id
         *ORDER_MENUS_ID       @var 订单id
         */
        const RULES_ORDER_MENUS_ID = 5;

        const ORDER_MENUS_ID = 4;

        const GOODS_MENUS_ID = 2;



    }