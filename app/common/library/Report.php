<?php

namespace app\common\library;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use app\common\model\House;
use PhpOffice\PhpWord\SimpleType\TextAlignment;
use PhpOffice\PhpWord\SimpleType\VerticalJc;

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

    public function createReport($id)
    {
        $house = House::with(['houseRate', 'user', 'area'])->find($id);
        $model = new House;
        $selects = [
            'HouseUsageList' => $model->getHouseUsageList(),
            'RelatedDataList' => $model->getRelatedDataList(),
            'HouseSafetyInvestigationList' => $model->getHouseSafetyInvestigationList(),
            'PeripherySafetyInvestigationList' => $model->getPeripherySafetyInvestigationList(),
            'HouseExtensionList' => $model->getHouseExtensionList(),
            'HouseChangeList' => $model->getHouseChangeList(),
            'HouseChangeFloorDataList' => $model->getHouseChangeFloorDataList(),
        ];
        dump($selects);
        $save_path = public_path() . '/report/';
        $phpWord = new PhpWord();
        $textRunStyle = [
            'alignment' => VerticalJc::CENTER,
            'textAlignment' => TextAlignment::CENTER,
        ];
        $section = $phpWord->addSection([
//            'marginTop' => 600,
//            'vAlign' => VerticalJc::CENTER,
        ]);
        $section->addTextBreak(10);
        $textrun = $section->addTextRun(array_merge($textRunStyle, ['spaceBefore' => 2000, 'spaceAfter' => 6000]));
        $section->addHeader();
        $textrun->addText($house['area_title'] . $house['title'], [
            'bold' => true,
            'size' => 24,
        ]);

//        $section->addTextBreak(10);
        $textrun = $section->addTextRun($textRunStyle);
        $textrun->addText("深圳地质建设工程公司\n\r");
        $section->addTextBreak();
        $textrun = $section->addTextRun($textRunStyle);
        $textrun->addText(date('Y年m月d日'));
        $section->addTextBreak(2);


        foreach ($this->infos as $key => $val) {
            foreach ($house[$key] as $value) {
                $file = public_path() . $value['image'];
                if (file_exists($file)) {
                    $section = $phpWord->addSection();
                    $textrun = $section->addTextRun([
                        'alignment' => VerticalJc::CENTER,
                        'textAlignment' => TextAlignment::CENTER,
                    ]);
                    $textrun->addImage($file, [
                        'width' => 450,
                        'height' => 550,
                    ]);
                    $section->addTextBreak();
                    $section->addText($value['description']);

                }
            }
        }

        $writer = IOFactory::createWriter($phpWord);
        $writer->save($save_path . '房屋排查_' . $house['code'] . '.docx');
//
//        $section = $phpWord->createSection();
//        $section->addText('Hello World!');
//        $file = $save_path . 'HelloWorld.docx';
//        header("Content-Description: File Transfer");
//        header('Content-Disposition: attachment; filename="' . '房屋排查_' . $house['code'] . '.docx' . '"');
//        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
//        header('Content-Transfer-Encoding: binary');
//        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//        header('Expires: 0');
//        $writer->save("php://output");
    }
}
