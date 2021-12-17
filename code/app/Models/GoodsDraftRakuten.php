<?php
/**
 * 乐天草稿箱模型
 * Auth: zt12779
 * created at: 2019/06/03
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GoodsDraftRakuten extends Model
{
    const SYNCHRONIZE_STATUS_NORMAL = 1;//未同步 草稿
    const SYNCHRONIZE_STATUS_SUCCESS = 2;//同步成功
    const SYNCHRONIZE_STATUS_ERROR = 3;//同步失败

    protected $table = 'goods_draft_rakuten';
    public $timestamps = true;
    public $primaryKey = 'id';

    public function Shops()
    {
        return $this->hasOne(SettingShops::class, 'id', 'belongs_shop');
    }

    public function Procurement()
    {
        return $this->hasOne(Procurements::class, 'goods_id', 'goods_id');
    }

    public function pictures()
    {
        return $this->hasMany(GoodsDraftRakutenPics::class, 'goods_id', 'id');
    }




    /**
     * 获取草稿箱数据
     * Auth: zt12779
     * create at:2019/06/03
     * @param $params
     * @return mixed
     */
    public static function getList($params, $user_id)
    {
        $page = (isset($params['page']) && !empty((int)$params['page'])) ? $params['page'] : 1;
        $limit = (isset($params['limit']) && !empty((int)$params['limit'])) ? $params['limit'] : 20;
        $collection = self::with('Shops')->with('Procurement');
        if (isset($params['source_shop']) && !empty($params['source_shop'])) {
            $collection->whereIn('belongs_shop', $params['source_shop']);
        }
        if (isset($params['local_sku']) && !empty($params['local_sku'])) {
            $collection->where('goods_draft_rakuten.local_sku', 'like', "%{$params['local_sku']}%");
        }
        if (isset($params['goods_name']) && !empty($params['goods_name'])) {
            $collection->where('goods_draft_rakuten.title', 'like', "%{$params['goods_name']}%");
        }

        $collection->where('user_id', $user_id);

        $selection = ['goods_draft_rakuten.local_sku', 'goods_draft_rakuten.title', 'goods_id',
            'goods_width', 'goods_height', 'goods_length', 'goods_weight','goods_draft_rakuten.img_url',
            'goods_draft_rakuten.sale_price', 'goods_draft_rakuten.currency_code', 'goods_draft_rakuten.belongs_shop',
            'goods_draft_rakuten.synchronize_status', 'goods_draft_rakuten.id'];
        if (isset($params['synchronizeType']) && $params['synchronizeType'] == 3) {
            $collection->where('goods_draft_rakuten.synchronize_status', $params['synchronizeType']);
            $selection[] = 'goods_draft_rakuten.synchronize_info';
        } else {
            $collection->whereNotIn('goods_draft_rakuten.synchronize_status',  [2, 3]);
        }

        $collection->select($selection)->orderByDesc('goods_draft_rakuten.created_at');
        return $collection->paginate($limit, ['*'], 'page', $page)->toArray();
    }

    public static function getOne($id, $user_id)
    {
        $whereMap = ['user_id' => $user_id, 'id' => $id];
        $collection = self::with('pictures');
        return $collection->where($whereMap)->first();
    }

    public function updatedById($data,$id,$user_id,$sku)
    {
        if(!$id) {
            $data['created_at'] = date('Y-m-d H:i:s') ;
            $data['updated_at'] = date('Y-m-d H:i:s') ;
            $data['local_sku'] = $sku;

            $localProduct = Goods::where('sku', $sku)->where('user_id', $user_id)->first();
//            $procurement = Procurements::where('goods_id', $localProduct['id'])->first();
            $data['goods_id'] = $localProduct['id'];
            $data['goods_attribute_id'] = $localProduct['goods_attribute_id'];

            return $this->insertGetId($data);
        }

        $data['updated_at'] = date('Y-m-d H:i:s') ;
        return $this->where('id' ,$id)->where('user_id',$user_id)->update($data) ;
    }

    public static function getLotteCat($rakuten_category_id) {
        if($rakuten_category_id) {
            $arr = explode(',',$rakuten_category_id);
            $first_lv = CategorysRakuten::where('categories_lv',1)->get();
            if(!$first_lv->isEmpty()) {
                $first_lv_arr = $first_lv->toArray();
            }

            if(!empty($arr[0])) {
                $cat1 = CategorysRakuten::where(['genreId'=>$arr[0]])->select('genreName')->first();
                $second_lv = CategorysRakuten::where('parentID',$arr[0])->get();
                if(!$second_lv->isEmpty()) {
                    $second_lv_arr = $second_lv->toArray();
                }
            }

            if(!empty($arr[1])) {
                $cat2 = CategorysRakuten::where(['genreId'=>$arr[1]])->select('genreName')->first();
                $third_lv = CategorysRakuten::where('parentID',$arr[1])->get();
                if(!$third_lv->isEmpty()) {
                    $third_lv_arr = $third_lv->toArray();
                }
            }

            if(!empty($arr[2])) {
                $cat3 = CategorysRakuten::where(['genreId'=>$arr[2]])->select('genreName')->first();
                $foure_lv = CategorysRakuten::where('parentID',$arr[2])->get();
                if(!$foure_lv->isEmpty()) {
                    $foure_lv_arr = $foure_lv->toArray();
                }
            }

            if(!empty($arr[3])) {
                $cat4 = CategorysRakuten::where(['genreId'=>$arr[3]])->select('genreName')->first();
            }

            $str = (isset($cat1) ? $cat1->genreName.' > ' : '')
                 . (isset($cat2) ? $cat2->genreName. ' > ' : '')
                 . (isset($cat3) ? $cat3->genreName.' > ' : '')
                 . (isset($cat4) ? $cat4->genreName : '');
        }

        $category = [
            'str' => isset($str) ? $str : '',
            'first' => isset($first_lv_arr) ? $first_lv_arr : '',
            'second' => isset($second_lv_arr) ? $second_lv_arr : '',
            'third' => isset($third_lv_arr) ? $third_lv_arr : '',
            'four' => isset($foure_lv_arr) ? $foure_lv_arr : ''
        ];

        return $category;
    }

    public function deleteById($user_id,$id,$pic) {
        $pic->where(['goods_id'=>$id,'user_id'=>$user_id])->delete();
        $rakuten = $this->where(['id'=>$id,'user_id'=>$user_id])->first();
        if(!$rakuten) {
            abort(404);
        }

        return $rakuten->delete();
    }
}
