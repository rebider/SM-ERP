<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorysRakuten extends Model
{
    protected $table = 'category_genreid_rakuten';
//    protected $table = 'categories_amazon';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','genreId','parentID','categories_lv','parentBrowsePathByID','genreName','genreName_CN','created_at','updated_at'];

    public static function getCategoryStringInSort($categoryId)
    {
//        $parentNode = self::where('genreId', $categoryId)->first();
        $parentNode = explode(',', $categoryId);
        $collection = self::whereIn('genreId', $parentNode)
            ->select('genreName', 'genreName_CN', 'genreId')->get()->toArray();
        $inSort = [];
        foreach ( $parentNode as $key => $val) {
            foreach ($collection as $keyInside => $valInSide) {
                if ($valInSide['genreId'] != $val) {
                    continue;
                }
                $inSort[] = $valInSide['genreName'];
            }
        }

        return implode(' > ', $inSort);
    }

    public static function getCategoryByParentId($parentId)
    {
        return self::where('parentID', $parentId)->get()->toArray();
    }
}
