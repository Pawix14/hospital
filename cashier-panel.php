<?php
session_start();
include('func.php');
include('newfunc.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'] ?? '';
if ($role != 'cashier') {
    die("Access denied. Cashier access required.");
}

$cashier_name = $_SESSION['fname'] . ' ' . $_SESSION['lname'];
$cashier_id = $_SESSION['cid'] ?? $_SESSION['pid']; 
if (isset($_POST['accept_payment'])) {
    $bill_id = $_POST['bill_id'];
    $payment_method = $_POST['payment_method'];
    $payment_amount = $_POST['payment_amount'];
    $patient_check = "SELECT a.status FROM billtb b JOIN admissiontb a ON b.pid = a.pid WHERE b.id='$bill_id'";
    $patient_result = mysqli_query($con, $patient_check);
    $patient_data = mysqli_fetch_assoc($patient_result);

    if ($patient_data['status'] != 'Ready for Discharge') {
        echo "<script>alert('Payment cannot be accepted. Patient must be ready for discharge by the doctor first.');</script>";
    } else {
        $update_bill = "UPDATE billtb SET status='Paid', amount_paid='$payment_amount', payment_method='$payment_method', payment_date=CURDATE(), payment_time=CURTIME(), cashier_id='$cashier_id', payment_status='Approved' WHERE id='$bill_id'";
        mysqli_query($con, $update_bill);
        $update_payment = "UPDATE paymentstb SET status='Approved', processed_by='Cashier: $cashier_name' WHERE pid=(SELECT pid FROM billtb WHERE id='$bill_id') AND status='Pending' ORDER BY payment_date DESC, payment_time DESC LIMIT 1";
        mysqli_query($con, $update_payment);
        echo "<script>alert('Payment accepted successfully!');</script>";
    }
}

if (isset($_POST['reject_payment'])) {
    $bill_id = $_POST['bill_id'];
    $reason = $_POST['reject_reason'];
    $update_bill = "UPDATE billtb SET status='Rejected', payment_status='Rejected' WHERE id='$bill_id'";
    mysqli_query($con, $update_bill);
    $update_payment = "UPDATE paymentstb SET status='Rejected', notes=CONCAT(IFNULL(notes,''), ' | Rejected by Cashier: $reason') WHERE pid=(SELECT pid FROM billtb WHERE id='$bill_id') AND status='Pending' ORDER BY payment_date DESC, payment_time DESC LIMIT 1";
    mysqli_query($con, $update_payment);

    echo "<script>alert('Payment rejected!');</script>";
}
if (isset($_POST['approve_invoice'])) {
    $invoice_id = $_POST['invoice_id'];
    $update_query = "UPDATE invoicetb SET status='Approved' WHERE id='$invoice_id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Invoice request approved successfully!');</script>";
    }
}
if (isset($_POST['deny_invoice'])) {
    $invoice_id = $_POST['invoice_id'];
    $update_query = "UPDATE invoicetb SET status='Denied' WHERE id='$invoice_id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Invoice request denied.');</script>";
    }
}
$query = "SELECT b.*, a.fname, a.lname, a.contact, a.pid,
          COALESCE(b.amount_paid, 0) as paid_amount,
          (b.consultation_fees + b.lab_fees + b.medicine_fees + COALESCE(b.room_charges,0) + COALESCE(b.service_charges,0) - COALESCE(b.discount,0)) as total_amount
          FROM billtb b
          JOIN admissiontb a ON b.pid = a.pid
          ORDER BY b.id DESC";

$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Panel - Madridano Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-unpaid { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cash-register me-2"></i>Madridano Hospital - Cashier Panel
            </a>
            <div class="navbar-nav ml-auto">
                <span class="nav-link">Cashier: <?php echo $cashier_name; ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <ul class="nav nav-tabs" id="cashierTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="bills-tab" data-toggle="tab" href="#bills" role="tab" aria-controls="bills" aria-selected="true">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Patient Bills
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="invoice-requests-tab" data-toggle="tab" href="#invoice-requests" role="tab" aria-controls="invoice-requests" aria-selected="false">
                    <i class="fas fa-file-invoice me-2"></i>Invoice Requests
                </a>
            </li>
        </ul>

        <div class="tab-content mt-4">
            <div class="tab-pane fade show active" id="bills" role="tabpanel" aria-labelledby="bills-tab">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>All Patient Bills</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Bill ID</th>
                                        <th>Patient</th>
                                        <th>Contact</th>
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($bill = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $bill['id']; ?></td>
                                        <td><?php echo $bill['fname'] . ' ' . $bill['lname']; ?> (ID: <?php echo $bill['pid']; ?>)</td>
                                        <td><?php echo $bill['contact']; ?></td>
                                        <td>₱<?php echo number_format($bill['total_amount'], 2); ?></td>
                                        <td>₱<?php echo number_format($bill['paid_amount'], 2); ?></td>
                                        <td>₱<?php echo number_format($bill['total_amount'] - $bill['paid_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php
                                                echo ($bill['status'] == 'Paid') ? 'success' :
                                                     (($bill['status'] == 'Rejected') ? 'danger' : 'warning');
                                            ?>">
                                                <?php echo $bill['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($bill['status'] != 'Paid' && $bill['status'] != 'Rejected'): ?>
                                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#paymentModal-<?php echo $bill['id']; ?>">
                                                <i class="fas fa-check"></i> Accept Payment
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal-<?php echo $bill['id']; ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                            <?php endif; ?>

                                            <?php if($bill['status'] == 'Paid'): ?>
                                            <a href="generate_invoice.php?bill_id=<?php echo $bill['id']; ?>" class="btn btn-primary btn-sm" target="_blank">
                                                <i class="fas fa-file-invoice"></i> Invoice
                                            </a>
                                            <a href="generate_receipt.php?pid=<?php echo $bill['pid']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-receipt"></i> Receipt
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="paymentModal-<?php echo $bill['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Accept Payment - Bill #<?php echo $bill['id']; ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                                        <p><strong>Patient:</strong> <?php echo $bill['fname'] . ' ' . $bill['lname']; ?></p>
                                                        <p><strong>Total Amount:</strong> ₱<?php echo number_format($bill['total_amount'], 2); ?></p>
                                                        <p><strong>Balance Due:</strong> ₱<?php echo number_format($bill['total_amount'] - $bill['paid_amount'], 2); ?></p>

                                                        <div class="form-group">
                                                            <label>Payment Amount</label>
                                                            <input type="number" step="0.01" name="payment_amount" class="form-control"
                                                                   value="<?php echo $bill['total_amount'] - $bill['paid_amount']; ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Payment Method</label>
                                                            <select name="payment_method" class="form-control" required>
                                                                <option value="Cash">Cash</option>
                                                                <option value="Credit Card">Credit Card</option>
                                                                <option value="Debit Card">Debit Card</option>
                                                                <option value="Online Transfer">Online Transfer</option>
                                                                <option value="Insurance">Insurance</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="accept_payment" class="btn btn-success">Accept Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="rejectModal-<?php echo $bill['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Payment - Bill #<?php echo $bill['id']; ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                                        <p><strong>Patient:</strong> <?php echo $bill['fname'] . ' ' . $bill['lname']; ?></p>
                                                        <div class="form-group">
                                                            <label>Reason for Rejection</label>
                                                            <textarea name="reject_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="reject_payment" class="btn btn-danger">Reject Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Requests Tab -->
            <div class="tab-pane fade" id="invoice-requests" role="tabpanel" aria-labelledby="invoice-requests-tab">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Requests</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Patient ID</th>
                                        <th>Patient Name</th>
                                        <th>Invoice Number</th>
                                        <th>Total Amount</th>
                                        <th>Generated Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $invoice_query = "SELECT i.*, a.fname, a.lname FROM invoicetb i JOIN admissiontb a ON i.pid = a.pid ORDER BY i.generated_date DESC, i.generated_time DESC";
                                    $invoice_result = mysqli_query($con, $invoice_query);
                                    while ($invoice = mysqli_fetch_array($invoice_result)) {
                                        $statusClass = 'secondary';
                                        if ($invoice['status'] == 'Approved') {
                                            $statusClass = 'success';
                                        } elseif ($invoice['status'] == 'Denied') {
                                            $statusClass = 'danger';
                                        } elseif ($invoice['status'] == 'Generated') {
                                            $statusClass = 'warning';
                                        }
                                        echo '<tr>
                                            <td>' . $invoice['id'] . '</td>
                                            <td>' . $invoice['pid'] . '</td>
                                            <td>' . $invoice['fname'] . ' ' . $invoice['lname'] . '</td>
                                            <td>' . $invoice['invoice_number'] . '</td>
                                            <td>₱' . number_format($invoice['total_amount'], 2) . '</td>
                                            <td>' . $invoice['generated_date'] . '</td>
                                            <td><span class="badge bg-' . $statusClass . '">' . $invoice['status'] . '</span></td>
                                            <td>';
                                        if ($invoice['status'] == 'Generated') {
                                            echo '<form method="POST" style="display:inline;">
                                                <input type="hidden" name="invoice_id" value="' . $invoice['id'] . '">
                                                <button type="submit" name="approve_invoice" class="btn btn-sm btn-success" onclick="return confirm(\'Approve this invoice request?\')">Approve</button>
                                                <button type="submit" name="deny_invoice" class="btn btn-sm btn-danger" onclick="return confirm(\'Deny this invoice request?\')">Deny</button>
                                            </form>';
                                        } else {
                                            echo '-';
                                        }
                                        echo '</td>
                                        </tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
