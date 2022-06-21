<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\db\exception\DbException;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\House as HouseModel;
use app\common\model\HouseRate;
use app\common\model\Area;

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
            $code = $request->param('code', '');
            $areaId = (int) $request->param('area_id', 0);
            $status = (int) $request->param('status', -1);
            $rate_status = (int) $request->param('rate_status', -1);
            $map = [];

            if ($title) {
                $map[] = ['title', 'like', '%' . $title . '%'];
            }

            if ($code) {
                $map[] = ['code', 'like', '%' . $code . '%'];
            }

            if ($areaId) {
                $map[] = ['area_id', '=', $areaId];
            }

            if ($status >= 0) {
                $map[] = ['status', '=', $status];
            }

            if ($rate_status >= 0) {
                $map[] = ['rate_status', '=', $rate_status];
            }

            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->with(['area', 'admin'])
                ->hidden(['area', 'admin'])
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
            'houseUsageList' => $model->getHouseUsageList(),
            'peripheryEnvList' => $model->getPeripheryEnvList(),
            'balconyTypeList' => $model->getBalconyTypeList(),
            'designPaperList' => $model->getDesignPaperList(),
            'purposeList' => $model->getPurposeList(),
            'afterChangeList' => $model->getAfterChangeList(),
            'crackTypeList' => $model->getCrackTypeList(),
            'inclineOrDepositionTypeList' => $model->getInclineOrDepositionTypeList(),
            'examinatorTypeList' => $model->getExaminatorTypeList(),
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
            'houseUsageList' => $model->getHouseUsageList(),
            'peripheryEnvList' => $model->getPeripheryEnvList(),
            'balconyTypeList' => $model->getBalconyTypeList(),
            'designPaperList' => $model->getDesignPaperList(),
            'purposeList' => $model->getPurposeList(),
            'afterChangeList' => $model->getAfterChangeList(),
            'crackTypeList' => $model->getCrackTypeList(),
            'inclineOrDepositionTypeList' => $model->getInclineOrDepositionTypeList(),
            'examinatorTypeList' => $model->getExaminatorTypeList(),
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

//            foreach ($this->infos as $key => $val) {
//                $columns[$key][] = [
//                    'image' => '',
//                    'description' => ''
//                ];
//            }

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
