<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;
use app\common\model\Investigation as InvestigationModel;

class HouseRate extends AdminController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = InvestigationModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $house_id = $this->request->param('house_id');
        $investigation_times = $this->request->param('investigation_times');

        $this->view->assign([
            'investigation_times' => $investigation_times,
            'house_id' => $house_id,
            'typeList' => (new $this->model)->getTypeList()
        ]);
        return $this->view->fetch();
    }

    public function getInvestigationList()
    {
        if ($this->request->isAjax()) {
            $type = $this->request->param('type');
            $house_id = $this->request->param('house_id');
            $investigation_times = $this->request->param('investigation_times');
            $map = [
                ['investigation_times', '=', $investigation_times],
                ['house_id', '=', $house_id],
                ['type', '=', $type],
            ];
            $list = $this->model::where($map)->select();
            $this->returnData['data'] = $list;
            $this->returnData['code'] = 1;
            $this->success('ppp');
        }
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
    public function update(Request $request, $id)
    {
        //
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
