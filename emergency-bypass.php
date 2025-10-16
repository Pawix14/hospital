<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
$prefill_username = isset($_SESSION['2fa_username']) ? $_SESSION['2fa_username'] : '';

if (isset($_POST['emergency_request'])) {
    $username = $_POST['username'];
    $reason = $_POST['reason'];
    $contact = $_POST['contact'];
    $additional_info = $_POST['additional_info'] ?? '';
    $role = $_SESSION['2fa_role'] ?? '';
    if (!$role) {
        $tables = ['adminusertb', 'doctortb', 'nursetb', 'labtb'];
        foreach ($tables as $table) {
            $result = mysqli_query($con, "SELECT * FROM $table WHERE username = '$username'");
            if (mysqli_num_rows($result) > 0) {
                $role = str_replace('tb', '', $table); 
                break;
            }
        }
    }
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO emergency_access_logs (staff_username, staff_role, reason, contact_info, additional_info, ip_address) 
              VALUES ('$username', '$role', '$reason', '$contact', '$additional_info', '$ip_address')";
    
    if (mysqli_query($con, $query)) {
        $success_message = "✅ Emergency request submitted successfully! ";
        $success_message .= "Administrators have been notified and will review your request shortly.";
        $success_message .= "<br><strong>You will be contacted at: $contact</strong>";
        $success_message .= "<br><small class='text-white-50'>Please wait for administrator approval. You will be able to login without 2FA once approved.</small>";
        
    } else {
        $error = "❌ Failed to submit emergency request. Please try again or call administration directly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Access Request - Madridano Hospital</title>
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
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }
        .alert-emergency {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid #ffc107;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <div class="text-center mb-4">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h2 class="text-white">Emergency Access Request</h2>
            <p class="text-white-50">Use this form if you cannot receive 2FA verification codes</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-light">Return to Login</a>
            </div>
        <?php else: ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="alert alert-emergency mb-4">
                <strong><i class="fas fa-info-circle me-2"></i>Important:</strong> 
                This form should only be used for genuine emergencies. Administrators will verify your identity before granting one-time 2FA bypass.
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-2"></i>Your Username</label>
                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($prefill_username); ?>" required 
                           placeholder="Enter your system username">
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-phone me-2"></i>Contact Information</label>
                    <input type="text" class="form-control" name="contact" 
                           placeholder="Phone number or alternative email where we can contact you" required>
                    <div class="form-text text-white-50">We will use this to verify your identity and contact you</div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-exclamation-circle me-2"></i>Reason for Emergency Access</label>
                    <select class="form-control" name="reason" required onchange="toggleAdditionalInfo(this)">
                        <option value="">Select the reason...</option>
                        <option value="Not receiving verification codes">Not receiving verification codes by email</option>
                        <option value="Lost access to email">Lost access to registered email account</option>
                        <option value="Urgent patient care required">Urgent patient care situation</option>
                        <option value="System emergency">Critical system issue requiring immediate access</option>
                        <option value="Other">Other reason</option>
                    </select>
                </div>

                <div class="mb-3" id="additionalInfoSection" style="display: none;">
                    <label class="form-label"><i class="fas fa-info-circle me-2"></i>Additional Information</label>
                    <textarea class="form-control" name="additional_info" rows="3" 
                              placeholder="Please provide more details about your situation..."></textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="acknowledge" required>
                        <label class="form-check-label" for="acknowledge">
                            I understand that this is for emergency use only and allows one-time 2FA bypass
                        </label>
                    </div>
                </div>

                <button type="submit" name="emergency_request" class="btn btn-warning w-100 btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>Submit Emergency Request
                </button>
            </form>

            <div class="mt-4 text-center">
                <small class="text-white-50">
                    <i class="fas fa-clock me-1"></i>Administrators typically respond within 15-30 minutes during business hours
                </small>
                <br>
                <small class="text-white-50">
                    For immediate assistance, call: <strong>+63-9940213443</strong>
                </small>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAdditionalInfo(select) {
            const additionalSection = document.getElementById('additionalInfoSection');
            if (select.value === 'Other') {
                additionalSection.style.display = 'block';
            } else {
                additionalSection.style.display = 'none';
            }
        }
        document.querySelector('form').addEventListener('submit', function(e) {
            const acknowledge = document.getElementById('acknowledge');
            if (!acknowledge.checked) {
                e.preventDefault();
                alert('Please acknowledge that this is for emergency use only.');
                acknowledge.focus();
            }
        });
    </script>
</body>
</html>