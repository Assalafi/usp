<?php
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';
 $servername = "localhost";
$username = "";
$password = "";
$bdname = "assalafi";
$conn = mysqli_connect($servername, $username, $password, $bdname);
$name = '';$id_no = '';$state = '';$nationality = '';$sex = '';$pend = '';$p = 0;
$aa = 0;$bb = 0;$cc = 0;$dd = 0;$ee = 0;$ff = 0;$gg = 0;
$first = 0;$second = 0;$f = 0;$s = 0;$cugp = 0;$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;$class = '';
//$code = mysqli_real_escape_string($con,$_POST['course']);
//$session = mysqli_real_escape_string($con,$_POST['session']);
$id = "21/05/04/004";
$session = "2020/2021";
$semester = "First";
$code = "CPE201";
$title = "Introduction to Computer Engineering";
$unit = "2";
$type = "PROVISIONAL";
$lvl = '100';
if($lvl == '100'){
  $level = "I";
  $level_ = "II";
}elseif($lvl == '200'){
  $level = "II";
  $level_ = "III";
}elseif($lvl == '300'){
  $level = "III";
  $level_ = "IV";
}elseif($lvl == '400'){
  $level = "IV";
  $level_ = "V";
}elseif($lvl == '500'){
  $level = "V";
  $level_ = "(GRADUATION)";
}
$sql7 = "SELECT * FROM courses WHERE level = '100'";
      $r = mysqli_query($conn,$sql7);
      if (mysqli_num_rows($r) > 0) {
          while($c = mysqli_fetch_assoc($r)) {
          $id = $c['code'];
            $sql6 = "SELECT DISTINCT course FROM results WHERE course='$id' and session = '2020/2021'";
          $name = mysqli_query($conn,$sql6);
          $name = mysqli_fetch_assoc($name);
          if(isset($name['course'])){
            
          }else{
            $p++;
           $pend = $pend.' '.$c['code'];
          }
          }
        }
$sqll = "SELECT * FROM courses WHERE code='$code'";
            $select = mysqli_query($conn,$sqll);
            $select = mysqli_fetch_assoc($select);
            if(isset($select['code'])){
                $semester = $select['semester'];
                $title = $select['title'];
                $unit = $select['unit'];
            }
$sql = "SELECT * FROM results where session = '2020/2021' ORDER BY course ASC";
$result = mysqli_query($conn, $sql);
 $pdf=new exFPDF();
 $pdf->AddPage(); 
 $pdf->SetFont('helvetica','',8);
 $pdf->AddFont('lato','','Lato-Regular.php');
 $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php'); 
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php'); 
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php'); 
 $table=new easyTable($pdf, '{8.5, 20, 50, 10, 12, 10, 13,55,10}', 'width:170; border-color:black; font-size:8; border:1; paddingY:0.5;');
 $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:12; align:C;border:0;');
 $table->printRow();
 $table->easyCell('(FACULTY OF ENGINEERING)', 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $table->easyCell('DEPARMENT OF COMPUTER ENGINEERING', 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $table->easyCell($type.' ACADEMIC STATUS OF PART '.$level.' STUDENTS AT THE END OF '.$session.' ACADEMIC SESSION', 'colspan:14; font-size:10; align:C;border:0;');
 $table->printRow();
 $pdf->Ln(4);
 $sql9 = "SELECT * FROM level WHERE level = '100' and session = '2020/2021'";
                        $r = mysqli_query($conn,$sql9);
                        $sn = 1;
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $status = '';
                        $class = '';
                        $f = 0;$flag = 0;
                        if (mysqli_num_rows($r) > 0) {
                            while($results = mysqli_fetch_assoc($r)) {
                            $id = $results['id_no'];
                              $sql8 = "SELECT * FROM students WHERE id_no='$id'";
                            $name = mysqli_query($conn,$sql8);
                            $name = mysqli_fetch_assoc($name);
                            $name = $name['name'];
                             $sqli = "SELECT * FROM results WHERE id_no = '$id' and session = '$session'";
                        $rw = mysqli_query($conn,$sqli);
                        if (mysqli_num_rows($rw) > 0) {
                            while($result = mysqli_fetch_assoc($rw)) {                            
                            $unit = $unit + $result['unit'];
                            $ugp = $ugp + $result['ugp'];
                            if($result['grade'] == 'F'){
                              $carry = $carry .' '. $result['course'];
                              $f++;
                              //$c = 1;
                            }
                          }
                          $cgpa = $ugp/$unit;
                        }
                        if($f==0){
                          if($flag == 0){
                            $table->easyCell('A. The following part '.$level.' having passed all the priscribed courses are to proceed to part '.$level_.':', 'colspan:14; font-size:10; align:L;border:0;');
                           $table->printRow();                            
                          $table->rowStyle('align:{CCCCCCCC};');
                          //  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
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
                          $aa++;                         
                        $table->rowStyle('align:{CCLCCCCL};');
                        $table->easyCell($sn++);
                        $table->easyCell($results['id_no']);
                        $table->easyCell($name);
                        $table->easyCell($unit);
                        $table->easyCell($ugp);
                        $table->easyCell(number_format((float)$cgpa,2,'.',''));
                        $table->easyCell('Proceed');
                        if($pend!=''){
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry."\n".'AR['.$p.']'.$pend);
                        }else{
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry);
                        }
                        if($cgpa >= 4.5){$class = 'First';}
                          elseif($cgpa >= 3.5){$class = 'Upper';
                            }elseif($cgpa >= 2.4){$class = 'Lower';
                              }elseif($cgpa >= 1.5){$class = 'Third';
                                }elseif($cgpa >= 1.0){$class = 'Pass';
                                  }else{$class = 'Fail';}
                        $table->easyCell($class);
                        $table->printRow();                      
                        }
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $f = 0;
                        $class = '';
                      }}
                      if($flag == 0){
                        $table->easyCell('A. The following part '.$level.' having passed all the priscribed courses are to proceed to part '.$level_.': Nil', 'colspan:14; font-size:10; align:L;border:0;');
                        $table->printRow();
                      }
$pdf->Ln(4);
$sql9 = "SELECT * FROM level WHERE level = '100' and session = '2020/2021'";
                        $r = mysqli_query($conn,$sql9);
                        $sn = 1;
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $status = '';
                        $class = '';
                        $f = 0;$flag =0;
                        if (mysqli_num_rows($r) > 0) {
                            while($results = mysqli_fetch_assoc($r)) {
                            $id = $results['id_no'];
                              $sql8 = "SELECT * FROM students WHERE id_no='$id'";
                            $name = mysqli_query($conn,$sql8);
                            $name = mysqli_fetch_assoc($name);
                            $name = $name['name'];
                             $sqli = "SELECT * FROM results WHERE id_no = '$id' and session = '$session'";
                        $rw = mysqli_query($conn,$sqli);
                        if (mysqli_num_rows($rw) > 0) {
                            while($result = mysqli_fetch_assoc($rw)) {                            
                            $unit = $unit + $result['unit'];
                            $ugp = $ugp + $result['ugp'];
                            if($result['grade'] == 'F'){
                              $carry = $carry .' '. $result['course'];
                              $f++;
                              //$c = 1;
                            }
                          }
                          $cgpa = $ugp/$unit;
                        }
                        if($f>0 and !($f > 6 || $cgpa < 1)){
                          if($flag == 0){
                            $table->easyCell('B. The following part '.$level.' having passed some of the priscribed courses and failed others, are to proceed to part '.$level_.' but will carry over failed courses:', 'colspan:14; font-size:10; align:L;border:0;');
                             $table->printRow();
                              
                            $table->rowStyle('align:{CCCCCCCC};');
                            //  $table->rowStyle('align:{LRCC}; bgcolor:#00ace6;font-style:B');
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
                          $bb++;                    
                          $table->rowStyle('align:{CCLCCCCL};');
                        $table->easyCell($sn++);
                        $table->easyCell($results['id_no']);
                        $table->easyCell($name);
                        $table->easyCell($unit);
                        $table->easyCell($ugp);
                        $table->easyCell(number_format((float)$cgpa,2,'.',''));
                        $table->easyCell('Proceed');
                        if($pend!=''){
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry."\n".'AR['.$p.']'.$pend);
                        }else{
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry);
                        }
                        
                        if($cgpa >= 4.5){$class = 'First';}
                          elseif($cgpa >= 3.5){$class = 'Upper';
                            }elseif($cgpa >= 2.4){$class = 'Lower';
                              }elseif($cgpa >= 1.5){$class = 'Third';
                                }elseif($cgpa >= 1){$class = 'Pass';
                                  }else{$class = 'Fail';}
                        $table->easyCell($class);
                        $table->printRow();                      
                        }
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $f = 0;
                        $class = '';
                      }}
                      if($flag == 0){
                        $table->easyCell('B. The following part '.$level.' having passed some of the priscribed courses and failed others, are to proceed to part '.$level_.' but will carry over failed courses: Nil', 'colspan:14; font-size:10; align:L;border:0;');
                             $table->printRow();
                      }
$pdf->Ln(4);
$sql9 = "SELECT * FROM level WHERE level = '100' and session = '2020/2021'";
                        $r = mysqli_query($conn,$sql9);
                        $sn = 1;
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $status = '';
                        $class = '';
                        $f = 0;$flag=0;
                        if (mysqli_num_rows($r) > 0) {
                            while($results = mysqli_fetch_assoc($r)) {
                            $id = $results['id_no'];
                              $sql8 = "SELECT * FROM students WHERE id_no='$id'";
                            $name = mysqli_query($conn,$sql8);
                            $name = mysqli_fetch_assoc($name);
                            $name = $name['name'];
                             $sqli = "SELECT * FROM results WHERE id_no = '$id' and session = '$session'";
                        $rw = mysqli_query($conn,$sqli);
                        if (mysqli_num_rows($rw) > 0) {
                            while($result = mysqli_fetch_assoc($rw)) {
                            
                            $unit = $unit + $result['unit'];
                            $ugp = $ugp + $result['ugp'];
                            if($result['grade'] == 'F'){
                              $carry = $carry .' '. $result['course'];
                              $f++;
                              //$c = 1;
                            }
                          }
                          $cgpa = $ugp/$unit;
                        }
                        if($f > 6 || $cgpa < 1.0){                          
                          if($flag == 0){
                            $table->easyCell('C. The following part '.$level.' having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses are to repeat (probation):', 'colspan:14; font-size:10; align:L;border:0;');
                             $table->printRow(); 
                            $table->rowStyle('align:{CCCCCCCC};');
                            $table->easyCell("S/No.");
                           $table->easyCell("ID. No.");
                           $table->easyCell("Name");
                           $table->easyCell("Cum. Unit");
                           $table->easyCell("Cum. Product");
                           $table->easyCell("CGPA");
                           $table->easyCell("Status");
                            $table->easyCell("Remarks", 'colspan:2');
                            $table->printRow();
                            $flag=1;
                          }
                          $cc++;                        
                          $table->rowStyle('align:{CCLCCCCL};');
                        $table->easyCell($sn++);
                        $table->easyCell($results['id_no']);
                        $table->easyCell($name);
                        $table->easyCell($unit);
                        $table->easyCell($ugp);
                        $table->easyCell(number_format((float)$cgpa,2,'.',''));
                        $cgpa = 3.33;
                        $table->easyCell('Repeat');
                        if($pend!=''){
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry."\n".'AR['.$p.']'.$pend);
                        }else{
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry);
                        }
                        if($cgpa >= 4.5){$class = 'First';}
                          elseif($cgpa >= 3.5){$class = 'Upper';
                            }elseif($cgpa >= 2.4){$class = 'Lower';
                              }elseif($cgpa >= 1.5){$class = 'Third';
                                }elseif($cgpa >= 1.0){$class = 'Pass';
                                  }else{$class = 'Fail';}
                        $table->easyCell($class);
                        $table->printRow();
                      
                        }
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $f = 0;
                        $class = '';
                      }}
                      if($flag == 0){
                        $table->easyCell('C. The following part '.$level.' having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses are to repeat (probation): Nil', 'colspan:14; font-size:10; align:L;border:0;');
                             $table->printRow();
                      }
$pdf->Ln(4);
$sql9 = "SELECT * FROM level WHERE level = '100' and session = '2020/2021'";
                        $r = mysqli_query($conn,$sql9);
                        $sn = 1;
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $status = '';
                        $class = '';
                        $f = 0;$flag=0;
                        if (mysqli_num_rows($r) > 0) {
                            while($results = mysqli_fetch_assoc($r)) {
                            $id = $results['id_no'];
                              $sql8 = "SELECT * FROM students WHERE id_no='$id'";
                            $name = mysqli_query($conn,$sql8);
                            $name = mysqli_fetch_assoc($name);
                            $name = $name['name'];
                             $sqli = "SELECT * FROM results WHERE id_no = '$id' and session = '$session'";
                        $rw = mysqli_query($conn,$sqli);
                        if (mysqli_num_rows($rw) > 0) {
                            while($result = mysqli_fetch_assoc($rw)) {
                            
                            $unit = $unit + $result['unit'];
                            $ugp = $ugp + $result['ugp'];
                            if($result['grade'] == 'F'){
                              $carry = $carry .' '. $result['course'];
                              $f++;
                              //$c = 1;
                            }
                          }
                          $cgpa = $ugp/$unit;
                        }
                        if($f == 6 and $p > 0){                          
                          if($flag == 0){
                            $table->easyCell('D. The status of the following part '.$level.' student is "pending" to be determined after obtaining the pending results:', 'colspan:14; font-size:10; align:L;border:0;');
                             $table->printRow(); 
                            $table->rowStyle('align:{CCCCCCCC};');
                            $table->easyCell("S/No.");
                           $table->easyCell("ID. No.");
                           $table->easyCell("Name");
                           $table->easyCell("Cum. Unit");
                           $table->easyCell("Cum. Product");
                           $table->easyCell("CGPA");
                           $table->easyCell("Status");
                            $table->easyCell("Remarks", 'colspan:2');
                            $table->printRow();
                            $flag=1;
                          }
                          $dd++;                          
                          $table->rowStyle('align:{CCLCCCCL};');
                        $table->easyCell($sn++);
                        $table->easyCell($results['id_no']);
                        $table->easyCell($name);
                        $table->easyCell($unit);
                        $table->easyCell($ugp);
                        $table->easyCell(number_format((float)$cgpa,2,'.',''));
                        $cgpa = 3.33;
                        $table->easyCell('Pending');
                        if($pend!=''){
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry."\n".'AR['.$p.']'.$pend);
                        }else{
                        if($f == 0){$carry=' NIL';}$table->easyCell('F['.$f.']'.$carry);
                        }
                        if($cgpa >= 4.5){$class = 'First';}
                          elseif($cgpa >= 3.5){$class = 'Upper';
                            }elseif($cgpa >= 2.4){$class = 'Lower';
                              }elseif($cgpa >= 1.5){$class = 'Third';
                                }elseif($cgpa >= 1.0){$class = 'Pass';
                                  }else{$class = 'Fail';}
                        $table->easyCell($class);
                        $table->printRow();
                      
                        }
                        $unit = 0;
                        $ugp = 0;
                        $cgpa = 0;
                        $carry = '';
                        $f = 0;
                        $class = '';
                      }}
                      if($flag == 0){
                        $table->easyCell('D. The status of the following part '.$level.' student is "pending" to be determined after obtaining the pending results: Nil', 'colspan:14; font-size:10; align:L;border:0;');
                             $table->printRow();
                      }
$pdf->Ln(2);
$table->easyCell('E. The following part '.$level.' student are on suspension: Nil', 'colspan:14; font-size:10; align:L;border:0;');
$pdf->Ln(2);
$table->printRow();
$table->easyCell('F. The following part '.$level.' having failed to secure a minimum CGPA of 1.0 and/or failed more than six (6) courses during the repeat year (probation) are to withdraw: Nil', 'colspan:14; font-size:10; align:L;border:0;');
$pdf->Ln(2);
$table->printRow();
$table->easyCell('G. The following student, having failed to register for session '.$session.' are consider to have voluntarily withdraw from program: Nil', 'colspan:14; font-size:10; align:L;border:0;');
$table->printRow();
$pdf->Ln(4);
$table->easyCell('SUMMARY:', 'colspan:14; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Proceed without carryover', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$aa, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Proceed with carryover', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$bb, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Repeat (probation)', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$cc, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Pending', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$dd, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Suspension', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$ee, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Expulsion', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$ff, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$gg, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Voluntarily Withdraw', 'colspan:3; font-size:10; align:L;border:0;');
$table->easyCell('= '.$gg, 'colspan:7; font-size:10; align:L;border:0;');
$table->printRow();
$table->easyCell('Total', 'colspan:3; font-style:B; font-size:10; align:L;border:0;');
$table->easyCell('= '.($aa+$bb+$cc+$dd+$ee+$ff+$gg), 'colspan:7; font-style:B; font-size:10; align:L;border:0;');
$table->printRow();
 $table->endTable(4);
 
//-----------------------------------------

 $pdf->Output(); 

?>