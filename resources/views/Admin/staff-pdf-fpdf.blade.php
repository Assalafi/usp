<?php
use Illuminate\Support\Facades\DB;

include('../resources/views/pdf/pdf_mc_table.php');
include '../resources/views/pdf/exfpdf.php';
include '../resources/views/pdf/easyTable.php';

set_time_limit(300);
date_default_timezone_set("Africa/Lagos");

class StaffPDF extends exFPDF
{
    function Header()
    {
        // Minimal header - just filter info
        $this->SetY(3);
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 3, 'Staff Export - ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        $this->Ln(1);
    }

    function Footer()
    {
        // Simple footer
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 5, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create PDF instance with LANDSCAPE orientation
$pdf = new StaffPDF();
$pdf->AliasNbPages();
$pdf->AddPage('L'); // LANDSCAPE!
$pdf->SetMargins(1, 15, 1); // 1mm sides, 15mm top for better spacing

// Add filters if any - minimal spacing
if (!empty($filters)) {
    $filterText = '';
    if (!empty($filters['state'])) $filterText .= 'State: ' . $filters['state'] . ' | ';
    if (!empty($filters['lga'])) $filterText .= 'LGA: ' . $filters['lga'] . ' | ';
    if (!empty($filters['gender'])) $filterText .= 'Gender: ' . $filters['gender'] . ' | ';
    if (!empty($filters['faculty'])) $filterText .= 'Faculty: ' . $filters['faculty'] . ' | ';
    if (!empty($filters['department'])) $filterText .= 'Department: ' . $filters['department'] . ' | ';
    if (!empty($filters['program'])) $filterText .= 'Program: ' . $filters['program'] . ' | ';
    if (!empty($filters['unit_id'])) $filterText .= 'Unit: ' . $filters['unit_id'] . ' | ';
    if (!empty($filters['designation_id'])) $filterText .= 'Rank: ' . $filters['designation_id'] . ' | ';
    if (!empty($filters['grade_id'])) $filterText .= 'Grade: ' . $filters['grade_id'] . ' | ';
    if (!empty($filters['step_id'])) $filterText .= 'Step: ' . $filters['step_id'];
    
    if ($filterText) {
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->Cell(0, 2, rtrim($filterText, ' | '), 0, 1, 'L');
        $pdf->Ln(1);
    }
}

// Check if there are staff
if (empty($staff)) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, 'No staff found matching the selected criteria.', 0, 1, 'C');
} else {
    // Create table for LANDSCAPE - ABSOLUTE MAXIMUM WIDTH (297mm - 2mm margins = 295mm)
    // Columns: S/NO(8) SP.NO(15) NAME(35) NIN(18) DOB(14) STATE(18) LGA(18) GENDER(12) DOA(14) DOC(14) RANK(25) DEPT/UNIT(30) PHONE(18) EMAIL(30) = 295
    $table = new easyTable($pdf, '{8, 15, 35, 18, 14, 18, 18, 12, 14, 14, 25, 30, 18, 30}', 'width:295; border-color:black; font-size:6; border:1; paddingY:1; paddingX:1;');
    
    // Add headers
    $table->easyCell('S/NO', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('SP. NO', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('NAME', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('NIN', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('DOB', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('STATE', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('LGA', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('GENDER', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('DATE OF APPT.', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('DATE OF CONF.', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('CURRENT RANK', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('DEPT/UNIT', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('PHONE', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->easyCell('E-MAIL', 'font-style:B; align:C; bgcolor:#f2f2f2;');
    $table->printRow();
    
    // Add data rows
    foreach ($staff as $row) {
        $table->easyCell($row['sno'], 'align:C;');
        $table->easyCell($row['sp_no'], 'align:C;');
        $table->easyCell($row['name'], 'align:L;');
        $table->easyCell($row['nin'], 'align:C; font-size:5;');
        $table->easyCell($row['dob'], 'align:C;');
        $table->easyCell($row['state'], 'align:L;');
        $table->easyCell($row['lga'], 'align:L;');
        $table->easyCell($row['gender'], 'align:C;');
        $table->easyCell($row['date_of_appointment'], 'align:C;');
        $table->easyCell($row['date_of_confirmation'], 'align:C;');
        $table->easyCell($row['current_rank'], 'align:L; font-size:5;');
        $table->easyCell($row['dept_unit'], 'align:L; font-size:5;');
        $table->easyCell($row['phone'], 'align:C;');
        $table->easyCell($row['email'], 'align:L; font-size:5;');
        $table->printRow();
    }
    
    // Add total row in table with colspan
    $table->easyCell('Total: ' . count($staff) . ' staff', 'colspan:14; font-style:B; font-size:7; bgcolor:#f9f9f9; border:1;');
    $table->printRow();
    
    $table->endTable(2);
}

// Output PDF
$pdf->Output('D', 'staff_' . date('Y-m-d_H-i-s') . '.pdf');
?>
