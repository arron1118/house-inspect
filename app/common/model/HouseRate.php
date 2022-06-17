<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class HouseRate extends Model
{
    public static function onBeforeInsert($HouseRate)
    {
    }

    public function getTypeList()
    {
        return ['现场', '裂缝', '无法进入'];
    }

    public function getImagesAttr($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getImageTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getCrackImagesAttr($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getCrackImageTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getRefuseImagesAttr($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getRefuseImageTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
