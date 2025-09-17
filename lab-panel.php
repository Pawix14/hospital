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
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Madridano Health Care Hospital </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <style >
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
        </style>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
                </li>
            </ul>
        </div>
    </nav>
</head>
<style type="text/css">
    button:hover{cursor:pointer;}
    #inputbtn:hover{cursor:pointer;}
</style>
<body style="padding-top:50px;">
<div class="container-fluid" style="margin-top:50px;">
    <h3 style = "margin-left: 40%;  padding-bottom: 20px; font-family: 'IBM Plex Sans', sans-serif;"> Welcome Lab User &nbsp<?php echo $lab_user ?> </h3>
    <div class="row">
        <div class="col-md-4" style="max-width:25%; margin-top: 3%">
            <div class="list-group" id="list-tab" role="tablist">
                <a class="list-group-item list-group-item-action active" id="list-dash-list" data-toggle="list" href="#list-dash" role="tab" aria-controls="home">Dashboard</a>
                <a class="list-group-item list-group-item-action" href="#list-tests" id="list-tests-list" role="tab" data-toggle="list" aria-controls="home">Lab Tests</a>
            </div><br>
        </div>
        <div class="col-md-8" style="margin-top: 3%;">
            <div class="tab-content" id="nav-tabContent" style="width: 950px;">
                <div class="tab-pane fade  show active" id="list-dash" role="tabpanel" aria-labelledby="list-dash-list">
                    <div class="container-fluid" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px;">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-lg" style="background: rgba(255,255,255,0.95); border-radius: 20px;">
                                    <div class="card-body text-center py-4">
                                        <h2 class="mb-2" style="color: #2c3e50; font-weight: 700;">
                                            <i class="fa fa-flask text-primary me-3"></i>Laboratory Dashboard
                                        </h2>
                                        <p class="text-muted mb-0">Welcome to Madridano Health Care Hospital - Laboratory Panel</p>
                                        <small class="text-muted">Manage lab tests, results, and quality control</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-vials fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2"><?php echo $total_tests; ?></h3>
                                        <p class="mb-3 opacity-90">Total Lab Tests</p>
                                        <script>
                                            function clickDiv(id) {
                                                document.querySelector(id).click();
                                            }
                                        </script>
                                        <button class="btn btn-light btn-sm fw-bold" onclick="clickDiv('#list-tests-list')">
                                            <i class="fa fa-eye me-1"></i>View All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-hourglass-half fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2"><?php echo $pending_tests; ?></h3>
                                        <p class="mb-3 opacity-90">Pending Tests</p>
                                        <button class="btn btn-light btn-sm fw-bold" onclick="clickDiv('#list-tests-list')">
                                            <i class="fa fa-clock me-1"></i>Process Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-check-circle fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2"><?php echo $completed_tests; ?></h3>
                                        <p class="mb-3 opacity-90">Completed Tests</p>
                                        <button class="btn btn-light btn-sm fw-bold">
                                            <i class="fa fa-chart-line me-1"></i>View Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 15px;">
                                    <div class="card-body text-center text-white p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-dollar-sign fa-3x opacity-75"></i>
                                        </div>
                                        <h3 class="fw-bold mb-2">₱<?php echo number_format($revenue, 2); ?></h3>
                                        <p class="mb-3 opacity-90">Total Revenue</p>
                                        <button class="btn btn-light btn-sm fw-bold">
                                            <i class="fa fa-money-bill me-1"></i>View Details
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
                                            <i class="fa fa-microscope fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3" style="color: #2c3e50;">Lab Test Management</h5>
                                        <p class="text-muted mb-3">Process and manage laboratory tests</p>
                                        <button class="btn btn-primary fw-bold" onclick="clickDiv('#list-tests-list')">
                                            <i class="fa fa-flask me-1"></i>Manage Tests
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-lg h-100" style="background: rgba(255,255,255,0.95); border-radius: 15px;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fa fa-file-medical fa-3x text-success"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3" style="color: #2c3e50;">Test Results</h5>
                                        <p class="text-muted mb-3">Upload and manage test results</p>
                                        <button class="btn btn-success fw-bold">
                                            <i class="fa fa-upload me-1"></i>Upload Results
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
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

                <div class="tab-pane fade" id="list-tests" role="tabpanel" aria-labelledby="list-tests-list">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
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
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
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