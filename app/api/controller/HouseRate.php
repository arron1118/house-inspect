<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\HouseRate as HouseRateModel;
use app\common\model\Area;
use app\common\model\House;

class HouseRate extends ApiController
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
        $page = $this->params['page'] ?? 1;
        $limit = $this->params['limit'] ?? 10;
        $house_id = $this->params['house_id'] ?? 0;
        $areaId = $this->params['area_id'] ?? 0;
        $type = $this->params['type'] ?? 0;

        if ((int) $house_id <= 0) {
            $this->returnApiData('请提供房号ID: house_id');
        }

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $map = [
            ['house_id', '=', $house_id],
        ];

        $fields = 'id, house_id, type, create_time';

        $this->returnData['code'] = 1;
        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::field($fields)
            ->where($map)
            ->order('id desc')
            ->limit(($page - 1) * $limit, $limit)
            ->select();

        $this->returnApiData(lang('Done'));
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
            $params = $request->only(['title', 'area_id', 'building_id', 'house_id', 'type', 'images', 'image_time', 'description', 'crack_area', 'crack_sum', 'crack_images', 'crack_description', 'crack_image_time', 'refuse_images', 'refuse_image_time', 'refuse_reason']);
            $params['investigation_times'] = getInvestigationTimes($params['area_id']);
            $params['user_id'] = $this->userInfo->id;
            $params['type'] = (int) $params['type'];

            if (isset($params['title']) && $params['title'] !== '') {
                $house = new House();
                $house->investigation_times = $params['investigation_times'];
                $house->area_id = $params['area_id'];
                $house->building_id = $params['building_id'];
                $house->user_id = $params['user_id'];
                $house->title = $params['title'];
                $house->save();

                $params['house_id'] = $house->id;
            }

            if (!$params['house_id']) {
                $this->returnApiData('未找到套房ID');
            }

            if ($params['type'] !== 3) {

                (new $this->model)->save($params);
            }

            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData();
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
