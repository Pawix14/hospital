 <!DOCTYPE html>
<?php
session_start();
include('func.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
$doctor = $_SESSION['username'];

if (isset($_POST['delete_diagnosis'])) {
    $delete_id = $_POST['delete_diagnosis_id'];
    $delete_query = "DELETE FROM diagnosticstb WHERE id = '$delete_id'";
    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Diagnosis record deleted successfully!'); window.location.href='doctor-panel.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting diagnosis record: " . mysqli_error($con) . "');</script>";
    }
}

if (isset($_POST['add_charges'])) {
    $pid = $_POST['pid'];
    $service = $_POST['service'];
    $price = $_POST['price'];
    $date = $_POST['date'];
    $reason = $_POST['reason'];

    $create_charges_table = "CREATE TABLE IF NOT EXISTS chargestb (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pid INT NOT NULL,
        service VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        date DATE NOT NULL,
        reason VARCHAR(255),
        FOREIGN KEY (pid) REFERENCES admissiontb(pid)
    )";
    mysqli_query($con, $create_charges_table);
    $check_pid_query = "SELECT pid FROM admissiontb WHERE pid = '$pid'";
    $check_pid_result = mysqli_query($con, $check_pid_query);

    if (mysqli_num_rows($check_pid_result) > 0) {
        $insert_charge = "INSERT INTO chargestb (pid, service, price, date, reason) VALUES ('$pid', '$service', '$price', '$date', '$reason')";
        if (mysqli_query($con, $insert_charge)) {
            $update_bill = "UPDATE billtb SET medicine_fees = medicine_fees + $price, total = consultation_fees + lab_fees + medicine_fees + $price WHERE pid='$pid'";
            mysqli_query($con, $update_bill);
            echo "<script>alert('Charges added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding charges!');</script>";
        }
    } else {
        echo "<script>alert('Error: Patient is not admitted. Cannot add charges.');</script>";
    }
}

if (isset($_POST['suggest_lab'])) {
    $pid = $_POST['pid'];
    $test_name = $_POST['test_name'];
    $price = $_POST['price'];
    $scheduled_date = $_POST['scheduled_date'];
    $priority = $_POST['priority'] ?? 'Normal';
    $check_pid_query = "SELECT pid FROM admissiontb WHERE pid = '$pid'";
    $check_pid_result = mysqli_query($con, $check_pid_query);
    if (mysqli_num_rows($check_pid_result) > 0) {
        $query = "INSERT INTO labtesttb (pid, test_name, suggested_by_doctor, price, scheduled_date, requested_date, requested_time, priority) VALUES ('$pid', '$test_name', '$doctor', '$price', '$scheduled_date', CURDATE(), CURTIME(), '$priority')";
        if (mysqli_query($con, $query)) {
            $update_bill = "UPDATE billtb SET lab_fees = lab_fees + $price, total = consultation_fees + lab_fees + medicine_fees + service_charges + room_charges WHERE pid='$pid'";
            mysqli_query($con, $update_bill);
            echo "<script>alert('Lab test suggested successfully!');</script>";
        } else {
            echo "<script>alert('Error suggesting lab test!');</script>";
        }
    } else {
        echo "<script>alert('Error: Patient is not admitted. Cannot suggest lab test.');</script>";
    }
}
if (isset($_POST['add_diagnosis'])) {
    $pid = $_POST['pid'];
    $symptoms = $_POST['symptoms'];
    $diagnosis = $_POST['diagnosis'];
    $vital_signs = $_POST['vital_signs'];
    $physical_examination = $_POST['physical_examination'];
    $medical_history = $_POST['medical_history'];
    $diagnostic_tests_ordered = $_POST['diagnostic_tests_ordered'];
    $treatment_plan = $_POST['treatment_plan'];
    
    $query = "INSERT INTO diagnosticstb (pid, doctor_name, symptoms, diagnosis, vital_signs, physical_examination, medical_history, diagnostic_tests_ordered, treatment_plan, created_date, created_time) VALUES ('$pid', '$doctor', '$symptoms', '$diagnosis', '$vital_signs', '$physical_examination', '$medical_history', '$diagnostic_tests_ordered', '$treatment_plan', CURDATE(), CURTIME())";
    
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Diagnosis added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding diagnosis: " . mysqli_error($con) . "');</script>";
    }
}
if (isset($_POST['add_service_charge'])) {
    $pid = $_POST['pid'];
    $service_id = $_POST['service_id'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $service_query = "SELECT service_name, price FROM servicestb WHERE id = '$service_id'";
    $service_result = mysqli_query($con, $service_query);
    $service = mysqli_fetch_array($service_result);
    
    $unit_price = $service['price'];
    $total_price = $unit_price * $quantity;
    
    $query = "INSERT INTO patient_chargstb (pid, service_id, quantity, unit_price, total_price, added_by, added_date, added_time, description) VALUES ('$pid', '$service_id', '$quantity', '$unit_price', '$total_price', '$doctor', CURDATE(), CURTIME(), '$description')";
    
    if (mysqli_query($con, $query)) {
        $update_bill = "UPDATE billtb SET service_charges = service_charges + $total_price, total = consultation_fees + lab_fees + medicine_fees + service_charges + room_charges WHERE pid='$pid'";
        mysqli_query($con, $update_bill);
        echo "<script>alert('Service charge added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding service charge!');</script>";
    }
}

if (isset($_POST['update_service_charge'])) {
    $charge_id = $_POST['charge_id'];
    $pid = $_POST['pid'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $current_query = "SELECT unit_price, total_price FROM patient_chargstb WHERE id = '$charge_id'";
    $current_result = mysqli_query($con, $current_query);
    $current_charge = mysqli_fetch_array($current_result);
    $unit_price = $current_charge['unit_price'];
    $old_total_price = $current_charge['total_price'];
    $new_total_price = $unit_price * $quantity;
    $update_query = "UPDATE patient_chargstb SET quantity = '$quantity', total_price = '$new_total_price', description = '$description' WHERE id = '$charge_id'";
    if (mysqli_query($con, $update_query)) {
        $update_bill = "UPDATE billtb SET service_charges = service_charges - $old_total_price + $new_total_price, total = consultation_fees + lab_fees + medicine_fees + service_charges + room_charges WHERE pid='$pid'";
        mysqli_query($con, $update_bill);
        echo "<script>alert('Service charge updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating service charge!');</script>";
    }
}

if (isset($_POST['delete_service_charge'])) {
    $charge_id = $_POST['charge_id'];
    $pid = $_POST['pid'];
    $current_query = "SELECT total_price FROM patient_chargstb WHERE id = '$charge_id'";
    $current_result = mysqli_query($con, $current_query);
    $current_charge = mysqli_fetch_array($current_result);
    $total_price = $current_charge['total_price'];

    $delete_query = "DELETE FROM patient_chargstb WHERE id = '$charge_id'";
    if (mysqli_query($con, $delete_query)) {
        $update_bill = "UPDATE billtb SET service_charges = service_charges - $total_price, total = consultation_fees + lab_fees + medicine_fees + service_charges + room_charges WHERE pid='$pid'";
        mysqli_query($con, $update_bill);
        echo "<script>alert('Service charge deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting service charge!');</script>";
    }
}

if (isset($_POST['mark_ready'])) {
    $pid = $_POST['pid'];
    $query = "UPDATE admissiontb SET status='Ready for Discharge' WHERE pid='$pid'";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Patient marked as ready for discharge!');</script>";
    } else {
        echo "<script>alert('Error updating status!');</script>";
    }
}

// Store data for modals
$patients_data = [];
$diagnostics_data = [];
$lab_data = [];

// Get patients data
$dname = $_SESSION['username'];
$query = "SELECT pid, fname, lname, age, gender, contact, room_number, admission_date, reason, status FROM admissiontb WHERE assigned_doctor='$dname' AND status != 'Discharged'";
$patients_result = mysqli_query($con,$query);
while ($row = mysqli_fetch_array($patients_result)) {
    $patients_data[] = $row;
}

// Get diagnostics data
$diagnostics_query = "SELECT d.*, a.fname, a.lname, p.prescribed_medicines FROM diagnosticstb d 
                    JOIN admissiontb a ON d.pid = a.pid 
                    LEFT JOIN prestb p ON d.pid = p.pid AND d.doctor_name = p.doctor
                    WHERE d.doctor_name = '$doctor' 
                    ORDER BY d.created_date DESC, d.created_time DESC";
$diagnostics_result = mysqli_query($con, $diagnostics_query);
while ($diag = mysqli_fetch_array($diagnostics_result)) {
    $diagnostics_data[] = $diag;
}

// Get lab data
$lab_query = "SELECT lt.*, a.fname, a.lname FROM labtesttb lt 
            JOIN admissiontb a ON lt.pid = a.pid 
            WHERE lt.suggested_by_doctor = '$doctor' 
            ORDER BY lt.requested_date DESC, lt.requested_time DESC";
$lab_result = mysqli_query($con, $lab_query);
while ($lab = mysqli_fetch_array($lab_result)) {
    $lab_data[] = $lab;
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Madridano Health Care Hospital</title>
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
            cursor: pointer !important;
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

        .badge-lg {
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .btn-outline-light:hover {
            color: #25bef7;
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }

        .modal {
            z-index: 1050 !important;
        }
        .modal-backdrop {
            z-index: 1040 !important;
        }
        
        /* Fix for clickable sidebar */
        .nav-pills .nav-link {
            pointer-events: all !important;
        }
        
        .sidebar * {
            pointer-events: all !important;
        }
        
        /* Fix for tab content - CRITICAL FIX */
        .tab-content > .tab-pane {
            display: none;
        }
        .tab-content > .active {
            display: block;
        }
        
        /* Ensure proper tab transitions */
        .fade {
            transition: opacity 0.15s linear;
        }
        .fade:not(.show) {
            opacity: 0;
        }
        .fade.show {
            opacity: 1;
        }
        
        /* Force proper tab display */
        .tab-pane {
            display: none !important;
        }
        .tab-pane.active {
            display: block !important;
        }
        .tab-pane.show {
            display: block !important;
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
                    <i class="fas fa-user-md me-1"></i>Dr. <?php echo $doctor; ?>
                </span>
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px;">
        <div class="welcome-header">
            <h2>Welcome, Dr. <?php echo $doctor; ?>!</h2>
            <p>Doctor Dashboard - Manage patient diagnostics, treatments, and medical care</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-dashboard-tab" data-toggle="pill" href="#v-pills-dashboard" role="tab" aria-controls="v-pills-dashboard" aria-selected="true">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" id="v-pills-patients-tab" data-toggle="pill" href="#v-pills-patients" role="tab" aria-controls="v-pills-patients" aria-selected="false">
                            <i class="fas fa-procedures me-2"></i>My Assigned Patients
                        </a>
                        <a class="nav-link" id="v-pills-diagnostics-tab" data-toggle="pill" href="#v-pills-diagnostics" role="tab" aria-controls="v-pills-diagnostics" aria-selected="false">
                            <i class="fas fa-stethoscope me-2"></i>Diagnostics
                        </a>
                        <a class="nav-link" id="v-pills-lab-requests-tab" data-toggle="pill" href="#v-pills-lab-requests" role="tab" aria-controls="v-pills-lab-requests" aria-selected="false">
                            <i class="fas fa-flask me-2"></i>Lab Requests
                        </a>
                        <a class="nav-link" id="v-pills-services-tab" data-toggle="pill" href="#v-pills-services" role="tab" aria-controls="v-pills-services" aria-selected="false">
                            <i class="fas fa-file-medical-alt me-2"></i>Service Reports
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9 col-md-8">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel" aria-labelledby="v-pills-dashboard-tab">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--primary-gradient);">
                                    <i class="fas fa-procedures fa-3x mb-3"></i>
                                    <h3>
                                        <?php
                                        $dname = $_SESSION['username'];
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE assigned_doctor='$dname' AND status != 'Discharged'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </h3>
                                    <p>My Patients</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-stethoscope fa-3x mb-3"></i>
                                    <h3>
                                        <?php
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM diagnosticstb WHERE doctor_name='$dname'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </h3>
                                    <p>Diagnostics Made</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--warning-gradient);">
                                    <i class="fas fa-flask fa-3x mb-3"></i>
                                    <h3>
                                        <?php
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM labtesttb lt JOIN admissiontb a ON lt.pid = a.pid WHERE a.assigned_doctor='$dname'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </h3>
                                    <p>Lab Tests Ordered</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--success-gradient);">
                                    <i class="fas fa-money-bill fa-3x mb-3"></i>
                                    <h3>
                                        <?php
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM patient_chargstb pc JOIN admissiontb a ON pc.pid = a.pid WHERE a.assigned_doctor='$dname'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </h3>
                                    <p>Charges Applied</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-card p-4 mb-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-user-md me-2"></i>Doctor Dashboard
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Doctor Name:</strong> Dr. <?php echo $doctor; ?></p>
                                    <p><strong>Total Patients:</strong> 
                                        <?php
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE assigned_doctor='$dname' AND status != 'Discharged'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </p>
                                    <p><strong>Diagnostics Today:</strong> 
                                        <?php
                                        $today = date('Y-m-d');
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM diagnosticstb WHERE doctor_name='$dname' AND DATE(created_date) = '$today'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Ready for Discharge:</strong> 
                                        <?php
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE assigned_doctor='$dname' AND status='Ready for Discharge'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </p>
                                    <p><strong>Lab Tests Today:</strong> 
                                        <?php
                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM labtesttb WHERE suggested_by_doctor='$dname' AND DATE(requested_date) = '$today'");
                                        $row = mysqli_fetch_assoc($result);
                                        echo $row['total'];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Patients Tab -->
                    <div class="tab-pane fade" id="v-pills-patients" role="tabpanel" aria-labelledby="v-pills-patients-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-procedures me-2"></i>My Assigned Patients
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th scope="col">Patient ID</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Age</th>
                                            <th scope="col">Gender</th>
                                            <th scope="col">Contact</th>
                                            <th scope="col">Room</th>
                                            <th scope="col">Admission Date</th>
                                            <th scope="col">Reason</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        mysqli_data_seek($patients_result, 0);
                                        while ($row = mysqli_fetch_array($patients_result)){
                                        ?>
                                        <tr>
                                            <td><?php echo $row['pid'];?></td>
                                            <td><?php echo $row['fname'] . ' ' . $row['lname'];?></td>
                                            <td><?php echo $row['age'];?></td>
                                            <td><?php echo $row['gender'];?></td>
                                            <td><?php echo $row['contact'];?></td>
                                            <td><?php echo $row['room_number'];?></td>
                                            <td><?php echo $row['admission_date'];?></td>
                                            <td><?php echo substr($row['reason'], 0, 50) . '...';?></td>
                                            <td><span class="badge badge-info badge-lg"><?php echo $row['status'];?></span></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm mb-1" data-toggle="modal" data-target="#diagnosisModal-<?php echo $row['pid']; ?>">
                                                    <i class="fas fa-stethoscope"></i> Diagnose
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#suggestLabModal-<?php echo $row['pid']; ?>">
                                                    <i class="fas fa-flask"></i> Lab Test
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm mb-1" data-toggle="modal" data-target="#addServiceModal-<?php echo $row['pid']; ?>">
                                                    <i class="fas fa-plus"></i> Add Service
                                                </button>
<?php if ($row['status'] !== 'Ready for Discharge'): ?>
                                                <button type="button" class="btn btn-warning btn-sm mb-1" onclick="markReady(<?php echo $row['pid']; ?>)">
                                                    <i class="fas fa-check"></i> Ready for Discharge
                                                </button>
<?php endif; ?>
                                                <a href="prescribe.php?pid=<?php echo $row['pid']; ?>&fname=<?php echo urlencode($row['fname']); ?>&lname=<?php echo urlencode($row['lname']); ?>" class="btn btn-success btn-sm mb-1">
                                                    <i class="fas fa-prescription-bottle-alt"></i> Prescribe Medicine
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Diagnostics Tab -->
                    <div class="tab-pane fade" id="v-pills-diagnostics" role="tabpanel" aria-labelledby="v-pills-diagnostics-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-stethoscope me-2"></i>Patient Diagnostics History
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient</th>
                                            <th>Symptoms</th>
                                            <th>Diagnosis</th>
                                            <th>Treatment Plan</th>
                                            <th>Prescription</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        mysqli_data_seek($diagnostics_result, 0);
                                        while ($diag = mysqli_fetch_array($diagnostics_result)) {
                                            $prescription_display = (!empty($diag['prescribed_medicines'])) ? substr($diag['prescribed_medicines'], 0, 30) . '...' : 'no prescription';
                                            echo '<tr>
                                                <td>' . $diag['created_date'] . '<br><small>' . $diag['created_time'] . '</small></td>
                                                <td>' . $diag['fname'] . ' ' . $diag['lname'] . '<br><small>ID: ' . $diag['pid'] . '</small></td>
                                                <td>' . substr($diag['symptoms'], 0, 50) . '...</td>
                                                <td>' . substr($diag['diagnosis'], 0, 50) . '...</td>
                                                <td>' . substr($diag['treatment_plan'], 0, 50) . '...</td>
                                                <td>' . $prescription_display . '</td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewDiagnosisModal-' . $diag['id'] . '">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this diagnosis?\');">
                                                        <input type="hidden" name="delete_diagnosis_id" value="' . $diag['id'] . '">
                                                        <button type="submit" name="delete_diagnosis" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
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
                    
                    <!-- Lab Requests Tab -->
                    <div class="tab-pane fade" id="v-pills-lab-requests" role="tabpanel" aria-labelledby="v-pills-lab-requests-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-flask me-2"></i>Lab Test Requests
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Request Date</th>
                                            <th>Patient</th>
                                            <th>Test Name</th>
                                            <th>Priority</th>
                                            <th>Scheduled Date</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        mysqli_data_seek($lab_result, 0);
                                        while ($lab = mysqli_fetch_array($lab_result)) {
                                            $status_class = '';
                                            if ($lab['status'] == 'Completed') $status_class = 'badge-success';
                                            elseif ($lab['status'] == 'Pending') $status_class = 'badge-warning';
                                            elseif ($lab['status'] == 'In Progress') $status_class = 'badge-info';
                                            else $status_class = 'badge-secondary';
                                            
                                            echo "<tr>
                                                <td>{$lab['requested_date']}<br><small>{$lab['requested_time']}</small></td>
                                                <td>{$lab['fname']} {$lab['lname']}<br><small>ID: {$lab['pid']}</small></td>
                                                <td>{$lab['test_name']}</td>
                                                <td><span class='badge badge-primary badge-lg'>{$lab['priority']}</span></td>
                                                <td>{$lab['scheduled_date']}</td>
                                                <td>₱{$lab['price']}</td>
                                                <td><span class='badge {$status_class} badge-lg'>{$lab['status']}</span></td>
                                                <td>
                                                    <button class='btn btn-info btn-sm' data-toggle='modal' data-target='#viewLabResultModal-{$lab['id']}'>
                                                        <i class='fas fa-eye'></i> View
                                                    </button>
                                                </td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Services Tab -->
                    <div class="tab-pane fade" id="v-pills-services" role="tabpanel" aria-labelledby="v-pills-services-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-file-medical-alt me-2"></i>Service Charges Management
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Service</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total Price</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $services_query = "SELECT pc.*, a.fname, a.lname, s.service_name 
                                                        FROM patient_chargstb pc 
                                                        JOIN admissiontb a ON pc.pid = a.pid 
                                                        JOIN servicestb s ON pc.service_id = s.id 
                                                        WHERE a.assigned_doctor = '$doctor' 
                                                        ORDER BY pc.added_date DESC, pc.added_time DESC";
                                        $services_result = mysqli_query($con, $services_query);
                                        while ($service = mysqli_fetch_array($services_result)) {
                                            echo "<tr>
                                                <td>{$service['fname']} {$service['lname']}<br><small>ID: {$service['pid']}</small></td>
                                                <td>{$service['service_name']}</td>
                                                <td>{$service['quantity']}</td>
                                                <td>₱{$service['unit_price']}</td>
                                                <td><strong>₱{$service['total_price']}</strong></td>
                                                <td>{$service['added_date']}<br><small>{$service['added_time']}</small></td>
                                                <td>" . (strlen($service['description']) > 50 ? substr($service['description'], 0, 50) . '...' : $service['description']) . "</td>
                                                <td>
                                                    <button class='btn btn-info btn-sm mb-1 edit-service-btn' 
                                                            data-id='{$service['id']}' 
                                                            data-pid='{$service['pid']}'
                                                            data-quantity='{$service['quantity']}'
                                                            data-description='{$service['description']}'>
                                                        <i class='fas fa-edit'></i> Edit
                                                    </button>
                                                    <button class='btn btn-danger btn-sm mb-1 delete-service-btn' 
                                                            data-id='{$service['id']}' 
                                                            data-pid='{$service['pid']}'
                                                            data-service='{$service['service_name']}'>
                                                        <i class='fas fa-trash'></i> Delete
                                                    </button>
                                                </td>
                                            </tr>";
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

    <!-- Modals for Patients -->
    <?php foreach($patients_data as $row): ?>
    <div class="modal fade" id="diagnosisModal-<?php echo $row['pid']; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form method="post" action="doctor-panel.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diagnosis for <?php echo $row['fname'] . ' ' . $row['lname']; ?> (ID: <?php echo $row['pid']; ?>)</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Symptoms</label>
                                <textarea name="symptoms" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Vital Signs</label>
                                <textarea name="vital_signs" class="form-control" rows="2" placeholder="BP, Pulse, Temperature, etc."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Physical Examination</label>
                                <textarea name="physical_examination" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Diagnosis</label>
                                <textarea name="diagnosis" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Medical History</label>
                                <textarea name="medical_history" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Diagnostic Tests Ordered</label>
                                <textarea name="diagnostic_tests_ordered" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Treatment Plan</label>
                                <textarea name="treatment_plan" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_diagnosis" class="btn btn-primary">Save Diagnosis</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="suggestLabModal-<?php echo $row['pid']; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" action="doctor-panel.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Lab Test for <?php echo $row['fname'] . ' ' . $row['lname']; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                    <div class="form-group">
                        <label>Test Name</label>
                        <select name="test_name" class="form-control" onchange="updateLabPrice(this, <?php echo $row['pid']; ?>)" required>
                            <option value="">Select Test</option>
                            <option value="Complete Blood Count (CBC)" data-price="450.00">Complete Blood Count (CBC) - ₱450.00</option>
                            <option value="Blood Sugar Test" data-price="250.00">Blood Sugar Test - ₱250.00</option>
                            <option value="Urine Analysis" data-price="300.00">Urine Analysis - ₱300.00</option>
                            <option value="Chest X-Ray" data-price="150.00">Chest X-Ray - ₱150.00</option>
                            <option value="ECG" data-price="100.00">ECG - ₱100.00</option>
                            <option value="MRI Brain" data-price="500.00">MRI Brain - ₱500.00</option>
                            <option value="CT Scan" data-price="400.00">CT Scan - ₱400.00</option>
                            <option value="Liver Function Test" data-price="600.00">Liver Function Test - ₱600.00</option>
                            <option value="Kidney Function Test" data-price="550.00">Kidney Function Test - ₱550.00</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Scheduled Date</label>
                        <input type="date" name="scheduled_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" id="lab_price_<?php echo $row['pid']; ?>" class="form-control" readonly required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="suggest_lab" class="btn btn-primary">Request Lab Test</button>
                    <button type="button" class='btn btn-secondary' data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="addServiceModal-<?php echo $row['pid']; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" action="doctor-panel.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Service Charge for <?php echo $row['fname'] . ' ' . $row['lname']; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                    <div class="form-group">
                        <label>Service</label>
                        <select name="service_id" class="form-control" onchange="updateServicePrice(this, <?php echo $row['pid']; ?>)" required>
                            <option value="">Select Service</option>
                            <?php
                            $service_query = "SELECT id, service_name, price FROM servicestb WHERE status = 'Active'";
                            $service_result = mysqli_query($con, $service_query);
                            while ($service = mysqli_fetch_array($service_result)) {
                                echo "<option value='{$service['id']}' data-price='{$service['price']}'>{$service['service_name']} - ₱{$service['price']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="service_quantity_<?php echo $row['pid']; ?>" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Price</label>
                        <input type="number" step="0.01" id="service_price_<?php echo $row['pid']; ?>" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Total Price</label>
                        <input type="number" step="0.01" id="service_total_price_<?php echo $row['pid']; ?>" name="price" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_service_charge" class="btn btn-success">Add Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Modals for Diagnostics -->
    <?php foreach($diagnostics_data as $diag): ?>
    <div class="modal fade" id="viewDiagnosisModal-<?php echo $diag['id']; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diagnosis Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Patient Information</h6>
                            <p><strong>Name:</strong> <?php echo $diag['fname'] . ' ' . $diag['lname']; ?></p>
                            <p><strong>Patient ID:</strong> <?php echo $diag['pid']; ?></p>
                            <p><strong>Date:</strong> <?php echo $diag['created_date']; ?></p>
                            <p><strong>Time:</strong> <?php echo $diag['created_time']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Medical Information</h6>
                            <p><strong>Vital Signs:</strong> <?php echo $diag['vital_signs']; ?></p>
                            <p><strong>Physical Examination:</strong> <?php echo $diag['physical_examination']; ?></p>
                            <p><strong>Medical History:</strong> <?php echo $diag['medical_history']; ?></p>
                            <p><strong>Tests Ordered:</strong> <?php echo $diag['diagnostic_tests_ordered']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Symptoms</h6>
                            <p><?php echo $diag['symptoms']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Diagnosis</h6>
                            <p><?php echo $diag['diagnosis']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Treatment Plan</h6>
                            <p><?php echo $diag['treatment_plan']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Modals for Lab Results -->
    <?php foreach($lab_data as $lab): ?>
    <div class="modal fade" id="viewLabResultModal-<?php echo $lab['id']; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lab Test Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Patient:</strong> <?php echo $lab['fname'] . ' ' . $lab['lname']; ?></p>
                            <p><strong>Patient ID:</strong> <?php echo $lab['pid']; ?></p>
                            <p><strong>Test Name:</strong> <?php echo $lab['test_name']; ?></p>
                            <p><strong>Priority:</strong> <?php echo $lab['priority']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Requested Date:</strong> <?php echo $lab['requested_date']; ?></p>
                            <p><strong>Requested Time:</strong> <?php echo $lab['requested_time']; ?></p>
                            <p><strong>Scheduled Date:</strong> <?php echo $lab['scheduled_date']; ?></p>
                            <p><strong>Status:</strong> <?php echo $lab['status']; ?></p>
                        </div>
                    </div>
                    <?php if (!empty($lab['results'])) { ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Test Result</h6>
                            <p><?php echo $lab['results']; ?></p>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (!empty($lab['lab_notes'])) { ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Lab Technician Comments</h6>
                            <p><?php echo $lab['lab_notes']; ?></p>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Service Charge Modals -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" action="doctor-panel.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Service Charge</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="charge_id" id="editChargeId">
                    <input type="hidden" name="pid" id="editPid">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="editQuantity" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_service_charge" class="btn btn-primary">Update Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteServiceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" action="doctor-panel.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="charge_id" id="deleteChargeId">
                    <input type="hidden" name="pid" id="deletePid">
                    <p>Are you sure you want to delete the service charge for <span id="deleteServiceName"></span>?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="delete_service_charge" class="btn btn-danger">Delete Service</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updateLabPrice(select, pid) {
            var price = $(select).find(':selected').data('price');
            $('#lab_price_' + pid).val(price);
        }
        
        function updateServicePrice(select, pid) {
            var price = $(select).find(':selected').data('price');
            $('#service_price_' + pid).val(price);
            updateServiceTotalPrice(pid);
        }

        function updateServiceTotalPrice(pid) {
            var quantity = parseInt($('#service_quantity_' + pid).val()) || 0;
            var unitPrice = parseFloat($('#service_price_' + pid).val()) || 0;
            var totalPrice = quantity * unitPrice;
            $('#service_total_price_' + pid).val(totalPrice.toFixed(2));
        }

        $(document).on('input', '[id^=service_quantity_]', function() {
            var pid = this.id.split('_').pop();
            updateServiceTotalPrice(pid);
        });

        function markReady(pid) {
            if (confirm("Are you sure you want to mark this patient as ready for discharge?")) {
                var form = document.createElement("form");
                form.method = "post";
                form.action = "doctor-panel.php";
                
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "pid";
                input.value = pid;
                form.appendChild(input);
                
                var submit = document.createElement("input");
                submit.type = "hidden";
                submit.name = "mark_ready";
                submit.value = "1";
                form.appendChild(submit);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        $(document).on('click', '.edit-service-btn', function() {
            var chargeId = $(this).data('id');
            var pid = $(this).data('pid');
            var quantity = $(this).data('quantity');
            var description = $(this).data('description');

            $('#editChargeId').val(chargeId);
            $('#editPid').val(pid);
            $('#editQuantity').val(quantity);
            $('#editDescription').val(description);

            $('#editServiceModal').modal('show');
        });

        $(document).on('click', '.delete-service-btn', function() {
            var chargeId = $(this).data('id');
            var pid = $(this).data('pid');
            var serviceName = $(this).data('service');

            $('#deleteChargeId').val(chargeId);
            $('#deletePid').val(pid);
            $('#deleteServiceName').text(serviceName);

            $('#deleteServiceModal').modal('show');
        });

        // Enhanced tab switching fix
        $(document).ready(function() {
            // Initialize Bootstrap tabs properly
            $('#v-pills-tab a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
                
                // Force hide all other tabs
                $('.tab-pane').removeClass('show active');
                var target = $(this).attr('href');
                $(target).addClass('show active');
            });

            // Ensure proper initial state
            $('.tab-pane').removeClass('show active');
            $('#v-pills-dashboard').addClass('show active');

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

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
</body>
</html>