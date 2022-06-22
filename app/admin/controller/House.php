<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\db\exception\DbException;
use think\facade\Log;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\House as HouseModel;
use app\common\model\HouseRate;
use app\common\model\Area;
use app\common\model\User as UserModel;

class House extends AdminController
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

    protected function initialize()
    {
        parent::initialize();

        $this->model = HouseModel::class;
        $this->districtList = (new $this->model)->getDistrictList();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->view->assign('districtList', $this->districtList);
        $this->view->assign('areaList', Area::field('id, title')->order('id desc')->select());
        $this->view->assign('userList', UserModel::field('id, username')->order('id desc, login_time desc')->select());
        return $this->view->fetch();
    }

    public function getHouseList(Request $request)
    {
        if ($request->isAjax()) {
            $page = (int) $request->param('page', 1);
            $limit = (int) $request->param('limit', 10);
            $title = $request->param('title', '');
            $district = $request->param('district', 0);
            $code = $request->param('code', '');
            $areaId = (int) $request->param('area_id', 0);
            $user_id = (int) $request->param('user_id', 0);
            $status = (int) $request->param('status', -1);
            $rate_status = (int) $request->param('rate_status', -1);
            $districtList = $this->districtList;
            $map = [];

            if ($title) {
                $map[] = ['title', 'like', '%' . $title . '%'];
            }

            if ($district) {
                $map[] = ['district', '=', $district];
            }

            if ($code) {
                $map[] = ['code', 'like', '%' . $code . '%'];
            }

            if ($areaId) {
                $map[] = ['area_id', '=', $areaId];
            }

            if ($user_id) {
                $map[] = ['user_id', '=', $user_id];
            }

            if ($status >= 0) {
                $map[] = ['status', '=', $status];
            }

            if ($rate_status >= 0) {
                $map[] = ['rate_status', '=', $rate_status];
            }

            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->withAttr('district', function ($value, $data) use ($districtList) {
                    return $districtList[$value];
                })
                ->with(['area', 'admin', 'user'])
                ->hidden(['area', 'admin', 'user'])
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
            'HouseUsageList' => $model->getHouseUsageList(),
            'DistrictList' => $model->getDistrictList(),
            'RelatedDataList' => $model->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $model->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $model->getPeripherySafetyInvestigationList(),
            'StructureList' => $model->getStructureList(),
            'BasisTypeList' => $model->getBasisTypeList(),
            'HouseExtensionList' => $model->getHouseExtensionList(),
            'HouseChangeList' => $model->getHouseChangeList(),
            'HouseChangeFloorDataList' => $model->getHouseChangeFloorDataList(),
            'infos' => $this->infos,
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

            $params['house_usage'] = isset($params['house_usage']) ? array_values($params['house_usage']) : [];
            $params['house_extension'] = isset($params['house_extension']) ? array_values($params['house_extension']) : [];
            $params['house_change_floor_data'] = isset($params['house_change_floor_data']) ? array_values($params['house_change_floor_data']) : [];

            $house = $this->model::getByCode($params['code']);
            if ($house) {
                $this->error('房屋编码已存在');
            }

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

            (new $this->model)->save($params);

            $this->returnData['code'] = 1;
            $this->returnData['data'] = $params;
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
            'HouseUsageList' => $model->getHouseUsageList(),
            'DistrictList' => $model->getDistrictList(),
            'RelatedDataList' => $model->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $model->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $model->getPeripherySafetyInvestigationList(),
            'StructureList' => $model->getStructureList(),
            'BasisTypeList' => $model->getBasisTypeList(),
            'HouseExtensionList' => $model->getHouseExtensionList(),
            'HouseChangeList' => $model->getHouseChangeList(),
            'HouseChangeFloorDataList' => $model->getHouseChangeFloorDataList(),
            'infos' => $this->infos,
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
            $params = $request->except(['id']);

            $params['house_usage'] = isset($params['house_usage']) ? array_values($params['house_usage']) : [];
            $params['house_extension'] = isset($params['house_extension']) ? array_values($params['house_extension']) : [];
            $params['house_change_floor_data'] = isset($params['house_change_floor_data']) ? array_values($params['house_change_floor_data']) : [];

            $house = $this->model::where('id != ' . $id . ' and code = "' . $params['code'] . '"')->find();
            if ($house) {
                $this->error('房屋编码已存在');
            }

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
            $params['status'] = 1;

            $house->save($params);
            $this->returnData['data'] = $params;
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


    public function importExcel()
    {
        if ($this->request->isPost()) {
            $file = request()->file('file');
            $columns = [
                'user_id' => 0,
                'area_id' => $this->request->param('area_id')
            ];

            $data = readExcel($file, $columns);
            try {
                $res = (new $this->model)->saveAll($data);

                $this->returnData['code'] = 1;
                $this->returnData['data'] = $data;

                $this->success(lang('The import was successful'));
            } catch (DbException $dbException) {
                $this->returnData['code'] = $dbException->getCode();
                $this->returnData['data'] = $dbException->getData();

                $this->error($dbException->getMessage());
            }
        }

        $this->error();
    }

    private function readExcel($file, $appendColumns = [])
    {
        return readExcel($file, $appendColumns);
    }

    public function checkStatus($id)
    {
        $house = $this->model::find($id);
        if ($house->status) {
            $this->returnData['code'] = 1;
            $this->success('已完成');
        }
        $this->error('排查未完成，不能评级');
    }
}
