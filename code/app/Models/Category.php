<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use http\Params;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    //
    protected $table = 'categories';

    public $timestamps = true;

    public $primaryKey = 'id';

    /**
     * @var 本地商品分类
     */
    const TYPE_LOCAL = 0 ;

    /**
     * @var 仓库分类
     */
    const TYPE_WAREHOUSE = 1 ;

    /**
     * @var 乐天平台分类
     */
    const TYPE_LETIAN = 2 ;

    /**
     * @var 亚马逊平台分类
     */
    const TYPE_AMAZON = 3 ;

    /**
     * @description 获取分类信息
     * @author zt7927
     * @date 2019/4/10 18:34
     * @param $id
     * @return bool|Model|null|static
     */
    public function getCategoriesById($id)
    {
        if (is_numeric($id) && $id > 0) {
            return self::where('id', $id)->first(['name']);
        }
        return false;
    }

    /**
     * @description 把返回的数据集转换成Tree
     * @author zt7927
     * @date 2019/4/11 9:34
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $child child标记字段
     * @return array
     */
    public function getTree($list, $root = 0, $pk='id', $pid = 'parent_id', $child = 'child')
    {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }

            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * @desc 获取所有本地的一级分类
     * @author zt6650
     * CreateTime: 2019-04-09 10:19
     * @param $type int
     * @return Category[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllFirstCategoryByType($type = self::TYPE_LOCAL,$user_id)
    {
        if ($type == self::TYPE_LOCAL) {
            return $this->where([
                'type'=>$type ,
                'user_id'=>$user_id,
                'parent_id'=>0
            ])->select('id' ,'name')->get() ;
        } else {
            return $this->where([
                'type'=>$type ,
                'parent_id'=>0
            ])->select('id' ,'name')->get() ;
        }

    }

    /**
     * @desc 获取指定ID下的所有子类分类
     * @author zt6650
     * CreateTime: 2019-04-09 10:19
     * @param $id
     * @return Category[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllChildrenById($id,$user_id)
    {
        //为什么要删user_id 条件?
        return $this->select('id' ,'name')->where('parent_id' ,$id)->where('user_id',$user_id)->where('type', self::TYPE_LOCAL)->get() ;
    }

    /**
     * @description 根据分类名称查询分类
     * @author zt7927
     * @date 2019/4/19 18:01
     * @param $categoryName
     * @return Model|null|static
     */
    public function getCategoryByName($categoryName,$user_id)
    {
        return self::where('name', $categoryName)->where('user_id',$user_id)->where('type', self::TYPE_LOCAL)->first();
    }

    /**
     * @description 更新分类名称
     * @author zt7927
     * @date 2019/4/19 18:03
     * @param $params
     * @return bool
     */
    public function updateArr($params,$user_id)
    {
        return self::where('id', $params['category_id'])->where('user_id',$user_id)->where('type', self::TYPE_LOCAL)->update([
            'name' => $params['category_name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @description 新增分类
     * @author zt7927
     * @date 2019/4/23 13:47
     * @param $params
     * @return bool
     */
    public function insertArr($params)
    {
        $insertArr['created_man']   = $params ['created_man'];
        $insertArr['user_id']       = $params ['user_id'];
        $insertArr['parent_id']     = $params ['parent_id'];
        $insertArr['name']          = $params ['category_name'];
        $insertArr['type']          = self::TYPE_LOCAL;
        $insertArr['created_at']    = $insertArr ['updated_at']  = date('Y-m-d H:i:s');
        return self::insertGetId($insertArr);
    }

    /**
     * @description 删除分类
     * @author zt7927
     * @date 2019/4/23 15:10
     * @param $id
     * @return int
     */
    public function delCategoryById($id,$user_id)
    {
        return self::where('user_id', $user_id)->where('id',$id)->delete();
    }

    /**
     * @description 根据父级id查找子类
     * @author zt7927
     * @date 2019/4/23 15:24
     * @param $id
     * @return Model|null|static
     */
    public function getChildrenById($id,$user_id)
    {
        return self::where('parent_id', $id)->where('user_id',$user_id)->first();
    }

    /**
     * @note
     * 获取仓库分类
     * @since: 2019/6/12
     * @author: zt7837
     * @return: string
     */
    public function getSelect($id1,$id2,$id3,$user_id)
    {
        $parent = self::where(['parent_id'=>0,'type'=>self::TYPE_WAREHOUSE])->select('name','id','category_id')->get();
        $ware_cat = [
            'cat1'=>'',
            'cat2'=>'',
            'cat3'=>''
        ];
//        dump($id1,$id2,$id3);
        if($parent) {
            $chiid = self::where(['parent_id'=>$id1,'type'=>self::TYPE_WAREHOUSE])->select('name','parent_id','id','category_id')->get();
            $son = self::where(['parent_id'=>$id2,'type'=>self::TYPE_WAREHOUSE])->select('name','id','category_id')->get();
            $cat1 = '<select name="category1"  lay-filter="cat1" class="cat1"><option  value="">请选择</option>';
            $cat2 = '<select name="category2"  lay-filter="cat2" class="cat2"><option  value="">请选择</option>';
            $cat3 = '<select name="category3"  lay-filter="cat3" class="cat3"><option  value="">请选择</option>';

            foreach($parent as $k => $v) {
                $cat1 .= '<option value="'.$v->category_id.'"';
                if($v->category_id == $id1) {
                    $cat1 .= 'selected';
                }
                $cat1 .= '>'.$v->name.'</option>';
            }

            foreach($chiid as $key => $val) {
                $cat2 .= '<option value="'.$val->category_id.'"';
                if($val->category_id == $id2) {
                    $cat2 .= ' selected ';
                }
                $cat2 .= '>'.$val->name.'</option>';
            }

            foreach($son as $ke => $va) {
                $cat3 .= '<option value="'.$va->category_id.'"';
                if($va->category_id == $id3) {
                    $cat3 .= ' selected ';
                }
                $cat3 .= '>'.$va->name.'</option>';
            }

            $cat2 .= '</select>';
            $cat1 .= '</select>';
            $cat3 .= '</select>';
            $ware_cat['cat1'] = $cat1;
            $ware_cat['cat2'] = $cat2;
            $ware_cat['cat3'] = $cat3;
        }

        return $ware_cat;
    }
}
