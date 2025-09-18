<?php
session_start();
include('func.php');
include('newfunc.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!isset($_GET['invoice_id'])) {
    die("Invoice ID is required.");
}

$invoice_id = $_GET['invoice_id'];

$invoice_query = "SELECT i.*, a.fname, a.lname FROM invoicetb i JOIN admissiontb a ON i.pid = a.pid WHERE i.id = '$invoice_id' AND i.status = 'Approved'";
$invoice_result = mysqli_query($con, $invoice_query);

if (mysqli_num_rows($invoice_result) == 0) {
    die("Invoice not found or not approved.");
}

$invoice = mysqli_fetch_assoc($invoice_result);

$bill_query = "SELECT * FROM billtb WHERE pid = '" . $invoice['pid'] . "'";
$bill_result = mysqli_query($con, $bill_query);
$bill_data = mysqli_fetch_assoc($bill_result);

$doctor_query = "SELECT fname, lname FROM doctortb WHERE id = (SELECT assigned_doctor FROM admissiontb WHERE pid = '" . $invoice['pid'] . "')";

$doctor_result = mysqli_query($con, $doctor_query);
$doctor_name = '';
if ($doctor_result && mysqli_num_rows($doctor_result) > 0) {
    $doctor_row = mysqli_fetch_assoc($doctor_result);
    $doctor_name = $doctor_row['fname'] . ' ' . $doctor_row['lname'];
}

// Calculate medicine fees dynamically from prestb
$medicine_fees_result = mysqli_query($con, "SELECT SUM(price) AS total_medicine_fees FROM prestb WHERE pid = '" . $invoice['pid'] . "' AND diagnosis_details IS NOT NULL AND diagnosis_details != ''");
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

// Get patient details for additional information
$patient_query = "SELECT * FROM admissiontb WHERE pid = '" . $invoice['pid'] . "'";
$patient_result = mysqli_query($con, $patient_query);
$patient_data = mysqli_fetch_assoc($patient_result);

// Get payment status
$payment_status = $bill_data['status'] ?? 'Unpaid';
$status_class = ($payment_status == 'Paid') ? 'badge-success' : 'badge-warning';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Madridano Hospital - Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6fdc;
            --secondary-color: #2c3e50;
            --accent-color: #667eea;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .invoice-container {
            max-width: 800px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .hospital-logo {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .hospital-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .billing-system {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .hospital-info {
            font-size: 14px;
            opacity: 0.8;
            line-height: 1.5;
        }
        
        .invoice-body {
            padding: 30px;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: var(--light-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .detail-card h5 {
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .detail-value {
            color: var(--secondary-color);
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .invoice-table th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .invoice-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        
        .invoice-table tr.item-row:hover {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 18px;
        }
        
        .total-row td {
            border-top: 2px solid var(--primary-color);
            padding-top: 20px;
        }
        
        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .invoice-footer {
            background: #f8f9fa;
            padding: 25px 30px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .thank-you {
            color: #6c757d;
            font-style: italic;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-print {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-print:hover {
            background: #3a5fc8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-download {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-download:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .watermark {
            position: absolute;
            opacity: 0.03;
            font-size: 180px;
            font-weight: bold;
            color: var(--primary-color);
            transform: rotate(-45deg);
            pointer-events: none;
            z-index: -1;
        }
        
        @media print {
            body {
                background: white;
                display: block;
            }
            
            .invoice-container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            
            .action-buttons {
                display: none;
            }
            
            .watermark {
                opacity: 0.05;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-details {
                grid-template-columns: 1fr;
            }
            
            .invoice-footer {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-print, .btn-download {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">MADRIDANO HOSPITAL</div>
    
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="hospital-logo">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="hospital-name">MADRIDANO HOSPITAL</div>
            <div class="billing-system">Billing System</div>
            <div class="hospital-info">
                Cagayan De Oro City<br>
                Phinma COC | Phone: 09940213443<br>
                Email: gama.madridano.coc@phinmaed.com
            </div>
        </div>

        <div class="invoice-body">
            <div class="invoice-title">
                INVOICE
                <span class="payment-status badge <?php echo $status_class; ?>">
                    <?php echo $payment_status; ?>
                </span>
            </div>

            <div class="invoice-details">
                <div class="detail-card">
                    <h5>Patient Information</h5>
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($invoice['fname'] . ' ' . $invoice['lname']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Patient ID:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($invoice['pid']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Doctor:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($doctor_name); ?></span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h5>Invoice Details</h5>
                    <div class="detail-item">
                        <span class="detail-label">Invoice #:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date Issued:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($invoice['generated_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Status:</span>
                        <span class="detail-value badge <?php echo $status_class; ?>"><?php echo $payment_status; ?></span>
                    </div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="item-row">
                        <td>Consultation Fees</td>
                        <td class="text-right"><?php echo number_format($bill_data['consultation_fees'], 2); ?></td>
                    </tr>
                    <tr class="item-row">
                        <td>Lab Fees</td>
                        <td class="text-right"><?php echo number_format($bill_data['lab_fees'], 2); ?></td>
                    </tr>
                    <tr class="item-row">
                        <td>Medicine Fees</td>
                        <td class="text-right"><?php echo number_format($calculated_medicine_fees, 2); ?></td>
                    </tr>
                    <tr class="item-row">
                        <td>Room Charges</td>
                        <td class="text-right"><?php echo number_format($bill_data['room_charges'], 2); ?></td>
                    </tr>
                    <tr class="item-row">
                        <td>Service Charges</td>
                        <td class="text-right"><?php echo number_format($bill_data['service_charges'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Amount</td>
                        <td class="text-right">₱ <?php echo number_format($calculated_total, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="invoice-footer">
            <div class="thank-you">
                Thank you for choosing Madridano Hospital for your healthcare needs.
            </div>
            <div class="action-buttons">
                <button onclick="window.print()" class="btn-print">
                    <i class="fas fa-print"></i> Print Invoice
                </button>
                <button onclick="downloadInvoice()" class="btn-download">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        function downloadInvoice() {
            // This would typically connect to a PDF generation service
            alert('PDF download functionality would be implemented here. This would generate a PDF version of the invoice.');
        }
    </script>
</body>
</html>