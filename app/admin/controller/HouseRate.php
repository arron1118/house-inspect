<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\common\model\House as HouseModel;
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

        $rate = $this->model::getByHouseId($house_id);
        if (!$rate) {
            $rate = $this->model::create(['house_id' => $house_id]);
            HouseModel::update(['admin_id' => $this->userInfo->id, 'id' => $house_id]);
        }

        $model = new $this->model;
        $this->view->assign([
            'rate' => $rate,
            'StructureList' => $model->getStructureList(),
            'BasisTypeList' => $model->getBasisTypeList(),
            'FoundationSafetyRateList' => $model->getFoundationSafetyRateList(),
            'FoundationRateList'=> $model->getFoundationRateList(),
            'HouseSafetyRateList' => $model->getHouseSafetyRateList(),
            'HouseDangerFrameRateList' => $model->getHouseDangerFrameRateList(),
            'HouseDangerRoofRateList' => $model->getHouseDangerRoofRateList(),
            'HouseLatentDangerFrameRateList' => $model->getHouseLatentDangerFrameRateList(),
            'FinalRateList' => $model->getFinalRateList(),
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
            $params = $request->except(['id']);

            $params['foundation_rate'] = isset($params['foundation_rate']) ? array_values($params['foundation_rate']) : [];
            $params['house_danger_frame_rate'] = isset($params['house_danger_frame_rate']) ? array_values($params['house_danger_frame_rate']) : [];
            $params['house_danger_roof_rate'] = isset($params['house_danger_roof_rate']) ? array_values($params['house_danger_roof_rate']) : [];
            $params['house_latent_danger_frame_rate'] = isset($params['house_latent_danger_frame_rate']) ? array_values($params['house_latent_danger_frame_rate']) : [];
            $params['suggestion'] = isset($params['suggestion']) ? array_values($params['suggestion']) : [];

            $rate = $this->model::find($id);
            $rate->save($params);
            HouseModel::update(['rate_status' => 1, 'id' => $rate->house_id, 'admin_id' => $this->userInfo->id]);
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
