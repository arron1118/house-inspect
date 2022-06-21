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
        'house_usage',
        'purpose',
        'after_change',
        'crack_type',
        'incline_or_deposition_type',
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

    protected $jsonAssoc = true;

    public static function onBeforeWrite($house): void
    {
        if (isset($house->house_usage)) {
            $house->house_usage = array_values($house->house_usage);
        }
        if (isset($house->purpose)) {
            $house->purpose = array_values($house->purpose);
        }
        if (isset($house->after_change)) {
            $house->after_change = array_values($house->after_change);
        }
        if (isset($house->crack_type)) {
            $house->crack_type = array_values($house->crack_type);
        }
        if (isset($house->incline_or_deposition_type)) {
            $house->incline_or_deposition_type = array_values($house->incline_or_deposition_type);
        }
    }

    public static function onAfterDelete($house): void
    {
    }

    public function user()
    {
        return $this->belongsTo(User::class)->bind(['user_username' => 'username']);
    }

    public function houseRate()
    {
        return $this->hasOne(HouseRate::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class)->bind(['area_title' => 'title']);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class)->bind(['admin_username' => 'username']);
    }

    public function getDistrictList()
    {
        return [1 => '东方', 2 => '楼岗', 3 => '花果山', 4 => '松涛'];
    }

    public function getHouseUsageList()
    {
        return [1 => '厂房', 2 => '住宅', 3 => '商业', 4 => '商住', 5 => '办公', 9 => '其他'];
    }

    public function getDesignPaperList()
    {
        return [1 => '完整建筑结构设计图纸', 2 => '部分建筑结构设计图纸', 3 => '没有建筑结构设计图纸'];
    }

    public function getPeripheryEnvList()
    {
        return [1 => '大中型学校', 2 => '医院', 3 => '集市市场'];
    }

    public function getBalconyTypeList()
    {
        return [1 => '挑梁式阳台', 2 => '挑板式阳台', 9 => '其他'];
    }

    public function getPurposeList()
    {
        return [1 => '厂房', 2 => '住宅', 3 => '商业', 4 => '商住', 5 => '办公', 9 => '其他'];
    }

    public function getAfterChangeList()
    {
        return [1 => '厂房', 2 => '住宅', 3 => '商业', 4 => '商住', 5 => '办公', 9 => '其他'];
    }

    public function getCrackTypeList()
    {
        return [1 => '裂缝', 2 => '钢筋外露', 3 => '钢筋锈蚀', 9 => '其他'];
    }

    public function getInclineOrDepositionTypeList()
    {
        return [1 => '倾斜', 2 => '沉降', 9 => '其他'];
    }

    public function getExaminatorTypeList()
    {
        return [1 => '市级', 2 => '区级', 3 => '街道', 4 => '社区', 5 => '网络'];
    }
}
