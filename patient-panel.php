<!DOCTYPE html>
<?php
session_start();
include('func.php');
include('newfunc.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!isset($_SESSION['pid'])) {
    header("Location: index.php");
    exit();
}
$pid = $_SESSION['pid'];
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$fname = $_SESSION['fname'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$contact = $_SESSION['contact'] ?? '';


if (isset($_POST['request_invoice'])) {
    $invoice_number = 'INV-' . $pid . '-' . date('Ymd') . '-' . rand(1000, 9999);
    $total_amount = $_POST['total_amount'];
    $invoice_query = "INSERT INTO invoicetb (pid, invoice_number, generated_date, generated_time, generated_by, total_amount, status) VALUES ('$pid', '$invoice_number', CURDATE(), CURTIME(), 'Patient Request', '$total_amount', 'Generated')";
    if (mysqli_query($con, $invoice_query)) {
        echo "<script>alert('Invoice requested successfully! Invoice Number: $invoice_number');</script>";
    } else {
        echo "<script>alert('Error requesting invoice!');</script>";
    }
}

if (isset($_POST['request_payment'])) {
    $amount = $_POST['payment_amount'];
    $payment_method = $_POST['payment_method'];
    
    $payment_query = "INSERT INTO paymentstb (pid, amount, payment_method, payment_date, payment_time, processed_by, notes) VALUES ('$pid', '$amount', '$payment_method', CURDATE(), CURTIME(), 'Patient Self-Service', 'Payment request submitted by patient')";
    
    if (mysqli_query($con, $payment_query)) {
        echo "<script>alert('Payment request submitted successfully! Please wait for admin approval.');</script>";
    } else {
        echo "<script>alert('Error submitting payment request!');</script>";
    }
}
$admission_query = "SELECT * FROM admissiontb WHERE pid='$pid'";
$admission_result = mysqli_query($con, $admission_query);
$admission_data = mysqli_fetch_array($admission_result);
$bill_query = "SELECT * FROM billtb WHERE pid='$pid'";
$bill_result = mysqli_query($con, $bill_query);
$bill_data = mysqli_fetch_assoc($bill_result);
$diagnostics_query = "SELECT * FROM diagnosticstb WHERE pid='$pid' ORDER BY created_date DESC, created_time DESC";
$diagnostics_result = mysqli_query($con, $diagnostics_query);
$lab_tests_query = "SELECT * FROM labtesttb WHERE pid='$pid' ORDER BY requested_date DESC, requested_time DESC";
$lab_tests_result = mysqli_query($con, $lab_tests_query);
$charges_query = "SELECT pc.*, s.service_name FROM patient_chargstb pc JOIN servicestb s ON pc.service_id = s.id WHERE pc.pid='$pid' ORDER BY pc.added_date DESC, pc.added_time DESC";
$charges_result = mysqli_query($con, $charges_query);
$payments_query = "SELECT * FROM paymentstb WHERE pid='$pid' ORDER BY payment_date DESC, payment_time DESC";
$payments_result = mysqli_query($con, $payments_query);

?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Madridano Health Care Hospital </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <style>
            .bg-primary {
                background: -webkit-linear-gradient(left, #3931af, #00c6ff);
            }
            .list-group-item.active {
                z-index: 2;
                color: #fff;
                background-color: #342ac1;
                border-color: #007bff;
            }
            .text-primary {
                color: #342ac1!important;
            }
            .btn-primary{
                background-color: #3c50c1;
                border-color: #3c50c1;
            }
            .info-card {
                background: #f8f9fa;
                border-left: 4px solid #342ac1;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 5px;
            }
            .info-title {
                color: #342ac1;
                font-weight: bold;
                margin-bottom: 15px;
            }
        </style>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
                </li>
            </ul>
        </div>
    </nav>
</head>

<body style="padding-top:50px;">
<div class="container-fluid" style="margin-top:50px;">
    <h3 style="margin-left: 40%; padding-bottom: 20px; font-family: 'IBM Plex Sans', sans-serif;">
        Welcome <?php echo $fname . " " . $lname; ?>
    </h3>
    
    <div class="row">
        <div class="col-md-4" style="max-width:25%; margin-top: 3%">
            <div class="list-group" id="list-tab" role="tablist">
                <a class="list-group-item list-group-item-action active" id="list-personal-list" data-toggle="list" href="#list-personal" role="tab" aria-controls="home">Personal Information</a>
                <a class="list-group-item list-group-item-action" id="list-admission-list" data-toggle="list" href="#list-admission" role="tab" aria-controls="home">Admission Details</a>
                <a class="list-group-item list-group-item-action" href="#list-diagnostics" id="list-diagnostics-list" role="tab" data-toggle="list" aria-controls="home">Medical Records</a>
                <a class="list-group-item list-group-item-action" href="#list-lab-results" id="list-lab-results-list" role="tab" data-toggle="list" aria-controls="home">Lab Results</a>
                <a class="list-group-item list-group-item-action" href="#list-charges" id="list-charges-list" role="tab" data-toggle="list" aria-controls="home">Service Charges</a>
                <a class="list-group-item list-group-item-action" href="#list-billing" id="list-billing-list" role="tab" data-toggle="list" aria-controls="home">Total Bill & Payment</a>
            </div><br>
        </div>
        
        <div class="col-md-8" style="margin-top: 3%;">
            <div class="tab-content" id="nav-tabContent" style="width: 950px;">
                <div class="tab-pane fade show active" id="list-personal" role="tabpanel" aria-labelledby="list-personal-list">
                    <div class="container-fluid" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px;">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-lg" style="background: rgba(255,255,255,0.95); border-radius: 20px;">
                                    <div class="card-body text-center py-4">
                                        <h2 class="mb-2" style="color: #2c3e50; font-weight: 700;">
                                            <i class="fa fa-user-circle text-primary me-3"></i>Patient Dashboard
                                        </h2>
                                        <p class="text-muted mb-0">Welcome <?php echo $fname . ' ' . $lname; ?> - Madridano Health Care Hospital</p>
                                        <small class="text-muted">Access your medical records, bills, and health information</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                                    <div class="card-body text-white p-4">
                                        <h5 class="fw-bold mb-3"><i class="fa fa-id-card me-2"></i>Personal Information</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-2 opacity-90"><strong>Patient ID:</strong> <?php echo $pid; ?></p>
                                                <p class="mb-2 opacity-90"><strong>First Name:</strong> <?php echo $fname; ?></p>
                                                <p class="mb-2 opacity-90"><strong>Last Name:</strong> <?php echo $lname; ?></p>
                                                <p class="mb-0 opacity-90"><strong>Gender:</strong> <?php echo $gender; ?></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-2 opacity-90"><strong>Email:</strong> <?php echo $email; ?></p>
                                                <p class="mb-2 opacity-90"><strong>Contact:</strong> <?php echo $contact; ?></p>
                                                <p class="mb-0 opacity-90"><strong>Username:</strong> <?php echo $username; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px;">
                                    <div class="card-body text-white p-4">
                                        <h5 class="fw-bold mb-3"><i class="fa fa-hospital me-2"></i>Current Status</h5>
                                        <?php if($admission_data): ?>
                                        <p class="mb-2 opacity-90"><strong>Status:</strong> 
                                            <span class="badge bg-light text-dark"><?php echo $admission_data['status']; ?></span>
                                        </p>
                                        <p class="mb-2 opacity-90"><strong>Room:</strong> <?php echo $admission_data['room_number'] ?? 'Not Assigned'; ?></p>
                                        <p class="mb-2 opacity-90"><strong>Doctor:</strong> <?php echo $admission_data['assigned_doctor']; ?></p>
                                        <p class="mb-0 opacity-90"><strong>Admitted:</strong> <?php echo $admission_data['admission_date']; ?></p>
                                        <?php else: ?>
                                        <p class="opacity-90">No current admission records found.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-stethoscope fa-3x text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold mb-3" style="color: #2c3e50;">Medical Records</h6>
                                        <button class="btn btn-primary btn-sm fw-bold" onclick="document.querySelector('#list-diagnostics-list').click()">
                                            <i class="fa fa-eye me-1"></i>View Records
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-flask fa-3x text-success"></i>
                                        </div>
                                        <h6 class="fw-bold mb-3" style="color: #2c3e50;">Lab Results</h6>
                                        <button class="btn btn-success btn-sm fw-bold" onclick="document.querySelector('#list-lab-results-list').click()">
                                            <i class="fa fa-vial me-1"></i>View Results
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-money-bill fa-3x text-warning"></i>
                                        </div>
                                        <h6 class="fw-bold mb-3" style="color: #2c3e50;">Billing</h6>
                                        <button class="btn btn-warning btn-sm fw-bold" onclick="document.querySelector('#list-billing-list').click()">
                                            <i class="fa fa-calculator me-1"></i>View Bills
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-list-alt fa-3x text-info"></i>
                                        </div>
                                        <h6 class="fw-bold mb-3" style="color: #2c3e50;">Service Charges</h6>
                                        <button class="btn btn-info btn-sm fw-bold" onclick="document.querySelector('#list-charges-list').click()">
                                            <i class="fa fa-receipt me-1"></i>View Charges
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-admission" role="tabpanel" aria-labelledby="list-admission-list">
                    <div class="info-card">
                        <h4 class="info-title"><i class="fa fa-hospital-o"></i> Admission Details</h4>
                        <?php if($admission_data): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Admission Date:</strong> <?php echo $admission_data['admission_date']; ?></p>
                                <p><strong>Admission Time:</strong> <?php echo $admission_data['admission_time'] ?? 'Not recorded'; ?></p>
                                <p><strong>Assigned Doctor:</strong> <?php echo $admission_data['assigned_doctor']; ?></p>
                                <p><strong>Room Number:</strong> <?php echo $admission_data['room_number'] ?? 'Not Assigned'; ?></p>
                                <p><strong>Age:</strong> <?php echo $admission_data['age'] ?? 'Not recorded'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    <span class="badge <?php echo ($admission_data['status'] == 'Admitted') ? 'badge-success' : (($admission_data['status'] == 'Ready for Discharge') ? 'badge-warning' : 'badge-info'); ?>">
                                        <?php echo $admission_data['status']; ?>
                                    </span>
                                </p>
                                <p><strong>Admission Type:</strong> Walk-in Direct Admission</p>
                                <p><strong>Address:</strong> <?php echo $admission_data['address'] ?? 'Not provided'; ?></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p><strong>Reason for Admission:</strong></p>
                            <div class="border p-3 bg-light">
                                <?php echo $admission_data['reason'] ?? 'Not specified'; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No admission records found.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-diagnostics" role="tabpanel" aria-labelledby="list-diagnostics-list">
                    <div class="info-card">
                        <h4 class="info-title"><i class="fa fa-stethoscope"></i> Medical Records & Diagnostics</h4>
                        <?php if(mysqli_num_rows($diagnostics_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Treatment Plan</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($diag = mysqli_fetch_array($diagnostics_result)): ?>
                                    <tr>
                                        <td><?php echo $diag['created_date']; ?><br><small><?php echo $diag['created_time']; ?></small></td>
                                        <td><?php echo $diag['doctor_name']; ?></td>
                                        <td><?php echo substr($diag['diagnosis'], 0, 50) . '...'; ?></td>
                                        <td><?php echo substr($diag['treatment_plan'], 0, 50) . '...'; ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#diagModal-<?php echo $diag['id']; ?>">
                                                <i class="fa fa-eye"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="diagModal-<?php echo $diag['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Medical Record - <?php echo $diag['created_date']; ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Doctor:</strong> <?php echo $diag['doctor_name']; ?></p>
                                                            <p><strong>Date:</strong> <?php echo $diag['created_date'] . ' ' . $diag['created_time']; ?></p>
                                                            <p><strong>Symptoms:</strong></p>
                                                            <div class="border p-2 mb-3"><?php echo $diag['symptoms']; ?></div>
                                                            <p><strong>Vital Signs:</strong></p>
                                                            <div class="border p-2 mb-3"><?php echo $diag['vital_signs']; ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Physical Examination:</strong></p>
                                                            <div class="border p-2 mb-3"><?php echo $diag['physical_examination']; ?></div>
                                                            <p><strong>Diagnosis:</strong></p>
                                                            <div class="border p-2 mb-3"><?php echo $diag['diagnosis']; ?></div>
                                                            <p><strong>Treatment Plan:</strong></p>
                                                            <div class="border p-2 mb-3"><?php echo $diag['treatment_plan']; ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No medical records available yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-lab-results" role="tabpanel" aria-labelledby="list-lab-results-list">
                    <div class="info-card">
                        <h4 class="info-title"><i class="fa fa-flask"></i> Laboratory Test Results</h4>
                        <?php if(mysqli_num_rows($lab_tests_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Test Name</th>
                                        <th>Requested By</th>
                                        <th>Status</th>
                                        <th>Scheduled Date</th>
                                        <th>Completed Date</th>
                                        <th>Price</th>
                                        <th>Results</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($lab = mysqli_fetch_array($lab_tests_result)): ?>
                                    <tr>
                                        <td><strong><?php echo $lab['test_name']; ?></strong></td>
                                        <td><?php echo $lab['suggested_by_doctor']; ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                switch($lab['status']) {
                                                    case 'Pending': echo 'badge-warning'; break;
                                                    case 'Accepted': echo 'badge-info'; break;
                                                    case 'Completed': echo 'badge-success'; break;
                                                    case 'Rejected': echo 'badge-danger'; break;
                                                    default: echo 'badge-secondary';
                                                }
                                            ?>"><?php echo $lab['status']; ?></span>
                                        </td>
                                        <td><?php echo $lab['scheduled_date'] ?? 'Not scheduled'; ?></td>
                                        <td><?php echo $lab['completed_date'] ?? 'Pending'; ?></td>
                                        <td>₱<?php echo number_format($lab['price'], 2); ?></td>
                                        <td>
                                            <?php if($lab['status'] == 'Completed' && $lab['results']): ?>
                                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#labModal-<?php echo $lab['id']; ?>">
                                                    <i class="fa fa-eye"></i> View Results
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if($lab['status'] == 'Completed' && $lab['results']): ?>
                                    <div class="modal fade" id="labModal-<?php echo $lab['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Lab Results - <?php echo $lab['test_name']; ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Test:</strong> <?php echo $lab['test_name']; ?></p>
                                                            <p><strong>Requested by:</strong> <?php echo $lab['suggested_by_doctor']; ?></p>
                                                            <p><strong>Completed:</strong> <?php echo $lab['completed_date']; ?></p>
                                                            <p><strong>Priority:</strong> <?php echo $lab['priority'] ?? 'Normal'; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Price:</strong> ₱<?php echo number_format($lab['price'], 2); ?></p>
                                                            <p><strong>Status:</strong> <span class="badge badge-success"><?php echo $lab['status']; ?></span></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <p><strong>Test Results:</strong></p>
                                                    <div class="border p-3 bg-light mb-3"><?php echo $lab['results']; ?></div>
                                                    <?php if($lab['lab_notes']): ?>
                                                    <p><strong>Lab Notes:</strong></p>
                                                    <div class="border p-3 bg-light"><?php echo $lab['lab_notes']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No laboratory tests ordered yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-charges" role="tabpanel" aria-labelledby="list-charges-list">
                    <div class="info-card">
                        <h4 class="info-title"><i class="fa fa-list-alt"></i> Service Charges</h4>
                        <?php if(mysqli_num_rows($charges_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Service</th>
                                        <th>Added By</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total Price</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($charge = mysqli_fetch_array($charges_result)): ?>
                                    <tr>
                                        <td><?php echo $charge['added_date']; ?><br><small><?php echo $charge['added_time']; ?></small></td>
                                        <td><strong><?php echo $charge['service_name']; ?></strong></td>
                                        <td><?php echo $charge['added_by']; ?></td>
                                        <td><?php echo $charge['quantity']; ?></td>
                                        <td>₱<?php echo number_format($charge['unit_price'], 2); ?></td>
                                        <td><strong>₱<?php echo number_format($charge['total_price'], 2); ?></strong></td>
                                        <td><?php echo $charge['description'] ?? 'No description'; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No additional service charges.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-billing" role="tabpanel" aria-labelledby="list-billing-list">
                    <div class="info-card">
                        <h4 class="info-title"><i class="fa fa-money"></i> Total Bill & Payment</h4>
                        <?php if($bill_data): ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fa fa-calculator"></i> Bill Breakdown</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Consultation Fees:</strong> ₱<?php echo number_format($bill_data['consultation_fees'], 2); ?></p>
                                        <p><strong>Lab Fees:</strong> ₱<?php echo number_format($bill_data['lab_fees'], 2); ?></p>
                                        <p><strong>Medicine Fees:</strong> ₱<?php echo number_format($bill_data['medicine_fees'], 2); ?></p>
                                        <p><strong>Room Charges:</strong> ₱<?php echo number_format($bill_data['room_charges'] ?? 0, 2); ?></p>
                                        <p><strong>Service Charges:</strong> ₱<?php echo number_format($bill_data['service_charges'] ?? 0, 2); ?></p>
                                        <hr>
                                        <h5><strong>Total Amount:</strong> <span class="text-success">₱<?php echo number_format($bill_data['total'], 2); ?></span></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-<?php echo ($bill_data['status'] == 'Paid') ? 'success' : 'warning'; ?>">
                                    <div class="card-header bg-<?php echo ($bill_data['status'] == 'Paid') ? 'success' : 'warning'; ?> text-white">
                                        <h6 class="mb-0"><i class="fa fa-credit-card"></i> Payment Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Status:</strong> 
                                            <span class="badge badge-<?php echo ($bill_data['status'] == 'Paid') ? 'success' : 'warning'; ?> badge-lg">
                                                <?php echo $bill_data['status']; ?>
                                            </span>
                                        </p>
                                        <p><strong>Amount Paid:</strong>₱<?php echo number_format($bill_data['amount_paid'] ?? 0, 2); ?></p>
                                        <p><strong>Balance Due:</strong> ₱<?php echo number_format($bill_data['total'] - ($bill_data['amount_paid'] ?? 0), 2); ?></p>
                                        <?php if(!empty($bill_data['payment_date'])): ?>
                                        <p><strong>Payment Date:</strong> <?php echo $bill_data['payment_date']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5><i class="fa fa-history"></i> Payment History</h5>
                        <?php if(mysqli_num_rows($payments_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Processed By</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($payment = mysqli_fetch_array($payments_result)): ?>
                                    <tr>
                                        <td><?php echo $payment['payment_date']; ?></td>
                                        <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo $payment['payment_method']; ?></td>
                                        <td><?php echo $payment['processed_by']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo (!empty($payment['status']) && $payment['status'] == 'Approved') ? 'success' : ((!empty($payment['status']) && $payment['status'] == 'Pending') ? 'warning' : 'danger'); ?>">
                                                <?php echo !empty($payment['status']) ? $payment['status'] : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $payment['notes'] ?? 'No notes'; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No payment history available.</p>
                        <?php endif; ?>
                        </div>
                        <?php if($bill_data['status'] != 'Paid'): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fa fa-file-text"></i> Request Invoice</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Generate an official invoice for your records.</p>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="total_amount" value="<?php echo $bill_data['total']; ?>">
                            <button type="submit" name="request_invoice" class="btn btn-primary">
                                <i class="fa fa-download"></i> Request Invoice
                            </button>
                        </form>
                        <?php
                        $invoice_check_query = "SELECT * FROM invoicetb WHERE pid='$pid' AND status='Approved' ORDER BY generated_date DESC, generated_time DESC LIMIT 1";
                        $invoice_check_result = mysqli_query($con, $invoice_check_query);
                        if (mysqli_num_rows($invoice_check_result) > 0) {
                            $invoice = mysqli_fetch_assoc($invoice_check_result);
                            echo '<a href="view_invoice.php?invoice_id=' . $invoice['id'] . '" class="btn btn-success mt-2" target="_blank">
                                    <i class="fa fa-file-invoice"></i> Show Invoice
                                  </a>';
                        }
                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fa fa-credit-card"></i> Make Payment</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="form-group">
                                                <label>Payment Amount:</label>
                                                <input type="number" name="payment_amount" class="form-control" 
                                                       value="<?php echo $bill_data['total'] - ($bill_data['amount_paid'] ?? 0); ?>" 
                                                       step="0.01" min="0.01" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Payment Method:</label>
                                                <select name="payment_method" class="form-control" required>
                                                    <option value="">Select Method</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Credit Card">Credit Card</option>
                                                    <option value="Debit Card">Debit Card</option>
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Insurance">Insurance</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="request_payment" class="btn btn-success">
                                                <i class="fa fa-money"></i> Submit Payment Request
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> <strong>Payment Complete!</strong> Your bill has been fully paid. Thank you!
                        </div>
                        <?php endif; ?>

                            </thead>
                            <tbody>
                                <tr>
                                    <td>Doctor Consultation</td>
                                    <td>₱<?php echo number_format($bill_data['consultation_fees'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Laboratory Tests</td>
                                    <td>₱<?php echo number_format($bill_data['lab_fees'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Medicines & Supplies</td>
                                    <td>₱<?php echo number_format($bill_data['medicine_fees'], 2); ?></td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td><strong>₱<?php echo number_format($bill_data['total'], 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">No billing information available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>
