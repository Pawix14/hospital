<?php
// test_smtp.php

// SMTP Configuration for Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pmadridano2@gmail.com');  // Your Gmail
define('SMTP_PASSWORD', 'tqcr wgip cdzk ufow'); // ← YOU NEED TO GET THIS
define('SMTP_FROM_EMAIL', 'no-reply@madridanohospital.com');
define('SMTP_FROM_NAME', 'Madridano Hospital');
define('SMTP_AUTH', true);
define('SMTP_SECURE', 'tls');

// Include PHPMailer
require_once('PHPMailer/PHPMailer.php');
require_once('PHPMailer/SMTP.php');
require_once('PHPMailer/Exception.php');

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

echo "<h2>SMTP Email Test</h2>";

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress('pmadridano2@gmail.com', 'Test User');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test - Madridano Hospital';
    $mail->Body    = '<h1>SMTP Test Successful!</h1><p>If you receive this, SMTP is working correctly.</p>';
    $mail->AltBody = 'SMTP Test Successful! If you receive this, SMTP is working correctly.';
    
    $mail->send();
    echo "<h2 style='color: green;'>✅ SUCCESS: SMTP Email Sent!</h2>";
    echo "<p>Check your email inbox at pmadridano2@gmail.com</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ FAILED: " . $e->getMessage() . "</h2>";
    echo "<p><strong>Most common issue:</strong> You need to generate a Gmail App Password</p>";
}
?>