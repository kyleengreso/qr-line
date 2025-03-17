<?php
require('fpdf.php');
include_once './../../includes/db_conn.php';
include_once './../../base.php';
class PDF extends FPDF
{
// Page header
function Header()
{
    global $project_name, $project_name_full;
    // Logo
    $this->Image('./../../asset/images/favicon.png',15,15,20);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(90);
    // Title
    $this->Cell(15,30,$project_name_full,0,0,'C');
    $this->SetFont('Arial','',10);
    $this->Ln(5);
    $this->Cell(90);
    $this->Cell(20,30,'Tiniguiban Heights, Puerto Princesa, 5300',0,0,'C');
    $this->Ln(5);
    $this->Cell(90);
    $this->Cell(20,30,'Palawan, Philippines',0,0,'C');
    $this->Ln(20);
    $this->Cell(70);
    $this->SetFont('Arial','B',12);
    $this->Cell(50,10,'GENERATED REPORT',1,0,'C');
    $this->SetFont('Arial','',10);

    // Generate Report Info
    $this->Ln(10);
    $this->Cell(50,10,'Generated Report by: marc',0,0,'L');
    $this->Ln(5);
    $this->Cell(50,10,'Start Date: 2021-10-10 00:00:00 AM',0,0,'L');
    $this->Ln(5);
    $this->Cell(50,10,'End Date: 2021-10-10 00:00:00 AM',0,0,'L');
    $this->Ln(10);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','B',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// First Page
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Total Transactions',0,1,'C');
$pdf->SetFont('Arial','',10);

// Table Header
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,10,'Transaction Time',1,0,'C');
$pdf->Cell(60,10,'Name',1,0,'C');
$pdf->Cell(50,10,'Email',1,0,'C');
$pdf->Cell(30,10,'Payment',1,0,'C');
$pdf->SetFont('Arial','',10);
$pdf->Ln(10);

$start_date_str = '2025-03-14 00:00:00 AM';
$end_date_str = '2025-03-15 00:00:00 AM';

$sql_cmd = "SELECT * FROM requesters WHERE created_at BETWEEN '$start_date_str' AND '$end_date_str'";
$stmt = $conn->prepare($sql_cmd);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
foreach ($result as $row) {
    $pdf->Cell(50,10,$row['created_at'],1,0,'C');
    $pdf->Cell(60,10,$row['name'],1,0,'C');
    $pdf->Cell(50,10,$row['email'],1,0,'C');
    $pdf->Cell(30,10,$row['payment'],1,0,'C');
    $pdf->Ln(10);
}

// Payment Section
$pdf->AddPage();

// Second Page
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Registrar',0,1,'C');
$pdf->SetFont('Arial','',10);

// Table Header
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,10,'Transaction Time',1,0,'C');
$pdf->Cell(60,10,'Name',1,0,'C');
$pdf->Cell(50,10,'Email',1,0,'C');
$pdf->Cell(30,10,'Payment',1,0,'C');
$pdf->SetFont('Arial','',10);
$pdf->Ln(10);

$sql_cmd = "SELECT * FROM requesters WHERE created_at BETWEEN '$start_date_str' AND '$end_date_str' AND payment = 'registrar'";
$stmt = $conn->prepare($sql_cmd);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
foreach ($result as $row) {
    $pdf->Cell(50,10,$row['created_at'],1,0,'C');
    $pdf->Cell(60,10,$row['name'],1,0,'C');
    $pdf->Cell(50,10,$row['email'],1,0,'C');
    $pdf->Cell(30,10,$row['payment'],1,0,'C');
    $pdf->Ln(10);
}

// Assessment Section
$pdf->AddPage();

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Assessment',0,1,'C');
$pdf->SetFont('Arial','',10);

// Table Header
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,10,'Transaction Time',1,0,'C');
$pdf->Cell(60,10,'Name',1,0,'C');
$pdf->Cell(50,10,'Email',1,0,'C');
$pdf->Cell(30,10,'Payment',1,0,'C');
$pdf->SetFont('Arial','',10);
$pdf->Ln(10);
$sql_cmd = "SELECT * FROM requesters WHERE created_at BETWEEN '$start_date_str' AND '$end_date_str' AND payment = 'assessment'";
$stmt = $conn->prepare($sql_cmd);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
foreach ($result as $row) {
    $pdf->Cell(50,10,$row['created_at'],1,0,'C');
    $pdf->Cell(60,10,$row['name'],1,0,'C');
    $pdf->Cell(50,10,$row['email'],1,0,'C');
    $pdf->Cell(30,10,$row['payment'],1,0,'C');
    $pdf->Ln(10);
}
// for($i=1;$i<=40;$i++)
//     $pdf->Cell(0,10,'Printing line number '.$i,0,1);
$pdf->Output();
?>