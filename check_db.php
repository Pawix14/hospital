<?php
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!$con) {
    echo "Database connection failed: " . mysqli_connect_error();
    exit;
}
echo "Database connected successfully\n\n";
$required_tables = ['admissiontb', 'billtb', 'diagnosticstb', 'doctortb', 'labtesttb', 'medicinetb', 'nursetb', 'patient_chargstb', 'paymentstb', 'servicestb'];
echo "=== TABLE CHECK ===\n";
foreach ($required_tables as $table) {
    $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "✓ Table $table exists\n";
    } else {
        echo "✗ Table $table missing\n";
    }
}
echo "\n=== DATA CHECK ===\n";
$tables_with_data = [
    'admissiontb' => 'SELECT COUNT(*) as count FROM admissiontb',
    'doctortb' => 'SELECT COUNT(*) as count FROM doctortb',
    'medicinetb' => 'SELECT COUNT(*) as count FROM medicinetb',
    'nursetb' => 'SELECT COUNT(*) as count FROM nursetb',
    'diagnosticstb' => 'SELECT COUNT(*) as count FROM diagnosticstb',
    'patient_chargstb' => 'SELECT COUNT(*) as count FROM patient_chargstb',
    'servicestb' => 'SELECT COUNT(*) as count FROM servicestb'
];

foreach ($tables_with_data as $table => $query) {
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "$table has {$row['count']} records\n";
    } else {
        echo "$table: Query failed - " . mysqli_error($con) . "\n";
    }
}

echo "\n=== SAMPLE PATIENT DATA ===\n";
$result = mysqli_query($con, "SELECT pid, fname, lname, email, password FROM admissiontb LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $has_password = !empty($row['password']) ? 'Yes' : 'No';
    echo "Patient {$row['pid']}: {$row['fname']} {$row['lname']} - Password: $has_password\n";
}

mysqli_close($con);
?>
