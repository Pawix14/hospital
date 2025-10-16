<?php
session_start();
include('func.php');
include('newfunc.php');
require_once('TCPDF/tcpdf.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

if (!isset($_GET['bill_id'])) {
    die("Bill ID is required.");
}
$bill_id = $_GET['bill_id'];

$role = $_SESSION['role'] ?? '';
// Fetch bill data first to get patient ID for access check
$bill_query = "SELECT b.*, a.fname, a.lname, a.contact, a.pid FROM billtb b JOIN admissiontb a ON b.pid = a.pid WHERE b.id='$bill_id'";
$bill_result = mysqli_query($con, $bill_query);
$bill_data = mysqli_fetch_assoc($bill_result);

if (!$bill_data) {
    die("Bill not found.");
}

if ($role != 'cashier' && $role != 'admin' && (!isset($_SESSION['pid']) || $_SESSION['pid'] != $bill_data['pid'])) {
    die("Access denied.");
}

// Check if bill is paid
if ($bill_data['status'] != 'Paid') {
    die("Invoice can only be generated for paid bills.");
}

$patient_query = "SELECT * FROM admissiontb WHERE pid='{$bill_data['pid']}'";
$patient_result = mysqli_query($con, $patient_query);
$patient_data = mysqli_fetch_assoc($patient_result);

$doctor_query = "SELECT fname, lname FROM doctortb WHERE username = (SELECT assigned_doctor FROM admissiontb WHERE pid = '{$bill_data['pid']}')";
$doctor_result = mysqli_query($con, $doctor_query);
$doctor_name = '';
if ($doctor_result && mysqli_num_rows($doctor_result) > 0) {
    $doctor_row = mysqli_fetch_assoc($doctor_result);
    $doctor_name = $doctor_row['fname'] . ' ' . $doctor_row['lname'];
}

// Calculate totals
$calculated_total = ($bill_data['consultation_fees'] ?? 0) + ($bill_data['lab_fees'] ?? 0) + ($bill_data['medicine_fees'] ?? 0) + ($bill_data['room_charges'] ?? 0) + ($bill_data['service_charges'] ?? 0);

$insurance_coverage_percent = 0;
$insurance_coverage_amount = 0;
$insurance_query = "SELECT coverage_percent FROM patient_insurancetb WHERE patient_id='{$bill_data['pid']}' AND status='active' ORDER BY start_date DESC LIMIT 1";
$insurance_result = mysqli_query($con, $insurance_query);
if ($insurance_result && mysqli_num_rows($insurance_result) > 0) {
    $insurance_row = mysqli_fetch_assoc($insurance_result);
    $insurance_coverage_percent = floatval($insurance_row['coverage_percent']);
    $insurance_coverage_amount = ($insurance_coverage_percent / 100) * $calculated_total;
}

$final_total = $calculated_total - $insurance_coverage_amount - ($bill_data['discount'] ?? 0);

class ProfessionalInvoicePDF extends TCPDF {

    public function Header() {
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);

        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 12, 'MADRIDANO HOSPITAL', 0, 1, 'C', true);

        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'OFFICIAL INVOICE', 0, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Cagayan De Oro City | Phinma COC', 0, 1, 'C', true);
        $this->Cell(0, 5, 'Phone: 09940213443 | Email: gama.madridano.coc@phinmaed.com', 0, 1, 'C', true);

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);

        $this->Ln(8);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Thank you for choosing Madridano Hospital | Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }

    public function addSectionHeader($title) {
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Ln(2);
    }
}

// CREATE PDF
$pdf = new ProfessionalInvoicePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Madridano Hospital');
$pdf->SetAuthor('Madridano Hospital');
$pdf->SetTitle('Invoice - Bill ID: ' . $bill_id);
$pdf->SetSubject('Official Invoice');

$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// INVOICE HEADER
$pdf->addSectionHeader('INVOICE DETAILS');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Invoice Date:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, date('F j, Y'), 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 6, 'Invoice No:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'INV-' . $bill_id . '-' . date('Ymd'), 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Bill ID:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, $bill_id, 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 6, 'Patient ID:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, $bill_data['pid'], 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Patient Name:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, $patient_data['fname'] . ' ' . $patient_data['lname'], 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 6, 'Contact:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, $patient_data['contact'], 0, 1);

if (!empty($doctor_name)) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Doctor:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $doctor_name, 0, 1);
}

$pdf->Ln(8);

// BILL BREAKDOWN
$pdf->addSectionHeader('BILL BREAKDOWN');

$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(120, 8, 'DESCRIPTION', 1, 0, 'L', true);
$pdf->Cell(40, 8, 'AMOUNT', 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);

if (($bill_data['consultation_fees'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Consultation Fees', 1, 0);
    $pdf->Cell(40, 7, number_format($bill_data['consultation_fees'], 2), 1, 1, 'R');
}

if (($bill_data['lab_fees'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Laboratory Tests', 1, 0);
    $pdf->Cell(40, 7, number_format($bill_data['lab_fees'], 2), 1, 1, 'R');
}

if (($bill_data['medicine_fees'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Medicines & Prescriptions', 1, 0);
    $pdf->Cell(40, 7, number_format($bill_data['medicine_fees'], 2), 1, 1, 'R');
}

if (($bill_data['room_charges'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Room Charges', 1, 0);
    $pdf->Cell(40, 7, number_format($bill_data['room_charges'], 2), 1, 1, 'R');
}

if (($bill_data['service_charges'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Service Charges', 1, 0);
    $pdf->Cell(40, 7, number_format($bill_data['service_charges'], 2), 1, 1, 'R');
}

if (($bill_data['other_charges'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Other Charges', 1, 0);
    $pdf->Cell(40, 7, number_format($bill_data['other_charges'], 2), 1, 1, 'R');
}

// SUBTOTAL
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(120, 8, 'SUBTOTAL', 1, 0, 'R');
$pdf->Cell(40, 8, number_format($calculated_total, 2), 1, 1, 'R');

if ($insurance_coverage_percent > 0) {
    $pdf->Cell(120, 8, 'INSURANCE COVERAGE (' . number_format($insurance_coverage_percent, 2) . '%)', 1, 0, 'R');
    $pdf->Cell(40, 8, '-' . number_format($insurance_coverage_amount, 2), 1, 1, 'R');
}

if (($bill_data['discount'] ?? 0) > 0) {
    $pdf->Cell(120, 8, 'DISCOUNT', 1, 0, 'R');
    $pdf->Cell(40, 8, '-' . number_format($bill_data['discount'], 2), 1, 1, 'R');
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(120, 10, 'TOTAL AMOUNT DUE', 1, 0, 'R', true);
$pdf->Cell(40, 10, '₱ ' . number_format($final_total, 2), 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);

// PAYMENT STATUS
$pdf->addSectionHeader('PAYMENT STATUS');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Payment Status:', 0, 0);
$pdf->SetTextColor($bill_data['status'] == 'Paid' ? 0 : 255, $bill_data['status'] == 'Paid' ? 128 : 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, strtoupper($bill_data['status']), 0, 1);
$pdf->SetTextColor(0, 0, 0);

if ($bill_data['status'] == 'Paid') {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Payment Date:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $bill_data['payment_date'] ?? date('F j, Y'), 0, 1);

    if (!empty($bill_data['payment_method'])) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Payment Method:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $bill_data['payment_method'], 0, 1);
    }
}

$pdf->Ln(8);

// AUTHORIZATION
$pdf->addSectionHeader('AUTHORIZATION');

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, "This invoice is an official document from Madridano Hospital. Payment is due upon receipt. For any questions regarding this invoice, please contact our billing department.");

$pdf->Ln(8);

// Signature lines
$pdf->Cell(90, 6, '_________________________', 0, 0, 'C');
$pdf->Cell(20, 6, '', 0, 0);
$pdf->Cell(90, 6, '_________________________', 0, 1, 'C');

$pdf->Cell(90, 6, 'Hospital Cashier', 0, 0, 'C');
$pdf->Cell(20, 6, '', 0, 0);
$pdf->Cell(90, 6, 'Patient/Representative', 0, 1, 'C');

$pdf->Ln(5);

// Final note
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 4, "Note: This invoice serves as a record of services provided. Please retain for your records. For payment inquiries, contact 09940213443.");

// Output the PDF
$pdf->Output('invoice_' . $bill_id . '_' . date('Ymd') . '.pdf', 'I');
?>
