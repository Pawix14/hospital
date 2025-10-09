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
$today = date('Y-m-d');
$today_tests_query = "SELECT COUNT(*) as total FROM labtesttb WHERE DATE(requested_date) = '$today'";
$today_tests_result = mysqli_query($con, $today_tests_query);
$today_tests = mysqli_fetch_array($today_tests_result)['total'];
$today_completed_query = "SELECT COUNT(*) as completed FROM labtesttb WHERE status='Completed' AND DATE(completed_date) = '$today'";
$today_completed_result = mysqli_query($con, $today_completed_query);
$today_completed = mysqli_fetch_array($today_completed_result)['completed'];
$today_revenue_query = "SELECT SUM(price) as revenue FROM labtesttb WHERE status='Completed' AND DATE(completed_date) = '$today'";
$today_revenue_result = mysqli_query($con, $today_revenue_query);
$today_revenue = mysqli_fetch_array($today_revenue_result)['revenue'] ?? 0;
$emergency_tests_query = "SELECT COUNT(*) as emergency FROM labtesttb WHERE priority='Emergency' AND status != 'Completed'";
$emergency_tests_result = mysqli_query($con, $emergency_tests_query);
$emergency_tests = mysqli_fetch_array($emergency_tests_result)['emergency'];
$urgent_tests_query = "SELECT COUNT(*) as urgent FROM labtesttb WHERE priority='Urgent' AND status != 'Completed'";
$urgent_tests_result = mysqli_query($con, $urgent_tests_query);
$urgent_tests = mysqli_fetch_array($urgent_tests_result)['urgent'];
$total_equipment = 15;
$operational_equipment = 12;
$maintenance_equipment = 3;
$recent_activity_query = "
    (SELECT 'test_request' as type, CONCAT('New test: ', test_name, ' for Patient ID: ', pid) as activity, requested_date as date, requested_time as time
     FROM labtesttb
     ORDER BY requested_date DESC, requested_time DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'test_completed' as type, CONCAT('Completed: ', test_name) as activity, completed_date as date, '00:00:00' as time
     FROM labtesttb
     WHERE status='Completed'
     ORDER BY completed_date DESC
     LIMIT 3)
    ORDER BY date DESC, time DESC
    LIMIT 5
";
$recent_activity_result = mysqli_query($con, $recent_activity_query);

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

        .metric-card {
            padding: 15px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
            text-align: center;
        }

        .activity-item {
            padding: 10px;
            border-left: 3px solid #667eea;
            background: rgba(255,255,255,0.05);
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .stat-card-enhanced {
            border-radius: 15px;
            padding: 25px;
            color: white;
            height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card-enhanced .d-flex {
            align-items: center;
            justify-content: space-between;
        }

        .stat-card-enhanced .icon-container {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .priority-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .priority-emergency { background-color: #dc3545; }
        .priority-urgent { background-color: #fd7e14; }
        .priority-normal { background-color: #20c997; }

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
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-dashboard-tab" data-toggle="pill" href="#v-pills-dashboard" role="tab" aria-controls="v-pills-dashboard" aria-selected="true">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" id="v-pills-lab-tests-tab" data-toggle="pill" href="#v-pills-lab-tests" role="tab" aria-controls="v-pills-lab-tests" aria-selected="false">
                            <i class="fas fa-vials me-2"></i>Lab Tests
                        </a>
                        <a class="nav-link" id="v-pills-equipment-tab" data-toggle="pill" href="#v-pills-equipment" role="tab" aria-controls="v-pills-equipment" aria-selected="false">
                            <i class="fas fa-cogs me-2"></i>Equipment
                        </a>
                        <a class="nav-link" id="v-pills-quality-control-tab" data-toggle="pill" href="#v-pills-quality-control" role="tab" aria-controls="v-pills-quality-control" aria-selected="false">
                            <i class="fas fa-check-circle me-2"></i>Quality Control
                        </a>
                        <a class="nav-link" id="v-pills-reports-tab" data-toggle="pill" href="#v-pills-reports" role="tab" aria-controls="v-pills-reports" aria-selected="false">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </div>
                </div>
            </div>          
            <div class="col-lg-9 col-md-8">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel" aria-labelledby="v-pills-dashboard-tab">
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Quick Actions</h5>
                            <div class="row g-2">
                                <div class="col-auto">
                                    <a href="#v-pills-lab-tests" class="btn btn-outline-primary btn-sm quick-action-btn">
                                        <i class="fas fa-vials me-1"></i>Manage Tests
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="#v-pills-equipment" class="btn btn-outline-success btn-sm quick-action-btn">
                                        <i class="fas fa-cogs me-1"></i>Equipment Status
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="#v-pills-quality-control" class="btn btn-outline-warning btn-sm quick-action-btn">
                                        <i class="fas fa-check-circle me-1"></i>Quality Control
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="#v-pills-reports" class="btn btn-outline-info btn-sm quick-action-btn">
                                        <i class="fas fa-chart-bar me-1"></i>View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--primary-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3><?php echo $total_tests; ?></h3>
                                            <p>Total Lab Tests</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-vials"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">All-time tests <i class="fas fa-chart-line trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--secondary-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3><?php echo $pending_tests; ?></h3>
                                            <p>Pending Tests</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">Awaiting processing <i class="fas fa-clock trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--warning-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3><?php echo $completed_tests; ?></h3>
                                            <p>Completed Tests</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">Successfully processed <i class="fas fa-tasks trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--success-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3>₱<?php echo number_format($revenue, 2); ?></h3>
                                            <p>Total Revenue</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">From completed tests <i class="fas fa-chart-line trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                        </div>
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Today's Overview</h5>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-primary"><?php echo $today_tests; ?></h3>
                                        <small>Tests Requested Today</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-info"><?php echo $today_completed; ?></h3>
                                        <small>Completed Today</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-warning"><?php echo $pending_tests; ?></h3>
                                        <small>Pending Tests</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-success">₱<?php echo number_format($today_revenue, 2); ?></h3>
                                        <small>Today's Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="glass-card p-4 h-100">
                                    <h5 class="text-dark mb-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Priority Tests Requiring Attention
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-glass">
                                            <thead>
                                                <tr>
                                                    <th>Test ID</th>
                                                    <th>Patient</th>
                                                    <th>Test Name</th>
                                                    <th>Priority</th>
                                                    <th>Requested</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $priority_query = "SELECT l.*, a.fname, a.lname 
                                                                  FROM labtesttb l 
                                                                  JOIN admissiontb a ON l.pid = a.pid 
                                                                  WHERE l.status IN ('Pending', 'Accepted') 
                                                                  AND l.priority IN ('Emergency', 'Urgent')
                                                                  ORDER BY 
                                                                  CASE l.priority 
                                                                      WHEN 'Emergency' THEN 1 
                                                                      WHEN 'Urgent' THEN 2 
                                                                  END,
                                                                  l.requested_date DESC, l.requested_time DESC
                                                                  LIMIT 5";
                                                $priority_result = mysqli_query($con, $priority_query);
                                                $has_priority = false;
                                                
                                                while ($row = mysqli_fetch_array($priority_result)) {
                                                    $has_priority = true;
                                                    $status_class = '';
                                                    switch($row['status']) {
                                                        case 'Pending': $status_class = 'badge-warning'; break;
                                                        case 'Accepted': $status_class = 'badge-info'; break;
                                                        case 'Completed': $status_class = 'badge-success'; break;
                                                        case 'Rejected': $status_class = 'badge-danger'; break;
                                                        case 'Denied': $status_class = 'badge-secondary'; break;
                                                    }
                                                    
                                                    $priority_class = '';
                                                    $priority_indicator = '';
                                                    switch($row['priority']) {
                                                        case 'Normal': 
                                                            $priority_class = 'badge-secondary'; 
                                                            $priority_indicator = 'priority-normal';
                                                            break;
                                                        case 'Urgent': 
                                                            $priority_class = 'badge-warning'; 
                                                            $priority_indicator = 'priority-urgent';
                                                            break;
                                                        case 'Emergency': 
                                                            $priority_class = 'badge-danger'; 
                                                            $priority_indicator = 'priority-emergency';
                                                            break;
                                                    }
                                                    
                                                    echo "<tr>
                                                        <td><strong>#{$row['id']}</strong></td>
                                                        <td>{$row['fname']} {$row['lname']}</td>
                                                        <td>{$row['test_name']}</td>
                                                        <td>
                                                            <span class='priority-indicator {$priority_indicator}'></span>
                                                            <span class='badge {$priority_class}'>{$row['priority']}</span>
                                                        </td>
                                                        <td>{$row['requested_date']}</td>
                                                        <td><span class='badge {$status_class}'>{$row['status']}</span></td>
                                                        <td>";
                                                    
                                                    if ($row['status'] == 'Pending') {
                                                        echo "<button class='btn btn-success btn-sm mb-1' data-toggle='modal' data-target='#acceptModal-{$row['id']}'>
                                                                <i class='fa fa-check'></i> Accept
                                                              </button>";
                                                    } elseif ($row['status'] == 'Accepted') {
                                                        echo "<button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#completeModal-{$row['id']}'>
                                                                <i class='fa fa-flask'></i> Complete
                                                              </button>";
                                                    }
                                                    
                                                    echo "</td></tr>";
                                                }
                                                
                                                if (!$has_priority) {
                                                    echo "<tr><td colspan='7' class='text-center text-muted py-4'>No priority tests requiring immediate attention</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="glass-card p-4 mb-4">
                                    <h5 class="text-dark mb-3">
                                        <i class="fas fa-list-alt me-2"></i>Recent Activity
                                    </h5>
                                    <div class="activity-feed">
                                        <?php
                                        $has_activity = false;
                                        while($activity = mysqli_fetch_array($recent_activity_result)) {
                                            $has_activity = true;
                                            $badge_class = '';
                                            $icon = '';
                                            switch($activity['type']) {
                                                case 'test_request':
                                                    $badge_class = 'bg-primary';
                                                    $icon = 'fa-vials';
                                                    break;
                                                case 'test_completed':
                                                    $badge_class = 'bg-success';
                                                    $icon = 'fa-check-circle';
                                                    break;
                                            }
                                            
                                            $time_display = ($activity['time'] != '00:00:00') ? date('g:i A', strtotime($activity['time'])) : '';
                                            
                                            echo '<div class="activity-item d-flex align-items-center">
                                                <span class="badge ' . $badge_class . ' me-2"><i class="fas ' . $icon . ' me-1"></i>' . ucfirst(str_replace('_', ' ', $activity['type'])) . '</span>
                                                <span class="flex-grow-1 small">' . $activity['activity'] . '</span>
                                                <small class="text-muted">' . $time_display . '</small>
                                            </div>';
                                        }
                                        
                                        if (!$has_activity) {
                                            echo '<div class="text-center text-muted py-3">No recent activity</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="glass-card p-4">
                                    <h5 class="text-dark mb-3">
                                        <i class="fas fa-cogs me-2 text-info"></i>Equipment Status
                                    </h5>
                                    <div class="equipment-status">
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                            <div>
                                                <strong>Total Equipment</strong><br>
                                                <small class="text-muted">All laboratory devices</small>
                                            </div>
                                            <span class="badge badge-primary"><?php echo $total_equipment; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                            <div>
                                                <strong>Operational</strong><br>
                                                <small class="text-muted">Ready for use</small>
                                            </div>
                                            <span class="badge badge-success"><?php echo $operational_equipment; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                            <div>
                                                <strong>Under Maintenance</strong><br>
                                                <small class="text-muted">Requires service</small>
                                            </div>
                                            <span class="badge badge-warning"><?php echo $maintenance_equipment; ?></span>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="#v-pills-equipment" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-cogs me-1"></i>Manage Equipment
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Statistics Breakdown -->
                        <div class="glass-card p-4 mt-4">
                            <h5 class="text-dark mb-3">
                                <i class="fas fa-chart-pie me-2"></i>Test Statistics Breakdown
                            </h5>
                            <div class="row text-center">
                                <div class="col-md-2">
                                    <div class="metric-card">
                                        <h3 class="text-warning"><?php echo $pending_tests; ?></h3>
                                        <small>Pending</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="metric-card">
                                        <h3 class="text-info"><?php echo $accepted_tests; ?></h3>
                                        <small>Accepted</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="metric-card">
                                        <h3 class="text-success"><?php echo $completed_tests; ?></h3>
                                        <small>Completed</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="metric-card">
                                        <h3 class="text-danger"><?php echo $emergency_tests; ?></h3>
                                        <small>Emergency</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="metric-card">
                                        <h3 class="text-warning"><?php echo $urgent_tests; ?></h3>
                                        <small>Urgent</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="metric-card">
                                        <h3 class="text-primary">₱<?php echo number_format($today_revenue, 2); ?></h3>
                                        <small>Today's Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-lab-tests" role="tabpanel" aria-labelledby="v-pills-lab-tests-tab">
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
                    <div class="tab-pane fade" id="v-pills-equipment" role="tabpanel" aria-labelledby="v-pills-equipment-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-cogs me-2"></i>Equipment Management
                            </h4>
                            <div class="equipment-status">
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong>Total Equipment</strong><br>
                                        <small class="text-muted">All laboratory devices</small>
                                    </div>
                                    <span class="badge badge-primary"><?php echo $total_equipment; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong>Operational</strong><br>
                                        <small class="text-muted">Ready for use</small>
                                    </div>
                                    <span class="badge badge-success"><?php echo $operational_equipment; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong>Under Maintenance</strong><br>
                                        <small class="text-muted">Requires service</small>
                                    </div>
                                    <span class="badge badge-warning"><?php echo $maintenance_equipment; ?></span>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="#v-pills-equipment" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-cogs me-1"></i>Manage Equipment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="v-pills-quality-control" role="tabpanel" aria-labelledby="v-pills-quality-control-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-check-circle me-2"></i>Quality Control
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>QC ID</th>
                                            <th>Test Name</th>
                                            <th>Control Level</th>
                                            <th>Result</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $quality_control_data = [
                                            ['id' => 1, 'test_name' => 'Glucose', 'control_level' => 'Level 1', 'result' => '5.2 mmol/L', 'date' => '2024-06-01', 'status' => 'Pass'],
                                            ['id' => 2, 'test_name' => 'Cholesterol', 'control_level' => 'Level 2', 'result' => '190 mg/dL', 'date' => '2024-06-01', 'status' => 'Pass'],
                                            ['id' => 3, 'test_name' => 'Hemoglobin', 'control_level' => 'Level 1', 'result' => '13.5 g/dL', 'date' => '2024-06-01', 'status' => 'Fail'],
                                            ['id' => 4, 'test_name' => 'Calcium', 'control_level' => 'Level 2', 'result' => '9.8 mg/dL', 'date' => '2024-06-01', 'status' => 'Pass'],
                                        ];
                                        foreach ($quality_control_data as $qc) {
                                            $status_class = ($qc['status'] == 'Pass') ? 'badge-success' : 'badge-danger';
                                            echo "<tr>
                                                <td>#{$qc['id']}</td>
                                                <td>{$qc['test_name']}</td>
                                                <td>{$qc['control_level']}</td>
                                                <td>{$qc['result']}</td>
                                                <td>{$qc['date']}</td>
                                                <td><span class='badge {$status_class}'>{$qc['status']}</span></td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="v-pills-reports" role="tabpanel" aria-labelledby="v-pills-reports-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-chart-bar me-2"></i>Reports
                            </h4>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6 col-lg-3">
                                    <div class="stat-card-enhanced" style="background: var(--primary-gradient);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h3><?php echo $total_tests; ?></h3>
                                                <p>Total Lab Tests</p>
                                            </div>
                                            <div class="icon-container">
                                                <i class="fas fa-vials"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="stat-card-enhanced" style="background: var(--secondary-gradient);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h3><?php echo $pending_tests; ?></h3>
                                                <p>Pending Tests</p>
                                            </div>
                                            <div class="icon-container">
                                                <i class="fas fa-hourglass-half"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="stat-card-enhanced" style="background: var(--warning-gradient);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h3><?php echo $accepted_tests; ?></h3>
                                                <p>Accepted Tests</p>
                                            </div>
                                            <div class="icon-container">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="stat-card-enhanced" style="background: var(--success-gradient);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h3><?php echo $completed_tests; ?></h3>
                                                <p>Completed Tests</p>
                                            </div>
                                            <div class="icon-container">
                                                <i class="fas fa-tasks"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="stat-card-enhanced" style="background: var(--success-gradient);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h3>₱<?php echo number_format($revenue, 2); ?></h3>
                                                <p>Total Revenue</p>
                                            </div>
                                            <div class="icon-container">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="glass-card p-3">
                                        <h5 class="text-dark mb-3">Priority Breakdown</h5>
                                        <table class="table table-glass table-hover table-striped mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Priority</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><span class="badge badge-danger">Emergency</span></td>
                                                    <td><?php echo $emergency_tests; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge badge-warning">Urgent</span></td>
                                                    <td><?php echo $urgent_tests; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge badge-secondary">Normal</span></td>
                                                    <td><?php echo $total_tests - $emergency_tests - $urgent_tests; ?></td>
                                                </tr>
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
        // Tab functionality
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
        $('#v-pills-dashboard').addClass('show active');

        // Fix for pointer events
        $('.sidebar').css('pointer-events', 'auto');
        $('.quick-action-btn').css('pointer-events', 'auto');
        
        // Modal functionality
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

        // Form validation
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