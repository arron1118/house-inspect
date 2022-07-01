<?php

namespace app\common\library;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class Report
{

    public function createReport($data)
    {
        $phpword = new PhpWord();
        $section = $phpword->addSection();
        $textrun = $section->addTextRun();
        $textrun->addText('Welcome to my world!!!');
        $writer = IOFactory::createWriter($phpword);
        $writer->save(date('Y_m_d_H_i_s') . '.docx');
    }
}
