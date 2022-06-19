<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;
use app\common\model\HouseRate as HouseRateModel;

class HouseRate extends AdminController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = HouseRateModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $house_id = (int) $this->request->param('house_id');

        if ($house_id <= 0) {
            $this->error('请提供house_id');
        }

        $rate = $this->model::getByHouseId($house_id);
        if (!$rate) {
            $rate = $this->model::create(['house_id' => $house_id]);
        }

        $model = new $this->model;
        $this->view->assign([
            'rate' => $rate,
            'placeRateList' => $model->getPlaceRateList(),
            'foundationRateList'=> $model->getFoundationRateList(),
            'mainRateList' => $model->getMainRateList(),
            'houseSafetyRateList' => $model->getHouseSafetyRateList()
        ]);
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
        if ($request->isAjax()) {
            $this->model::save($request->param());
            $this->success(lang('Done'));
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
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
        if ($request->isAjax()) {
            $rate = $this->model::find($id);
            $rate->save($request->param());
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }
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
