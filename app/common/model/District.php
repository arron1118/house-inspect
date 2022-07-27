<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class District extends Model
{
    public function area()
    {
        return $this->belongsTo(Area::class)->bind(['area_title' => 'title']);
    }

    public function house()
    {
        return $this->hasMany(House::class);
    }
}
