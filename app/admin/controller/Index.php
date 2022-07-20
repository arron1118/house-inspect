<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\exception\ValidateException;
use think\Request;
use app\common\model\Admin;

class Index extends AdminController
{
    public function index()
    {
        return $this->view->fetch();
    }

    protected function addAdmin()
    {
        $admin = Admin::select()->toArray();
        if (empty($admin)) {
            $params = [
                'username' => 'admin',
                'password' => password_hash('123456', PASSWORD_BCRYPT),
            ];
            (new Admin())->save($params);
        }
    }

    public function createAdmin($from, $to)
    {
        for ($i = $from; $i <= $to; $i ++) {
            $params = [
                'username' => 'admin' . $i,
                'password' => password_hash('123456', PASSWORD_BCRYPT),
            ];
            (new Admin())->save($params);
        }
    }

    public function login(Request $request)
    {
        $this->addAdmin();

        if ($this->userInfo && $this->userInfo->token_expire_time > time() && $this->userInfo->getData('status')) {
            return redirect((string) url('/index'));
        }

        if ($request->isPost()) {
//            if (!$request->checkToken()) {
//                throw new ValidateException(lang('Invalid token'));
//            }
            $params = $request->param();

            $user = Admin::getByUsername($params['username']);
            if (!$user) {
                $this->error(lang('Account is incorrect'));
            }

            $now = time();
            $user->token = createToken($user->password);
            $user->token_expire_time = $now + $this->token_expire_time;

            if (!$user->getData('status')) {
                $this->error(lang('Account is locked'));
            }

            if (!password_verify($params['password'], $user->password)) {
                $this->error(lang('Password is incorrect'));
            }

            $user->last_login_time = $user->getData('login_time');
            $user->login_time = $now;
            $user->last_login_ip = $user->getData('login_ip');
            $user->login_ip = $request->ip();
            $user->save();

            cookie('RB_TOKEN', $user->token, $this->token_expire_time);

            $this->returnData['code'] = 1;
            $this->returnData['data']['url'] = (string) url('/index');
            $this->success(lang('Logined'));
        }

        return $this->view->fetch();
    }

    public function logout()
    {
        cookie('RB_TOKEN', null);
        $this->userInfo->token = '';
        $this->userInfo->token_expire_time = 0;
        $this->userInfo->save();

        return redirect((string) url('/index/login'));
    }
}
