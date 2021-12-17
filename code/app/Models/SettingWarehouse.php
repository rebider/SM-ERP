<?php

    namespace App\Models;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Common\Enums\AccountType;
    use Illuminate\Database\Eloquent\Model;
    use App\Auth\Models\Users;

    /**
     * Class WarehouseHandle.php
     * Notes: 仓库表
     * @package App\Models
     * Data: 2019/3/7 15:19
     * Author: zt7785
     */
    class SettingWarehouse extends Model
    {
        //启用
        const ON = 1;//是
        //禁用
        const OFF = 2;//否

        const SELFDEFINE = 2;

        //速贸仓储
        const SM_TYPE = 1;
        //自定义
        const CUSTOM_TYPE = 2;

        protected $table = 'setting_warehouse';
        public $timestamps = true;
        public $primaryKey = 'id';

        public $fillable = ['id', 'created_man', 'type', 'warehouse_name', 'facilitator', 'charge_person', 'phone_number', 'qq', 'address', 'disable', 'created_at', 'updated_at','user_id'];


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
         * @return $this
         * Note: 物流关联仓库模型
         * Data: 2019/3/26 19:00
         * Author: zt8067
         */
        public function Logistics()
        {
            return $this->belongsToMany(SettingLogistics::class, 'setting_logistics_warehouses', 'warehouse_id', 'logistic_id');
        }

        /**
         * @return $this
         * Note: 获取完整仓库信息
         * Data: 2019/3/11 14:06
         * Author: zt8076
         */
        public static function getCompleteWarehouse($hidden = [])
        {
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            return self::where('user_id',$user_id)->orderByDesc('created_at')->get()->map(function($item) use ($hidden){
                if(empty($item)) return false;
                $data = $item->makeHidden($hidden)->toArray();
                $data['type'] = $data['type']['type_name'] ?? '';
                return $data;
            });
        }

        /**
         * @return collection
         * Note: 仓库管理搜索列表
         * Data: 2019/3/15 16:03
         * Author: zt8076
         */
        public static function getSummaryByPage($params)
        {
            $data   = $params->all();
            $limit  = $params->get('limit',20);
            $CurrentUser = CurrentUser::getCurrentUser();
            if($CurrentUser->userAccountType == AccountType::CHILDREN){
                //主账号id
                $user_id = $CurrentUser->userParentId;
            }else{
                $user_id = $CurrentUser->userId;
            }
            $param['type'] = (isset($data['data']['type']) &&  !empty($data['data']['type']))
                ? $data['data']['type'] : '';

            $param['disable'] = (isset($data['data']['disable']) &&  !empty($data['data']['disable']))
                ? $data['data']['disable'] : '';

            $param['name'] = (isset($data['data']['name']) &&  !empty($data['data']['name']))
                ? $data['data']['name'] : '';

            $collection =self::query();

            $param['type'] &&  $collection->where('type',$param['type']);
            $param['disable'] && $collection->where('disable',$param['disable']);
            $param['name'] && $collection->where('warehouse_name','like','%'.$param['name'].'%');


            $pagingData = $collection->where('user_id',$user_id)->orderByDesc('id')->paginate($limit)->toArray();

            return $pagingData;
        }

        /**
         * @param $user_id
         * @return array
         * Note: 获取客户所有启用中的物流
         * Data: 2019/3/23 15:27
         */
        public static function getAllWarehousesByUserId($user_id)
        {
            return self::where('user_id',$user_id)->where('disable',self::ON)->orderBy('id','ASC')->get(['id','type','warehouse_name','created_man','user_id'])->toArray();
        }

        public static function getAllCountryExclud($ids)
        {
            return self::whereNotIn('id',$ids)->where('disable',self::ON)->orderBy('id','ASC')->get(['id','type','warehouse_name','created_man','user_id'])->toArray();
        }

        public static function getAllCountryInclud($ids)
        {
            return self::whereIn('id',$ids)->where('disable',self::ON)->orderBy('id','ASC')->get(['id','type','warehouse_name','created_man','user_id'])->toArray();
        }

        /**
         * @description 获取开启中的仓库
         * @author zt7927
         * @date 2019/4/16 17:16
         * @return array
         */
        public static function getWarehouseByStatus($user_id)
        {
            return self::where('disable', self::ON)->where('user_id', $user_id)->get()->toArray();
        }
    }
