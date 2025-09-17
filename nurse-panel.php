<!DOCTYPE html>
<?php
session_start();
include('func.php');
include('newfunc.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'nurse') {
    header("Location: index.php");
    exit();
}

$nurse = $_SESSION['username'];

mysqli_query($con, $create_rounds_table);

if (isset($_POST['register_admit_patient'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $assigned_doctor = mysqli_real_escape_string($con, $_POST['assigned_doctor']);
    $room_number = mysqli_real_escape_string($con, $_POST['room_number']);
    $age = (int)$_POST['age'];
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $reason = mysqli_real_escape_string($con, $_POST['reason']);
    $admission_date = date("Y-m-d");
    $admission_time = date("H:i:s");

    if ($password !== $cpassword) {
        echo "<script>alert('Passwords do not match! Please try again.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

$reg_query = "INSERT INTO admissiontb (fname, lname, gender, email, contact, password, admission_date, assigned_doctor, room_number, age, address, reason, status) VALUES ('$fname', '$lname', '$gender', '$email', '$contact', '$hashed_password', '$admission_date', '$assigned_doctor', '$room_number', $age, '$address', '$reason', 'Admitted')";
        if (mysqli_query($con, $reg_query)) {
            $pid = mysqli_insert_id($con);

            $doctor_fee_query = "SELECT consultation_fee FROM doctortb WHERE username='$assigned_doctor'";
            $doctor_fee_result = mysqli_query($con, $doctor_fee_query);
            $doctor_fee_row = mysqli_fetch_array($doctor_fee_result);
            $consultation_fee = $doctor_fee_row['consultation_fee'];

            $room_charge = 0;
            if (strpos($room_number, '101') !== false || strpos($room_number, '102') !== false || strpos($room_number, '103') !== false) {
                $room_charge = 250; 
            } elseif (strpos($room_number, '201') !== false || strpos($room_number, '202') !== false || strpos($room_number, '203') !== false) {
                $room_charge = 550; 
            } elseif (strpos($room_number, '301') !== false || strpos($room_number, '302') !== false) {
                $room_charge = 500; 
            } elseif (strpos($room_number, '401') !== false || strpos($room_number, '402') !== false) {
                $room_charge = 400; 
            }

            $total = $consultation_fee + $room_charge;
            $bill_query = "INSERT INTO billtb (pid, consultation_fees, room_charges, lab_fees, medicine_fees, service_charges, total, status) VALUES ('$pid', '$consultation_fee', '$room_charge', 0, 0, 0, '$total', 'Unpaid')";
            mysqli_query($con, $bill_query);

            echo "<script>alert('Patient registered and admitted successfully!\\nPatient ID: $pid\\nRoom: $room_number\\nAssigned Doctor: $assigned_doctor\\nPatient can now login with email: $email');</script>";
        } else {
            echo "<script>alert('Error registering patient: " . mysqli_error($con) . "');</script>";
        }
    }
}

if (isset($_POST['add_medicine'])) {
    $medicine_name = mysqli_real_escape_string($con, $_POST['medicine_name']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    $query = "INSERT INTO medicinetb (medicine_name, quantity, added_by_nurse, price) VALUES ('$medicine_name', $quantity, '$nurse', $price)";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Medicine added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding medicine: " . mysqli_error($con) . "');</script>";
    }
}
    if (isset($_POST['add_sample_medicines'])) {
    $sample_medicines = array(
        array("Paracetamol 500mg", 100, 5.50),
        array("Ibuprofen 400mg", 80, 8.75),
        array("Amoxicillin 500mg", 50, 12.30),
        array("Aspirin 100mg", 120, 6.25),
        array("Loratadine 10mg", 60, 9.80),
        array("Omeprazole 20mg", 70, 15.40),
        array("Metformin 500mg", 90, 7.90),
        array("Atorvastatin 20mg", 40, 18.20),
        array("Salbutamol Inhaler", 30, 22.50),
        array("Insulin Glargine", 25, 45.00),
        array("Amlodipine 5mg", 65, 10.25),
        array("Losartan 50mg", 55, 11.75),
        array("Cetirizine 10mg", 85, 7.30),
        array("Diazepam 5mg", 20, 14.50),
        array("Ciprofloxacin 500mg", 45, 16.80),
        array("Simvastatin 20mg", 60, 13.40),
        array("Metoprolol 50mg", 75, 9.25),
        array("Warfarin 5mg", 35, 8.90),
        array("Levothyroxine 50mcg", 40, 12.60),
        array("Albuterol Inhaler", 28, 24.30)
    );

    $success_count = 0;
    $error_count = 0;
    
    foreach ($sample_medicines as $medicine) {
        $name = mysqli_real_escape_string($con, $medicine[0]);
        $qty = (int)$medicine[1];
        $price_val = (float)$medicine[2];
        
        $query = "INSERT INTO medicinetb (medicine_name, quantity, added_by_nurse, price) 
                  VALUES ('$name', $qty, '$nurse', $price_val)";
        
        if (mysqli_query($con, $query)) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
    
    echo "<script>alert('Added $success_count sample medicines. $error_count failed.');</script>";
}

if (isset($_GET['delete_medicine'])) {
    $id = (int)$_GET['delete_medicine'];

    $query = "DELETE FROM medicinetb WHERE id=$id";
    if (mysqli_query($con, $query)) {
        echo "<script>
            if(confirm('Medicine deleted successfully! Click OK to refresh.')) {
                window.location.href = 'nurse-panel.php';
            }
        </script>";
    } else {
        echo "<script>alert('Error deleting medicine!');</script>";
    }
}

if (isset($_POST['schedule_round'])) {
    $pid = (int)$_POST['patient_id'];
    $round_date = mysqli_real_escape_string($con, $_POST['round_date']);
    $round_time = mysqli_real_escape_string($con, $_POST['round_time']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);

    $query = "INSERT INTO patient_roundstb (pid, nurse_username, round_date, round_time, notes) VALUES ($pid, '$nurse', '$round_date', '$round_time', '$notes')";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Patient round scheduled successfully!');</script>";
    } else {
        echo "<script>alert('Error scheduling round: " . mysqli_error($con) . "');</script>";
    }
}
    if (isset($_POST['update_round'])) {
    $round_id = (int)$_POST['round_id'];
    $vital_signs = mysqli_real_escape_string($con, $_POST['vital_signs']);
    $notes = mysqli_real_escape_string($con, $_POST['update_notes']);
    
    $query = "UPDATE patient_roundstb SET vital_signs = '$vital_signs', notes = CONCAT(notes, '\\nUpdate: ', '$notes'), status = 'Completed' WHERE id = $round_id";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Round updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating round: " . mysqli_error($con) . "');</script>";
    }
}
    if (isset($_GET['delete_round'])) {
    $id = (int)$_GET['delete_round'];
    $query = "DELETE FROM patient_roundstb WHERE id=$id";
    if (mysqli_query($con, $query)) {
        echo "<script>
            if(confirm('Round deleted successfully! Click OK to refresh.')) {
                window.location.href = 'nurse-panel.php';
            }
        </script>";
    } else {
        echo "<script>alert('Error deleting round!');</script>";
    }
}

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" crossorigin="anonymous"></script>
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
        button:hover{cursor:pointer;}
        #inputbtn:hover{cursor:pointer;}
        .round-status-scheduled { background-color: #fff3cd; }
        .round-status-completed { background-color: #d4edda; }
        .round-status-missed { background-color: #f8d7da; }
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
            </ul>
        </div>
    </nav>

<div class="container-fluid" style="margin-top:50px;">
    <h3 style = "margin-left: 40%;  padding-bottom: 20px; font-family: 'IBM Plex Sans', sans-serif;"> Welcome Nurse &nbsp<?php echo $nurse ?> </h3>
    <div class="row">
        <div class="col-md-4" style="max-width:25%; margin-top: 3%">
            <div class="list-group" id="list-tab" role="tablist">
                <a class="list-group-item list-group-item-action active" id="list-dash-list" data-toggle="list" href="#list-dash" role="tab" aria-controls="home">Dashboard</a>
                <a class="list-group-item list-group-item-action" id="list-admit-list" data-toggle="list" href="#list-admit" role="tab" aria-controls="home">Register Patient</a>
                <a class="list-group-item list-group-item-action" href="#list-patients" id="list-pat-list" role="tab" data-toggle="list" aria-controls="home">Patient List</a>
                <a class="list-group-item list-group-item-action" href="#list-meds" id="list-meds-list" role="tab" data-toggle="list" aria-controls="home">Manage Medicines</a>
                <a class="list-group-item list-group-item-action" href="#list-rounds" id="list-rounds-list" role="tab" data-toggle="list" aria-controls="home">Schedule Patient Rounds</a>
            </div><br>
        </div>
        <div class="col-md-8" style="margin-top: 3%;">
            <div class="tab-content" id="nav-tabContent" style="width: 950px;">
                <div class="tab-pane fade show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
                    <div class="container-fluid" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px;">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-lg" style="background: rgba(255,255,255,0.95); border-radius: 20px;">
                                    <div class="card-body text-center py-4">
                                        <h2 class="mb-2" style="color: #2c3e50; font-weight: 700;">
                                            <i class="fa fa-user-nurse text-primary me-3"></i>Nurse Dashboard
                                        </h2>
                                        <p class="text-muted mb-0">Welcome to Madridano Health Care Hospital - Nurse Panel</p>
                                        <small class="text-muted">Manage patient admissions, medicines, and care coordination</small>
                                    </div>
                                </div>
                            </div>
                        <div class="row g-4 mb-4 horizontal-cards" style="display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 1rem; padding-bottom: 1rem;">
                            <div style="flex: 0 0 180px;">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-user-plus fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2">Register Patient</h3>
                                        <p class="mb-3 opacity-90">Quick admission for walk-in patients</p>
                                        <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-admit-list').click()">
                                            <i class="fa fa-plus me-1"></i>Register Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div style="flex: 0 0 180px;">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-bed fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2">
                                            <?php
                                            $con = mysqli_connect("localhost", "root", "", "myhmsdb");
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE status != 'Discharged'");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <p class="mb-3 opacity-90">Active Patients</p>
                                        <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-pat-list').click()">
                                            <i class="fa fa-eye me-1"></i>View All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div style="flex: 0 0 180px;">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-pills fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2">
                                            <?php
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM medicinetb");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <p class="mb-3 opacity-90">Available Medicines</p>
                                        <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-meds-list').click()">
                                            <i class="fa fa-cog me-1"></i>Manage
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div style="flex: 0 0 180px;">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-user-md fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2">
                                            <?php
                                            $result = mysqli_query($con, "SELECT COUNT(DISTINCT username) AS total FROM doctortb");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <p class="mb-3 opacity-90">Available Doctors</p>
                                        <button class="btn btn-light btn-sm fw-bold">
                                            <i class="fa fa-info me-1"></i>View Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div style="flex: 0 0 180px;">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4 d-flex flex-column justify-content-center align-items-center">
                                        <div class="mb-3">
                                            <i class="fa fa-clock fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2">
                                            <?php
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM patient_roundstb WHERE round_date = CURDATE()");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <p class="mb-3 opacity-90 text-center">Scheduled Rounds Today</p>
                                        <button class="btn btn-light btn-sm fw-bold" onclick="$('#list-rounds-list').click()">
                                            <i class="fa fa-eye me-1"></i>View All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <div style="flex: 0 0 180px;">
                                <div class="card border-0 shadow-lg h-100 horizontal-cards" style=" background: #f093fb 0%; border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-users fa-3x text-success"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3" style="color: #2c3e50;">Patient Management</h5>
                                        <p class="text-muted mb-3">View and manage all admitted patients</p>
                                        <button class="btn btn-success fw-bold" onclick="$('#list-pat-list').click()">
                                            <i class="fa fa-list me-1"></i>View Patients
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div style="flex: 0 0 250px;">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-chart-line fa-3x text-info"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3" style="color: #2c3e50;">Today's Summary</h5>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h6 class="text-primary fw-bold">
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE DATE(admission_date) = '$today'");
                                                    $row = mysqli_fetch_assoc($result);
                                                    echo $row['total'];
                                                    ?>
                                                </h6>
                                                <small class="text-muted">New Admissions</small>
                                            </div>
                                            <div class="col-6">
                                                <h6 class="text-warning fw-bold">
                                                    <?php
$result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE admission_date = '$today'");
                                                    $row = mysqli_fetch_assoc($result);
                                                    echo $row['total'];
                                                    ?>
                                                </h6>
                                                <small class="text-muted">Medicines Given</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="flex: 0 0 250px;">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-clock fa-3x text-warning"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3" style="color: #2c3e50;">Patient Rounds</h5>
                                        <p class="text-muted mb-3">Schedule and manage patient rounds</p>
                                        <button class="btn btn-warning fw-bold" onclick="$('#list-rounds-list').click()">
                                            <i class="fa fa-calendar me-1"></i>Manage Rounds
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-lg" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body">
                                        <h4 class="mb-4">Today's Scheduled Patient Rounds</h4>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Patient ID</th>
                                                        <th>Patient Name</th>
                                                        <th>Scheduled Time</th>
                                                        <th>Status</th>
                                                        <th>Nurse</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    $rounds_query = "SELECT pr.*, a.fname, a.lname 
                                                                     FROM patient_roundstb pr 
                                                                     JOIN admissiontb a ON pr.pid = a.pid 
                                                                     WHERE pr.round_date = '$today' 
                                                                     ORDER BY pr.round_time";
                                                    $rounds_result = mysqli_query($con, $rounds_query);
                                                    while ($round = mysqli_fetch_array($rounds_result)) {
                                                        $status_class = "";
                                                        if ($round['status'] == 'Completed') {
                                                            $status_class = "round-status-completed";
                                                        } elseif ($round['status'] == 'Missed') {
                                                            $status_class = "round-status-missed";
                                                        } else {
                                                            $status_class = "round-status-scheduled";
                                                        }
                                                        
                                                        echo "<tr class='$status_class'>
                                                            <td>{$round['pid']}</td>
                                                            <td>{$round['fname']} {$round['lname']}</td>
                                                            <td>{$round['round_time']}</td>
                                                            <td>{$round['status']}</td>
                                                            <td>{$round['nurse_username']}</td>
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

                <div class="tab-pane fade" id="list-admit" role="tabpanel" aria-labelledby="list-admit-list">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-body">
                                <center><h4>Register Patient</h4></center><br>
                                <form class="form-group" method="post" action="nurse-panel.php">
                                    <div class="row">
                                        <div class="col-md-4"><label for="fname">First Name:</label></div>
                                        <div class="col-md-8"><input type="text" class="form-control" name="fname" required></div><br><br>
                                        <div class="col-md-4"><label for="lname">Last Name:</label></div>
                                        <div class="col-md-8"><input type="text" class="form-control" name="lname" required></div><br><br>
                                        <div class="col-md-4"><label for="gender">Gender:</label></div>
                                        <div class="col-md-8">
                                            <select name="gender" class="form-control" required>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div><br><br>
                                        <div class="col-md-4"><label for="email">Email:</label></div>
                                        <div class="col-md-8"><input type="email" class="form-control" name="email" required></div><br><br>
                                        <div class="col-md-4"><label for="contact">Contact:</label></div>
                                        <div class="col-md-8"><input type="text" class="form-control" name="contact" required></div><br><br>
                                        <div class="col-md-4"><label for="age">Age:</label></div>
                                        <div class="col-md-8"><input type="number" class="form-control" name="age" min="1" max="120" required></div><br><br>
                                        <div class="col-md-4"><label for="address">Address:</label></div>
                                        <div class="col-md-8"><textarea class="form-control" name="address" rows="3" required></textarea></div><br><br>
                                        <div class="col-md-4"><label for="reason">Reason for Admission:</label></div>
                                        <div class="col-md-8"><textarea class="form-control" name="reason" rows="3" placeholder="Describe the medical condition or reason for admission" required></textarea></div><br><br>
                                        <div class="col-md-4"><label for="password">Password:</label></div>
                                        <div class="col-md-8"><input type="password" class="form-control" name="password" required></div><br><br>
                                        <div class="col-md-4"><label for="cpassword">Confirm Password:</label></div>
                                        <div class="col-md-8"><input type="password" class="form-control" name="cpassword" required></div><br><br>
                                        <div class="col-md-4"><label for="assigned_doctor">Assign Doctor:</label></div>
                                        <div class="col-md-8">
                                            <select name="assigned_doctor" id="assigned_doctor" class="form-control" required onchange="updateDoctorFee()">
                                                <option value="">Select Doctor</option>
                                                <?php
                                                $doctor_query = "SELECT username, consultation_fee FROM doctortb";
                                                $doctor_result = mysqli_query($con, $doctor_query);
                                                while ($doctor = mysqli_fetch_array($doctor_result)) {
                                                    echo "<option value='{$doctor['username']}' data-fee='{$doctor['consultation_fee']}'>{$doctor['username']} - Fee: ₱{$doctor['consultation_fee']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div><br><br>
                                        <div class="col-md-4"><label for="room_number">Room Number:</label></div>
                                        <div class="col-md-8">
                                            <select name="room_number" class="form-control" required>
                                                <option value="">Select Room</option>
                                                <option value="101">Room 101 - General Ward</option>
                                                <option value="102">Room 102 - General Ward</option>
                                                <option value="103">Room 103 - General Ward</option>
                                                <option value="201">Room 201 - Private Room</option>
                                                <option value="202">Room 202 - Private Room</option>
                                                <option value="203">Room 203 - Private Room</option>
                                                <option value="301">Room 301 - ICU</option>
                                                <option value="302">Room 302 - ICU</option>
                                                <option value="401">Room 401 - Emergency</option>
                                                <option value="402">Room 402 - Emergency</option>
                                            </select>
                                        </div><br><br>
                                        <div class="col-md-4"><label for="doctor_fee">Doctor's Fee:</label></div>
                                        <div class="col-md-8"><input type="text" class="form-control" id="doctor_fee" readonly></div><br><br>
                                        <div class="col-md-4">
                                            <input type="submit" name="register_admit_patient" value="Register Patient" class="btn btn-primary" id="inputbtn">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div><br>
                </div>

                <div class="tab-pane fade" id="list-patients" role="tabpanel" aria-labelledby="list-pat-list">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Patient ID</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Email</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Admission Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM admissiontb";
                            $result = mysqli_query($con, $query);
                            while ($row = mysqli_fetch_array($result)) {
                                $pid = $row['pid'];
                                $fname = $row['fname'];
                                $lname = $row['lname'];
                                echo "<tr>
                                    <td>{$pid}</td>
                                    <td>{$fname}</td>
                                    <td>{$lname}</td>
                                    <td>{$row['gender']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['contact']}</td>
                                    <td>{$row['admission_date']}</td>
                                    <td>{$row['status']}</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

<div class="tab-pane fade" id="list-meds" role="tabpanel" aria-labelledby="list-meds-list">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <center><h4>Add Medicine</h4></center><br>
                <form class="form-group" method="post" action="nurse-panel.php">
                    <div class="row">
                        <div class="col-md-4"><label for="medicine_name">Medicine Name:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="medicine_name" required></div><br><br>
                        <div class="col-md-4"><label for="quantity">Quantity:</label></div>
                        <div class="col-md-8"><input type="number" class="form-control" name="quantity" min="1" required></div><br><br>
                        <div class="col-md-4"><label for="price">Price per Unit:</label></div>
                        <div class="col-md-8"><input type="number" step="0.01" min="0" class="form-control" name="price" required></div><br><br>
                        <div class="col-md-4">
                            <input type="submit" name="add_medicine" value="Add Medicine" class="btn btn-primary" id="inputbtn">
                        </div>
                        <div class="col-md-8">
                            <input type="submit" name="add_sample_medicines" value="Add Sample Medicines" class="btn btn-success">
                        </div>
                    </div>
                </form>
                                    </div>
                                </div>
                                <br>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5>Medicine Inventory</h5>
                                <?php                        
                                $medicine_table = "medicinetb";
                                if (!empty($medicine_table)) {
                                    $query = "SELECT * FROM $medicine_table ORDER BY medicine_name";
                                    $result = mysqli_query($con, $query);
                                    
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        echo '<table class="table table-hover table-striped">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th scope="col">ID</th>
                                                        <th scope="col">Medicine Name</th>
                                                        <th scope="col">Quantity</th>
                                                        <th scope="col">Price per Unit</th>
                                                        <th scope="col">Added By</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                        
                                        while ($row = mysqli_fetch_array($result)) {
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['medicine_name']}</td>
                                                    <td>{$row['quantity']}</td>
                                                    <td>₱" . number_format($row['price'], 2) . "</td>
                                                    <td>{$row['added_by_nurse']}</td>
                                                    <td>
                                                        <a href='nurse-panel.php?delete_medicine={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this medicine?\");'>Delete</a>
                                                    </td>
                                                </tr>";
                                        }
                                        
                                        echo '</tbody></table>';
                                    } else {
                                        echo "<div class='alert alert-info'>No medicines found in the $medicine_table table. Add some medicines using the form above.</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-warning'>Could not find a medicine table in the database. Please check your database structure.</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
        <br>
        
        <?php
        $test_query = "SELECT COUNT(*) as total FROM medicinetb";
        $test_result = mysqli_query($con, $test_query);
        if ($test_result) {
            $test_row = mysqli_fetch_assoc($test_result);
            echo "<div class='alert alert-info'>Debug: Found " . $test_row['total'] . " medicines in database</div>";
        } else {
            echo "<div class='alert alert-danger'>Debug: Error querying medicine table: " . mysqli_error($con) . "</div>";
        }
        ?>
        
        <div class="card">
            <div class="card-body">
                <h5>Medicine Inventory</h5>
                <table class="table table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Medicine Name</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Price per Unit</th>
                            <th scope="col">Added By</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM medicinetb ORDER BY medicine_name";
                        $result = mysqli_query($con, $query);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_array($result)) {
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['medicine_name']}</td>
                                    <td>{$row['quantity']}</td>
                                    <td>₱" . number_format($row['price'], 2) . "</td>
                                    <td>{$row['added_by_nurse']}</td>
                                    <td>
                                        <a href='nurse-panel.php?delete_medicine={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this medicine?\");'>Delete</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No medicines found in inventory.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
                <div class="tab-pane fade" id="list-rounds" role="tabpanel" aria-labelledby="list-rounds-list">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-body">
                                <center><h4>Schedule Patient Round</h4></center><br>
                                <form class="form-group" method="post" action="nurse-panel.php">
                                    <div class="row">
                                        <div class="col-md-4"><label for="patient_id">Patient:</label></div>
                                        <div class="col-md-8">
                                            <select name="patient_id" class="form-control" required>
                                                <option value="">Select Patient</option>
                                                <?php
                                                $patient_query = "SELECT pid, fname, lname FROM admissiontb WHERE status != 'Discharged'";
                                                $patient_result = mysqli_query($con, $patient_query);
                                                while ($patient = mysqli_fetch_array($patient_result)) {
                                                    echo "<option value='{$patient['pid']}'>ID: {$patient['pid']} - {$patient['fname']} {$patient['lname']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div><br><br>
                                        <div class="col-md-4"><label for="round_date">Round Date:</label></div>
                                        <div class="col-md-8"><input type="date" class="form-control" name="round_date" required value="<?php echo date('Y-m-d'); ?>"></div><br><br>
                                        <div class="col-md-4"><label for="round_time">Round Time:</label></div>
                                        <div class="col-md-8"><input type="time" class="form-control" name="round_time" required value="<?php echo date('H:i'); ?>"></div><br><br>
                                        <div class="col-md-4"><label for="notes">Notes:</label></div>
                                        <div class="col-md-8"><textarea class="form-control" name="notes" placeholder="Any special instructions or observations"></textarea></div><br><br>
                                        <div class="col-md-4">
                                            <input type="submit" name="schedule_round" value="Schedule Round" class="btn btn-primary" id="inputbtn">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <br>
                        
                        <div class="card">
                            <div class="card-body">
                                <center><h4>Today's Rounds</h4></center><br>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Patient ID</th>
                                            <th scope="col">Patient Name</th>
                                            <th scope="col">Scheduled Time</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $today = date('Y-m-d');
                                        $rounds_query = "SELECT pr.*, a.fname, a.lname 
                                                         FROM patient_roundstb pr 
                                                         JOIN admissiontb a ON pr.pid = a.pid 
                                                         WHERE pr.round_date = '$today' 
                                                         ORDER BY pr.round_time";
                                        $rounds_result = mysqli_query($con, $rounds_query);
                                        while ($round = mysqli_fetch_array($rounds_result)) {
                                            $status_class = "";
                                            if ($round['status'] == 'Completed') {
                                                $status_class = "round-status-completed";
                                            } elseif ($round['status'] == 'Missed') {
                                                $status_class = "round-status-missed";
                                            } else {
                                                $status_class = "round-status-scheduled";
                                            }
                                            
                                            echo "<tr class='$status_class'>
                                                <td>{$round['pid']}</td>
                                                <td>{$round['fname']} {$round['lname']}</td>
                                                <td>{$round['round_time']}</td>
                                                <td>{$round['status']}</td>
                                                <td>
                                                    <button type='button' class='btn btn-info btn-sm' data-toggle='modal' data-target='#updateRoundModal{$round['id']}'>Update</button>
                                                    <a href='nurse-panel.php?delete_round={$round['id']}' class='btn btn-danger btn-sm'>Delete</a>
                                                </td>
                                            </tr>";
                                            echo "
                                            <div class='modal fade' id='updateRoundModal{$round['id']}' tabindex='-1' role='dialog' aria-labelledby='updateRoundModalLabel' aria-hidden='true'>
                                                <div class='modal-dialog' role='document'>
                                                    <div class='modal-content'>
                                                        <div class='modal-header'>
                                                            <h5 class='modal-title' id='updateRoundModalLabel'>Update Round for Patient ID: {$round['pid']}</h5>
                                                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                                <span aria-hidden='true'>&times;</span>
                                                            </button>
                                                        </div>
                                                        <form method='post' action='nurse-panel.php'>
                                                            <div class='modal-body'>
                                                                <input type='hidden' name='round_id' value='{$round['id']}'>
                                                                <div class='form-group'>
                                                                    <label for='vital_signs'>Vital Signs:</label>
                                                                    <textarea class='form-control' name='vital_signs' placeholder='Blood pressure, temperature, pulse, etc.'>{$round['vital_signs']}</textarea>
                                                                </div>
                                                                <div class='form-group'>
                                                                    <label for='update_notes'>Notes:</label>
                                                                    <textarea class='form-control' name='update_notes' placeholder='Observations and findings'>{$round['notes']}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class='modal-footer'>
                                                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                                                <button type='submit' name='update_round' class='btn btn-primary'>Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <br>
                        <div class="card">
                            <div class="card-body">
                                <center><h4>All Scheduled Rounds</h4></center><br>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Patient ID</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Time</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Nurse</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $all_rounds_query = "SELECT pr.*, a.fname, a.lname 
                                                             FROM patient_roundstb pr 
                                                             JOIN admissiontb a ON pr.pid = a.pid 
                                                             ORDER BY pr.round_date DESC, pr.round_time DESC 
                                                             LIMIT 20";
                                        $all_rounds_result = mysqli_query($con, $all_rounds_query);
                                        while ($round = mysqli_fetch_array($all_rounds_result)) {
                                            $status_class = "";
                                            if ($round['status'] == 'Completed') {
                                                $status_class = "round-status-completed";
                                            } elseif ($round['status'] == 'Missed') {
                                                $status_class = "round-status-missed";
                                            } else {
                                                $status_class = "round-status-scheduled";
                                            }
                                            
                                            echo "<tr class='$status_class'>
                                                <td>{$round['id']}</td>
                                                <td>{$round['pid']} ({$round['fname']} {$round['lname']})</td>
                                                <td>{$round['round_date']}</td>
                                                <td>{$round['round_time']}</td>
                                                <td>{$round['status']}</td>
                                                <td>{$round['nurse_username']}</td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.10.1/sweetalert2.all.min.js"></script>
<script>
function updateDoctorFee() {
    var select = document.getElementById('assigned_doctor');
    var feeInput = document.getElementById('doctor_fee');
    var selectedOption = select.options[select.selectedIndex];
    var fee = selectedOption.getAttribute('data-fee');
    feeInput.value = fee ? '$' + fee : '';
}
</script>
</body>
</html>