
<?php
session_start();

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
$admin_username = $_SESSION['username'];
if (isset($_POST['discharge_patient'])) {
    $pid = $_POST['discharge_pid'];
    $discharge_date = date('Y-m-d');
    $update_query = "UPDATE admissiontb SET status='Discharged' WHERE pid='$pid'";
    $discharge_query = "INSERT INTO dischargetb (pid, discharge_date, approved_by_admin) VALUES ('$pid', '$discharge_date', 1) ON DUPLICATE KEY UPDATE discharge_date='$discharge_date', approved_by_admin=1";
    if (mysqli_query($con, $update_query)) {
        mysqli_query($con, $discharge_query);
        echo "<script>alert('Patient discharged successfully!');</script>";
    }
}
if (isset($_POST['update_payment'])) {
    $pid = $_POST['pid'];
    $status = $_POST['payment_status'];
    
    $update_query = "UPDATE billtb SET status='$status' WHERE pid='$pid'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Payment status updated successfully!');</script>";
    }
}
if (isset($_POST['update_patient'])) {
    $pid = $_POST['pid'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $age = $_POST['age'];
    $address = $_POST['address'];
    $blood_group = $_POST['blood_group'];
    $emergency_contact = $_POST['emergency_contact'];
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $password = $_POST['password'] ?? null; 
    
    $update_query = "UPDATE admissiontb SET fname='$fname', lname='$lname', gender='$gender', email='$email', contact='$contact', age='$age', address='$address', blood_group='$blood_group', emergency_contact='$emergency_contact', emergency_contact_name='$emergency_contact_name' WHERE pid='$pid'";
    $update_patreg_query = "UPDATE patreg SET fname='$fname', lname='$lname', gender='$gender', email='$email', contact='$contact'";
    if ($password !== null && $password !== '') {
        $update_patreg_query .= ", password='$password'";
    }
    $update_patreg_query .= " WHERE pid='$pid'";
    
    $success1 = mysqli_query($con, $update_query);
    $success2 = mysqli_query($con, $update_patreg_query);
    if ($success1 && $success2) {
        echo "<script>alert('Patient updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating patient details.');</script>";
    }
}

if (isset($_POST['delete_patient'])) {
    $pid = $_POST['delete_pid'];
    mysqli_query($con, "DELETE FROM billtb WHERE pid='$pid'");
    mysqli_query($con, "DELETE FROM dischargetb WHERE pid='$pid'");
    mysqli_query($con, "DELETE FROM labtesttb WHERE pid='$pid'");
    $delete_query1 = "DELETE FROM admissiontb WHERE pid='$pid'";
    $delete_query2 = "DELETE FROM patreg WHERE pid='$pid'";
    $success1 = mysqli_query($con, $delete_query1);
    $success2 = mysqli_query($con, $delete_query2);
    if ($success1 && $success2) {
        echo "<script>alert('Patient deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting patient.');</script>";
    }
}
if (isset($_POST['add_doctor'])) {
    $username = $_POST['doctor_username'];
    $password = $_POST['doctor_password'];
    $fname = $_POST['doctor_fname'];
    $lname = $_POST['doctor_lname'];
    $email = $_POST['doctor_email'];
    $contact = $_POST['doctor_contact'];
    $specialization = $_POST['doctor_specialization'];
    $qualification = $_POST['doctor_qualification'];
    $experience = $_POST['doctor_experience'];
    $fee = $_POST['doctor_fee'];
    
    $insert_query = "INSERT INTO doctortb (username, password, fname, lname, email, contact, specialization, qualification, experience_years, consultation_fee, created_by) VALUES ('$username', '$password', '$fname', '$lname', '$email', '$contact', '$specialization', '$qualification', '$experience', '$fee', '$admin_username')";
    if (mysqli_query($con, $insert_query)) {
        echo "<script>alert('Doctor added successfully!');</script>";
    }
}
if (isset($_POST['update_doctor'])) {
    $id = $_POST['doctor_id'];
    $fname = $_POST['doctor_fname'];
    $lname = $_POST['doctor_lname'];
    $email = $_POST['doctor_email'];
    $contact = $_POST['doctor_contact'];
    $specialization = $_POST['doctor_specialization'];
    $qualification = $_POST['doctor_qualification'];
    $experience = $_POST['doctor_experience'];
    $fee = $_POST['doctor_fee'];
    $status = $_POST['doctor_status'];
    
    $update_query = "UPDATE doctortb SET fname='$fname', lname='$lname', email='$email', contact='$contact', specialization='$specialization', qualification='$qualification', experience_years='$experience', consultation_fee='$fee', status='$status' WHERE id='$id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Doctor updated successfully!');</script>";
    }
}
if (isset($_POST['delete_doctor'])) {
    $id = $_POST['delete_doctor_id'];
    
    $delete_query = "DELETE FROM doctortb WHERE id='$id'";
    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Doctor deleted successfully!');</script>";
    }
}
if (isset($_POST['add_nurse'])) {
    $username = $_POST['nurse_username'];
    $password = $_POST['nurse_password'];
    $fname = $_POST['nurse_fname'];
    $lname = $_POST['nurse_lname'];
    $email = $_POST['nurse_email'];
    $contact = $_POST['nurse_contact'];
    $department = $_POST['nurse_department'];
    $shift = $_POST['nurse_shift'];
    
    $insert_query = "INSERT INTO nursetb (username, password, email, fname, lname, contact, department, shift, created_by) VALUES ('$username', '$password', '$email', '$fname', '$lname', '$contact', '$department', '$shift', '$admin_username')";
    if (mysqli_query($con, $insert_query)) {
        echo "<script>alert('Nurse added successfully!');</script>";
    }
}
if (isset($_POST['update_nurse'])) {
    $id = $_POST['nurse_id'];
    $fname = $_POST['nurse_fname'];
    $lname = $_POST['nurse_lname'];
    $email = $_POST['nurse_email'];
    $contact = $_POST['nurse_contact'];
    $department = $_POST['nurse_department'];
    $shift = $_POST['nurse_shift'];
    $status = $_POST['nurse_status'];
    
    $update_query = "UPDATE nursetb SET fname='$fname', lname='$lname', email='$email', contact='$contact', department='$department', shift='$shift', status='$status' WHERE id='$id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Nurse updated successfully!');</script>";
    }
}
if (isset($_POST['delete_nurse'])) {
    $id = $_POST['delete_nurse_id'];
    
    $delete_query = "DELETE FROM nursetb WHERE id='$id'";
    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Nurse deleted successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Madridano Health Care Hospital</title>
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
                    <i class="fas fa-user-shield me-1"></i>Admin: <?php echo $admin_username; ?>
                </span>
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px;">
        <div class="welcome-header">
            <h2>Welcome back, <?php echo $admin_username; ?>!</h2>
            <p>Hospital Administration Dashboard - Manage admissions, billing, and discharges</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <a class="nav-link active" role="tab" data-toggle="tab" href="#dashboard" aria-controls="dashboard" aria-selected="true">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#admissions" aria-controls="admissions" aria-selected="false">
                            <i class="fas fa-hospital-user me-2"></i>Patient Admissions
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#billing" aria-controls="billing" aria-selected="false">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Billing Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#discharge" aria-controls="discharge" aria-selected="false">
                            <i class="fas fa-sign-out-alt me-2"></i>Patient Discharge
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#list-pat-list" aria-controls="patient-management" aria-selected="false">
                            <i class="fas fa-users me-2"></i>Patient Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#doctor-management" aria-controls="doctor-management" aria-selected="false">
                            <i class="fas fa-user-md me-2"></i>Doctor Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#nurse-management" aria-controls="nurse-management" aria-selected="false">
                            <i class="fas fa-user-nurse me-2"></i>Nurse Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#invoice-requests" aria-controls="invoice-requests" aria-selected="false">
                            <i class="fas fa-file-invoice me-2"></i>Invoice Requests
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
                <div class="tab-content">
                                <div class="tab-pane fade" id="invoice-requests" role="tabpanel" aria-labelledby="invoice-requests-tab">
                <div class="glass-card p-4">
                    <h4 class="text-dark mb-4">
                        <i class="fas fa-file-invoice me-2"></i>Invoice Requests
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-glass">
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

                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--primary-gradient);">
                                    <i class="fas fa-hospital-user fa-3x mb-3"></i>
                                    <h3><?php 
                                        $query = mysqli_query($con, "SELECT COUNT(*) as total FROM admissiontb");
                                        $row = mysqli_fetch_assoc($query);
                                        echo $row['total'] ?? 0;
                                    ?></h3>
                                    <p>Total Admissions</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-user-injured fa-3x mb-3"></i>
                                    <h3><?php 
                                        $query = mysqli_query($con, "SELECT COUNT(*) as total FROM admissiontb WHERE status='Admitted'");
                                        $row = mysqli_fetch_assoc($query);
                                        echo $row['total'] ?? 0;
                                    ?></h3>
                                    <p>Active Patients</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--warning-gradient);">
                                    <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                    <h3><?php 
                                        $query = mysqli_query($con, "SELECT COUNT(*) as total FROM billtb WHERE status='Unpaid'");
                                        $row = mysqli_fetch_assoc($query);
                                        echo $row['total'] ?? 0;
                                    ?></h3>
                                    <p>Pending Bills</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--success-gradient);">
                                    <i class="fas fa-dollar-sign fa-3x mb-3"></i>
                                    <h3>₱<?php 
                                        $query = mysqli_query($con, "SELECT SUM(total) as revenue FROM billtb WHERE status='Paid'");
                                        $row = mysqli_fetch_assoc($query);
                                        echo number_format($row['revenue'] ?? 0, 2);
                                    ?></h3>
                                    <p>Total Revenue</p>
                                </div>
                            </div>
                        </div>
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-clock me-2"></i>Recent Admissions
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Admission Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $query = mysqli_query($con, "SELECT * FROM admissiontb ORDER BY admission_date DESC LIMIT 5");
                                        while($row = mysqli_fetch_array($query)) {
                                            $statusClass = ($row['status'] == 'Admitted') ? 'success' : 'secondary';
                                            echo '<tr>
                                                <td>'.$row['pid'].'</td>
                                                <td>'.$row['fname'].' '.$row['lname'].'</td>
                                                <td>'.$row['contact'].'</td>
                                                <td>'.$row['admission_date'].'</td>
                                                <td><span class="badge bg-'.$statusClass.'">'.$row['status'].'</span></td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="admissions">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-hospital-user me-2"></i>All Patient Admissions
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Contact</th>
                                            <th>Email</th>
                                            <th>Admission Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $query = mysqli_query($con, "SELECT * FROM admissiontb ORDER BY admission_date DESC");
                                        while($row = mysqli_fetch_array($query)) {
                                            $statusClass = ($row['status'] == 'Admitted') ? 'success' : 'secondary';
                                            echo '<tr>
                                                <td>'.$row['pid'].'</td>
                                                <td>'.$row['fname'].' '.$row['lname'].'</td>
                                                <td>'.$row['gender'].'</td>
                                                <td>'.$row['contact'].'</td>
                                                <td>'.$row['email'].'</td>
                                                <td>'.$row['admission_date'].'</td>
                                                <td><span class="badge bg-'.$statusClass.'">'.$row['status'].'</span></td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="billing">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Billing Management
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Patient Name</th>
                                            <th>Consultation</th>
                                            <th>Lab Fees</th>
                                            <th>Medicine</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $query = mysqli_query($con, "SELECT b.*, a.fname, a.lname FROM billtb b JOIN admissiontb a ON b.pid = a.pid ORDER BY b.pid DESC");
                                        while($row = mysqli_fetch_array($query)) {
                                            $statusClass = ($row['status'] == 'Paid') ? 'success' : 'warning';
                                            echo '<tr>
                                                <td>'.$row['pid'].'</td>
                                                <td>'.$row['fname'].' '.$row['lname'].'</td>
                                                <td>$'.number_format($row['consultation_fees'], 2).'</td>
                                                <td>$'.number_format($row['lab_fees'], 2).'</td>
                                                <td>$'.number_format($row['medicine_fees'], 2).'</td>
                                                <td>$'.number_format($row['total'], 2).'</td>
                                                <td><span class="badge bg-'.$statusClass.'">'.$row['status'].'</span></td>
                                                <td>';
                                            if($row['status'] == 'Unpaid') {
                                                echo '<form method="POST" style="display:inline;">
                                                    <input type="hidden" name="pid" value="'.$row['pid'].'">
                                                    <input type="hidden" name="payment_status" value="Paid">
                                                    <button type="submit" name="update_payment" class="btn btn-sm btn-success">Mark Paid</button>
                                                </form>';
                                            }
                                            echo '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="discharge">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-sign-out-alt me-2"></i>Patient Discharge Management
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Admission Date</th>
                                            <th>Bill Status</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $query = mysqli_query($con, "SELECT a.*, b.status as bill_status FROM admissiontb a LEFT JOIN billtb b ON a.pid = b.pid WHERE a.status = 'Admitted' ORDER BY a.admission_date DESC");
                                        while($row = mysqli_fetch_array($query)) {
                                            $billStatusClass = ($row['bill_status'] == 'Paid') ? 'success' : 'warning';
                                            echo '<tr>
                                                <td>'.$row['pid'].'</td>
                                                <td>'.$row['fname'].' '.$row['lname'].'</td>
                                                <td>'.$row['contact'].'</td>
                                                <td>'.$row['admission_date'].'</td>
                                                <td><span class="badge bg-'.$billStatusClass.'">'.($row['bill_status'] ?? 'Unpaid').'</span></td>
                                                <td><span class="badge bg-info">'.$row['status'].'</span></td>
                                                <td>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="discharge_pid" value="'.$row['pid'].'">
                                                        <button type="submit" name="discharge_patient" class="btn btn-sm btn-primary" onclick="return confirm(\'Are you sure you want to discharge this patient?\')">
                                                            <i class="fas fa-sign-out-alt"></i> Discharge
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="patient-management">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-users me-2"></i>Patient Management
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Gender</th>
                                            <th>Contact</th>
                                            <th>Email</th>
                                            <th>Age</th>
                                            <th>Address</th>
                                            <th>Blood Group</th>
                                            <th>Emergency Contact</th>
                                            <th>Emergency Contact Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM admissiontb ORDER BY admission_date DESC");
                                        while ($row = mysqli_fetch_array($query)) {
                                            echo '<tr>
                                                <td>' . $row['pid'] . '</td>
                                                <td>' . $row['fname'] . '</td>
                                                <td>' . $row['lname'] . '</td>
                                                <td>' . $row['gender'] . '</td>
                                                <td>' . $row['contact'] . '</td>
                                                <td>' . $row['email'] . '</td>
                                                <td>' . $row['age'] . '</td>
                                                <td>' . $row['address'] . '</td>
                                                <td>' . $row['blood_group'] . '</td>
                                                <td>' . $row['emergency_contact'] . '</td>
                                                <td>' . $row['emergency_contact_name'] . '</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editPatientModal" onclick="editPatient(\'' . $row['pid'] . '\', \'' . $row['fname'] . '\', \'' . $row['lname'] . '\', \'' . $row['gender'] . '\', \'' . $row['email'] . '\', \'' . $row['contact'] . '\', \'' . $row['age'] . '\', \'' . $row['address'] . '\', \'' . $row['blood_group'] . '\', \'' . $row['emergency_contact'] . '\', \'' . $row['emergency_contact_name'] . '\')">Edit</button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="delete_pid" value="' . $row['pid'] . '">
                                                        <button type="submit" name="delete_patient" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this patient?\')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="doctor-management">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-md me-2"></i>Doctor Management</span>
                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addDoctorModal">
                                    <i class="fas fa-plus"></i> Add Doctor
                                </button>
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Doctor ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Specialization</th>
                                            <th>Qualification</th>
                                            <th>Experience (Years)</th>
                                            <th>Consultation Fee</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM doctortb ORDER BY id DESC");
                                        while ($row = mysqli_fetch_array($query)) {
                                            echo '<tr>
                                                <td>' . $row['id'] . '</td>
                                                <td>' . $row['fname'] . '</td>
                                                <td>' . $row['lname'] . '</td>
                                                <td>' . $row['email'] . '</td>
                                                <td>' . $row['contact'] . '</td>
                                                <td>' . $row['specialization'] . '</td>
                                                <td>' . $row['qualification'] . '</td>
                                                <td>' . $row['experience_years'] . '</td>
                                                <td>₱' . number_format($row['consultation_fee'], 2) . '</td>
                                                <td>' . $row['status'] . '</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editDoctorModal" onclick="editDoctor(\'' . $row['id'] . '\', \'' . $row['fname'] . '\', \'' . $row['lname'] . '\', \'' . $row['email'] . '\', \'' . $row['contact'] . '\', \'' . $row['specialization'] . '\', \'' . $row['qualification'] . '\', \'' . $row['experience_years'] . '\', \'' . $row['consultation_fee'] . '\', \'' . $row['status'] . '\')">Edit</button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="delete_doctor_id" value="' . $row['id'] . '">
                                                        <button type="submit" name="delete_doctor" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this doctor?\')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add Doctor Modal -->
                    <div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addDoctorModalLabel">Add Doctor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_username" class="form-label">Username</label>
                                                <input type="text" class="form-control" id="doctor_username" name="doctor_username" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="doctor_password" name="doctor_password" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_fname" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="doctor_fname" name="doctor_fname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_lname" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="doctor_lname" name="doctor_lname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="doctor_email" name="doctor_email" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_contact" class="form-label">Contact</label>
                                                <input type="text" class="form-control" id="doctor_contact" name="doctor_contact" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_specialization" class="form-label">Specialization</label>
                                                <input type="text" class="form-control" id="doctor_specialization" name="doctor_specialization" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_qualification" class="form-label">Qualification</label>
                                                <input type="text" class="form-control" id="doctor_qualification" name="doctor_qualification" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_experience" class="form-label">Experience (Years)</label>
                                                <input type="number" class="form-control" id="doctor_experience" name="doctor_experience" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="doctor_fee" class="form-label">Consultation Fee</label>
                                                <input type="number" step="0.01" class="form-control" id="doctor_fee" name="doctor_fee" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Nurse Management -->
                    <div class="tab-pane fade" id="nurse-management">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-nurse me-2"></i>Nurse Management</span>
                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addNurseModal">
                                    <i class="fas fa-plus"></i> Add Nurse
                                </button>
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Nurse ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Department</th>
                                            <th>Shift</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM nursetb ORDER BY id DESC");
                                        while ($row = mysqli_fetch_array($query)) {
                                            echo '<tr>
                                                <td>' . $row['id'] . '</td>
                                                <td>' . $row['fname'] . '</td>
                                                <td>' . $row['lname'] . '</td>
                                                <td>' . $row['email'] . '</td>
                                                <td>' . $row['contact'] . '</td>
                                                <td>' . $row['department'] . '</td>
                                                <td>' . $row['shift'] . '</td>
                                                <td>' . $row['status'] . '</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editNurseModal" onclick="editNurse(\'' . $row['id'] . '\', \'' . $row['fname'] . '\', \'' . $row['lname'] . '\', \'' . $row['email'] . '\', \'' . $row['contact'] . '\', \'' . $row['department'] . '\', \'' . $row['shift'] . '\', \'' . $row['status'] . '\')">Edit</button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="delete_nurse_id" value="' . $row['id'] . '">
                                                        <button type="submit" name="delete_nurse" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this nurse?\')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="addNurseModal" tabindex="-1" aria-labelledby="addNurseModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addNurseModalLabel">Add Nurse</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_username" class="form-label">Username</label>
                                                <input type="text" class="form-control" id="nurse_username" name="nurse_username" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="nurse_password" name="nurse_password" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_fname" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="nurse_fname" name="nurse_fname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_lname" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="nurse_lname" name="nurse_lname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="nurse_email" name="nurse_email" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_contact" class="form-label">Contact</label>
                                                <input type="text" class="form-control" id="nurse_contact" name="nurse_contact" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_department" class="form-label">Department</label>
                                                <input type="text" class="form-control" id="nurse_department" name="nurse_department" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nurse_shift" class="form-label">Shift</label>
                                                <select class="form-control" id="nurse_shift" name="nurse_shift" required>
                                                    <option value="Morning">Morning</option>
                                                    <option value="Evening">Evening</option>
                                                    <option value="Night">Night</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="add_nurse" class="btn btn-primary">Add Nurse</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editPatientModalLabel">Edit Patient</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="edit_pid" name="pid">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_fname" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="edit_fname" name="fname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_lname" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="edit_lname" name="lname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_gender" class="form-label">Gender</label>
                                                <select class="form-control" id="edit_gender" name="gender" required>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="edit_email" name="email" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_contact" class="form-label">Contact</label>
                                                <input type="text" class="form-control" id="edit_contact" name="contact" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_age" class="form-label">Age</label>
                                                <input type="number" class="form-control" id="edit_age" name="age" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="edit_address" class="form-label">Address</label>
                                                <textarea class="form-control" id="edit_address" name="address" rows="2" required></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_blood_group" class="form-label">Blood Group</label>
                                                <input type="text" class="form-control" id="edit_blood_group" name="blood_group" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_emergency_contact" class="form-label">Emergency Contact</label>
                                                <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="edit_emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                                <input type="text" class="form-control" id="edit_emergency_contact_name" name="emergency_contact_name" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update_patient" class="btn btn-primary">Update Patient</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="editDoctorModal" tabindex="-1" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editDoctorModalLabel">Edit Doctor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="edit_doctor_id" name="doctor_id">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_fname" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="edit_doctor_fname" name="doctor_fname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_lname" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="edit_doctor_lname" name="doctor_lname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="edit_doctor_email" name="doctor_email" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_contact" class="form-label">Contact</label>
                                                <input type="text" class="form-control" id="edit_doctor_contact" name="doctor_contact" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_specialization" class="form-label">Specialization</label>
                                                <input type="text" class="form-control" id="edit_doctor_specialization" name="doctor_specialization" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_qualification" class="form-label">Qualification</label>
                                                <input type="text" class="form-control" id="edit_doctor_qualification" name="doctor_qualification" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_experience" class="form-label">Experience (Years)</label>
                                                <input type="number" class="form-control" id="edit_doctor_experience" name="doctor_experience" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_doctor_fee" class="form-label">Consultation Fee</label>
                                                <input type="number" step="0.01" class="form-control" id="edit_doctor_fee" name="doctor_fee" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="edit_doctor_status" class="form-label">Status</label>
                                                <select class="form-control" id="edit_doctor_status" name="doctor_status" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                    <option value="On Leave">On Leave</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update_doctor" class="btn btn-primary">Update Doctor</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="editNurseModal" tabindex="-1" aria-labelledby="editNurseModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editNurseModalLabel">Edit Nurse</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="edit_nurse_id" name="nurse_id">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_nurse_fname" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="edit_nurse_fname" name="nurse_fname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_nurse_lname" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="edit_nurse_lname" name="nurse_lname" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_nurse_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="edit_nurse_email" name="nurse_email" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_nurse_contact" class="form-label">Contact</label>
                                                <input type="text" class="form-control" id="edit_nurse_contact" name="nurse_contact" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_nurse_department" class="form-label">Department</label>
                                                <input type="text" class="form-control" id="edit_nurse_department" name="nurse_department" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_nurse_shift" class="form-label">Shift</label>
                                                <select class="form-control" id="edit_nurse_shift" name="nurse_shift" required>
                                                    <option value="Morning">Morning</option>
                                                    <option value="Evening">Evening</option>
                                                    <option value="Night">Night</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="edit_nurse_status" class="form-label">Status</label>
                                                <select class="form-control" id="edit_nurse_status" name="nurse_status" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update_nurse" class="btn btn-primary">Update Nurse</button>
                                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
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
?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editPatient(pid, fname, lname, gender, email, contact, age, address, blood_group, emergency_contact, emergency_contact_name) {
        document.getElementById('edit_pid').value = pid;
        document.getElementById('edit_fname').value = fname;
        document.getElementById('edit_lname').value = lname;
        document.getElementById('edit_gender').value = gender;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_contact').value = contact;
        document.getElementById('edit_age').value = age;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_blood_group').value = blood_group;
        document.getElementById('edit_emergency_contact').value = emergency_contact;
        document.getElementById('edit_emergency_contact_name').value = emergency_contact_name;
    }

    function editDoctor(id, fname, lname, email, contact, specialization, qualification, experience, fee, status) {
        document.getElementById('edit_doctor_id').value = id;
        document.getElementById('edit_doctor_fname').value = fname;
        document.getElementById('edit_doctor_lname').value = lname;
        document.getElementById('edit_doctor_email').value = email;
        document.getElementById('edit_doctor_contact').value = contact;
        document.getElementById('edit_doctor_specialization').value = specialization;
        document.getElementById('edit_doctor_qualification').value = qualification;
        document.getElementById('edit_doctor_experience').value = experience;
        document.getElementById('edit_doctor_fee').value = fee;
        document.getElementById('edit_doctor_status').value = status;
    }

    function editNurse(id, fname, lname, email, contact, department, shift, status) {
        document.getElementById('edit_nurse_id').value = id;
        document.getElementById('edit_nurse_fname').value = fname;
        document.getElementById('edit_nurse_lname').value = lname;
        document.getElementById('edit_nurse_email').value = email;
        document.getElementById('edit_nurse_contact').value = contact;
        document.getElementById('edit_nurse_department').value = department;
        document.getElementById('edit_nurse_shift').value = shift;
        document.getElementById('edit_nurse_status').value = status;
    }
