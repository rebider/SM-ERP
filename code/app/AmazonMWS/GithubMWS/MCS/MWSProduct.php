<?php
namespace App\AmazonMWS\GithubMWS\MCS;

use Exception;

/**
 * Class MWSProduct
 * Notes: 亚马逊商品操作类
 * @package App\AmazonMWS\GithubMWS\MCS
 * Data: 2019/6/11 14:08
 * Author: zt7785
 */
class MWSProduct{

    /*
     * @var 商品SKU
     */
    public $sku;
    /*
     * @var 商品价格格式:1291.00
     */
    public $price;
    /*
     * @var 商品数量
     */
    public $quantity = 0;
    /**
     * @var 产品唯一标识
     */
    public $product_id;
    /**
     * @var 产品唯一类型:ASIN,UPC,EAN
     */
    public $product_id_type;
    /**
     * @var string 商品类型:New全新
     */
    public $condition_type = 'New';
    /**
     * @var 说明项目实际情况的描述性文本。如果条件类型不是“New”，则必需。一个文本字符串;最长1000个字符。
     */
    public $condition_note = '';
    /**
     * @var string ASIN-hint 您正在销售的产品的Amazon标准标识符或ASIN。您可以从Amazon上的产品详细信息页面获得ASIN。这是为了解决标准产品id与Amazon上的两个或多个产品匹配时的模糊性。Amazon标准标识符也称为ASIN
     */
    public $ASIN_hint;
    /**
     * @var string 您不需要提供产品名称。执行产品查找时，此字段将自动填充Amazon目录中的产品标题。它的目的是为您提供关于您的标识符将在Amazon站点上匹配的产品的反馈。使用产品查找时自动填充的产品标题。此字段中的值将不用于更新Amazon目录中的产品标题。
     */
    public $title;


    public $product_tax_code = '';
    /**
     * @var string 指定要对提供的数据执行的操作类型(更新、删除)。如果留空，默认行为是“Update”。当您希望从目录中完全永久删除列表时，请使用“Delete”。选择以下选项之一:更新或删除。
     */
    public $operation_type = 'Update';

    /**
     * @var 指商家以美元表示的销售价格。该网站将标出该商品的正常价格，并表明该商品正在以原价出售。
    小数点左边最多允许有18位，小数点右边最多允许有2位。请不要使用逗号或美元符号。
     */
    public $sale_price;
    /**
     * @var 销售价格将开始覆盖项目正常价格的日期:yyyy-mm-dd 2004-01-22
     */
    public $sale_start_date;
    /**
     * @var 销售价格将超过该物品正常价格的最后一个日期;商品的正常价格将在之后显示。此格式的日期:yyyy-mm-dd 2004-03-05
     */
    public $sale_end_date;

    /**
     * @var array
     * product_tax_code : 亚马逊的标准代码，用来识别产品的税务属性。只有当您确定您在销售者中心是免税的，并且您的所有产品都有亚马逊的税务代码时，才可以使用产品的税务代码。
     * operation_type : 指定要对提供的数据执行的操作类型(更新、删除)。如果留空，默认行为是“Update”。当您希望从目录中完全永久删除列表时，请使用“Delete”。
     * leadtime_to_ship :  指示从收到某项商品的订单到可以装运该商品之间的时间(以天为单位)。默认的交付时间是一到两个工作日。如果交付时间超过两个工作日，请使用此字段。
     * launch_date : 指定此清单在站点上可用的日期。
     * is-giftwrap-available : 这个礼品包装是否支持这个特殊的产品?如果留白，默认为“false”。
     * is_gift_message_available : 此特定产品是否支持礼品消息传递?如果留白，默认为“false”。
     * fulfillment_center_id : Amazon- fulfillment products:对于使用Amazon fulfillment services的商家，它指定将使用哪个fulfillment network。如果为fulfillment-center-id指定一个“DEFAULT”以外的值，则将取消已销售的产品。重新提交一个空白或“默认”值的履行中心id，连同数量，将切换回商品履行。
    全销产品:不要输入全销中心id，因为它不适用。
     */
    protected $defaultVal = [

        'operation_type'=>[
            '_default'  =>'Update',
            '_scope'  =>[
                'Update','Delete'
            ]
        ],
        'is_giftwrap_available'=>[
            '_default'  =>'false',
            '_scope'  =>[
                'true','false'
            ]
        ],
        'is_gift_message_available'=>[
            '_default'  =>'false',
            '_scope'  =>[
                'true','false'
            ]
        ],
        'fulfillment_center_id'=>[
          '_default'  =>'AMAZON_NA',
          '_scope'  =>[
              'AMAZON_NA','DEFAULT'
          ]
        ],

    ];
    /**
     * @var array 异常错误信息集合
     */
    private $validation_errors = [];

    /**
     * @var array 商品属性 全新 二手
     */
    private $conditions = [
        'New', 'Refurbished', 'UsedLikeNew', 
        'UsedVeryGood', 'UsedGood', 'UsedAcceptable'
    ];

    /**
     * 初始化 处理商品信息
     * MWSProduct constructor.
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        foreach ($array as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * @return array
     * Note: 返回验证异常信息
     * Data: 2019/6/11 14:11
     * Author: zt7785
     */
    public function getValidationErrors()
    {
        return $this->validation_errors;   
    }

    /**
     * @return array
     * Note: 转换为标准数组信息
     * Data: 2019/6/11 14:11
     * Author: zt7785
     */
    public function toArray()
    {
        //Required : sku price quantity product-id product-id-type condition-type condition-note
        //Optional : ASIN_hint title product_tax_code operation_type sale_price sale_start_date sale_end_date leadtime_to_ship launch_date is_giftwrap_available is_gift_message_available fulfillment_center_id main_offer_image offer_image1 _ offer_image5
        $response = [
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'product_id' => $this->product_id,
            'product_id_type' => $this->product_id_type,
            'condition_type' => $this->condition_type,
            'condition_note' => $this->condition_note,
//            //↑以上为必填项
        ];
        return $response;
        $OptionalArr = ["ASIN_hint","title","product_tax_code","operation_type","sale_price","sale_start_date","sale_end_date","leadtime_to_ship","launch_date","is_giftwrap_available","is_gift_message_available","fulfillment_center_id"];

        foreach ($OptionalArr as $Optional) {
            if (property_exists($this, $Optional)) {
                $response [$Optional] = $this->$Optional;
            } else {
                $response [$Optional] = '';
            }
        }
        return $response;

//            'ASIN_hint' => $this->ASIN_hint,
//            'title' => $this->title,
//            'product_tax_code' => $this->product_tax_code,
//            'operation_type' => $this->operation_type,
//            'sale_price' => $this->sale_price,
//            'sale_start_date' => $this->sale_start_date,
//            'sale_end_date' => $this->sale_end_date,
    }

    /**
     * @return bool
     * Note: 商品信息验证
     * Data: 2019/6/11 11:14
     * Author: zt7785
     */
    public function validate()
    {
        //SKU验证
        if (mb_strlen($this->sku) < 1 or strlen($this->sku) > 40) {
            $this->validation_errors['sku'] = 'Should be longer then 1 character and shorter then 40 characters';
        }
        //价格验证
        $this->price = str_replace(',', '.', $this->price);

        $exploded_price = explode('.', $this->price);

        if (count($exploded_price) == 2) {
            if (mb_strlen($exploded_price[0]) > 18) { 
                $this->validation_errors['price'] = 'Too high';        
            } else if (mb_strlen($exploded_price[1]) > 2) {
                $this->validation_errors['price'] = 'Too many decimals';    
            }
        } else {
            $this->validation_errors['price'] = 'Looks wrong';        
        }
        
        $this->quantity = (int) $this->quantity;
        $this->product_id = (string) $this->product_id;
        
        $product_id_length = mb_strlen($this->product_id);
        //ASIN UPC EAN 长度校验
        switch ($this->product_id_type) {
            case 'ASIN':
                if ($product_id_length != 10) {
                    $this->validation_errors['product_id'] = 'ASIN should be 10 characters long';                
                }
                break;
            case 'UPC':
                if ($product_id_length != 12) {
                    $this->validation_errors['product_id'] = 'UPC should be 12 characters long';                
                }
                break;
            case 'EAN':
                if ($product_id_length != 13) {
                    $this->validation_errors['product_id'] = 'EAN should be 13 characters long';                
                }
                break;
            default:
               $this->validation_errors['product_id_type'] = 'Not one of: ASIN,UPC,EAN';        
        }
        
        if (!in_array($this->condition_type, $this->conditions)) {
            $this->validation_errors['condition_type'] = 'Not one of: ' . implode($this->conditions, ',');                
        }
        
        if ($this->condition_type != 'New') {
            $length = mb_strlen($this->condition_note);
            if ($length < 1) {
                $this->validation_errors['condition_note'] = 'Required if condition_type not is New';                    
            } else if ($length > 1000) {
                $this->validation_errors['condition_note'] = 'Should not exceed 1000 characters';                    
            }
        }
        
        if (count($this->validation_errors) > 0) {
            return false;    
        } else {
            return true;    
        }
    }
    
    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            return $this->$property = $value;
        }
    }    
}
