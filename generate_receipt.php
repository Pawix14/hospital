<?php
session_start();
include('func.php');
include('newfunc.php');
require_once('TCPDF/tcpdf.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

if (!isset($_GET['pid'])) {
    die("Patient ID is required.");
}
$pid = $_GET['pid'];
$generate_receipt = isset($_GET['generate']) && $_GET['generate'] == 'true';
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
$role = $_SESSION['role'] ?? '';
if ($role != 'admin' && $_SESSION['pid'] != $pid) {
    die("Access denied.");
}
$bill_query = "SELECT * FROM billtb WHERE pid='$pid'";
$bill_result = mysqli_query($con, $bill_query);
$bill_data = mysqli_fetch_assoc($bill_result);

if (!$bill_data) {
    die("Bill not found.");
}
if ($bill_data['status'] != 'Paid') {
    die("Bill is not paid. Cannot generate receipt.");
}
$patient_query = "SELECT * FROM admissiontb WHERE pid='$pid'";
$patient_result = mysqli_query($con, $patient_query);
$patient_data = mysqli_fetch_assoc($patient_result);

$doctor_query = "SELECT fname, lname FROM doctortb WHERE username = (SELECT assigned_doctor FROM admissiontb WHERE pid = '$pid')";
$doctor_result = mysqli_query($con, $doctor_query);
$doctor_name = '';
if ($doctor_result && mysqli_num_rows($doctor_result) > 0) {
    $doctor_row = mysqli_fetch_assoc($doctor_result);
    $doctor_name = $doctor_row['fname'] . ' ' . $doctor_row['lname'];
}
$medicine_details_query = "SELECT prescribed_medicines, dosage, frequency, duration, price, prescription 
                          FROM prestb 
                          WHERE pid = '$pid' 
                          AND (prescribed_medicines IS NOT NULL AND prescribed_medicines != '')";
$medicine_details_result = mysqli_query($con, $medicine_details_query);
$medicine_details = [];
$calculated_medicine_fees = 0;

while ($med = mysqli_fetch_assoc($medicine_details_result)) {
    if (strpos($med['prescribed_medicines'], ',') !== false) {
        $medicines = explode(', ', $med['prescribed_medicines']);
        $dosages = explode(',', $med['dosage']);
        $frequencies = explode(',', $med['frequency']);
        $durations = explode(',', $med['duration']);
        
        for ($i = 0; $i < count($medicines); $i++) {
            $medicine_name = trim($medicines[$i] ?? '');
            if (!empty($medicine_name)) {
                $medicine_details[] = [
                    'name' => $medicine_name,
                    'dosage' => trim($dosages[$i] ?? ''),
                    'frequency' => trim($frequencies[$i] ?? ''),
                    'duration' => trim($durations[$i] ?? ''),
                    'price' => ($i == 0) ? $med['price'] : 0 // Only charge once per prescription
                ];
            }
        }
    } else {
        $medicine_name = trim($med['prescribed_medicines']);
        if (!empty($medicine_name)) {
            $medicine_details[] = [
                'name' => $medicine_name,
                'dosage' => $med['dosage'] ?? '',
                'frequency' => $med['frequency'] ?? '',
                'duration' => $med['duration'] ?? '',
                'price' => $med['price']
            ];
        }
    }
    $calculated_medicine_fees += $med['price'];
}

$fallback_medicine_query = "SELECT prescription, price FROM prestb WHERE pid = '$pid' AND (prescription IS NOT NULL AND prescription != '')";
$fallback_medicine_result = mysqli_query($con, $fallback_medicine_query);
while ($fallback_med = mysqli_fetch_assoc($fallback_medicine_result)) {
    if (!empty($fallback_med['prescription']) && count($medicine_details) == 0) {
        $medicine_names = explode(', ', $fallback_med['prescription']);
        foreach ($medicine_names as $med_name) {
            $med_name = trim($med_name);
            if (!empty($med_name)) {
                $medicine_details[] = [
                    'name' => $med_name,
                    'dosage' => '',
                    'frequency' => '',
                    'duration' => '',
                    'price' => $fallback_med['price']
                ];
            }
        }
        $calculated_medicine_fees += $fallback_med['price'];
    }
}

$calculated_total = 
    ($bill_data['consultation_fees'] ?? 0) + 
    ($bill_data['lab_fees'] ?? 0) + 
    $calculated_medicine_fees + 
    ($bill_data['room_charges'] ?? 0) + 
    ($bill_data['service_charges'] ?? 0);

class ProfessionalReceiptPDF extends TCPDF {
    
    public function Header() {
        // Set header background color (dark blue)
        $this->SetFillColor(44, 62, 80);
        $this->SetTextColor(255, 255, 255);
        
        // Hospital name
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 12, 'MADRIDANO HOSPITAL', 0, 1, 'C', true);
        
        // Receipt title
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'OFFICIAL PAYMENT RECEIPT', 0, 1, 'C', true);
        
        // Hospital contact info
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Cagayan De Oro City | Phinma COC', 0, 1, 'C', true);
        $this->Cell(0, 5, 'Phone: 09940213443 | Email: gama.madridano.coc@phinmaed.com', 0, 1, 'C', true);
        
        // Reset colors for content
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
    
    // Add a proper section header
    public function addSectionHeader($title) {
        $this->SetFillColor(74, 107, 220);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Ln(2);
    }
}

// CREATE PDF with proper UTF-8 encoding
$pdf = new ProfessionalReceiptPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information with UTF-8
$pdf->SetCreator('Madridano Hospital');
$pdf->SetAuthor('Madridano Hospital');
$pdf->SetTitle('Payment Receipt - Patient ID: ' . $pid);
$pdf->SetSubject('Official Payment Receipt');

// Set proper margins
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// Use proper UTF-8 compatible peso sign
$peso_sign = "₱";

// PATIENT INFORMATION SECTION
$pdf->addSectionHeader('PATIENT INFORMATION');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Receipt Date:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, date('F j, Y'), 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 6, 'Receipt No:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'RC-' . $pid . '-' . date('Ymd'), 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Patient ID:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, $pid, 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 6, 'Patient Name:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, $patient_data['fname'] . ' ' . $patient_data['lname'], 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Contact:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 6, $patient_data['contact'], 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 6, 'Doctor:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, $doctor_name, 0, 1);

$pdf->Ln(8);

// BILL BREAKDOWN SECTION
$pdf->addSectionHeader('BILL BREAKDOWN');

// Table header
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(120, 8, 'DESCRIPTION', 1, 0, 'L', true);
$pdf->Cell(30, 8, 'QTY', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'AMOUNT', 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);

// Consultation Fees
if (($bill_data['consultation_fees'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Consultation Fees', 1, 0);
    $pdf->Cell(30, 7, '1', 1, 0, 'C');
    $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($bill_data['consultation_fees'], 2), 1, 1, 'R');
}

// Lab Tests
if (($bill_data['lab_fees'] ?? 0) > 0) {
    $lab_details_query = "SELECT test_name, price FROM labtesttb WHERE pid = '$pid'";
    $lab_details_result = mysqli_query($con, $lab_details_query);
    
    // First show the total lab fees line
    $pdf->Cell(120, 7, 'Laboratory Tests', 1, 0);
    $pdf->Cell(30, 7, '1', 1, 0, 'C');
    $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($bill_data['lab_fees'], 2), 1, 1, 'R');
    
    // Then show individual tests as sub-items
    while ($lab = mysqli_fetch_assoc($lab_details_result)) {
        $pdf->Cell(10, 7, '', 0, 0); // Indentation
        $pdf->Cell(110, 7, '• ' . $lab['test_name'], 1, 0);
        $pdf->Cell(30, 7, '1', 1, 0, 'C');
        $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($lab['price'], 2), 1, 1, 'R');
    }
}

// MEDICINE FEES - FIXED SECTION
if (count($medicine_details) > 0 || $calculated_medicine_fees > 0) {
    $pdf->Cell(120, 7, 'Medicines & Prescriptions', 1, 0);
    $pdf->Cell(30, 7, count($medicine_details), 1, 0, 'C');
    $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($calculated_medicine_fees, 2), 1, 1, 'R');
    
    // Show individual medicines as sub-items
    if (count($medicine_details) > 0) {
        foreach ($medicine_details as $medicine) {
            $pdf->Cell(10, 7, '', 0, 0); // Indentation
            $medicine_text = '• ' . $medicine['name'];
            if (!empty($medicine['dosage'])) {
                $medicine_text .= ' (' . $medicine['dosage'] . ')';
            }
            $pdf->Cell(110, 7, $medicine_text, 1, 0);
            $pdf->Cell(30, 7, '1', 1, 0, 'C');
            $price_display = ($medicine['price'] > 0) ? $peso_sign . ' ' . number_format($medicine['price'], 2) : 'Included';
            $pdf->Cell(40, 7, $price_display, 1, 1, 'R');
        }
    } else {
        // If we have medicine fees but no details, show a generic line
        $pdf->Cell(10, 7, '', 0, 0);
        $pdf->Cell(110, 7, '• Prescribed Medications', 1, 0);
        $pdf->Cell(30, 7, '1', 1, 0, 'C');
        $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($calculated_medicine_fees, 2), 1, 1, 'R');
    }
}

// Room Charges
if (($bill_data['room_charges'] ?? 0) > 0) {
    $room_query = "SELECT room_number FROM admissiontb WHERE pid = '$pid'";
    $room_result = mysqli_query($con, $room_query);
    $room_data = mysqli_fetch_assoc($room_result);
    $room_number = $room_data['room_number'] ?? '';
    $room_type = "";
    if (strpos($room_number, '101') !== false || strpos($room_number, '102') !== false || strpos($room_number, '103') !== false) {
        $room_type = "General Ward";
    } elseif (strpos($room_number, '201') !== false || strpos($room_number, '202') !== false || strpos($room_number, '203') !== false) {
        $room_type = "Private Room";
    } elseif (strpos($room_number, '301') !== false || strpos($room_number, '302') !== false) {
        $room_type = "ICU";
    } elseif (strpos($room_number, '401') !== false || strpos($room_number, '402') !== false) {
        $room_type = "Emergency";
    }
    $room_info = $room_type ? $room_type . ' (Room ' . $room_number . ')' : 'Room Charges';

    $pdf->Cell(120, 7, $room_info, 1, 0);
    $pdf->Cell(30, 7, '1', 1, 0, 'C');
    $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($bill_data['room_charges'], 2), 1, 1, 'R');
}

// Service Charges
if (($bill_data['service_charges'] ?? 0) > 0) {
    $service_details_query = "SELECT s.service_name, pc.quantity, pc.total_price 
                             FROM patient_chargstb pc 
                             JOIN servicestb s ON pc.service_id = s.id 
                             WHERE pc.pid = '$pid'";
    $service_details_result = mysqli_query($con, $service_details_query);
    
    // First show the total service charges line
    $pdf->Cell(120, 7, 'Service Charges', 1, 0);
    $pdf->Cell(30, 7, '1', 1, 0, 'C');
    $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($bill_data['service_charges'], 2), 1, 1, 'R');
    
    // Then show individual services as sub-items
    while ($service = mysqli_fetch_assoc($service_details_result)) {
        $pdf->Cell(10, 7, '', 0, 0); // Indentation
        $pdf->Cell(110, 7, '• ' . $service['service_name'], 1, 0);
        $pdf->Cell(30, 7, $service['quantity'], 1, 0, 'C');
        $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($service['total_price'], 2), 1, 1, 'R');
    }
}

// Other Charges
if (($bill_data['other_charges'] ?? 0) > 0) {
    $pdf->Cell(120, 7, 'Other Charges', 1, 0);
    $pdf->Cell(30, 7, '1', 1, 0, 'C');
    $pdf->Cell(40, 7, $peso_sign . ' ' . number_format($bill_data['other_charges'], 2), 1, 1, 'R');
}

// TOTAL SECTION
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(120, 8, 'SUBTOTAL', 1, 0, 'R');
$pdf->Cell(30, 8, '', 1, 0, 'C');
$pdf->Cell(40, 8, $peso_sign . ' ' . number_format($calculated_total, 2), 1, 1, 'R');

if (($bill_data['discount'] ?? 0) > 0) {
    $pdf->Cell(120, 8, 'DISCOUNT', 1, 0, 'R');
    $pdf->Cell(30, 8, '', 1, 0, 'C');
    $pdf->Cell(40, 8, '-' . $peso_sign . ' ' . number_format($bill_data['discount'], 2), 1, 1, 'R');
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(74, 107, 220);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(120, 10, 'TOTAL AMOUNT PAID', 1, 0, 'R', true);
$pdf->Cell(30, 10, '', 1, 0, 'C', true);
$pdf->Cell(40, 10, $peso_sign . ' ' . number_format($calculated_total - ($bill_data['discount'] ?? 0), 2), 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);

// PAYMENT INFORMATION SECTION
$pdf->addSectionHeader('PAYMENT INFORMATION');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Payment Status:', 0, 0);
$pdf->SetTextColor(0, 128, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, 'PAID', 0, 1);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, 'Payment Date:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, date('F j, Y'), 0, 1);

if (!empty($bill_data['payment_method'])) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Payment Method:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $bill_data['payment_method'], 0, 1);
}

$pdf->Ln(8);

// AUTHORIZATION SECTION
$pdf->addSectionHeader('AUTHORIZATION');

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, "This receipt serves as official confirmation that payment has been received and processed by Madridano Hospital. All transactions are final.");

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
$pdf->MultiCell(0, 4, "Note: Please retain this receipt for your records. For any inquiries, please contact our billing department at 09940213443.");

// Update database if generating receipt
if ($generate_receipt) {
    $update_query = "UPDATE billtb SET receipt_generated=1 WHERE pid='$pid'";
    mysqli_query($con, $update_query);
}

// Output the PDF with proper encoding
$pdf->Output('receipt_' . $pid . '_' . date('Ymd') . '.pdf', 'I');
?>