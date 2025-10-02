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

$medicine_fees_query = "SELECT SUM(price) AS total_medicine_fees FROM prestb WHERE pid='$pid' AND diagnosis_details IS NOT NULL AND diagnosis_details != ''";
$medicine_fees_result = mysqli_query($con, $medicine_fees_query);
$medicine_fees_row = mysqli_fetch_assoc($medicine_fees_result);
$calculated_medicine_fees = $medicine_fees_row['total_medicine_fees'] ?? 0;
if ($bill_data) {
    $bill_data['medicine_fees'] = $calculated_medicine_fees;
    $bill_data['total'] = 
        ($bill_data['consultation_fees'] ?? 0) + 
        ($bill_data['lab_fees'] ?? 0) + 
        $calculated_medicine_fees + 
        ($bill_data['room_charges'] ?? 0) + 
        ($bill_data['service_charges'] ?? 0);
}
$diagnostics_query = "SELECT * FROM diagnosticstb WHERE pid='$pid' ORDER BY created_date DESC, created_time DESC";
$diagnostics_result = mysqli_query($con, $diagnostics_query);
$lab_tests_query = "SELECT * FROM labtesttb WHERE pid='$pid' ORDER BY requested_date DESC, requested_time DESC";
$lab_tests_result = mysqli_query($con, $lab_tests_query);
$charges_query = "SELECT pc.*, s.service_name FROM patient_chargstb pc JOIN servicestb s ON pc.service_id = s.id WHERE pc.pid='$pid' ORDER BY pc.added_date DESC, pc.added_time DESC";
$charges_result = mysqli_query($con, $charges_query);
$payments_query = "SELECT * FROM paymentstb WHERE pid='$pid' ORDER BY payment_date DESC, payment_time DESC";
$payments_result = mysqli_query($con, $payments_query);
<<<<<<< HEAD
$prescriptions_query = "SELECT id, doctor, pid, fname, lname, symptoms, allergy, prescription, price, diagnosis_details, prescribed_medicines, created_at AS created_date FROM prestb WHERE pid='$pid' AND diagnosis_details IS NOT NULL AND diagnosis_details != '' ORDER BY id DESC";
$prescriptions_result = mysqli_query($con, $prescriptions_query);

=======
$prescriptions_query = "SELECT id, doctor, pid, fname, lname, symptoms, allergy, prescription, price, diagnosis_details, prescribed_medicines, dosage, frequency, duration, created_at AS created_date FROM prestb WHERE pid='$pid' AND diagnosis_details IS NOT NULL AND diagnosis_details != '' ORDER BY id DESC";
$prescriptions_result = mysqli_query($con, $prescriptions_query);

// Store diagnostics data for modals
$diagnostics_data = [];
while($diag = mysqli_fetch_array($diagnostics_result)) {
    $diagnostics_data[] = $diag;
}

// Reset pointer for table display
mysqli_data_seek($diagnostics_result, 0);

// Store lab test data for modals
$lab_tests_data = [];
while($lab = mysqli_fetch_array($lab_tests_result)) {
    $lab_tests_data[] = $lab;
}

// Reset pointer for table display
mysqli_data_seek($lab_tests_result, 0);

>>>>>>> a5c017c (Initial project setup with updated files)
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Madridano Health Care Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
        }

        .navbar-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: white !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 20px;
            pointer-events: auto !important;
            z-index: 10;
        }

        .nav-pills .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            pointer-events: auto !important;
            z-index: 10;
        }

        .nav-pills .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-pills .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .table-glass {
            background: transparent;
            color: #2c3e50;
        }

        .table-glass th {
            background: rgba(255, 255, 255, 0.2);
            color: #2c3e50;
            font-weight: 600;
        }

        .welcome-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-header h2 {
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        .welcome-header p {
            color: rgba(255, 255, 255, 0.8);
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            border-left: 4px solid #342ac1;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .info-title {
            color: #342ac1;
            font-weight: bold;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .badge-lg {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
<<<<<<< HEAD
=======
        
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
>>>>>>> a5c017c (Initial project setup with updated files)
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top navbar-glass">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hospital-symbol me-2"></i>
                Madridano Health Care Hospital
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user me-1"></i>Patient: <?php echo $fname . ' ' . $lname; ?>
                </span>
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px;">
        <div class="welcome-header">
            <h2>Welcome, <?php echo $fname . ' ' . $lname; ?>!</h2>
            <p>Patient Dashboard - Access your medical records, bills, and health information</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <a class="nav-link active" role="tab" data-toggle="tab" href="#dashboard" aria-controls="dashboard" aria-selected="true" id="dashboard-tab">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#personal" aria-controls="personal" aria-selected="false" id="personal-tab">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#admission" aria-controls="admission" aria-selected="false" id="admission-tab">
                            <i class="fas fa-hospital-user me-2"></i>Admission Details
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#diagnostics" aria-controls="diagnostics" aria-selected="false" id="diagnostics-tab">
                            <i class="fas fa-stethoscope me-2"></i>Medical Records
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#lab-results" aria-controls="lab-results" aria-selected="false" id="lab-results-tab">
                            <i class="fas fa-flask me-2"></i>Lab Results
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#charges" aria-controls="charges" aria-selected="false" id="charges-tab">
                            <i class="fas fa-list-alt me-2"></i>Service Charges
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#billing" aria-controls="billing" aria-selected="false" id="billing-tab">
                            <i class="fas fa-money-bill me-2"></i>Billing & Payment
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#prescriptions" aria-controls="prescriptions" aria-selected="false" id="prescriptions-tab">
                            <i class="fas fa-medkit me-2"></i>Prescriptions
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9 col-md-8">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--primary-gradient);">
                                    <i class="fas fa-user-injured fa-3x mb-3"></i>
                                    <h3><?php echo ($admission_data && $admission_data['status'] == 'Admitted') ? 'Admitted' : 'Not Admitted'; ?></h3>
                                    <p>Current Status</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-file-medical fa-3x mb-3"></i>
                                    <h3><?php echo mysqli_num_rows($diagnostics_result); ?></h3>
                                    <p>Medical Records</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--warning-gradient);">
                                    <i class="fas fa-pills fa-3x mb-3"></i>
                                    <h3><?php echo mysqli_num_rows($prescriptions_result); ?></h3>
                                    <p>Prescriptions</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--success-gradient);">
                                    <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                                    <h3>₱<?php echo ($bill_data) ? number_format($bill_data['total'], 2) : '0.00'; ?></h3>
                                    <p>Total Bill</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-card p-4 mb-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-user-circle me-2"></i>Personal Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Patient ID:</strong> <?php echo $pid; ?></p>
                                    <p><strong>First Name:</strong> <?php echo $fname; ?></p>
                                    <p><strong>Last Name:</strong> <?php echo $lname; ?></p>
                                    <p><strong>Gender:</strong> <?php echo $gender; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email:</strong> <?php echo $email; ?></p>
                                    <p><strong>Contact:</strong> <?php echo $contact; ?></p>
                                    <p><strong>Username:</strong> <?php echo $username; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($admission_data): ?>
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-hospital me-2"></i>Current Admission Status
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge <?php echo ($admission_data['status'] == 'Admitted') ? 'badge-success' : (($admission_data['status'] == 'Ready for Discharge') ? 'badge-warning' : 'badge-info'); ?> badge-lg">
                                            <?php echo $admission_data['status']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Room:</strong> <?php echo $admission_data['room_number'] ?? 'Not Assigned'; ?></p>
                                    <p><strong>Doctor:</strong> <?php echo $admission_data['assigned_doctor']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Admitted:</strong> <?php echo $admission_data['admission_date']; ?></p>
                                    <p><strong>Admission Type:</strong> Walk-in Direct Admission</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tab-pane fade" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Patient ID:</strong> <?php echo $pid; ?></p>
                                    <p><strong>First Name:</strong> <?php echo $fname; ?></p>
                                    <p><strong>Last Name:</strong> <?php echo $lname; ?></p>
                                    <p><strong>Gender:</strong> <?php echo $gender; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email:</strong> <?php echo $email; ?></p>
                                    <p><strong>Contact:</strong> <?php echo $contact; ?></p>
                                    <p><strong>Username:</strong> <?php echo $username; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="admission" role="tabpanel" aria-labelledby="admission-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-hospital-user me-2"></i>Admission Details
                            </h4>
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
                                        <span class="badge <?php echo ($admission_data['status'] == 'Admitted') ? 'badge-success' : (($admission_data['status'] == 'Ready for Discharge') ? 'badge-warning' : 'badge-info'); ?> badge-lg">
                                            <?php echo $admission_data['status']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Admission Type:</strong> Walk-in Direct Admission</p>
                                    <p><strong>Address:</strong> <?php echo $admission_data['address'] ?? 'Not provided'; ?></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><strong>Reason for Admission:</strong></p>
                                <div class="border p-3 bg-light rounded">
                                    <?php echo $admission_data['reason'] ?? 'Not specified'; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No admission records found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="diagnostics" role="tabpanel" aria-labelledby="diagnostics-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-stethoscope me-2"></i>Medical Records & Diagnostics
                            </h4>
                            <?php if(mysqli_num_rows($diagnostics_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
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
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            </td>
                                        </tr>
<<<<<<< HEAD
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
                                                                <div class="border p-2 mb-3 rounded"><?php echo $diag['symptoms']; ?></div>
                                                                <p><strong>Vital Signs:</strong></p>
                                                                <div class="border p-2 mb-3 rounded"><?php echo $diag['vital_signs']; ?></div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Physical Examination:</strong></p>
                                                                <div class="border p-2 mb-3 rounded"><?php echo $diag['physical_examination']; ?></div>
                                                                <p><strong>Diagnosis:</strong></p>
                                                                <div class="border p-2 mb-3 rounded"><?php echo $diag['diagnosis']; ?></div>
                                                                <p><strong>Treatment Plan:</strong></p>
                                                                <div class="border p-2 mb-3 rounded"><?php echo $diag['treatment_plan']; ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
=======
>>>>>>> a5c017c (Initial project setup with updated files)
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No medical records available yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="lab-results" role="tabpanel" aria-labelledby="lab-results-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-flask me-2"></i>Laboratory Test Results
                            </h4>
                            <?php if(mysqli_num_rows($lab_tests_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
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
                                                ?> badge-lg"><?php echo $lab['status']; ?></span>
                                            </td>
                                            <td><?php echo $lab['scheduled_date'] ?? 'Not scheduled'; ?></td>
                                            <td><?php echo $lab['completed_date'] ?? 'Pending'; ?></td>
                                            <td>₱<?php echo number_format($lab['price'], 2); ?></td>
                                            <td>
                                                <?php if($lab['status'] == 'Completed' && $lab['results']): ?>
                                                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#labModal-<?php echo $lab['id']; ?>">
                                                        <i class="fas fa-eye"></i> View Results
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
<<<<<<< HEAD
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
                                                        <div class="border p-3 bg-light mb-3 rounded"><?php echo $lab['results']; ?></div>
                                                        <?php if($lab['lab_notes']): ?>
                                                        <p><strong>Lab Notes:</strong></p>
                                                        <div class="border p-3 bg-light rounded"><?php echo $lab['lab_notes']; ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
=======
>>>>>>> a5c017c (Initial project setup with updated files)
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No laboratory tests ordered yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="charges" role="tabpanel" aria-labelledby="charges-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-list-alt me-2"></i>Service Charges
                            </h4>
                            <?php if(mysqli_num_rows($charges_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
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
                    
                    <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-money-bill me-2"></i>Total Bill & Payment
                            </h4>
                            <?php if($bill_data): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-calculator"></i> Bill Breakdown</h6>
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
                                    <form method="post" action="patient-panel.php" class="mt-3">
                                        <input type="hidden" name="total_amount" value="<?php echo $bill_data['total']; ?>">
                                        <button type="submit" name="request_invoice" class="btn btn-primary btn-block">
                                            <i class="fas fa-file-invoice"></i> Request Invoice
                                        </button>
                                    </form>
                                    <?php
                                    // Check if invoice exists and its status
                                    $invoice_check_query = "SELECT * FROM invoicetb WHERE pid='$pid' ORDER BY generated_date DESC, generated_time DESC LIMIT 1";
                                    $invoice_check_result = mysqli_query($con, $invoice_check_query);
                                    if ($invoice_check_result && mysqli_num_rows($invoice_check_result) > 0) {
                                        $invoice = mysqli_fetch_assoc($invoice_check_result);
                                        if ($invoice['status'] == 'Approved') {
                                            echo '<a href="view_invoice.php?invoice_id=' . htmlspecialchars($invoice['id']) . '" class="btn btn-success btn-block mt-2" target="_blank"><i class="fas fa-eye"></i> Show Invoice</a>';
                                        } else {
                                            echo '<button class="btn btn-warning btn-block mt-2" onclick="alert(\'Waiting for approval of admin.\')"><i class="fas fa-clock"></i> Show Invoice</button>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-<?php echo ($bill_data['status'] == 'Paid') ? 'success' : 'warning'; ?>">
                                        <div class="card-header bg-<?php echo ($bill_data['status'] == 'Paid') ? 'success' : 'warning'; ?> text-white">
                                            <h6 class="mb-0"><i class="fas fa-credit-card"></i> Payment Status</h6>
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
                                            <?php if($bill_data['status'] != 'Paid'): ?>
                                            <form method="post" action="patient-panel.php" class="mt-3">
                                                <div class="form-group">
                                                    <label for="payment_amount">Payment Amount</label>
                                                    <input type="number" step="0.01" name="payment_amount" id="payment_amount" class="form-control" value="<?php echo $bill_data['total'] - ($bill_data['amount_paid'] ?? 0); ?>" min="0.01" max="<?php echo $bill_data['total'] - ($bill_data['amount_paid'] ?? 0); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="payment_method">Payment Method</label>
                                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                                        <option value="">Select Method</option>
                                                        <option value="Cash">Cash</option>
                                                        <option value="Credit Card">Credit Card</option>
                                                        <option value="Online Transfer">Online Transfer</option>
                                                    </select>
                                                </div>
                                                <button type="submit" name="request_payment" class="btn btn-success btn-block">
                                                    <i class="fas fa-credit-card"></i> Pay
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <h5><i class="fas fa-history"></i> Payment History</h5>
                            <?php if(mysqli_num_rows($payments_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-glass">
                                    <thead>
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
                            <?php else: ?>
                            <p class="text-muted">No billing information available yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="prescriptions" role="tabpanel" aria-labelledby="prescriptions-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-medkit me-2"></i>Prescriptions
                            </h4>
                            <?php if(mysqli_num_rows($prescriptions_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Doctor</th>
<<<<<<< HEAD
                                            <th>Medicine Name</th>
                                            <th>Dosage</th>
                                            <th>Frequency</th>
                                            <th>Duration</th>
                                            <th>Notes</th>
=======
                                            <th>Diagnosis</th>
                                            <th>Prescribed Medicines</th>
                                            <th>Price</th>
                                            <th>Action</th>
>>>>>>> a5c017c (Initial project setup with updated files)
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($pres = mysqli_fetch_array($prescriptions_result)): ?>
                                        <tr>
<<<<<<< HEAD
                                            <td><?php echo isset($pres['created_date']) ? $pres['created_date'] : 'N/A'; ?></td>
                                            <td><?php echo isset($pres['doctor']) ? $pres['doctor'] : 'N/A'; ?></td>
                                            <td><strong><?php echo isset($pres['prescription']) ? $pres['prescription'] : 'N/A'; ?></strong></td>
                                            <td>N/A</td>
                                            <td>N/A</td>
                                            <td>N/A</td>
                                            <td><?php echo isset($pres['diagnosis_details']) ? $pres['diagnosis_details'] : 'No additional notes'; ?></td>
=======
                                            <td><?php echo $pres['created_date']; ?></td>
                                            <td><?php echo $pres['doctor']; ?></td>
                                            <td><?php echo substr($pres['diagnosis_details'], 0, 50) . '...'; ?></td>
                                            <td>
                                                <?php 
                                                $medicines = explode(',', $pres['prescribed_medicines']);
                                                $dosages = isset($pres['dosage']) ? explode(',', $pres['dosage']) : [];
                                                $frequencies = isset($pres['frequency']) ? explode(',', $pres['frequency']) : [];
                                                $durations = isset($pres['duration']) ? explode(',', $pres['duration']) : [];
                                                $count = count($medicines);
                                                $details = [];
                                                for ($i = 0; $i < $count; $i++) {
                                                    $med = trim($medicines[$i]);
                                                    $dos = isset($dosages[$i]) ? trim($dosages[$i]) : '';
                                                    $freq = isset($frequencies[$i]) ? trim($frequencies[$i]) : '';
                                                    $dur = isset($durations[$i]) ? trim($durations[$i]) : '';
                                                    $details[] = $med . ($dos ? " ($dos" : "") . ($freq ? ", $freq" : "") . ($dur ? ", $dur)" : ($dos ? ")" : ""));
                                                }
                                                echo implode('<br>', $details);
                                                ?>
                                            </td>
                                        <td>₱<?php echo number_format($pres['price'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#presModal-<?php echo $pres['id']; ?>">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                        </td>
>>>>>>> a5c017c (Initial project setup with updated files)
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No prescriptions available yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
=======
    
    <!-- Modals for Diagnostics Details -->
    <?php foreach($diagnostics_data as $diag): ?>
    <div class="modal fade" id="diagModal-<?php echo $diag['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="diagModalLabel-<?php echo $diag['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="diagModalLabel-<?php echo $diag['id']; ?>">Diagnostic Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> <?php echo $diag['created_date']; ?></p>
                            <p><strong>Time:</strong> <?php echo $diag['created_time']; ?></p>
                            <p><strong>Doctor:</strong> <?php echo $diag['doctor_name']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Patient ID:</strong> <?php echo $diag['pid']; ?></p>
                            <p><strong>Patient Name:</strong> <?php echo $fname . ' ' . $lname; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Diagnosis</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $diag['diagnosis']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Treatment Plan</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $diag['treatment_plan']; ?>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($diag['notes'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Additional Notes</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $diag['notes']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Modals for Lab Results -->
    <?php foreach($lab_tests_data as $lab): ?>
    <div class="modal fade" id="labModal-<?php echo $lab['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="labModalLabel-<?php echo $lab['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labModalLabel-<?php echo $lab['id']; ?>">Lab Test Results - <?php echo $lab['test_name']; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Test Name:</strong> <?php echo $lab['test_name']; ?></p>
                            <p><strong>Requested By:</strong> <?php echo $lab['suggested_by_doctor']; ?></p>
                            <p><strong>Status:</strong> <?php echo $lab['status']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Requested Date:</strong> <?php echo $lab['requested_date']; ?></p>
                            <p><strong>Completed Date:</strong> <?php echo $lab['completed_date'] ?? 'Not completed'; ?></p>
                            <p><strong>Price:</strong> ₱<?php echo number_format($lab['price'], 2); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Test Results</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $lab['results'] ?? 'Results not available yet.'; ?>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($lab['notes'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Additional Notes</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $lab['notes']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Modals for Prescriptions -->
    <?php mysqli_data_seek($prescriptions_result, 0); ?>
    <?php while($pres = mysqli_fetch_array($prescriptions_result)): ?>
    <div class="modal fade" id="presModal-<?php echo $pres['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="presModalLabel-<?php echo $pres['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="presModalLabel-<?php echo $pres['id']; ?>">Prescription Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> <?php echo $pres['created_date']; ?></p>
                            <p><strong>Doctor:</strong> <?php echo $pres['doctor']; ?></p>
                            <p><strong>Patient ID:</strong> <?php echo $pres['pid']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Patient Name:</strong> <?php echo $pres['fname'] . ' ' . $pres['lname']; ?></p>
                            <p><strong>Price:</strong> ₱<?php echo number_format($pres['price'], 2); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Diagnosis Details</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $pres['diagnosis_details']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Prescribed Medicines</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php 
                                $medicines = explode(',', $pres['prescribed_medicines']);
                                $dosages = isset($pres['dosage']) ? explode(',', $pres['dosage']) : [];
                                $frequencies = isset($pres['frequency']) ? explode(',', $pres['frequency']) : [];
                                $durations = isset($pres['duration']) ? explode(',', $pres['duration']) : [];
                                $count = count($medicines);
                                $details = [];
                                for ($i = 0; $i < $count; $i++) {
                                    $med = trim($medicines[$i]);
                                    $dos = isset($dosages[$i]) ? trim($dosages[$i]) : '';
                                    $freq = isset($frequencies[$i]) ? trim($frequencies[$i]) : '';
                                    $dur = isset($durations[$i]) ? trim($durations[$i]) : '';
                                    $details[] = $med . ($dos ? " (Dosage: " . htmlspecialchars($dos) : "") . ($freq ? ", Frequency: " . htmlspecialchars($freq) : "") . ($dur ? ", Duration: " . htmlspecialchars($dur) . ")" : ($dos ? ")" : ""));
                                }
                                echo implode('<br>', $details);
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($pres['symptoms'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Symptoms</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $pres['symptoms']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($pres['allergy'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Allergies</h6>
                            <div class="border p-3 bg-light rounded">
                                <?php echo $pres['allergy']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap components
            $('[data-toggle="tab"]').tab();
            
            // Fix for modal glitching
            $('.modal').on('show.bs.modal', function () {
                $('body').addClass('modal-open');
            });
            
            $('.modal').on('hidden.bs.modal', function () {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
            
            // Ensure modals close properly
            $('.modal .close, .modal [data-dismiss="modal"]').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });
        });
    </script>
>>>>>>> a5c017c (Initial project setup with updated files)
</body>
</html>