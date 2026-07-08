<?php

use Illuminate\Support\Facades\DB;

$semester = DB::table('semester')->where('status', '1')->value('semester');
$session = DB::table('session')->where('status', '1')->value('title');
include('pdf_mc_table.php');
//include 'fpdf/fpdf.php';
include 'exfpdf.php';
include 'easyTable.php';

$pdf = new exFPDF();
$pdf->AddPage('O');
$pdf->SetFont('helvetica', '', 10);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');
//$pdf->MultiCell(190, '10', $final, '', 'L');

$table = new easyTable($pdf, '{45, 45, 45, 45, 45, 45}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
$table->printRow();
$table->easyCell('(' . DB::table('faculty')->where('code', $faculty)->value('title') . ' LECTURE TIMETABLE)', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('[SECOND SEMESTER ' . $session . ' ACADEMIC SESSION]', 'colspan:14; font-style:B; font-size:10; align:C;border:0;');
$table->printRow();
$pdf->Ln(5);
$table->rowStyle('align:{CCCCCC};');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
$table->easyCell("Monday", 'font-style:B;');
$table->easyCell("Tuesday", 'font-style:B;');
$table->easyCell("Wednesday", 'font-style:B;');
$table->easyCell("Thursday", 'font-style:B;');
$table->easyCell("Friday", 'font-style:B;');
$table->easyCell("Saturday", 'font-style:B;');
$table->printRow();
function abbreviateMiddleName($name)
{
    $nameParts = explode(" ", $name);
    if (count($nameParts) === 3) {
        $nameParts[1] = substr($nameParts[1], 0, 1) . ".";
        $abbreviatedName = implode(" ", $nameParts);
        return $abbreviatedName;
    }
    return $name;
}
$data = DB::table('lecture_timetable')->where(['faculty' => $faculty])->orderBy('day_no', 'ASC')->orderBy('start', 'ASC')->get();
$x = 0;
$mon = 0;
$tue = 0;
$wed = 0;
$thu = 0;
$fri = 0;
$sat = 0;
$monday = $tuesday = $wednesday = $thursday = $friday = $saturday = array();
foreach ($data as $row) {
    $staffs = DB::table('course_allocation')->where(['course' => $row->course])->select('name')->orderBy('type', 'ASC')->get();
    $lecturer = "";
    foreach ($staffs as $staff) {
        $lecturer .= abbreviateMiddleName($staff->name) . "\n";
    }
    $x++;
    $info = $row->course . "\n" . date('h:i A', strtotime($row->start)) . " - " . date('h:i A', strtotime($row->end)) . "\n" . $row->hall . "\n" . ucwords(strtolower($lecturer)) . strtolower($row->comment);
    if ($row->day_no == 1) {
        $mon++;
        $monday[$mon] = $info;
    }
    if ($row->day_no == 2) {
        $tue++;
        $tuesday[$tue] = $info;
    }
    if ($row->day_no == 3) {
        $wed++;
        $wednesday[$wed] = $info;
    }
    if ($row->day_no == 4) {
        $thu++;
        $thursday[$thu] = $info;
    }
    if ($row->day_no == 5) {
        $fri++;
        $friday[$fri] = $info;
    }
    if ($row->day_no == 6) {
        $sat++;
        $saturday[$sat] = $info;
    }
}
$days_no = [$mon, $tue, $wed, $thu, $fri, $sat];
$i = max($days_no);
// echo $thursday[1].'GOOO';
// die;
for ($x = 1; $x <= $i; $x++) {
    if (isset($monday[$x])) {
        $table->easyCell($monday[$x]);
    } else {
        $table->easyCell("");
    }
    if (isset($tuesday[$x])) {
        $table->easyCell($tuesday[$x]);
    } else {
        $table->easyCell("");
    }
    if (isset($wednesday[$x])) {
        $table->easyCell($wednesday[$x]);
    } else {
        $table->easyCell("");
    }
    if (isset($thursday[$x])) {
        $table->easyCell($thursday[$x]);
    } else {
        $table->easyCell("");
    }
    if (isset($friday[$x])) {
        $table->easyCell($friday[$x]);
    } else {
        $table->easyCell("");
    }
    if (isset($saturday[$x])) {
        $table->easyCell($saturday[$x]);
    } else {
        $table->easyCell("");
    }

    $table->printRow();
}
$table->endTable();
echo $pdf->Output('lecture timetable', 'I');
exit;
