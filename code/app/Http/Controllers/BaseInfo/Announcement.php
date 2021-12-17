<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/3/29
     * Time: 10:34
     */

    namespace App\Http\Controllers\BaseInfo;

    use App\Auth\Common\CurrentUser;
    use App\Auth\Models\Menus;
    use App\Exceptions\DataNotFoundException;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\AnnouncementRequest;
    use App\Models\BaseModel;
    use App\Models\SettingNotices;
    use Illuminate\Http\Request;

    class Announcement extends Controller
    {
        public $menusModel = [];

        public function __construct()
        {
            $this->menusModel = new Menus();
        }

        /**
         * @return array
         * Note: 公告首页
         * Data: 2019/03/39 10:35
         * Author: zt8067
         */
        public function announcementIndex()
        {

            $responseData ['shortcutMenus'] = $this->menusModel->getShortcutMenu(BaseModel::RULES_ORDER_MENUS_ID);
            return view('BaseInfo.announcement.index', $responseData);
        }
        /**
         * @return array
         * Note: 公告创建页
         * Data: 2019/03/39 10:35
         * Author: zt8067
         */
        public function editIndex(Request $request)
        {
            $id = $request->get('id');
            $SettingNoticesModel = SettingNotices::find($id);
            if (empty($SettingNoticesModel)){
                $data['SettingNotices'] = array();
            }else{
                $data['SettingNotices'] = $SettingNoticesModel->toArray();
            }
            return view('BaseInfo.announcement.create',$data);
        }

        /**
         * @return array
         * Note: 公告创建
         * Data: 2019/03/39 10:35
         * Author: zt8067
         */
        public function createOrUpdate(AnnouncementRequest $request)
        {
            $results = ['code' => -1, 'msg' => ''];
            $params = $request->all();
            $currentUser = CurrentUser::getCurrentUser();
            $SettingNoticesModel = new SettingNotices;
            if (empty($params['id'])) {
                $SettingNoticesModel->created_man = $currentUser->userId;
                $SettingNoticesModel->title = $params['title'];
                $SettingNoticesModel->content = htmlspecialchars($params['content']);
                $SettingNoticesModel->status = $params['status'];
                $SettingNoticesModel->important = $params['important'];
                $SettingNoticesModel->save() && $results = ['code' => 1, 'msg' => '添加成功'];
            } else {
                $SettingNoticesModel->where('id', $params['id'])->update([
                    'id'          => $params['id'],
                    'created_man' => $currentUser->userId,
                    'title'       => $params['title'],
                    'content'     => htmlspecialchars($params['content']),
                    'status'      => $params['status'],
                    'important'   => $params['important'],
                ]) && $results = ['code' => 1, 'msg' => '更新成功'];
            }
            return parent::layResponseData($results);
        }

        /**
         * @return array
         * Note: 公告列表
         * Data: 2019/03/39 15:35
         * Author: zt8067
         */
        public function announcementLists(Request $request)
        {
            $limit = $request->get('limit', 20);
            $SettingNoticesModel = SettingNotices::with('Users');
            if (empty($SettingNoticesModel)) {
                throw new DataNotFoundException();
            }
            $lists = $SettingNoticesModel->orderByDesc('id')->paginate($limit)->toArray();
            if ($lists) {
                $results = [
                    'msg'   => 'Success',
                    'data'  => $lists['data'],
                    'count' => $lists['total'],
                ];
            } else {
                $results = [
                    'code' => '999',
                    'msg'  => 'Error',
                ];
            }
            return parent::layResponseData($results);
        }

        /**
         * @return array
         * Note: 公告列表
         * Data: 2019/03/39 15:35
         * Author: zt8067
         */
        public function delete(Request $request){
            $msg = ['code' => 0, 'msg' => ''];
            $res = SettingNotices::destroy($request->id);
            if ($res) {
                $msg['code'] = 1;
                $msg['msg'] = "删除成功！";
            } else {
                $msg['code'] = -1;
                $msg['msg'] = "删除失败！";
            }
            return parent::layResponseData($msg);

        }

    }