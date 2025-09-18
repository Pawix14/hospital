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

$create_rounds_table = "CREATE TABLE IF NOT EXISTS patient_roundstb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pid INT NOT NULL,
    nurse_username VARCHAR(255) NOT NULL,
    round_date DATE NOT NULL,
    round_time TIME NOT NULL,
    notes TEXT,
    vital_signs TEXT,
    status VARCHAR(50) DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

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
        array("Paracetamol 500mg", 100, 30.50),
        array("Ibuprofen 400mg", 80, 17.75),
        array("Amoxicillin 500mg", 50, 12.30),
        array("Aspirin 100mg", 120, 16.25),
        array("Loratadine 10mg", 60, 29.80),
        array("Omeprazole 20mg", 70, 25.40),
        array("Metformin 500mg", 90, 17.90),
        array("Atorvastatin 20mg", 40, 18.20),
        array("Salbutamol Inhaler", 30, 22.50),
        array("Insulin Glargine", 25, 45.00),
        array("Amlodipine 5mg", 65, 10.25),
        array("Losartan 50mg", 55, 11.75),
        array("Cetirizine 10mg", 85, 10.30),
        array("Diazepam 5mg", 20, 14.50),
        array("Ciprofloxacin 500mg", 45, 16.80),
        array("Simvastatin 20mg", 60, 13.40),
        array("Metoprolol 50mg", 75, 25.25),
        array("Warfarin 5mg", 35, 38.90),
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

if (isset($_POST['update_medicine_quantity'])) {
    $medicine_id = (int)$_POST['medicine_id'];
    $new_quantity = (int)$_POST['edit_quantity'];

    if ($new_quantity < 0) {
        echo "<script>alert('Quantity cannot be negative.');</script>";
    } else {
        $query = "UPDATE medicinetb SET quantity = $new_quantity WHERE id = $medicine_id";
        if (mysqli_query($con, $query)) {
            echo "<script>alert('Medicine quantity updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating medicine quantity: " . mysqli_error($con) . "');</script>";
        }
    }
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard - Madridano Health Care Hospital</title>
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
        
        .round-status-scheduled { background-color: #fff3cd; }
        .round-status-completed { background-color: #d4edda; }
        .round-status-missed { background-color: #f8d7da; }
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
                    <i class="fas fa-user-nurse me-1"></i>Nurse: <?php echo $nurse; ?>
                </span>
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px;">
        <div class="welcome-header">
            <h2>Welcome back, <?php echo $nurse; ?>!</h2>
            <p>Nurse Dashboard - Manage patient admissions, medicines, and care coordination</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <a class="nav-link active" role="tab" data-toggle="tab" href="#dashboard" aria-controls="dashboard" aria-selected="true" id="dashboard-tab">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#register-patient" aria-controls="register-patient" aria-selected="false" id="register-patient-tab">
                            <i class="fas fa-hospital-user me-2"></i>Register Patient
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#patient-list" aria-controls="patient-list" aria-selected="false" id="patient-list-tab">
                            <i class="fas fa-users me-2"></i>Patient List
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#medicine-management" aria-controls="medicine-management" aria-selected="false" id="medicine-management-tab">
                            <i class="fas fa-pills me-2"></i>Manage Medicines
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#patient-rounds" aria-controls="patient-rounds" aria-selected="false" id="patient-rounds-tab">
                            <i class="fas fa-clock me-2"></i>Schedule Rounds
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9 col-md-8">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
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
                                    <i class="fas fa-pills fa-3x mb-3"></i>
                                    <h3><?php 
                                        $query = mysqli_query($con, "SELECT COUNT(*) as total FROM medicinetb");
                                        $row = mysqli_fetch_assoc($query);
                                        echo $row['total'] ?? 0;
                                    ?></h3>
                                    <p>Available Medicines</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--success-gradient);">
                                    <i class="fas fa-user-md fa-3x mb-3"></i>
                                    <h3><?php 
                                        $query = mysqli_query($con, "SELECT COUNT(DISTINCT username) as total FROM doctortb");
                                        $row = mysqli_fetch_assoc($query);
                                        echo $row['total'] ?? 0;
                                    ?></h3>
                                    <p>Available Doctors</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-clock me-2"></i>Today's Scheduled Rounds
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Patient Name</th>
                                            <th>Scheduled Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
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
                    </div>
                    
                    <!-- Register Patient Tab -->
                    <div class="tab-pane fade" id="register-patient" role="tabpanel" aria-labelledby="register-patient-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-hospital-user me-2"></i>Register New Patient
                            </h4>
                            <form class="form-group" method="post" action="nurse-panel.php">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fname">First Name:</label>
                                        <input type="text" class="form-control" name="fname" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lname">Last Name:</label>
                                        <input type="text" class="form-control" name="lname" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="gender">Gender:</label>
                                        <select name="gender" class="form-control" required>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact">Contact:</label>
                                        <input type="text" class="form-control" name="contact" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="age">Age:</label>
                                        <input type="number" class="form-control" name="age" min="1" max="120" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="address">Address:</label>
                                        <textarea class="form-control" name="address" rows="3" required></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="reason">Reason for Admission:</label>
                                        <textarea class="form-control" name="reason" rows="3" placeholder="Describe the medical condition or reason for admission" required></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password">Password:</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cpassword">Confirm Password:</label>
                                        <input type="password" class="form-control" name="cpassword" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="assigned_doctor">Assign Doctor:</label>
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
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="room_number">Room Number:</label>
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
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_fee">Doctor's Fee:</label>
                                        <input type="text" class="form-control" id="doctor_fee" readonly>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" name="register_admit_patient" value="Register Patient" class="btn btn-primary" id="inputbtn">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Patient List Tab -->
                    <div class="tab-pane fade" id="patient-list" role="tabpanel" aria-labelledby="patient-list-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-users me-2"></i>Patient List
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
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
                        </div>
                    </div>
                    
                    <!-- Medicine Management Tab -->
                    <div class="tab-pane fade" id="medicine-management" role="tabpanel" aria-labelledby="medicine-management-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-pills me-2"></i>Medicine Management
                            </h4>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5>Add Medicine</h5>
                                            <form class="form-group" method="post" action="nurse-panel.php">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="medicine_name">Medicine Name:</label>
                                                        <input type="text" class="form-control" name="medicine_name" required>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="quantity">Quantity:</label>
                                                        <input type="number" class="form-control" name="quantity" min="1" required>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="price">Price per Unit:</label>
                                                        <input type="number" step="0.01" min="0" class="form-control" name="price" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <input type="submit" name="add_medicine" value="Add Medicine" class="btn btn-primary" id="inputbtn">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <input type="submit" name="add_sample_medicines" value="Add Sample Medicines" class="btn btn-success">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
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
                                                                    <button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editMedicineModal{$row['id']}'>Edit Qty</button>
                                                                    <a href='nurse-panel.php?delete_medicine={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this medicine?\");'>Delete</a>
                                                                </td>
                                                            </tr>";
                                                    }
                                                    
                                                    echo '</tbody></table>';
                                                    
                                                    // Generate modals for editing quantity
                                                    mysqli_data_seek($result, 0); // Reset result pointer
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "
                                                        <div class='modal fade' id='editMedicineModal{$row['id']}' tabindex='-1' role='dialog' aria-labelledby='editMedicineModalLabel{$row['id']}' aria-hidden='true'>
                                                            <div class='modal-dialog' role='document'>
                                                                <div class='modal-content'>
                                                                    <div class='modal-header'>
                                                                        <h5 class='modal-title' id='editMedicineModalLabel{$row['id']}'>Edit Quantity for {$row['medicine_name']}</h5>
                                                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                                            <span aria-hidden='true'>&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <form method='post' action='nurse-panel.php'>
                                                                        <div class='modal-body'>
                                                                            <input type='hidden' name='medicine_id' value='{$row['id']}'>
                                                                            <div class='form-group'>
                                                                                <label for='edit_quantity'>New Quantity:</label>
                                                                                <input type='number' class='form-control' name='edit_quantity' min='0' value='{$row['quantity']}' required>
                                                                            </div>
                                                                        </div>
                                                                        <div class='modal-footer'>
                                                                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                                                            <button type='submit' name='update_medicine_quantity' class='btn btn-primary'>Update Quantity</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>";
                                                    }
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
                    
                    <!-- Patient Rounds Tab -->
                    <div class="tab-pane fade" id="patient-rounds" role="tabpanel" aria-labelledby="patient-rounds-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-clock me-2"></i>Schedule Patient Rounds
                            </h4>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5>Schedule New Round</h5>
                                            <form class="form-group" method="post" action="nurse-panel.php">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="patient_id">Patient:</label>
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
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="round_date">Round Date:</label>
                                                        <input type="date" class="form-control" name="round_date" required value="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="round_time">Round Time:</label>
                                                        <input type="time" class="form-control" name="round_time" required value="<?php echo date('H:i'); ?>">
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label for="notes">Notes:</label>
                                                        <textarea class="form-control" name="notes" placeholder="Any special instructions or observations"></textarea>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="submit" name="schedule_round" value="Schedule Round" class="btn btn-primary" id="inputbtn">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5>All Scheduled Rounds</h5>
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.nav-pills .nav-link').removeClass('active');
        $(this).addClass('active');
    });

    function updateDoctorFee() {
        var select = document.getElementById('assigned_doctor');
        var feeInput = document.getElementById('doctor_fee');
        var selectedOption = select.options[select.selectedIndex];
        var fee = selectedOption.getAttribute('data-fee');
        feeInput.value = fee ? '₱' + fee : '';
    }
    </script>
</body>
</html>