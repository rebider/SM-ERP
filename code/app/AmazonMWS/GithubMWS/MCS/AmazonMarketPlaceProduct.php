<?php


namespace App\AmazonMWS\GithubMWS\MCS;


class AmazonMarketPlaceProduct
{

    public $feed_product_type;
    public $sku;
    public $price;
    public $quantity = 0;
    public $product_id;
    public $product_id_type;
    public $condition_type = 'New';
    public $condition_note;
    public $title;
    public $brand;
    public $shipping;
    public $weight;
    public $image = [];
    public $recommended_browse_nodes;

    /**
     * @param mixed $sku
     * @return AmazonMarketPlaceProduct
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @param mixed $price
     * @return AmazonMarketPlaceProduct
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param int $quantity
     * @return AmazonMarketPlaceProduct
     */
    public function setQuantity(int $quantity): AmazonMarketPlaceProduct
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param mixed $product_id
     * @return AmazonMarketPlaceProduct
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     * @param mixed $product_id_type
     * @return AmazonMarketPlaceProduct
     */
    public function setProductIdType($product_id_type)
    {
        $this->product_id_type = $product_id_type;
        return $this;
    }

    /**
     * @param string $condition_type
     * @return AmazonMarketPlaceProduct
     */
    public function setConditionType(string $condition_type): AmazonMarketPlaceProduct
    {
        $this->condition_type = $condition_type;
        return $this;
    }

    /**
     * @param mixed $condition_note
     * @return AmazonMarketPlaceProduct
     */
    public function setConditionNote($condition_note)
    {
        $this->condition_note = $condition_note;
        return $this;
    }

    /**
     * @param mixed $title
     * @return AmazonMarketPlaceProduct
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $brand
     * @return AmazonMarketPlaceProduct
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @param mixed $shipping
     * @return AmazonMarketPlaceProduct
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * @param array $image
     * @return AmazonMarketPlaceProduct
     */
    public function setImage(array $image): AmazonMarketPlaceProduct
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param array $validation_errors
     * @return AmazonMarketPlaceProduct
     */
    public function setValidationErrors(array $validation_errors): AmazonMarketPlaceProduct
    {
        $this->validation_errors = $validation_errors;
        return $this;
    }

    /**
     * @param array $conditions
     * @return AmazonMarketPlaceProduct
     */
    public function setConditions(array $conditions): AmazonMarketPlaceProduct
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     * @return AmazonMarketPlaceProduct
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecommendedBrowseNodes()
    {
        return $this->recommended_browse_nodes;
    }

    /**
     * @param string $recommended_browse_nodes
     * @return AmazonMarketPlaceProduct
     */
    public function setRecommendedBrowseNodes($recommended_browse_nodes)
    {
        $this->recommended_browse_nodes = $recommended_browse_nodes;
        return $this;
    }

    /**
     * @return string
     */
    public function getFeedProductType()
    {
        return $this->feed_product_type;
    }

    /**
     * @param string $feed_product_type
     * @return AmazonMarketPlaceProduct
     */
    public function setFeedProductType($feed_product_type)
    {
        $this->feed_product_type = $feed_product_type;
        return $this;
    }


    private $validation_errors = [];

    private $conditions = [
        'New', 'Refurbished', 'UsedLikeNew',
        'UsedVeryGood', 'UsedGood', 'UsedAcceptable'
    ];

    public function __construct(array $array = [])
    {
        foreach ($array as $property => $value) {
            $this->{$property} = $value;
        }
    }

    public function getValidationErrors()
    {
        return $this->validation_errors;
    }



    public function toArray()
    {
        /*'feed_product_type' => 'television',*/
        return [
            'feed_product_type' => $this->feed_product_type,
            'item_sku' => $this->sku,
            'external_product_id' => $this->product_id,
            'external_product_id_type' => $this->product_id_type,
            'brand_name' => utf8_decode($this->brand),
            'item_name' => utf8_decode($this->title),
            "manufacturer" => utf8_decode($this->brand),
            'standard_price' => $this->price,
            'quantity' => $this->quantity,
            'merchant_shipping_group_name' => utf8_decode($this->shipping),
            'website_shipping_weight_unit_of_measure' => $this->weight,
            'recommended_browse_nodes' => $this->recommended_browse_nodes,
            'main_image_url' => array_key_exists(0, $this->image) ? $this->image[0] : '',
            'other_image_url1' => array_key_exists(1, $this->image) ? $this->image[1] : '',
            'other_image_url2' => array_key_exists(2, $this->image) ? $this->image[2] : '',
            'other_image_url3' => array_key_exists(3, $this->image) ? $this->image[3] : '',
        ];

        /*
        return [

            "feed_product_type" => "",
            "item_sku" => "",
            "external_product_id" => "",
            "external_product_id_type" => "",
            "brand_name" => "",
            "item_name" => "",
            "manufacturer" => "",
            "standard_price" => "",
            "quantity" => "",
            "merchant_shipping_group_name" => "",
            "main_image_url" => "",

            "swatch_image_url" => "",
            "other_image_url1" => "",
            "other_image_url2" => "",
            "other_image_url3" => "",
            "main_offer_image" => "",
            "offer_image" => "",
            "recommended_browse_nodes" => "",
            "product_description" => "",
            "part_number" => "",
            "model" => "",
            "update_delete" => "",
            "item_display_length" => "",
            "item_display_width" => "",
            "item_display_height" => "",
            "display_dimensions_unit_of_measure" => "",
            "item_display_weight" => "",
            "item_display_weight_unit_of_measure" => "",
            "volume_capacity_name" => "",
            "volume_capacity_name_unit_of_measure" => "",
            "item_length_unit_of_measure" => "",
            "item_display_height_unit_of_measure" => "",
            "item_display_width_unit_of_measure" => "",
            "item_display_length_unit_of_measure" => "",
            "website_shipping_weight" => "",
            "website_shipping_weight_unit_of_measure" => "",
            "item_length" => "",
            "item_width" => "",
            "item_height" => "",
            "item_dimensions_unit_of_measure" => "",
            "item_weight" => "",
            "item_weight_unit_of_measure" => "",
            "condition_type" => "",
            "condition_note" => "",
            "sale_price" => "",
            "sale_from_date" => "",
            "sale_end_date" => "",
            "max_order_quantity" => "",
            "list_price" => "",
            "map_price" => "",
            "item_package_quantity" => "",
            "offering_can_be_gift_messaged" => "",
            "offering_can_be_giftwrapped" => "",
            "missing_keyset_reason" => "",
            "number_of_items" => "",
            "offering_end_date" => "",
            "offering_start_date" => "",
            "product_tax_code" => "",
            "product_site_launch_date" => "",
            "merchant_release_date" => "",
            "fulfillment_latency" => "",
            "restock_date" => "",
            "max_aggregate_ship_quantity" => "",
            "is_discontinued_by_manufacturer"
        ];
        */


    }

    public function validate()
    {
        if (mb_strlen($this->sku) < 1 or strlen($this->sku) > 40) {
            $this->validation_errors['sku'] = 'Should be longer then 1 character and shorter then 40 characters';
        }

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

        $this->quantity = (int)$this->quantity;
        $this->product_id = (string)$this->product_id;

        $product_id_length = mb_strlen($this->product_id);

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

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}