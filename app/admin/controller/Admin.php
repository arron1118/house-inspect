<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;

class Admin extends AdminController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
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
        //
    }

    public function profile()
    {
        return $this->view->fetch('user/profile');
    }

    public function resetPassword()
    {
        if ($this->request->isPost()) {
            $old_password = trim($this->request->param('old_password'));
            $new_password = trim($this->request->param('new_password'));
            $confirm_password = trim($this->request->param('confirm_password'));
            if (empty($old_password)) {
                $this->error(lang('Please enter your old password'));
            }
            if (empty($new_password)) {
                $this->error(lang('Please enter a new password'));
            }
            if (empty($confirm_password)) {
                $this->error(lang('Please enter a confirmation password'));
            }
            if (!password_verify($old_password, $this->userInfo->password)) {
                $this->error(lang('The old password entered is incorrect'));
            }
            if ($new_password !== $confirm_password) {
                $this->error(lang('The confirmation password entered is incorrect'));
            }
            $this->userInfo->password = password_hash($confirm_password, PASSWORD_BCRYPT);
            if ($this->userInfo->save()) {
                $this->returnData['msg'] = lang('Password modification successful, please log in again');
                $this->returnData['code'] = 1;
            }

            $this->success();
        }

        return $this->view->fetch('user/reset_password');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
