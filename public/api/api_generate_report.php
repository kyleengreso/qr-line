<?php
// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");

include './../base.php';
include './../includes/fpdf186/fpdf.php';
include './../includes/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET['year']) && !isset($_GET['month'])) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please provide the tentative year and month."
        ));
        exit;
    }
    // parse start and end datetime
    $date = new DateTime();

    $year = $_GET['year'];
    $month = $_GET['month'];

    $date = new DateTime();
    $date->setDate($year, $month, 1);
    $monthName = $date->format('F');
    // Transfer value month to words like January, February, etc.
    // i mean thats tentative
    
    if (isset($_GET['payment'])) {
        $payment_type = $_GET['payment'];
    }

    class PDF extends FPDF { 
        // Page header 
        function Header() 
        { 
            global $project_name, $project_name_full;
            global $start_date_str, $end_date_str;
            global $year, $monthName;
            // Logo
            $this->Image('./../asset/images/favicon.png',15,15,20);
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
            $this->Cell(50,10,'Generated Report by: Administrators',0,0,'L');
            $this->Ln(5);
            $this->Cell(50, 10, 'Transaction Date: ' . $monthName . ' ' . $year, 0, 0, 'L');
            $this->Ln(10);
        } 

        // Page footer 
        function Footer() 
        { 
            // Position at 1.5 cm from bottom 
            $this->SetY(-15); 
            
            // Set font-family and font-size of footer. 
            $this->SetFont('Arial', 'I', 8); 

            // set page number 
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C'); 
        } 
    } 

    global $conn;

    // $data = json_decode(file_get_contents("php://input"), true);

    $pdf = new PDF();
    $pdf->AliasNbPages();

    //////////////////////////////////////////////////

    // Total transactions
    $pdf->AddPage();
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

    $sql_cmd = "SELECT * FROM requesters
                WHERE (YEAR(created_at) = ? AND MONTH(created_at) = ?)";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("ss", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    foreach ($result as $row) {
        $d = new DateTime($row['created_at']);
        $created_at = $d->format('Y-m-d h:i:s A');
        $pdf->Cell(50,10,$created_at,1,0,'C');
        $pdf->Cell(60,10,$row['name'],1,0,'C');
        $pdf->Cell(50,10,$row['email'],1,0,'C');
        $pdf->Cell(30,10,$row['payment'],1,0,'C');
        $pdf->Ln(10);
    }


    /////////////////////////////////////////////////

    // Registrar transactions
    $pdf->AddPage();
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

    $sql_cmd = "SELECT * FROM requesters
                WHERE (YEAR(created_at) = ? AND MONTH(created_at) = ?) AND payment = 'registrar'";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("ss", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    foreach ($result as $row) {
        $d = new DateTime($row['created_at']);
        $created_at = $d->format('Y-m-d h:i:s A');
        $pdf->Cell(50,10,$created_at,1,0,'C');
        $pdf->Cell(50,10,$row['created_at'],1,0,'C');
        $pdf->Cell(60,10,$row['name'],1,0,'C');
        $pdf->Cell(50,10,$row['email'],1,0,'C');
        $pdf->Cell(30,10,$row['payment'],1,0,'C');
        $pdf->Ln(10);
    }

    /////////////////////////////////////////////////

    // Assessment transactions
    $pdf->AddPage();
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

    $sql_cmd = "SELECT * FROM requesters
                WHERE (YEAR(created_at) = ? AND MONTH(created_at) = ?) AND payment = 'assessment'";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("ss", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    foreach ($result as $row) {
        $d = new DateTime($row['created_at']);
        $created_at = $d->format('Y-m-d h:i:s A');
        $pdf->Cell(50,10,$created_at,1,0,'C');
        $pdf->Cell(60,10,$row['name'],1,0,'C');
        $pdf->Cell(50,10,$row['email'],1,0,'C');
        $pdf->Cell(30,10,$row['payment'],1,0,'C');
        $pdf->Ln(10);
    }

    $pdf->Output();


} else if (isset($data['datetime_start']) && isset($data['datetime_end'])) {
    $datetime_start = $data['datetime_start'];
    $datetime_end = $data['datetime_end'];
    $payment_type = $data['payment'];


} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Please provide the start and end date."
    ));
    exit;
}


?>