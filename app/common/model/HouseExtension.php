<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class HouseExtension extends Model
{
    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
