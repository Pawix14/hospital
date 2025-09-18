<!DOCTYPE html>
<?php
include('func.php');
include('newfunc.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'lab') {
    header("Location: index.php");
    exit();
}

$lab_user = $_SESSION['username'];
$total_tests_query = "SELECT COUNT(*) as total FROM labtesttb";
$total_tests_result = mysqli_query($con, $total_tests_query);
$total_tests = mysqli_fetch_array($total_tests_result)['total'];
$pending_tests_query = "SELECT COUNT(*) as pending FROM labtesttb WHERE status='Pending'";
$pending_tests_result = mysqli_query($con, $pending_tests_query);
$pending_tests = mysqli_fetch_array($pending_tests_result)['pending'];
$accepted_tests_query = "SELECT COUNT(*) as accepted FROM labtesttb WHERE status='Accepted'";
$accepted_tests_result = mysqli_query($con, $accepted_tests_query);
$accepted_tests = mysqli_fetch_array($accepted_tests_result)['accepted'];
$completed_tests_query = "SELECT COUNT(*) as completed FROM labtesttb WHERE status='Completed'";
$completed_tests_result = mysqli_query($con, $completed_tests_query);
$completed_tests = mysqli_fetch_array($completed_tests_result)['completed'];
$revenue_query = "SELECT SUM(price) as revenue FROM labtesttb WHERE status='Completed'";
$revenue_result = mysqli_query($con, $revenue_query);
$revenue = mysqli_fetch_array($revenue_result)['revenue'] ?? 0;

if (isset($_POST['accept_test'])) {
    $id = $_POST['test_id'];
    $scheduled_date = $_POST['scheduled_date'];
    $query = "UPDATE labtesttb SET status='Accepted', scheduled_date='$scheduled_date' WHERE id='$id'";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Test accepted and scheduled!');</script>";
        echo "<script>window.location.href='lab-panel.php';</script>";
    } else {
        echo "<script>alert('Error updating test!');</script>";
    }
}

if (isset($_POST['deny_test'])) {
    $id = $_POST['test_id'];
    $query = "UPDATE labtesttb SET status='Denied' WHERE id='$id'";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Test denied!');</script>";
        echo "<script>window.location.href='lab-panel.php';</script>";
    } else {
        echo "<script>alert('Error updating test!');</script>";
    }
}

if (isset($_POST['complete_test'])) {
    $id = $_POST['test_id'];
    $results = mysqli_real_escape_string($con, $_POST['results']);
    $lab_notes = mysqli_real_escape_string($con, $_POST['lab_notes']);
    $completed_date = date("Y-m-d");
    
    $query = "UPDATE labtesttb SET status='Completed', completed_date='$completed_date', results='$results', lab_notes='$lab_notes' WHERE id='$id'";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Test completed successfully with results!');</script>";
        echo "<script>window.location.href='lab-panel.php';</script>";
    } else {
        echo "<script>alert('Error updating test!');</script>";
    }
}

if (isset($_POST['reject_test'])) {
    $id = $_POST['test_id'];
    $rejection_reason = mysqli_real_escape_string($con, $_POST['rejection_reason']);
    $query = "UPDATE labtesttb SET status='Rejected', lab_notes='$rejection_reason' WHERE id='$id'";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Test rejected!');</script>";
        echo "<script>window.location.href='lab-panel.php';</script>";
    } else {
        echo "<script>alert('Error updating test!');</script>";
    }
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Dashboard - Madridano Health Care Hospital</title>
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
        
        .badge-warning { background-color: #ffc107; }
        .badge-info { background-color: #17a2b8; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
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
                    <i class="fas fa-flask me-1"></i>Lab Technician: <?php echo $lab_user; ?>
                </span>
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px;">
        <div class="welcome-header">
            <h2>Welcome back, <?php echo $lab_user; ?>!</h2>
            <p>Laboratory Dashboard - Manage lab tests, results, and quality control</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <a class="nav-link active" role="tab" data-toggle="tab" href="#dashboard" aria-controls="dashboard" aria-selected="true" id="dashboard-tab">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" role="tab" data-toggle="tab" href="#lab-tests" aria-controls="lab-tests" aria-selected="false" id="lab-tests-tab">
                            <i class="fas fa-vials me-2"></i>Lab Tests
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
                                    <i class="fas fa-vials fa-3x mb-3"></i>
                                    <h3><?php echo $total_tests; ?></h3>
                                    <p>Total Lab Tests</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--secondary-gradient);">
                                    <i class="fas fa-hourglass-half fa-3x mb-3"></i>
                                    <h3><?php echo $pending_tests; ?></h3>
                                    <p>Pending Tests</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--warning-gradient);">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h3><?php echo $completed_tests; ?></h3>
                                    <p>Completed Tests</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card" style="background: var(--success-gradient);">
                                    <i class="fas fa-dollar-sign fa-3x mb-3"></i>
                                    <h3>₱<?php echo number_format($revenue, 2); ?></h3>
                                    <p>Total Revenue</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-clock me-2"></i>Today's Lab Activity
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-microscope fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3" style="color: #2c3e50;">Lab Test Management</h5>
                                            <p class="text-muted mb-3">Process and manage laboratory tests</p>
                                            <button class="btn btn-primary fw-bold" onclick="$('#lab-tests-tab').click()">
                                                <i class="fa fa-flask me-1"></i>Manage Tests
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fa fa-chart-bar fa-3x text-info"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3" style="color: #2c3e50;">Today's Summary</h5>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h6 class="text-primary fw-bold">
                                                        <?php
                                                        $today = date('Y-m-d');
                                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM labtesttb WHERE DATE(requested_date) = '$today'");
                                                        $row = mysqli_fetch_assoc($result);
                                                        echo $row['total'];
                                                        ?>
                                                    </h6>
                                                    <small class="text-muted">Today's Tests</small>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="text-warning fw-bold">
                                                        <?php
                                                        $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM labtesttb WHERE status='Completed' AND DATE(completed_date) = '$today'");
                                                        $row = mysqli_fetch_assoc($result);
                                                        echo $row['total'];
                                                        ?>
                                                    </h6>
                                                    <small class="text-muted">Completed Today</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lab Tests Tab -->
                    <div class="tab-pane fade" id="lab-tests" role="tabpanel" aria-labelledby="lab-tests-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-vials me-2"></i>Lab Test Management
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Test ID</th>
                                            <th>Patient Info</th>
                                            <th>Test Details</th>
                                            <th>Doctor</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Dates</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT l.*, a.fname, a.lname, a.room_number FROM labtesttb l 
                                                 JOIN admissiontb a ON l.pid = a.pid 
                                                 ORDER BY 
                                                 CASE l.priority 
                                                     WHEN 'Emergency' THEN 1 
                                                     WHEN 'Urgent' THEN 2 
                                                     WHEN 'Normal' THEN 3 
                                                 END, 
                                                 l.requested_date DESC, l.requested_time DESC";
                                        $result = mysqli_query($con, $query);
                                        while ($row = mysqli_fetch_array($result)) {
                                            $status_class = '';
                                            switch($row['status']) {
                                                case 'Pending': $status_class = 'badge-warning'; break;
                                                case 'Accepted': $status_class = 'badge-info'; break;
                                                case 'Completed': $status_class = 'badge-success'; break;
                                                case 'Rejected': $status_class = 'badge-danger'; break;
                                                case 'Denied': $status_class = 'badge-secondary'; break;
                                            }
                                            
                                            $priority_class = '';
                                            switch($row['priority']) {
                                                case 'Normal': $priority_class = 'badge-secondary'; break;
                                                case 'Urgent': $priority_class = 'badge-warning'; break;
                                                case 'Emergency': $priority_class = 'badge-danger'; break;
                                            }
                                            
                                            echo "<tr>
                                                <td><strong>#{$row['id']}</strong></td>
                                                <td>
                                                    <strong>{$row['fname']} {$row['lname']}</strong><br>
                                                    <small>ID: {$row['pid']} | Room: {$row['room_number']}</small>
                                                </td>
                                                <td>
                                                    <strong>{$row['test_name']}</strong><br>
                                                    <small>Requested: {$row['requested_date']} {$row['requested_time']}</small>
                                                </td>
                                                <td><small>{$row['suggested_by_doctor']}</small></td>
                                                <td><span class='badge {$priority_class}'>{$row['priority']}</span></td>
                                                <td><span class='badge {$status_class}'>{$row['status']}</span></td>
                                                <td>
                                                    <small>
                                                        Scheduled: " . ($row['scheduled_date'] ? $row['scheduled_date'] : 'Not set') . "<br>
                                                        Completed: " . ($row['completed_date'] ? $row['completed_date'] : 'Not completed') . "
                                                    </small>
                                                </td>
                                                <td><strong>₱{$row['price']}</strong></td>
                                                <td>";
                                            
                                            if ($row['status'] == 'Pending') {
                                                echo "<button class='btn btn-success btn-sm mb-1' data-toggle='modal' data-target='#acceptModal-{$row['id']}'>
                                                        <i class='fa fa-check'></i> Accept
                                                      </button>
                                                      <button class='btn btn-danger btn-sm mb-1' data-toggle='modal' data-target='#rejectModal-{$row['id']}'>
                                                        <i class='fa fa-times'></i> Reject
                                                      </button>";
                                            } elseif ($row['status'] == 'Accepted') {
                                                echo "<button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#completeModal-{$row['id']}'>
                                                        <i class='fa fa-flask'></i> Complete Test
                                                      </button>";
                                            } elseif ($row['status'] == 'Completed') {
                                                echo "<button class='btn btn-info btn-sm' data-toggle='modal' data-target='#viewResultsModal-{$row['id']}'>
                                                        <i class='fa fa-eye'></i> View Results
                                                      </button>";
                                            }
                                            
                                            echo "</td></tr>";
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

    <!-- Modals -->
    <?php
    mysqli_data_seek($result, 0);
    while ($row = mysqli_fetch_array($result)) {
        $priority_class = '';
        switch($row['priority']) {
            case 'Normal': $priority_class = 'badge-secondary'; break;
            case 'Urgent': $priority_class = 'badge-warning'; break;
            case 'Emergency': $priority_class = 'badge-danger'; break;
        }

        echo "<div class='modal fade' id='acceptModal-{$row['id']}' tabindex='-1'>
            <div class='modal-dialog'>
                <form method='post' action='lab-panel.php' class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Accept Lab Test</h5>
                        <button type='button' class='close' data-dismiss='modal'><span>&times;</span></button>
                    </div>
                    <div class='modal-body'>
                        <input type='hidden' name='test_id' value='{$row['id']}'>
                        <p><strong>Test:</strong> {$row['test_name']}</p>
                        <p><strong>Patient:</strong> {$row['fname']} {$row['lname']} (ID: {$row['pid']})</p>
                        <p><strong>Priority:</strong> <span class='badge {$priority_class}'>{$row['priority']}</span></p>
                        <div class='form-group'>
                            <label>Schedule Test Date:</label>
                            <input type='date' name='scheduled_date' class='form-control' required min='" . date('Y-m-d') . "'>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='submit' name='accept_test' class='btn btn-success'>Accept & Schedule</button>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                    </div>
                </form>
            </div>
        </div>";
        echo "<div class='modal fade' id='rejectModal-{$row['id']}' tabindex='-1'>
            <div class='modal-dialog'>
                <form method='post' action='lab-panel.php' class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Reject Lab Test</h5>
                        <button type='button' class='close' data-dismiss='modal'><span>&times;</span></button>
                    </div>
                    <div class='modal-body'>
                        <input type='hidden' name='test_id' value='{$row['id']}'>
                        <p><strong>Test:</strong> {$row['test_name']}</p>
                        <p><strong>Patient:</strong> {$row['fname']} {$row['lname']}</p>
                        <div class='form-group'>
                            <label>Reason for Rejection:</label>
                            <textarea name='rejection_reason' class='form-control' rows='3' required></textarea>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='submit' name='reject_test' class='btn btn-danger'>Reject Test</button>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                    </div>
                </form>
            </div>
        </div>";
        if ($row['status'] == 'Accepted') {
            echo "<div class='modal fade' id='completeModal-{$row['id']}' tabindex='-1'>
                <div class='modal-dialog modal-lg'>
                    <form method='post' action='lab-panel.php' class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Complete Lab Test - {$row['test_name']}</h5>
                            <button type='button' class='close' data-dismiss='modal'><span>&times;</span></button>
                        </div>
                        <div class='modal-body'>
                            <input type='hidden' name='test_id' value='{$row['id']}'>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <p><strong>Patient:</strong> {$row['fname']} {$row['lname']}</p>
                                    <p><strong>Test:</strong> {$row['test_name']}</p>
                                    <p><strong>Priority:</strong> <span class='badge {$priority_class}'>{$row['priority']}</span></p>
                                </div>
                                <div class='col-md-6'>
                                    <p><strong>Requested by:</strong> {$row['suggested_by_doctor']}</p>
                                    <p><strong>Scheduled:</strong> {$row['scheduled_date']}</p>
                                    <p><strong>Price:</strong> ₱{$row['price']}</p>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label>Test Results:</label>
                                <textarea name='results' class='form-control' rows='5' placeholder='Enter detailed test results...' required></textarea>
                            </div>
                            <div class='form-group'>
                                <label>Lab Notes (Optional):</label>
                                <textarea name='lab_notes' class='form-control' rows='3' placeholder='Additional notes or observations...'></textarea>
                            </div>
                        </div>
                        <div class='modal-footer'>
                            <button type='submit' name='complete_test' class='btn btn-success'>Complete Test</button>
                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>";
        }
        if ($row['status'] == 'Completed') {
            echo "<div class='modal fade' id='viewResultsModal-{$row['id']}' tabindex='-1'>
                <div class='modal-dialog modal-lg'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Test Results - {$row['test_name']}</h5>
                            <button type='button' class='close' data-dismiss='modal'><span>&times;</span></button>
                        </div>
                        <div class='modal-body'>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <p><strong>Patient:</strong> {$row['fname']} {$row['lname']}</p>
                                    <p><strong>Test:</strong> {$row['test_name']}</p>
                                    <p><strong>Completed:</strong> {$row['completed_date']}</p>
                                </div>
                                <div class='col-md-6'>
                                    <p><strong>Requested by:</strong> {$row['suggested_by_doctor']}</p>
                                    <p><strong>Priority:</strong> <span class='badge {$priority_class}'>{$row['priority']}</span></p>
                                    <p><strong>Price:</strong> ₱{$row['price']}</p>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><strong>Results:</strong></label>
                                <div class='border p-3 bg-light'>" . nl2br(htmlspecialchars($row['results'])) . "</div>
                            </div>
                            <div class='form-group'>
                                <label><strong>Lab Notes:</strong></label>
                                <div class='border p-3 bg-light'>" . nl2br(htmlspecialchars($row['lab_notes'])) . "</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
    }
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.modal').on('show.bs.modal', function() {
            $(this).find('form').trigger('reset');
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
        $('form').on('submit', function(e) {
            let isValid = true;
            $(this).find('[required]').each(function() {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback">This field is required</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                $(this).find('.is-invalid').first().focus();
            }
        });
        <?php if(isset($_POST['accept_test']) || isset($_POST['reject_test']) || isset($_POST['complete_test'])): ?>
            $('.modal').modal('hide');
        <?php endif; ?>
    });
    </script>
</body>
</html>