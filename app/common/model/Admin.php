<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Admin extends Model
{
    public function getLoginTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getLastLoginTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function house($value)
    {
        return $this->belongsTo(House::class);
    }
}
