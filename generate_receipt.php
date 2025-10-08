<?php
session_start();
include('func.php');
include('newfunc.php');
require_once('TCPDF/tcpdf.php');

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!isset($_GET['pid'])) {
    die("Patient ID is required.");
}

$pid = $_GET['pid'];
$generate_receipt = isset($_GET['generate']) && $_GET['generate'] == 'true';

// Verify access: admin or the patient themselves
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'] ?? '';
if ($role != 'admin' && $_SESSION['pid'] != $pid) {
    die("Access denied.");
}

// Fetch bill data
$bill_query = "SELECT * FROM billtb WHERE pid='$pid'";
$bill_result = mysqli_query($con, $bill_query);
$bill_data = mysqli_fetch_assoc($bill_result);

if (!$bill_data) {
    die("Bill not found.");
}

if ($bill_data['status'] != 'Paid') {
    die("Bill is not paid. Cannot generate receipt.");
}

// Fetch patient data
$patient_query = "SELECT * FROM admissiontb WHERE pid='$pid'";
$patient_result = mysqli_query($con, $patient_query);
$patient_data = mysqli_fetch_assoc($patient_result);

// Fetch doctor data
$doctor_query = "SELECT fname, lname FROM doctortb WHERE username = (SELECT assigned_doctor FROM admissiontb WHERE pid = '$pid')";
$doctor_result = mysqli_query($con, $doctor_query);
$doctor_name = '';
if ($doctor_result && mysqli_num_rows($doctor_result) > 0) {
    $doctor_row = mysqli_fetch_assoc($doctor_result);
    $doctor_name = $doctor_row['fname'] . ' ' . $doctor_row['lname'];
}

// Calculate medicine fees
$medicine_fees_result = mysqli_query($con, "SELECT SUM(price) AS total_medicine_fees FROM prestb WHERE pid = '$pid' AND diagnosis_details IS NOT NULL AND diagnosis_details != ''");
$calculated_medicine_fees = 0;
if ($medicine_fees_result && mysqli_num_rows($medicine_fees_result) > 0) {
    $mf_row = mysqli_fetch_assoc($medicine_fees_result);
    $calculated_medicine_fees = $mf_row['total_medicine_fees'] ?? 0;
}

// Recalculate total
$calculated_total = 
    ($bill_data['consultation_fees'] ?? 0) + 
    ($bill_data['lab_fees'] ?? 0) + 
    $calculated_medicine_fees + 
    ($bill_data['room_charges'] ?? 0) + 
    ($bill_data['service_charges'] ?? 0);

// Create PDF
class ReceiptPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Madridano Health Care Hospital', 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 10, 'OFFICIAL PAYMENT RECEIPT', 0, 1, 'C');
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Thank you for choosing Madridano Health Care Hospital | This is an official receipt', 0, 0, 'C');
    }
}

$pdf = new ReceiptPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Madridano Hospital');
$pdf->SetTitle('Payment Receipt - Patient ID: ' . $pid);
$pdf->SetSubject('Payment Receipt');

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->AddPage();

// Patient Information
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Patient Information', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Receipt Date:', 0, 0);
$pdf->Cell(0, 8, date('F j, Y'), 0, 1);
$pdf->Cell(50, 8, 'Patient ID:', 0, 0);
$pdf->Cell(0, 8, $pid, 0, 1);
$pdf->Cell(50, 8, 'Name:', 0, 0);
$pdf->Cell(0, 8, $patient_data['fname'] . ' ' . $patient_data['lname'], 0, 1);
$pdf->Cell(50, 8, 'Contact:', 0, 0);
$pdf->Cell(0, 8, $patient_data['contact'], 0, 1);
$pdf->Cell(50, 8, 'Doctor:', 0, 0);
$pdf->Cell(0, 8, $doctor_name, 0, 1);
$pdf->Ln(5);

// Bill Breakdown
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Bill Breakdown', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

// Basic Bill Items
$items = [
    ['Consultation Fees', $bill_data['consultation_fees'] ?? 0],
    ['Lab Tests Fees', $bill_data['lab_fees'] ?? 0],
    ['Medicine Fees', $calculated_medicine_fees],
    ['Room Charges', $bill_data['room_charges'] ?? 0],
    ['Service Charges', $bill_data['service_charges'] ?? 0],
    ['Other Charges', $bill_data['other_charges'] ?? 0]
];

foreach ($items as $item) {
    if ($item[1] > 0) {
        $pdf->Cell(120, 8, $item[0] . ':', 0, 0);
        $pdf->Cell(0, 8, '₱' . number_format($item[1], 2), 0, 1, 'R');
    }
}

// Detailed Medicine Breakdown
if ($calculated_medicine_fees > 0) {
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Medicine Details:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $medicine_details_query = "SELECT prescribed_medicines, price FROM prestb WHERE pid = '$pid' AND diagnosis_details IS NOT NULL AND diagnosis_details != ''";
    $medicine_details_result = mysqli_query($con, $medicine_details_query);
    
    while ($med = mysqli_fetch_assoc($medicine_details_result)) {
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(100, 6, '• ' . $med['prescribed_medicines'], 0, 0);
        $pdf->Cell(0, 6, '₱' . number_format($med['price'], 2), 0, 1, 'R');
    }
}

// Detailed Lab Tests
if ($bill_data['lab_fees'] > 0) {
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Lab Tests:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $lab_details_query = "SELECT test_name, price FROM labtesttb WHERE pid = '$pid'";
    $lab_details_result = mysqli_query($con, $lab_details_query);
    
    while ($lab = mysqli_fetch_assoc($lab_details_result)) {
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(100, 6, '• ' . $lab['test_name'], 0, 0);
        $pdf->Cell(0, 6, '₱' . number_format($lab['price'], 2), 0, 1, 'R');
    }
}

// Service Charges Details
if ($bill_data['service_charges'] > 0) {
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Service Charges:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $service_details_query = "SELECT s.service_name, pc.quantity, pc.unit_price, pc.total_price 
                             FROM patient_chargstb pc 
                             JOIN servicestb s ON pc.service_id = s.id 
                             WHERE pc.pid = '$pid'";
    $service_details_result = mysqli_query($con, $service_details_query);
    
    while ($service = mysqli_fetch_assoc($service_details_result)) {
        $pdf->Cell(5, 6, '', 0, 0);
        $pdf->Cell(100, 6, '• ' . $service['service_name'] . ' (Qty: ' . $service['quantity'] . ')', 0, 0);
        $pdf->Cell(0, 6, '₱' . number_format($service['total_price'], 2), 0, 1, 'R');
    }
}

// Total Section
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(120, 8, 'Subtotal:', 0, 0);
$pdf->Cell(0, 8, '₱' . number_format($calculated_total, 2), 0, 1, 'R');

if (($bill_data['discount'] ?? 0) > 0) {
    $pdf->Cell(120, 8, 'Discount:', 0, 0);
    $pdf->Cell(0, 8, '-₱' . number_format($bill_data['discount'], 2), 0, 1, 'R');
}

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(120, 10, 'TOTAL AMOUNT PAID:', 0, 0);
$pdf->Cell(0, 10, '₱' . number_format($calculated_total - ($bill_data['discount'] ?? 0), 2), 0, 1, 'R');

// Payment Information
$pdf->Ln(8);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Payment Information:', 0, 1);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(50, 6, 'Payment Status:', 0, 0);
$pdf->Cell(0, 6, 'PAID', 0, 1);
$pdf->Cell(50, 6, 'Payment Date:', 0, 0);
$pdf->Cell(0, 6, date('F j, Y'), 0, 1);

if (!empty($bill_data['payment_method'])) {
    $pdf->Cell(50, 6, 'Payment Method:', 0, 0);
    $pdf->Cell(0, 6, $bill_data['payment_method'], 0, 1);
}

// Footer Note
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->MultiCell(0, 8, "This receipt confirms that the payment has been received and accepted by the administration.\nAmount paid in full. Thank you for your payment.", 0, 'C');

if ($generate_receipt) {
    $update_query = "UPDATE billtb SET receipt_generated=1 WHERE pid='$pid'";
    mysqli_query($con, $update_query);
}
`
// Output PDF inline
$pdf->Output('receipt_' . $pid . '_' . date('Ymd') . '.pdf', 'I');
?>