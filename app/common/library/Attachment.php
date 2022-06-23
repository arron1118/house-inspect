<?php
declare (strict_types = 1);

namespace app\common\library;

use think\Image;
use think\db\exception\DbException;
use think\exception\FileException;
use think\facade\Config;
use think\facade\Filesystem;
use app\common\model\Attachment as AttachmentModel;
use app\common\model\Config as SiteConfig;

class Attachment
{

    /**
     * @param string $fileName  上传的文件名称
     * @param string $savePath
     * @param false $watermark
     * @return false|string[]|void
     */
    public function upload($file, string $savePath = 'attachment', bool $watermark = false, $params = [])
    {
        try {
            if (!$file) {
                return false;
            }

            $filesystem = Config::get('filesystem.default');
            $disks = Config::get('filesystem.disks');

            // 保存上传文件
            $saveName = Filesystem::putFile($savePath, $file);
            $saveName = $disks[$filesystem]['url'] . '/' . str_replace('\\', '/', $saveName);


            if ($watermark) {  // 添加水印
                $site_watermark_type = SiteConfig::getByKeyword('site_watermark_type');
                $img = getcwd() . $saveName;
                switch ($site_watermark_type->content) {
                    case 'image':   // 添加图片水印
                        $site_watermark_image = SiteConfig::getByKeyword('site_watermark_image');
                        Image::open($img)->water(getcwd() . $site_watermark_image->value, \think\Image::WATER_SOUTHEAST, 30)->save($img);
                        break;

                    case 'text':    // 添加文字水印
                        $site_watermark_text = SiteConfig::getByKeyword('site_watermark_text');
                        Image::open($img)->text($site_watermark_text->value, getcwd() . '/static/fonts/simsun.ttc', 28, '#00000000', Image::WATER_SOUTHEAST, -20, 50)->save($img);
                        break;
                }
            }

            try {
                // 保存上传记录
                $data = [
                    'url' => $saveName,
                    'mime_type' => $file->getOriginalMime(),
                    'file_extension' => $file->getOriginalExtension(),
                    'storage' => 'local',
                    'sha1' => $file->sha1(),
                    'file_name' => str_replace('.' . $file->getOriginalExtension(), '', $file->getOriginalName()),
                    'file_size' => $file->getSize(),
                ];
                $data = array_merge($data, $params);
                AttachmentModel::create($data);
            } catch (DbException $dbException) {
                exit($dbException);
            }

            return [
                'savePath' => $saveName
            ];
        } catch (FileException $e) {
            dump($e);
        }
    }

}
