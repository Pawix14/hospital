<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Validate token
    $query = "SELECT * FROM emergency_access_logs 
              WHERE one_time_token = '$token' 
              AND token_expires > NOW() 
              AND auto_login_used = 0 
              AND status = 'approved'";
    
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $request = mysqli_fetch_assoc($result);
        
        // Mark token as used
        mysqli_query($con, "UPDATE emergency_access_logs SET auto_login_used = 1 WHERE one_time_token = '$token'");
        
        // Set session variables for auto-login
        $_SESSION['username'] = $request['staff_username'];
        $_SESSION['role'] = $request['staff_role'];
        $_SESSION['emergency_login'] = true; // Flag to indicate emergency login
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Redirect to appropriate panel
        header("Location: {$request['staff_role']}-panel.php");
        exit();
    } else {
        // Invalid or expired token
        header("Location: index.php?error=invalid_emergency_token");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>