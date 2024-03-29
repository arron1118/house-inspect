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
//        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $log = [];
//        $districtList = array_flip((new \app\common\model\House())->getDistrictList());

        for ($i = 2; $i < $highestRow; $i++) {
            $id = $sheet->getCellByColumnAndRow(1, $i)->getValue();
            $pid = $sheet->getCellByColumnAndRow(2, $i)->getValue();
            $title = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
            $level = $sheet->getCellByColumnAndRow(4, $i)->getValue();

//            if ($district !== '') {
//                $district = $districtList[$district];
//            }

//            $height = $sheet->getCellByColumnAndRow(7, $i)->getValue();
//            $space = $sheet->getCellByColumnAndRow(5, $i)->getValue() . '/' . $sheet->getCellByColumnAndRow(7, $i)->getValue();
//            $contact = $sheet->getCellByColumnAndRow(9, $i)->getValue() . ' ' . $sheet->getCellByColumnAndRow(10, $i)->getValue();
//            $address = $sheet->getCellByColumnAndRow(4, $i)->getValue() . $sheet->getCellByColumnAndRow(13, $i)->getValue();
//            $completion_time = $sheet->getCellByColumnAndRow(7, $i)->getValue();

            $log[] = array_merge($appendColumns, [
                'id' => $id,
                'pid' => $pid,
                'title' => $title,
                'level' => $level,
//                'space' => trim($space),
//                'contact' => trim($contact),
//                'address' => trim($address),
//                'completion_time' => trim($completion_time),
            ]);
        }

        return $log;
    }
}
