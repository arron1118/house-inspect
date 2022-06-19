<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\House as HouseModel;

class House extends ApiController
{
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
        $title = $this->params['title'] ?? '';
        $areaId = $this->params['area_id'] ?? 0;

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $map = [
            ['area_id', '=', $areaId],
            ['user_id', '=', $this->userInfo->id]
        ];

        if ($title) {
            $map[] = ['title', 'like', '%' . $title . '%'];
        }

        $this->returnData['code'] = 1;
        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::field('id, title, code, area_id, create_time')
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

            // 门牌信息
            $doorplate_info_image = $this->upload('doorplate_info_image');
            $params['doorplate_info'] = [];
            if ($doorplate_info_image) {
                foreach ($doorplate_info_image as $key => $val) {
                    $params['doorplate_info'][] = [
                        'image' => $val,
                        'description' => $params['doorplate_info_description'][$key],
                    ];
                }
            }
            // 外立面信息
            $house_info_image = $this->upload('house_info_image');
            $params['house_info'] = [];
            if ($house_info_image) {
                foreach ($house_info_image as $key => $val) {
                    $params['house_info'][] = [
                        'image' => $val,
                        'description' => $params['house_info_description'][$key],
                    ];
                }
            }

            // 户内信息
            $indoor_info_image = $this->upload('indoor_info_image');
            $params['indoor_info'] = [];
            if ($indoor_info_image) {
                foreach ($indoor_info_image as $key => $val) {
                    $params['indoor_info'][] = [
                        'image' => $val,
                        'description' => $params['indoor_info_description'][$key],
                    ];
                }
            }

            // 屋顶信息
            $roof_info_image = $this->upload('roof_info_image');
            $params['roof_info'] = [];
            if ($roof_info_image) {
                foreach ($roof_info_image as $key => $val) {
                    $params['roof_info'][] = [
                        'image' => $val,
                        'description' => $params['roof_info_description'][$key],
                    ];
                }
            }

            // 扩建信息
            $extension_info_image = $this->upload('extension_info_image');
            $params['extension_info'] = [];
            if ($extension_info_image) {
                foreach ($extension_info_image as $key => $val) {
                    $params['extension_info'][] = [
                        'image' => $val,
                        'description' => $params['extension_info_description'][$key],
                    ];
                }
            }

            // 扩建可拆除信息
            $extension_demountable_info_image = $this->upload('extension_demountable_info_image');
            $params['extension_demountable_info'] = [];
            if ($extension_demountable_info_image) {
                foreach ($extension_demountable_info_image as $key => $val) {
                    $params['extension_demountable_info'][] = [
                        'image' => $val,
                        'description' => $params['extension_demountable_info_description'][$key],
                    ];
                }
            }

            // 锈蚀信息
            $rust_eaten_info_image = $this->upload('rust_eaten_info_image');
            $params['rust_eaten_info'] = [];
            if ($rust_eaten_info_image) {
                foreach ($rust_eaten_info_image as $key => $val) {
                    $params['rust_eaten_info'][] = [
                        'image' => $val,
                        'description' => $params['rust_eaten_info_description'][$key],
                    ];
                }
            }

            // 裂缝信息
            $crack_info_image = $this->upload('crack_info_image');
            $params['crack_info'] = [];
            if ($crack_info_image) {
                foreach ($crack_info_image as $key => $val) {
                    $params['crack_info'][] = [
                        'image' => $val,
                        'description' => $params['crack_info_description'][$key],
                    ];
                }
            }

            // 其他信息
            $other_info_image = $this->upload('other_info_image');
            $params['other_info'] = [];
            if ($other_info_image) {
                foreach ($other_info_image as $key => $val) {
                    $params['other_info'][] = [
                        'image' => $val,
                        'description' => $params['other_info_description'][$key],
                    ];
                }
            }
            unset($params['doorplate_info_image'],
                $params['doorplate_info_description'],
                $params['house_info_image'],
                $params['house_info_description'],
                $params['indoor_info_description'],
                $params['indoor_info_image'],
                $params['roof_info_description'],
                $params['roof_info_image'],
                $params['extension_info_description'],
                $params['extension_info_image'],
                $params['extension_demountable_info_description'],
                $params['extension_demountable_info_image'],
                $params['rust_eaten_info_description'],
                $params['rust_eaten_info_image'],
                $params['crack_info_description'],
                $params['crack_info_image'],
                $params['other_info_image'],
                $params['other_info_description']
            );

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
        if ($request->isPost()) {
            $params = $request->param();
            $house = $this->model::find($id);
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
