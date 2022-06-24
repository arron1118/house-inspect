<?php
declare (strict_types=1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends AdminController
{
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

    public function download()
    {
//        $id = intval(input('id', 0));
//        $product = $this->model::where('id', $id)->find();
//        if(empty($product)){
//            $this->error('作品不存在');
//        }
//
        $photoOrigin = $this->model::field('url, sha1')->select();
        if (empty($photoOrigin)) {
            $this->error('作品不存在图片');
        }

//        $tmpFile = tempnam(sys_get_temp_dir(), 'photo_');
//        $dir = 'photo_';
        $tmpFile = '';
//        if (!is_dir($dir)) {
//            if (!mkdir($dir) && !is_dir($dir)) {
//                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
//            }
//        }
        $fileName = '房屋排查.zip';
        if (!is_file($fileName)) {
            dump($fileName);
            touch($fileName);
        }
//        if (!$tmpFile) {
//            $this->error('system error');
//        }

        $zip = new \ZipArchive();
        $zip->open($fileName, \ZipArchive::CREATE);
        dump($zip);
/*
        foreach ($photoOrigin as $k => $v) {
            $fileContent = file_get_contents($v['url']);
            $zip->addFromString(basename($v['sha1']), $fileContent);
        }

        $zip->close();

        $out = '房屋排查.zip';

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $out);
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);

        unlink($tmpFile);
        exit;*/
    }
}
