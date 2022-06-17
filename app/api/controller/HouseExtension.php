<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\Building as BuildingModel;

class HouseExtension extends ApiController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = BuildingModel::class;
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
        $title = $this->params['title'] ?? '';
        $areaId = $this->params['area_id'] ?? 0;

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $map = [
            ['area_id', '=', $areaId]
        ];

        if ($title) {
            $map[] = ['title', 'like', '%' . $title . '%'];
        }

        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::where($map)
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
        if (!$id) {
            $this->returnApiData('未提供正确的id');
        }
        $this->returnData['data'] = $this->model::findOrEmpty($id);
        if ($this->returnData['data']->isEmpty()) {
            $this->returnApiData(lang('No data was found'));
        }
        $this->returnData['code'] = 1;
        $this->returnApiData(lang('Done'));
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
