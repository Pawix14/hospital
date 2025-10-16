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
$validation_errors = [];
$form_data = [];
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
    $insurance_company = !empty($_POST['insurance_company']) ? mysqli_real_escape_string($con, $_POST['insurance_company']) : NULL;
    $policy_number = !empty($_POST['policy_number']) ? mysqli_real_escape_string($con, $_POST['policy_number']) : NULL;
    $coverage_percent = (float)($_POST['coverage_percent'] ?? 0);
    $effective_date = !empty($_POST['effective_date']) ? mysqli_real_escape_string($con, $_POST['effective_date']) : NULL;
    $expiry_date = !empty($_POST['expiry_date']) ? mysqli_real_escape_string($con, $_POST['expiry_date']) : NULL;
    $admission_date = date("Y-m-d");
    $admission_time = date("H:i:s");
    $form_data = [
        'fname' => $fname,
        'lname' => $lname,
        'gender' => $gender,
        'email' => $email,
        'contact' => $contact,
        'age' => $age,
        'address' => $address,
        'reason' => $reason,
        'assigned_doctor' => $assigned_doctor,
        'room_number' => $room_number,
        'insurance_company' => $insurance_company,
        'policy_number' => $policy_number,
        'coverage_percent' => $coverage_percent,
        'effective_date' => $effective_date,
        'expiry_date' => $expiry_date
    ];
    if (empty($email)) {
        $validation_errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validation_errors['email'] = "Please enter a valid email address.";
    } else {
        $email_check_query = "SELECT pid FROM admissiontb WHERE email = '$email'";
        $email_check_result = mysqli_query($con, $email_check_query);
        if (mysqli_num_rows($email_check_result) > 0) {
            $validation_errors['email'] = "This email is already registered. Please use a different email.";
        }
    }
    if (empty($password)) {
        $validation_errors['password'] = "Password is required.";
    } else {
        if (strlen($password) < 8) {
            $validation_errors['password'] = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $validation_errors['password'] = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $validation_errors['password'] = "Password must contain at least one lowercase letter.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $validation_errors['password'] = "Password must contain at least one number.";
        } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $validation_errors['password'] = "Password must contain at least one special character.";
        }
    }
    if (empty($cpassword)) {
        $validation_errors['cpassword'] = "Please confirm your password.";
    } elseif ($password !== $cpassword) {
        $validation_errors['cpassword'] = "Passwords do not match.";
    }
    if (empty($contact)) {
        $validation_errors['contact'] = "Contact number is required.";
    } elseif (!preg_match('/^(09|\+639)\d{9}$/', $contact)) {
        $validation_errors['contact'] = "Please enter a valid Philippine mobile number (09XXXXXXXXX or +639XXXXXXXXX).";
    }
    if ($age < 1 || $age > 120) {
        $validation_errors['age'] = "Please enter a valid age (1-120).";
    }
    $required_fields = [
        'fname' => 'First name',
        'lname' => 'Last name',
        'gender' => 'Gender',
        'assigned_doctor' => 'Assigned doctor',
        'room_number' => 'Room number',
        'address' => 'Address',
        'reason' => 'Reason for admission'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            $validation_errors[$field] = "$label is required.";
        }
    }
    if (empty($validation_errors)) {
        $room_check_query = "SELECT pid FROM admissiontb WHERE room_number = '$room_number' AND status = 'Admitted'";
        $room_check_result = mysqli_query($con, $room_check_query);

        if (mysqli_num_rows($room_check_result) > 0) {
            $validation_errors['room_number'] = "Room $room_number is already occupied. Please select a different room.";
        }
    }
    if (!empty($insurance_company)) {
        if (empty($policy_number)) {
            $validation_errors['policy_number'] = "Policy number is required when insurance is selected.";
        }
        if ($coverage_percent <= 0 || $coverage_percent > 100) {
            $validation_errors['coverage_percent'] = "Coverage percent must be between 1 and 100 when insurance is selected.";
        }
        if (!empty($effective_date) && !empty($expiry_date) && $effective_date > $expiry_date) {
            $validation_errors['expiry_date'] = "Expiry date must be after effective date.";
        }
    }
    if (empty($validation_errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $reg_query = "INSERT INTO admissiontb (fname, lname, gender, email, contact, password, admission_date, assigned_doctor, room_number, age, address, reason, status) VALUES ('$fname', '$lname', '$gender', '$email', '$contact', '$hashed_password', '$admission_date', '$assigned_doctor', '$room_number', $age, '$address', '$reason', 'Admitted')";
        
        if (mysqli_query($con, $reg_query)) {
            $pid = mysqli_insert_id($con);
            $insurance_insertion_success = true;
            if (!empty($insurance_company) && !empty($policy_number) && $coverage_percent > 0) {
                $insurance_query = "INSERT INTO patient_insurancetb (patient_id, insurance_id, policy_number, coverage_percent, start_date, end_date, status) VALUES ('$pid', '$insurance_company', '$policy_number', '$coverage_percent', '$effective_date', " . ($expiry_date ? "'$expiry_date'" : "NULL") . ", 'active')";

                if (!mysqli_query($con, $insurance_query)) {
                    $insurance_insertion_success = false;
                    error_log("Insurance insertion failed: " . mysqli_error($con));
                }
            }
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
            $insurance_covered = 0;
            $patient_payable = $total;

            if ($insurance_insertion_success && $coverage_percent > 0) {
                $insurance_covered = $total * ($coverage_percent / 100);
                $patient_payable = $total - $insurance_covered;
            }

            $bill_query = "INSERT INTO billtb (pid, consultation_fees, room_charges, lab_fees, medicine_fees, service_charges, total, insurance_covered, patient_payable, status) VALUES ('$pid', '$consultation_fee', '$room_charge', 0, 0, 0, '$total', '$insurance_covered', '$patient_payable', 'Unpaid')";

            if (mysqli_query($con, $bill_query)) {
                $insurance_message = "";
                if (!empty($insurance_company) && $insurance_insertion_success) {
                    if (is_array($insurance_companies) && !empty($insurance_companies)) {
                        $key = array_search($insurance_company, array_column($insurance_companies, 'insurance_id'));
                        if ($key !== false) {
                            $company_name = $insurance_companies[$key]['company_name'];
                            $insurance_message = "\nInsurance Assigned: {$company_name} ({$coverage_percent}% coverage)";
                        } else {
                            $insurance_message = "\nInsurance Assigned: Unknown Company ({$coverage_percent}% coverage)";
                        }
                    } else {
                        $insurance_message = "\nInsurance Assigned: Company details unavailable ({$coverage_percent}% coverage)";
                    }
                } elseif (!empty($insurance_company) && !$insurance_insertion_success) {
                    $insurance_message = "\nWarning: Insurance assignment failed, but patient was registered.";
                }

                $_SESSION['success_message'] = "Patient registered and admitted successfully!\nPatient ID: $pid\nRoom: $room_number\nAssigned Doctor: $assigned_doctor\nPatient can now login with email: $email" . $insurance_message;
                $form_data = [];
                 header("Location: nurse-panel.php#register-patient");
            exit();
            } else {
                error_log("Billing insertion failed: " . mysqli_error($con));
                echo "<script>alert('Patient registered but billing setup failed. Please contact administrator.\nPatient ID: $pid\nPatient can now login with email: $email');</script>";
            }
        } else {
            echo "<script>alert('Error registering patient: " . mysqli_error($con) . "');</script>";
        }
    } else {
        $error_messages = implode("\\n", $validation_errors);
        echo "<script>alert('Please fix the following errors:\\n$error_messages');</script>";
    }
}
if (isset($_POST['add_medicine'])) {
    $medicine_name = mysqli_real_escape_string($con, $_POST['medicine_name']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $dosage = mysqli_real_escape_string($con, $_POST['dosage']);
    $frequency = mysqli_real_escape_string($con, $_POST['frequency']);
    $duration = mysqli_real_escape_string($con, $_POST['duration']);
    $medicine_type = mysqli_real_escape_string($con, $_POST['medicine_type']);

    $query = "INSERT INTO medicinetb (medicine_name, quantity, added_by_nurse, price, dosage, frequency, duration, medicine_type) VALUES ('$medicine_name', $quantity, '$nurse', $price, '$dosage', '$frequency', '$duration', '$medicine_type')";
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
if (isset($_POST['update_medicine_details'])) {
    $medicine_id = (int)$_POST['medicine_id'];
    $new_quantity = (int)$_POST['edit_quantity'];
    $new_dosage = mysqli_real_escape_string($con, $_POST['edit_dosage']);
    $new_frequency = mysqli_real_escape_string($con, $_POST['edit_frequency']);
    $new_duration = mysqli_real_escape_string($con, $_POST['edit_duration']);
    $new_medicine_type = mysqli_real_escape_string($con, $_POST['edit_medicine_type']);

    if ($new_quantity < 0) {
        echo "<script>alert('Quantity cannot be negative.');</script>";
    } else {
        $query = "UPDATE medicinetb SET quantity = $new_quantity, dosage = '$new_dosage', frequency = '$new_frequency', duration = '$new_duration', medicine_type = '$new_medicine_type' WHERE id = $medicine_id";
        if (mysqli_query($con, $query)) {
            echo "<script>alert('Medicine details updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating medicine details: " . mysqli_error($con) . "');</script>";
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

<<<<<<< HEAD
// Medication Administration: Mark as Given
if (isset($_POST['mark_as_given'])) {
    $prescription_id = (int)($_POST['prescription_id'] ?? 0);
    if ($prescription_id > 0) {
        $nurse_username = mysqli_real_escape_string($con, $nurse);
        $update_sql = "UPDATE prestb SET status='Given', given_by='$nurse_username', given_date=NOW() WHERE id=$prescription_id AND status='Dispensed'";
        if (mysqli_query($con, $update_sql)) {
            echo "<script>alert('Medication marked as Given successfully!');</script>";
        } else {
            $err = mysqli_error($con);
            echo "<script>alert('Failed to mark as Given: " . addslashes($err) . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid prescription ID.');</script>";
    }
}

=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
// Store rounds data for modals
$today_rounds_data = [];
$today = date('Y-m-d');
$rounds_query = "SELECT pr.*, a.fname, a.lname 
                 FROM patient_roundstb pr 
                 JOIN admissiontb a ON pr.pid = a.pid 
                 WHERE pr.round_date = '$today' 
                 ORDER BY pr.round_time";
$rounds_result = mysqli_query($con, $rounds_query);
while ($round = mysqli_fetch_array($rounds_result)) {
    $today_rounds_data[] = $round;
}

// Store medicine data for modals
$medicine_data = [];
$medicine_table = "medicinetb";
$medicine_query = "SELECT * FROM $medicine_table ORDER BY medicine_name";
$medicine_result = mysqli_query($con, $medicine_query);
if ($medicine_result) {
    while ($row = mysqli_fetch_array($medicine_result)) {
        $medicine_data[] = $row;
    }
}

// Get room occupancy data
$rooms_data = [];
$all_rooms = ['101', '102', '103', '201', '202', '203', '301', '302', '401', '402'];
foreach ($all_rooms as $room) {
    $room_query = "SELECT a.pid, a.fname, a.lname, a.admission_date, a.status, a.assigned_doctor 
                   FROM admissiontb a 
                   WHERE a.room_number = '$room' AND a.status = 'Admitted' 
                   ORDER BY a.admission_date DESC 
                   LIMIT 1";
    $room_result = mysqli_query($con, $room_query);
    
    $room_info = [
        'room_number' => $room,
        'is_occupied' => false,
        'patient_info' => null
    ];
    
    if ($room_result && mysqli_num_rows($room_result) > 0) {
        $room_info['is_occupied'] = true;
        $room_info['patient_info'] = mysqli_fetch_assoc($room_result);
    }
    
    $rooms_data[] = $room_info;
}

// Get insurance companies
$insurance_companies = [];
$insurance_query = "SELECT insurance_id, company_name FROM insurance_companiestb";
$insurance_result = mysqli_query($con, $insurance_query);
while ($insurance = mysqli_fetch_array($insurance_result)) {
    $insurance_companies[] = $insurance;
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
<<<<<<< HEAD
            --primary-blue: #007bff;
            --light-blue: #e3f2fd;
            --dark-blue: #0056b3;
            --accent-blue: #42a5f5;
            --background-blue: #f8f9fa;
            --card-background: #ffffff;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --primary-gradient: var(--primary-blue);
            --secondary-gradient: var(--accent-blue);
            --warning-gradient: #ffc107;
            --success-gradient: #28a745;
        }

        body {
            background-color: var(--background-blue);
            font-family: 'Inter', sans-serif;
        }

        .navbar-glass {
            background-color: var(--primary-blue) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border-bottom: 1px solid var(--dark-blue);
=======
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
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: white !important;
        }

        .glass-card {
<<<<<<< HEAD
            background-color: var(--card-background);
            border: 1px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
=======
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            transition: all 0.3s ease;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
        }

        .glass-card:hover {
            transform: translateY(-5px);
        }

<<<<<<< HEAD
        .stat-card-enhanced {
            border-radius: 10px;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card-enhanced h3 {
            font-size: 2rem;
            font-weight: 600;
        }

        .stat-card-enhanced p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .icon-container {
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .sidebar {
            background-color: var(--card-background);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .nav-link {
            color: var(--text-dark) !important;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .nav-link.active {
            background-color: var(--primary-blue);
            color: white !important;
        }

        .nav-link:hover {
            background-color: var(--light-blue);
            color: var(--primary-blue) !important;
=======
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
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
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
<<<<<<< HEAD
=======
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-header h2 {
<<<<<<< HEAD
            color: var(--primary-blue);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
=======
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        .welcome-header p {
            color: rgba(255, 255, 255, 0.8);
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
        }
        
        .round-status-scheduled { background-color: #fff3cd; }
        .round-status-completed { background-color: #d4edda; }
        .round-status-missed { background-color: #f8d7da; }
        
        .room-occupied { background-color: #f8d7da; }
        .room-available { background-color: #d4edda; }
        
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
        
        .room-card {
            transition: all 0.3s ease;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /* Enhanced Professional Styles */
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
<<<<<<< HEAD
            background-color: var(--light-blue);
            border: 1px solid var(--accent-blue);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }

        .metric-card h3 {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .metric-card small {
            color: var(--text-muted);
=======
            padding: 15px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
            text-align: center;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
        }

        .activity-item {
            padding: 10px;
<<<<<<< HEAD
            border-left: 3px solid var(--primary-blue);
            background-color: var(--light-blue);
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid var(--accent-blue);
=======
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
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
        }

        .stat-card-enhanced .d-flex {
            align-items: center;
            justify-content: space-between;
        }

        .stat-card-enhanced .icon-container {
<<<<<<< HEAD
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
=======
            font-size: 2.5rem;
            opacity: 0.9;
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
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
<<<<<<< HEAD
<style>
#v-pills-medicine-management { display: none !important; }
</style>
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
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
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-dashboard-tab" data-toggle="pill" href="#v-pills-dashboard" role="tab" aria-controls="v-pills-dashboard" aria-selected="true">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" id="v-pills-register-patient-tab" data-toggle="pill" href="#v-pills-register-patient" role="tab" aria-controls="v-pills-register-patient" aria-selected="false">
                            <i class="fas fa-hospital-user me-2"></i>Register Patient
                        </a>
                        <a class="nav-link" id="v-pills-patient-list-tab" data-toggle="pill" href="#v-pills-patient-list" role="tab" aria-controls="v-pills-patient-list" aria-selected="false">
                            <i class="fas fa-users me-2"></i>Patient List
                        </a>
<<<<<<< HEAD
                                                <a class="nav-link" id="v-pills-patient-rounds-tab" data-toggle="pill" href="#v-pills-patient-rounds" role="tab" aria-controls="v-pills-patient-rounds" aria-selected="false">
=======
                        <a class="nav-link" id="v-pills-medicine-management-tab" data-toggle="pill" href="#v-pills-medicine-management" role="tab" aria-controls="v-pills-medicine-management" aria-selected="false">
                            <i class="fas fa-pills me-2"></i>Manage Medicines
                        </a>
                        <a class="nav-link" id="v-pills-patient-rounds-tab" data-toggle="pill" href="#v-pills-patient-rounds" role="tab" aria-controls="v-pills-patient-rounds" aria-selected="false">
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
                            <i class="fas fa-clock me-2"></i>Schedule Rounds
                        </a>
                        <a class="nav-link" id="v-pills-room-management-tab" data-toggle="pill" href="#v-pills-room-management" role="tab" aria-controls="v-pills-room-management" aria-selected="false">
                            <i class="fas fa-bed me-2"></i>Room Management
                        </a>
<<<<<<< HEAD
                        <a class="nav-link" id="v-pills-med-admin-tab" data-toggle="pill" href="#v-pills-med-admin" role="tab" aria-controls="v-pills-med-admin" aria-selected="false">
                            <i class="fas fa-syringe me-2"></i>Medication Administration
                        </a>
=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
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
                                    <a href="#v-pills-register-patient" class="btn btn-outline-primary btn-sm quick-action-btn">
                                        <i class="fas fa-hospital-user me-1"></i>Register Patient
                                    </a>
                                </div>
<<<<<<< HEAD
                                                                <div class="col-auto">
=======
                                <div class="col-auto">
                                    <a href="#v-pills-medicine-management" class="btn btn-outline-success btn-sm quick-action-btn">
                                        <i class="fas fa-pills me-1"></i>Manage Medicines
                                    </a>
                                </div>
                                <div class="col-auto">
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
                                    <a href="#v-pills-patient-rounds" class="btn btn-outline-warning btn-sm quick-action-btn">
                                        <i class="fas fa-clock me-1"></i>Schedule Rounds
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="#v-pills-room-management" class="btn btn-outline-info btn-sm quick-action-btn">
                                        <i class="fas fa-bed me-1"></i>Room Management
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--primary-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3>
                                                <?php 
                                                $query = mysqli_query($con, "SELECT COUNT(*) as total FROM admissiontb");
                                                $row = mysqli_fetch_assoc($query);
                                                echo $row['total'] ?? 0;
                                                ?>
                                            </h3>
                                            <p>Total Admissions</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-hospital-user"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">All-time registrations <i class="fas fa-chart-line trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--secondary-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3>
                                                <?php 
                                                $query = mysqli_query($con, "SELECT COUNT(*) as total FROM admissiontb WHERE status='Admitted'");
                                                $row = mysqli_fetch_assoc($query);
                                                echo $row['total'] ?? 0;
                                                ?>
                                            </h3>
                                            <p>Active Patients</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-user-injured"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">Currently admitted <i class="fas fa-procedures trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--warning-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3>
                                                <?php 
                                                $query = mysqli_query($con, "SELECT COUNT(*) as total FROM medicinetb");
                                                $row = mysqli_fetch_assoc($query);
                                                echo $row['total'] ?? 0;
                                                ?>
                                            </h3>
                                            <p>Available Medicines</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-pills"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">In inventory <i class="fas fa-capsules trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card-enhanced" style="background: var(--success-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3>
                                                <?php 
                                                $query = mysqli_query($con, "SELECT COUNT(DISTINCT room_number) as total FROM admissiontb WHERE status='Admitted'");
                                                $row = mysqli_fetch_assoc($query);
                                                echo $row['total'] ?? 0;
                                                ?>
                                            </h3>
                                            <p>Occupied Rooms</p>
                                        </div>
                                        <div class="icon-container">
                                            <i class="fas fa-bed"></i>
                                        </div>
                                    </div>
                                    <small class="text-white-50">Currently in use <i class="fas fa-door-closed trend-indicator trend-up"></i></small>
                                </div>
                            </div>
                        </div>
                        <div class="glass-card p-4 mb-4">
                            <h5 class="text-dark mb-3">Today's Overview</h5>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-primary">
                                            <?php
                                            $today = date('Y-m-d');
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM admissiontb WHERE DATE(admission_date) = '$today'");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <small>New Admissions Today</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-info">
                                            <?php
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM patient_roundstb WHERE round_date = '$today'");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <small>Rounds Scheduled</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-warning">
                                            <?php
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM patient_roundstb WHERE round_date = '$today' AND status = 'Completed'");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <small>Rounds Completed</small>
                                    </div>
                                </div>
<<<<<<< HEAD
                                                            </div>
=======
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3 class="text-success">
                                            <?php
                                            $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM medicinetb WHERE quantity < 10");
                                            $row = mysqli_fetch_assoc($result);
                                            echo $row['total'];
                                            ?>
                                        </h3>
                                        <small>Low Stock Medicines</small>
                                    </div>
                                </div>
                            </div>
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
                        </div>

                        <div class="row g-4">
                            <!-- Today's Rounds -->
                            <div class="col-lg-8">
                                <div class="glass-card p-4 h-100">
                                    <h5 class="text-dark mb-3">
                                        <i class="fas fa-clock me-2"></i>Today's Scheduled Rounds
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-glass">
                                            <thead>
                                                <tr>
                                                    <th>Patient</th>
                                                    <th>Scheduled Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                mysqli_data_seek($rounds_result, 0);
                                                $has_rounds = false;
                                                while ($round = mysqli_fetch_array($rounds_result)) {
                                                    $has_rounds = true;
                                                    $status_class = "";
                                                    $status_badge = "";
                                                    if ($round['status'] == 'Completed') {
                                                        $status_class = "round-status-completed";
                                                        $status_badge = "success";
                                                    } elseif ($round['status'] == 'Missed') {
                                                        $status_class = "round-status-missed";
                                                        $status_badge = "danger";
                                                    } else {
                                                        $status_class = "round-status-scheduled";
                                                        $status_badge = "warning";
                                                    }
                                                    
                                                    echo "<tr class='$status_class'>
                                                        <td>
                                                            <strong>ID: {$round['pid']}</strong><br>
                                                            <small>{$round['fname']} {$round['lname']}</small>
                                                        </td>
                                                        <td>{$round['round_time']}</td>
                                                        <td><span class='badge badge-$status_badge'>{$round['status']}</span></td>
                                                        <td>
                                                            <button type='button' class='btn btn-info btn-sm' data-toggle='modal' data-target='#updateRoundModal{$round['id']}'>
                                                                <i class='fas fa-edit'></i> Update
                                                            </button>
                                                            <a href='nurse-panel.php?delete_round={$round['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this round?\")'>
                                                                <i class='fas fa-trash'></i>
                                                            </a>
                                                        </td>
                                                    </tr>";
                                                }
                                                
                                                if (!$has_rounds) {
                                                    echo "<tr><td colspan='4' class='text-center text-muted py-4'>No rounds scheduled for today</td></tr>";
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
$activity_query = mysqli_query($con, '
    (SELECT \'admission\' as type, CONCAT(\'New admission: \', fname, \' \', lname) as activity, admission_date as date, \'00:00:00\' as time
     FROM admissiontb
     ORDER BY admission_date DESC
     LIMIT 3)
    UNION ALL
    (SELECT \'medicine\' as type, CONCAT(\'Medicine added: \', medicine_name) as activity, NULL as date, NULL as time
     FROM medicinetb
     ORDER BY id DESC
     LIMIT 3)
    UNION ALL
    (SELECT \'round\' as type, CONCAT(\'Round scheduled for patient ID: \', pid) as activity, round_date as date, round_time as time
     FROM patient_roundstb
     ORDER BY round_date DESC, round_time DESC
     LIMIT 3)
    ORDER BY date DESC, time DESC
    LIMIT 5
');
                                        
                                        while($activity = mysqli_fetch_array($activity_query)) {
                                            $badge_class = '';
                                            $icon = '';
                                            switch($activity['type']) {
                                                case 'admission':
                                                    $badge_class = 'bg-primary';
                                                    $icon = 'fa-user-plus';
                                                    break;
                                                case 'medicine':
                                                    $badge_class = 'bg-success';
                                                    $icon = 'fa-pills';
                                                    break;
                                                case 'round':
                                                    $badge_class = 'bg-warning';
                                                    $icon = 'fa-clock';
                                                    break;
                                            }
                                            
                                            echo '<div class="activity-item d-flex align-items-center">
                                                <span class="badge ' . $badge_class . ' me-2"><i class="fas ' . $icon . ' me-1"></i>' . ucfirst($activity['type']) . '</span>
                                                <span class="flex-grow-1">' . $activity['activity'] . '</span>
                                                <small class="text-muted">' . date('g:i A', strtotime($activity['time'])) . '</small>
                                            </div>';
                                        }
                                        
                                        if (mysqli_num_rows($activity_query) === 0) {
                                            echo '<div class="text-center text-muted py-3">No recent activity</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
<<<<<<< HEAD
                                                            </div>
=======
                                <div class="glass-card p-4">
                                    <h5 class="text-dark mb-3">
                                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock Alert
                                    </h5>
                                    <div class="low-stock-list">
                                        <?php
                                        $low_stock_query = mysqli_query($con, "
                                            SELECT medicine_name, quantity 
                                            FROM medicinetb 
                                            WHERE quantity < 10 
                                            ORDER BY quantity ASC 
                                            LIMIT 5
                                        ");                                       
                                        $low_stock_count = 0;
                                        while($medicine = mysqli_fetch_array($low_stock_query)) {
                                            $low_stock_count++;
                                            $stock_class = $medicine['quantity'] == 0 ? 'text-danger' : 'text-warning';
                                            $stock_text = $medicine['quantity'] == 0 ? 'Out of Stock' : 'Low Stock';
                                            
                                            echo '<div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                                <div>
                                                    <strong>' . $medicine['medicine_name'] . '</strong><br>
                                                    <small class="' . $stock_class . '">' . $stock_text . ' - ' . $medicine['quantity'] . ' remaining</small>
                                                </div>
                                                <a href="#v-pills-medicine-management" class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-sync-alt"></i>
                                                </a>
                                            </div>';
                                        }
                                        
                                        if ($low_stock_count === 0) {
                                            echo '<div class="text-center text-muted py-3">All medicines are well stocked</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-register-patient" role="tabpanel" aria-labelledby="v-pills-register-patient-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-hospital-user me-2"></i>Register New Patient
                            </h4>
                            <form class="form-group" method="post" action="nurse-panel.php#v-pills-register-patient" id="patientRegistrationForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fname">First Name:</label>
                                        <input type="text" class="form-control <?php echo isset($validation_errors['fname']) ? 'is-invalid' : ''; ?>" 
                                            name="fname" value="<?php echo htmlspecialchars($form_data['fname'] ?? ''); ?>" required>
                                        <?php if (isset($validation_errors['fname'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['fname']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lname">Last Name:</label>
                                        <input type="text" class="form-control <?php echo isset($validation_errors['lname']) ? 'is-invalid' : ''; ?>" 
                                            name="lname" value="<?php echo htmlspecialchars($form_data['lname'] ?? ''); ?>" required>
                                        <?php if (isset($validation_errors['lname'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['lname']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="gender">Gender:</label>
                                        <select name="gender" class="form-control <?php echo isset($validation_errors['gender']) ? 'is-invalid' : ''; ?>" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo (($form_data['gender'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (($form_data['gender'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                        <?php if (isset($validation_errors['gender'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['gender']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control <?php echo isset($validation_errors['email']) ? 'is-invalid' : ''; ?>" 
                                            name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                                        <?php if (isset($validation_errors['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['email']; ?></div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">We'll never share your email with anyone else.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact">Contact:</label>
                                        <input type="text" class="form-control <?php echo isset($validation_errors['contact']) ? 'is-invalid' : ''; ?>" 
                                            name="contact" value="<?php echo htmlspecialchars($form_data['contact'] ?? ''); ?>" 
                                            placeholder="09XXXXXXXXX or +639XXXXXXXXX" required>
                                        <?php if (isset($validation_errors['contact'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['contact']; ?></div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">Philippine mobile number format: 09XXXXXXXXX or +639XXXXXXXXX</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="age">Age:</label>
                                        <input type="number" class="form-control <?php echo isset($validation_errors['age']) ? 'is-invalid' : ''; ?>" 
                                            name="age" min="1" max="120" value="<?php echo htmlspecialchars($form_data['age'] ?? ''); ?>" required>
                                        <?php if (isset($validation_errors['age'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['age']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="address">Address:</label>
                                        <textarea class="form-control <?php echo isset($validation_errors['address']) ? 'is-invalid' : ''; ?>" 
                                                name="address" rows="3" required><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                                        <?php if (isset($validation_errors['address'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['address']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="reason">Reason for Admission:</label>
                                        <textarea class="form-control <?php echo isset($validation_errors['reason']) ? 'is-invalid' : ''; ?>" 
                                                name="reason" rows="3" placeholder="Describe the medical condition or reason for admission" required><?php echo htmlspecialchars($form_data['reason'] ?? ''); ?></textarea>
                                        <?php if (isset($validation_errors['reason'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['reason']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="insurance_company">Insurance Company (Optional):</label>
                                        <select name="insurance_company" class="form-control <?php echo isset($validation_errors['insurance_company']) ? 'is-invalid' : ''; ?>">
                                            <option value="">No Insurance</option>
                                            <?php foreach ($insurance_companies as $insurance): ?>
                                                <option value="<?php echo $insurance['insurance_id']; ?>" <?php echo (($form_data['insurance_company'] ?? '') == $insurance['insurance_id']) ? 'selected' : ''; ?>><?php echo $insurance['company_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($validation_errors['insurance_company'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['insurance_company']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="policy_number">Policy Number:</label>
                                        <input type="text" class="form-control <?php echo isset($validation_errors['policy_number']) ? 'is-invalid' : ''; ?>"
                                            name="policy_number" value="<?php echo htmlspecialchars($form_data['policy_number'] ?? ''); ?>">
                                        <?php if (isset($validation_errors['policy_number'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['policy_number']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="coverage_percent">Coverage Percent (0-100):</label>
                                        <input type="number" class="form-control <?php echo isset($validation_errors['coverage_percent']) ? 'is-invalid' : ''; ?>"
                                            name="coverage_percent" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($form_data['coverage_percent'] ?? ''); ?>">
                                        <?php if (isset($validation_errors['coverage_percent'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['coverage_percent']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="effective_date">Effective Date:</label>
                                        <input type="date" class="form-control <?php echo isset($validation_errors['effective_date']) ? 'is-invalid' : ''; ?>"
                                            name="effective_date" value="<?php echo htmlspecialchars($form_data['effective_date'] ?? ''); ?>">
                                        <?php if (isset($validation_errors['effective_date'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['effective_date']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_date">Expiry Date (Optional):</label>
                                        <input type="date" class="form-control <?php echo isset($validation_errors['expiry_date']) ? 'is-invalid' : ''; ?>"
                                            name="expiry_date" value="<?php echo htmlspecialchars($form_data['expiry_date'] ?? ''); ?>">
                                        <?php if (isset($validation_errors['expiry_date'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['expiry_date']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password">Password:</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control <?php echo isset($validation_errors['password']) ? 'is-invalid' : ''; ?>" 
                                                name="password" id="password" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <?php if (isset($validation_errors['password'])): ?>
                                                <div class="invalid-feedback"><?php echo $validation_errors['password']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <small class="form-text text-muted">
                                            Password must contain:
                                            <ul class="small pl-3 mb-0">
                                                <li id="length" class="text-muted">At least 8 characters</li>
                                                <li id="uppercase" class="text-muted">One uppercase letter</li>
                                                <li id="lowercase" class="text-muted">One lowercase letter</li>
                                                <li id="number" class="text-muted">One number</li>
                                                <li id="special" class="text-muted">One special character</li>
                                            </ul>
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cpassword">Confirm Password:</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control <?php echo isset($validation_errors['cpassword']) ? 'is-invalid' : ''; ?>" 
                                                name="cpassword" id="cpassword" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <?php if (isset($validation_errors['cpassword'])): ?>
                                                <div class="invalid-feedback"><?php echo $validation_errors['cpassword']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <small class="form-text text-muted" id="passwordMatchMessage"></small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="assigned_doctor">Assign Doctor:</label>
                                        <select name="assigned_doctor" id="assigned_doctor" class="form-control <?php echo isset($validation_errors['assigned_doctor']) ? 'is-invalid' : ''; ?>" required onchange="updateDoctorFee()">
                                            <option value="">Select Doctor</option>
                                            <?php
                                            $doctor_query = "SELECT username, consultation_fee FROM doctortb";
                                            $doctor_result = mysqli_query($con, $doctor_query);
                                            while ($doctor = mysqli_fetch_array($doctor_result)) {
                                                $selected = (($form_data['assigned_doctor'] ?? '') == $doctor['username']) ? 'selected' : '';
                                                echo "<option value='{$doctor['username']}' data-fee='{$doctor['consultation_fee']}' $selected>{$doctor['username']} - Fee: ₱{$doctor['consultation_fee']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <?php if (isset($validation_errors['assigned_doctor'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['assigned_doctor']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="room_number">Room Number:</label>
                                        <select name="room_number" id="room_number" class="form-control <?php echo isset($validation_errors['room_number']) ? 'is-invalid' : ''; ?>" required onchange="checkRoomAvailability()">
                                            <option value="">Select Room</option>
                                            <?php
                                            foreach ($rooms_data as $room) {
                                                $room_number = $room['room_number'];
                                                $room_type = "";
                                                $room_price = 0;
                                                
                                                if (strpos($room_number, '101') !== false || strpos($room_number, '102') !== false || strpos($room_number, '103') !== false) {
                                                    $room_type = "General Ward";
                                                    $room_price = 250;
                                                } elseif (strpos($room_number, '201') !== false || strpos($room_number, '202') !== false || strpos($room_number, '203') !== false) {
                                                    $room_type = "Private Room";
                                                    $room_price = 550;
                                                } elseif (strpos($room_number, '301') !== false || strpos($room_number, '302') !== false) {
                                                    $room_type = "ICU";
                                                    $room_price = 500;
                                                } elseif (strpos($room_number, '401') !== false || strpos($room_number, '402') !== false) {
                                                    $room_type = "Emergency";
                                                    $room_price = 400;
                                                }
                                                
                                                $disabled = $room['is_occupied'] ? 'disabled' : '';
                                                $status = $room['is_occupied'] ? ' (Occupied)' : ' (Available)';
                                                $selected = (($form_data['room_number'] ?? '') == $room_number) ? 'selected' : '';
                                                echo "<option value='$room_number' $disabled $selected data-price='$room_price'>Room $room_number - $room_type - ₱$room_price$status</option>";
                                            }
                                            ?>
                                        </select>
                                        <?php if (isset($validation_errors['room_number'])): ?>
                                            <div class="invalid-feedback"><?php echo $validation_errors['room_number']; ?></div>
                                        <?php endif; ?>
                                        <small id="roomAvailabilityMessage" class="form-text"></small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_fee">Doctor's Fee:</label>
                                        <input type="text" class="form-control" id="doctor_fee" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="room_charge">Room Charge:</label>
                                        <input type="text" class="form-control" id="room_charge" readonly>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" name="register_admit_patient" value="Register Patient" class="btn btn-primary" id="inputbtn">
                                    </div>
                                </div>
                            </form>

                            <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <strong>Success!</strong> <?php echo nl2br(htmlspecialchars($_SESSION['success_message'])); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Patient List Tab -->
                    <div class="tab-pane fade" id="v-pills-patient-list" role="tabpanel" aria-labelledby="v-pills-patient-list-tab">
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
                                            <th scope="col">Room Number</th>
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
                                                <td>{$row['room_number']}</td>
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
                    <div class="tab-pane fade" id="v-pills-medicine-management" role="tabpanel" aria-labelledby="v-pills-medicine-management-tab">
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
        <div class="col-md-3 mb-3">
            <label for="medicine_name">Medicine Name:</label>
            <input type="text" class="form-control" name="medicine_name" required>
        </div>
        <div class="col-md-2 mb-3">
            <label for="quantity">Quantity:</label>
            <input type="number" class="form-control" name="quantity" min="1" required>
        </div>
        <div class="col-md-2 mb-3">
            <label for="price">Price per Unit:</label>
            <input type="number" step="0.01" min="0" class="form-control" name="price" required>
        </div>
        <div class="col-md-2 mb-3">
            <label for="dosage">Dosage:</label>
            <input type="text" class="form-control" name="dosage" placeholder="e.g. 500mg">
        </div>
        <div class="col-md-2 mb-3">
            <label for="frequency">Frequency:</label>
            <input type="text" class="form-control" name="frequency" placeholder="e.g. Twice a day">
        </div>
        <div class="col-md-2 mb-3">
            <label for="duration">Duration:</label>
            <input type="text" class="form-control" name="duration" placeholder="e.g. 5 days">
        </div>
        <div class="col-md-3 mb-3">
            <label for="medicine_type">Medicine Type:</label>
            <select class="form-control" name="medicine_type" required>
                <option value="oral" selected>Oral</option>
                <option value="injection">Injection</option>
                <option value="topical">Topical</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <input type="submit" name="add_medicine" value="Add Medicine" class="btn btn-primary" id="inputbtn">
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
                                            if (!empty($medicine_table)) {
                                                mysqli_data_seek($medicine_result, 0);
                                                
                                                if ($medicine_result && mysqli_num_rows($medicine_result) > 0) {
                                                    echo '<table class="table table-hover table-striped">
                                                            <thead class="thead-dark">
                                                                <tr>
<th scope="col">ID</th>
<th scope="col">Medicine Name</th>
<th scope="col">Quantity</th>
<th scope="col">Dosage</th>
<th scope="col">Frequency</th>
<th scope="col">Duration</th>
<th scope="col">Medicine Type</th>
<th scope="col">Price per Unit</th>
<th scope="col">Added By</th>
<th scope="col">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>';
                                                    
while ($row = mysqli_fetch_array($medicine_result)) {
    $quantity_display = ($row['quantity'] == 0) ? '<span class="text-danger font-weight-bold">Out of Stock</span>' : $row['quantity'];
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['medicine_name']}</td>
            <td>{$quantity_display}</td>
            <td>{$row['dosage']}</td>
            <td>{$row['frequency']}</td>
            <td>{$row['duration']}</td>
            <td>{$row['medicine_type']}</td>
            <td>₱" . number_format($row['price'], 2) . "</td>
            <td>{$row['added_by_nurse']}</td>
            <td>
                <button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editMedicineModal{$row['id']}'>Edit Qty</button>
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
<<<<<<< HEAD
                    <!-- Medication Administration Tab -->
                    <div class="tab-pane fade" id="v-pills-med-admin" role="tabpanel" aria-labelledby="v-pills-med-admin-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-syringe me-2"></i>Medication Administration
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-glass">
                                    <thead>
                                        <tr>
                                            <th>Prescription ID</th>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Medicines</th>
                                            <th>Dosage</th>
                                            <th>Dispensed By</th>
                                            <th>Dispensed Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $dispensed_q = "SELECT p.*, a.fname, a.lname FROM prestb p JOIN admissiontb a ON p.pid = a.pid WHERE p.status = 'Dispensed' AND a.status = 'Admitted' ORDER BY p.dispensed_date DESC";
                                        $dispensed_res = mysqli_query($con, $dispensed_q);
                                        if ($dispensed_res && mysqli_num_rows($dispensed_res) > 0) {
                                            while ($row = mysqli_fetch_assoc($dispensed_res)) {
                                                ?>
                                                <tr>
                                                    <td>#<?php echo (int)$row['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                    <td>Dr. <?php echo htmlspecialchars($row['doctor']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['prescribed_medicines']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['dispensed_by']); ?></td>
                                                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($row['dispensed_date']))); ?></td>
                                                    <td>
                                                        <form method="POST" onsubmit="return confirm('Mark this medication as given to the patient?');" style="margin: 0; padding: 0;">
                                                            <input type="hidden" name="prescription_id" value="<?php echo (int)$row['id']; ?>">
                                                            <button type="submit" name="mark_as_given" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check me-1"></i>Mark as Given
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center text-muted py-4">No dispensed prescriptions pending administration.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

=======
>>>>>>> 988146efdeebdeb84e801caeb3930c961cd69516
                    <div class="tab-pane fade" id="v-pills-patient-rounds" role="tabpanel" aria-labelledby="v-pills-patient-rounds-tab">
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
                    <div class="tab-pane fade" id="v-pills-room-management" role="tabpanel" aria-labelledby="v-pills-room-management-tab">
                        <div class="glass-card p-4">
                            <h4 class="text-dark mb-4">
                                <i class="fas fa-bed me-2"></i>Room Management
                            </h4>
                            <div class="row">
                                <?php
                                $occupied_count = 0;
                                $available_count = 0;
                                
                                foreach ($rooms_data as $room) {
                                    $room_number = $room['room_number'];
                                    $is_occupied = $room['is_occupied'];
                                    $patient_info = $room['patient_info'];
                                    $room_type = "";
                                    $room_price = 0;
                                    $room_class = "";
                                    
                                    if (strpos($room_number, '101') !== false || strpos($room_number, '102') !== false || strpos($room_number, '103') !== false) {
                                        $room_type = "General Ward";
                                        $room_price = 250;
                                        $room_class = "primary";
                                    } elseif (strpos($room_number, '201') !== false || strpos($room_number, '202') !== false || strpos($room_number, '203') !== false) {
                                        $room_type = "Private Room";
                                        $room_price = 550;
                                        $room_class = "success";
                                    } elseif (strpos($room_number, '301') !== false || strpos($room_number, '302') !== false) {
                                        $room_type = "ICU";
                                        $room_price = 500;
                                        $room_class = "warning";
                                    } elseif (strpos($room_number, '401') !== false || strpos($room_number, '402') !== false) {
                                        $room_type = "Emergency";
                                        $room_price = 400;
                                        $room_class = "danger";
                                    }
                                    
                                    if ($is_occupied) {
                                        $occupied_count++;
                                        $status_class = "room-occupied";
                                        $status_text = "Occupied";
                                        $status_badge = "danger";
                                    } else {
                                        $available_count++;
                                        $status_class = "room-available";
                                        $status_text = "Available";
                                        $status_badge = "success";
                                    }
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card room-card <?php echo $status_class; ?>">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    Room <?php echo $room_number; ?>
                                                    <span class="badge badge-<?php echo $status_badge; ?> float-right"><?php echo $status_text; ?></span>
                                                </h5>
                                                <p class="card-text">
                                                    <strong>Type:</strong> <?php echo $room_type; ?><br>
                                                    <strong>Price:</strong> ₱<?php echo $room_price; ?>/day<br>
                                                    <?php if ($is_occupied && $patient_info): ?>
                                                        <strong>Patient:</strong> <?php echo $patient_info['fname'] . ' ' . $patient_info['lname']; ?><br>
                                                        <strong>Patient ID:</strong> <?php echo $patient_info['pid']; ?><br>
                                                        <strong>Admitted:</strong> <?php echo $patient_info['admission_date']; ?><br>
                                                        <strong>Doctor:</strong> <?php echo $patient_info['assigned_doctor']; ?>
                                                    <?php else: ?>
                                                        <strong>Patient:</strong> None<br>
                                                        <strong>Status:</strong> Ready for admission
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5>Room Summary</h5>
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-primary text-white rounded">
                                                        <h3><?php echo count($rooms_data); ?></h3>
                                                        <p>Total Rooms</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-success text-white rounded">
                                                        <h3><?php echo $available_count; ?></h3>
                                                        <p>Available Rooms</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-danger text-white rounded">
                                                        <h3><?php echo $occupied_count; ?></h3>
                                                        <p>Occupied Rooms</p>
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
            </div>
        </div>
    </div>
    <?php foreach($today_rounds_data as $round): ?>
    <div class="modal fade" id="updateRoundModal<?php echo $round['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateRoundModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateRoundModalLabel">Update Round for Patient ID: <?php echo $round['pid']; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="nurse-panel.php">
                    <div class="modal-body">
                        <input type="hidden" name="round_id" value="<?php echo $round['id']; ?>">
                        <div class="form-group">
                            <label for="vital_signs">Vital Signs:</label>
                            <textarea class="form-control" name="vital_signs" placeholder="Blood pressure, temperature, pulse, etc."><?php echo $round['vital_signs']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="update_notes">Notes:</label>
                            <textarea class="form-control" name="update_notes" placeholder="Observations and findings"><?php echo $round['notes']; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_round" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php foreach($medicine_data as $row): ?>
<div class="modal fade" id="editMedicineModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editMedicineModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMedicineModalLabel<?php echo $row['id']; ?>">Edit Medicine: <?php echo $row['medicine_name']; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="nurse-panel.php">
                <div class="modal-body">
                    <input type="hidden" name="medicine_id" value="<?php echo $row['id']; ?>">
                    <div class="form-group">
                        <label for="edit_quantity">New Quantity:</label>
                        <input type="number" class="form-control" name="edit_quantity" min="0" value="<?php echo $row['quantity']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_dosage">Dosage:</label>
                        <input type="text" class="form-control" name="edit_dosage" value="<?php echo htmlspecialchars($row['dosage']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_frequency">Frequency:</label>
                        <input type="text" class="form-control" name="edit_frequency" value="<?php echo htmlspecialchars($row['frequency']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_duration">Duration:</label>
                        <input type="text" class="form-control" name="edit_duration" value="<?php echo htmlspecialchars($row['duration']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_medicine_type">Medicine Type:</label>
                        <select class="form-control" name="edit_medicine_type" required>
                            <option value="oral" <?php if($row['medicine_type'] == 'oral') echo 'selected'; ?>>Oral</option>
                            <option value="injection" <?php if($row['medicine_type'] == 'injection') echo 'selected'; ?>>Injection</option>
                            <option value="topical" <?php if($row['medicine_type'] == 'topical') echo 'selected'; ?>>Topical</option>
                            <option value="other" <?php if($row['medicine_type'] == 'other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_medicine_details" class="btn btn-primary">Update Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

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
        $('#v-pills-dashboard').addClass('show active');
        $('.sidebar').css('pointer-events', 'auto');
        $('.quick-action-btn').css('pointer-events', 'auto');       
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
    function updateDoctorFee() {
        var select = document.getElementById('assigned_doctor');
        var feeInput = document.getElementById('doctor_fee');
        var selectedOption = select.options[select.selectedIndex];
        var fee = selectedOption.getAttribute('data-fee');
        feeInput.value = fee ? '₱' + fee : '';
    }
    function checkRoomAvailability() {
        var roomSelect = document.getElementById('room_number');
        var selectedOption = roomSelect.options[roomSelect.selectedIndex];
        var roomChargeInput = document.getElementById('room_charge');
        var messageElement = document.getElementById('roomAvailabilityMessage');
        
        if (selectedOption.disabled) {
            messageElement.innerHTML = '<span class="text-danger">This room is currently occupied. Please select an available room.</span>';
            roomChargeInput.value = '';
        } else {
            var price = selectedOption.getAttribute('data-price');
            roomChargeInput.value = price ? '₱' + price : '';
            messageElement.innerHTML = '<span class="text-success">This room is available for admission.</span>';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        checkRoomAvailability();
    });    
    function setupPasswordToggle(passwordFieldId, toggleButtonId) {
        const passwordField = document.getElementById(passwordFieldId);
        const toggleButton = document.getElementById(toggleButtonId);
        const eyeIcon = toggleButton.querySelector('i');
        
        toggleButton.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
                toggleButton.setAttribute('title', 'Hide password');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
                toggleButton.setAttribute('title', 'Show password');
            }
        });
    }   
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        for (const [key, met] of Object.entries(requirements)) {
            const element = document.getElementById(key);
            if (met) {
                element.classList.remove('text-muted');
                element.classList.add('text-success');
            } else {
                element.classList.remove('text-success');
                element.classList.add('text-muted');
            }
        }
    });    
    document.getElementById('cpassword').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        const messageElement = document.getElementById('passwordMatchMessage');
        
        if (confirmPassword === '') {
            messageElement.textContent = '';
            messageElement.className = 'form-text text-muted';
        } else if (password === confirmPassword) {
            messageElement.textContent = 'Passwords match!';
            messageElement.className = 'form-text text-success';
        } else {
            messageElement.textContent = 'Passwords do not match!';
            messageElement.className = 'form-text text-danger';
        }
    });   
    document.getElementById('patientRegistrationForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('cpassword').value;        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match. Please check your entries.');
            return false;
        }       
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };       
        const allMet = Object.values(requirements).every(met => met);
        if (!allMet) {
            e.preventDefault();
            alert('Please ensure your password meets all the requirements.');
            return false;
        }       
        return true;
    });   
    document.addEventListener('DOMContentLoaded', function() {
        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('cpassword', 'toggleConfirmPassword');       
        updateDoctorFee();
        checkRoomAvailability();        
        <?php if (!empty($validation_errors)): ?>
            document.getElementById('v-pills-register-patient-tab').click();
            window.location.hash = 'v-pills-register-patient';
        <?php endif; ?>
    });
    </script>
</body>
</html>