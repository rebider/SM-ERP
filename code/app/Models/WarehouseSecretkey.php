<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/4/2
     * Time: 15:19
     */

    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class WarehouseSecretkey extends Model
    {
        protected $table = 'warehouse_secretkey';

        public $timestamps = true;

        public $primaryKey = 'id';

        public $fillable = ['id', 'created_man', 'appToken', 'appKey', 'sku', 'status', 'created_at', 'updated_at'];

        /**
         * @var启用
         */
        const STATUS_ON = 1;

        const STATUS_OFF= 2;



    }