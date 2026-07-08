<?php

use Illuminate\Support\Facades\DB;

include ('pdf_mc_table.php');
//include 'fpdf/fpdf.php';
include 'exfpdf.php';
include 'easyTable.php';

function firstUpper($input) {
    $excludeWords = ['or', 'and', 'of', 'the', 'in', 'on', 'at', 'by', 'for', 'to', 'iii'];
    $input = ucwords(strtolower($input));
    $words = explode(' ', $input);
    foreach ($words as &$word) {
        if (in_array(strtolower($word), $excludeWords)) {
            $word = strtolower($word);
        }
    }
    return implode(' ', $words);
}
$name = '';
$pend = '';
$p = 0;
$first = 0;
$upper = 0;
$lower = 0;
$third = 0;
$pass = 0;
$deferred = 0;
$expulsion = 0;
$withdraw = 0;
$voluntary = 0;
$suspension = 0;
$others = 0;
$first = 0;
$second = 0;
$f = 0;
$s = 0;
$cugp = 0;
$a = 0;
$b = 0;
$c = 0;
$d = 0;
$e = 0;
$f = 0;
$class = '';
//$session = session('system_session');
$program = session('program');
$programs = session('program');
$programTitle = '';
$rec = DB::table('program')->where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row->award;
    $faculty = DB::table('faculty')->where(['code' => $row->faculty])->value('title');
    $dept = $row->title;
    $duration = ($row->duration) * 100;
    $programTitle = $row->title;
}
$duration = 200;
//$sql = "SELECT * FROM results where session = '$session' ORDER BY course ASC";
//$result = mysqli_query($con, $sql);
$pdf = new exFPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');
$table = new easyTable($pdf, '{10, 20, 40,40, 20, 20,20}', 'width:100; border-color:black; font-size:9; border:1; paddingY:0.8;');

$table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
$table->printRow();
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
$table->printRow();
$table->easyCell(firstUpper($faculty), 'colspan:14; font-size:14; align:C;border:0;');
$table->printRow();
$table->easyCell('(Department of ' . firstUpper($dept . ')'), 'colspan:14; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('STUDENT REGISTERED COURSES', 'colspan:14; font-size:10; align:C;border:0; font-style:B;');
$table->printRow();
$pdf->Ln(4);

$sn = 1;
$unit = 0;
$unit2 = 0;
$table->easyCell('SESSION: ' . $session, 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell($award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'colspan:4; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('NAME: ' . DB::table('students')->where(['user_id' => session('id')])->value('fullname'), 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('ID. NO: ' . session('id_number'), 'colspan:4; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell("FIRST SEMESTER", 'colspan:7; font-size:10; align:C;font-style:B');
$table->printRow();
$table->rowStyle('align:{CCCCCC}; font-style:B;');
$table->easyCell("SN");
$table->easyCell("CODE");
$table->easyCell("TITLE", 'colspan:3;');
$table->easyCell("UNIT", 'colspan:2;');
$table->printRow();
//MBBS
//BDS

if ($programs == 'MBBS' || $programs == 'DBS') {
    $regs = DB::table('student_course_registration')
        ->where(['username' => session('id_number'), 'session' => $session])
        ->where(function ($query) {
            $query->where('code', 'like', 'GST%')
                ->where('semester', 'FIRST'); // Specify semester as 2
        })
        ->orWhere(function ($query) {
            $query->where('code', 'not like', 'GST%'); // Get all other courses
        })
        ->where(['username' => session('id_number')])
        ->orderBy('level', 'ASC')
        ->get();
} else {
    $regs = DB::table('student_course_registration')->where(['username' => session('id_number'), 'semester' => 'FIRST', 'session' => $session])->orderBy('level', 'ASC')->get();
}

foreach ($regs as $reg) {
    $table->rowStyle('align:{CCLCCC};');
    $table->easyCell($sn);
    $table->easyCell($reg->code);
    $table->easyCell(DB::table('course')->where(['code' => $reg->code])->value('title'), 'colspan:3;');
    $table->easyCell($reg->unit, 'colspan:2;');
    $table->printRow();
    $sn++;
    $unit += $reg->unit;
}
$table->easyCell('Total Unit of First Semester is ' . $unit, 'colspan:6; font-size:10; align:L;border:0;');
$table->printRow();

$pdf->Ln(10);

$sn = 1;
$table->easyCell("SECOND SEMESTER", 'colspan:7; font-size:10; align:C;font-style:B');
$table->printRow();
$table->rowStyle('align:{CCCCCC}; font-style:B;');
$table->easyCell("SN");
$table->easyCell("CODE");
$table->easyCell("TITLE", 'colspan:3;');
$table->easyCell("UNIT", 'colspan:2;');
$table->printRow();

if ($programs == 'MBBS' || $programs == 'DBS') {
    $regs = DB::table('student_course_registration')
        ->where(['username' => session('id_number'), 'session' => $session])
        ->where(function ($query) {
            $query->where('code', 'like', 'GST%')
                ->where('semester', 'SECOND'); // Specify semester as 2
        })
        ->orWhere(function ($query) {
            $query->where('code', 'not like', 'GST%'); // Get all other courses
        })
        ->where(['username' => session('id_number')])
        ->orderBy('level', 'ASC')
        ->get();
} else {
    $regs = DB::table('student_course_registration')->where(['username' => session('id_number'), 'semester' => 'SECOND', 'session' => $session])->orderBy('level', 'ASC')->get();
}
foreach ($regs as $reg) {
    $table->rowStyle('align:{CCLCCC};');
    $table->easyCell($sn);
    $table->easyCell($reg->code);
    $table->easyCell(DB::table('course')->where(['code' => $reg->code])->value('title'), 'colspan:3;');
    $table->easyCell($reg->unit, 'colspan:2;');
    $table->printRow();
    $sn++;
    $unit2 += $reg->unit;
}
$table->easyCell('Total Unit of Second Semester is ' . $unit2, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();

$pdf->Ln(5);
$table->easyCell("STUDENT'S SIGNATURE: .............................................................................................................................", 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$pdf->Ln(5);
$table->easyCell("HOD'S SIGNATURE:        .................................................................................................................................", 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$pdf->Ln(5);
$table->easyCell("REGISTRAR'S SIGNATURE: ..........................................................................................................................", 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();

$table->endTable(4);

$pdf->Output();
die;
