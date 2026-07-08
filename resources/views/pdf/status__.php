<?php

use Illuminate\Support\Facades\DB;

include('pdf_mc_table.php');
//include 'fpdf/fpdf.php';
include 'exfpdf.php';
include 'easyTable.php';
function formatName($fullName) {
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
function codeMod($input)
{
    return preg_replace('/([A-Za-z]+)([0-9]+)/', '$1 $2', $input);
}
$name = '';
$id_no = '';
$state = '';
$nationality = '';
$sex = '';
$pend = '';
$p = 0;
$aa = 0;
$bb = 0;
$cc = 0;
$dd = 0;
$ee = 0;
$ff = 0;
$gg = 0;
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
$lvl = $lvl;
$session = $session;
$program = $program;
$fac = DB::table('program')->where('code', $program)->value('faculty');
$pro_name = DB::table('program')->where('code', $program)->value('title');
$dept = DB::table('program')->where('code', $program)->value('department');
$dept_name = DB::table('department')->where('code', $dept)->value('title');
$fac_name = DB::table('faculty')->where('code', $fac)->value('title');
$type = $type;
//$session = "2020/2021";
//$type = "PROVISIONAL";
//$lvl = '100';
if ($lvl == '100') {
    $level = "I";
    $level_ = "II";
} elseif ($lvl == '200') {
    $level = "II";
    $level_ = "III";
} elseif ($lvl == '300') {
    $level = "III";
    $level_ = "IV";
} elseif ($lvl == '400') {
    $level = "IV";
    $level_ = "V";
} elseif ($lvl == '500') {
    $level = "V";
    $level_ = "(GRADUATION)";
}
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
$table = new easyTable($pdf, '{8.5, 25, 55, 10, 12, 10, 13, 130}', 'width:170; border-color:black; font-size:8; border:1; paddingY:0.5;');
$table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
$table->printRow();
$table->easyCell('(' . strtoupper($fac_name) . ')', 'colspan:14; font-size:10; align:C;border:0;');
$table->printRow();
$table->easyCell('DEPARMENT OF ' . strtoupper($dept_name), 'colspan:14; font-size:10; font-style:B; align:C;border:0;');
$table->printRow();
$table->easyCell('Provisional Academic Status of Part ' . $level . ' Students at the End of ' . $session . ' Academic Session', 'colspan:14; font-size:10; font-style:B; align:C;border:0;');
$table->printRow();
$pdf->Ln(4);
$session_history = DB::table('session_history')->where(['session' => $session, 'level' => $lvl, 'program' => $program])->get();
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
    $name = DB::table('students')->where(['username' => $id])->select('fullname')->value('fullname');
    $reg = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
    $failingCourses = [];
    $pendingCourses = [];
    foreach ($reg as $result) {

        $unit = $unit + $result->unit;
        if ($result->status == 'awaiting') {
            $p++;
            $pendingCourses[] = codeMod($result->code);
            //$pend = $pend . ' ' . $result->code;
        } else {
            $ugp = $ugp + $result->ugp;
            if ($result->grade == 'F') {
                $failingCourses[] = codeMod($result->code);
                //$carry = $carry . ' ' . $result->code;
                $f++;
            }
        }
    }
    if (count($failingCourses) > 0) {
        if (count($failingCourses) == 1) {
            $carry = $failingCourses[0];
        } elseif (count($failingCourses) == 2) {
            $carry = $failingCourses[0] . ' and ' . $failingCourses[1];
        } else {
            $carry = implode(', ', array_slice($failingCourses, 0, -1));
            $carry .= ' and ' . end($failingCourses);
        }
    }
    if (count($pendingCourses) > 0) {
        if (count($pendingCourses) == 1) {
            $pend = $pendingCourses[0];
        } elseif (count($pendingCourses) == 2) {
            $pend = $pendingCourses[0] . ' and ' . $pendingCourses[1];
        } else {
            $pend = implode(', ', array_slice($pendingCourses, 0, -1));
            $pend .= ' and ' . end($pendingCourses);
        }
    }
    $totalUnits = DB::table('session_history')->where('username', $id)->select('total_unit')->sum('total_unit');
    $totalGradePoints = DB::table('session_history')->where('username', $id)->select('product')->sum('product');
    $ugp = $ugp + $totalGradePoints;
    $unit = $unit + $totalUnits;
    if ($unit > 0) {
        $cgpa = $ugp / $unit;
    }
    if ($f == 0 && $p == 0 && $cgpa > 1.0) {
        if ($flag == 0) {
            $table->easyCell('A. The following Part ' . $level . ' Students, having passed all the priscribed courses are to proceed to Part ' . $level_ . ':', 'colspan:14; font-style:B; font-size:10; align:L;border:0;');
            $table->printRow();
            $table->rowStyle('align:{CCCCCCCC}; font-style:B;');
            //  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
            $table->easyCell("S/No.");
            $table->easyCell("ID. No.");
            $table->easyCell("Name");
            $table->easyCell("Cum. Unit");
            $table->easyCell("Cum. Product");
            $table->easyCell("CGPA");
            $table->easyCell("Status");
            $table->easyCell("Remarks");
            $table->printRow();
            $flag = 1;
        }
        $aa++;
        $table->rowStyle('align:{CCLCCCCL};');
        $table->easyCell($sn++);
        $table->easyCell($id);
        $table->easyCell(formatName($name));
        $table->easyCell((float)$unit);
        $table->easyCell((float)$ugp);
        $table->easyCell(number_format((float)$cgpa, 2, '.', ''));
        $table->easyCell('Proceed');
        if ($pend != '') {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            }
        } else {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry);
            }
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
if ($flag == 0) {
    $table->easyCell('A. The following Part ' . $level . ' Students, having passed all the priscribed courses are to proceed to Part ' . $level_ . ': Nil', 'colspan:14; font-style:B; font-size:10; align:L;border:0;');
    $table->printRow();
}
$pdf->Ln(4);
// $sn = 1;
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
    $names = '';
    $names = DB::table('students')->where(['username' => $id])->select('first_name','last_name','other_name')->first();
    $name = $names->first_name .' '. $names->last_name .' '. $names->other_name;
    $reg = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
    $failingCourses = [];
    $pendingCourses = [];
    foreach ($reg as $result) {

        $unit = $unit + $result->unit;

        if ($result->status == 'awaiting') {
            $p++;
            $pendingCourses[] = codeMod($result->code);
            //$pend = $pend . ' ' . $result->code;
        } else {
            $ugp = $ugp + $result->ugp;
            if ($result->grade == 'F') {
                $failingCourses[] = codeMod($result->code);
                //$carry = $carry . ' ' . $result->code;
                $f++;
            }
        }
    }
    if (count($failingCourses) > 0) {
        if (count($failingCourses) == 1) {
            $carry = $failingCourses[0];
        } elseif (count($failingCourses) == 2) {
            $carry = $failingCourses[0] . ' and ' . $failingCourses[1];
        } else {
            $carry = implode(', ', array_slice($failingCourses, 0, -1));
            $carry .= ' and ' . end($failingCourses);
        }
    }
    if (count($pendingCourses) > 0) {
        if (count($pendingCourses) == 1) {
            $pend = $pendingCourses[0];
        } elseif (count($pendingCourses) == 2) {
            $pend = $pendingCourses[0] . ' and ' . $pendingCourses[1];
        } else {
            $pend = implode(', ', array_slice($pendingCourses, 0, -1));
            $pend .= ' and ' . end($pendingCourses);
        }
    }

    $totalUnits = DB::table('session_history')->where('username', $id)->select('total_unit')->sum('total_unit');
    $totalGradePoints = DB::table('session_history')->where('username', $id)->select('product')->sum('product');
    $ugp = $ugp + $totalGradePoints;
    $unit = $unit + $totalUnits;
    if ($unit > 0) {
        $cgpa = $ugp / $unit;
    }
    if (($cgpa > 1.0 && ($p + $f) < 7)) {
        if ($flag == 0) {
            $table->easyCell('B. The following Part ' . $level . ' Students, having passed some of the priscribed courses and failed others, are to proceed to Part ' . $level_ . ' but will carry over failed courses:', 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
            $table->printRow();

            $table->rowStyle('align:{CCCCCCCC}; font-style:B;');
            //  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
            $table->easyCell("S/No.");
            $table->easyCell("ID. No.");
            $table->easyCell("Name");
            $table->easyCell("Cum. Unit");
            $table->easyCell("Cum. Product");
            $table->easyCell("CGPA");
            $table->easyCell("Status");
            $table->easyCell("Remarks");
            $table->printRow();
            $flag = 1;
        }
        $bb++;
        $table->rowStyle('align:{CCLCCCCL};');
        $table->easyCell($sn++);
        $table->easyCell($id);
        $table->easyCell(formatName($name));
        $table->easyCell($unit);
        $table->easyCell($ugp);
        $table->easyCell(number_format((float)$cgpa, 2, '.', ''));
        $table->easyCell('Proceed');
        if ($pend != '') {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            }
        } else {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry);
            }
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
if ($flag == 0) {
    $table->easyCell('B. The following Part ' . $level . ' Students, having passed some of the priscribed courses and failed others, are to proceed to Part ' . $level_ . ' but will carry over failed courses: Nil', 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
    $table->printRow();
}
$pdf->Ln(4);
// $sn = 1;
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
    $failingCourses = [];
    $pendingCourses = [];
    foreach ($reg as $result) {

        $unit = $unit + $result->unit;

        if ($result->status == 'awaiting') {
            $p++;
            $pendingCourses[] = codeMod($result->code);
            //$pend = $pend . ' ' . $result->code;
        } else {
            $ugp = $ugp + $result->ugp;
            if ($result->grade == 'F') {
                $failingCourses[] = codeMod($result->code);
                //$carry = $carry . ' ' . $result->code;
                $f++;
            }
        }
    }
    if (count($failingCourses) > 0) {
        if (count($failingCourses) == 1) {
            $carry = $failingCourses[0];
        } elseif (count($failingCourses) == 2) {
            $carry = $failingCourses[0] . ' and ' . $failingCourses[1];
        } else {
            $carry = implode(', ', array_slice($failingCourses, 0, -1));
            $carry .= ' and ' . end($failingCourses);
        }
    }
    if (count($pendingCourses) > 0) {
        if (count($pendingCourses) == 1) {
            $pend = $pendingCourses[0];
        } elseif (count($pendingCourses) == 2) {
            $pend = $pendingCourses[0] . ' and ' . $pendingCourses[1];
        } else {
            $pend = implode(', ', array_slice($pendingCourses, 0, -1));
            $pend .= ' and ' . end($pendingCourses);
        }
    }

    $totalUnits = DB::table('session_history')->where('username', $id)->select('total_unit')->sum('total_unit');
    $totalGradePoints = DB::table('session_history')->where('username', $id)->select('product')->sum('product');
    $ugp = $ugp + $totalGradePoints;
    $unit = $unit + $totalUnits;
    if ($unit > 0) {
        $cgpa = $ugp / $unit;
    }
    if ($f > 6 || ($cgpa < 1.0 && $p == 0 && $ugp != 0)) {
        if ($flag == 0) {
            $table->easyCell('C. The following Part ' . $level . ' Students, having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses are to repeat (probation):', 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
            $table->printRow();
            $table->rowStyle('align:{CCCCCCCC}; font-style:B;');
            $table->easyCell("S/No.");
            $table->easyCell("ID. No.");
            $table->easyCell("Name");
            $table->easyCell("Cum. Unit");
            $table->easyCell("Cum. Product");
            $table->easyCell("CGPA");
            $table->easyCell("Status");
            $table->easyCell("Remarks");
            $table->printRow();
            $flag = 1;
        }
        $cc++;
        $table->rowStyle('align:{CCLCCCCL};');
        $table->easyCell($sn++);
        $table->easyCell($id);
        $table->easyCell(formatName($name));
        $table->easyCell($unit);
        $table->easyCell($ugp);
        $table->easyCell(number_format((float)$cgpa, 2, '.', ''));
        $table->easyCell('Repeat');
        if ($pend != '') {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            }
        } else {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry);
            }
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
if ($flag == 0) {
    $table->easyCell('C. The following Part ' . $level . ' Students, having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses are to repeat (probation): Nil', 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
    $table->printRow();
}
$pdf->Ln(4);
// $sn = 1;
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
    $failingCourses = [];
    $pendingCourses = [];
    foreach ($reg as $result) {

        $unit = $unit + $result->unit;

        if ($result->status == 'awaiting') {
            $p++;
            $pendingCourses[] = codeMod($result->code);
            //$pend = $pend . ' ' . $result->code;
        } else {
            $ugp = $ugp + $result->ugp;
            if ($result->grade == 'F') {
                $failingCourses[] = codeMod($result->code);
                //$carry = $carry . ' ' . $result->code;
                $f++;
            }
        }
    }
    if (count($failingCourses) > 0) {
        if (count($failingCourses) == 1) {
            $carry = $failingCourses[0];
        } elseif (count($failingCourses) == 2) {
            $carry = $failingCourses[0] . ' and ' . $failingCourses[1];
        } else {
            $carry = implode(', ', array_slice($failingCourses, 0, -1));
            $carry .= ' and ' . end($failingCourses);
        }
    }
    if (count($pendingCourses) > 0) {
        if (count($pendingCourses) == 1) {
            $pend = $pendingCourses[0];
        } elseif (count($pendingCourses) == 2) {
            $pend = $pendingCourses[0] . ' and ' . $pendingCourses[1];
        } else {
            $pend = implode(', ', array_slice($pendingCourses, 0, -1));
            $pend .= ' and ' . end($pendingCourses);
        }
    }

    $totalUnits = DB::table('session_history')->where('username', $id)->select('total_unit')->sum('total_unit');
    $totalGradePoints = DB::table('session_history')->where('username', $id)->select('product')->sum('product');
    $ugp = $ugp + $totalGradePoints;
    $unit = $unit + $totalUnits;
    if ($unit > 0) {
        $cgpa = $ugp / $unit;
    }
    // $f == 0 && $p == 0 && $cgpa > 1.0
    // $cgpa > 1.0 && ($p + $f) < 7
    // $f > 6 || ($cgpa < 1.0 && $p == 0 && $ugp != 0)
    // ($f + $p) > 6
    if (($f + $p) > 6) {
        if ($flag == 0) {
            $table->easyCell('D. The status of the following Part ' . $level . ' Students, student is "pending" to be determined after obtaining the pending result(s):', 'colspan:14; font-size:10; align:L;border:0;');
            $table->printRow();
            $table->rowStyle('align:{CCCCCCCC}; font-style:B;');
            $table->easyCell("S/No.");
            $table->easyCell("ID. No.");
            $table->easyCell("Name");
            $table->easyCell("Cum. Unit");
            $table->easyCell("Cum. Product");
            $table->easyCell("CGPA");
            $table->easyCell("Status");
            $table->easyCell("Remarks", 'colspan:2');
            $table->printRow();
            $flag = 1;
        }
        $dd++;
        $table->rowStyle('align:{CCLCCCCL};');
        $table->easyCell($sn++);
        $table->easyCell($id);
        $table->easyCell(formatName($name));
        $table->easyCell($unit);
        $table->easyCell($ugp);
        $table->easyCell(number_format((float)$cgpa, 2, '.', ''));
        $table->easyCell('Pending');
        if ($pend != '') {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry . "\n" . 'P[' . $p . ']: ' . $pend);
            }
        } else {
            if ($f == 0) {
                $carry = ' Nil';
                $table->easyCell('F:' . $carry);
            } else {
                $table->easyCell('F[' . $f . ']: ' . $carry);
            }
        }
        if ($cgpa >= 4.5) {
            $class = 'First';
        } elseif ($cgpa >= 3.5) {
            $class = 'Upper';
        } elseif ($cgpa >= 2.4) {
            $class = 'Lower';
        } elseif ($cgpa >= 1.5) {
            $class = 'Third';
        } elseif ($cgpa >= 1.0) {
            $class = 'Pass';
        } else {
            $class = 'Fail';
        }
        $table->easyCell($class);
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
if ($flag == 0) {
    $table->easyCell('D. The status of the following Part ' . $level . ' Students, student is "pending" to be determined after obtaining the pending result(s): Nil', 'colspan:14; font-style:B; font-size:10; align:L;border:0;');
    $table->printRow();
}
$pdf->Ln(2);
$table->easyCell('E. The following Part ' . $level . ' Students, student are on suspension: Nil', 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
$pdf->Ln(2);
$table->printRow();
$table->easyCell('F. The following Part ' . $level . ' Students, having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses during the repeat year (probation) are to withdraw: Nil', 'colspan:14; font-size:10; font-style:B; align:L;border:0;');
$pdf->Ln(2);
$table->printRow();
$table->easyCell('G. The following students having failed to register for session ' . $session . ' are consider to have voluntarily withdraw from program: Nil', 'colspan:14; font-style:B; font-size:10; align:L;border:0;');
$table->printRow();
$pdf->Ln(4);
$table->easyCell('SUMMARY:', 'colspan:14; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Proceed without carryover', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $aa), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Proceed with carryover', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $bb), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Repeat (probation)', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $cc), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Pending', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $dd), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Suspension', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $ee), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Expulsion', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $ff), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $gg), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Voluntarily Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", $gg), 'colspan:1; font-size:10; align:R;border:0;');
$table->printRow();
$table->easyCell('Total', 'colspan:3; font-style:B; font-size:10; align:L;border:0;');
$table->easyCell('=', 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->easyCell(sprintf("%02d", ($aa + $bb + $cc + $dd + $ee + $ff + $gg)), 'colspan:1; font-style:B; font-size:10; align:R;border:0;');
$table->printRow();
$table->endTable(4);

//-----------------------------------------
//$name = $lvl.'L '.$session;
//$pdf->Output('I',$name.'.pdf');
$pdf->Output();
die;
