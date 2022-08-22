<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\model\User as UserModel;
use think\Request;
use app\common\controller\AdminController;

class User extends AdminController
{
    protected function initialize()
    {
        parent::initialize();

        $this->model = UserModel::class;
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->view->fetch();
    }

    public function getUserList()
    {
        if ($this->request->isAjax()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $username = $this->request->param('username', '');
            $map = [];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            $this->returnData['code'] = 1;
            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->withCount(['house'])
                ->order('id desc, login_time desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            $this->success(lang('Done'));
        }

        $this->error();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return $this->view->fetch('user/add');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        if ($this->request->isPost()) {
            $params = $request->param();
            $params['username'] = trim($params['username']);
            $params['password'] = password_hash(trim($params['password']), PASSWORD_BCRYPT);

            if ($this->model::getByUsername($params['username'])) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if (!empty($params['phone']) && $this->model::getByPhone($params['phone'])) {
                $this->error('手机号已经存在');
            }

            $userModel = new $this->model();

            if ($userModel->save($params)) {
                $this->returnData['msg'] = '开通成功';
                $this->returnData['code'] = 1;
            } else {
                $this->returnData['msg'] = '开通失败';
            }

            $this->success();
        }

        $this->error();
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $this->view->assign('user', $this->model::find($id));
        return $this->view->fetch();
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->isPost()) {
            $params = $request->only(['username', 'password', 'phone', 'status']);
            $area = $this->model::find($id);
            if (isset($params['password']) && $area->password !== $params['password']) {
                $params['password'] = password_hash(trim($params['password']), PASSWORD_BCRYPT);
            }
            $area->save($params);
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if ($this->request->isPost()) {
            $user = $this->model::find($id);
            $user->delete();
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }
}
