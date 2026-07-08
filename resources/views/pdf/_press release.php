<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
//include 'fpdf/fpdf.php';
include 'exfpdf.php';
include 'easyTable.php';
function formatName($fullName)
{
    $fullName = trim($fullName);
    $nameParts = explode(' ', $fullName);
    $lastNameIndex = count($nameParts) - 1;
    $nameParts[$lastNameIndex] = strtoupper($nameParts[$lastNameIndex]);
    for ($i = 0; $i < $lastNameIndex; $i++) {
        $nameParts[$i] = ucfirst(strtolower($nameParts[$i]));
    }
    $updatedFullName = implode(' ', $nameParts);
    return $updatedFullName;
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
$session = $session;
$program = $program;
$rec = DB::table('program')->where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row->award;
    $faculty = DB::table('faculty')->where(['code' => $row->faculty])->value('title');
    $dept = $row->title;
    $duration = ($row->duration) * 100;
    //$duration = 200;
}
//$duration = 200;
//$sql = "SELECT * FROM results where session = '$session' ORDER BY course ASC";
//$result = mysqli_query($con, $sql);
$pdf = new exFPDF();
$pdf->AddPage('O');
$pdf->SetFont('helvetica', '', 8);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');
$table = new easyTable($pdf, '{13, 30, 60, 70, 60}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('(' . strtoupper($faculty) . ')', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('DEPARMENT OF ' . strtoupper($dept), 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('Summary of Graduating Students for ' . $session . ' Academic Session', 'colspan:14; font-size:12; align:C;border:0;');
$table->printRow();
$pdf->Ln(4);

$table->easyCell('Degree: ' . $award . ' (Hons) ' . ucwords(strtolower($dept)), 'colspan:14; font-size:12; align:L;border:0;');
$table->printRow();
$table->rowStyle('align:{CCCCCCCC}; font-style:B;');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
$table->easyCell("S/No.");
$table->easyCell("ID. No.");
$table->easyCell("Name");
$table->easyCell("Degree Award");
$table->easyCell("Class of Degree");
$table->printRow();
$session_history = DB::table('session_history')->where(['session' => $session, 'level' => $duration, 'program' => $program])->get();
$sn = 1;
$unit = 0;
$ugp = 0;
$cgpa = 0;
$carry = '';
$status = '';
$class = '';
$f = 0;
$flag = 0;
$p = 0;
foreach ($session_history as $results) {
    $id = $results->username;
    $name = DB::table('students')->where(['username' => $id])->select('fullname')->value('fullname');
    $reg = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
    foreach ($reg as $result) {
        $unit = $unit + $result->unit;
        if ($result->status == 'awaiting') {
            $p++;
            $pend = $pend . ' ' . $result->code;
        } else {
            $ugp = $ugp + $result->ugp;
            if ($result->grade == 'F') {
                $carry = $carry . ' ' . $result->code;
                $f++;
            }
        }
    }
    $cgpa = $ugp / $unit;

    if ($cgpa >= 4.5) {
        $class = 'First Class';
        if ($f == 0) $first++;
    } elseif ($cgpa >= 3.5) {
        $class = 'Second Class Upper Division';
        if ($f == 0) $upper++;
    } elseif ($cgpa >= 2.4) {
        $class = 'Second Class Lower Division';
        if ($f == 0) $lower++;
    } elseif ($cgpa >= 1.5) {
        $class = 'Third Class';
        if ($f == 0) $third++;
    } else {
        $class = 'Pass';
        if ($f == 0 && $ugp != 0) $pass++;
    }
    $table->rowStyle('align:{CCLCL};');
    if ($f == 0 && $ugp != 0) {
        $table->easyCell($sn++);
        $table->easyCell($id);
        $table->easyCell(formatName($name));
        $table->easyCell($award . ' (Hons) ' . ucwords(strtolower($dept)));
        $table->easyCell($class);
    }
    $table->printRow();
    $unit = 0;
    $ugp = 0;
    $cgpa = 0;
    $carry = '';
    $f = 0;
    $p = 0;
    $pend = '';
    $class = '';
}
$pdf->Ln(4);
$table->easyCell('SUMMARY:', 'colspan:2; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('First Class', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $first), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Second Class Upper Division', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $upper), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Second Class Lower Division', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $lower), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Third Class', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $third), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Pass', 'font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $pass), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Total', 'font-style:B; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", --$sn), 'colspan:7; font-style:B; font-size:10; align:L;border:0;');
$table->printRow();
$table->endTable(4);

//-----------------------------------------
//$name = $lvl.'L '.$session;
//$pdf->Output('I',$name.'.pdf');
$pdf->Output();
die;
