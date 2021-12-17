<?php
namespace App\Auth\Models;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Models\RulesOrderTrouble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

/**
 * 菜单模型
 */
class Menus extends Model
{
    //TODO
    //2019年3月7日14:10:45 需要重写
    //指定表名
    protected $table = 'menu';

    //指定id
    protected $primaryKey = 'id';

    //自动维护时间戳
    public $timestamps = false;

    //规则
    protected $rules = array(
        'name' => 'required',
        'parent_id' => 'required|integer',
    );

    //提示
    protected $message = array(
        "required" => ":attribute 不能为空",
        "integer" => "请选择 :attribute"
    );

    //字段名称
    protected $attribute = array(
        'name' => '菜单名称',
        'parent_id' => '上级菜单'
    );

    public $fillable = ['id','created_man','name','parent_id','url','status','sort','created_at','updated_at'];

    public static $cache = null;


    /**
     * @var 模型前缀
     */
    public static $cachePrefix = 'Menu_';
    /**
     * 添加菜单
     */
    public function insert($redis = null)
    {
        $validator = Validator::make(
            Input::all(),
            $this->rules,
            $this->message,
            $this->attribute
        );
        if ($validator->fails()) {
            $warnings = $validator->messages();
            return response()->json($warnings);
        } else {
            $this->name = Input::get('name');
            $this->parent_id = Input::get('parent_id','0');
            $this->url = Input::get('url');
            $this->sort = Input::get('sort','999');
            $this->status = 1;
            $this->save();
            if ($redis) {
                $redis->setMenu($this->id.'_1_'.$this->parent_id,$this->toArray());
            }
            return response()->json(['code'=>'ok','info'=>'添加成功']);
        }
    }

    /**
     * 获取菜单总条数
     */
    public function getCount()
    {
        return $this->count();
    }

    /**
     * 分页获菜单信息
     */
    public function getAllByMap()
    {
        //todo
        //后面加用户菜单限制
        $list = $this->orderBy('id', 'asc')
                     ->get(['id','name','parent_id','url','status'])
                    ->toArray();
        $list = $this->list_to_tree($list,'id','parent_id','_child',0);
        return $list;
    }

    /**
     * @return array
     * Note: 主账户给子账户配置菜单列表
     * Data: 2019/3/28 11:26
     * Author: zt7785
     */
    public function getSecondByMap()
    {
        //后面加用户菜单限制
        $list =    $this->where('parent_id','>',0)
                        ->where('name','!=','子账号')
                        ->where('status','1')
                        ->get(['id','name','parent_id','url','status'])
                        ->toArray();
        $list = $this->list_to_tree($list,'id','parent_id','_child',0);
        return $list;
    }
    /**
     * 获取上级菜单名称
     */
    private function getFieldVal($parent_id)
    {
        $val = $this->where('id',$parent_id)->pluck('name');
        return empty($val[0]) ? '无':$val[0];
    }

    /**
     * 获取菜单列表
     */
    public function getToTree($redis= null)
    {
        if ($redis) {
            $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.'*_1_*');
            foreach ($menuDatas as $val) {
                $menuData  = $redis->assignKeys($redis->dbIndex_users,$val,'hGetAll',2);
                if ($menuData) {
                    $list[] = $menuData;
                }
            }
        } else {
            $list = $this->where('status',1)->get();
            if (!$list->isEmpty()) {
                $list = $list->toArray();
            }
        }
        $list = $this->list_to_tree($list,'id','parent_id','_child','0');
        $tmp_arr = array(
            0 => ['id'=>0,'parent_id'=> 0,'name'=>'无上级菜单']
        );
        return array_merge($tmp_arr,$list);
    }

    /**
     * 获取菜单单列
     */
    public function getFind($id,$redis=null)
    {
        if ($redis) {
            $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.$id.'_*_*');
            if ($menuDatas) {
               return $redis->array_to_object($redis->assignKeys($redis->dbIndex_users,$menuDatas[0],'hGetAll',2));
            } else {
                return false; //id不存在
            }
        } else {
            if ($this->isMenu('id',$id)){
                return $this->where('id',$id)->first();
            }else{
                return false; //id不存在
            }
        }
    }



    /**
     * 编辑菜单
     */
    public function edit($redis = null)
    {
        $id = Input::get('id');
        if ($this->isMenu('id',$id)){
            $data = [];
            $data['name'] = Input::get('name');
            $data['parent_id'] = Input::get('parent_id','0');
            $data['url'] = Input::get('url');
            $data['sort'] = Input::get('sort','999');
            $updateRe = $this->where('id',$id)->update($data);
            if ($updateRe) {
                if ($redis) {
                    $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.$id.'_*_*');
                    if ($menuDatas) {
                        $menuKey = str_replace(self::$cachePrefix,'',$menuDatas[0]);
                        foreach ($data as $k => $v) {
                            $redis->setMenuField($menuKey,$k,$v);
                        }
                    }
                }
            }
            return true;
        }else{
            return false; //id不存在
        }
    }

    /**
     * 删除菜单
     */
    public function del($id,$redis = null)
    {
        if ($this->isMenu('id',$id)){
            $updateRe =  $this->where('id',$id)->update(['status' => 0]);
            if ($updateRe) {
                if ($redis) {
                    $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.$id.'_*_*');
                    if ($menuDatas) {
                        $menuKey = str_replace(self::$cachePrefix,'',$menuDatas[0]);
                        $menuData = $redis->getMenu($menuKey);
                        if (!empty($menuData['status'])) {
                            $redis->setMenuField($menuKey,'status',0);
                            $redis->renameKeys($redis->dbIndex_users,$menuDatas[0],self::$cachePrefix.$id.'_0_'.$menuData['parent_id']);
                        }
                    }
                }
                return true;
            }
            return false;
        }else{
            return false; //id不存在
        }
    }

    /**
     * 恢复菜单
     */
    public function undel($id,$redis = null)
    {
        if ($this->isMenu('id',$id)){
            $updateRe =  $this->where('id',$id)->update(['status' => 1]);
            if ($updateRe) {
                if ($redis) {
                    $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.$id.'_*_*');
                    if ($menuDatas) {
                        $menuKey = str_replace(self::$cachePrefix,'',$menuDatas[0]);
                        $menuData = $redis->getMenu($menuKey);
                        if (empty($menuData['status'])) {
                            $redis->setMenuField($menuKey,'status',1);
                            $redis->renameKeys($redis->dbIndex_users,$menuDatas[0],self::$cachePrefix.$id.'_1_'.$menuData['parent_id']);
                        }
                    }
                }
                return true;
            }
            return false;
        }else{
            return false; //id不存在
        }
    }

    /**
     * 根据条件判断菜单是否存在
     */
    private function isMenu($key,$val)
    {
        $is_data = $this->where($key,$val)->first();
        if (empty($is_data)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent 标记字段
     * @param string $level level 标记字段
     * @return array
     */
    private function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0 ,$is_exclude = false)
    {
        $tree = array();
        if(is_array($list)) {
            //创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            //处理排序问题
            array_multisort(array_column($list, 'id'), SORT_ASC, $list);
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parent_id =  $data[$pid];
                if ($root == $parent_id) {
                    $tree[] =& $list[$key];
                }else{
                    if ($is_exclude) {
                        if (isset($refer[$parent_id]) && in_array($parent_id,array_column($tree,'id'))) {
                            $parent =& $refer[$parent_id];
                            $parent[$child][] =& $list[$key];
                        }
                    } else {
                        if (isset($refer[$parent_id])) {
                            $parent =& $refer[$parent_id];
                            $parent[$child][] =& $list[$key];
                        } else {
                            $tree[] =& $list[$key];
                        }
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 根据菜单ID获取菜单信息
     */
    public  function getMapMenuList($menuArrId,$userType,$redis= null)
    {
        if (empty($menuArrId)){
            return false;
        }else{
            $list = [];
            if ($redis) {
                sort($menuArrId);
                foreach ($menuArrId  as $v) {
                    $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.$v.'_1_*');
                    foreach ($menuDatas as $val) {
                        $menuData = $redis->assignKeys($redis->dbIndex_users,$val,'hGetAll',2);
                        if ($menuData) {
                            $list[] = $menuData;
                        }
                    }
                }
                $list = array_filter($list);
                array_multisort(array_column($list, 'id'), SORT_ASC, $list);
            } else {
                if ($userType == AccountType::CHILDREN) {
                    //可能有3级菜单没有2级菜单
                    $list = $this->where('status',1)->where('name','!=','子账号')->where(function($query) use($menuArrId) {
                        $query->whereIn('id',$menuArrId)->where('parent_id',0);
                        $query->orWhereIn('parent_id',$menuArrId);
                        $query->orWhereIn('id',$menuArrId);
                    })->orderBy('sort','asc')->orderBy('id', 'asc')->get();
                } else {
                    $list = $this->where('status',1)->whereIn('id',$menuArrId)->orderBy('sort','asc')->orderBy('id', 'asc')->get();
                }
                if (!$list->isEmpty()) {
                    $list = $list->toArray();
                }
            }
            $lists = $this->list_to_tree($list,'id','parent_id','_child','0',false);
            return ['menusNav'=>$lists,'permissions'=>$list];
        }
    }

    /**
     * 根据url获取菜单id
     */
    public function getConditionMenuId($url,$redis = null)
    {
        if ($redis) {
            $menuKeys = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.'*_1_*');
            $menuDatas = [];
            foreach ($menuKeys as $k=>$v) {
                $menuDatas[] = $redis->assignKeys($redis->dbIndex_users,$v,'hGetAll',2);
            }
            $trueKey = array_search($url,array_column($menuDatas,'url'));
            $field[0] = '';
            if (!is_bool($trueKey)) {
                $field[0] = $menuDatas[$trueKey]['id'];
            }
        } else {
            $field = $this->where('url',$url)->where('status',1)->pluck('id');
        }
        return empty($field[0]) ? '':$field[0];
    }

    /**
     * 根据角色权限获取url
     */
    public function getConditionMenuUrl($menuArrId)
    {
        $url = $this->whereIn('id',$menuArrId)->where('status',1)->pluck('url')->toArray();
        return empty($url) ? '':$url;
    }

    /**
     * 根据条件获取账户拥有权限
     * @param array $menuArrId 菜单id
     * @param int $step 菜单层级
     * @return mixed
     */
    public function getUsePower($menuArrId, $step = 1,$redis = null)
    {
        //获取一级菜单拥有权限
        if ($step == 1){
            if ($redis) {
                $powerId = [];
                foreach ($menuArrId as $v) {
                    $menuData = $redis->getMenu($v.'_1_0');
                    if ($menuData) {
                        $powerId[] = $menuData['id'];
                    }
                }
            } else {
                $powerId = $this->where('parent_id',0)->where('status',1)->whereIn('id',$menuArrId)->pluck('id');
                if (!$powerId->isEmpty()) {
                    $powerId = $powerId->toArray();
                } else {
                    $powerId = [] ;
                }
            }
        }
        //获取二级菜单拥有权限及子模块权限
        if ($step == 2 || $step == 3){
            if ($redis) {
                $powerId = [];
                foreach ($menuArrId as $v) {
                    $menuDatas = $redis->searchKeys($redis->dbIndex_users,self::$cachePrefix.'*_1_'.$v);
                    foreach ($menuDatas as $val) {
                        $menuData = $redis->assignKeys($redis->dbIndex_users,$val,'hGet',2,'id');
                        if ($menuData) {
                            $powerId[] = $menuData;
                        }
                    }
                }
            } else {
                $powerId = $this->whereIn('parent_id',$menuArrId)->where('status',1)->pluck('id');
                if(!$powerId->isEmpty()) {
                    $powerId = $powerId->toArray();
                } else {
                    $powerId = [] ;
                }
            }
        }
        return empty($powerId) ? [] :$powerId;
    }

    /**
     * Note  菜单数据刷新
     * User: zt7387
     * Date: 2018/11/8
     * Time: 16:27
     */
    public static function setCacheData($cache = null){
        if(is_null($cache)){
            $redis = new Redis();
            if(empty($redis->exception_mes)) {
                $redisStatus = is_string($redis->checkCacheService()) ? false : true;
            } else {
                $redisStatus = false;
            }
            self::$cache = $redisStatus ? $redis : null;
            $cache =   self::$cache;
        }
        if (empty($cache)) {
            return ;
        }
            self::chunk(10,function($values) use($cache){
                if(!$values->isEmpty()){
                    foreach($values->toArray() as $value){
                        $cache->setMenu($value['id'].'_'.$value['status'].'_'.$value['parent_id'],$value);
                    }
                }
            });
    }

    /**
     * @param $type
     * Note: 快捷菜单
     * Data: 2019/3/12 15:58
     * Author: zt7785
     */
    public  function  getShortcutMenu ($first_id) {
        $collection = self::select(['id','name','parent_id','url','status']);
        if ($first_id == Users::BASE_SETTING_MENUS_ID) {
            //子账号
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType == AccountType::CHILDREN) {
                $collection->where('name','!=','子账号');
            }
        }
        if ($first_id == RulesOrderTrouble::RULES_ORDER_MENUS_ID) {
            //子账号
            $currentUser = CurrentUser::getCurrentUser();
            if ($currentUser->userAccountType != AccountType::ADMIN) {
                //公告管理屏蔽
                $collection->where('name','!=','公告管理');
            }
        }
        $menus = $collection->get()->toArray();
        $menuList = $this->list_to_tree($menus,'id','parent_id','_child',$first_id,true);
        return $menuList;
    }

}