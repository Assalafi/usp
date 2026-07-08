<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

include('pdf_mc_table.php');
include 'exfpdf.php';
include 'easyTable.php';
function codeMod($input) {
    return preg_replace('/([A-Za-z]+)([0-9]+)/', '$1 $2', $input);
}
function password($length = 8) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
}
function firstUpper($input) {
    $excludeWords = ['or', 'and', 'of', 'the', 'in', 'on', 'at', 'by', 'for', 'to'];
    $input = ucwords(strtolower($input));
    $words = explode(' ', $input);
    foreach ($words as &$word) {
        if (in_array(strtolower($word), $excludeWords)) {
            $word = strtolower($word);
        }
    }
    return implode(' ', $words);
}
class PDFWithWatermark extends exFPDF
{
    protected $angle = 0;

    function Header()
    {
        // Add watermark on every page
        $this->SetFont('Arial', 'B', 80);
        $this->SetTextColor(255, 192, 203); // Pink color for the watermark

        // Calculate the center position
        $textWidth = $this->GetStringWidth('CONFIDENTIAL');
        $x = ($this->GetPageWidth() - $textWidth) / 2;
        $y = ($this->GetPageHeight() / 2);

        // Rotate and print the watermark
        $this->RotateText($x, $y, 'CONFIDENTIAL', 45);

        // Reset the rotation back to 0
        $this->Rotate(0);
    }

    function RotateText($x, $y, $txt, $angle)
    {
        // Rotate around a point
        $this->Rotate($angle, $x, $y);
        $this->Text(-20, 210, $txt);
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.2F %.2F %.2F %.2F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

$pdf = new PDFWithWatermark();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$pdf->AddFont('lato', '', 'Lato-Regular.php');
$pdf->AddFont('FontUTF8', '', 'Arimo-Regular.php');
$pdf->AddFont('FontUTF8', 'B', 'Arimo-Bold.php');
$pdf->AddFont('FontUTF8', 'BI', 'Arimo-BoldItalic.php');
$pdf->AddFont('FontUTF8', 'I', 'Arimo-Italic.php');

// Table initialization
$table = new easyTable($pdf, '{12, 20, 30, 30, 30, 20, 30}', 'width:170; border-color:black; font-size:10; border:1; paddingY:2;');

    // Table header
    $table->easyCell('', 'img:uploads/logo.png, w30; colspan:14; font-style:B; align:C; border:0;');
    $table->printRow();
    $table->easyCell('UNIVERSITY OF MAIDUGURI', 'colspan:14; font-style:B; font-size:18; align:C;border:0;font-color:#87CEEB;');
    $table->printRow();

    // Grades header
    $table->rowStyle('align:{CCCCCCC}; font-style:B;');
    $table->easyCell("SN");
    $table->easyCell("SP");
    $table->easyCell("NAME");
    $table->easyCell("DEPT/UNIT");
    $table->easyCell("DESIGNATION");
    $table->easyCell("PHONE");
    $table->easyCell("PASSWORD");
    $table->printRow();
    $pdf->Ln(5);

    // Table body (data rows)
    $x = 0;
    foreach ($data as $row) {
        $pass = password();
        if(User::where(['username' => $row->username])->update(['password' => Hash::make(strtoupper($pass))])){
        $x++;
        $table->rowStyle('align:{CLLLLCC}; font-size:8;');
        $table->easyCell($x);
        $table->easyCell($row->username);
        $table->easyCell($row->name);
        $table->easyCell($row->unit);
        $table->easyCell($row->current_rank);
        $table->easyCell($row->phone);
        $table->easyCell($pass);
        $table->printRow();
        }

    $pdf->Ln(5);

    }

$table->endTable(4);
$pdf->Output();
die;
