<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class House extends Model
{
    protected $json = [
        'doorplate_info',
        'house_info',
        'indoor_info',
        'roof_info',
        'extension_info',
        'extension_demountable_info',
        'rust_eaten_info',
        'crack_info',
        'other_info',
    ];

    public static function onAfterInsert($house): void
    {
    }

    public static function onAfterDelete($house): void
    {
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
