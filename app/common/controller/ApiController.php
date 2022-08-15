<?php
declare (strict_types = 1);

namespace app\common\controller;

use app\common\library\Aes;
use app\common\library\Attachment;
use app\common\model\Config as SiteConfig;
use app\common\model\User as UserModel;
use think\Request;
use app\BaseController;

class ApiController extends BaseController
{
    /**
     * 无需登录的接口
     * @var string[]
     */
    protected $noNeedLogin = ['login', 'getAesEncodeData', 'getAesDecodeData', 'getSiteInfo'];

    /**
     * 用户信息
     * @var null
     */
    protected $userInfo = null;

    /**
     * 用户模型
     * @var null
     */
    protected $UserModel = null;

    /**
     * 当前模块的模型
     * @var null
     */
    protected $model = null;

    /**
     * 请求参数
     * @var array
     */
    protected $params = [];

    /**
     * api token
     * @var null
     */
    protected $token = null;

    /**
     * 加密/解密模型
     * @var null
     */
    protected $aes = null;

    /**
     * 是否加密传输
     * @var bool
     */
    protected $needAes = false;

    /**
     * 接口返回数据
     * @var array
     */
    protected $returnData = [
        'code' => 0,
        'msg' => '未知错误',
        'data' => [],
    ];

    /**
     * 初始化数据
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function initialize()
    {
        parent::initialize();

        $this->aes = new Aes();
        $this->UserModel = UserModel::class;
        $this->params = $this->getRequestParams();
        $this->token = $this->params['token'] ?? null;
        $action = $this->request->action();

        if (!in_array($action, $this->noNeedLogin, true)) {
            $this->returnData['code'] = 5003;
            if (!$this->token) {
                $this->returnApiData('权限不足：未登录');
            }

            $this->userInfo = UserModel::where('token', $this->token)->find();
            if (!$this->userInfo) {
                $this->returnApiData('用户不存在或未登录');
            }

            if (!$this->userInfo->getData('status')) {
                $this->returnApiData(lang('Account is locked'));
            }

            if ($this->userInfo->token_expire_time < time()) {
                $this->returnApiData('登录过期，请重新登录');
            }

            $this->returnData['code'] = 1;
        }
    }

    public function getUserInfo()
    {
        return $this->userInfo->hidden(['password'])->toArray();
    }

    protected function isLogin()
    {
        return $this->userInfo;
    }

    /**
     * 输出结果集并退出程序
     *
     * @param string $msg
     */
    protected function returnApiData($msg = ''): void
    {
        if ($msg !== '') {
            $this->returnData['msg'] = $msg;
        }

        if ($this->returnData['data'] && $this->needAes) {
            $this->returnData['data'] = $this->aes->aesEncode(json_encode($this->returnData['data'], JSON_UNESCAPED_UNICODE));
        }

        response($this->returnData, 200, [], 'json')->send();
        exit;
    }

    /**
     * 获取加密的请求数据
     *
     * @param string $param
     * @return mixed
     */
    protected function getRequestParams($param = 'params')
    {
        if ($this->needAes) {
            $data = $this->request->param($param);
//        if (!$data) {
//            $this->returnApiData('未提供正确的参数');
//        }

            if ($data) {
                return $this->paramFilter(json_decode($this->aes->aesDecode($data), true));
            }

            return [];
        }

        return $this->paramFilter($this->request->param());
    }

    /**
     * 参数过滤
     *
     * @param $param
     * @return mixed
     */
    protected function paramFilter($param)
    {
        if (isset($param['page'])) {
            $param['page'] = (int) $param['page'];
        }

        if (isset($param['limit'])) {
            $param['limit'] = (int) $param['limit'];
        }

        return $param;
    }

    /**
     * 获取加密后的用户数据
     */
    public function getAesEncodeData()
    {
        $this->returnData['data'] = $this->request->param();
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = 'success';
        $this->returnApiData();
    }

    /**
     * 获取解密后的请求数据
     *
     * @param string $param
     * @return \think\response\Json
     */
    public function getAesDecodeData($param = 'params')
    {
        $this->returnData['data'] = $this->getRequestParams($param);
        $this->returnData['msg'] = 'success';
        $this->returnData['code'] = 1;
        return json($this->returnData);
    }

    /**
     * 上传
     *
     * @param string $fileName
     * @return array
     */
    public function upload($fileName = 'file')
    {
        $site_watermark_engine = SiteConfig::getByKeyword('site_watermark_engine');

        $files = request()->file($fileName);
        $this->returnData['files'] = $files;
        if ($files) {
            foreach ($files as $file) {
                $upload = (new Attachment())->upload($file, 'attachment', (bool) (int) $site_watermark_engine->value, ['user_id' => $this->userInfo->id]);

                if (!$upload) {
                    $this->returnApiData('上传失败: 未找到文件');
                }

                $this->returnData['data'][] = $upload['savePath'];
            }
        }
//
//        return $saveName;
//        $file = request()->file($fileName);
//        $upload = (new Attachment())->upload($file, 'attachment', (bool) (int) $site_watermark_engine->value);
//
//        if (!$upload) {
//            $this->returnApiData('上传失败: 未找到文件');
//        }

        $this->returnData['code'] = 1;
        $this->returnApiData(lang('Done'));
    }

    /**
     * @param $method
     * @param $args
     */
    public function __call($method, $args)
    {
        $this->returnApiData('错误的请求[方法不存在]：' . $method);
    }
}
