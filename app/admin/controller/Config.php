<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\library\Attachment;
use app\common\model\Config as SiteConfig;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\Config as ConfigModel;

class Config extends AdminController
{
    protected function initialize()
    {
        parent::initialize();

        $this->model = ConfigModel::class;
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
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
        if ($request->isPost()) {
            $params = $request->param('param');
            foreach ($params as $key => $value) {
                $data = ['value' => $value['value']];
                if ($value['type'] === 'select') {
                    $data = ['content' => $value['value']];
                }
                $this->model::where('keyword', $key)->update($data);
            }
            $this->returnData['code'] = 1;
            $this->returnData['data'] = $params;
            $this->returnData['param'] = $request->param();
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
        //
    }

    public function upload()
    {
        $upload = (new Attachment())->upload(request()->file('file'));

        if (!$upload) {
            $this->error('上传失败: 未找到文件');
        }

        $this->returnData['code'] = 1;
        $this->returnData['data']['savePath'] = $upload['savePath'];
        $this->success(lang('Done'));
    }
}
