<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class House extends Model
{
    public static function onAfterInsert($house): void
    {
    }

    public static function onAfterDelete($house): void
    {
    }

    public function houseExtension()
    {
        return $this->hasOne(HouseExtension::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function houseRate()
    {
        return $this->hasOne(HouseRate::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class)->bind(['area_title' => 'title']);
    }
}
