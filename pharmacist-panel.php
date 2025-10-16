<?php
session_start();
include('func.php');
include('newfunc.php');
$host = "localhost";
$user = "root";
$password = "";
$db = "myhmsdb";
$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'pharmacist') {
    header("Location: index.php");
    exit();
}

$pharmacist_username = $_SESSION["username"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["dispense_medication"])) {
        $prescription_id = (int)$_POST["prescription_id"];
        $patient_id = (int)$_POST["patient_id"];
        $medicines = isset($_POST["medicines"]) && is_array($_POST["medicines"]) ? $_POST["medicines"] : [];

        mysqli_begin_transaction($conn);
        try {
            $update_pres_sql = "UPDATE prestb SET status = 'Dispensed', dispensed_by = '$pharmacist_username', dispensed_date = NOW() WHERE id = '$prescription_id'";
            if (!mysqli_query($conn, $update_pres_sql)) {
                throw new Exception('Failed to update prescription status: ' . mysqli_error($conn));
            }
            $total_cost = 0.0;
            foreach ($medicines as $medicine_id => $quantity_dispensed) {
                $medicine_id = (int)$medicine_id;
                $quantity_dispensed = (int)$quantity_dispensed;
                if ($medicine_id <= 0 || $quantity_dispensed <= 0) continue;
                $check_sql = "SELECT quantity, price FROM medicinetb WHERE id = '$medicine_id' FOR UPDATE";
                $check_res = mysqli_query($conn, $check_sql);
                if (!$check_res || mysqli_num_rows($check_res) !== 1) {
                    throw new Exception('Medicine not found for ID ' . $medicine_id);
                }
                $mrow = mysqli_fetch_assoc($check_res);
                $current_qty = (int)$mrow['quantity'];
                $price = (float)$mrow['price'];
                if ($quantity_dispensed > $current_qty) {
                    throw new Exception('Insufficient stock for medicine ID ' . $medicine_id);
                }
                $total_cost += ($price * $quantity_dispensed);
            }
            foreach ($medicines as $medicine_id => $quantity_dispensed) {
                $medicine_id = (int)$medicine_id;
                $quantity_dispensed = (int)$quantity_dispensed;
                if ($medicine_id <= 0 || $quantity_dispensed <= 0) continue;
                $current_qty_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity FROM medicinetb WHERE id = '$medicine_id'"));
                $current_qty = (int)$current_qty_row['quantity'];
                $new_quantity = $current_qty - $quantity_dispensed;
                if (!mysqli_query($conn, "UPDATE medicinetb SET quantity = '$new_quantity' WHERE id = '$medicine_id'")) {
                    throw new Exception('Failed to update stock for medicine ID ' . $medicine_id);
                }
                $log_sql = "INSERT INTO inventory_logs (medicine_id, action_type, quantity_changed, previous_quantity, new_quantity, performed_by, reason, log_date, log_time) 
                             VALUES ('$medicine_id', 'Dispensed', '-$quantity_dispensed', '$current_qty', '$new_quantity', '$pharmacist_username', 'Prescription dispensing', CURDATE(), CURTIME())";
                if (!mysqli_query($conn, $log_sql)) {
                    throw new Exception('Failed to log inventory for medicine ID ' . $medicine_id);
                }
            }
            $bill_res = mysqli_query($conn, "SELECT * FROM billtb WHERE pid = '$patient_id' LIMIT 1");
            if ($bill_res && mysqli_num_rows($bill_res) === 1) {
                $bill = mysqli_fetch_assoc($bill_res);
                $consultation = (float)$bill['consultation_fees'];
                $room = (float)($bill['room_charges'] ?? 0);
                $lab = (float)$bill['lab_fees'];
                $medicine = (float)$bill['medicine_fees'];
                $service = (float)($bill['service_charges'] ?? 0);
                $other = (float)($bill['other_charges'] ?? 0);
                $discount = (float)($bill['discount'] ?? 0);
                $new_medicine = $medicine + $total_cost;
                $subtotal = $consultation + $room + $lab + $new_medicine + $service + $other - $discount;
                $coverage_percent = 0.0;
                $ins_res = mysqli_query($conn, "SELECT coverage_percent FROM patient_insurancetb WHERE patient_id = '$patient_id' AND status = 'active' ORDER BY created_at DESC LIMIT 1");
                if ($ins_res && mysqli_num_rows($ins_res) === 1) {
                    $coverage_percent = (float)mysqli_fetch_assoc($ins_res)['coverage_percent'];
                }
                $insurance_covered = $subtotal * ($coverage_percent / 100.0);
                $patient_payable = $subtotal - $insurance_covered;
                $upd_bill_sql = "UPDATE billtb SET medicine_fees = '$new_medicine', total = '$subtotal', insurance_covered = '$insurance_covered', patient_payable = '$patient_payable' WHERE pid = '$patient_id'";
                if (!mysqli_query($conn, $upd_bill_sql)) {
                    throw new Exception('Failed to update billing: ' . mysqli_error($conn));
                }
            }
            mysqli_commit($conn);
            $_SESSION['success'] = "Prescription marked as Dispensed. Inventory and billing updated.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error'] = $e->getMessage();
        }
    }
    if (isset($_POST["add_medicine"])) {
        $medicine_name = mysqli_real_escape_string($conn, $_POST["medicine_name"]);
        $quantity = $_POST["quantity"];
        $price = $_POST["price"];
        $dosage = mysqli_real_escape_string($conn, $_POST["dosage"]);
        $medicine_type = mysqli_real_escape_string($conn, $_POST["medicine_type"]);
        
        $insert_query = "INSERT INTO medicinetb (medicine_name, quantity, price, dosage, medicine_type, added_by_nurse) 
                        VALUES ('$medicine_name', '$quantity', '$price', '$dosage', '$medicine_type', '$pharmacist_username')";        
        if (mysqli_query($conn, $insert_query)) {
            $medicine_id = mysqli_insert_id($conn);
            $log_query = "INSERT INTO inventory_logs (medicine_id, action_type, quantity_changed, previous_quantity, new_quantity, performed_by, reason, log_date, log_time) 
                         VALUES ('$medicine_id', 'Added', '$quantity', 0, '$quantity', '$pharmacist_username', 'New stock addition', CURDATE(), CURTIME())";
            mysqli_query($conn, $log_query);         
            $_SESSION['success'] = "Medicine added to inventory successfully!";
        } else {
            $_SESSION['error'] = "Error adding medicine: " . mysqli_error($conn);
        }
    }
    if (isset($_POST["update_stock"])) {
        $medicine_id = $_POST["medicine_id"];
        $new_quantity = $_POST["new_quantity"];
        $reason = mysqli_real_escape_string($conn, $_POST["reason"]);
        $current_qty_query = "SELECT quantity FROM medicinetb WHERE id = '$medicine_id'";
        $current_qty_result = mysqli_query($conn, $current_qty_query);
        $current_qty = mysqli_fetch_assoc($current_qty_result)['quantity'];
        
        $quantity_changed = $new_quantity - $current_qty;
        $update_query = "UPDATE medicinetb SET quantity = '$new_quantity' WHERE id = '$medicine_id'";       
        if (mysqli_query($conn, $update_query)) {
            $log_query = "INSERT INTO inventory_logs (medicine_id, action_type, quantity_changed, previous_quantity, new_quantity, performed_by, reason, log_date, log_time) 
                         VALUES ('$medicine_id', 'Adjusted', '$quantity_changed', '$current_qty', '$new_quantity', '$pharmacist_username', '$reason', CURDATE(), CURTIME())";
            mysqli_query($conn, $log_query);
            
            $_SESSION['success'] = "Stock updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating stock: " . mysqli_error($conn);
        }
    }
    if (isset($_POST["add_counseling"])) {
        $patient_id = $_POST["patient_id"];
        $medication_name = mysqli_real_escape_string($conn, $_POST["medication_name"]);
        $counseling_type = mysqli_real_escape_string($conn, $_POST["counseling_type"]);
        $notes = mysqli_real_escape_string($conn, $_POST["notes"]);
        $patient_understanding = mysqli_real_escape_string($conn, $_POST["patient_understanding"]);
        $follow_up_required = isset($_POST["follow_up_required"]) ? 1 : 0;
        $follow_up_date = !empty($_POST["follow_up_date"]) ? $_POST["follow_up_date"] : NULL;
        
        $insert_query = "INSERT INTO counseling_notes (pid, pharmacist_username, counseling_date, counseling_time, medication_name, counseling_type, notes, patient_understanding, follow_up_required, follow_up_date) 
                        VALUES ('$patient_id', '$pharmacist_username', CURDATE(), CURTIME(), '$medication_name', '$counseling_type', '$notes', '$patient_understanding', '$follow_up_required', '$follow_up_date')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = "Counseling notes added successfully!";
        } else {
            $_SESSION['error'] = "Error adding counseling notes: " . mysqli_error($conn);
        }
    }
    if (isset($_POST["report_adverse_reaction"])) {
        $patient_id = $_POST["patient_id"];
        $medication_name = mysqli_real_escape_string($conn, $_POST["medication_name"]);
        $severity = mysqli_real_escape_string($conn, $_POST["severity"]);
        $symptoms = mysqli_real_escape_string($conn, $_POST["symptoms"]);
        $action_taken = mysqli_real_escape_string($conn, $_POST["action_taken"]);
        $reported_to_doctor = isset($_POST["reported_to_doctor"]) ? 1 : 0;
        $doctor_notified = mysqli_real_escape_string($conn, $_POST["doctor_notified"]);
        
        $insert_query = "INSERT INTO adverse_reactions (pid, pharmacist_username, medication_name, reaction_date, reaction_time, severity, symptoms, action_taken, reported_to_doctor, doctor_notified) 
                        VALUES ('$patient_id', '$pharmacist_username', '$medication_name', CURDATE(), CURTIME(), '$severity', '$symptoms', '$action_taken', '$reported_to_doctor', '$doctor_notified')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = "Adverse reaction reported successfully!";
        } else {
            $_SESSION['error'] = "Error reporting adverse reaction: " . mysqli_error($conn);
        }
    }
}
$total_prescriptions_query = "SELECT COUNT(*) as total FROM prestb";
$total_prescriptions_result = mysqli_query($conn, $total_prescriptions_query);
$total_prescriptions = mysqli_fetch_assoc($total_prescriptions_result)['total'];
$pending_prescriptions_query = "SELECT COUNT(*) as pending FROM prestb WHERE status = 'Pending'";
$pending_prescriptions_result = mysqli_query($conn, $pending_prescriptions_query);
$pending_prescriptions = mysqli_fetch_assoc($pending_prescriptions_result)['pending'];
$total_medicines_query = "SELECT COUNT(*) as total FROM medicinetb";
$total_medicines_result = mysqli_query($conn, $total_medicines_query);
$total_medicines = mysqli_fetch_assoc($total_medicines_result)['total'];
$low_stock_query = "SELECT COUNT(*) as low_stock FROM medicinetb WHERE quantity < 20";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$low_stock_medicines = mysqli_fetch_assoc($low_stock_result)['low_stock'];
$today_prescriptions_query = "SELECT COUNT(*) as today FROM prestb WHERE DATE(created_at) = CURDATE()";
$today_prescriptions_result = mysqli_query($conn, $today_prescriptions_query);
$today_prescriptions = mysqli_fetch_assoc($today_prescriptions_result)['today'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Panel - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007bff;
            --light-blue: #e3f2fd;
            --dark-blue: #0056b3;
            --background-blue: #f8f9fa;
            --card-background: #ffffff;
            --text-dark: #212529;
            --text-muted: #6c757d;
        }
        body {
            background-color: var(--background-blue);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
        }
        .sidebar {
            background-color: var(--card-background);
            color: var(--text-dark);
            height: 100vh;
            position: fixed;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .sidebar h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 10px;
        }
        .sidebar .nav-link, .sidebar a.nav-link {
            color: var(--text-dark);
            text-decoration: none;
            display: block;
            padding: 12px 14px;
            border-bottom: none;
            border-radius: 8px;
            margin-bottom: 6px;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            background-color: var(--light-blue);
            color: var(--primary-blue) !important;
        }
        .sidebar .nav-link.active {
            background-color: var(--primary-blue);
            color: #fff !important;
            border: none;
        }
        .main-content {
            margin-left: 280px;
            padding: 30px 24px;
        }
        .glass-card, .card {
            background-color: var(--card-background);
            border: 1px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .nav-tabs .nav-link {
            color: var(--text-dark);
        }
        .nav-tabs .nav-link.active {
            background-color: var(--primary-blue);
            color: #fff;
            border: none;
        }
        .table-responsive {
            max-height: 500px;
        }
        .card-header h5, h3, h2 {
            margin: 0;
        }
        .badge.bg-primary {
            background-color: var(--primary-blue) !important;
        }
    </style>
    <style>
        /* Professional layout refinements */
        .sidebar {
            width: 260px;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1030;
        }
        .main-content { margin-left: 280px; }
        .card { border-color: #e5e7eb; }
        .card-header { background: #fff; border-bottom: 1px solid #eef0f3; }
        .card-header h5 { font-weight: 600; color: var(--text-dark); }

        /* Metric cards */
        .stat-card .card-body { padding: 20px 20px; }
        .stat-card h4 { font-size: 2rem; font-weight: 700; margin: 0; }
        .stat-card p { margin: 0; font-weight: 500; opacity: .95; }
        .stat-card i { background: rgba(255,255,255,.25); border-radius: 50%; width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; }
        .card.stat-card.bg-primary { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; border: none; }
        .card.stat-card.bg-warning { background: linear-gradient(135deg, #FFD86F 0%, #FC6262 100%) !important; border: none; }
        .card.stat-card.bg-success { background: linear-gradient(135deg, #42E695 0%, #3BB2B8 100%) !important; border: none; }
        .card.stat-card.bg-danger { background: linear-gradient(135deg, #ff6a88 0%, #ff99ac 100%) !important; border: none; }

        /* Tables and lists */
        .list-group-item { border: 1px solid #eef0f3; border-radius: 8px; margin-bottom: 8px; }
        .list-group-item small { color: var(--text-muted); }
        .table thead th { font-weight: 600; color: #334155; border-bottom: 1px solid #e9ecef; }

        /* Tabs */
        .nav-tabs { border-bottom: 1px solid #e9ecef; }
        .nav-tabs .nav-link { border: none; }
        .nav-tabs .nav-link.active { border-radius: 8px; }

        /* Section titles */
        h3.mb-4 { font-weight: 700; letter-spacing: .2px; }

        /* Responsive layout */
        @media (max-width: 992px) {
            .sidebar { position: static; width: 100%; height: auto; border-radius: 0; }
            .main-content { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h3 class="text-center py-3">Pharmacist Panel</h3>
                <div class="nav flex-column">
                    <a href="#dashboard" class="nav-link active" data-bs-toggle="tab">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="#prescriptions" class="nav-link" data-bs-toggle="tab">
                        <i class="fas fa-prescription me-2"></i>Prescriptions
                    </a>
                    <a href="#inventory" class="nav-link" data-bs-toggle="tab">
                        <i class="fas fa-pills me-2"></i>Inventory
                    </a>
                    <a href="#counseling" class="nav-link" data-bs-toggle="tab">
                        <i class="fas fa-comments me-2"></i>Patient Counseling
                    </a>
                    <a href="#adverse-reactions" class="nav-link" data-bs-toggle="tab">
                        <i class="fas fa-exclamation-triangle me-2"></i>Adverse Reactions
                    </a>
                    <a href="#reports" class="nav-link" data-bs-toggle="tab">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Welcome, <?php echo $pharmacist_username; ?></h2>
                    <span class="badge bg-primary">Pharmacist</span>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <h3 class="mb-4">Dashboard Overview</h3>
                        
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $total_prescriptions; ?></h4>
                                                <p>Total Prescriptions</p>
                                            </div>
                                            <i class="fas fa-prescription fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $pending_prescriptions; ?></h4>
                                                <p>Pending Prescriptions</p>
                                            </div>
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $total_medicines; ?></h4>
                                                <p>Medicines in Stock</p>
                                            </div>
                                            <i class="fas fa-pills fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card bg-danger text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $low_stock_medicines; ?></h4>
                                                <p>Low Stock Items</p>
                                            </div>
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Recent Prescriptions</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $recent_prescriptions_query = "SELECT p.*, a.fname, a.lname FROM prestb p 
                                                                      JOIN admissiontb a ON p.pid = a.pid 
                                                                      ORDER BY p.created_at DESC LIMIT 5";
                                        $recent_prescriptions_result = mysqli_query($conn, $recent_prescriptions_query);
                                        ?>
                                        <div class="list-group">
                                            <?php while ($row = mysqli_fetch_assoc($recent_prescriptions_result)): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1"><?php echo $row['fname'] . ' ' . $row['lname']; ?></h6>
                                                        <small><?php echo date('M j, Y', strtotime($row['created_at'])); ?></small>
                                                    </div>
                                                    <p class="mb-1"><?php echo substr($row['prescribed_medicines'], 0, 50) . '...'; ?></p>
                                                    <small class="text-muted">Dr. <?php echo $row['doctor']; ?></small>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Low Stock Alert</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $low_stock_list_query = "SELECT * FROM medicinetb WHERE quantity < 20 ORDER BY quantity ASC LIMIT 5";
                                        $low_stock_list_result = mysqli_query($conn, $low_stock_list_query);
                                        ?>
                                        <div class="list-group">
                                            <?php while ($row = mysqli_fetch_assoc($low_stock_list_result)): ?>
                                                <div class="list-group-item list-group-item-warning">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1"><?php echo $row['medicine_name']; ?></h6>
                                                        <span class="badge bg-danger"><?php echo $row['quantity']; ?> left</span>
                                                    </div>
                                                    <small class="text-muted">Reorder immediately</small>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prescriptions Tab -->
                    <div class="tab-pane fade" id="prescriptions">
                        <h3 class="mb-4">Manage Prescriptions</h3>
                        
                        <ul class="nav nav-tabs" id="prescriptionTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="dispensed-tab" data-bs-toggle="tab" data-bs-target="#dispensed" type="button">Dispensed</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <!-- Pending Prescriptions -->
                            <div class="tab-pane fade show active" id="pending">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Prescription ID</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Medicines</th>
                                                <th>Dosage</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $pending_query = "SELECT p.*, a.fname, a.lname FROM prestb p 
                                                            JOIN admissiontb a ON p.pid = a.pid 
                                                            WHERE p.status = 'Pending' 
                                                            ORDER BY p.created_at DESC";
                                            $pending_result = mysqli_query($conn, $pending_query);
                                            
                                            while ($row = mysqli_fetch_assoc($pending_result)):
                                            ?>
                                                <tr>
                                                    <td>#<?php echo $row['id']; ?></td>
                                                    <td><?php echo $row['fname'] . ' ' . $row['lname']; ?></td>
                                                    <td>Dr. <?php echo $row['doctor']; ?></td>
                                                    <td><?php echo $row['prescribed_medicines']; ?></td>
                                                    <td><?php echo $row['dosage']; ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dispenseModal<?php echo $row['id']; ?>">
                                                            Dispense
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Dispense Modal -->
                                                <div class="modal fade" id="dispenseModal<?php echo $row['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Dispense Medication</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="prescription_id" value="<?php echo $row['id']; ?>">
                                                                    <input type="hidden" name="patient_id" value="<?php echo $row['pid']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Patient:</label>
                                                                        <input type="text" class="form-control" value="<?php echo $row['fname'] . ' ' . $row['lname']; ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Prescribed Medicines:</label>
                                                                        <textarea class="form-control" rows="3" readonly><?php echo $row['prescribed_medicines']; ?></textarea>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Dosage Instructions:</label>
                                                                        <textarea class="form-control" rows="2" readonly><?php echo $row['dosage']; ?></textarea>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Dispense Quantities</label>
                                                                        <div class="border rounded p-2" style="max-height: 240px; overflow-y: auto;">
                                                                            <?php
                                                                            $names = array_filter(array_map('trim', explode(',', $row['prescribed_medicines'])));
                                                                            if (empty($names)) {
                                                                                echo '<div class="text-muted">No structured medicines found in prescription.</div>';
                                                                            }
                                                                            foreach ($names as $medName) {
                                                                                $esc = mysqli_real_escape_string($conn, $medName);
                                                                                $mres = mysqli_query($conn, "SELECT id, medicine_name, quantity, price FROM medicinetb WHERE medicine_name = '$esc' LIMIT 1");
                                                                                if ($mres && mysqli_num_rows($mres) === 1) {
                                                                                    $m = mysqli_fetch_assoc($mres);
                                                                                    echo '<div class="d-flex align-items-center justify-content-between mb-2">'
                                                                                        . '<div>'
                                                                                            . '<strong>' . htmlspecialchars($m['medicine_name']) . '</strong>'
                                                                                            . '<div class="small text-muted">Stock: ' . (int)$m['quantity'] . ' | ₱' . number_format((float)$m['price'], 2) . '</div>'
                                                                                          . '</div>'
                                                                                          . '<div style="width: 140px;">'
                                                                                            . '<input type="number" class="form-control form-control-sm" name="medicines[' . (int)$m['id'] . ']" min="0" max="' . (int)$m['quantity'] . '" value="' . (int)min(1, (int)$m['quantity']) . '">' 
                                                                                          . '</div>'
                                                                                      . '</div>';
                                                                                } else {
                                                                                    echo '<div class="d-flex align-items-center justify-content-between mb-2">'
                                                                                        . '<div>'
                                                                                            . '<strong>' . htmlspecialchars($medName) . '</strong>'
                                                                                            . '<div class="small text-danger">Not found in inventory</div>'
                                                                                          . '</div>'
                                                                                          . '<div style="width: 140px;">'
                                                                                            . '<input type="number" class="form-control form-control-sm" value="0" disabled>'
                                                                                          . '</div>'
                                                                                      . '</div>';
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                        <small class="text-muted">Adjust quantities as prepared for this dispense.</small>
                                                                    </div>

                                                                    <div class="alert alert-info">
                                                                        <i class="fas fa-info-circle"></i> Verify prescription details before dispensing.
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="dispense_medication" class="btn btn-success">Confirm Dispense</button>
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
                            
                            <!-- Dispensed Prescriptions -->
                            <div class="tab-pane fade" id="dispensed">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Prescription ID</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Medicines</th>
                                                <th>Dispensed By</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $dispensed_query = "SELECT p.*, a.fname, a.lname FROM prestb p 
                                                              JOIN admissiontb a ON p.pid = a.pid 
                                                              WHERE p.status = 'Dispensed' 
                                                              ORDER BY p.dispensed_date DESC";
                                            $dispensed_result = mysqli_query($conn, $dispensed_query);
                                            
                                            while ($row = mysqli_fetch_assoc($dispensed_result)):
                                            ?>
                                                <tr>
                                                    <td>#<?php echo $row['id']; ?></td>
                                                    <td><?php echo $row['fname'] . ' ' . $row['lname']; ?></td>
                                                    <td>Dr. <?php echo $row['doctor']; ?></td>
                                                    <td><?php echo $row['prescribed_medicines']; ?></td>
                                                    <td><?php echo $row['dispensed_by']; ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($row['dispensed_date'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Tab -->
                    <div class="tab-pane fade" id="inventory">
                        <h3 class="mb-4">Medicine Inventory</h3>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Add New Medicine</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Medicine Name</label>
                                                    <input type="text" class="form-control" name="medicine_name" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" name="quantity" required min="1">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Price</label>
                                                    <input type="number" step="0.01" class="form-control" name="price" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Medicine Type</label>
                                                    <select class="form-control" name="medicine_type" required>
                                                        <option value="oral">Oral</option>
                                                        <option value="injection">Injection</option>
                                                        <option value="topical">Topical</option>
                                                        <option value="inhalation">Inhalation</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Dosage Information</label>
                                                <textarea class="form-control" name="dosage" rows="2"></textarea>
                                            </div>
                                            <button type="submit" name="add_medicine" class="btn btn-primary">Add Medicine</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Update Stock</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Select Medicine</label>
                                                <select class="form-control" name="medicine_id" required>
                                                    <option value="">Choose medicine...</option>
                                                    <?php
                                                    $medicines_query = "SELECT * FROM medicinetb ORDER BY medicine_name";
                                                    $medicines_result = mysqli_query($conn, $medicines_query);
                                                    while ($medicine = mysqli_fetch_assoc($medicines_result)):
                                                    ?>
                                                        <option value="<?php echo $medicine['id']; ?>">
                                                            <?php echo $medicine['medicine_name']; ?> (Current: <?php echo $medicine['quantity']; ?>)
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">New Quantity</label>
                                                <input type="number" class="form-control" name="new_quantity" required min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Update</label>
                                                <input type="text" class="form-control" name="reason" required>
                                            </div>
                                            <button type="submit" name="update_stock" class="btn btn-warning">Update Stock</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Medicine List -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Current Inventory</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Medicine Name</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Dosage</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $inventory_query = "SELECT * FROM medicinetb ORDER BY medicine_name";
                                            $inventory_result = mysqli_query($conn, $inventory_query);
                                            
                                            while ($medicine = mysqli_fetch_assoc($inventory_result)):
                                                $status_class = $medicine['quantity'] == 0 ? 'danger' : ($medicine['quantity'] < 20 ? 'warning' : 'success');
                                                $status_text = $medicine['quantity'] == 0 ? 'Out of Stock' : ($medicine['quantity'] < 20 ? 'Low Stock' : 'In Stock');
                                            ?>
                                                <tr>
                                                    <td><?php echo $medicine['medicine_name']; ?></td>
                                                    <td><?php echo ucfirst($medicine['medicine_type']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <?php echo $medicine['quantity']; ?>
                                                        </span>
                                                    </td>
                                                    <td>₱<?php echo number_format($medicine['price'], 2); ?></td>
                                                    <td><?php echo $medicine['dosage']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <?php echo $status_text; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Counseling Tab -->
                    <div class="tab-pane fade" id="counseling">
                        <h3 class="mb-4">Patient Counseling</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add Counseling Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Select Patient</label>
                                                <select class="form-control" name="patient_id" required>
                                                    <option value="">Choose patient...</option>
                                                    <?php
                                                    $patients_query = "SELECT pid, fname, lname FROM admissiontb WHERE status = 'Admitted'";
                                                    $patients_result = mysqli_query($conn, $patients_query);
                                                    while ($patient = mysqli_fetch_assoc($patients_result)):
                                                    ?>
                                                        <option value="<?php echo $patient['pid']; ?>">
                                                            <?php echo $patient['fname'] . ' ' . $patient['lname']; ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Medication Name</label>
                                                <input type="text" class="form-control" name="medication_name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Counseling Type</label>
                                                <select class="form-control" name="counseling_type" required>
                                                    <option value="New Medication">New Medication</option>
                                                    <option value="Refill Counseling">Refill Counseling</option>
                                                    <option value="Side Effects">Side Effects Management</option>
                                                    <option value="Adherence">Medication Adherence</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control" name="notes" rows="4" required></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Patient Understanding</label>
                                                    <select class="form-control" name="patient_understanding">
                                                        <option value="Not Assessed">Not Assessed</option>
                                                        <option value="Poor">Poor</option>
                                                        <option value="Fair">Fair</option>
                                                        <option value="Good">Good</option>
                                                        <option value="Excellent">Excellent</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="follow_up_required" id="followUpCheck">
                                                        <label class="form-check-label" for="followUpCheck">Follow-up Required</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3" id="followUpDate" style="display: none;">
                                                <label class="form-label">Follow-up Date</label>
                                                <input type="date" class="form-control" name="follow_up_date">
                                            </div>
                                            <button type="submit" name="add_counseling" class="btn btn-primary">Save Counseling Notes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Recent Counseling Sessions</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $counseling_query = "SELECT cn.*, a.fname, a.lname FROM counseling_notes cn 
                                                           JOIN admissiontb a ON cn.pid = a.pid 
                                                           ORDER BY cn.created_at DESC LIMIT 5";
                                        $counseling_result = mysqli_query($conn, $counseling_query);
                                        ?>
                                        <div class="list-group">
                                            <?php while ($session = mysqli_fetch_assoc($counseling_result)): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1"><?php echo $session['fname'] . ' ' . $session['lname']; ?></h6>
                                                        <small><?php echo date('M j, Y', strtotime($session['counseling_date'])); ?></small>
                                                    </div>
                                                    <p class="mb-1"><strong>Medication:</strong> <?php echo $session['medication_name']; ?></p>
                                                    <p class="mb-1"><strong>Type:</strong> <?php echo $session['counseling_type']; ?></p>
                                                    <p class="mb-1"><?php echo substr($session['notes'], 0, 100) . '...'; ?></p>
                                                    <small class="text-muted">Understanding: <?php echo $session['patient_understanding']; ?></small>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adverse Reactions Tab -->
                    <div class="tab-pane fade" id="adverse-reactions">
                        <h3 class="mb-4">Adverse Drug Reactions</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Report Adverse Reaction</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Select Patient</label>
                                                <select class="form-control" name="patient_id" required>
                                                    <option value="">Choose patient...</option>
                                                    <?php
                                                    $patients_query = "SELECT pid, fname, lname FROM admissiontb WHERE status = 'Admitted'";
                                                    $patients_result = mysqli_query($conn, $patients_query);
                                                    while ($patient = mysqli_fetch_assoc($patients_result)):
                                                    ?>
                                                        <option value="<?php echo $patient['pid']; ?>">
                                                            <?php echo $patient['fname'] . ' ' . $patient['lname']; ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Medication Name</label>
                                                <input type="text" class="form-control" name="medication_name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Severity</label>
                                                <select class="form-control" name="severity" required>
                                                    <option value="Mild">Mild</option>
                                                    <option value="Moderate">Moderate</option>
                                                    <option value="Severe">Severe</option>
                                                    <option value="Life-threatening">Life-threatening</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Symptoms</label>
                                                <textarea class="form-control" name="symptoms" rows="3" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Action Taken</label>
                                                <textarea class="form-control" name="action_taken" rows="3" required></textarea>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" name="reported_to_doctor" id="reportCheck">
                                                <label class="form-check-label" for="reportCheck">Reported to Doctor</label>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Doctor Notified</label>
                                                <input type="text" class="form-control" name="doctor_notified">
                                            </div>
                                            <button type="submit" name="report_adverse_reaction" class="btn btn-danger">Report Reaction</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Recent Adverse Reactions</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $reactions_query = "SELECT ar.*, a.fname, a.lname FROM adverse_reactions ar 
                                                          JOIN admissiontb a ON ar.pid = a.pid 
                                                          ORDER BY ar.created_at DESC LIMIT 5";
                                        $reactions_result = mysqli_query($conn, $reactions_query);
                                        ?>
                                        <div class="list-group">
                                            <?php while ($reaction = mysqli_fetch_assoc($reactions_result)): 
                                                $severity_class = $reaction['severity'] == 'Severe' || $reaction['severity'] == 'Life-threatening' ? 'danger' : 'warning';
                                            ?>
                                                <div class="list-group-item list-group-item-<?php echo $severity_class; ?>">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1"><?php echo $reaction['fname'] . ' ' . $reaction['lname']; ?></h6>
                                                        <span class="badge bg-<?php echo $severity_class; ?>"><?php echo $reaction['severity']; ?></span>
                                                    </div>
                                                    <p class="mb-1"><strong>Medication:</strong> <?php echo $reaction['medication_name']; ?></p>
                                                    <p class="mb-1"><strong>Symptoms:</strong> <?php echo substr($reaction['symptoms'], 0, 100) . '...'; ?></p>
                                                    <small class="text-muted">Reported on: <?php echo date('M j, Y g:i A', strtotime($reaction['reaction_date'] . ' ' . $reaction['reaction_time'])); ?></small>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Tab -->
                    <div class="tab-pane fade" id="reports">
                        <h3 class="mb-4">Pharmacy Reports</h3>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                        <h5>Monthly Dispensing</h5>
                                        <p class="text-muted">View monthly medication dispensing trends</p>
                                        <button class="btn btn-outline-primary">Generate Report</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-boxes fa-3x text-warning mb-3"></i>
                                        <h5>Inventory Report</h5>
                                        <p class="text-muted">Complete inventory status and alerts</p>
                                        <button class="btn btn-outline-warning">Generate Report</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                        <h5>Adverse Reactions</h5>
                                        <p class="text-muted">Adverse drug reaction summary</p>
                                        <button class="btn btn-outline-danger">Generate Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Statistics -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Pharmacy Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h3 class="text-primary"><?php echo $today_prescriptions; ?></h3>
                                        <p class="text-muted">Today's Prescriptions</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="text-success"><?php echo $total_medicines; ?></h3>
                                        <p class="text-muted">Total Medicines</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 class="text-warning"><?php echo $low_stock_medicines; ?></h3>
                                        <p class="text-muted">Low Stock Items</p>
                                    </div>
                                    <div class="col-md-3">
                                        <?php
                                        $counseling_count_query = "SELECT COUNT(*) as total FROM counseling_notes WHERE DATE(created_at) = CURDATE()";
                                        $counseling_count_result = mysqli_query($conn, $counseling_count_query);
                                        $today_counseling = mysqli_fetch_assoc($counseling_count_result)['total'];
                                        ?>
                                        <h3 class="text-info"><?php echo $today_counseling; ?></h3>
                                        <p class="text-muted">Today's Counseling</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('followUpCheck').addEventListener('change', function() {
            document.getElementById('followUpDate').style.display = this.checked ? 'block' : 'none';
        });
        document.addEventListener('DOMContentLoaded', function() {
            const triggerTabList = [].slice.call(document.querySelectorAll('#myTab a'))
            triggerTabList.forEach(function (triggerEl) {
                const tabTrigger = new bootstrap.Tab(triggerEl)
                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault()
                    tabTrigger.show()
                })
            })
        });
        setInterval(function() {
            const activeTab = document.querySelector('.tab-pane.active');
            if (activeTab && activeTab.id === 'dashboard') {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>