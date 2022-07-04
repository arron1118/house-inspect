<?php

namespace app\common\library;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use app\common\model\House;
use PhpOffice\PhpWord\SimpleType\TextAlignment;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\ListItem;

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
        'lineHeight' => 1.5,
    ];

    protected $reportTitleStyle = [
        'bold' => true,
        'size' => 22,
    ];

    public function __construct($id)
    {
        $this->fontStyle = new Font();
        $this->fontStyle->setSize(14);
        $this->house = House::with(['houseRate', 'user', 'area'])->find($id);
        $this->HouseModel = new House;
        $this->selects = [
            'HouseUsageList' => $this->HouseModel->getHouseUsageList(),
            'RelatedDataList' => $this->HouseModel->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $this->HouseModel->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $this->HouseModel->getPeripherySafetyInvestigationList(),
            'HouseExtensionList' => $this->HouseModel->getHouseExtensionList(),
            'HouseChangeList' => $this->HouseModel->getHouseChangeList(),
            'HouseChangeFloorDataList' => $this->HouseModel->getHouseChangeFloorDataList(),
            'DistrictList' => $this->HouseModel->getDistrictList(),
        ];
        dump($this->selects);
        $this->reportTitle = $this->house->area_title . $this->selects['DistrictList'][$this->house->district] . '社区' . $this->house->title;
    }

    public function createReport()
    {
        $save_path = public_path() . '/report/';
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('SimSun');
        $phpWord->setDefaultFontSize(14);
        $section = $phpWord->addSection();

        $section->addTextBreak(10);

        $textRun = $section->addTextRun(array_merge($this->textRunStyle, []));
        $this->addText($textRun, $this->reportTitle, $this->reportTitleStyle);
        $this->addText($textRun, '<w:br />房屋结构安全隐患排查报告', $this->reportTitleStyle);

        $section->addTextBreak(15);

        $textRun = $section->addTextRun($this->textRunStyle);
        $this->addText($textRun, '深圳地质建设工程公司<w:br />', ['size' => 14]);
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
        $this->addText($textRun, '<w:br />张思明', ['size' => 15]);
        $this->addText($textRun, '<w:br />程振华', ['size' => 15]);
        $this->addText($textRun, '<w:br />吴  磊', ['size' => 15]);
        $section->addTextBreak(2);

        $textRun = $section->addTextRun();
        $textRun->addText('重要提示：<w:br />', $this->fontStyle);
        $this->addListItem($section, '报告未盖检测鉴定单位公章无效。');
        $this->addListItem($section, '报告无检测、编写、审核、审定人签字无效。');
        $this->addListItem($section, '报告发生涂改、换页或剪贴无效。');
        $this->addListItem($section, '未经检测鉴定单位同意，报告不得复制。');
        $this->addListItem($section, '如对检测鉴定报告有异议，应于收到报告之日起十五日内向检测鉴定单位提出，逾期视为认可检测鉴定结果。');
        $section->addLine(['width' => 450, 'weight' => 1, 'color' => '#ccc']);

        $textRun = $section->addTextRun(['lineHeight' => 1.5]);
        $textRun->addText('<w:br />检测鉴定单位地址：深圳市福田区燕南路98号<w:br />');
        $textRun->addText('联系人及电话：张思明(电话：19520791510)');

        $section = $phpWord->addSection();
        $textRun = $section->addTextRun(['pageBreakBefore' => true, 'alignment' => Jc::CENTER]);
        $textRun->addText('项目概况<w:br />', ['bold' => true]);
        $textRun = $section->addTextRun(['alignment' => Jc::START, 'lineHeight' => 1.5, 'indentation' => ['firstLine' => 2 * 14 * 20]]);
        $textRun->addText('为落实上级有关指示批示精神及《广东省住房和城乡建设厅关于深刻汲取湖南长沙“4·29”楼房坍塌事故教训立即'
            . '开展建筑安全隐患排查整治的紧急通知》要求，加强全市既有房屋及在建工程安全管理工作，切实保障人民群众生命财产安全，根据《广东省住房和城乡'
            . '建设厅关于深刻汲取湖南长沙“4·29”楼房坍塌事故教训立即开展建筑安全隐患排查整治的紧急通知》要求，结合《深圳市住房 和建设局转发广东省'
            . '住房和城乡建设厅关于深刻汲取河南郑州 “4.18”游泳馆坍塌事故教训切实加强公共场所建筑安全隐患排查整改的紧急通知》，按照《深圳市房屋建'
            . '筑隐患排查整治专项工作方案》对罗湖区清水河街道所有社区涉及房屋建筑及构筑物等进行安全隐患排查。'
        );
        $section->addTextBreak(2);
        $textRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        $textRun->addText('排查依据<w:br />', ['bold' => true]);
        $textRun = $section->addTextRun(['alignment' => Jc::START, 'lineHeight' => 1.5]);
        $textRun->addText('1. 《深圳市房屋建筑安全隐患排查整治专项工作方案》<w:br />');
        $textRun->addText('2. 《深圳市既有房屋结构安全隐患排查标准》SJG 41-2017');

        foreach ($this->infos as $key => $val) {
            foreach ($this->house[$key] as $value) {
                $file = public_path() . $value['image'];
                if (file_exists($file)) {
                    $section = $phpWord->addSection();
                    $textRun = $section->addTextRun($this->textRunStyle);
                    $textRun->addImage($file, [
                        'width' => 450,
                        'height' => 550,
                    ]);
                    $textRun->addText('<w:br />' . $value['description'], ['size' => 13], ['lineHeight' => 1.5]);
                }
            }
        }

        $writer = IOFactory::createWriter($phpWord);
        $writer->save($save_path . '房屋排查_' . $this->house->code . '.docx');
//
//        $section = $phpWord->createSection();
//        $section->addText('Hello World!');
//        $file = $save_path . 'HelloWorld.docx';
//        header("Content-Description: File Transfer");
//        header('Content-Disposition: attachment; filename="' . '房屋排查_' . $this->house->code . '.docx' . '"');
//        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
//        header('Content-Transfer-Encoding: binary');
//        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//        header('Expires: 0');
//        $writer->save("php://output");
    }

    protected function addTextRun($section, $style = [])
    {
        return $section->addTextRun(array_merge($this->textRunStyle, $style));
    }

    protected function addText($textRun, $text, $fStyle = [], $pStyle = [])
    {
        $textRun->addText($text, $fStyle, array_merge(['lineHeight' => 1.5], $pStyle));
    }

    protected function addListItem($section, $text, $style = [])
    {
        return $section->addListItem($text, 0, $this->fontStyle, array_merge(['listType' => ListItem::TYPE_NUMBER], $style), ['lineHeight' => 1.5]);
    }
}
