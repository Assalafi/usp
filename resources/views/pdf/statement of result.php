<?php
use Illuminate\Support\Facades\DB;
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';
$name = '';$pend = '';$p = 0;
$first = 0;$upper = 0;$lower = 0;$third = 0;$pass = 0;$deferred = 0;$expulsion = 0;$withdraw = 0;$voluntary = 0;$suspension = 0;$others = 0;
$first = 0;$second = 0;$f = 0;$s = 0;$cugp = 0;$a=0;$b=0;$c=0;$d=0;$e=0;$f=0;$class = '';
 set_time_limit(0);
 date_default_timezone_set("Africa/Lagos");
$time = date("h:i:sa");
$date = date('d-m-Y');
 $pdf=new exFPDF();
 $pdf->AddPage();
 $pdf->SetFont('helvetica','',10);
 $pdf->AddFont('lato','','Lato-Regular.php');
 $pdf->AddFont('FontUTF8','','Arimo-Regular.php');
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');


$data['username'] = $id_number;
$data['program'] = $program;
$filteredData = array_filter($data);
$query = DB::table('session_history');
foreach ($filteredData as $key => $value) {
    $query->where($key, $value);
}

$program = $program;
if(isset($id_number)){
$id_number = $id_number;
    $info = DB::table('students')->where(['username' => $id_number])->get();
    foreach ($info as $info) {
      $program = $info -> program;
      $id = $info -> username;
      $name = $info -> fullname;
    }
}
    
$rec = DB::table('program')->where(['code' => $program])->get();
foreach ($rec as $row) {
    $award = $row -> award;
    $faculty = DB::table('faculty')->where(['code' => $row -> faculty])->value('title');
    $dept = $row -> title;
    $duration = ($row -> duration) * 100;
}
$duration = 200;

 //foreach ($users as $key => $row){
    $pic = DB::table('students')->where(['username' => session('username')])->value('picture');

    if($pic){
        $image = 'storage/picture/'.$pic;
        $path_parts = pathinfo($image);
        $ext = strtoupper($path_parts['extension']);
    }else{
        $image = 'card/default.jpg';
        $ext = strtoupper('JPG');
    }


$session_history = $query->where(['next_level' => $duration])->get();
//$session_history = DB::table('session_history')->where(['next_level' => $duration, 'program' => $program])->get();
$sn = 1;
$unit = 0;
$ugp = 0;
$cgpa = 0;
$carry = '';
$status = '';
$class = '';
$f = 0;$flag = 0;
$p = 0;
foreach ($session_history as $results) {
    $id = $results -> username;
    $name = DB::table('students')->where(['username' => $id])->select('fullname')->value('fullname');
    $reg = DB::table('student_course_registration')->where(['username' => $id])->orderBy('username', 'ASC')->get();
    foreach ($reg as $result) {
        $unit = $unit + $result -> unit;
        $ugp = $ugp + $result -> ugp;
        if($result -> grade == 'F'){
          $carry = $carry .' '. $result -> code;
          $f++;
        }
          if($result -> status == 'awaiting'){
            $p++;
            $pend = $pend.' '.$result -> code;
          }
  }
$cgpa = $ugp/$unit;

if($cgpa >= 4.5){$class = 'First Class';if($f == 0)$first++;}
  elseif($cgpa >= 3.5){$class = 'Second Class Upper Division'; if($f == 0)$upper++;
    }elseif($cgpa >= 2.4){$class = 'Second Class Lower Division'; if($f == 0)$lower++;
      }elseif($cgpa >= 1.5){$class = 'Third Class'; if($f == 0)$third++;
          }else{$class = 'Pass'; if($f == 0)$pass++;}

if($f == 0){

    $table=new easyTable($pdf, '{95, 95}', 'width:170; border-color:black; font-size:10; border:1; paddingY:1;');
    $pdf -> Image('uploads/logo.png',2,5,34.8,32,'PNG');
     //$pdf->Ln(20);
     $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:30; align:C;border:0;font-color:#87CEEB;');
     $table->printRow();
     $table->easyCell('(Office of the Registrar)', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
     $table->printRow();
     $table->easyCell('DIRECTORATE OF SENATE AND ACADEMIC MATTERS', 'colspan:14; font-style:B; font-style:B; font-size:14; align:C;border:0;');
     $table->printRow();
     $pdf->SetLineWidth(1.5);
     $y = 40;
    $pdf->Line(0, $y, 250, $y);
     $pdf->Ln(20);
     $pdf->Ln(3);
     $table->easyCell('<b>Ref: R/ACA.267/Vol. XI</b>', 'font-size:12; align:L;border:0;');
     $table->easyCell('<b>Date:</b> 28TH OCTOBER, 2021', 'font-size:12; align:R;border:0;');
     $table->printRow();
     $pdf->Ln(10);
     $table->easyCell(strtoupper($name), 'font-size:12; border:0;');
     $table->printRow();
     $pdf->Ln(10);
     $table->easyCell('<b>DEGREE STATEMENT OF RESULT: CANDIDATE NO:</b> '.$id, ' colspan:2; font-size:12; border:0;');
     $table->printRow();
     $pdf->Ln(10);
     $table->easyCell('I am pleased to inform you that you have been awarded the Degree of <b>'.$award.' (Hons) '.$dept.'</b> with <b>'.$class.'</b> by the Senate of this University.', 'colspan:2; font-size:12; border:0;');
     $table->printRow();
     $pdf->Ln(5);
     $table->easyCell('Certificates, Diplomas and Degrees will be formally awarded to all deserving students at the next Convocation Ceremony of the University.', 'colspan:2; font-size:12; border:0;');
     $table->printRow();
     $pdf->Ln(5);
     $table->easyCell('The issuing of the Degree Certificate itself will take place after the Convocation Ceremony. In the mean time, this letter is being issued to you in lieu of your degree certificate for purposes of employment or admission.', 'colspan:2; font-size:12; border:0;');
     $table->printRow();
     $pdf->Ln(5);
     $table->easyCell('Transcripts of Academic Records are issued to institutions and organizations on request and payment of the appropriate fees.', 'colspan:2; font-size:12; border:0;');
     $table->printRow();
     $pdf->Ln(40);
     
     $table->easyCell('','img:uploads/sign.jpg, w20; colspan:2; font-size:12; align:L;border:0; font-style:U;');
     $table->printRow();
     $table->easyCell('FILIBUS YAMTA MSHELIA', 'colspan:2; font-size:12; align:L;border:0; font-style:B;');
     $table->printRow();
     $pdf->Ln(5);
     $table->easyCell('For: REGISTRAR', 'colspan:2; font-size:12; align:L;border:0;');
     $table->printRow();
     $pdf->Ln(5);
     $table->easyCell("CC: Student's Record File", 'colspan:2; font-size:12; align:L;border:0;');
     $table->printRow();
     $table->endTable(2);
     $pdf->AddPage();

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

//}


 //$pdf->Output('D','permit.pdf',true);
 
 $pdf->Output();  
die;
?>