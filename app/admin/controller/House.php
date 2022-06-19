<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;
use app\common\model\House as HouseModel;
use app\common\model\HouseRate;
use app\common\model\Area;

class House extends AdminController
{
    protected function initialize()
    {
        parent::initialize();

        $this->model = HouseModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->view->assign('areaList', Area::select());
        return $this->view->fetch();
    }

    public function getHouseList(Request $request)
    {
        if ($request->isAjax()) {
            $page = (int) $request->param('page', 1);
            $limit = (int) $request->param('limit', 10);
            $title = $request->param('title', '');
            $areaId = (int) $request->param('area_id', 0);
            $map = [];

            if ($title) {
                $map[] = ['title', 'like', '%' . $title . '%'];
            }

            if ($areaId) {
                $map[] = ['area_id', '=', $areaId];
            }

            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->with(['area'])
                ->hidden(['area'])
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            $this->success();
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
        $model = new $this->model;
        $this->view->assign([
            'area_id' => $this->request->param('area_id'),
            'designPaperList' => $model->getDesignPaperList(),
            'purposeList' => $model->getPurposeList(),
            'afterChangeList' => $model->getAfterChangeList(),
            'crackTypeList' => $model->getCrackTypeList(),
            'inclineOrDepositionTypeList' => $model->getInclineOrDepositionTypeList(),
            'examinatorTypeList' => $model->getExaminatorTypeList(),
        ]);
        return $this->view->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $params = $request->param();
            (new $this->model)->save($params);
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
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
        $model = new $this->model;
        $this->view->assign([
            'house' => $this->model::find($id),
            'designPaperList' => $model->getDesignPaperList(),
            'purposeList' => $model->getPurposeList(),
            'afterChangeList' => $model->getAfterChangeList(),
            'crackTypeList' => $model->getCrackTypeList(),
            'inclineOrDepositionTypeList' => $model->getInclineOrDepositionTypeList(),
            'examinatorTypeList' => $model->getExaminatorTypeList(),
        ]);
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
            $params = $request->param();
            $house = $this->model::find($id);
            $house->save($params);
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
            $house = $this->model::find($id);
            $house->delete();
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }
}
