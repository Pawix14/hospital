<!DOCTYPE html>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('func1.php');
$pid='';
$ID='';
$appdate='';
$apptime='';
$fname = '';
$lname= '';
$doctor = '';
$is_nurse = false;
if (isset($_SESSION['dname'])) {
    $doctor = $_SESSION['dname'];
} elseif (isset($_SESSION['username'])) {
    $doctor = $_SESSION['username']; 
    $is_nurse = true;
} else {
    header("Location: index.php");
    exit();
}
if(isset($_GET['pid']) && isset($_GET['fname']) && isset($_GET['lname'])) {
    $pid = $_GET['pid'];
    $fname = $_GET['fname'];
    $lname = $_GET['lname'];
}

    if(isset($_POST['prescribe']) && isset($_POST['pid']) && isset($_POST['lname']) && isset($_POST['fname'])){
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $pid = $_POST['pid'];

if ($is_nurse) {
    $medicine_name = $_POST['medicine_name'];
    $quantity = $_POST['quantity'];
    $follow_up = $_POST['follow_up'];
    $symptoms = $_POST['symptoms'] ?? '';
    $price_query = "SELECT price FROM medicinetb WHERE medicine_name = '$medicine_name' LIMIT 1";
    $price_result = mysqli_query($con, $price_query);
    $price_row = mysqli_fetch_assoc($price_result);
    $price_per_unit = $price_row['price'] ?? 0;

    $price = $quantity * $price_per_unit;
    $allergy = "N/A";
    $prescription = $medicine_name;

    $query = mysqli_query($con, "INSERT INTO prestb(doctor, pid, fname, lname, symptoms, allergy, prescription, price) VALUES ('$doctor', '$pid', '$fname', '$lname', '$symptoms', '$allergy', '$prescription', '$price')");
    if ($query) {
        $update_qty_query = "UPDATE medicinetb SET quantity = quantity - $quantity WHERE medicine_name = '$medicine_name' AND quantity >= $quantity";
        mysqli_query($con, $update_qty_query);

        echo "<script>alert('Prescribed successfully!');</script>";
    } else {
        echo "<script>alert('Unable to process your request. Try again!');</script>";
    }
} else {
            $diagnosis_details = isset($_POST['diagnosis_details']) ? $_POST['diagnosis_details'] : '';
            $allergy = $_POST['allergy'];
            $medicines = $_POST['medicines'];
            $medicine_quantities = $_POST['medicine_quantities'];
            $price = $_POST['price'];

            $medicine_names = [];
            $dosages = [];
            $frequencies = [];
            $durations = [];
            $total_price = 0;
            $insufficient_stock = false;
            foreach ($medicines as $med_id) {
                $quantity_prescribed = isset($medicine_quantities[$med_id]) ? intval($medicine_quantities[$med_id]) : 0;
                $med_query = mysqli_query($con, "SELECT medicine_name, price, quantity FROM medicinetb WHERE id = '$med_id'");
                if ($med_row = mysqli_fetch_array($med_query)) {
                    if ($med_row['quantity'] < $quantity_prescribed) {
                        $insufficient_stock = true;
                        break;
                    }
                    $medicine_names[] = $med_row['medicine_name'];
                    $total_price += $med_row['price'] * $quantity_prescribed;
                }
                $dosages[] = isset($_POST['dosage'][$med_id]) ? $_POST['dosage'][$med_id] : '';
                $frequencies[] = isset($_POST['frequency'][$med_id]) ? $_POST['frequency'][$med_id] : '';
                $durations[] = isset($_POST['duration'][$med_id]) ? $_POST['duration'][$med_id] : '';
            }
            if ($insufficient_stock) {
                echo "<script>alert('Insufficient stock for one or more medicines. Please adjust quantities.');</script>";
            } else {
                $prescribed_medicines = implode(', ', $medicine_names);
                $dosage_str = implode(',', $dosages);
                $frequency_str = implode(',', $frequencies);
                $duration_str = implode(',', $durations);
                $symptoms = $_POST['symptoms'] ?? ''; 

                $query = mysqli_query($con, "INSERT INTO prestb(doctor, pid, fname, lname, allergy, prescription, price, symptoms, diagnosis_details, prescribed_medicines, dosage, frequency, duration) VALUES ('$doctor', '$pid', '$fname', '$lname', '$allergy', '$prescribed_medicines', '$total_price', '$symptoms', '$diagnosis_details', '$prescribed_medicines', '$dosage_str', '$frequency_str', '$duration_str')");
                if ($query) {
                    foreach ($medicines as $med_id) {
                        $quantity_prescribed = isset($medicine_quantities[$med_id]) ? intval($medicine_quantities[$med_id]) : 0;
                        $update_qty_query = "UPDATE medicinetb SET quantity = quantity - $quantity_prescribed WHERE id = '$med_id' AND quantity >= $quantity_prescribed";
                        mysqli_query($con, $update_qty_query);
                    }
                    $update_bill_query = "UPDATE billtb SET medicine_fees = medicine_fees + $total_price, total = consultation_fees + lab_fees + medicine_fees + service_charges + room_charges WHERE pid = '$pid'";
                    mysqli_query($con, $update_bill_query);

                    echo "<script>alert('Prescribed successfully!');</script>";
                } else {
                    echo "<script>alert('Unable to process your request. Try again!');</script>";
                }
            }
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans:400,500,600,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2c6bae;
            --primary-dark: #1a4c7e;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            font-family: 'IBM Plex Sans', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            padding-top: 70px;
        }
        
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .bg-primary {
            background: linear-gradient(120deg, var(--primary-dark), var(--primary)) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 24px;
        }
        
        .card-header {
            background: linear-gradient(120deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            color: var(--primary-dark);
            border-radius: 8px 8px 0 0 !important;
            padding: 15px 20px;
        }
        
        .form-control, .custom-select {
            border-radius: 6px;
            padding: 10px 15px;
            border: 1px solid #d1d3e2;
            transition: all 0.3s;
        }
        
        .form-control:focus, .custom-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(44, 107, 174, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(120deg, var(--primary-dark), var(--primary));
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(120deg, var(--primary), var(--primary-dark));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .patient-info {
            background-color: #e8f4ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }
        
        .section-title {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e3e6f0;
        }
        
        .price-display {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-radius: 6px;
            font-weight: 500;
            color: var(--primary-dark);
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
            border-radius: 6px;
        }
        
        .medication-item {
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            border-left: 3px solid var(--primary);
        }
        
        .total-price-container {
            background: linear-gradient(120deg, #f8f9fa, #e9ecef);
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }
        
        .required-field::after {
            content: "*";
            color: var(--danger);
            margin-left: 4px;
        }
        
        .welcome-header {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .welcome-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0 15px;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }
    </style>
    
    <title>Medical Prescription | Madridano Hospital</title>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout1.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $is_nurse ? 'nurse-panel.php' : 'doctor-panel.php'; ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top:20px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h3 class="welcome-header">Welcome, Dr. <?php echo $doctor ?></h3>
                
                <div class="patient-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Patient ID:</strong> <?php echo $pid ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Patient Name:</strong> <?php echo $fname . ' ' . $lname ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="list-pres" role="tabpanel" aria-labelledby="list-pres-list">
                    <?php
                    if ($is_nurse) {
                        $check_query = "SELECT * FROM prestb WHERE pid='$pid' AND doctor != '$doctor' LIMIT 1";
                        $check_result = mysqli_query($con, $check_query);
                        if (mysqli_num_rows($check_result) == 0) {
                            echo "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i> Doctor must prescribe first before nurse can prescribe.</div>";
                        } else {
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-medkit"></i> Nurse Prescription
                        </div>
                        <div class="card-body">
                            <form class="form-group" name="prescribeform" method="post" action="prescribe.php">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="required-field">Medicine Name:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="medicine_name" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="required-field">Quantity:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control" name="quantity" min="1" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="required-field">Price per Unit ($):</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="number" step="0.01" min="0" class="form-control" name="price_per_unit" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label>Follow-up Medicines:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <textarea cols="86" rows="3" name="follow_up" class="form-control" placeholder="Enter any follow-up medication instructions"></textarea>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="fname" value="<?php echo $fname ?>" />
                                <input type="hidden" name="lname" value="<?php echo $lname ?>" />
                                <input type="hidden" name="pid" value="<?php echo $pid ?>" />
                                
                                <div class="text-center mt-4">
                                    <input type="submit" name="prescribe" value="Submit Prescription" class="btn btn-primary btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-file-text"></i> Doctor Prescription Form
                        </div>
                        <div class="card-body">
                            <form class="form-group" name="prescribeform" method="post" action="prescribe.php">
                    <!-- Removed Symptoms & Diagnosis section as per user request -->
                    <!--
                    <h5 class="section-title"><i class="fa fa-stethoscope"></i> Symptoms & Diagnosis</h5>
                                
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="required-field">Symptoms:</label>
                        </div>
                        <div class="col-md-9">
<textarea id="symptoms" cols="86" rows="3" name="symptoms" class="form-control" placeholder="e.g. Fever, cough, headache" required></textarea>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="required-field">Diagnosis Details:</label>
                        </div>
                        <div class="col-md-9">
<textarea id="diagnosis_details" cols="86" rows="4" name="diagnosis_details" class="form-control" placeholder="e.g. Viral infection, mild dehydration" required></textarea>
                        </div>
                    </div>
                    -->

                                <h5 class="section-title"><i class="fa fa-heartbeat"></i> Patient Information</h5>
                                
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="required-field">Known Allergies:</label>
                                    </div>
                                    <div c  lass="col-md-9">
                                        <select id="allergy" name="allergy" class="form-control" required>
                                            <option value="">Select Allergy</option>
                                            <option value="None">None</option>
                                            <option value="Penicillin">Penicillin</option>
                                            <option value="Aspirin">Aspirin</option>
                                            <option value="Latex">Latex</option>
                                            <option value="Pollen">Pollen</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <h5 class="section-title"><i class="fa fa-medkit"></i> Medication</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="required-field">Prescribe Medicines:</label>
                                    </div>
                                    <div class="col-md-9">
                                <div id="medicine-quantity-container">
                                    <?php
$medicine_query = "SELECT id, medicine_name, price, quantity, medicine_type FROM medicinetb";
$medicine_result = mysqli_query($con, $medicine_query);
$modern_prices = [
    'Paracetamol' => 10.50,
    'Ibuprofen' => 15.75,
    'Amoxicillin' => 15.20,
    'Aspirin' => 30.40,
    'Insulin' => 45.00,
    'Vitamin D' => 25.30,
    'Antihistamine' => 50.60,
    'Cough Syrup' => 70.00,
    'Bandages' => 30.10,
    'Antiseptic Cream' => 20.50
];
$default_dosages = [
    'Paracetamol' => '500mg',
    'Ibuprofen' => '200mg',
    'Amoxicillin' => '500mg',
    'Aspirin' => '75mg',
    'Insulin' => '10 units',
    'Vitamin D' => '1000 IU',
    'Antihistamine' => '10mg',
    'Cough Syrup' => '10ml',
    'Bandages' => '',
    'Antiseptic Cream' => ''
];
$default_frequencies = [
    'Paracetamol' => '3 times a day',
    'Ibuprofen' => '3 times a day',
    'Amoxicillin' => '2 times a day',
    'Aspirin' => '1 time a day',
    'Insulin' => 'As needed',
    'Vitamin D' => '1 time a day',
    'Antihistamine' => '2 times a day',
    'Cough Syrup' => '3 times a day',
    'Bandages' => '',
    'Antiseptic Cream' => ''
];
while ($med = mysqli_fetch_array($medicine_result)) {
    $price = $med['price'];
    if (array_key_exists($med['medicine_name'], $modern_prices)) {
        $price = $modern_prices[$med['medicine_name']];
    }
        $disabled = ($med['quantity'] <= 0) ? 'disabled' : '';
        $out_of_stock_label = ($med['quantity'] <= 0) ? ' <span class="text-danger font-weight-bold">(Out of Stock)</span>' : '';
        $show_dosage_fields = ($med['medicine_type'] == 'oral') ? '' : 'style="display:none;"';
        echo '<div class="form-group row align-items-center mb-2">';
        echo '<div class="col-md-4">';
        $default_dosage = $default_dosages[$med['medicine_name']] ?? '';
        $default_frequency = $default_frequencies[$med['medicine_name']] ?? '';
        echo '<input type="checkbox" class="form-check-input medicine-checkbox" id="med_' . $med['id'] . '" name="medicines[]" value="' . $med['id'] . '" data-price="' . $price . '" data-name="' . htmlspecialchars($med['medicine_name']) . '" data-dosage="' . $default_dosage . '" data-frequency="' . $default_frequency . '" ' . $disabled . '>';
        echo '<label class="form-check-label" for="med_' . $med['id'] . '">' . htmlspecialchars($med['medicine_name']) . ' - ₱' . number_format($price, 2) . ' (Available: ' . $med['quantity'] . ')' . $out_of_stock_label . '</label>';
        echo '</div>';
        echo '<div class="col-md-2">';
        echo '<input type="number" class="form-control medicine-quantity" name="medicine_quantities[' . $med['id'] . ']" min="1" max="' . $med['quantity'] . '" value="1" disabled placeholder="Qty" ' . $disabled . '>';
        echo '</div>';
        echo '<div class="col-md-2" ' . $show_dosage_fields . '>';
        echo '<input type="text" class="form-control" name="dosage[' . $med['id'] . ']" placeholder="Dosage" value="' . $default_dosage . '" disabled ' . $disabled . '>';
        echo '</div>';
        echo '<div class="col-md-2" ' . $show_dosage_fields . '>';
        echo '<input type="text" class="form-control" name="frequency[' . $med['id'] . ']" placeholder="Frequency" value="' . $default_frequency . '" disabled ' . $disabled . '>';
        echo '</div>';
        echo '<div class="col-md-2" ' . $show_dosage_fields . '>';
        echo '<input type="text" class="form-control" name="duration[' . $med['id'] . ']" placeholder="Duration" disabled ' . $disabled . '>';
        echo '</div>';
        echo '</div>';
}
                                    ?>
                                </div>
                                <small class="form-text text-muted">Select medicines and specify quantities.</small>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label>Selected Medications:</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div id="selected-medications" class="p-2">
                                            <p class="text-muted">No medications selected</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="required-field">Total Price:</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="total-price-container">
                                            ₱<span id="total-price">0.00</span>
                                        </div>
                                        <input type="hidden" id="total_price" name="price" value="0" required />
                                    </div>
                                </div>

                                <input type="hidden" name="fname" value="<?php echo $fname ?>" />
                                <input type="hidden" name="lname" value="<?php echo $lname ?>" />
                                <input type="hidden" name="appdate" value="<?php echo $appdate ?>" />
                                <input type="hidden" name="apptime" value="<?php echo $apptime ?>" />
                                <input type="hidden" name="pid" value="<?php echo $pid ?>" />
                                <input type="hidden" name="ID" value="<?php echo $ID ?>" />
                                
                                <div class="text-center mt-4">
                                    <input type="submit" name="prescribe" value="Submit Prescription" class="btn btn-primary btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const medicineCheckboxes = document.querySelectorAll('.medicine-checkbox');
            const totalPriceElement = document.getElementById('total-price');
            const totalPriceInput = document.getElementById('total_price');
            const selectedMedicationsContainer = document.getElementById('selected-medications');

            function updateMedicationSummary() {
                let total = 0;
                let medicationsHTML = '';
                medicineCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const medId = checkbox.value;
                        const medName = checkbox.getAttribute('data-name');
                        const price = parseFloat(checkbox.getAttribute('data-price'));
                        const quantityInput = document.querySelector('input[name="medicine_quantities[' + medId + ']"]');
                        const quantity = quantityInput ? parseInt(quantityInput.value) : 0;
                        const medTotal = price * quantity;
                        total += medTotal;

                        medicationsHTML += `
                            <div class="medication-item">
                                <div class="d-flex justify-content-between">
                                    <span>${medName} x ${quantity}</span>
                                    <span>₱${medTotal.toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                    }
                });

                if (medicationsHTML === '') {
                    medicationsHTML = '<p class="text-muted">No medications selected</p>';
                }

                selectedMedicationsContainer.innerHTML = medicationsHTML;
                totalPriceElement.textContent = total.toFixed(2);
                totalPriceInput.value = total.toFixed(2);
            }

            medicineCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const medId = this.value;
                    const quantityInput = document.querySelector('input[name="medicine_quantities[' + medId + ']"]');
                    const dosageInput = document.querySelector('input[name="dosage[' + medId + ']"]');
                    const frequencyInput = document.querySelector('input[name="frequency[' + medId + ']"]');
                    const durationInput = document.querySelector('input[name="duration[' + medId + ']"]');
                    if (this.checked) {
                        quantityInput.disabled = false;
                        dosageInput.disabled = false;
                        dosageInput.value = this.getAttribute('data-dosage');
                        frequencyInput.disabled = false;
                        frequencyInput.value = this.getAttribute('data-frequency');
                        durationInput.disabled = false;
                    } else {
                        quantityInput.disabled = true;
                        quantityInput.value = 1;
                        dosageInput.disabled = true;
                        dosageInput.value = '';
                        frequencyInput.disabled = true;
                        frequencyInput.value = '';
                        durationInput.disabled = true;
                        durationInput.value = '';
                    }
                    updateMedicationSummary();
                });
            });

            const quantityInputs = document.querySelectorAll('.medicine-quantity');
            quantityInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value < 1) this.value = 1;
                    updateMedicationSummary();
                });
            });

            updateMedicationSummary();
        });
    </script>
</body>
</html>