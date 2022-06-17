<?php

namespace app\common\middleware;

use app\common\model\Admin;

class Check
{
    protected $noNeedLogin = ['login'];

    protected $module = null;

    protected $model = null;

    protected $token = null;

    public function handle($request, \Closure $next)
    {
        $this->module = app('http')->getName();

        if ($this->module === 'admin') {
            $this->model = Admin::class;
            $this->token = $request->cookie('RB_TOKEN');
        }

        if (!in_array($request->action(), $this->noNeedLogin, true) && !$this->checkToken()) {
            if ($request->isAjax()) {
                return json(['url' => (string) url('/index/login', [], true, true), 'code' => 5003]);
            }

            return redirect((string) url('/index/login'));
        }

        return $next($request);
    }

    protected function checkToken()
    {
        if ($this->token) {
            $userInfo = $this->model::getByToken($this->token);

            return !(!$userInfo || !$userInfo->getData('status') || $userInfo->token_expire_time < time());
        }

        return false;
    }
}
