<?php
// 2fa_functions.php

function generate2FACode() {
    // Generate 6-digit code
    return sprintf("%06d", mt_rand(1, 999999));
}

function send2FAEmail($email, $code, $username) {
    include('smtp_config.php');
    
    require_once('PHPMailer/PHPMailer.php');
    require_once('PHPMailer/SMTP.php');
    require_once('PHPMailer/Exception.php');
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
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
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code - Madridano Hospital';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; color: #667eea; margin-bottom: 20px; }
                .code { font-size: 32px; font-weight: bold; color: #667eea; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 20px; font-size: 12px; color: #6c757d; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Madridano Health Care Hospital</h2>
                </div>
                <p>Hello <strong>$username</strong>,</p>
                <p>Your verification code for two-factor authentication is:</p>
                <div class='code'>$code</div>
                <p>This code will expire in <strong>10 minutes</strong>.</p>
                <p>If you didn't request this code, please contact your administrator immediately.</p>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        $mail->AltBody = "Madridano Hospital\nHello $username,\nYour verification code is: $code\nThis code will expire in 10 minutes.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function store2FACode($con, $username, $code, $role) {
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    switch($role) {
        case 'admin':
            $table = 'adminusertb';
            $id_field = 'username';
            break;
        case 'doctor':
            $table = 'doctortb';
            $id_field = 'username';
            break;
        case 'nurse':
            $table = 'nursetb';
            $id_field = 'username';
            break;
        case 'lab':
            $table = 'labtb';
            $id_field = 'username';
            break;
        default:
            return false;
    }
    
    $query = "UPDATE $table SET two_factor_code = '$code', two_factor_expires = '$expires' WHERE $id_field = '$username'";
    return mysqli_query($con, $query);
}

function validate2FACode($con, $username, $entered_code, $role) {
    switch($role) {
        case 'admin':
            $table = 'adminusertb';
            $id_field = 'username';
            break;
        case 'doctor':
            $table = 'doctortb';
            $id_field = 'username';
            break;
        case 'nurse':
            $table = 'nursetb';
            $id_field = 'username';
            break;
        case 'lab':
            $table = 'labtb';
            $id_field = 'username';
            break;
        default:
            return false;
    }
    
    $query = "SELECT two_factor_code, two_factor_expires FROM $table WHERE $id_field = '$username'";
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $stored_code = $row['two_factor_code'];
        $expires = $row['two_factor_expires'];
        if($stored_code && $stored_code === $entered_code && strtotime($expires) > time()) {
            mysqli_query($con, "UPDATE $table SET two_factor_code = NULL, two_factor_expires = NULL WHERE $id_field = '$username'");
            return true;
        }
    }
    
    return false;
}

function is2FAEnabled($con, $username, $role) {
    switch($role) {
        case 'admin':
            $table = 'adminusertb';
            $id_field = 'username';
            break;
        case 'doctor':
            $table = 'doctortb';
            $id_field = 'username';
            break;
        case 'nurse':
            $table = 'nursetb';
            $id_field = 'username';
            break;
        case 'lab':
            $table = 'labtb';
            $id_field = 'username';
            break;
        default:
            return false;
    }
    
    $query = "SELECT two_factor_enabled FROM $table WHERE $id_field = '$username'";
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        return $row['two_factor_enabled'] == 1;
    }
    
    return false;
}

function getUserEmail($con, $username, $role) {
    switch($role) {
        case 'admin':
            $query = "SELECT email FROM adminusertb WHERE username = '$username'";
            break;
        case 'doctor':
            $query = "SELECT email FROM doctortb WHERE username = '$username'";
            break;
        case 'nurse':
            $query = "SELECT email FROM nursetb WHERE username = '$username'";
            break;
        case 'lab':
            $query = "SELECT email FROM labtb WHERE username = '$username'";
            break;
        default:
            return null;
    }
    
    $result = mysqli_query($con, $query);
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        return $row['email'];
    }
    
    return null;
}

?>