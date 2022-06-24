<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\db\exception\DbException;
use think\facade\Log;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\House as HouseModel;
use app\common\model\HouseRate as HouseRateModel;
use app\common\model\Area;
use app\common\model\Admin as AdminModel;
use app\common\model\User as UserModel;

class House extends AdminController
{
    /**
     * 筛选项
     * @var string[]
     */
    protected $SelectList = [
        'house_extension' => '是否有加建',
        'is_owner_business' => '经营自建房',
        'is_balcony' => '悬挑阳台',
        'house_change' => '是否有扩建',
        'is_crack' => '是否有裂缝',
        'is_incline_or_deposition' => '是否有沉降',
        'is_rust_eaten' => '钢筋锈蚀',
    ];

    /**
     * 照片信息
     * @var string[]
     */
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
        $this->DistrictList = (new $this->model)->getDistrictList();
        $this->view->assign('DistrictList', $this->DistrictList);
        $this->view->assign('userList', UserModel::field('id, username')->order('id desc, login_time desc')->select());
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->view->assign('areaList', Area::field('id, title')->order('id desc')->select());
        $this->view->assign('adminList', AdminModel::field('id, username')->order('id desc, login_time desc')->select());
        return $this->view->fetch();
    }

    /**
     * 数据统计
     *
     * @return \think\Response
     */
    public function analysis()
    {
        $this->view->assign([
            'SelectList' => $this->SelectList,
            'AreaList' => Area::field('id, title')->order('id desc')->select(),
            'FinalRateList' => (new HouseRateModel)->getFinalRateList(),
        ]);
        return $this->view->fetch();
    }

    public function getHouseList(Request $request)
    {
        if ($request->isAjax()) {
            $districtList = $this->DistrictList;
            $page = (int) $request->param('page', 1);
            $limit = (int) $request->param('limit', 10);
            $title = $request->param('title', '');
            $district = $request->param('district', 0);
            $code = $request->param('code', '');
            $areaId = (int) $request->param('area_id', 0);
            $user_id = (int) $request->param('user_id', 0);
            $admin_id = (int) $request->param('admin_id', 0);
            $status = (int) $request->param('status', -1);
            $rate_status = (int) $request->param('rate_status', -1);
            $house_extension = (int) $request->param('house_extension', 0);
            $is_owner_business = (int) $request->param('is_owner_business', 0);
            $is_balcony = (int) $request->param('is_balcony', 0);
            $house_change = (int) $request->param('house_change', 0);
            $is_crack = (int) $request->param('is_crack', 0);
            $is_incline_or_deposition = (int) $request->param('is_incline_or_deposition', 0);
            $is_rust_eaten = (int) $request->param('is_rust_eaten', 0);
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

            if ($admin_id) {
                $map[] = ['admin_id', '=', $admin_id];
            }

            if ($status >= 0) {
                $map[] = ['status', '=', $status];
            }

            if ($rate_status >= 0) {
                $map[] = ['rate_status', '=', $rate_status];
            }

            if ($is_balcony > 0) {
                $map[] = ['is_balcony', '=', $is_balcony];
            }

            if ($is_owner_business > 0) {
                $map[] = ['is_owner_business', '=', $is_owner_business];
            }

            if ($house_change > 0) {
                $map[] = $house_change === 2 ? ['house_change', '=', 9] : ['house_change', 'in', [1, 2]];
            }

            if ($is_owner_business > 0) {
                $map[] = ['is_owner_business', '=', $is_owner_business];
            }

            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->withAttr('district', function ($value) use ($districtList) {
                    if ($value > 0) {
                        return $districtList[$value];
                    }
                })
                ->withAttr('status', function ($value) {
                    return $value === 1 ? '已完成' : '';
                })
                ->withAttr('rate_status', function ($value) {
                    return $value === 1 ? '已评级' : '';
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
            'RelatedDataList' => $model->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $model->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $model->getPeripherySafetyInvestigationList(),
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

    public function productDownload()
    {
        $id = intval(input('id', 0));
        $product = $this->model::where('id', $id)->find();
        if(empty($product)){
            $this->error('作品不存在');
        }

        $photoOrigin = $this->model::where('product_id', $id)->column('photo_origin');
        if(empty($photoOrigin)){
            $this->error('作品不存在图片');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'photo_');
        if(!$tmpFile){
            $this->error('system error');
        }

        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE);

        foreach($photoOrigin as $v){
            $fileContent = file_get_contents($v);
            $zip->addFromString(basename($v), $fileContent);
        }

        $zip->close();

        $out = "{$product['activity_id']}-{$product['id']}-".mb_substr($product['name'], 0, 10).'.zip';

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$out);
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);

        unlink($tmpFile);
        exit;
    }

}
