<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\common\library\Report;
use app\common\model\ResumeCategory;
use think\db\exception\DbException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\facade\Db;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\House as HouseModel;
use app\common\model\HouseRate as HouseRateModel;
use app\common\model\Area;
use app\common\model\Admin as AdminModel;
use app\common\model\User as UserModel;
use app\common\model\District;

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
        $this->view->assign([
            'userList' => UserModel::field('id, username')->order('id desc, login_time desc')->select(),
        ]);
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->view->assign([
            'adminList' => AdminModel::field('id, username')->order('id desc, login_time desc')->select(),
            'areaList' => Area::field('id, title')->order('id desc')->select(),
            'rateStatusList' => (new $this->model)->getRateStatusList()
        ]);
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
            $page = (int)$request->param('page', 1);
            $limit = (int)$request->param('limit', 10);
            $title = $request->param('title', '');
            $district_id = $request->param('district_id', 0);
            $code = $request->param('code', '');
            $areaId = (int)$request->param('area_id', 0);
            $user_id = (int)$request->param('user_id', 0);
            $admin_id = (int)$request->param('admin_id', 0);
            $status = (int)$request->param('status', -1);
            $rate_status = (int)$request->param('rate_status', -1);
            $house_extension = (int)$request->param('house_extension', 0);
            $is_owner_business = (int)$request->param('is_owner_business', 0);
            $is_balcony = (int)$request->param('is_balcony', 0);
            $house_change = (int)$request->param('house_change', 0);
            $is_crack = (int)$request->param('is_crack', 0);
            $is_incline_or_deposition = (int)$request->param('is_incline_or_deposition', 0);
            $is_rust_eaten = (int)$request->param('is_rust_eaten', 0);
            $final_rate = (int)$request->param('final_rate', 0);
            $map = [];
            $mapOr = [];
            $whereAnd = [];

            if ($title) {
                $map[] = ['title', 'like', '%' . $title . '%'];
            }

            if ($code) {
                $map[] = ['code', 'like', '%' . $code . '%'];
            }

            if ($areaId) {
                $map[] = ['area_id', '=', $areaId];

                if ($district_id) {
                    $map[] = ['district_id', '=', $district_id];
                }
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

            // 有无阳台
            if ($is_balcony > 0) {
                $map[] = ['is_balcony', '=', $is_balcony];
            }

            // 经营性自建房
            if ($is_owner_business > 0) {
                $map[] = ['is_owner_business', '=', $is_owner_business];
            }

            // 有无扩建
            if ($house_change > 0) {
                $map[] = $house_change === 2 ? ['house_change', '=', 9] : ['house_change', 'in', [1, 2]];
            }

            // 有无加建
            if ($house_extension > 0) {
                if ($house_extension === 2) {
                    $map[] = ['house_extension', 'like', '%9%'];
                } else {
                    $mapOr = function ($query) {
                        $query->where("house_extension not like '%9%' and house_extension != '[]' and house_extension != ''");
                    };
                }
            }

            // 有无沉降
            if ($is_incline_or_deposition > 0) {
                $where = $is_incline_or_deposition === 1 ? 'foundation_rate like "%5%"' : 'foundation_rate not like "%5%"';
                $rate = HouseRateModel::where($where)->column('house_id');
                $map[] = ['id', 'in', $rate];
            }

            // 排查结论
            if ($final_rate > 0) {
                $rate = HouseRateModel::where('final_rate', $final_rate)->column('house_id');
                $map[] = ['id', 'in', $rate];
            }

            // 锈蚀
            if ($is_rust_eaten > 0) {
                if ($is_rust_eaten === 1) {
                    $where = 'house_danger_frame_rate like "%1%" or house_danger_roof_rate like "%4%" or house_latent_danger_frame_rate like "%2%"';
                    $mapOr = function ($query) {
                        $query->where('rust_eaten_info != "[]"');
                    };
                } else {
                    $where = '(house_danger_frame_rate != "[]" and house_danger_frame_rate not like "%1%") or (house_danger_roof_rate != "[]" and house_danger_roof_rate not like "%4%") or (house_latent_danger_frame_rate != "[]" and house_latent_danger_frame_rate not like "%2%")';
                }

                $rate = HouseRateModel::where($where)->column('house_id');
                $map[] = ['id', 'in', $rate];
            }

            // 裂缝
            if ($is_crack > 0) {
                if ($is_crack === 1) {
                    $mapOr = function ($query) {
                        $query->where('crack_info != "[]"');
                    };
                }
                $fields = ['foundation_rate', 'house_danger_frame_rate', 'house_latent_danger_frame_rate', 'house_latent_danger_frame_rate'];
                $where = '';
                foreach ($fields as $val) {
                    if ($is_crack === 1) {
                        switch ($val) {
                            case 'foundation_rate':
                                $where .= ' (' . $val . ' like "%2%" or ' . $val . ' like "%3%" or ' . $val . ' like "%4%") ';
                                break;

                            case 'house_danger_frame_rate':
                                $where .= ' or (' . $val . ' != "" and ' . $val . ' != "[]" and ' . $val . ' not like "%1%") ';
                                break;

                            case 'house_danger_roof_rate':
                                $where .= ' or (' . $val . ' != "" and ' . $val . ' !="[]" and ' . $val . ' not like "%4%") ';
                                break;

                            case 'house_latent_danger_frame_rate':
                                $where .= ' or (' . $val . ' like "%1%") ';
                                break;
                        }
                    } else {
                        switch ($val) {
                            case 'foundation_rate':
                                $where .= '(' . $val . ' not like "%2%" and ' . $val . ' not like "%3%" and ' . $val . ' not like "%4%")';
                                break;

                            case 'house_danger_frame_rate':
                                $where .= ' or (' . $val . ' = "" or ' . $val . ' = "[]" or ' . $val . ' like "%1%") ';
                                break;

                            case 'house_danger_roof_rate':
                                $where .= ' or (' . $val . ' = "" or ' . $val . ' ="[]" or ' . $val . ' like "%4%") ';
                                break;

                            case 'house_latent_danger_frame_rate':
                                $where .= ' or (' . $val . ' not like "%1%") ';
                                break;
                        }
                    }
                }
                $rate = HouseRateModel::where($where)->column('house_id');
                $map[] = ['id', 'in', $rate];
            }

            if ($rate_status >= 0) {
                switch ($rate_status) {
                    case 0:
                        $whereAnd = 'rate_status != 1 and rate_status_set != 1';
                        break;

                    case 1:
                        $whereAnd = 'rate_status = 1 and rate_status_set != 1';
                        break;

                    case 2:
                        $whereAnd = 'rate_status_set = 1';
                        break;
                }
            }

            $this->returnData['total'] = $this->model::where($map)->where($mapOr)->where($whereAnd)->count();
            $this->returnData['data'] = $this->model::where($map)->where($mapOr)->where($whereAnd)
                ->withAttr('status', function ($value) {
                    return $value === 1 ? '已完成' : '';
                })
                ->withAttr('rate_status', function ($value, $data) {
                    $text = '';
                    if ($value === 1) {
                        $text = '<a href="javascript:;" class=" text-decoration-none " lay-event="createReport" title="生成报告">生成报告</a>';
                    }

                    if ($data['rate_status_set'] === 1) {
                        $text = '<span class="text-decoration-line-through">已拆除</span>';
                    }

                    return $text;
                })
                ->with(['area', 'district', 'admin', 'user'])
                ->hidden(['area', 'district', 'admin', 'user'])
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            $this->success();
        }

        $this->error();
    }

    public function getDistrictList(Request $request, $area_id)
    {
        if ($request->isAjax()) {
            $this->returnData['data'] = District::with(['area'])->hidden(['area'])->withCount(['house'])
                ->field('id, title')
                ->where('area_id', $area_id)
                ->order('id desc')
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
    public function create($area_id)
    {
        $model = new $this->model;
        $this->view->assign([
            'area_id' => $area_id,
            'DistrictList' => District::field('id, title')->where('area_id', $area_id)->select(),
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
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $params = $request->param('param');

            $params['house_usage'] = isset($params['house_usage']) ? array_values($params['house_usage']) : [];
            $params['house_extension'] = isset($params['house_extension']) ? array_values($params['house_extension']) : [];
            $params['house_change_floor_data'] = isset($params['house_change_floor_data']) ? array_values($params['house_change_floor_data']) : [];

            $house = $this->model::where('code = "' . $params['code'] . '"')->findOrEmpty();
            if (!$house->isEmpty()) {
                $this->error('房屋编码已存在');
            }

            $params = $this->getParams($params);
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
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $rate = HouseRateModel::getByHouseId($id);
        if (!$rate) {
            $rate = HouseRateModel::create(['house_id' => $id]);
        }
        //
        $model = new $this->model;
        $houseRateModel = new HouseRateModel;
        $house = $this->model::find($id);
        $DistrictList = District::field('id, title')->where('area_id', $house->area_id)->select();

        $this->view->assign([
            'house' => $house,
            'DistrictList' => $DistrictList,
            'HouseUsageList' => $model->getHouseUsageList(),
            'RelatedDataList' => $model->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $model->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $model->getPeripherySafetyInvestigationList(),
            'HouseExtensionList' => $model->getHouseExtensionList(),
            'HouseChangeList' => $model->getHouseChangeList(),
            'HouseChangeFloorDataList' => $model->getHouseChangeFloorDataList(),
            'infos' => $this->infos,
            // 评级
            'rate' => $rate,
            'StructureList' => $houseRateModel->getStructureList(),
            'BasisTypeList' => $houseRateModel->getBasisTypeList(),
            'FoundationSafetyRateList' => $houseRateModel->getFoundationSafetyRateList(),
            'FoundationRateList' => $houseRateModel->getFoundationRateList(),
            'HouseSafetyRateList' => $houseRateModel->getHouseSafetyRateList(),
            'HouseDangerFrameRateList' => $houseRateModel->getHouseDangerFrameRateList(),
            'HouseDangerRoofRateList' => $houseRateModel->getHouseDangerRoofRateList(),
            'HouseLatentDangerFrameRateList' => $houseRateModel->getHouseLatentDangerFrameRateList(),
            'FinalRateList' => $houseRateModel->getFinalRateList(),
            'GradeList' => $houseRateModel->getGradeList(),
            'SuggestionList' => $houseRateModel->getSuggestionList(),
        ]);
        return $this->view->fetch();
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        if ($request->isPost()) {
            $params = $request->param('param.house');

            $params['house_usage'] = isset($params['house_usage']) ? array_values($params['house_usage']) : [];
            $params['house_extension'] = isset($params['house_extension']) ? array_values($params['house_extension']) : [];
            $params['house_change_floor_data'] = isset($params['house_change_floor_data']) ? array_values($params['house_change_floor_data']) : [];

            $house = $this->model::where('id != ' . $params['id'] . ' and code = "' . $params['code'] . '"')->findOrEmpty();
            if (!$house->isEmpty()) {
                $this->error('房屋编码已存在');
            }

            $house = $this->model::find($params['id']);

            $params = $this->getParams($params);

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
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if ($this->request->isPost()) {
            if ($this->userInfo->id !== 1) {
                $this->error('您没有权限操作');
            }

            $house = $this->model::find($id);
            $house->delete();
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getParams($params)
    {
        foreach ($this->infos as $key => $val) {
            if (isset($params[$key]['image'])) {
                $temp = [];
                for ($i = 0, $iMax = count($params[$key]['image']); $i < $iMax; $i++) {
                    $temp[$key][] = [
                        'image' => $params[$key]['image'][$i],
                        'description' => $params[$key]['description'][$i]
                    ];
                }
                $params[$key] = $temp[$key];
            } else {
                $params[$key] = [];
            }
        }
        return $params;
    }

    /**
     * 导入房屋列表
     */
    public function importExcel()
    {
        if ($this->request->isPost()) {
            set_time_limit(0);
            $file = request()->file('file');
            $columns = [
//                'user_id' => 0,
//                'area_id' => $this->request->param('area_id')
            ];

            $data = readExcel($file, $columns);
            try {
//                $res = (new $this->model)->saveAll($data);

                foreach ($data as $key => $val) {
//                    $house = $this->model::where('code', $val['code'])->findOrEmpty();
//                    if (!$house->isEmpty()) {
//                        $house->contact = $val['contact'];
//                        if ($house->space === '') {
//                            $house->space = $val['space'];
//                        }
//
//                        $house->save();
//                    }
//
//                    if ($key % 1000 === 0) {
//                        sleep(3);
//                    }

                    (new ResumeCategory())->save($val);
                }

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

    /**
     * 检查房屋状态
     * @param $id
     */
    public function checkStatus($id)
    {
        $house = $this->model::find($id);
        if ($house->status) {
            $this->returnData['code'] = 1;
            $this->success('已完成');
        }
        $this->error('排查未完成，不能评级');
    }

    /**
     * 导出图片
     * @param Request $request
     */
    public function exportImages(Request $request)
    {
        $ids = $request->param('ids', '');
        if (!$ids) {
            $this->error();
        }

        $fields = 'code, ' . implode(',', array_keys($this->infos));
        $house = $this->model::where('id', 'in', $ids)->column($fields);
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $zipName = '房屋排查_' . date('Y_m_d_H_i_s') . '.zip';
        // 实例化类,使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        $zip = new \ZipArchive;
        /*
        * 通过ZipArchive的对象处理zip文件
        * $zip->open这个方法如果对zip文件对象操作成功，$zip->open这个方法会返回TRUE
        * $zip->open这个方法第一个参数表示处理的zip文件名。
        * 这里重点说下第二个参数，它表示处理模式
        * ZipArchive::OVERWRITE 总是以一个新的压缩包开始，此模式下如果已经存在则会被覆盖。
        * ZipArchive::OVERWRITE 不会新建，只有当前存在这个压缩包的时候，它才有效
        * */
        if ($zip->open($zipName, \ZIPARCHIVE::CREATE) !== true) {
            $this->error('无法打开文件，或者文件创建失败');
        }

        // 文件打包
        foreach ($house as $k => $v) {
            foreach ($this->infos as $key => $val) {
                if ($v[$key]) {
                    foreach ($v[$key] as $value) {
                        $file = public_path() . $value['image'];
                        if (file_exists($file)) {
                            $zip->addFile($file, $v['code'] . '/' . $val . '/' . basename($file));
                        }
                    }
                }
            }
        }
        // 关闭
        $zip->close();
        // 验证文件是否存在
        if (!file_exists($zipName)) {
            $this->error("文件不存在");
        }

        // ob_clean();
        // 下载压缩包
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($zipName)); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: ' . filesize($zipName)); //告诉浏览器，文件大小
        header('Accept-Length: ' . filesize($zipName));
//            ob_end_clean();
        @readfile($zipName);//ob_end_clean();
        @unlink($zipName);//删除压缩包
        exit();
//        return download($zipName, $zipName);
    }

    /**
     * 图片归类
     * @param Request $request
     */
    public function baleImages(Request $request)
    {
        $ids = $request->param('ids', '');
        if (!$ids) {
            $this->error();
        }

        $fields = 'code, ' . implode(',', array_keys($this->infos));
        $house = $this->model::where('id', 'in', $ids)->column($fields);
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        // 文件打包
        foreach ($house as $k => $v) {
            if (!empty($v)) {
                foreach ($this->infos as $key => $val) {
                    if ($v[$key]) {
                        $path = public_path() . '/images/' . $v['code'] . '/' . $val . '/';
                        if (!is_dir($path) && !mkdir($concurrentDirectory = iconv('UTF-8', 'GBK', $path), 0777, true) && !is_dir($concurrentDirectory)) {
//                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
                            $this->error('无法创建目录：' . $path);
                        }
                        foreach ($v[$key] as $value) {
                            $file = public_path() . $value['image'];
                            if (file_exists($file)) {
                                copy($file, $path . $value['description'] . '.' . explode('.', basename($file))[1]);
                            }
                        }
                    }
                }
            }
        }

        $this->success(lang('Done'));
    }

    /**
     * 导出报表
     * @param $id
     */
    public function exportReport($id)
    {
        $report = new Report($id);
        $report->createReport();
    }

    /**
     * 导出已完成房屋EXCEL
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportExcel(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $title = $request->param('title', '');
        $district_id = $request->param('district_id', 0);
        $code = $request->param('code', '');
        $areaId = (int)$request->param('area_id', 0);
        $user_id = (int)$request->param('user_id', 0);
        $admin_id = (int)$request->param('admin_id', 0);
        $rate_status = (int)$request->param('rate_status', -1);
        $house_extension = (int)$request->param('house_extension', 0);
        $is_owner_business = (int)$request->param('is_owner_business', 0);
        $is_balcony = (int)$request->param('is_balcony', 0);
        $house_change = (int)$request->param('house_change', 0);
        $is_crack = (int)$request->param('is_crack', 0);
        $is_incline_or_deposition = (int)$request->param('is_incline_or_deposition', 0);
        $is_rust_eaten = (int)$request->param('is_rust_eaten', 0);
        $final_rate = (int)$request->param('final_rate', 0);
        $map = [
            ['status', '=', 1]
        ];
        $mapOr = [];
        $whereAnd = [];
        $area = '全部';
        $description = '';
        $final_rate_list = ['无', 'A类', 'B类', 'C1类', 'C2类', 'C3类'];

        if ($title) {
            $map[] = ['title', 'like', '%' . $title . '%'];
        }

        if ($code) {
            $map[] = ['code', 'like', '%' . $code . '%'];
        }

        if ($areaId) {
            $map[] = ['area_id', '=', $areaId];
            $area_title = Area::where('id', $areaId)->value('title');

            if ($district_id) {
                $map[] = ['district_id', '=', $district_id];
                $district_title = District::where('id', $district_id)->value('title');
                $area_title .= '-' . $district_title;
            }

            $area = $area_title;
        }

        if ($user_id) {
            $map[] = ['user_id', '=', $user_id];
        }

        if ($admin_id) {
            $map[] = ['admin_id', '=', $admin_id];
        }

        // 有无阳台
        if ($is_balcony > 0) {
            $map[] = ['is_balcony', '=', $is_balcony];
            $description .= ' ' . ($is_balcony === 1 ? '有' : '无') . '阳台';
        }

        // 经营性自建房
        if ($is_owner_business > 0) {
            $map[] = ['is_owner_business', '=', $is_owner_business];
            $description .= ' ' . ($is_balcony === 1 ? '是' : '不是') . '经营性自建房';
        }

        // 有无扩建
        if ($house_change > 0) {
            $map[] = $house_change === 2 ? ['house_change', '=', 9] : ['house_change', 'in', [1, 2]];
            $description .= ' ' . ($house_change === 1 ? '有' : '无') . '扩建';
        }

        // 有无加建
        if ($house_extension > 0) {
            if ($house_extension === 2) {
                $map[] = ['house_extension', 'like', '%9%'];
                $description .= ' 无加建';
            } else {
                $mapOr = function ($query) {
                    $query->where("house_extension not like '%9%' and house_extension != '[]' and house_extension != ''");
                };
                $description .= ' 有加建';
            }
        }

        // 有无沉降
        if ($is_incline_or_deposition > 0) {
            $where = $is_incline_or_deposition === 1 ? 'foundation_rate like "%5%"' : 'foundation_rate not like "%5%"';
            $rate = HouseRateModel::where($where)->column('house_id');
            $map[] = ['id', 'in', $rate];
            $description .= ' ' . ($is_incline_or_deposition === 1 ? '有' : '无') . '沉降';
        }

        // 排查结论
        if ($final_rate > 0) {
            $rate = HouseRateModel::where('final_rate', $final_rate)->column('house_id');
            $map[] = ['id', 'in', $rate];
            $description .= ' ' . $final_rate_list[$final_rate];
        }

        // 锈蚀
        if ($is_rust_eaten > 0) {
            if ($is_rust_eaten === 1) {
                $where = 'house_danger_frame_rate like "%1%" or house_danger_roof_rate like "%4%" or house_latent_danger_frame_rate like "%2%"';
                $mapOr = function ($query) {
                    $query->where('rust_eaten_info != "[]"');
                };
            } else {
                $where = '(house_danger_frame_rate != "[]" and house_danger_frame_rate not like "%1%") or (house_danger_roof_rate != "[]" and house_danger_roof_rate not like "%4%") or (house_latent_danger_frame_rate != "[]" and house_latent_danger_frame_rate not like "%2%")';
            }

            $rate = HouseRateModel::where($where)->column('house_id');
            $map[] = ['id', 'in', $rate];
            $description .= ' ' . ($is_rust_eaten === 1 ? '有' : '无') . '锈蚀';
        }

        // 裂缝
        if ($is_crack > 0) {
            if ($is_crack === 1) {
                $mapOr = function ($query) {
                    $query->where('crack_info != "[]"');
                };
            }
            $fields = ['foundation_rate', 'house_danger_frame_rate', 'house_latent_danger_frame_rate', 'house_latent_danger_frame_rate'];
            $where = '';
            foreach ($fields as $val) {
                if ($is_crack === 1) {
                    switch ($val) {
                        case 'foundation_rate':
                            $where .= ' (' . $val . ' like "%2%" or ' . $val . ' like "%3%" or ' . $val . ' like "%4%") ';
                            break;

                        case 'house_danger_frame_rate':
                            $where .= ' or (' . $val . ' != "" and ' . $val . ' != "[]" and ' . $val . ' not like "%1%") ';
                            break;

                        case 'house_danger_roof_rate':
                            $where .= ' or (' . $val . ' != "" and ' . $val . ' !="[]" and ' . $val . ' not like "%4%") ';
                            break;

                        case 'house_latent_danger_frame_rate':
                            $where .= ' or (' . $val . ' like "%1%") ';
                            break;
                    }
                } else {
                    switch ($val) {
                        case 'foundation_rate':
                            $where .= '(' . $val . ' not like "%2%" and ' . $val . ' not like "%3%" and ' . $val . ' not like "%4%")';
                            break;

                        case 'house_danger_frame_rate':
                            $where .= ' or (' . $val . ' = "" or ' . $val . ' = "[]" or ' . $val . ' like "%1%") ';
                            break;

                        case 'house_danger_roof_rate':
                            $where .= ' or (' . $val . ' = "" or ' . $val . ' ="[]" or ' . $val . ' like "%4%") ';
                            break;

                        case 'house_latent_danger_frame_rate':
                            $where .= ' or (' . $val . ' not like "%1%") ';
                            break;
                    }
                }
            }
            $rate = HouseRateModel::where($where)->column('house_id');
            $map[] = ['id', 'in', $rate];
            $description .= ' ' . ($is_crack === 1 ? '有' : '无') . '裂缝';
        }

        if ($rate_status >= 0) {
            switch ($rate_status) {
                case 0:
                    $whereAnd = 'rate_status != 1 and rate_status_set != 1';
                    break;

                case 1:
                    $whereAnd = 'rate_status = 1 and rate_status_set != 1';
                    break;

                case 2:
                    $whereAnd = 'rate_status_set = 1';
                    break;
            }
        }

        $house = $this->model::with(['area', 'district', 'houseRate', 'user', 'admin'])
            ->field('id, title, code, fileNumber, district_id, user_id, admin_id, contact, space, height, address, 
            house_usage, house_usage_other, is_owner_business, is_balcony, house_extension, house_change, rate_status_set')
            ->where($map)
            ->where($mapOr)
            ->where($whereAnd)
            ->order('id desc')
            ->select();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $HouseRateModel = new HouseRateModel;
        $StructureList = $HouseRateModel->getStructureList();
        $BasisTypeList = $HouseRateModel->getBasisTypeList();
        $GradeList = $HouseRateModel->getGradeList();
        $SuggestionList = $HouseRateModel->getSuggestionList();
        $houseUsageList = (new $this->model)->getHouseUsageList();
        $title = [
            'id' => '序号',
            'district_title' => '社区',
            'fileNumber' => '报告编号',
            'code' => '房屋编码',
            'title' => '房屋名称',
            'contact' => '责任人及联系方式',
            'structure' => '结构形式',
            'floor' => '层数',
            'space' => '建筑面积（平米）',
            'height' => '高度',
            'address' => '地址',
            'basis_type' => '基础类型',
            'site_rate' => '场地排查',
            'foundation_basis_rate' => '地基基础排查',
            'main_foundation_rate' => '主体结构排查',
            'house_usage' => '现有使用功能',
            'is_owner_business' => '是否经营性自建房',
            'house_extension' => '是不改建、加建、扩建',
            'is_incline_or_deposition' => '是否存在明显沉降房屋',
            'is_crack' => '是否存在结构件开裂等',
            'is_rust_eaten' => '是否存在钢筋锈蚀房屋的（疑似海砂房）',
            'is_balcony' => '是否板式悬挑阳台房屋',
            'house_safety_remark' => '其他需要说明的危险性问题',
            'rate_status_set' => '已拆除',
            'username' => '排查人',
            'admin' => '评级人',
            'final_rate' => '排查结论',
            'suggestion' => '处理建议',
        ];
        $yesOrNo = [0 => '', 1 => '是', 2 => '否']; // 2 否
        $titCol = 'A';
        foreach ($title as $key => $value) {
            // 单元格内容写入
            $sheet->setCellValue($titCol . '1', $value);
            $titCol++;
        }
        $row = 2; // 从第二行开始
        foreach ($house as $item) {
            $dataCol = 'A';
            foreach ($title as $key => $value) {
                $cellValue = '';
                // 单元格内容写入
                if (in_array($key, ['id', 'title', 'address', 'contact', 'district_title', 'height', 'fileNumber'])) {
                    $cellValue = $item[$key];
                }

                if ($key === 'username') {
                    $cellValue = $item->user_username ?? '';
                }

                if ($key === 'admin') {
                    $cellValue = $item->admin_username ?? '';
                }

                if ($key === 'house_usage' && !empty($item[$key])) {
                    $temp = '';
                    foreach ($item[$key] as $v) {
                        if ((int) $v === 9) {
                            $temp .= '、' . $item->house_usage_other;
                        } else {
                            $temp .= '、' . $houseUsageList[$v];
                        }
                    }

                    $cellValue = trim($temp, '、');
                }

                if ($key === 'code') {
                    $cellValue = $item[$key] . "\n";
                }

                if (in_array($key, ['is_balcony', 'is_owner_business', 'rate_status_set'])) {
                    $cellValue = $yesOrNo[$item[$key]];
                }

                if ($key === 'house_extension') {
                    $k = 0;
                    if (in_array($item->house_change, [1, 2], true) || array_intersect([1, 2, 3], $item['house_extension'])) {
                        $k = 1;
                    } else if ($item->house_change === 9 || array_intersect([9], $item['house_extension'])) {
                        $k = 2;
                    }

                    $cellValue = $yesOrNo[$k];
                }

                $space = explode('/', $item->space);
                if ($key === 'space') {
                    $cellValue = count($space) > 0 ? $space[0] : '';
                }

                if ($key === 'floor') {
                    $cellValue = count($space) > 1 ? $space[1] : '';
                }

                if ($item->house_rate) {
                    if ($key === 'basis_type' && (int) $item->house_rate[$key] > 0) {
                        if ((int) $item->house_rate[$key] === 9) {
                            $cellValue = $item->house_rate->basis_type_other;
                        } else {
                            $cellValue = $BasisTypeList[$item->house_rate[$key]];
                        }
                    }

                    if (in_array($key, ['site_rate', 'foundation_basis_rate', 'main_foundation_rate']) && (int)$item->house_rate[$key] > 0) {
                        $cellValue = $GradeList[$item->house_rate[$key]];
                    }

                    if ($key === 'suggestion' && is_array($item->house_rate[$key])) {
                        $temp = '';
                        foreach ($item->house_rate[$key] as $v) {
                            if ((int) $v === 9) {
                                $temp .= $item->house_rate->suggestion_other;
                            } else {
                                $temp .= $SuggestionList[$v];
                            }
                        }

                        $cellValue = trim($temp);
                    }

                    if ($key === 'structure' && $item->house_rate->structure > 0) {
                        $cellValue = $item->house_rate->structure === 9 ? $item->house_rate->structure_other : $StructureList[$item->house_rate->structure];
                    }

                    if ($key === 'final_rate') {
                        $cellValue = $final_rate_list[$item->house_rate->final_rate];
                    }

                    // 有无沉降
                    if ($key === 'is_incline_or_deposition' && $item->house_rate->foundation_rate) {
                        $k = 0;
                        if (in_array(5, $item->house_rate->foundation_rate, true)) {
                            $k = 1;
                        } else if (!empty($item->house_rate->foundation_rate)) {
                            $k = 2;
                        }

                        $cellValue = $yesOrNo[$k];
                    }

                    // 锈蚀
                    if ($key === 'is_rust_eaten') {
                        $k = 0;
                        if (($item->house_rate->house_danger_frame_rate && array_intersect($item->house_rate->house_danger_frame_rate, [1]))
                            || ($item->house_rate->house_danger_roof_rate && array_intersect($item->house_rate->house_danger_roof_rate, [4]))
                            || ($item->house_rate->house_latent_danger_frame_rate && array_intersect($item->house_rate->house_latent_danger_frame_rate, [2]))) {
                            $k = 1;
                        } else {
                            $k = 2;
                        }
                        $cellValue = $yesOrNo[$k];
                    }

                    // 裂缝
                    if ($key === 'is_crack') {
                        $k = 0;
                        if (($item->house_rate->foundation_rate && array_intersect($item->house_rate->foundation_rate, [2, 3, 4]))
                            || ($item->house_rate->house_danger_frame_rate && array_diff($item->house_rate->house_danger_frame_rate, [1]))
                            || ($item->house_rate->house_danger_roof_rate && array_diff($item->house_rate->house_danger_roof_rate, [4]))
                            || ($item->house_rate->house_latent_danger_frame_rate && array_intersect($item->house_rate->house_latent_danger_frame_rate, [1]))) {
                            $k = 1;
                        } else {
                            $k = 2;
                        }

                        $cellValue = $yesOrNo[$k];
                    }

                    if ($key === 'house_safety_remark') {
                        $cellValue = $item->house_rate->house_safety_remark;
                    }
                }

                $sheet->setCellValue($dataCol . $row, $cellValue);
                $dataCol++;
            }
            $row++;
        }

        $filename = '[房屋排查] ' . $area;
        if ($description !== '') {
            $filename .= ' [' . $description . '] ';
        }
        $filename .= ' (已排查)';
        // Save
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
//        $writer->save('./report/house.xlsx');
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IoFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

}
