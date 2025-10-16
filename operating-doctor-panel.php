<?php
session_start();
include('func.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'operating_doctor') {
    header("Location: index.php");
    exit();
}
$doctor = $_SESSION['username'];
$doctor_id = $_SESSION['doctor_id'];
$query = "SELECT a.*, p.doctor AS doctor_id, p.diagnosis_details AS diagnosis, p.prescribed_medicines AS prescription, DATE(p.created_at) AS date, TIME(p.created_at) AS time
          FROM admissiontb a
          LEFT JOIN prestb p ON a.pid = p.pid
          WHERE a.operating_doctor_id = '$doctor_id' AND a.status = 'Admitted'
          ORDER BY a.admission_date DESC";
$result = mysqli_query($con, $query);
$patients = mysqli_fetch_all($result, MYSQLI_ASSOC);
$surgery_query = "SELECT * FROM surgerytb WHERE operating_doctor_id = '$doctor_id' ORDER BY scheduled_date DESC";
$surgery_result = mysqli_query($con, $surgery_query);
$surgeries = mysqli_fetch_all($surgery_result, MYSQLI_ASSOC);
if (isset($_POST['add_diagnosis'])) {
    $pid = $_POST['pid'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];

    $insert_query = "INSERT INTO prestb (pid, doctor, diagnosis_details, prescribed_medicines, created_at)
                     VALUES ('$pid', '$doctor_id', '$diagnosis', '$prescription', NOW())";
    if (mysqli_query($con, $insert_query)) {
        echo "<script>alert('Diagnosis added successfully!'); window.location.href='operating-doctor-panel.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding diagnosis: " . mysqli_error($con) . "');</script>";
    }
}
if (isset($_POST['schedule_surgery'])) {
    $pid = $_POST['pid'];
    $surgery_type = $_POST['surgery_type'];
    $scheduled_date = $_POST['scheduled_date'];
    $scheduled_time = $_POST['scheduled_time'];
    $notes = $_POST['notes'];

    $insert_surgery = "INSERT INTO surgerytb (pid, operating_doctor_id, surgery_type, scheduled_date, scheduled_time, notes, status)
                       VALUES ('$pid', '$doctor_id', '$surgery_type', '$scheduled_date', '$scheduled_time', '$notes', 'Scheduled')";
    if (mysqli_query($con, $insert_surgery)) {
        echo "<script>alert('Surgery scheduled successfully!'); window.location.href='operating-doctor-panel.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error scheduling surgery: " . mysqli_error($con) . "');</script>";
    }
}

if (isset($_POST['update_surgery_status'])) {
    $surgery_id = $_POST['surgery_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    $update_query = "UPDATE surgerytb SET status = '$status', notes = CONCAT(notes, '\n$notes'), completed_date = CURDATE(), completed_time = CURTIME() WHERE id = '$surgery_id'";
    if (mysqli_query($con, $update_query)) {
        echo "<script>alert('Surgery status updated successfully!'); window.location.href='operating-doctor-panel.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating surgery status: " . mysqli_error($con) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operating Doctor Panel - Madridano Health Care Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4a90e2;
            --light-blue: #e8f4fd;
            --dark-blue: #2c5282;
            --success-green: #48bb78;
            --warning-yellow: #ed8936;
            --danger-red: #e53e3e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .navbar-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--dark-blue) !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(0,0,0,0.05);
            pointer-events: auto !important;
            z-index: 10;
        }

        .nav-pills .nav-link {
            color: var(--dark-blue);
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            pointer-events: auto !important;
            z-index: 10;
            cursor: pointer !important;
        }

        .nav-pills .nav-link:hover {
            background: var(--light-blue);
            color: var(--primary-blue);
        }

        .nav-pills .nav-link.active {
            background: var(--primary-blue);
            color: white;
            box-shadow: 0 2px 10px rgba(74, 144, 226, 0.3);
        }

        .table-glass {
            background: transparent;
            color: #2c3e50;
        }

        .table-glass th {
            background: rgba(0,0,0,0.05);
            color: var(--dark-blue);
            font-weight: 600;
            border: none;
        }

        .table-glass td {
            border: none;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .welcome-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .welcome-header h2 {
            color: var(--dark-blue);
            font-weight: 600;
        }

        .welcome-header p {
            color: #718096;
        }

        .badge-lg {
            padding: 6px 10px;
            font-size: 0.85rem;
        }

        .btn-outline-primary:hover {
            color: white;
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.1);
            border-radius: 15px 15px 0 0;
        }

        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.1);
        }

        .status-admitted { color: var(--success-green); }
        .status-discharged { color: var(--danger-red); }
        .surgery-scheduled { background-color: rgba(237, 137, 54, 0.1); }
        .surgery-completed { background-color: rgba(72, 187, 120, 0.1); }
        .surgery-cancelled { background-color: rgba(229, 62, 62, 0.1); }

        .quick-action-btn {
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .tab-content > .tab-pane {
            display: none;
        }
        .tab-content > .active {
            display: block;
        }
        .fade {
            transition: opacity 0.15s linear;
        }
        .fade:not(.show) {
            opacity: 0;
        }
        .fade.show {
            opacity: 1;
        }

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
                <span class="nav-link text-dark">
                    <i class="fas fa-user-md me-1"></i>Dr. <?php echo $doctor; ?>
                </span>
                <a class="nav-link text-dark" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px;">
        <div class="welcome-header">
            <h2>Welcome, Dr. <?php echo $doctor; ?>!</h2>
            <p>Operating Doctor Dashboard - Manage surgical procedures and patient care</p>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-patients-tab" data-toggle="pill" href="#v-pills-patients" role="tab" aria-controls="v-pills-patients" aria-selected="true">
                            <i class="fas fa-users me-2"></i>My Patients
                        </a>
                        <a class="nav-link" id="v-pills-diagnosis-tab" data-toggle="pill" href="#v-pills-diagnosis" role="tab" aria-controls="v-pills-diagnosis" aria-selected="false">
                            <i class="fas fa-stethoscope me-2"></i>Diagnosis
                        </a>
                        <a class="nav-link" id="v-pills-surgeries-tab" data-toggle="pill" href="#v-pills-surgeries" role="tab" aria-controls="v-pills-surgeries" aria-selected="false">
                            <i class="fas fa-procedures me-2"></i>Surgeries
                        </a>
                        <a class="nav-link" id="v-pills-schedule-tab" data-toggle="pill" href="#v-pills-schedule" role="tab" aria-controls="v-pills-schedule" aria-selected="false">
                            <i class="fas fa-calendar-plus me-2"></i>Schedule Surgery
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-md-8">
                <!-- Quick Actions -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="text-dark mb-3">Quick Actions</h5>
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="#v-pills-patients" class="btn btn-outline-primary btn-sm quick-action-btn">
                                <i class="fas fa-users me-1"></i>View Patients
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#v-pills-diagnosis" class="btn btn-outline-success btn-sm quick-action-btn">
                                <i class="fas fa-stethoscope me-1"></i>Add Diagnosis
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#v-pills-surgeries" class="btn btn-outline-warning btn-sm quick-action-btn">
                                <i class="fas fa-procedures me-1"></i>View Surgeries
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#v-pills-schedule" class="btn btn-outline-info btn-sm quick-action-btn">
                                <i class="fas fa-calendar-plus me-1"></i>Schedule Surgery
                            </a>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Patients Tab -->
                    <div class="tab-pane fade show active" id="v-pills-patients" role="tabpanel" aria-labelledby="v-pills-patients-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-users me-2"></i>My Assigned Patients
                            </h4>
                            <div class="row">
                                <?php foreach ($patients as $patient): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="glass-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-dark"><?php echo $patient['fname'] . ' ' . $patient['lname']; ?></h5>
                                            <p class="card-text text-muted">
                                                <strong>PID:</strong> <?php echo $patient['pid']; ?><br>
                                                <strong>Admission Date:</strong> <?php echo $patient['admission_date']; ?><br>
                                                <strong>Reason:</strong> <?php echo $patient['reason']; ?><br>
                                                <strong>Status:</strong> <span class="status-admitted"><?php echo $patient['status']; ?></span>
                                            </p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#diagnosisModal<?php echo $patient['pid']; ?>">
                                                    <i class="fas fa-stethoscope"></i> Diagnose
                                                </button>
                                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#surgeryModal<?php echo $patient['pid']; ?>">
                                                    <i class="fas fa-procedures"></i> Schedule Surgery
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Diagnosis Modal -->
                                <div class="modal fade" id="diagnosisModal<?php echo $patient['pid']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Add Diagnosis for <?php echo $patient['fname'] . ' ' . $patient['lname']; ?></h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="pid" value="<?php echo $patient['pid']; ?>">
                                                    <div class="form-group">
                                                        <label>Diagnosis:</label>
                                                        <textarea class="form-control" name="diagnosis" rows="3" required></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Prescription:</label>
                                                        <textarea class="form-control" name="prescription" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" name="add_diagnosis" class="btn btn-primary">Add Diagnosis</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Surgery Modal -->
                                <div class="modal fade" id="surgeryModal<?php echo $patient['pid']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Schedule Surgery for <?php echo $patient['fname'] . ' ' . $patient['lname']; ?></h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="pid" value="<?php echo $patient['pid']; ?>">
                                                    <div class="form-group">
                                                        <label>Surgery Type:</label>
                                                        <input type="text" class="form-control" name="surgery_type" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Scheduled Date:</label>
                                                        <input type="date" class="form-control" name="scheduled_date" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Scheduled Time:</label>
                                                        <input type="time" class="form-control" name="scheduled_time" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Notes:</label>
                                                        <textarea class="form-control" name="notes" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" name="schedule_surgery" class="btn btn-success">Schedule Surgery</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnosis Tab -->
                    <div class="tab-pane fade" id="v-pills-diagnosis" role="tabpanel" aria-labelledby="v-pills-diagnosis-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-stethoscope me-2"></i>Recent Diagnoses
                            </h4>
                            <div class="row">
                                <?php foreach ($patients as $patient): ?>
                                <?php if (!empty($patient['diagnosis'])): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="glass-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-dark"><?php echo $patient['fname'] . ' ' . $patient['lname']; ?></h5>
                                            <p class="card-text text-muted">
                                                <strong>Diagnosis:</strong> <?php echo $patient['diagnosis']; ?><br>
                                                <strong>Prescription:</strong> <?php echo $patient['prescription']; ?><br>
                                                <strong>Date:</strong> <?php echo $patient['date'] . ' ' . $patient['time']; ?>
                                            </p>
                                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#diagnosisModal<?php echo $patient['pid']; ?>">
                                                <i class="fas fa-edit"></i> Update Diagnosis
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Surgeries Tab -->
                    <div class="tab-pane fade" id="v-pills-surgeries" role="tabpanel" aria-labelledby="v-pills-surgeries-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-procedures me-2"></i>My Surgeries
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th>Surgery Type</th>
                                            <th>Scheduled Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($surgeries as $surgery): ?>
                                        <tr class="<?php echo 'surgery-' . strtolower($surgery['status']); ?>">
                                            <td><?php echo $surgery['pid']; ?></td>
                                            <td><?php echo $surgery['surgery_type']; ?></td>
                                            <td><?php echo $surgery['scheduled_date'] . ' ' . $surgery['scheduled_time']; ?></td>
                                            <td>
                                                <span class="badge badge-lg <?php
                                                    if ($surgery['status'] == 'Scheduled') echo 'badge-warning';
                                                    elseif ($surgery['status'] == 'In Progress') echo 'badge-info';
                                                    elseif ($surgery['status'] == 'Completed') echo 'badge-success';
                                                    else echo 'badge-danger';
                                                ?>">
                                                    <?php echo $surgery['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($surgery['status'] == 'Scheduled'): ?>
                                                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#updateSurgeryModal<?php echo $surgery['id']; ?>">
                                                    Update Status
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Update Surgery Status Modal -->
                                        <div class="modal fade" id="updateSurgeryModal<?php echo $surgery['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Surgery Status</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="surgery_id" value="<?php echo $surgery['id']; ?>">
                                                            <div class="form-group">
                                                                <label>Status:</label>
                                                                <select class="form-control" name="status" required>
                                                                    <option value="In Progress">In Progress</option>
                                                                    <option value="Completed">Completed</option>
                                                                    <option value="Cancelled">Cancelled</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Additional Notes:</label>
                                                                <textarea class="form-control" name="notes" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <button type="submit" name="update_surgery_status" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-schedule" role="tabpanel" aria-labelledby="v-pills-schedule-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-calendar-plus me-2"></i>Schedule New Surgery
                            </h4>
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark">Patient ID:</label>
                                            <select class="form-control" name="pid" required>
                                                <option value="">Select Patient</option>
                                                <?php foreach ($patients as $patient): ?>
                                                <option value="<?php echo $patient['pid']; ?>"><?php echo $patient['pid'] . ' - ' . $patient['fname'] . ' ' . $patient['lname']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark">Surgery Type:</label>
                                            <input type="text" class="form-control" name="surgery_type" required placeholder="e.g., Appendectomy, Cataract Surgery">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark">Scheduled Date:</label>
                                            <input type="date" class="form-control" name="scheduled_date" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark">Scheduled Time:</label>
                                            <input type="time" class="form-control" name="scheduled_time" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="text-dark">Notes:</label>
                                    <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes or preparation instructions"></textarea>
                                </div>
                                <button type="submit" name="schedule_surgery" class="btn btn-success">
                                    <i class="fas fa-calendar-plus me-1"></i> Schedule Surgery
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#v-pills-tab a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
                $('.tab-pane').removeClass('show active');
                var target = $(this).attr('href');
                $(target).addClass('show active');
            });
            $('.quick-action-btn').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                $('.tab-pane').removeClass('show active');
                $(target).addClass('show active');
                $('#v-pills-tab a').removeClass('active').attr('aria-selected', 'false');
                $('#v-pills-tab a[href="' + target + '"]').addClass('active').attr('aria-selected', 'true');
            });

            $('.tab-pane').removeClass('show active');
            $('#v-pills-patients').addClass('show active');
            $('.modal').on('show.bs.modal', function () {
                $('body').addClass('modal-open');
            });
            $('.modal').on('hidden.bs.modal', function () {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
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