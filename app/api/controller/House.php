<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\House as HouseModel;

class House extends ApiController
{

    protected $infos = [
        'doorplate_info' => '门牌照片',
        'house_info' => '外立面照片',
        'indoor_info' => '户内照片',
        'roof_info' => '屋顶照片',
        'extension_info' => '加建照片',
        'rust_eaten_info' => '钢筋锈蚀照片',
        'crack_info' => '裂缝照片',
        'other_info' => '其他照片',
    ];

    public function initialize()
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
        $page = $this->params['page'] ?? 1;
        $limit = $this->params['limit'] ?? 10;
        $searchType = $this->params['search_type'] ?? 'title';
        $title = $this->params['title'] ?? '';
        $areaId = $this->params['area_id'] ?? 0;
        $status = $this->params['status'] ?? -1;
        $district = $this->params['district'] ?? 0;

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $map = [
            ['area_id', '=', $areaId],
        ];

        if ($district) {
            $districtList = (new $this->model)->getDistrictList();
            $map[] = ['district', 'like', '%' . $districtList[$district] . '%'];
        }

        if ($status >= 0) {
            $map[] = ['status', '=', $status];
        }

        switch ($searchType) {
            case 'code':
                $map[] = ['code', 'like', '%' . $title . '%'];
                break;

            default:
                $map[] = ['title', 'like', '%' . $title . '%'];
                break;
        }

        $this->returnData['code'] = 1;
        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::field('id, title, code, district, area_id, status, create_time')
            ->where('user_id = ' . $this->userInfo->id . ' or user_id = 0')
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
            $params = $request->param();
            $params['user_id'] = $this->userInfo->id;

            foreach ($this->infos as $key => $val) {
                if (isset($params[$key]['image'])) {
                    $temp = [];
                    for ($i = 0; $i < count($params[$key]['image']); $i ++) {
                        $temp[$key][] = [
                            'image' => $params[$key]['image'][$i],
                            'description' => $params[$key]['description'][$i]
                        ];
                    }
                    $params[$key] = $temp[$key];
                }
            }

            $result = (new $this->model)->save($params);

            $this->returnData['code'] = 1;
            $this->returnData['data'] = $result;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData('添加失败');
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
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
        if ($request->isPost()) {
            $params = $request->except(['id']);
            $house = $this->model::find($id);

            foreach ($this->infos as $key => $val) {
                if (isset($params[$key]['image'])) {
                    $temp = [];
                    for ($i = 0; $i < count($params[$key]['image']); $i ++) {
                        $temp[$key][] = [
                            'image' => $params[$key]['image'][$i],
                            'description' => $params[$key]['description'][$i]
                        ];
                    }
                    $params[$key] = $temp[$key];
                }
            }

            $house->save($params);

            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
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
        if ($this->request->isPost()) {
            $house = $this->model::find($id);
            $house->delete();
            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData();
    }
}
