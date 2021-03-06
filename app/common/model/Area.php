<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Area extends Model
{

    public function district()
    {
        return $this->hasMany(District::class);
    }

    public function house()
    {
        return $this->hasMany(House::class);
    }
}
