<?php
// 应用公共文件

function createToken($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

if (!function_exists('readExcel')) {
    function readExcel($file, $appendColumns = [])
    {
        $read = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($file->extension()));
        $spreadsheet = $read->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $log = [];
        $districtList = array_flip((new \app\common\model\House())->getDistrictList());

        for ($i = 2; $i < $highestRow; $i++) {
//            $title = $sheet->getCellByColumnAndRow(2, $i)->getValue();
            $code = $sheet->getCellByColumnAndRow(3, $i)->getValue();
            $district = trim($sheet->getCellByColumnAndRow(6, $i)->getValue());

            if ($district !== '') {
                $district = $districtList[$district];
            }

//            $height = $sheet->getCellByColumnAndRow(7, $i)->getValue();
//            $space = $sheet->getCellByColumnAndRow(11, $i)->getValue() . '/' . $sheet->getCellByColumnAndRow(9, $i)->getValue();
//            $address = $sheet->getCellByColumnAndRow(4, $i)->getValue() . $sheet->getCellByColumnAndRow(13, $i)->getValue();
//            $completion_time = $sheet->getCellByColumnAndRow(7, $i)->getValue();

            $log[] = array_merge($appendColumns, [
//                'title' => trim($title),
                'code' => trim($code),
                'district' => trim($district ?? ''),
//                'height' => trim($height ?? ''),
//                'space' => trim($space),
//                'address' => trim($address),
//                'completion_time' => trim($completion_time),
            ]);
        }

        return $log;
    }
}
