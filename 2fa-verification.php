<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

if (!isset($_SESSION['2fa_pending']) || !$_SESSION['2fa_pending']) {
    header("Location: index.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("Database connection failed");
    die("System error. Please try again later.");
}

include('2fa_functions.php');

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_code'])) {
        $entered_code = trim($_POST['verification_code'] ?? '');
        $username = $_SESSION['2fa_username'] ?? '';
        $role = $_SESSION['2fa_role'] ?? '';
        
        if (empty($entered_code) || empty($username) || empty($role)) {
            $error = "Please enter the verification code.";
        } elseif (validate2FACode($con, $username, $entered_code, $role)) {
            // 2FA successful - complete login
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Clear 2FA session data
            unset($_SESSION['2fa_pending'], $_SESSION['2fa_username'], $_SESSION['2fa_role'], $_SESSION['2fa_attempts']);
            
            header("Location: {$role}-panel.php");
            exit();
        } else {
            $_SESSION['2fa_attempts'] = ($_SESSION['2fa_attempts'] ?? 0) + 1;
            $error = "Invalid verification code. Please try again.";
            
            // Lock after 3 failed attempts
            if ($_SESSION['2fa_attempts'] >= 3) {
                session_destroy();
                header("Location: index.php?error=2fa_locked");
                exit();
            }
        }
    }
    
    // Handle resend code
    if (isset($_POST['resend_code'])) {
        // Rate limiting: max 3 resends per session
        $_SESSION['resend_count'] = ($_SESSION['resend_count'] ?? 0) + 1;
        
        if ($_SESSION['resend_count'] > 3) {
            $error = "Too many resend attempts. Please contact administrator.";
        } else {
            $username = $_SESSION['2fa_username'] ?? '';
            $role = $_SESSION['2fa_role'] ?? '';
            
            if (!empty($username) && !empty($role)) {
                $code = generate2FACode();
                $email = getUserEmail($con, $username, $role);
                
                if ($email && store2FACode($con, $username, $code, $role) && send2FAEmail($email, $code, $username)) {
                    $success = "New verification code sent to your email!";
                } else {
                    $error = "Failed to send verification code. Please contact administrator.";
                }
            } else {
                $error = "Session error. Please login again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Madridano Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            color: white;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .attempts-warning {
            font-size: 0.9em;
            color: #ffcc00;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <div class="text-center mb-4">
            <i class="fas fa-shield-alt fa-3x mb-3 text-white"></i>
            <h2 class="text-white">Two-Factor Authentication</h2>
            <p class="text-white-50">Enter the verification code sent to your email</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" id="2faForm">
            <div class="mb-3">
                <label class="form-label text-white">Verification Code</label>
                <input type="text" class="form-control" name="verification_code" 
                       placeholder="Enter 6-digit code" required maxlength="6" 
                       pattern="[0-9]{6}" title="Please enter exactly 6 digits"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <div class="form-text text-white-50">Check your email for the 6-digit code</div>
            </div>
            
            <button type="submit" name="verify_code" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-check me-2"></i>Verify Code
            </button>
            
            <button type="submit" name="resend_code" class="btn btn-outline-light w-100">
                <i class="fas fa-redo me-2"></i>Resend Code
                <?php if (isset($_SESSION['resend_count']) && $_SESSION['resend_count'] > 0): ?>
                    <small>(<?php echo 3 - $_SESSION['resend_count']; ?> left)</small>
                <?php endif; ?>
            </button>
        </form>

        <?php if (isset($_SESSION['2fa_attempts']) && $_SESSION['2fa_attempts'] > 0): ?>
            <div class="attempts-warning text-center mt-3">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Attempts: <?php echo $_SESSION['2fa_attempts']; ?>/3
            </div>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="emergency-bypass.php" class="text-white">Contact Administrator (Emergency Access)</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit when 6 digits are entered
        document.querySelector('input[name="verification_code"]').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                document.getElementById('2faForm').querySelector('button[name="verify_code"]').focus();
            }
        });
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>