<!DOCTYPE html>
<?php
session_start();
include('func1.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!isset($_SESSION['dname'])) {
    header("Location: login.php");
    exit();
}
$doctor = $_SESSION['dname'];

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

?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" crossorigin="anonymous"></script>
    
    <style>
      .btn-outline-light:hover{
        color: #25bef7;
        background-color: #f8f9fa;
        border-color: #f8f9fa;
      }
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
      button:hover{cursor:pointer;}
      #inputbtn:hover{cursor:pointer;}

      .modal {
        z-index: 1050 !important;
      }
      .modal-backdrop {
        z-index: 1040 !important;
      }
      #list-dash .container-fluid {
        position: relative;
        z-index: 1;
      }
    </style>
</head>
<body style="padding-top:50px;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Madridano Health Care Hospital </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"></a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="post" action="search.php">
                <input class="form-control mr-sm-2" type="text" placeholder="Enter contact number" aria-label="Search" name="contact">
                <input type="submit" class="btn btn-outline-light" id="inputbtn" name="search_submit" value="Search">
            </form>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top:50px;">
        <h3 style="margin-left: 40%; padding-bottom: 20px;font-family:'IBM Plex Sans', sans-serif;"> Welcome &nbsp<?php echo $_SESSION['dname'] ?>  </h3>
        <div class="row">
            <div class="col-md-4" style="max-width:18%;margin-top: 3%;">
                <div class="list-group" id="list-tab" role="tablist" style="background-color: #f8f9fa; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <a class="list-group-item list-group-item-action active d-flex align-items-center" id="list-dash-list" href="#list-dash" role="tab" aria-controls="home" data-toggle="list" style="border-radius: 8px; background-color: #007bff; color: white;">
                        <i class="fa fa-tachometer mr-2"></i> Dashboard
                    </a>
                    <a class="list-group-item list-group-item-action d-flex align-items-center" id="list-patients-list" href="#list-patients" role="tab" data-toggle="list" aria-controls="home" style="border-radius: 8px; color: #495057;">
                        <i class="fa fa-procedures mr-2"></i> My Assigned Patients
                    </a>
                    <a class="list-group-item list-group-item-action d-flex align-items-center" id="list-diagnostics-list" href="#list-diagnostics" role="tab" data-toggle="list" aria-controls="home" style="border-radius: 8px; color: #495057;">
                        <i class="fa fa-stethoscope mr-2"></i> Diagnostics
                    </a>
                    <a class="list-group-item list-group-item-action d-flex align-items-center" id="list-lab-requests-list" href="#list-lab-requests" role="tab" data-toggle="list" aria-controls="home" style="border-radius: 8px; color: #495057;">
                        <i class="fa fa-flask mr-2"></i> Lab Requests
                    </a>
                    <a class="list-group-item list-group-item-action d-flex align-items-center" id="list-services-list" href="#list-services" role="tab" data-toggle="list" aria-controls="home" style="border-radius: 8px; color: #495057;">
                        <i class="fa fa-file-medical-alt mr-2"></i> Service Reports
                    </a>
                </div><br>
            </div>
            <div class="col-md-8" style="margin-top: 3%;">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
                        <div class="container-fluid" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px;">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-0 shadow-lg" style="background: rgba(255,255,255,0.95); border-radius: 20px;">
                                        <div class="card-body text-center py-4">
                                            <h2 class="mb-2" style="color: #2c3e50; font-weight: 700;">
                                                <i class="fa fa-user-md text-primary me-3"></i>Doctor Dashboard
                                            </h2>
                                            <p class="text-muted mb-0">Welcome Dr. <?php echo $doctor; ?> - Madridano Health Care Hospital</p>
                                            <small class="text-muted">Manage patient diagnostics, treatments, and medical care</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4 mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                                        <div class="card-body text-center text-white p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-procedures fa-3x opacity-75"></i>
                                            </div>
                                            <h3 class="fw-bold mb-2">
                                                <?php
                                                $dname = $_SESSION['dname'];
                                                $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE assigned_doctor='$dname' AND status != 'Discharged'");
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['total'];
                                                ?>
                                            </h3>
                                            <p class="mb-3 opacity-90">My Patients</p>
                                            <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-patients-list').click()">
                                                <i class="fa fa-eye me-1"></i>View All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px;">
                                        <div class="card-body text-center text-white p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-stethoscope fa-3x opacity-75"></i>
                                            </div>
                                            <h3 class="fw-bold mb-2">
                                                <?php
                                                $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM diagnosticstb WHERE doctor_name='$dname'");
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['total'];
                                                ?>
                                            </h3>
                                            <p class="mb-3 opacity-90">Diagnostics Made</p>
                                            <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-diagnostics-list').click()">
                                                <i class="fa fa-plus me-1"></i>Add New
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px;">
                                        <div class="card-body text-center text-white p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-flask fa-3x opacity-75"></i>
                                            </div>
                                            <h3 class="fw-bold mb-2">
                                                <?php
                                                $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM labtesttb lt JOIN admissiontb a ON lt.pid = a.pid WHERE a.assigned_doctor='$dname'");
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['total'];
                                                ?>
                                            </h3>
                                            <p class="mb-3 opacity-90">Lab Tests Ordered</p>
                                            <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-lab-requests-list').click()">
                                                <i class='fa fa-vial me-1'></i>Order Tests
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 15px;">
                                        <div class="card-body text-center text-white p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-money-bill fa-3x opacity-75"></i>
                                            </div>
                                            <h3 class="fw-bold mb-2">
                                                <?php
                                                $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM patient_chargstb pc JOIN admissiontb a ON pc.pid = a.pid WHERE a.assigned_doctor='$dname'");
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['total'];
                                                ?>
                                            </h3>
                                            <p class="mb-3 opacity-90">Charges Applied</p>
                                            <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-services-list').click()">
                                                <i class="fa fa-chart-line me-1"></i>View Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-user-injured fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3" style="color: #2c3e50;">Patient Management</h5>
                                            <p class="text-muted mb-3">View and manage your assigned patients</p>
                                            <button class="btn btn-primary fw-bold" onclick="$('#list-patients-list').click()">
                                                <i class="fa fa-users me-1"></i>View My Patients
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-diagnoses fa-3x text-success"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3" style="color: #2c3e50;">Medical Diagnostics</h5>
                                            <p class="text-muted mb-3">Create diagnoses and treatment plans</p>
                                            <button class="btn btn-success fw-bold" onclick="$('#list-diagnostics-list').click()">
                                                <i class="fa fa-stethoscope me-1"></i>Add Diagnosis
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-chart-pie fa-3x text-info"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3" style="color: #2c3e50;">Today's Summary</h5>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h6 class="text-primary fw-bold">
                                                        <?php
                                                        $today = date('Y-m-d');
                                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM diagnosticstb WHERE doctor_name='$dname' AND DATE(created_date) = '$today'");
                                                        $row = mysqli_fetch_assoc($result);
                                                        echo $row['total'];
                                                        ?>
                                                    </h6>
                                                    <small class="text-muted">Today's Diagnoses</small>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="text-warning fw-bold">
                                                        <?php
                                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE assigned_doctor='$dname' AND status='Ready for Discharge'");
                                                        $row = mysqli_fetch_assoc($result);
                                                        echo $row['total'];
                                                        ?>
                                                    </h6>
                                                    <small class="text-muted">Ready for Discharge</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="list-patients" role="tabpanel" aria-labelledby="list-patients-list">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="thead-light">
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
                                    $con=mysqli_connect("localhost","root","","myhmsdb");
                                    global $con;
                                    $dname = $_SESSION['dname'];
                                    $query = "SELECT pid, fname, lname, age, gender, contact, room_number, admission_date, reason, status FROM admissiontb WHERE assigned_doctor='$dname' AND status != 'Discharged'";
                                    $result = mysqli_query($con,$query);
                                    while ($row = mysqli_fetch_array($result)){
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
                                        <td><span class="badge badge-info"><?php echo $row['status'];?></span></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm mb-1" data-toggle="modal" data-target="#diagnosisModal-<?php echo $row['pid']; ?>">
                                                <i class="fa fa-stethoscope"></i> Diagnose
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#suggestLabModal-<?php echo $row['pid']; ?>">
                                                <i class="fa fa-flask"></i> Lab Test
                                            </button>
                    <button type="button" class="btn btn-success btn-sm mb-1" data-toggle="modal" data-target="#addServiceModal-<?php echo $row['pid']; ?>">
                        <i class="fa fa-plus"></i> Add Service
                    </button>
<button type="button" class="btn btn-warning btn-sm mb-1" onclick="markReady(<?php echo $row['pid']; ?>)">
    <i class="fa fa-check"></i> Ready for Discharge
</button>
<a href="prescribe.php?pid=<?php echo $row['pid']; ?>&fname=<?php echo urlencode($row['fname']); ?>&lname=<?php echo urlencode($row['lname']); ?>" class="btn btn-success btn-sm mb-1">
    <i class="fa fa-prescription-bottle-alt"></i> Prescribe Medicine
</a>
<button type="button" class="btn btn-secondary btn-sm mb-1" data-toggle="modal" data-target="#viewPrescriptionsModal-<?php echo $row['pid']; ?>">
    <i class="fa fa-file-medical"></i> View Prescriptions
</button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="list-diagnostics" role="tabpanel" aria-labelledby="list-diagnostics-list">
                        <h4>Patient Diagnostics History</h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Symptoms</th>
                                        <th>Diagnosis</th>
                                        <th>Treatment Plan</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $diagnostics_query = "SELECT d.*, a.fname, a.lname FROM diagnosticstb d 
                                                        JOIN admissiontb a ON d.pid = a.pid 
                                                        WHERE d.doctor_name = '$doctor' 
                                                        ORDER BY d.created_date DESC, d.created_time DESC";
                                    $diagnostics_result = mysqli_query($con, $diagnostics_query);
                                    while ($diag = mysqli_fetch_array($diagnostics_result)) {
                                        echo "<tr>
                                            <td>{$diag['created_date']}<br><small>{$diag['created_time']}</small></td>
                                            <td>{$diag['fname']} {$diag['lname']}<br><small>ID: {$diag['pid']}</small></td>
                                            <td>" . substr($diag['symptoms'], 0, 50) . "...</td>
                                            <td>" . substr($diag['diagnosis'], 0, 50) . "...</td>
                                            <td>" . substr($diag['treatment_plan'], 0, 50) . "...</td>
                                            <td>
                                                <button class='btn btn-info btn-sm' data-toggle='modal' data-target='#viewDiagnosisModal-{$diag['id']}'>
                                                    <i class='fa fa-eye'></i> View
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="list-lab-requests" role="tabpanel" aria-labelledby="list-lab-requests-list">
                        <h4>Lab Test Requests</h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
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
                                    $lab_query = "SELECT lt.*, a.fname, a.lname FROM labtesttb lt 
                                                JOIN admissiontb a ON lt.pid = a.pid 
                                                WHERE lt.suggested_by_doctor = '$doctor' 
                                                ORDER BY lt.requested_date DESC, lt.requested_time DESC";
                                    $lab_result = mysqli_query($con, $lab_query);
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
                                            <td><span class='badge badge-primary'>{$lab['priority']}</span></td>
                                            <td>{$lab['scheduled_date']}</td>
                                            <td>₱{$lab['price']}</td>
                                            <td><span class='badge {$status_class}'>{$lab['status']}</span></td>
                                            <td>
                                                <button class='btn btn-info btn-sm' data-toggle='modal' data-target='#viewLabResultModal-{$lab['id']}'>
                                                    <i class='fa fa-eye'></i> View
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="list-services" role="tabpanel" aria-labelledby="list-services-list">
                        <h4>Service Charges Management</h4>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Patient Services</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-light">
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
                                                            <i class='fa fa-edit'></i> Edit
                                                        </button>
                                                        <button class='btn btn-danger btn-sm mb-1 delete-service-btn' 
                                                                data-id='{$service['id']}' 
                                                                data-pid='{$service['pid']}'
                                                                data-service='{$service['service_name']}'>
                                                            <i class='fa fa-trash'></i> Delete
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
    </div>
    <?php
    $con=mysqli_connect("localhost","root","","myhmsdb");
    $dname = $_SESSION['dname'];
    $query = "SELECT pid, fname, lname, age, gender, contact, room_number, admission_date, reason, status FROM admissiontb WHERE assigned_doctor='$dname' AND status != 'Discharged'";
    $result = mysqli_query($con,$query);
    while ($row = mysqli_fetch_array($result)){
    ?>
 <?php echo $row['pid']; ?>
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

 <?php echo $row['pid']; ?>
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
                            <option value="Complete Blood Count (CBC)" data-price="45.00">Complete Blood Count (CBC) - ₱450.00</option>
                            <option value="Blood Sugar Test" data-price="25.00">Blood Sugar Test - ₱250.00</option>
                            <option value="Urine Analysis" data-price="30.00">Urine Analysis - ₱300.00</option>
                            <option value="Chest X-Ray" data-price="150.00">Chest X-Ray - ₱150.00</option>
                            <option value="ECG" data-price="100.00">ECG - ₱100.00</option>
                            <option value="MRI Brain" data-price="500.00">MRI Brain - ₱500.00</option>
                            <option value="CT Scan" data-price="400.00">CT Scan - ₱400.00</option>
                            <option value="Liver Function Test" data-price="60.00">Liver Function Test - ₱600.00</option>
                            <option value="Kidney Function Test" data-price="55.00">Kidney Function Test - ₱550.00</option>
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

 <?php echo $row['pid']; ?> 
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
                                echo "<option value='{$service['id']}' data-price='{$service['price']}'>{$service['service_name']} - \${$service['price']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Price</label>
                        <input type="number" step="0.01" id="service_price_<?php echo $row['pid']; ?>" class="form-control" readonly>
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
    <?php } ?>

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

    <?php
    $diagnostics_query = "SELECT d.*, a.fname, a.lname FROM diagnosticstb d 
                        JOIN admissiontb a ON d.pid = a.pid 
                        WHERE d.doctor_name = '$doctor' 
                        ORDER BY d.created_date DESC, d.created_time DESC";
    $diagnostics_result = mysqli_query($con, $diagnostics_query);
    while ($diag = mysqli_fetch_array($diagnostics_result)) {
    ?>
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
    <?php } ?>

    <?php
    $lab_query = "SELECT lt.*, a.fname, a.lname FROM labtesttb lt 
                JOIN admissiontb a ON lt.pid = a.pid 
                WHERE lt.suggested_by_doctor = '$doctor' 
                ORDER BY lt.requested_date DESC, lt.requested_time DESC";
    $lab_result = mysqli_query($con, $lab_query);
    while ($lab = mysqli_fetch_array($lab_result)) {
    ?>
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
    <?php } ?>

    <script>
        function updateLabPrice(select, pid) {
            var price = $(select).find(':selected').data('price');
            $('#lab_price_' + pid).val(price);
        }
        
        function updateServicePrice(select, pid) {
            var price = $(select).find(':selected').data('price');
            $('#service_price_' + pid).val(price);
        }
        
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
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
</body>
</html>