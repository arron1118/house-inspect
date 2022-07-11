<?php

namespace app\common\library;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use app\common\model\House;
use app\common\model\HouseRate;
use PhpOffice\PhpWord\SimpleType\TextAlignment;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\ListItem;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Cell;

class Report
{

    /**
     * 照片信息
     * @var string[]
     */
    protected $infos = [
        'doorplate_info' => '门牌照片',
        'house_info' => '外立面照片',
        'indoor_info' => '户内照片',
        'roof_info' => '屋顶照片',
        'extension_info' => '加建照片',
        'rust_eaten_info' => '钢筋锈蚀照片',
        'crack_info' => '裂缝照片',
        'other_info' => '其他照片',
    ];

    protected $lineHeight = 1.5;

    protected $textRunStyle = [
        'alignment' => VerticalJc::CENTER,
        'textAlignment' => TextAlignment::CENTER,
        'lineHeight' => 1.2,
    ];

    protected $reportTitleStyle = [
        'bold' => true,
        'size' => 22,
        'name' => '黑体',
    ];

    public function __construct($id)
    {
        $this->fontStyle = new Font();
        $this->fontStyle->setSize(14);
        $this->house = House::with(['houseRate', 'user', 'area'])->find($id);
        $this->HouseModel = new House;
        $this->HouseRateModel = new HouseRate;
        $this->selects = [
            'HouseUsageList' => $this->HouseModel->getHouseUsageList(),
            'RelatedDataList' => $this->HouseModel->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $this->HouseModel->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $this->HouseModel->getPeripherySafetyInvestigationList(),
            'HouseExtensionList' => $this->HouseModel->getHouseExtensionList(),
            'HouseChangeList' => $this->HouseModel->getHouseChangeList(),
            'HouseChangeFloorDataList' => $this->HouseModel->getHouseChangeFloorDataList(),
            'DistrictList' => $this->HouseModel->getDistrictList(),
            'StructureList' => $this->HouseRateModel->getStructureList(),
            'BasisTypeList' => $this->HouseRateModel->getBasisTypeList(),
            'FoundationSafetyRateList' => $this->HouseRateModel->getFoundationSafetyRateList(),
            'FoundationRateList'=> $this->HouseRateModel->getFoundationRateList(),
            'HouseSafetyRateList' => $this->HouseRateModel->getHouseSafetyRateList(),
            'HouseDangerFrameRateList' => $this->HouseRateModel->getHouseDangerFrameRateList(),
            'HouseDangerRoofRateList' => $this->HouseRateModel->getHouseDangerRoofRateList(),
            'HouseLatentDangerFrameRateList' => $this->HouseRateModel->getHouseLatentDangerFrameRateList(),
            'FinalRateList' => $this->HouseRateModel->getFinalRateList(),
            'GradeList' => $this->HouseRateModel->getGradeList(),
        ];
        $this->reportTitle = $this->house->area_title . $this->selects['DistrictList'][$this->house->district] . '社区' . $this->house->title;
    }

    public function createReport()
    {
        $save_path = public_path() . '/report/';
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('SimSun');
        $phpWord->setDefaultFontSize(14);
        $section = $phpWord->addSection();
        $textRun = $section->addTextRun();
        $this->addText($textRun, '【' . $this->selects['DistrictList'][$this->house->district] . '社区】', ['name' => '黑体', 'size' => 12, 'bold' => true]);

        $section->addTextBreak(5);

        $textRun = $section->addTextRun(array_merge($this->textRunStyle, []));
        $this->addText($textRun, $this->reportTitle, $this->reportTitleStyle);
        $this->addText($textRun, '<w:br />房屋结构安全隐患排查报告', $this->reportTitleStyle);
//        $this->addText($textRun, '<w:br /><w:br />（报告编码：SDJ/03:JD / 2022）', ['size' => 12]);

        $section->addTextBreak(13);

        $textRun = $section->addTextRun($this->textRunStyle);
        $textRun->addImage(public_path() . '/static/images/dizhi-logo.png');
        $this->addText($textRun, '<w:br />深圳地质建设工程公司<w:br />', ['size' => 14]);
        $this->addText($textRun, date('Y年m月d日'), ['size' => 14]);

        $textRun = $this->addTextRun($section, ['pageBreakBefore' => true]);
        $this->addText($textRun, $this->reportTitle, $this->reportTitleStyle);
        $this->addText($textRun, '<w:br />房屋结构安全隐患排查报告', $this->reportTitleStyle);

        $section->addTextBreak();

        $textRun = $section->addTextRun($this->textRunStyle);
        $this->addText($textRun, '报告审定人：', ['bold' => true, 'size' => 15]);
        $this->addText($textRun, '孟薄萍', ['size' => 15]);
        $this->addText($textRun, '<w:br />（检测鉴定技术负责人）', ['size' => 10]);
        $this->addText($textRun, '<w:br />报告审核人：', ['bold' => true, 'size' => 15]);
        $this->addText($textRun, '龙行伟', ['size' => 15]);
        $this->addText($textRun, '<w:br />报告编写人：', ['bold' => true, 'size' => 15]);
        $this->addText($textRun, '张思明', ['size' => 15]);
        $this->addText($textRun, '<w:br />检测人员：', ['bold' => true, 'size' => 15]);
        $this->addText($textRun, '龙行伟', ['size' => 15]);
        $this->addText($textRun, '<w:br />' . str_repeat(' ', 10) . '张思明', ['size' => 15], []);
        $this->addText($textRun, '<w:br />' . str_repeat(' ', 10) . '程振华', ['size' => 15]);
        $this->addText($textRun, '<w:br />' . str_repeat(' ', 10) . '吴  磊', ['size' => 15]);
        $section->addTextBreak(2);

        $section = $phpWord->addSection();
        $textRun = $section->addTextRun();
        $textRun->addText('重要提示：<w:br />', $this->fontStyle);
        $this->addListItem($section, '报告未盖检测鉴定单位公章无效。');
        $this->addListItem($section, '报告无检测、编写、审核、审定人签字无效。');
        $this->addListItem($section, '报告发生涂改、换页或剪贴无效。');
        $this->addListItem($section, '未经检测鉴定单位同意，报告不得复制。');
        $this->addListItem($section, '如对检测鉴定报告有异议，应于收到报告之日起十五日内向检测鉴定单位提出，逾期视为认可检测鉴定结果。');
        $section->addLine(['width' => 450, 'weight' => 1, 'color' => '#cccccc']);

        $textRun = $section->addTextRun(['lineHeight' => 1.2]);
        $textRun->addText('<w:br />检测鉴定单位地址：深圳市福田区燕南路98号<w:br />', [], ['spaceBefore' => 1000]);
        $textRun->addText('联系人及电话：张思明(电话：19520791510)');

        $section = $phpWord->addSection();
        $textRun = $section->addTextRun(['pageBreakBefore' => true, 'alignment' => Jc::CENTER]);
        $textRun->addText('项目概况<w:br />', ['bold' => true]);
        $textRun = $section->addTextRun(['alignment' => Jc::START, 'lineHeight' => 1.5, 'indentation' => ['firstLine' => 2 * 14 * 20]]);
        $textRun->addText('为落实上级有关指示批示精神及《广东省住房和城乡建设厅关于深刻汲取湖南长沙“4·29”楼房坍塌事故教训立即'
            . '开展建筑安全隐患排查整治的紧急通知》要求，加强全市既有房屋及在建工程安全管理工作，切实保障人民群众生命财产安全，根据《广东省住房和城乡'
            . '建设厅关于深刻汲取湖南长沙“4·29”楼房坍塌事故教训立即开展建筑安全隐患排查整治的紧急通知》要求，结合《深圳市住房 和建设局转发广东省'
            . '住房和城乡建设厅关于深刻汲取河南郑州 “4.18”游泳馆坍塌事故教训切实加强公共场所建筑安全隐患排查整改的紧急通知》，按照《深圳市房屋建'
            . '筑隐患排查整治专项工作方案》对' . $this->house->area_title . $this->selects['DistrictList'][$this->house->district] . '社区涉及房屋建筑及构筑物等进行安全隐患排查。'
        );
        $section->addTextBreak(2);
        $textRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        $textRun->addText('排查依据<w:br />', ['bold' => true]);
        $textRun = $section->addTextRun(['alignment' => Jc::START, 'lineHeight' => 1.2]);
        $textRun->addText('1. 《深圳市房屋建筑安全隐患排查整治专项工作方案》<w:br />');
        $textRun->addText('2. 《深圳市既有房屋结构安全隐患排查技术标准》SJG 41-2017');

        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array('borderSize' => 0, 'borderColor' => '000000', 'cellMargin' => 80, 'alignment' => JcTable::CENTER, 'cellSpacing' => 0, 'width' => 6000, 'unit' => 'pct');
        $fancyTableFirstRowStyle = array('borderSize' => 1, 'borderColor' => '000000');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('bold' => true, 'size' => 12);
        $fancyTableCellFontStyle = ['size' => 11];
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $section = $phpWord->addSection(['pageBreakBefore' => true]);
        $table = $section->addTable($fancyTableStyleName);
        $table->addRow(500);
        $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'size' => 13])->addText($this->house->area_title . $this->selects['DistrictList'][$this->house->district] . '社区房屋建筑结构安全排查', $fancyTableFontStyle);
        $table->addRow(500);
        $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center'])->addText('一、房屋基本信息调查', $fancyTableFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋名称', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->title, $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋编码', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->code, $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('面积/层数', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->space, $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋地址', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->address, $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('建筑物高度', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->height, $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('所用人数', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->galleryful, $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('设计时间', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->design_time, $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('设计单位', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->design_company, $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('竣工日期', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->completion_time, $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('施工单位', $fancyTableCellFontStyle);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->build_company, $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋安全责任人及联系方式', $fancyTableCellFontStyle);
        $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText($this->house->contact, $fancyTableCellFontStyle);

        // 选中：
        $checkBoxYes = '<w:r><w:sym w:font="Wingdings" w:char="00FE"/></w:r>';
        // 未选中：
        $checkBoxNo = '<w:r><w:sym w:font="Wingdings" w:char="00A8"/></w:r>';
        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('现有使用功能', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        foreach ($this->selects['HouseUsageList'] as $key => $val) {
            $select = in_array($key, $this->house->house_usage, false) ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
            if ($key === 9) {
                $textRun->addText('(' . $this->house->house_usage_other . ')', $fancyTableCellFontStyle);
            }
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('结构形式', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        foreach ($this->selects['StructureList'] as $key => $val) {
            $select = isset($this->house->house_rate->structure) && $key === $this->house->house_rate->structure ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
            if ($key === 9) {
                $textRun->addText('(' . $this->house->house_rate->structure_other . ')', $fancyTableCellFontStyle);
            }
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('基础类型', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        foreach ($this->selects['BasisTypeList'] as $key => $val) {
            $select = $key === $this->house->house_rate->basis_type ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
            if ($key === 9) {
                $textRun->addText('(' . $this->house->house_rate->basis_type_other . ')', $fancyTableCellFontStyle);
            }
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('相关资料', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        foreach ($this->selects['RelatedDataList'] as $key => $val) {
            $select = $key === $this->house->related_data ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $table->addRow(500);
        $cell = $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $textRun->addText('二、房屋使用安全排查  ', $fancyTableFontStyle);
        foreach ($this->selects['HouseSafetyInvestigationList'] as $key => $val) {
            $select = $key === $this->house->house_safety_investigation ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('使用功能', $fancyTableCellFontStyle);
        $cell = $table->addCell(3000, ['valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_usage_change === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('无改变 ', $fancyTableCellFontStyle);
        $select = $this->house->is_usage_change === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('由（' . $this->house->is_usage_change_from . '）改变为（' . $this->house->is_usage_change_to . '）', $fancyTableCellFontStyle);

        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('使用荷载', $fancyTableCellFontStyle);
        $cell = $table->addCell(3000, ['valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_usage_onus === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('无明显增加 ', $fancyTableCellFontStyle);
        $select = $this->house->is_usage_onus === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('明显增加 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('有悬挑阳台', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_balcony === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('是 ', $fancyTableCellFontStyle);
        $select = $this->house->is_balcony === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('否 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('加建', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        foreach ($this->selects['HouseExtensionList'] as $key => $val) {
            $select = in_array($key, $this->house->house_extension, false) ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('改建', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        foreach ($this->selects['HouseChangeList'] as $key => $val) {
            $select = $key === $this->house->house_change ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            if ($key === 1) {
                $textRun->addText('拆除（' . $this->house->house_change_floor . '）层的（', $fancyTableCellFontStyle);
                foreach ($this->selects['HouseChangeFloorDataList'] as $k => $v) {
                    $select = in_array($k, $this->house->house_change_floor_data, false) ? $checkBoxYes : $checkBoxNo;
                    $textRun->addText($select, $fancyTableCellFontStyle);
                    $textRun->addText($v . ' ', $fancyTableCellFontStyle);
                }
                $textRun->addText('） ', $fancyTableCellFontStyle);
            } else {
                $textRun->addText($val . ' ', $fancyTableCellFontStyle);
            }
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('灾害影响', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $textRun->addText('房屋是否受过火灾等外力损害 ', $fancyTableCellFontStyle);
        $select = $this->house->is_house_has_scourge === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('是 ', $fancyTableCellFontStyle);
        $select = $this->house->is_house_has_scourge === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('否 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $cell = $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $textRun->addText('三、房屋周边环境安全排查  ', $fancyTableFontStyle);
        foreach ($this->selects['PeripherySafetyInvestigationList'] as $key => $val) {
            $select = $key === $this->house->periphery_safety_investigation ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋周边是否有开挖土方', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_periphery_excavation === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('是 ', $fancyTableCellFontStyle);
        $select = $this->house->is_periphery_excavation === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('否 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋周边是否有地铁、管廊、隧道等工程施工', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_periphery_construction === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('是 ', $fancyTableCellFontStyle);
        $select = $this->house->is_periphery_construction === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('否 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋周边是否有山体或边坡', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_periphery_hillside === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('是 ', $fancyTableCellFontStyle);
        $select = $this->house->is_periphery_hillside === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('否 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('房屋周边地面是否有塌陷', $fancyTableCellFontStyle);
        $cell = $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun();
        $select = $this->house->is_periphery_sink === 1 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('是 ', $fancyTableCellFontStyle);
        $select = $this->house->is_periphery_sink === 2 ? $checkBoxYes : $checkBoxNo;
        $textRun->addText($select, $fancyTableCellFontStyle);
        $textRun->addText('否 ', $fancyTableCellFontStyle);

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('其他说明', $fancyTableCellFontStyle);
        $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center'])->addText($this->house->remark, $fancyTableCellFontStyle);

        $table->addRow(500);
        $cell = $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        $textRun->addText('四、地基基础安全排查  ', $fancyTableFontStyle);
        foreach ($this->selects['FoundationSafetyRateList'] as $key => $val) {
            $select = $key === $this->house->house_rate->foundation_safety_rate ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }
        $table->addRow(500);
        $cell = $table->addCell(6500, ['gridSpan' => 4, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2, 'spaceBefore' => 20]);
        foreach ($this->selects['FoundationRateList'] as $key => $val) {
            $select = isset($this->house->house_rate->foundation_rate) && in_array($key, $this->house->house_rate->foundation_rate, false) ? $checkBoxYes : $checkBoxNo;
            $key > 1 ? $textRun->addText('<w:br />' . $select, $fancyTableCellFontStyle) : $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val, $fancyTableCellFontStyle);
        }

        $table->addRow(500);
        $cell = $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2, 'spaceBefore' => 20]);
        $textRun->addText('五、上部结构安全排查  ', $fancyTableFontStyle);
        foreach ($this->selects['HouseSafetyRateList'] as $key => $val) {
            $select = $key === $this->house->house_rate->house_safety_rate ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $row = $table->addRow(500);
        $row->addCell(1300, ['vMerge' => 'restart', 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('危险', $fancyTableCellFontStyle);
        $cell = $row->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        $textRun->addText('混凝土构件<w:br />', array_merge($fancyTableCellFontStyle, ['bold' => true]));
        foreach ($this->selects['HouseDangerFrameRateList'] as $key => $val) {
            $select = isset($this->house->house_rate->house_danger_frame_rate) && in_array($key, $this->house->house_rate->house_danger_frame_rate, false) ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . '<w:br />', $fancyTableCellFontStyle);
        }
        $textRun->addText('<w:br />悬挑梁、板（雨篷）', array_merge($fancyTableCellFontStyle, ['bold' => true]));
        foreach ($this->selects['HouseDangerRoofRateList'] as $key => $val) {
            $select = isset($this->house->house_rate->house_danger_roof_rate) && in_array($key, $this->house->house_rate->house_danger_roof_rate, false) ? $checkBoxYes : $checkBoxNo;
            $textRun->addText('<w:br />' . $select, $fancyTableCellFontStyle);
            $textRun->addText($val, $fancyTableCellFontStyle);
        }

        $row = $table->addRow(500);
        $row->addCell(1300, ['vMerge' => 'restart', 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('潜在危险', $fancyTableCellFontStyle);
        $cell = $row->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        $textRun->addText('混凝土构件', array_merge($fancyTableCellFontStyle, ['bold' => true]));
        foreach ($this->selects['HouseLatentDangerFrameRateList'] as $key => $val) {
            $select = isset($this->house->house_rate->house_latent_danger_frame_rate) && in_array($key, $this->house->house_rate->house_latent_danger_frame_rate, false) ? $checkBoxYes : $checkBoxNo;
            $textRun->addText('<w:br />' . $select, $fancyTableCellFontStyle);
            $textRun->addText($val, $fancyTableCellFontStyle);
        }

        $table->addRow(500);
        $table->addCell(1300, $fancyTableCellStyle)->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('其他需要说明的危险性问题', $fancyTableCellFontStyle);
        $table->addCell(6500, ['gridSpan' => 3, 'valign' => 'center'])->addText($this->house->house_rate->house_safety_remark, $fancyTableCellFontStyle);

        $row = $table->addRow(500);
        $row->addCell(1300, ['vMerge' => 'restart', 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('场地排查', $fancyTableCellFontStyle);
        $cell = $row->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        foreach ($this->selects['GradeList'] as $key => $val) {
            $select = $key === $this->house->house_rate->site_rate ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $row = $table->addRow(500);
        $row->addCell(1300, ['vMerge' => 'restart', 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('地基基础排查', $fancyTableCellFontStyle);
        $cell = $row->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        foreach ($this->selects['GradeList'] as $key => $val) {
            $select = $key === $this->house->house_rate->foundation_basis_rate ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $row = $table->addRow(500);
        $row->addCell(1300, ['vMerge' => 'restart', 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('主体结构排查', $fancyTableCellFontStyle);
        $cell = $row->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        foreach ($this->selects['GradeList'] as $key => $val) {
            $select = $key === $this->house->house_rate->main_foundation_rate ? $checkBoxYes : $checkBoxNo;
            $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val . ' ', $fancyTableCellFontStyle);
        }

        $row = $table->addRow(500);
        $row->addCell(1300, ['vMerge' => 'restart', 'valign' => 'center'])->addTextRun(['alignment' => 'center', 'lineHeight' => 1.2])->addText('排查结论', $fancyTableCellFontStyle);
        $cell = $row->addCell(6500, ['gridSpan' => 3, 'valign' => 'center']);
        $textRun = $cell->addTextRun(['lineHeight' => 1.2]);
        foreach ($this->selects['FinalRateList'] as $key => $val) {
            $select = $key === $this->house->house_rate->final_rate ? $checkBoxYes : $checkBoxNo;
            $key > 1 ? $textRun->addText('<w:br />' . $select, $fancyTableCellFontStyle) : $textRun->addText($select, $fancyTableCellFontStyle);
            $textRun->addText($val, $fancyTableCellFontStyle);
        }

        $section = $phpWord->addSection(['pageBreakBefore' => true]);
        // 现场照片
        $table = $section->addTable($fancyTableStyleName);
        $table->addRow(500);
        $table->addCell(8500, ['gridSpan' => 4, 'valign' => 'center'])
            ->addTextRun(['alignment' => 'center', 'size' => 13])
            ->addText('房屋外观、安全隐患及操作照片', $fancyTableFontStyle);
        $i = 0;
        foreach ($this->infos as $key => $val) {
            foreach ($this->house[$key] as $value) {
                if ($i % 2 === 0) {
                    $row = $table->addRow();
                }
                $file = public_path() . $value['image'];
                if (file_exists($file)) {
                    $cell = $row->addCell();
                    $textRun = $cell->addTextRun($this->textRunStyle);
                    $textRun->addImage($file, [
                        'width' => 230,
                        'height' => 280,
                    ]);
                    $textRun->addText('<w:br />' . $value['description'], ['size' => 13], ['lineHeight' => 1.2]);
                }

                $i++;
            }
        }

        $writer = IOFactory::createWriter($phpWord);
//        $writer->save($save_path . $this->house->title . '_' . $this->house->code . '.docx');
//
//        $section = $phpWord->createSection();
//        $section->addText('Hello World!');
//        $file = $save_path . 'HelloWorld.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $this->house->title . '_' . $this->house->code . '.docx' . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $writer->save("php://output");
    }

    protected function addTextRun($section, $style = [])
    {
        return $section->addTextRun(array_merge($this->textRunStyle, $style));
    }

    protected function addText($textRun, $text, $fStyle = [], $pStyle = [])
    {
        $textRun->addText($text, $fStyle, array_merge(['lineHeight' => 1.2], $pStyle));
    }

    protected function addListItem($section, $text, $style = [])
    {
        return $section->addListItem($text, 0, $this->fontStyle, array_merge(['listType' => ListItem::TYPE_NUMBER], $style), ['lineHeight' => 1.2]);
    }
}
