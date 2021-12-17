<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformsInformation extends Model
{
    //
    protected $table = 'platforms_information' ;

    /**
     * @var 草稿
     */
    const STATUS_DRAFT = 0 ;

    /**
     * @var 上架失败
     */
    const STATUS_PUT_FAIL = 1 ;

    /**
     * @var 已上架
     */
    const STATUS_PUT_ON = 2 ;

    /**
     * @var 更新失败
     */
    const STATUS_UPDATE_FAIL = 3 ;


    /**
     * @desc 更新商品的信息
     * @author zt6650
     * CreateTime: 2019-04-16 18:15
     * @param $id
     * @param $insertArr
     * @return bool
     */
    public function updateById($id ,$insertArr)
    {
        $insertArr['updated_at'] = date('Y-m-d H:i:s') ;
        $re = $this->where('id' ,$id)->update($insertArr) ;

        if ($re === false) {
            return false ;
        }

        return true ;
    }
}
