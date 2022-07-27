<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\model\Area;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\District as DistrictModel;

class District extends AdminController
{

    protected function initialize()
    {
        parent::initialize();

        $this->model = DistrictModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->view->assign([
            'areaList' => Area::field('id, title')->order('id desc')->select(),
        ]);

        return $this->view->fetch();
    }

    public function getDistrictList(Request $request)
    {
        if ($request->isAjax()) {
            $page = (int) $request->param('page', 1);
            $limit = (int) $request->param('limit', 10);
            $area_id = (int) $request->param('area_id', 0);
            $title = $request->param('title', '');
            $map = [];

            if ($title) {
                $map[] = ['title', 'like', '%' . $title . '%'];
            }

            if ($area_id) {
                $map[] = ['area_id', '=', $area_id];
            }

            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::with(['area'])->hidden(['area'])->withCount(['house'])
                ->where($map)
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['code'] = 1;
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
