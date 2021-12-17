<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Integer;

class GoodsCollection extends Model
{
    //
    protected $table = 'goods_collection';

    public $timestamps = true;

    public $primaryKey = 'id';


    /**
     * @var 乐天平台
     */
    const PLAT_LETIAN = 1 ;

    /**
     * @var 亚马逊平台
     */
    const PLAT_AMAZON = 2 ;

    /**
     * @var 未认领
     */
    const STATUS_WAIT = 1 ;

    /**
     * @var 已经认领
     */
    const STATUS_PASS = 2 ;

    /**
     * @var 未同步
     */
    const SYNCHRONIZATION_NO = 1 ;

    /**
     * @var 同步失败
     */
    const SYNCHRONIZATION_FAIL = 2 ;

    /**
     * @var 同步成功
     */
    const SYNCHRONIZATION_SUCCESS = 3 ;

    /**
     * @description　关联模型　商品图片信息
     * @author zt6650
     * @creteTime 2019/3/26 10:23
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function GoodsPic()
    {
        return $this->hasMany(GoodsPic::class ,'goods_id' ,'id') ;
    }

    public function Goods()
    {
        return $this->belongsTo(Goods::class ,'goods_id' ,'id') ;
    }

    /**
     * @description 根据条件查询
     * @author zt6650
     * @creteTime 2019/3/11 15:01
     * @param array $params
     * @return self ;
     */
    public function search(array $params=[])
    {
        $collection = self::with('Goods');
        if (isset($params['start_time']) && $params['start_time']){ //采集时间
            $collection = $collection->whereDate('created_at' ,'>=' ,date_create($params['start_time'])->format('Y-m-d')) ;
        }
        $collection->where('user_id',$params ['user_id']);
        if (isset($params['end_time']) && $params['end_time']){ //采集时间
            $collection = $collection->whereDate('created_at' ,'<=' ,date_create($params['end_time'])->format('Y-m-d')) ;
        }

        if (isset($params['status']) && $params['status'] && in_array($params['status'] ,[self::STATUS_WAIT ,self::STATUS_PASS])){ //认领状态
            $collection = $collection->where('status' ,$params['status']) ;
        }

        if (isset($params['plat']) && $params['plat']){ //产品分类
            $collection = $collection->where('plat' ,$params['plat']) ;
        }
        $collection->orderBy('created_at','DESC');
        return $collection ;

    }

    /**
     * @description 存入商品
     * @author zt6650
     * @creteTime 2019/3/11 15:20
     * @param array $insert
     * @return bool|int
     */
    public function insertArr(array $insert=[],$userInfo)
    {
        $insert['created_man'] = $userInfo ['created_man'] ;
        $insert['user_id'] = $userInfo ['user_id'] ;
        $insert['created_at'] = date('Y-m-d H:i:s') ; //todo 获取创建人
        $insert['updated_at'] = date('Y-m-d H:i:s') ; //todo 获取创建人
        $re = $this->insertGetId($insert) ;
        if ($re){
            return $re ;
        }

        return false ;
    }

    /**
     * @description 更新商品的信息
     * @author zt6650
     * @creteTime 2019/3/11 17:16
     * @param array $updateArr
     * @param int $goods_id
     * @return bool
     */
    public function updateById(array $updateArr=[] ,$goods_id=0)
    {
        $updateArr['updated_at'] = date('Y-m-d H:i:s') ;
        $re = $this->where('id' ,$goods_id)->update($updateArr) ;
        if ($re === false){
            return false ;
        }else{
            return true ;
        }
    }

    /**
     * @description
     * @author zt6650
     * @creteTime 2019/3/12 14:58
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function delById($id,$user_id)
    {
        if (is_numeric($id) && $id>0){//单个删除
            return $this->where('id' ,$id)->where('user_id',$user_id)->delete() ;
        }elseif (is_array($id)){                 //批量删除
            return $this->whereIn('id' ,$id)->where('user_id',$user_id)->delete() ;
        }

        return false ;
    }


    /**
     * @description 认领
     * @author zt6650
     * @creteTime 2019/3/22 11:40
     * @param $id
     * @return bool
     */
    public function claimById($id,$goods_id)
    {
        if (!is_numeric($id) && $id>0){
            return false ;
        }

        $re = $this->where('id' ,$id)->update(['status'=>self::STATUS_PASS,'goods_id'=>$goods_id,'updated_at'=>date('Y-m-d H:i:s')]);

        if ($re === false){
            return false ;
        }

        return true ;
    }


    /**
     * @description 获取单个商品牛的信息
     * @author zt6650
     * @creteTime 2019/3/26 10:23
     * @param int $id
     * @return GoodsCollection|bool|\Illuminate\Database\Eloquent\Builder|Model|null
     */
    public function getInfoById($id=0)
    {
        if (!$id || !is_numeric($id)){
            return false ;
        }

        $re = $this->with('GoodsPic')->where('id' ,$id)->first() ;
        if ($re) {
            return $re ;
        }

        return false ;
    }
}
