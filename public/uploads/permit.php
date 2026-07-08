<?php
use Illuminate\Support\Facades\DB;
include('pdf_mc_table.php');
 //include 'fpdf/fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';
 set_time_limit(0);
 date_default_timezone_set("Africa/Lagos");
$time = date("h:i:sa");
$originalDate = date("d-m-Y");
$date = date("Y-m-d", strtotime($originalDate));
$data = 'ss';
$users = DB::table('hostel')->where(['id' => $id])->get();
 $pdf=new exFPDF();
 $pdf->AddPage();
 $pdf->SetFont('helvetica','',10);
 $pdf->AddFont('lato','','Lato-Regular.php');
 $pdf->AddFont('FontUTF8','','Arimo-Regular.php');
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php');
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php');
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');
 foreach ($users as $key => $row){
    $pic = DB::table('students')->where(['username' => $row -> occupant])->value('picture');

    if($pic){
        $image = 'storage/picture/'.$pic;
        $path_parts = pathinfo($image);
        $ext = strtoupper($path_parts['extension']);
    }else{
        $image = 'card/default.jpg';
        $ext = strtoupper('JPG');
    }
     $table=new easyTable($pdf, '{11, 40, 13, 13, 14, 10, 10, 10, 10, 10, 10, 10, 11, 18}', 'width:170; border-color:black; font-size:10; border:1; paddingY:0.5;');
     //$pdf->Header();
     $pdf -> Image('uploads/logo.png',1,1,1,1,'PNG');

     try{
        $pdf -> Image($image,160,70,26.8,28,$ext);
    }catch (\Exception $e) {
        $pdf -> Image('card/default.jpg',160,70,26.8,28,'JPG');
    } finally {}
     //$pdf->Ln(20);
     $table->easyCell('','img:uploads/logo.png, w20; colspan:14; font-style:B; align:C; border:0;');
     $table->printRow();
     $table->easyCell('University of Maiduguri', 'colspan:14; font-style:B; font-size:24; align:C;border:0;font-color:#87CEEB;');
     $table->printRow();
     $table->easyCell('(Office of the Vice - Chancellor)', 'colspan:14; font-style:B; font-size:14; align:C;border:0;');
     $table->printRow();
     $pdf->Ln(6);
     $table->easyCell('STUDENT AFFAIRS DIVISION', 'colspan:14; font-style:B; font-style:U; font-size:14; align:C;border:0;');
     $table->printRow();
     $pdf->Ln(20);
     if($row -> gender == 'MALE'){$gender = 'Master';$gen = 'Mr';}else{$gender = 'Mistress';$gen = 'Mrs';}

    // echo $pic;
    // die;

     $face_file= 'uploads/student/'.$pic;
    if (!file_exists($face_file)) {
        $pic = 'default.jpg';
    }

     $table->easyCell('The Hall Officer', 'colspan:7; font-size:12; align:L;border:0; font-style:B;');

     //$pdf -> Image('uploads/student/'.$pic.'.jpg',169,79,30,35,'jpg');
     $table->printRow();
     $table->easyCell($row -> hall.' HALL', 'colspan:7; font-size:12; align:L;border:0; font-style:B;');
     $table->printRow();
     $table->easyCell('University of Maiduguri', 'colspan:7; font-size:12; align:L;border:0; font-style:B;');
     $table->printRow();
     $table->easyCell('Maiduguri.', 'colspan:7; font-size:12; align:L;border:0; font-style:B;');
     $table->printRow();
     $table->easyCell('', 'colspan:7; font-size:12; align:L;border:0; font-style:B;');
     $table->printRow();
     $pdf->Ln(19);

     $session = '2024/2025';
     $table->easyCell('STUDENT ACCOMMODATION PERMIT '.$session, 'colspan:12; font-size:14; align:C;border:0; font-style:U;font-color:#e6ac00;');
     $table->printRow();
     $pdf->Ln(3);

     $name = DB::table('students')->where(['username' => $row -> occupant])->value('fullname');
     $dept = DB::table('students')->where(['username' => $row -> occupant])->value('program');
     $table->easyCell($name.' of the Department of '.$dept.' with ID. No. '.$row -> occupant.' is here by permitted to stay in '.$row -> hall.' HALL of the University', 'colspan:14; font-size:12; align:L;border:0;');
     $table->printRow();

     $pdf->Ln(3);
     $table->easyCell('The Hall Officer of '.$row -> hall.' HALL is requested to accommodate the above named student.', 'colspan:14; font-size:12; align:L;border:0;');
     $table->printRow();
     $pdf->Ln(3);
     $table->easyCell('Room Allocated: '.$row -> block.' Room:'.$row -> room.' Bed:'.$row -> bed, 'colspan:14; font-size:12; align:L;border:0; font-style:B');
     $table->printRow();
     $pdf->Ln(3);
     $table->easyCell('Your allocation of bed space will be revoked if you violate any of the conditions of tenancy agreement.', 'colspan:14; font-size:12; align:L;border:0; font-style:;');
     $table->printRow();
     $pdf->Ln(50);
      // $pdf->Line(10, 235, 10, 265); //left
      // $pdf->Line(10, 235, 200, 235); //top
      // $pdf->Line(200, 235, 200, 265); //right
      // $pdf->Line(10, 265, 200, 265); //bottom


     $table->easyCell('','img:uploads/sign.png, w40; colspan:11; font-size:12; align:L;border:0; font-style:U;');
     $table->printRow();
     $table->easyCell('Dr. Mohammed Yahaya', 'colspan:11; font-size:12; align:L;border:0; font-style:B;');
     $table->printRow();
     $table->easyCell('(Dean of Students)', 'colspan:11; font-size:12; align:L;border:0;');
     $table->printRow();
     $table->easyCell('Date:'.$date, 'colspan:11; font-size:12; align:L;border:0;');
     $table->printRow();
     $table->endTable(2);
     //$pdf->AddPage();
}


 //$pdf->Output('D','permit.pdf',true);

 $pdf->Output();
die;
?>
