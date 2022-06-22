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
        'house_extension',
        'house_change_floor_data',
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

//    public function getDistrictAttr($value)
//    {
//        return $this->getDistrictList()[$value];
//    }

    public function getDistrictList()
    {
        return [1 => '东方', 2 => '楼岗', 3 => '花果山', 4 => '松涛'];
    }

    public function getHouseUsageList()
    {
        return [1 => '厂房', 2 => '住宅', 3 => '商业', 4 => '商住', 5 => '办公', 9 => '其他'];
    }

    public function getRelatedDataList()
    {
        return [1 => '完整建筑结构设计图纸', 2 => '部分建筑结构设计图纸', 3 => '排查或鉴定报告', 4 => '无图纸或报告'];
    }

    public function getHouseSafetyInvestigationList()
    {
        return [1 => '危险', 2 => '潜在危险', 3 => '暂无危险'];
    }

    public function getPeripherySafetyInvestigationList()
    {
        return [1 => '危险', 2 => '潜在危险', 3 => '暂无危险'];
    }

    public function getStructureList()
    {
        return [1 => '剪力墙', 2 => '框架', 3 => '砖混', 4 => '排架', 5 => '钢结构', 6 => '空旷砖房', 7 => '木结构', 8 => '砖土瓦房', 9 => '其他'];
    }

    public function getBasisTypeList()
    {
        return [1 => '天然或独立基础', 2 => '条形基础', 3 => '片筏及箱型', 4 => '桩基础', 9 => '其他'];
    }

    public function getHouseExtensionList()
    {
        return [1 => '房屋顶部有加建', 2 => '房屋底层有夹层加建', 3 => '房屋底层开挖地下室', 9 => '无'];
    }

    public function getHouseChangeList()
    {
        return [1 => '有', 2 => '存在新增洞口', 9 => '无'];
    }

    public function getHouseChangeFloorDataList()
    {
        return [1 => '承重墙', 2 => '梁', 3 => '板'];
    }
}
