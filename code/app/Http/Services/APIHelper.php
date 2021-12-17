<?php
/**
 * Created by PhpStorm.
 * User: jungle
 * Date: 2019/3/13
 * Time: 9:56
 */

namespace App\Http\Services;


use App\Http\Services\Plate\Amazon;
use App\Http\Services\Plate\ChinerBank;
use App\Http\Services\Plate\LeTian;
use phpDocumentor\Reflection\Types\Null_;

class APIHelper
{

    protected $url ;            //采集数据的接口地址

    /**
     * @var Collection ;
     */
    public $plate  ;         //本次所用的平台

    const LETIAN = 'https://item.rakuten.co.jp' ;  //乐天平台

    const AMAZON = 'https://www.amazon.co.jp' ;  //亚马逊平台

    const CHINER_BANK = 'http://www.boc.cn' ;  //中国银行

    public function __construct($url)
    {
        $this->url = $url ;
        $this->getPlat() ;
    }

    protected function checkUrl()
    {

    }

    /**
     * @description 获取平台 || 乐天或是亚马逊
     * @author zt6650
     * @creteTime 2019/3/13 10:26
     */
    protected function getPlat()
    {
        if(strpos($this->url,self::AMAZON) !== false){
            $this->plate = new Amazon($this->url) ;
            return true ;
        }elseif(strpos($this->url,self::LETIAN) !== false){
           $this->plate = new LeTian($this->url) ;
           return true ;
        }else if(strpos($this->url,self::CHINER_BANK) !== false){
            $this->plate = new ChinerBank($this->url) ;
            return true ;
        }
        info($this->url.'有误!') ;
        return false ;
    }

    /**
     * @description 获取标题
     * @author zt6650
     * @creteTime 2019/3/13 10:48
     * @return mixed
     */
    public function getTitle()
    {
        return $this->plate->getTitleByUrl() ;
    }

    /**
     * @description 获取商品的描述
     * @author zt6650
     * @creteTime 2019/3/13 10:48
     * @return mixed
     */
    public function getDescription()
    {
        return $this->plate->getDescriptionByUrl() ;
    }

    /**
     * @description 获取商品的名
     * @author zt6650
     * @creteTime 2019/3/13 10:48
     * @return mixed
     */
    public function getName()
    {
        return $this->plate->getNameByUrl() ;
    }

    public function getPlate()
    {
        return $this->plate->getPlate() ;
    }

    /**
     * @description 获取采集的所有信息
     * @author zt6650
     * @creteTime 2019/3/13 10:49
     * @return mixed
     */
    public function getAll()
    {
        return $this->plate->getAllByUrl() ;
    }

    public function __destruct()
    {
        $this->plate = null ;
    }


}