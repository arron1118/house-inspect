<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class HouseRate extends Model
{
    public static function onAfterInsert($HouseRate)
    {

    }

    public function getPlaceRateList()
    {
        return [1 => 'a类', 2 => 'b类', 3 => 'c类'];
    }

    public function getFoundationRateList()
    {
        return [1 => 'a类', 2 => 'b类', 3 => 'c类'];
    }

    public function getMainRateList()
    {
        return [1 => 'a类', 2 => 'b类', 3 => 'c类'];
    }

    public function getHouseSafetyRateList()
    {
        return [1 => 'a类', 2 => 'b类', 3 => 'c1类', 4 => 'c2类', 5 => 'c3类'];
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
