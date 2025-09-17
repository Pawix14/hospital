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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            margin: 20px;
            font-family: Arial, sans-serif;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.item td{
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .print-button {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <h2>Invoice</h2>
        <p><strong>Invoice Number:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
        <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($invoice['fname'] . ' ' . $invoice['lname']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($invoice['generated_date']); ?></p>
        <p><strong>Doctor:</strong> <?php echo htmlspecialchars($doctor_name); ?></p>

        <table>
            <tr class="heading">
                <td>Description</td>
                <td class="text-right">Amount (₱)</td>
            </tr>
            <tr class="item">
                <td>Consultation Fees</td>
                <td class="text-right"><?php echo number_format($bill_data['consultation_fees'], 2); ?></td>
            </tr>
            <tr class="item">
                <td>Lab Fees</td>
                <td class="text-right"><?php echo number_format($bill_data['lab_fees'], 2); ?></td>
            </tr>
            <tr class="item">
                <td>Medicine Fees</td>
                <td class="text-right"><?php echo number_format($bill_data['medicine_fees'], 2); ?></td>
            </tr>
            <tr class="item">
                <td>Room Charges</td>
                <td class="text-right"><?php echo number_format($bill_data['room_charges'], 2); ?></td>
            </tr>
            <tr class="item">
                <td>Service Charges</td>
                <td class="text-right"><?php echo number_format($bill_data['service_charges'], 2); ?></td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td class="text-right"><?php echo number_format($bill_data['total'], 2); ?></td>
            </tr>
        </table>

        <div class="print-button">
            <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
        </div>
    </div>
</body>
</html>
