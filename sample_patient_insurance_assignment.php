<?php
// Sample PHP code for patient insurance assignment during admission
// This would typically be integrated into the nurse's admission form

session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if nurse is logged in (assuming nurse role)
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'nurse') {
    header("Location: index.php");
    exit();
}

$nurse_username = $_SESSION['username'];
if (isset($_POST['admit_patient_with_insurance'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $gender = $_POST['gender'];
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $age = $_POST['age'];
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $blood_group = $_POST['blood_group'];
    $emergency_contact = mysqli_real_escape_string($con, $_POST['emergency_contact']);
    $emergency_contact_name = mysqli_real_escape_string($con, $_POST['emergency_contact_name']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $admission_date = date('Y-m-d');

    // Insert patient into admissiontb
    $admission_query = "INSERT INTO admissiontb (fname, lname, gender, email, contact, age, address, blood_group, emergency_contact, emergency_contact_name, password, admission_date, status) VALUES ('$fname', '$lname', '$gender', '$email', '$contact', '$age', '$address', '$blood_group', '$emergency_contact', '$emergency_contact_name', '$password', '$admission_date', 'Admitted')";

    if (mysqli_query($con, $admission_query)) {
        $patient_id = mysqli_insert_id($con); // Get the auto-generated patient ID

        // Insurance assignment data
        $insurance_id = $_POST['insurance_id'];
        $policy_number = mysqli_real_escape_string($con, $_POST['policy_number']);
        $coverage_percent = $_POST['coverage_percent'];
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

        // Insert insurance assignment
        $insurance_query = "INSERT INTO patient_insurancetb (patient_id, insurance_id, policy_number, coverage_percent, start_date, end_date, status) VALUES ('$patient_id', '$insurance_id', '$policy_number', '$coverage_percent', '$start_date', " . ($end_date ? "'$end_date'" : "NULL") . ", 'active')";

        if (mysqli_query($con, $insurance_query)) {
            echo "<script>alert('Patient admitted and insurance assigned successfully!');</script>";
        } else {
            echo "<script>alert('Patient admitted but insurance assignment failed: " . mysqli_error($con) . "');</script>";
        }
    } else {
        echo "<script>alert('Error admitting patient: " . mysqli_error($con) . "');</script>";
    }
}

// Fetch available insurance companies for dropdown
$insurance_query = "SELECT insurance_id, company_name FROM insurance_companiestb WHERE status = 'active' ORDER BY company_name";
$insurance_result = mysqli_query($con, $insurance_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Admission with Insurance - Madridano Health Care Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Patient Admission with Insurance Assignment</h2>
        <form method="POST">
            <!-- Patient Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Patient Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="fname" name="fname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lname" name="lname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="blood_group" class="form-label">Blood Group</label>
                            <select class="form-control" id="blood_group" name="blood_group" required>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insurance Assignment -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Insurance Assignment</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="insurance_id" class="form-label">Insurance Company</label>
                            <select class="form-control" id="insurance_id" name="insurance_id" required>
                                <option value="">Select Insurance Company</option>
                                <?php while ($insurance = mysqli_fetch_array($insurance_result)): ?>
                                    <option value="<?php echo $insurance['insurance_id']; ?>">
                                        <?php echo $insurance['company_name']; ?>
                                    </option>
                                <?php endwhile; ?>
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
                    </div>
                </div>
            </div>

            <button type="submit" name="admit_patient_with_insurance" class="btn btn-primary">Admit Patient with Insurance</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
mysqli_close($con);
?>
