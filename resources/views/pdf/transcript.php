<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
//include 'fpdf/fpdf.php';
include 'exfpdf.php';
include 'easyTable.php';
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

$id_number = $id_number;
$info = DB::table('students')->where(['username' => $id_number])->get();
foreach ($info as $info) {
    $program = $info->program;
    $id = $info->username;
    $name = $info->fullname;
}
$rec = DB::table('program')->where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row->award;
    $faculty = DB::table('faculty')->where(['code' => $row->faculty])->value('title');
    $dept = $row->title;
    $duration = ($row->duration) * 100;
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
$table = new easyTable($pdf, '{30, 70, 20, 20, 20, 20}', 'width:170; border-color:black; font-size:8; border:1; paddingY:0.5;');
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('(Office of the Registrar)', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('CONFIDENTIAL ACADEMIC DIVISION', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('TRANSCRIPT OF ACADEMIC RECORD', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$pdf->Ln(4);

$sn = 1;
$unit = 0;
$ugp = 0;
$cgpa = 0;
// $table->easyCell('In case of doubt, the authenticity of this Transcript should be checked with this office.', 'colspan:6; font-size:8; border:0;');
// $table->printRow();
$table->easyCell('NAME: ' . $name, 'colspan:2; font-size:8; align:L;border:0;');
$table->easyCell('ID. NO: ' . $id, 'colspan:4; font-size:8; align:R;border:0;');
$table->printRow();
$table->easyCell(strtoupper($faculty), 'colspan:2; font-size:8; align:L;border:0;');
$table->easyCell('DEPARTMENT: ' . strtoupper($dept), 'colspan:4; font-size:8; align:R;border:0;');
$table->printRow();
$table->rowStyle('align:{CCCCCCC}; font-style:B;');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
$table->easyCell("COURSE NO");
$table->easyCell("COURSE TITLE");
$table->easyCell("UNIT");
$table->easyCell("MARK");
$table->easyCell("GRADE");
$table->easyCell("REMARKS");
$table->printRow();

$student = DB::table('session_history')->where(['username' => $id])->orderBy('session', 'ASC')->distinct()->get('session');
foreach ($student as $row) {
    $lvl = DB::table('session_history')->where(['username' => $id, 'session' => $row->session])->select('level')->value('level');
    if ($lvl == '100') {
        $level = "I";
    } elseif ($lvl == '200') {
        $level = "II";
    } elseif ($lvl == '300') {
        $level = "III";
    } elseif ($lvl == '400') {
        $level = "IV";
    } elseif ($lvl == '500') {
        $level = "V";
    }

    $table->easyCell("");
    $table->easyCell($row->session . ' PART ' . $level, 'align:C;');
    $table->easyCell("");
    $table->easyCell("");
    $table->easyCell("");
    $table->easyCell("");
    $table->printRow();
    $cunit = 0;
    $cugp = 0;
    $ccgpa = 0;
    $regs = DB::table('student_course_registration')->where(['username' => $id, 'session' => $row->session])->orderBy('username', 'ASC')->get();
    foreach ($regs as $reg) {
        $getCourses = DB::table('course')->where(['code' => $reg->code])->select('unit', 'title')->get();
        foreach ($getCourses as $c) {
            $cunit = $cunit + $c->unit;
            $title = $c->title;
        }
        $unit = $unit + $reg->unit;
        $ugp = $ugp + $reg->ugp;

        $cugp = $cugp + $reg->ugp;

        $table->rowStyle('align:{LLCCCCC};');
        $table->easyCell($reg->code);
        $table->easyCell($title);
        $table->easyCell($c->unit);
        $table->easyCell($reg->total);
        $table->easyCell($reg->grade);
        $table->easyCell($reg->status);
        $table->printRow();
    }
    if( $unit > 0 ){
        $cgpa = $ugp / $unit;
    }
    if( $cunit > 0 ){
        $ccgpa = $cugp / $cunit;
    }

    $table->rowStyle('align:{LCCCCCC};');
    $table->easyCell('GPA: ' . number_format((float)$cgpa, 2, '.', ''), 'font-style:B;');
    $table->easyCell("");
    $table->easyCell("");
    $table->easyCell("");
    $table->easyCell('CGPA: ' . number_format((float)$cgpa, 2, '.', ''), 'font-style:B;colspan:2;');
    $table->printRow();
    $cunit = 0;
    $cugp = 0;
    $ccgpa = 0;
}

if ($cgpa >= 4.5) {
    $class = 'First Class';
} elseif ($cgpa >= 3.5) {
    $class = 'Second Class Upper Division';
} elseif ($cgpa >= 2.4) {
    $class = 'Second Class Lower Division';
} elseif ($cgpa >= 1.5) {
    $class = 'Third Class';
} else {
    $class = 'Pass';
}

$pdf->Ln(4);

$table->easyCell('Degree Awarded: ' . $award . ' (HONS)(' . strtoupper($dept) . ')', 'colspan:6; font-size:8; align:L;border:0;font-style:B;');
$table->printRow();
$table->easyCell('Class of Degree: ' . strtoupper($class), 'colspan:6; font-size:8; align:L;border:0;font-style:B;');
$table->printRow();

$table->endTable(4);
$table = new easyTable($pdf, '{20, 20, 20, 30, 70}', 'width:170; align:{CCCCCCC}; font-style:B; border-color:black; font-size:8; border:1; paddingY:0.5;');

$table->easyCell("SCORE");
$table->easyCell("GRADE");
$table->easyCell("GP");
$table->easyCell("CGPA");
$table->easyCell("CLASS OF DEGREE");
$table->printRow();
$table->easyCell("70-100%");
$table->easyCell("A");
$table->easyCell("5");
$table->easyCell("4.50-5.00");
$table->easyCell("FIRST CLASS", 'align:L;');
$table->printRow();
$table->easyCell("60-69");
$table->easyCell("B");
$table->easyCell("4");
$table->easyCell("3.50-3.49");
$table->easyCell("SECOND CLASS UPPER", 'align:L;');
$table->printRow();
$table->easyCell("50-59");
$table->easyCell("C");
$table->easyCell("3");
$table->easyCell("2.40-3.49");
$table->easyCell("SECOND CLASS LOWER", 'align:L;');
$table->printRow();
$table->easyCell("45-49");
$table->easyCell("D");
$table->easyCell("2");
$table->easyCell("1.50-2.39");
$table->easyCell("THIRD CLASS", 'align:L;');
$table->printRow();
$table->easyCell("40-44");
$table->easyCell("E");
$table->easyCell("1");
$table->easyCell("1.00-1.49");
$table->easyCell("PASS", 'align:L;');
$table->printRow();
$table->easyCell("0-39");
$table->easyCell("F");
$table->easyCell("0");
$table->easyCell("0.00-0.99");
$table->easyCell("FAIL", 'align:L;');
$table->printRow();
$table->endTable(4);

//-----------------------------------------
//$name = $lvl.'L '.$session;
//$pdf->Output('I',$name.'.pdf');
$pdf->Output();
die;
