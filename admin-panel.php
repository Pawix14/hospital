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

// Calculate performance metrics
$avg_stay_query = mysqli_query($con, "SELECT AVG(DATEDIFF(NOW(), admission_date)) as avg_stay FROM admissiontb WHERE status='Admitted'");
$avg_stay = mysqli_fetch_assoc($avg_stay_query)['avg_stay'] ?? 0;

$bed_occupancy_query = mysqli_query($con, "SELECT (COUNT(*) / 100) * 100 as occupancy FROM admissiontb WHERE status='Admitted'"); // Assuming 100 total beds
$occupancy_rate = mysqli_fetch_assoc($bed_occupancy_query)['occupancy'] ?? 0;

// Fetch statistics for dashboard
$total_admissions = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM admissiontb"))['total'] ?? 0;
$active_patients = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM admissiontb WHERE status='Admitted'"))['total'] ?? 0;
$pending_bills = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM billtb WHERE status='Unpaid'"))['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(total) as revenue FROM billtb WHERE status='Paid'"))['revenue'] ?? 0;

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
    $password = $_POST['password'] ?? null;
    $update_query = "UPDATE admissiontb SET fname='$fname', lname='$lname', gender='$gender', email='$email', contact='$contact', age='$age', address='$address'";
    if ($password !== null && $password !== '') {
        $update_query .= ", password='$password'";
    }
    $update_query .= " WHERE pid='$pid'";

    $success1 = mysqli_query($con, $update_query);
    if ($success1) {
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
    mysqli_query($con, "DELETE FROM diagnosticstb WHERE pid='$pid'");
    mysqli_query($con, "DELETE FROM patient_chargstb WHERE pid='$pid'");
    mysqli_query($con, "DELETE FROM paymentstb WHERE pid='$pid'");
    mysqli_query($con, "DELETE FROM invoicetb WHERE pid='$pid'");
    $delete_query1 = "DELETE FROM admissiontb WHERE pid='$pid'";
    $success1 = mysqli_query($con, $delete_query1);
    if ($success1) {
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

if (isset($_POST['toggle_2fa'])) {
    $user_type = $_POST['user_type'];
    $user_id = $_POST['user_id'];
    $current_status = $_POST['current_status'];
    $new_status = $current_status ? 0 : 1;
    
    switch($user_type) {
        case 'patient':
            $table = 'admissiontb';
            $id_field = 'pid';
            break;
        case 'doctor':
            $table = 'doctortb';
            $id_field = 'id';
            break;
        case 'nurse':
            $table = 'nursetb';
            $id_field = 'id';
            break;
        case 'lab':
            $table = 'labtb';
            $id_field = 'id';
            break;
        case 'admin':
            $table = 'adminusertb';
            $id_field = 'username';
            break;
        default:
            echo "<script>alert('Invalid user type!');</script>";
            break;
    }
    
    if (isset($table)) {
        $update_query = "UPDATE $table SET two_factor_enabled = '$new_status' WHERE $id_field = '$user_id'";
        if (mysqli_query($con, $update_query)) {
            $status_text = $new_status ? 'enabled' : 'disabled';
            echo "<script>alert('2FA successfully $status_text for user!');</script>";
            echo "<script>window.location.href = 'admin-panel.php#password-management';</script>";
        } else {
            echo "<script>alert('Error updating 2FA status!');</script>";
        }
    }
}

// Insurance Company Management
if (isset($_POST['add_insurance_company'])) {
    $company_name = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $policy_details = $_POST['policy_details'];

    $insert_query = "INSERT INTO insurance_companiestb (company_name, contact_person, contact_number, email, address, policy_details) VALUES ('$company_name', '$contact_person', '$contact_number', '$email', '$address', '$policy_details')";
    if (mysqli_query($con, $insert_query)) {
        echo "<script>alert('Insurance company added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding insurance company.');</script>";
    }
}

if (isset($_POST['update_insurance_company'])) {
    $insurance_id = $_POST['insurance_id'];
    $company_name = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $policy_details = $_POST['policy_details'];

    $update_query = "UPDATE insurance_companiestb SET company_name='$company_name', contact_person='$contact_person', contact_number='$contact_number', email='$email', address='$address', policy_details='$policy_details' WHERE insurance_id='$insurance_id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Insurance company updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating insurance company.');</script>";
    }
}

if (isset($_POST['delete_insurance_company'])) {
    $insurance_id = $_POST['delete_insurance_id'];

    $delete_query = "DELETE FROM insurance_companiestb WHERE insurance_id='$insurance_id'";
    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Insurance company deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting insurance company.');</script>";
    }
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

if (isset($_POST['approve_emergency']) || isset($_POST['deny_emergency'])) {
    $request_id = $_POST['request_id'];
    $admin_username = $_SESSION['username'];
    $action = isset($_POST['approve_emergency']) ? 'approved' : 'denied';

    if ($action == 'approved') {
        $one_time_token = bin2hex(random_bytes(32));
        $token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE emergency_access_logs SET
                  status = 'approved',
                  handled_by = '$admin_username',
                  handled_at = NOW(),
                  one_time_token = '$one_time_token',
                  token_expires = '$token_expires',
                  auto_login_used = 0
                  WHERE id = '$request_id'";

        error_log("DEBUG: Approving emergency request ID: " . $request_id . " with token: " . $one_time_token);
    } else {
        $query = "UPDATE emergency_access_logs SET
                  status = 'denied',
                  handled_by = '$admin_username',
                  handled_at = NOW()
                  WHERE id = '$request_id'";
    }

    if (mysqli_query($con, $query)) {
        if ($action == 'approved') {
            $request_query = mysqli_query($con, "SELECT * FROM emergency_access_logs WHERE id = '$request_id'");
            $request = mysqli_fetch_assoc($request_query);

            error_log("DEBUG: Emergency request approved - User: " . $request['staff_username'] . ", Token: " . $request['one_time_token']);

            echo "<script>
                alert('Emergency request approved!\\\\nUser: {$request['staff_username']}\\\\nThey can now login once without 2FA within 1 hour.');
            </script>";
        } else {
            echo "<script>alert('Emergency request denied!');</script>";
        }
        echo "<script>window.location.href = 'admin-panel.php#emergency-management';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating emergency request!');</script>";
        error_log("ERROR: Failed to update emergency request: " . mysqli_error($con));
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        .password-toggle, .toggle-password {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #dc3545; }
        .strength-fair { background: #fd7e14; }
        .strength-good { background: #20c997; }
        .strength-strong { background: #198754; }

        /* Enhanced CSS */
        .metric-card {
            padding: 15px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
        }

        .report-card {
            transition: transform 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .report-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.05);
        }

        .department-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 15px;
        }

        .department-card:hover {
            background: rgba(255,255,255,0.1);
            border-color: #667eea;
        }

        .activity-item {
            padding: 10px;
            border-left: 3px solid #667eea;
            background: rgba(255,255,255,0.05);
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .quick-action-btn {
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .trend-indicator {
            font-size: 0.8em;
            margin-left: 5px;
        }

        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
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
                        <a class="nav-link active" role="tab" data-toggle="tab" href="#dashboard" aria-controls="dashboard" aria-selected="true" id="dashboard-tab">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#admissions" aria-controls="admissions" aria-selected="false" id="admissions-tab">
                            <i class="fas fa-hospital-user me-2"></i>Patient Admissions
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#billing" aria-controls="billing" aria-selected="false" id="billing-tab">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Billing Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#discharge" aria-controls="discharge" aria-selected="false" id="discharge-tab">
                            <i class="fas fa-sign-out-alt me-2"></i>Patient Discharge
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#patient-management" aria-controls="patient-management" aria-selected="false" id="patient-management-tab">
                            <i class="fas fa-users me-2"></i>Patient Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#doctor-management" aria-controls="doctor-management" aria-selected="false" id="doctor-management-tab">
                            <i class="fas fa-user-md me-2"></i>Doctor Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#nurse-management" aria-controls="nurse-management" aria-selected="false" id="nurse-management-tab">
                            <i class="fas fa-user-nurse me-2"></i>Nurse Management
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#invoice-requests" aria-controls="invoice-requests" aria-selected="false" id="invoice-requests-tab">
                            <i class="fas fa-file-invoice me-2"></i>Invoice Requests
                        </a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#prescriptions" aria-controls="prescriptions" aria-selected="false" id="prescriptions-tab">
                            <i class="fa fa-medkit me-2"></i>Prescriptions
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#password-management" aria-controls="password-management" aria-selected="false" id="password-management-tab">
                            <i class="fas fa-key me-2"></i>Password Management
                         </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#emergency-management" aria-controls="emergency-management" aria-selected="false" id="emergency-management-tab">
                        <i class="fas fa-exclamation-triangle me-2"></i>Emergency Requests
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#patient-insurance-management" aria-controls="patient-insurance-management" aria-selected="false" id="patient-insurance-management-tab">
                            <i class="fas fa-shield-alt me-2"></i>Patient Insurance
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9 col-md-8">
                <div class="tab-content">
                    <!-- Enhanced Dashboard -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <!-- Quick Actions -->
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Quick Actions</h5>
                            <div class="row g-2">
                                <div class="col-auto">
                                    <button class="btn btn-outline-primary btn-sm quick-action-btn" data-toggle="modal" data-target="#addDoctorModal">
                                        <i class="fas fa-plus me-1"></i>Add Doctor
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-outline-success btn-sm quick-action-btn" data-toggle="modal" data-target="#addNurseModal">
                                        <i class="fas fa-plus me-1"></i>Add Nurse
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <a href="#billing" class="btn btn-outline-warning btn-sm quick-action-btn">
                                        <i class="fas fa-file-invoice me-1"></i>Process Bills
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="#discharge" class="btn btn-outline-info btn-sm quick-action-btn">
                                        <i class="fas fa-sign-out-alt me-1"></i>Manage Discharges
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Statistics -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--primary-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3><?php echo $total_admissions; ?></h3>
                                            <p>Total Admissions</p>
                                        </div>
                                        <i class="fas fa-hospital-user fa-2x"></i>
                                    </div>
                                    <small class="text-white-50">+5% from last month <i class="fas fa-arrow-up trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--secondary-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3><?php echo $active_patients; ?></h3>
                                            <p>Active Patients</p>
                                        </div>
                                        <i class="fas fa-user-injured fa-2x"></i>
                                    </div>
                                    <small class="text-white-50">+2% from yesterday <i class="fas fa-arrow-up trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--warning-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3><?php echo $pending_bills; ?></h3>
                                            <p>Pending Bills</p>
                                        </div>
                                        <i class="fas fa-file-invoice fa-2x"></i>
                                    </div>
                                    <small class="text-white-50">-3% from last week <i class="fas fa-arrow-down trend-indicator trend-down"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--success-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3>₱<?php echo number_format($total_revenue, 2); ?></h3>
                                            <p>Total Revenue</p>
                                        </div>
                                        <i class="fas fa-hospital-symbol fa-2x"></i>
                                    </div>
                                    <small class="text-white-50">+12% this month <i class="fas fa-arrow-up trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Hospital Performance</h5>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-success"><?php echo number_format($avg_stay, 1); ?>d</h3>
                                        <small>Avg. Stay Duration</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-info"><?php echo number_format($occupancy_rate, 1); ?>%</h3>
                                        <small>Bed Occupancy</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-warning">15m</h3>
                                        <small>Avg. Response Time</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-primary">94%</h3>
                                        <small>Satisfaction Rate</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts & Analytics -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="glass-card p-4">
                                    <h5 class="text-dark mb-3">Monthly Admissions</h5>
                                    <canvas id="admissionsChart" height="200"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="glass-card p-4">
                                    <h5 class="text-dark mb-3">Revenue Trends</h5>
                                    <canvas id="revenueChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Department Overview -->
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Department Overview</h5>
                            <div class="row">
                                <?php
                                $dept_stats = mysqli_query($con, "
                                    SELECT specialization as department, COUNT(*) as count 
                                    FROM doctortb 
                                    WHERE status='Active' 
                                    GROUP BY specialization
                                    LIMIT 4
                                ");
                                while($dept = mysqli_fetch_array($dept_stats)) {
                                    echo '<div class="col-md-3 mb-3">
                                        <div class="department-card p-3">
                                            <h6 class="mb-1">'.$dept['department'].'</h6>
                                            <h4 class="text-primary mb-0">'.$dept['count'].'</h4>
                                            <small class="text-muted">Active Doctors</small>
                                        </div>
                                    </div>';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Reports & Analytics -->
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Reports & Analytics</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="report-card">
                                        <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                        <h6>Monthly Financial Report</h6>
                                        <button class="btn btn-sm btn-outline-danger">Generate PDF</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="report-card">
                                        <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                                        <h6>Patient Statistics</h6>
                                        <button class="btn btn-sm btn-outline-success">Export Excel</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="report-card">
                                        <i class="fas fa-chart-bar fa-2x text-primary mb-2"></i>
                                        <h6>Performance Analytics</h6>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="glass-card p-4">
                            <h5 class="text-dark mb-3">
                                <i class="fas fa-list-alt me-2"></i>Recent Activity
                            </h5>
                            <div class="activity-feed">
                                <?php
                                $activity_query = mysqli_query($con, "
                                    SELECT 'admission' as type, CONCAT('Patient ', fname, ' ', lname, ' admitted') as activity, admission_date as date 
                                    FROM admissiontb 
                                    ORDER BY admission_date DESC 
                                    LIMIT 5
                                ");
                                while($activity = mysqli_fetch_array($activity_query)) {
                                    echo '<div class="activity-item d-flex align-items-center">
                                        <span class="badge bg-primary me-2">'.ucfirst($activity['type']).'</span>
                                        <span>'.$activity['activity'].'</span>
                                        <small class="text-muted ms-auto">'.date('M j, g:i A', strtotime($activity['date'])).'</small>
                                    </div>';
                                }
                                ?>
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
                                            <th>Total Amount</th>
                                            <th>Insurance Covered</th>
                                            <th>Patient Payable</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                            <th>Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
$medicine_fees_result = mysqli_query($con, "SELECT pid, SUM(price) AS total_medicine_fees FROM prestb GROUP BY pid");
                                        $medicine_fees_map = [];
                                        while ($mf_row = mysqli_fetch_assoc($medicine_fees_result)) {
                                            $medicine_fees_map[$mf_row['pid']] = $mf_row['total_medicine_fees'];
                                        }

                                        $insurance_result = mysqli_query($con, "SELECT pi.patient_id, pi.coverage_percent FROM patient_insurancetb pi WHERE pi.status = 'active'");
                                        $insurance_map = [];
                                        while ($ins_row = mysqli_fetch_assoc($insurance_result)) {
                                            $insurance_map[$ins_row['patient_id']] = $ins_row['coverage_percent'];
                                        }

                                        $query = mysqli_query($con, "SELECT b.*, a.fname, a.lname FROM billtb b JOIN admissiontb a ON b.pid = a.pid ORDER BY b.pid DESC");
                                        while($row = mysqli_fetch_array($query)) {
                                            $statusClass = ($row['status'] == 'Paid') ? 'success' : 'warning';
                                            $calculated_medicine_fees = $medicine_fees_map[$row['pid']] ?? 0;
                                            $calculated_total =
                                                ($row['consultation_fees'] ?? 0) +
                                                ($row['lab_fees'] ?? 0) +
                                                $calculated_medicine_fees +
                                                ($row['room_charges'] ?? 0) +
                                                ($row['service_charges'] ?? 0);

                                            // Calculate insurance coverage
                                            $coverage_percent = $insurance_map[$row['pid']] ?? 0;
                                            $insurance_covered = $calculated_total * ($coverage_percent / 100);
                                            $patient_payable = $calculated_total - $insurance_covered;

                                            // Update billtb with calculated insurance amounts if not already set
                                            if (($row['insurance_covered'] ?? 0) == 0 && ($row['patient_payable'] ?? 0) == 0) {
                                                $update_insurance_query = "UPDATE billtb SET insurance_covered='$insurance_covered', patient_payable='$patient_payable' WHERE pid='{$row['pid']}'";
                                                mysqli_query($con, $update_insurance_query);
                                            }

                                            echo '<tr>
                                                <td>'.$row['pid'].'</td>
                                                <td>'.$row['fname'].' '.$row['lname'].'</td>
                                                <td>₱'.number_format($row['consultation_fees'], 2).'</td>
                                                <td>₱'.number_format($row['lab_fees'], 2).'</td>
                                                <td>₱'.number_format($calculated_medicine_fees, 2).'</td>
                                                <td>₱'.number_format($calculated_total, 2).'</td>
                                                <td>₱'.number_format($insurance_covered, 2).'<br><small class="text-muted">('.$coverage_percent.'% coverage)</small></td>
                                                <td>₱'.number_format($patient_payable, 2).'</td>
                                                <td><span class="badge bg-'.$statusClass.'">'.$row['status'].'</span></td>
                                                <td>';
                                            if($row['status'] == 'Unpaid') {
                                                echo '<form method="POST" style="display:inline;">
                                                    <input type="hidden" name="pid" value="'.$row['pid'].'">
                                                    <input type="hidden" name="payment_status" value="Paid">
                                                    <button type="submit" name="update_payment" class="btn btn-sm btn-success">Mark Paid</button>
                                                </form>';
                                            }
                                            echo '</td>
                                            <td>';
                                            if($row['status'] == 'Paid') {
                                                $receipt_generated = $row['receipt_generated'] ?? 0;
                                                if($receipt_generated == 1) {
                                                    echo '<a href="generate_receipt.php?pid='.$row['pid'].'" class="btn btn-sm btn-success" target="_blank">Show Receipt</a>';
                                                } else {
                                                    echo '<a href="generate_receipt.php?pid='.$row['pid'].'&generate=true" class="btn btn-sm btn-info" target="_blank">Generate Receipt</a>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Not Available</span>';
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
$query = mysqli_query($con, "SELECT a.*, b.status as bill_status FROM admissiontb a LEFT JOIN billtb b ON a.pid = b.pid ORDER BY a.admission_date DESC");
                                        while($row = mysqli_fetch_array($query)) {
                                            $billStatusClass = ($row['bill_status'] == 'Paid') ? 'success' : 'warning';
                                            $status = $row['status'];
                                            $badgeClass = 'secondary'; 
                                            if ($status === 'Admitted') {
                                                $badgeClass = 'success'; 
                                            } elseif ($status === 'Discharged') {
                                                $badgeClass = 'secondary'; 
                                            } elseif ($status === 'Ready for Discharge') {
                                                $badgeClass = 'warning';
                                            }
                                            echo '<tr>
                                                <td>' . $row['pid'] . '</td>
                                                <td>' . $row['fname'] . ' ' . $row['lname'] . '</td>
                                                <td>' . $row['contact'] . '</td>
                                                <td>' . $row['admission_date'] . '</td>
                                                <td><span class="badge bg-' . $billStatusClass . '">' . ($row['bill_status'] ?? 'Unpaid') . '</span></td>
                                                <td><span class="badge bg-' . $badgeClass . '">' . $status . '</span></td>
                                                <td>
                                                     <form method="POST" style="display:inline;">
                                                         <input type="hidden" name="discharge_pid" value="' . $row['pid'] . '">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
$query = mysqli_query($con, "
    SELECT pid, fname, lname, gender, contact, email, age, address, password
    FROM admissiontb
    ORDER BY pid DESC
");

while ($row = mysqli_fetch_assoc($query)) {
    echo '<tr>
        <td>' . $row['pid'] . '</td>
        <td>' . $row['fname'] . '</td>
        <td>' . $row['lname'] . '</td>
        <td>' . $row['gender'] . '</td>
        <td>' . $row['contact'] . '</td>
        <td>' . $row['email'] . '</td>
        <td>' . $row['age'] . '</td>
        <td>' . $row['address'] . '</td>
        <td>
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editPatientModal"
                onclick="editPatient(
                    \'' . $row['pid'] . '\',
                    \'' . $row['fname'] . '\',
                    \'' . $row['lname'] . '\',
                    \'' . $row['gender'] . '\',
                    \'' . $row['email'] . '\',
                    \'' . $row['contact'] . '\',
                    \'' . $row['age'] . '\',
                    \'' . $row['address'] . '\'
                )">Edit</button>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_pid" value="' . $row['pid'] . '">
                <button type="submit" name="delete_patient" class="btn btn-sm btn-danger" 
                    onclick="return confirm(\'Are you sure you want to delete this patient?\')">Delete</button>
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
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="tab-pane fade" id="prescriptions" role="tabpanel" aria-labelledby="prescriptions-tab">
                <div class="glass-card p-4">
                    <h4 class="text-dark mb-4">
                        <i class="fa fa-medkit me-2"></i>Prescriptions
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-glass">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient Name</th>
                                    <th>Doctor</th>
                                    <th>Symptoms</th>
                                    <th>Diagnosis</th>
                                    <th>Prescribed Medicines</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $prescriptions_query = "SELECT p.*, a.fname, a.lname FROM prestb p JOIN admissiontb a ON p.pid = a.pid ORDER BY p.id DESC";
                                $prescriptions_result = mysqli_query($con, $prescriptions_query);
                                while ($pres = mysqli_fetch_array($prescriptions_result)) {
                                    echo '<tr>
                                        <td>' . $pres['pid'] . '</td>
                                        <td>' . $pres['fname'] . ' ' . $pres['lname'] . '</td>
                                        <td>' . $pres['doctor'] . '</td>
                                        <td>' . $pres['symptoms'] . '</td>
                                        <td>' . $pres['diagnosis_details'] . '</td>
                                        <td>' . $pres['prescribed_medicines'] . '</td>
                                        <td>₱' . number_format($pres['price'], 2) . '</td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
<div class="tab-pane fade" id="password-management" role="tabpanel" aria-labelledby="password-management-tab">
    <div class="glass-card p-4">
        <h4 class="text-dark mb-4">
            <i class="fas fa-key me-2"></i>User Password & 2FA Management
        </h4>
        <div class="card mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i> Two-Factor Authentication Management</h6>
                <small>Toggle 2FA for users</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6><i class="fas fa-user-md me-2"></i> Doctors</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>2FA Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $doctor_query = mysqli_query($con, "SELECT id, username, fname, lname, two_factor_enabled FROM doctortb ORDER BY id DESC");
                                    while ($doctor = mysqli_fetch_array($doctor_query)) {
                                        $status = $doctor['two_factor_enabled'];
                                        $status_badge = $status ? 
                                            '<span class="badge bg-success">Enabled</span>' : 
                                            '<span class="badge bg-secondary">Disabled</span>';
                                        $btn_class = $status ? 'btn-warning' : 'btn-success';
                                        $btn_text = $status ? 'Disable' : 'Enable';
                                        $btn_icon = $status ? 'fa-times' : 'fa-check';
                                        
                                        echo '<tr>
                                            <td>' . $doctor['username'] . '</td>
                                            <td>' . $doctor['fname'] . ' ' . $doctor['lname'] . '</td>
                                            <td>' . $status_badge . '</td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_type" value="doctor">
                                                    <input type="hidden" name="user_id" value="' . $doctor['id'] . '">
                                                    <input type="hidden" name="current_status" value="' . $status . '">
                                                    <button type="submit" name="toggle_2fa" class="btn btn-sm ' . $btn_class . '" 
                                                            onclick="return confirm(\'' . ($status ? 'Disable' : 'Enable') . ' 2FA for ' . $doctor['username'] . '?\')">
                                                        <i class="fas ' . $btn_icon . ' me-1"></i>' . $btn_text . '
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
                    <div class="col-md-6 mb-4">
                        <h6><i class="fas fa-user-nurse me-2"></i> Nurses</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>2FA Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $nurse_query = mysqli_query($con, "SELECT id, username, fname, lname, two_factor_enabled FROM nursetb ORDER BY id DESC");
                                    while ($nurse = mysqli_fetch_array($nurse_query)) {
                                        $status = $nurse['two_factor_enabled'];
                                        $status_badge = $status ? 
                                            '<span class="badge bg-success">Enabled</span>' : 
                                            '<span class="badge bg-secondary">Disabled</span>';
                                        $btn_class = $status ? 'btn-warning' : 'btn-success';
                                        $btn_text = $status ? 'Disable' : 'Enable';
                                        $btn_icon = $status ? 'fa-times' : 'fa-check';
                                        
                                        echo '<tr>
                                            <td>' . $nurse['username'] . '</td>
                                            <td>' . $nurse['fname'] . ' ' . $nurse['lname'] . '</td>
                                            <td>' . $status_badge . '</td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_type" value="nurse">
                                                    <input type="hidden" name="user_id" value="' . $nurse['id'] . '">
                                                    <input type="hidden" name="current_status" value="' . $status . '">
                                                    <button type="submit" name="toggle_2fa" class="btn btn-sm ' . $btn_class . '" 
                                                            onclick="return confirm(\'' . ($status ? 'Disable' : 'Enable') . ' 2FA for ' . $nurse['username'] . '?\')">
                                                        <i class="fas ' . $btn_icon . ' me-1"></i>' . $btn_text . '
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
                    <div class="col-md-6 mb-4">
                        <h6><i class="fas fa-flask me-2"></i> Lab Staff</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>2FA Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $lab_query = mysqli_query($con, "SELECT id, username, fname, lname, two_factor_enabled FROM labtb ORDER BY id DESC");
                                    while ($lab = mysqli_fetch_array($lab_query)) {
                                        $status = $lab['two_factor_enabled'];
                                        $status_badge = $status ? 
                                            '<span class="badge bg-success">Enabled</span>' : 
                                            '<span class="badge bg-secondary">Disabled</span>';
                                        $btn_class = $status ? 'btn-warning' : 'btn-success';
                                        $btn_text = $status ? 'Disable' : 'Enable';
                                        $btn_icon = $status ? 'fa-times' : 'fa-check';
                                        
                                        echo '<tr>
                                            <td>' . $lab['username'] . '</td>
                                            <td>' . $lab['fname'] . ' ' . $lab['lname'] . '</td>
                                            <td>' . $status_badge . '</td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_type" value="lab">
                                                    <input type="hidden" name="user_id" value="' . $lab['id'] . '">
                                                    <input type="hidden" name="current_status" value="' . $status . '">
                                                    <button type="submit" name="toggle_2fa" class="btn btn-sm ' . $btn_class . '" 
                                                            onclick="return confirm(\'' . ($status ? 'Disable' : 'Enable') . ' 2FA for ' . $lab['username'] . '?\')">
                                                        <i class="fas ' . $btn_icon . ' me-1"></i>' . $btn_text . '
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
                    <div class="col-md-6 mb-4">
                        <h6><i class="fas fa-user-shield me-2"></i> Administrators</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>2FA Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $admin_query = mysqli_query($con, "SELECT username, email, two_factor_enabled FROM adminusertb ORDER BY username");
                                    while ($admin = mysqli_fetch_array($admin_query)) {
                                        $status = $admin['two_factor_enabled'];
                                        $status_badge = $status ? 
                                            '<span class="badge bg-success">Enabled</span>' : 
                                            '<span class="badge bg-secondary">Disabled</span>';
                                        $btn_class = $status ? 'btn-warning' : 'btn-success';
                                        $btn_text = $status ? 'Disable' : 'Enable';
                                        $btn_icon = $status ? 'fa-times' : 'fa-check';
                                        
                                        echo '<tr>
                                            <td>' . $admin['username'] . '</td>
                                            <td>' . $admin['email'] . '</td>
                                            <td>' . $status_badge . '</td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_type" value="admin">
                                                    <input type="hidden" name="user_id" value="' . $admin['username'] . '">
                                                    <input type="hidden" name="current_status" value="' . $status . '">
                                                    <button type="submit" name="toggle_2fa" class="btn btn-sm ' . $btn_class . '" 
                                                            onclick="return confirm(\'' . ($status ? 'Disable' : 'Enable') . ' 2FA for ' . $admin['username'] . '?\')">
                                                        <i class="fas ' . $btn_icon . ' me-1"></i>' . $btn_text . '
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
            </div>
        </div>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i> Patient Passwords</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $patient_query = mysqli_query($con, "SELECT pid, fname, lname, email FROM admissiontb ORDER BY pid DESC");
                                    while ($patient = mysqli_fetch_array($patient_query)) {
                                        echo '<tr>
                                            <td>' . $patient['pid'] . '</td>
                                            <td>' . $patient['fname'] . ' ' . $patient['lname'] . '</td>
                                            <td>' . $patient['email'] . '</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#changePasswordModal" 
                                                        onclick="setPasswordChange(\'patient\', \'' . $patient['pid'] . '\', \'' . $patient['fname'] . ' ' . $patient['lname'] . '\')">
                                                    <i class="fas fa-edit"></i> Change Password
                                                </button>
                                            </td>
                                        </tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i> Staff Passwords</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-user-md me-2"></i> Doctors</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $doctor_query = mysqli_query($con, "SELECT id, username, fname, lname FROM doctortb ORDER BY id DESC");
                                            while ($doctor = mysqli_fetch_array($doctor_query)) {
                                                echo '<tr>
                                                    <td>' . $doctor['username'] . '</td>
                                                    <td>' . $doctor['fname'] . ' ' . $doctor['lname'] . '</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#changePasswordModal" 
                                                                onclick="setPasswordChange(\'doctor\', \'' . $doctor['id'] . '\', \'' . $doctor['fname'] . ' ' . $doctor['lname'] . '\')">
                                                            <i class="fas fa-edit"></i> Change
                                                        </button>
                                                    </td>
                                                </tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-user-nurse me-2"></i> Nurses</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $nurse_query = mysqli_query($con, "SELECT id, username, fname, lname FROM nursetb ORDER BY id DESC");
                                            while ($nurse = mysqli_fetch_array($nurse_query)) {
                                                echo '<tr>
                                                    <td>' . $nurse['username'] . '</td>
                                                    <td>' . $nurse['fname'] . ' ' . $nurse['lname'] . '</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#changePasswordModal" 
                                                                onclick="setPasswordChange(\'nurse\', \'' . $nurse['id'] . '\', \'' . $nurse['fname'] . ' ' . $nurse['lname'] . '\')">
                                                            <i class="fas fa-edit"></i> Change
                                                        </button>
                                                    </td>
                                                </tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-flask me-2"></i> Lab Staff</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $lab_query = mysqli_query($con, "SELECT id, username, fname, lname FROM labtb ORDER BY id DESC");
                                            while ($lab = mysqli_fetch_array($lab_query)) {
                                                echo '<tr>
                                                    <td>' . $lab['username'] . '</td>
                                                    <td>' . $lab['fname'] . ' ' . $lab['lname'] . '</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#changePasswordModal" 
                                                                onclick="setPasswordChange(\'lab\', \'' . $lab['id'] . '\', \'' . $lab['fname'] . ' ' . $lab['lname'] . '\')">
                                                            <i class="fas fa-edit"></i> Change
                                                        </button>
                                                    </td>
                                                </tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-user-shield me-2"></i> Administrators</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $admin_query = mysqli_query($con, "SELECT username, email FROM adminusertb ORDER BY username");
                                            while ($admin = mysqli_fetch_array($admin_query)) {
                                                echo '<tr>
                                                    <td>' . $admin['username'] . '</td>
                                                    <td>' . $admin['email'] . '</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#changePasswordModal" 
                                                                onclick="setPasswordChange(\'admin\', \'' . $admin['username'] . '\', \'' . $admin['username'] . '\')">
                                                            <i class="fas fa-edit"></i> Change
                                                        </button>
                                                    </td>
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
        </div>
                <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="admin-panel.php" id="passwordChangeForm">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="user_type" id="user_type">
                                    <input type="hidden" name="user_id" id="user_id">
                                    
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="new_password" name="new_password" required 
                                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                                title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            Password must be at least 8 characters with uppercase, lowercase, number, and special character
                                        </small>
                                        <div class="password-strength mt-2" id="newPasswordStrength"></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback" id="passwordMatchError">
                                            Passwords do not match
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
<div class="tab-pane fade" id="patient-insurance-management" role="tabpanel" aria-labelledby="patient-insurance-management-tab">
    <div class="glass-card p-4">
        <h4 class="text-dark mb-4">
            <i class="fas fa-shield-alt me-2"></i>Patient Insurance Management
        </h4>

        <!-- Insurance Companies Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-building me-2"></i>Insurance Companies</h6>
                <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#addInsuranceCompanyModal">
                    <i class="fas fa-plus"></i> Add Company
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Policy Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $insurance_query = mysqli_query($con, "SELECT * FROM insurance_companiestb ORDER BY insurance_id DESC");
                            while ($insurance = mysqli_fetch_array($insurance_query)) {
                                echo '<tr>
                                    <td>' . $insurance['insurance_id'] . '</td>
                                    <td>' . $insurance['company_name'] . '</td>
                                    <td>' . $insurance['contact_person'] . '</td>
                                    <td>' . $insurance['contact_number'] . '</td>
                                    <td>' . $insurance['email'] . '</td>
                                    <td>' . $insurance['address'] . '</td>
                                    <td>' . $insurance['policy_details'] . '</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editInsuranceCompanyModal"
                                            onclick="editInsuranceCompany(\'' . $insurance['insurance_id'] . '\', \'' . addslashes($insurance['company_name']) . '\', \'' . addslashes($insurance['contact_person']) . '\', \'' . $insurance['contact_number'] . '\', \'' . $insurance['email'] . '\', \'' . addslashes($insurance['address']) . '\', \'' . addslashes($insurance['policy_details']) . '\')">Edit</button>
                                            <input type="hidden" name="delete_insurance_id" value="' . $insurance['insurance_id'] . '">
                                            <button type="submit" name="delete_insurance_company" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this insurance company?\')">Delete</button>
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
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i>Patient Insurance Assignments</h6>
                <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#addPatientInsuranceModal">
                    <i class="fas fa-plus"></i> Assign Insurance
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Insurance Company</th>
                                <th>Policy Number</th>
                                <th>Coverage (%)</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $patient_insurance_query = mysqli_query($con, "
                                SELECT pi.*, a.fname, a.lname, ic.company_name
                                FROM patient_insurancetb pi
                                JOIN admissiontb a ON pi.patient_id = a.pid
                                JOIN insurance_companiestb ic ON pi.insurance_id = ic.insurance_id
                                ORDER BY pi.patient_insurance_id DESC
                            ");
                            while ($assignment = mysqli_fetch_array($patient_insurance_query)) {
                                $status_badge = $assignment['status'] == 'active' ?
                                    '<span class="badge bg-success">Active</span>' :
                                    '<span class="badge bg-secondary">Inactive</span>';
                                echo '<tr>
                                    <td>' . $assignment['patient_insurance_id'] . '</td>
                                    <td>' . $assignment['fname'] . ' ' . $assignment['lname'] . '</td>
                                    <td>' . $assignment['company_name'] . '</td>
                                    <td>' . $assignment['policy_number'] . '</td>
                                    <td>' . $assignment['coverage_percent'] . '%</td>
                                    <td>' . $assignment['start_date'] . '</td>
                                    <td>' . ($assignment['end_date'] ?? 'Ongoing') . '</td>
                                    <td>' . $status_badge . '</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editPatientInsuranceModal"
                                            onclick="editPatientInsurance(\'' . $assignment['patient_insurance_id'] . '\', \'' . $assignment['patient_id'] . '\', \'' . $assignment['insurance_id'] . '\', \'' . $assignment['policy_number'] . '\', \'' . $assignment['coverage_percent'] . '\', \'' . $assignment['start_date'] . '\', \'' . ($assignment['end_date'] ?? '') . '\', \'' . $assignment['status'] . '\')">Edit</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_patient_insurance_id" value="' . $assignment['patient_insurance_id'] . '">
                                            <button type="submit" name="delete_patient_insurance" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this insurance assignment?\')">Delete</button>
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
    </div>
</div>

<!-- Add Insurance Company Modal -->
<div class="modal fade" id="addInsuranceCompanyModal" tabindex="-1" aria-labelledby="addInsuranceCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInsuranceCompanyModalLabel">Add Insurance Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="company_email" name="email" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="company_address" class="form-label">Address</label>
                            <textarea class="form-control" id="company_address" name="address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="policy_details" class="form-label">Policy Details</label>
                            <textarea class="form-control" id="policy_details" name="policy_details" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_insurance_company" class="btn btn-primary">Add Company</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="editInsuranceCompanyModal" tabindex="-1" aria-labelledby="editInsuranceCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInsuranceCompanyModalLabel">Edit Insurance Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_insurance_id" name="insurance_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="edit_company_name" name="company_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="edit_contact_person" name="contact_person" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="edit_contact_number" name="contact_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_company_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_company_email" name="email" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_company_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_company_address" name="address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_policy_details" class="form-label">Policy Details</label>
                            <textarea class="form-control" id="edit_policy_details" name="policy_details" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_insurance_company" class="btn btn-primary">Update Company</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Patient Insurance Modal -->
<div class="modal fade" id="addPatientInsuranceModal" tabindex="-1" aria-labelledby="addPatientInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPatientInsuranceModalLabel">Assign Insurance to Patient</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="patient_id" class="form-label">Patient</label>
                            <select class="form-control" id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php
                                $patient_query = mysqli_query($con, "SELECT pid, fname, lname FROM admissiontb ORDER BY fname, lname");
                                while ($patient = mysqli_fetch_array($patient_query)) {
                                    echo '<option value="' . $patient['pid'] . '">' . $patient['fname'] . ' ' . $patient['lname'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assign_insurance_id" class="form-label">Insurance Company</label>
                            <select class="form-control" id="assign_insurance_id" name="insurance_id" required>
                                <option value="">Select Insurance Company</option>
                                <?php
                                $insurance_query = mysqli_query($con, "SELECT insurance_id, company_name FROM insurance_companiestb ORDER BY company_name");
                                while ($insurance = mysqli_fetch_array($insurance_query)) {
                                    echo '<option value="' . $insurance['insurance_id'] . '">' . $insurance['company_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="policy_number" class="form-label">Policy Number</label>
                            <input type="text" class="form-control" id="policy_number" name="policy_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="coverage_percent" class="form-label">Coverage Percent (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="coverage_percent" name="coverage_percent" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="insurance_status" class="form-label">Status</label>
                            <select class="form-control" id="insurance_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_patient_insurance" class="btn btn-primary">Assign Insurance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Patient Insurance Modal -->
<div class="modal fade" id="editPatientInsuranceModal" tabindex="-1" aria-labelledby="editPatientInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPatientInsuranceModalLabel">Edit Patient Insurance Assignment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_patient_insurance_id" name="patient_insurance_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_patient_id" class="form-label">Patient</label>
                            <select class="form-control" id="edit_patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <?php
                                $patient_query = mysqli_query($con, "SELECT pid, fname, lname FROM admissiontb ORDER BY fname, lname");
                                while ($patient = mysqli_fetch_array($patient_query)) {
                                    echo '<option value="' . $patient['pid'] . '">' . $patient['fname'] . ' ' . $patient['lname'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_assign_insurance_id" class="form-label">Insurance Company</label>
                            <select class="form-control" id="edit_assign_insurance_id" name="insurance_id" required>
                                <option value="">Select Insurance Company</option>
                                <?php
                                $insurance_query = mysqli_query($con, "SELECT insurance_id, company_name FROM insurance_companiestb ORDER BY company_name");
                                while ($insurance = mysqli_fetch_array($insurance_query)) {
                                    echo '<option value="' . $insurance['insurance_id'] . '">' . $insurance['company_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_policy_number" class="form-label">Policy Number</label>
                            <input type="text" class="form-control" id="edit_policy_number" name="policy_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_coverage_percent" class="form-label">Coverage Percent (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="edit_coverage_percent" name="coverage_percent" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_insurance_status" class="form-label">Status</label>
                            <select class="form-control" id="edit_insurance_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_patient_insurance" class="btn btn-primary">Update Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="emergency-management" role="tabpanel" aria-labelledby="emergency-management-tab">
    <div class="glass-card p-4">
        <h4 class="text-dark mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>Emergency Access Requests
        </h4>

        <?php
        $emergency_query = mysqli_query($con, "SELECT * FROM emergency_access_logs ORDER BY created_at DESC");
        $pending_count = mysqli_num_rows(mysqli_query($con, "SELECT * FROM emergency_access_logs WHERE status='pending'"));
        ?>
        
        <?php if ($pending_count > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-bell me-2"></i>
                <strong><?php echo $pending_count; ?> pending emergency request(s) need attention!</strong>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-glass">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Staff</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Auto-Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = mysqli_fetch_array($emergency_query)): ?>
                    <tr>
                        <td>
                            <?php echo date('M j, g:i A', strtotime($request['created_at'])); ?>
                            <br><small class="text-muted"><?php echo $request['ip_address']; ?></small>
                        </td>
                        <td><strong><?php echo $request['staff_username']; ?></strong></td>
                        <td><?php echo ucfirst($request['staff_role']); ?></td>
                        <td><?php echo $request['contact_info']; ?></td>
                        <td>
                            <?php echo $request['reason']; ?>
                            <?php if ($request['additional_info']): ?>
                                <br><small class="text-muted"><?php echo $request['additional_info']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $request['status'] == 'pending' ? 'warning' : 
                                     ($request['status'] == 'approved' ? 'success' : 'secondary'); 
                            ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($request['status'] == 'approved' && !$request['auto_login_used'] && strtotime($request['token_expires']) > time()): ?>
                                <small class="text-success">
                                    <i class="fas fa-check-circle"></i> Ready<br>
                                    <small>Expires: <?php echo date('g:i A', strtotime($request['token_expires'])); ?></small>
                                </small>
                            <?php elseif ($request['status'] == 'approved' && $request['auto_login_used']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-check-circle"></i> Used<br>
                                    <?php echo date('M j, g:i A', strtotime($request['handled_at'])); ?>
                                </small>
                            <?php elseif ($request['status'] == 'approved' && strtotime($request['token_expires']) <= time()): ?>
                                <small class="text-danger">
                                    <i class="fas fa-clock"></i> Expired
                                </small>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($request['status'] == 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="approve_emergency" class="btn btn-sm btn-success" 
                                            onclick="return confirm('Approve emergency access for <?php echo $request['staff_username']; ?>? This will allow one-time 2FA bypass.')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="deny_emergency" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Deny emergency access for <?php echo $request['staff_username']; ?>?')">
                                        <i class="fas fa-times"></i> Deny
                                    </button>
                                </form>
                            <?php else: ?>
                                <small class="text-muted">
                                    Handled by: <?php echo $request['handled_by'] ?? 'System'; ?>
                                    <?php if ($request['handled_at']): ?>
                                        <br><?php echo date('M j, g:i A', strtotime($request['handled_at'])); ?>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
if (isset($_POST['approve_emergency']) || isset($_POST['deny_emergency'])) {
    $request_id = $_POST['request_id'];
    $admin_username = $_SESSION['username'];
    $action = isset($_POST['approve_emergency']) ? 'approved' : 'denied';

    if ($action == 'approved') {
        $one_time_token = bin2hex(random_bytes(32));
        $token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE emergency_access_logs SET
                  status = 'approved',
                  handled_by = '$admin_username',
                  handled_at = NOW(),
                  one_time_token = '$one_time_token',
                  token_expires = '$token_expires',
                  auto_login_used = 0
                  WHERE id = '$request_id'";

        error_log("DEBUG: Approving emergency request ID: " . $request_id . " with token: " . $one_time_token);
    } else {
        $query = "UPDATE emergency_access_logs SET
                  status = 'denied',
                  handled_by = '$admin_username',
                  handled_at = NOW()
                  WHERE id = '$request_id'";
    }

    if (mysqli_query($con, $query)) {
        if ($action == 'approved') {
            $request_query = mysqli_query($con, "SELECT * FROM emergency_access_logs WHERE id = '$request_id'");
            $request = mysqli_fetch_assoc($request_query);

            error_log("DEBUG: Emergency request approved - User: " . $request['staff_username'] . ", Token: " . $request['one_time_token']);

            echo "<script>
                alert('Emergency request approved!\\\\nUser: {$request['staff_username']}\\\\nThey can now login once without 2FA within 1 hour.');
            </script>";
        } else {
            echo "<script>alert('Emergency request denied!');</script>";
        }
        echo "<script>window.location.href = 'admin-panel.php#emergency-management';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating emergency request!');</script>";
        error_log("ERROR: Failed to update emergency request: " . mysqli_error($con));
    }
}

// Insurance Company Management
if (isset($_POST['add_insurance_company'])) {
    $company_name = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $policy_details = $_POST['policy_details'];

    $insert_query = "INSERT INTO insurance_companiestb (company_name, contact_person, contact_number, email, address, policy_details) VALUES ('$company_name', '$contact_person', '$contact_number', '$email', '$address', '$policy_details')";
    if (mysqli_query($con, $insert_query)) {
        echo "<script>alert('Insurance company added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding insurance company.');</script>";
    }
}

if (isset($_POST['update_insurance_company'])) {
    $insurance_id = $_POST['insurance_id'];
    $company_name = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $policy_details = $_POST['policy_details'];

    $update_query = "UPDATE insurance_companiestb SET company_name='$company_name', contact_person='$contact_person', contact_number='$contact_number', email='$email', address='$address', policy_details='$policy_details' WHERE insurance_id='$insurance_id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Insurance company updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating insurance company.');</script>";
    }
}

if (isset($_POST['delete_insurance_company'])) {
    $insurance_id = $_POST['delete_insurance_id'];

    $delete_query = "DELETE FROM insurance_companiestb WHERE insurance_id='$insurance_id'";
    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Insurance company deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting insurance company.');</script>";
    }
}
?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.nav-pills .nav-link').removeClass('active');
        $(this).addClass('active');
    });

    function editPatient(pid, fname, lname, gender, email, contact, age, address) {
        document.getElementById('edit_pid').value = pid;
        document.getElementById('edit_fname').value = fname;
        document.getElementById('edit_lname').value = lname;
        document.getElementById('edit_gender').value = gender;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_contact').value = contact;
        document.getElementById('edit_age').value = age;
        document.getElementById('edit_address').value = address;
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
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

    function checkNewPasswordStrength(password) {
        const strengthBar = document.getElementById('newPasswordStrength');
        if (!strengthBar) return;
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        
        strengthBar.className = 'password-strength';
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.style.backgroundColor = 'transparent';
        } else if (strength <= 1) {
            strengthBar.classList.add('strength-weak');
        } else if (strength === 2) {
            strengthBar.classList.add('strength-fair');
        } else if (strength === 3) {
            strengthBar.classList.add('strength-good');
        } else {
            strengthBar.classList.add('strength-strong');
        }
    }

    function validatePasswordMatch() {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorDiv = document.getElementById('passwordMatchError');
        
        if (confirmPassword && password !== confirmPassword) {
            errorDiv.style.display = 'block';
            return false;
        } else {
            errorDiv.style.display = 'none';
            return true;
        }
    }

    function setPasswordChange(userType, userId, userName) {
        document.getElementById('user_type').value = userType;
        document.getElementById('user_id').value = userId;
        document.getElementById('changePasswordModalLabel').textContent = 'Change Password for ' + userName;
        document.getElementById('passwordChangeForm').reset();
        document.getElementById('newPasswordStrength').style.width = '0%';
        document.getElementById('passwordMatchError').style.display = 'none';
    }
    document.getElementById('new_password')?.addEventListener('input', function(e) {
        checkNewPasswordStrength(e.target.value);
    });
    document.getElementById('confirm_password')?.addEventListener('input', validatePasswordMatch);
    document.getElementById('passwordChangeForm')?.addEventListener('submit', function(e) {
        if (!validatePasswordMatch()) {
            e.preventDefault();
            alert('Please make sure passwords match!');
        }
    });
</script>
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Admissions Chart
            const admissionsCtx = document.getElementById('admissionsChart').getContext('2d');
            const admissionsChart = new Chart(admissionsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Monthly Admissions',
                        data: [65, 59, 80, 81, 56, 72],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: [125000, 139000, 142000, 156000, 148000, 162000],
                        backgroundColor: '#4facfe',
                        borderColor: '#00f2fe',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });

        // Enhanced table filtering
        function initTableFilters() {
            $('.table-glass').each(function() {
                let table = $(this);
                let input = $('<input type="text" class="form-control mb-3" placeholder="Search...">');
                table.before(input);
                
                input.on('keyup', function() {
                    let value = $(this).val().toLowerCase();
                    table.find('tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
            });
        }

        // Initialize table filters when page loads
        $(document).ready(function() {
            initTableFilters();
        });

        // Loading states
        function showLoading() {
            $('body').append('<div class="loading-overlay"><div class="spinner-border text-primary"></div></div>');
        }

        function hideLoading() {
            $('.loading-overlay').remove();
        }

        // Enhanced notifications
        function showNotification(message, type = 'success') {
            const notification = $(`
                <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top:20px; right:20px; z-index:9999">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('body').append(notification);
            setTimeout(() => notification.alert('close'), 5000);
        }

        // Your existing JavaScript functions
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('.nav-pills .nav-link').removeClass('active');
            $(this).addClass('active');
        });
</script>
</body>
</html>
