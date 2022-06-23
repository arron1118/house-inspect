<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class HouseRate extends Model
{
    protected $json = [
        'foundation_rate',
        'house_danger_frame_rate',
        'house_danger_roof_rate',
        'house_latent_danger_frame_rate',
    ];

    protected $jsonAssoc = true;

    public static function onAfterInsert($HouseRate)
    {

    }

    public function getStructureList()
    {
        return [1 => '剪力墙', 2 => '框架', 3 => '砖混', 4 => '排架', 5 => '钢结构', 6 => '空旷砖房', 7 => '木结构', 8 => '砖土瓦房', 9 => '其他'];
    }

    public function getBasisTypeList()
    {
        return [1 => '天然或独立基础', 2 => '条形基础', 3 => '片筏及箱型', 4 => '桩基础', 9 => '其他'];
    }

    public function getFoundationSafetyRateList()
    {
        return [1 => '危险', 2 => '潜在危险', 3 => '暂无危险'];
    }

    public function getFoundationRateList()
    {
        return [
            1 => '未发现任何沉降和位移迹象',
            2 => '室外地面存在裂缝，填充墙产生沉降裂缝，但主体結构未出现沉降裂缝或整体倾斜',
            3 => '砌体墙单条竖向裂缝宽度大于10mm，或单道墙体产生多条平行的竖向裂缝或斜向裂缝、其中最大裂缝宽度大于 0.5mm',
            4 => '承重墙体产生贯穿裂缝、混凝土柱产生环状裂缝、梁产生多条平行斜裂缝、剪力墙出现竖向裂缝',
            5 => '建筑物出现明显傾斜或结构缝处出现严重挤压损伤',
        ];
    }

    public function getHouseSafetyRateList()
    {
        return [1 => '危险', 2 => '潜在危险', 3 => '暂无危险'];
    }

    public function getHouseDangerFrameRateList()
    {
        return [
            1 => '较多柱、梁、墙和楼板因钢筋锈蚀造成胀裂，缝宽大于1mm或保护层剥落、钢筋外露',
            2 => '柱有竖向受压裂缝',
            3 => '柱一侧有宽度大于 1mm 的水平裂缝，另一侧混凝土压碎',
            4 => '梁跨中下宽上窄的竖向裂缝，裂缝向上延伸达梁高的2/3，且梁底缝宽大于0.5mm',
            5 => '梁端部有宽度大于 0.4mm 的斜向裂缝',
        ];
    }

    public function getHouseDangerRoofRateList()
    {
        return [
            1 => '较多柱、梁、墙和楼板因钢筋锈蚀造成胀裂，缝宽大于1mm或保护层剥落、钢筋外露',
            2 => '柱有竖向受压裂缝',
            3 => '柱一侧有宽度大于 1mm 的水平裂缝，另一侧混凝土压碎',
            4 => '梁跨中下宽上窄的竖向裂缝，裂缝向上延伸达梁高的2/3，且梁底缝宽大于0.5mm',
            5 => '梁端部有宽度大于 0.4mm 的斜向裂缝',
        ];
    }

    public function getHouseLatentDangerFrameRateList()
    {
        return [
            1 => '柱、梁、板出现裂缝，但为非结构受力裂缝',
            2 => '个别柱、梁和楼板因钢筋锈蚀造成胀裂或保护层剥落、钢筋外露',
        ];
    }

    public function getFinalRateList()
    {
        return [
            1 => 'A类建锁物，可继续使用，或仅需对损伤进行处理后可继续使用',
            2 => 'B类建筑物，可观察使用，但应对损伤进行处理，当房屋存在异常情况时应及时进行检测鉴定',
            3 => 'C1类建负物，需采取修繕，加固或拆除加建部分等措施后可消除安全隐患的房屋',
            4 => 'C2类建筑物，需进行检测鉴定的房屋',
            5 => 'C3类建筑物，需立即停止使用的房屋',
        ];
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
