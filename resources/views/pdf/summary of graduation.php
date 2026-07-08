<?php

use Illuminate\Support\Facades\DB;
// program model
use App\Models\Program;

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
$session = $session;
$program = $program;
$programTitle = '';
$rec = Program::where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row->award;
    $faculty = $row->facultys->title;
    $dept = $row->depts->title;
    $programTitle = $row->title;
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
$table = new easyTable($pdf, '{13, 30, 15, 20, 15, 60, 40,60}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('(' . strtoupper($faculty) . ')', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('DEPARMENT OF ' . strtoupper($dept), 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('Summary of Graduating Students for ' . $session . ' Academic Session', 'colspan:14; font-size:12; align:C;border:0;');
$table->printRow();
$pdf->Ln(4);

$table->easyCell('Degree Awarded: ' . $award . ' (Hons) ' . ucwords(strtolower($programTitle)), 'colspan:7; font-size:12; align:L;border:0;');
$table->easyCell('Year of Graduation: ' . date('Y'), 'colspan:1; font-size:12; align:R;border:0;');
$table->printRow();
$table->rowStyle('align:{CCCCCCCC}; font-style:B;');
//  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
$table->easyCell("S/No.");
$table->easyCell("ID. No.");
$table->easyCell("Cum. Unit");
$table->easyCell("Cum. Product");
$table->easyCell("CGPA");
$table->easyCell("Degree Award");
$table->easyCell("Class of Degree");
$table->easyCell("Remarks");
$table->printRow();
$session_history = DB::table('session_history')->where(['session' => $session, 'level' => $duration, 'program' => $program])->get();
//$r = mysqli_query($con,$sql9);
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
    $level = $results->level;
    $session_history = $results->status;
    $names = DB::table('students')->where(['username' => $id])->select('first_name','last_name','other_name')->first();
    $name = $names->first_name .' '. $names->last_name .' '. $names->other_name;
    $reg = DB::table('student_course_registration')
    ->where(['username' => $id, 'session' => $session])->orderBy('username', 'ASC')->get();
    foreach ($reg as $result) {
        //$unit = $unit + $result->unit;
        if( $result->level == $level && strtoupper($session_history) != "REPEAT"){
            $unit += $result->unit;
        }
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
    $totalUnits = DB::table('session_history')->where('username', $id)->where('session', '<=', $session)->select('total_unit')->sum('total_unit');
    $totalGradePoints = DB::table('session_history')->where('username', $id)->where('session', '<=', $session)->select('product')->sum('product');
    $ugp = $ugp + $totalGradePoints;
    $unit = $unit + $totalUnits;
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
    $table->rowStyle('align:{CCCCCCCL};');
    if ($ugp != 0) {
        if ($f > 0) {
            $table->easyCell($sn++);
            $table->easyCell($id);
            $table->easyCell('-');
            $table->easyCell('-');
            $table->easyCell('-');
            $table->easyCell('-');
            $table->easyCell('-');
            $table->easyCell('Deferred (Yet to pass ' . $carry . ')');
            $deferred++;
        } else {
            $table->easyCell($sn++);
            $table->easyCell($id);
            $table->easyCell((float)$unit);
            $table->easyCell((float)$ugp);
            $table->easyCell(number_format((float)$cgpa, 2, '.', ''));
            $table->easyCell($award . ' (Hons) ' . ucwords(strtolower($programTitle)));
            $table->easyCell($class);
            $table->easyCell('Pass');
        }
        $table->printRow();
    }

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
$table->easyCell('First Class', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $first), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Second Class Upper Division', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $upper), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Second Class Lower Division', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $lower), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Third Class', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $third), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Pass', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $pass), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Deferred', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $deferred), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Expulsion', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $expulsion), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $withdraw), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Voluntary Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $voluntary), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Suspension', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $suspension), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Others', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", $others), 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('', 'colspan:2; font-size:10; align:L;border:0;');
$table->easyCell('Total', 'colspan:3; font-style:B; font-size:10; align:L;border:0;');
$table->easyCell('= ' . sprintf("%02d", --$sn), 'colspan:7; font-style:B; font-size:10; align:L;border:0;');
$table->printRow();
$table->endTable(4);

//-----------------------------------------
//$name = $lvl.'L '.$session;
//$pdf->Output('I',$name.'.pdf');
$pdf->Output();
die;
