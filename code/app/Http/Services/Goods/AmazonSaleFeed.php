<?php


namespace App\Http\Services\Goods;


use App\AmazonMWS\GithubMWS\MCS\AmazonMarketPlaceProduct;
use App\Models\AmazonApiServiceRequest;

class AmazonSaleFeed
{
    public function __construct()
    {
        if (PHP_VERSION_ID < 50600) {
            iconv_set_encoding('input_encoding', 'UTF-8');
            iconv_set_encoding('output_encoding', 'UTF-8');
            iconv_set_encoding('internal_encoding', 'UTF-8');
        } else {
            ini_set('default_charset', 'UTF-8');
        }
    }

    /**
     * Note: 新增商品上架队列信息
     * Data: 2019/6/17 20:39
     * Author: zt7785
     */
    public function putOnListPush ($param) {
        $insertData ['method'] = $param ['method'];
        $insertData ['request_table'] = $param ['request_table'];
        $insertData ['request_pk'] = $param ['request_pk'];
        $insertData ['request_user_id'] = $param ['request_user_id'];
        $insertData ['request_shop_id'] = $param ['request_shop_id'];
        $insertData ['is_finished'] = AmazonApiServiceRequest::UN_FINISHED;
        $insertData ['exception_info'] = '';
        $insertData ['params'] = json_encode($param ['params']);
        AmazonApiServiceRequest::postData (0,$insertData);
    }

    /**
     * @param $putOnRequsetInfo
     * @return array
     * Note: 上架
     * Data: 2019/6/17 21:13
     * Author: zt7785
     */
    public function putOn (array $putOnRequsetInfo) {

        $products = [];
        $product = new AmazonMarketPlaceProduct();
        foreach ($putOnRequsetInfo as $paramKey => $paramVal) {
            if (property_exists($product, $paramKey)) {
                $product->$paramKey = $paramVal;
            }
        }
//        $product->setSku('270002')
//            ->setPrice('2333.00')
//            ->setProductId('B07PDJZHBD')
//            ->setProductIdType('ASIN')
//            ->setConditionType('New')
//            ->setWeight('233')
//            ->setQuantity('2')
//            ->setTitle('setTitlesTitle')
//            ->setBrand('setBrandsetBrand')
//            ->setRecommendedBrowseNodes('2039662051')
//            ->setImage(['http://jinrong.zongteng.net/fkweb/images/banner.jpg','http://jinrong.zongteng.net/fkweb/images/banner4.jpg','http://jinrong.zongteng.net/fkweb/images/banner1.jpg']);
        array_push($products, $product);
        return ['method'=>'postProducts','param'=>['MWSProduct'=>$products]];
    }

    /**
     * @param array $putOnRequsetInfo
     * @return array
     * Note: 编辑商品
     * Data: 2019/6/19 17:51
     * Author: zt7785
     */
    public function updateProduct (array $putOnRequsetInfo) {
        $products = [];
        $product = new AmazonMarketPlaceProduct();
        foreach ($putOnRequsetInfo as $paramKey => $paramVal) {
            if (property_exists($product, $paramKey)) {
                $product->$paramKey = $paramVal;
            }
        }
        array_push($products, $product);
        return ['method'=>'postProducts','param'=>['MWSProduct'=>$products]];
    }

    /**
     * @param $putOffRequsetInfo
     * Note: 下架
     * Data: 2019/6/18 9:15
     * Author: zt7785
     */
    public function putOff ($putOffRequsetInfo) {
        return ['method'=>'deleteProductBySKU','param'=>['deleteProducts'=>$putOffRequsetInfo]];
    }

    /**
     * @param $editPriceRequsetInfo
     * @param null $editSalePriceRequsetInfo
     * @return array
     * Note: 只编辑价格
     * Data: 2019/6/18 13:59
     * Author: zt7785
     */
    public function editPrice ($editPriceRequsetInfo,$editSalePriceRequsetInfo = null) {
        return ['method'=>'updatePrice','param'=>['standardprice'=>$editPriceRequsetInfo,'saleprice'=>$editSalePriceRequsetInfo]];
    }

    /**
     * @param $putOffRequsetInfo
     * @return array
     * Note: 只编辑数量
     * Data: 2019/6/18 9:19
     * Author: zt7785
     */
    public function editQuantity ($editQuantityRequsetInfo) {
        return ['method'=>'updateStock','param'=>['productStock'=>$editQuantityRequsetInfo]];
    }

    /**
     * @param $editImageRequsetInfo
     * @return array
     * Note: 只编辑图片信息
     * Data: 2019/6/18 14:23
     * Author: zt7785
     */
    public function editGoodsImage ($editImageRequsetInfo) {
        return ['method'=>'updateGoodsImage','param'=>['standardImage'=>$editImageRequsetInfo]];
    }
}