<?php
/**
 * Created by PhpStorm.
 * User: jungle
 * Date: 2019/3/13
 * Time: 10:05
 */

namespace App\Http\Services\Plate;


use App\Http\Services\Collection;
use App\Models\GoodsCollection;
use QL\QueryList;


class LeTian implements Collection
{
    /**
     * @var 采集数据的链接
     */
    protected $url ;

    /**
     * @var  QueryList::get()
     */
    protected $ql ;

    public function __construct($url)
    {
        $this->url = $url ;
        $this->setQl() ;
    }

    /**
     * @description 设置句柄
     * @author zt6650
     * @creteTime 2019/3/13 14:52
     */
    protected function setQl()
    {
        $this->ql = QueryList::get($this->url , [] , ['headers'=> ['Referer'=>'https://item.rakuten.co.jp']]) ;
    }

    public function getAllByUrl()
    {
        $re = [
            'title'=>$this->getTitleByUrl() ,
            'description'=>$this->getDescriptionByUrl() ,
            'imgMain'=>$this->getImgImport() ,
            'imgSup'=>$this->getImgSup() ,
            'plat'=>$this->getPlate()
        ] ;
       return $re ;
    }

    public function getNameByUrl()
    {

    }

    public function getDescriptionByUrl()
    {
        $description = $this->ql->find('.catch_copy')->texts()->first();
        return $description ;
    }

    public function getImgImport()
    {
        $mainImg = $this->ql->find('.i_photo li img')->attrs('src')->first();
        if (empty($mainImg)) {
            $mainImg = $this->ql->find('.rakutenLimitedId_ImageMain1-3 img')->attrs('src')->first();
        }
        $extArr = ['.gif','.jpg','.jpeg','.png'];
        $newmainImg = '';
        foreach ($extArr as $value) {
            if (!is_bool(strpos($mainImg,$value))) {
                $mainImgArr = explode($value,$mainImg);
                $newmainImg = $mainImgArr[0].$value;
                break;
            }
        }
        return $newmainImg ;
    }

    public function getImgSup()
    {
        $supImg = $this->ql->find('.i_photo li img')->attrs('src')->all();
        return $supImg ;
    }

    public function getTitleByUrl()
    {
        $titles = $this->ql->find('.item_name')->texts()->first();
        //打印结果
       return $titles ;
    }

    public function getPlate()
    {
        return GoodsCollection::PLAT_LETIAN ;
    }
}