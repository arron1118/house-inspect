<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    public function getLoginTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getLastLoginTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function house()
    {
        return $this->hasMany(House::class);
    }
}
