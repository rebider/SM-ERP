<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategorysAmazon extends Model
{
    protected $table = 'category_amazon';
//    protected $table = 'categories_amazon';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','BrowseNodeID','parentID','categories_lv','parentBrowsePathByID','BrowseNodeName','BrowseNodeName_CN','created_at','updated_at'];

    public static function getCategoryStringInSort(array $categoryId)
    {
        $collection = self::whereIn('BrowseNodeID', $categoryId)
            ->select('BrowseNodeName_CN', 'BrowseNodeName', 'BrowseNodeID')->get()->toArray();
        $inSort = [];
        foreach ( $categoryId as $key => $val) {
            foreach ($collection as $keyInside => $valInSide) {
                if ($valInSide['BrowseNodeID'] != $val) {
                    continue;
                }
                $inSort[] = $valInSide['BrowseNodeName'];
            }
        }

        return implode(' > ', $inSort);
    }

    /**
     * @param array $option ['field'=>value]
     * @return array
     * Note: 根据条件获取亚马逊分类信息
     * Data: 2019/6/17 16:42
     * Author: zt7785
     */
    public static function getCategoryByOpt(array $option)
    {
        DB::connection()->enableQueryLog();
        $collection = self::select(['id','BrowseNodeID','parentID','categories_lv','parentBrowsePathByID','BrowseNodeName','BrowseNodeName_CN']);
        foreach ($option as $field => $value) {
            $collection->where($field,$value);
        }
        $result = $collection->orderBy('id')->groupBy('BrowseNodeID')->get();
        if ($result->isEmpty()) {
            return [];
        }
        return $result->toArray();
    }

    public static function getCategoryByParentId($parentId)
    {
        return self::where('parentID', $parentId)->get()->toArray();
    }

    /**
     * @param $nodeId
     * @return mixed
     * Note: 亚马逊节点校验
     * Data: 2019/6/17 19:34
     * Author: zt7785
     */
    public static function checkNode ($nodeId)
    {
        $respone ['exception_status'] = true;
        $respone ['exception_info'] = '';
        $respone ['data'] = [];
        $nodeInfo = self::where('BrowseNodeID',$nodeId)->first(['id','parentID','BrowseNodeID','parentBrowsePathByID','BrowseNodeName','BrowseNodeName_CN','categories_lv']);
        if (empty($nodeInfo)) {
            $respone ['exception_info'] = '亚马逊节点信息异常';
            return $respone;
        }
        if ($nodeInfo ['categories_lv'] == 1) {
            $respone ['exception_info'] = '请选择子节点';
            return $respone;
        }
        $childNode = self::where('parentID',$nodeId)->first(['id']);
        if ($childNode) {
            $respone ['exception_info'] = '请选择子节点';
            return $respone;
        }
        $respone ['exception_status'] = false;
        $respone ['data'] = $nodeInfo;
        return $respone;
    }
}
