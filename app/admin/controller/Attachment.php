<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\common\library\Zipdown;
use think\Request;
use app\common\controller\AdminController;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends AdminController
{

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

    public function initialize()
    {
        parent::initialize();

        $this->model = AttachmentModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
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
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
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
        //
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    public function downImages()
    {
        $ids = '310,599,1052,1290,1333,1803,1902,2057,2197,2243';
        $fields = 'code, ' . implode(',', array_keys($this->infos));
        $house = \app\common\model\House::where('id', 'in', $ids)->field($fields)->select()->toArray();
        $out = '房屋排查_' . date('Y_m_d_H_i_s') . '.zip';
        $zip = new Zipdown();
        $zip->zip_file($house, $out);
    }

    public function download()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
//        $photoOrigin = $this->model::field('url, sha1')->limit(50)->select();

        $ids = '310,599,1052,1290,1333,1803,1902,2057,2197,2243';
        $fields = 'code, ' . implode(',', array_keys($this->infos));
        $house = \app\common\model\House::where('id', 'in', $ids)->column($fields);

        $tmpFile = tempnam('/tmp', 'fangwupaicha');

        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE);
        foreach ($house as $k => $v) {
            foreach ($this->infos as $key => $val) {
                if ($v[$key]) {
                    foreach ($v[$key] as $value) {
                        $file = public_path() . $value['image'];
                        if (file_exists($file)) {
//                            $fileContent = file_get_contents($file);
//                            $zip->addFromString($v['code'] . '/' . $val . '/' . basename($file), $fileContent);
                            $zip->addFile($file, $v['code'] . '/' . $val . '/' . basename($file));
                        }
                    }
                }
            }
        }

        $zip->close();

        $out = '房屋排查_' . date('Y_m_d_H_i_s') . '.zip';
        dump($zip);
        dump(filesize($tmpFile));
        dump(file_exists($tmpFile));

        $fp = fopen($tmpFile, 'rb');

        header('Content-Type: application/octet-stream');
        header('Content-disposition: attachment; filename=' . $out);
        header('Content-Length: ' . filesize($tmpFile));
        header('Accept-Ranges: bytes');
        header('Accept-Length: ' . filesize($tmpFile));
        header('Content-Transfer-Encoding: binary');

        ob_clean();
        ob_flush();
        flush();
        $buffer = 4096;
        while (!feof($fp)) {
            echo fread($fp, $buffer);
        }
        fclose($fp);
//        readfile($tmpFile);
//        unlink($tmpFile);

        exit;

//        return download($tmpFile, $out);
    }
}
