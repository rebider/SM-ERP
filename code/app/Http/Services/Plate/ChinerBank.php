<?php
/**
 * Created by PhpStorm.
 * User: jungle
 * Date: 2019/3/13
 * Time: 10:05
 */

namespace App\Http\Services\Plate;
use App\Http\Services\APIHelper;
use App\Models\GoodsCollection;
use QL\QueryList ;

use App\Http\Services\Collection;

class ChinerBank implements Collection
{
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
        $res = QueryList::get($this->url, [], [
            'headers' => [
                'cache-control' => 'no-cache',
                'User-Agent' => 'PostmanRuntime/7.6.0',
                'Accept' => "*/*",
                'Host' => 'www.boc.cn',
                'accept-encoding' => 'gzip, deflate'
            ]
        ]);
        $html = str_replace('Shift_JIS' ,'UTF-8' ,$res->getHtml() ) ;
        $this->ql = QueryList::html($html) ;
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

    public function getPlate()
    {
        return GoodsCollection::PLAT_AMAZON ;
    }

    public function getNameByUrl()
    {

    }

    public function getDescriptionByUrl()
    {
        $des   = $this->ql->find('.publish table tr td ')->texts()->all() ;
        return $des ;
    }

    public function getImgImport()
    {
        $mainImg = $this->ql->find('.a-button-inner span img')->attrs('src')->first(); //中国亚马逊主图
        return $mainImg ;
    }

    public function getImgSup()
    {
        $supImg = $this->ql->find('#altImages ul img')->attrs('src')->all(); //中国亚马逊附图
        return $supImg ;
    }

    public function getTitleByUrl()
    {
        $title   = $this->ql->find('#productTitle')->texts()->first() ; //中国亚马逊商品名称
        return $title ;
    }

}