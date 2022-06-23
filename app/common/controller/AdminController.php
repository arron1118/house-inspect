<?php
declare (strict_types = 1);

namespace app\common\controller;

use app\common\model\Admin;
use app\common\library\Attachment;
use app\common\model\Config as SiteConfig;
use think\Request;
use app\BaseController;
use app\common\middleware\Check;

class AdminController extends BaseController
{
    protected $middleware = [Check::class];

    protected $model = null;

    protected $userInfo = null;

    protected $userType = 'admin';

    protected $returnData = [];

    protected function initialize()
    {
        parent::initialize();

        $this->returnData = [
            'code' => 0,
            'msg' => lang('Unknown error'),
            'data' => []
        ];

        $token = $this->request->cookie('RB_TOKEN');
        if ($token) {
            $this->userInfo = Admin::where('token', $token)->find();
        }
        $this->view->assign('userInfo', $this->userInfo);

        $this->view->assign('siteConfig', SiteConfig::select());
    }

    public function error($msg = '')
    {
        if ($msg) {
            $this->returnData['msg'] = $msg;
        }

//        $this->returnData['data']['token'] = $this->request->buildToken();

        response($this->returnData, 200, [], 'json')->send();
        exit;
    }

    public function success($msg = '')
    {
        if ($msg) {
            $this->returnData['msg'] = $msg;
        } else {
            $this->returnData['msg'] = lang('Done');
        }

        response($this->returnData, 200, [], 'json')->send();
        exit;
    }

    public function upload()
    {
        $site_watermark_engine = SiteConfig::getByKeyword('site_watermark_engine');
        $upload = (new Attachment())->upload(request()->file('file'), 'attachment', (bool)(int) $site_watermark_engine->value, ['admin_id' => $this->userInfo->id]);

        if (!$upload) {
            $this->error('上传失败: 未找到文件');
        }

        $this->returnData['code'] = 1;
        $this->returnData['data']['savePath'] = $upload['savePath'];
        $this->success(lang('Done'));
    }
}
