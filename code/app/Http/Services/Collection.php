<?php
/**
 * Created by PhpStorm.
 * User: jungle
 * Date: 2019/3/13
 * Time: 10:06
 */

namespace App\Http\Services;


interface Collection
{
    /**
     * @description 获取商品的标题
     * @author zt6650
     * @creteTime 2019/3/13 10:13
     * @return mixed
     */
    public function getTitleByUrl() ;

    /**
     * @description 获取商品的描述
     * @author zt6650
     * @creteTime 2019/3/13 10:14
     * @return mixed
     */
    public function getDescriptionByUrl() ;

    /**
     * @description 获取商品的名称
     * @author zt6650
     * @creteTime 2019/3/13 10:14
     * @return mixed
     */
    public function getNameByUrl() ;


    /**
     * @description 获取商品的所有信息
     * @author zt6650
     * @creteTime 2019/3/13 10:14
     * @return mixed
     */
    public function getAllByUrl() ;

    /**
     * @description 获取主图
     * @author zt6650
     * @creteTime 2019/3/13 14:47
     * @return mixed
     */
    public function getImgImport() ;

    /**
     * @description 获取附图
     * @author zt6650
     * @creteTime 2019/3/13 14:48
     * @return mixed
     */
    public function getImgSup() ;

    /**
     * @description 获取平台
     * @author zt6650
     * @creteTime 2019/3/20 15:50
     * @return mixed
     */
    public function getPlate() ;
    
}