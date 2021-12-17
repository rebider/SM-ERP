<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Suppliers
 * @description: 供应商表
 * @author: zt7927
 * @data: 2019/3/18 17:19
 * @package App\Models
 */
class Suppliers extends Model
{
    protected $table = 'suppliers';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'created_man', 'name', 'linkman', 'tel_no', 'address', 'email', 'status', 'created_at', 'updated_at','user_id'];

    /**
     * @var 供应商状态-禁用
     */
    const OFF = 1;
    /**
     * @var 供应商状态-开启
     */
    const ON  = 2;

    /**
     * @description 供应商搜索
     * @author zt7927
     * @date 2019/4/16 11:38
     * @param array $params
     * @return $this
     */
    public function search(array $params = [])
    {
        $collection = new self();

        //供应商状态(启用 停用)
        if (isset($params['status']) && $params['status']) {
            $collection = $collection->where('status', $params['status']);
        }

        //供应商名称
        if (isset($params['name']) && $params['name']) {
            $collection = $collection->where('name','like','%'. $params['name'] .'%');
        }

        //联系人
        if (isset($params['linkman']) && $params['linkman']) {
            $collection = $collection->where('linkman','like','%'. $params['linkman'] .'%');
        }

        //联系方式
        if (isset($params['tel_no']) && $params['tel_no']) {
            $collection = $collection->where('tel_no','like','%'. $params['tel_no'] .'%');
        }

        if (isset($params['user_id']) && $params['user_id']) {
            $collection = $collection->where('user_id',$params['user_id']);
        }

        return $collection->orderBy('created_at', 'DESC');
    }

    /**
     * @description 查询所有开启状态下的供应商
     * @author zt7927
     * @data 2019/3/18 17:23
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllSuppliers($user_id)
    {
        return self::where('status', self::ON)->where('user_id',$user_id)->get()->toArray();
    }

    /**
     * @description 根据id查询供应商名称
     * @author zt7927
     * @data 2019/3/19 10:45
     * @param $id
     * @return mixed
     */
    public function getSupplierDataById($id)
    {
        if (is_numeric($id) && $id>0){
            return self::where('id', $id)->first();
        }
        return false;
    }

    /**
     * @description 更新供应商信息
     * @author zt7927
     * @date 2019/4/16 15:07
     * @param $data
     * @param $id
     * @return bool
     */
    public function updatedArr($data, $id)
    {
        return self::where('id', $id)->update($data);
    }

    /**
     * @description 启用禁用供应商
     * @author zt7927
     * @date 2019/4/16 16:02
     * @param $id
     * @return array
     */
    public function changeStatus($id)
    {
        $status = self::where('id', $id)->pluck('status')->first();
        if ($status === self::ON){
            $status = self::OFF;
        } else {
            $status = self::ON;
        }

        $re = self::where('id', $id)->update([
            'status' => $status
        ]);
        if ($re){
            return [
              'status' => 1,
              'msg'    => '操作成功'
            ];
        }
        return [
            'status' => 0,
            'msg'    => '操作失败'
        ];
    }
}
