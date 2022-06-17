<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\controller\ApiController;
use app\common\model\User;

class Index extends ApiController
{
    public function index()
    {
        $this->returnApiData('您好！');
    }

    public function login()
    {
        if ($this->userInfo && $this->userInfo->token_expire_time > time() && $this->userInfo->getData('status')) {
            $this->returnData['code'] = 1;
            $this->returnApiData('您已经登录过');
        }

        if ($this->request->isPost()) {
            if (!isset($this->params['username']) || trim($this->params['username']) === '') {
                $this->returnApiData('参数错误：缺少 username');
            }

            if (!isset($this->params['password']) || trim($this->params['password']) === '') {
                $this->returnApiData('参数错误：缺少 password');
            }

            $user = User::getByUsername($this->params['username']);
            if (!$user) {
                $this->returnApiData(lang('Account is incorrect'));
            }

            if (!$user->getData('status')) {
                $this->returnApiData(lang('Account is locked'));
            }

            $now = time();
            $user->token = createToken($user->password);
            $user->token_expire_time = $now + $this->token_expire_time;

            if (!password_verify($this->params['password'], $user->password)) {
                $this->returnApiData(lang('Password is incorrect'));
            }

            $user->last_login_time = $user->getData('login_time');
            $user->login_time = $now;
            $user->last_login_ip = $user->getData('login_ip');
            $user->login_ip = $this->request->ip();
            $user->save();

            $this->returnData['code'] = 1;
            $this->returnData['data']['url'] = $user->hidden(['password'])->toArray();
            $this->returnApiData(lang('logined'));
        }

        $this->returnApiData();
    }

    public function logout()
    {
        $this->userInfo->token = '';
        $this->userInfo->token_expire_time = 0;
        $this->userInfo->save();
        $this->returnData['code'] = 1;
        $this->returnApiData('退出成功');
    }
}
