<?php
    require('fpdf/fpdf.php');
    use Illuminate\Support\Facades\DB;
    use App\Models\Student;
    use Illuminate\Database\QueryException;
    use Endroid\QrCode\QrCode;
    use Endroid\QrCode\Writer\PngWriter;

    if(isset($id)){
        $data = Student::where('id', $id)->select('fullname', 'username', 'jamb_no', 'faculty', 'program', 'state_origin', 'country', 'kin_name', 'kin_phone', 'picture', 'signiture')->get();
    }

    $pdf=new FPDF();
    foreach ($data as $row) {
        $name = $row->fullname;
        if($row->username){
            $id = $row->username;
        }else{
            $id = $row->jamb_no;
        }
        $faculty = $row->faculty;
        $course = $row->program;
        $state = $row->state_origin;
        $nationality = $row->country;
        $kin_name = $row->kin_name;
        $kin_phone = $row->kin_phone;
        if($row->picture){
            $image = 'storage/picture/'.$row->picture;
            $path_parts = pathinfo($image);
            $ext = strtoupper($path_parts['extension']);
        }else{
            $image = 'card/default.jpg';
            $ext = strtoupper('JPG');
        }

        if($row->signiture){
            $signature = 'storage/signature/'.$row->signiture;
            $path_parts = pathinfo($signature);
            $ext = strtoupper($path_parts['extension']);
        }else{
            $signature = 'card/student sign.png';
            $exts = strtoupper('PNG');
        }

    $pdf->AddPage();
    $pdf->SetFont('times','',10);

    $pdf -> Image('card/front.png',75,25,54,86,'PNG');
    $pdf -> Image('card/back.png',75,115,54,86,'PNG');
    //$pdf -> Image($image,228,59,43,47,$ext);
    try{
        $pdf -> Image($image,92.5,50.3,18.8,20,$ext);
    }catch (\Exception $e) {
        $pdf -> Image('card/default.jpg',92.5,50.3,18.8,20,'JPG');
    } finally {}

    try{
        $pdf -> Image($signature,97,175,10,5,$exts);
    }catch (\Exception $e) {
        $pdf -> Image('card/student sign.png',97,175,10,5,'PNG');
    } finally {}

    $pdf -> Image('card/registrar sign.png',97,185,10,5,'PNG');

    $pdf->SetY(74.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(54, 10, $name, 0, 1, 'C');

    $pdf->SetY(76.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $id, 0, 1, 'C');

    $pdf->SetY(81.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, DB::table('faculty')->where('code', $faculty)->value('title'), 0, 1, 'C');

    $pdf->SetY(86.9);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, DB::table('program')->where('code', $course)->value('title'), 0, 1, 'C');

    $pdf->SetY(92.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $state, 0, 1, 'C');

    $pdf->SetY(98);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $nationality, 0, 1, 'C');

    $pdf->SetY(151);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, date('d/m/Y'), 0, 1, 'C');

    $pdf->SetY(156);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, date('Y')+5, 0, 1, 'C');

    $pdf->SetY(163);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $kin_name, 0, 1, 'C');

    $pdf->SetY(165.5);
    $pdf->SetX(75);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(54, 10, $kin_phone, 0, 1, 'C');


    $qrCode = new QrCode($name.' With ID NO:'.$id.' From '.DB::table('program')->where('code', $course)->value('title'));
    $qrCode->setSize(60);
    $qrCode->setMargin(-10);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    file_put_contents('uploads/qrcode.png', $result->getString());
    $pdf->Image('uploads/qrcode.png', 96.75, 122, 0, 0, 'PNG');

    }

    $pdf->Output();
    exit;
?>
