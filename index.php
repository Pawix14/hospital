<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madridano Health Care Hospital - Advanced Healthcare </title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style1.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.37);
            --shadow-heavy: 0 15px 35px rgba(31, 38, 135, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            animation: grain 20s linear infinite;
        }

        @keyframes grain {
            0%, 100% { transform: translate(0, 0); }
            10% { transform: translate(-5%, -5%); }
            20% { transform: translate(-10%, 5%); }
            30% { transform: translate(5%, -10%); }
            40% { transform: translate(-5%, 15%); }
            50% { transform: translate(-10%, 5%); }
            60% { transform: translate(15%, 0%); }
            70% { transform: translate(0%, 10%); }
            80% { transform: translate(-15%, 0%); }
            90% { transform: translate(10%, 5%); }
        }
        .navbar-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            padding: 10px 20px !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .navbar-nav .nav-link:hover::before {
            left: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            z-index: 2;
            position: relative;
        }

        .hero-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            font-weight: 300;
            line-height: 1.6;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
        }

        .glass-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-heavy);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .login-tabs {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 5px;
            margin-bottom: 2rem;
        }

        .login-tabs .nav-link {
            color: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-tabs .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .login-tabs .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .login-tabs .nav-link:hover::before {
            left: 100%;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .alert-patient {
            position: fixed;
            top: 100px;
            right: 30px;
            z-index: 9999;
            min-width: 350px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            display: none;
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 40px;
            height: 40px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .glass-card {
                margin: 20px 10px;
            }
            
            .alert-patient {
                right: 10px;
                min-width: 300px;
            }
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            transition: opacity 0.5s ease;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        var check = function() {
            if (document.getElementById('password').value ==
                document.getElementById('cpassword').value) {
                document.getElementById('message').style.color = '#5dd05d';
                document.getElementById('message').innerHTML = 'Matched';
            } else {
                document.getElementById('message').style.color = '#f55252';
                document.getElementById('message').innerHTML = 'Not Matching';
            }
        }

        function alphaOnly(event) {
            var key = event.keyCode;
            return ((key >= 65 && key <= 90) || key == 8 || key == 32);
        };

        function checklen() {
            var pass1 = document.getElementById("password");  
            if(pass1.value.length<6){  
                alert("Password must be at least 6 characters long. Try again!");  
                return false;  
            }  
        }
        
        function showPatientAlert() {
            var alertDiv = document.getElementById('patientAlert');
            alertDiv.style.display = 'block';
            setTimeout(function() {
                alertDiv.style.display = 'none';
            }, 5000);
            
            return false;
        }
    </script>
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    <div class="bg-animation"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="alert alert-info alert-dismissible fade show alert-patient" role="alert" id="patientAlert">
        <strong><i class="fas fa-info-circle me-2"></i>Patient Registration Notice</strong><br>
        Patient registration is now handled by nurses in the Nurse Panel.<br>
        Please Go To the nurse to register a new patient.
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <nav class="navbar navbar-expand-lg fixed-top navbar-glass" data-aos="fade-down">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hospital-symbol me-2"></i>
                <span>MADRIDANO HOSPITALS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.html">
                            <i class="fas fa-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link register-patient" onclick="return showPatientAlert();">
                            <i class="fas fa-user-plus me-1"></i>Register Patient
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">

                <div class="col-lg-6 hero-content" data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up" data-aos-delay="200">
                        Welcome to <span class="text-gradient">Madridano Health Care Hospital</span>
                    </h1>
                    <h1 class="hero-title">
                        Advanced Healthcare
                        <span class="d-block" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Billing System</span>
                    </h1>
                    <p class="lead mb-5" data-aos="fade-up" data-aos-delay="400">
                        Experience world-class healthcare with our advanced management system at Madridano Health Care Hospital. 
                        Seamless patient care, cutting-edge technology, and compassionate service for your well-being.
                    </p>
<<<<<<< HEAD
                    <div class="d-flex gap-3 flex-wrap">
                        <button class="btn btn-primary btn-lg" onclick="document.getElementById('loginSection').scrollIntoView({behavior: 'smooth'});">
                            <i class="fas fa-sign-in-alt me-2"></i>Get Started
                        </button>
                    </div>
=======
>>>>>>> a5c017c (Initial project setup with updated files)
                </div>
                
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <div class="glass-card p-4" id="loginSection">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                                 style="width: 80px; height: 80px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                <i class="fas fa-hospital-symbol fa-2x text-white"></i>
                            </div>
                            <h3 class="text-white fw-bold mb-2">Welcome Back</h3>
                            <p class="text-white-50">Choose your role to access the system</p>
                        </div>
                        
                        <ul class="nav nav-pills nav-justified login-tabs" id="loginTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="patient-tab" data-bs-toggle="pill" data-bs-target="#patient" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>Patient
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="nurse-tab" data-bs-toggle="pill" data-bs-target="#nurse" type="button" role="tab">
                                    <i class="fas fa-user-nurse me-2"></i>Nurse
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="doctor-tab" data-bs-toggle="pill" data-bs-target="#doctor" type="button" role="tab">
                                    <i class="fas fa-user-md me-2"></i>Doctor
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="lab-tab" data-bs-toggle="pill" data-bs-target="#lab" type="button" role="tab">
                                    <i class="fas fa-flask me-2"></i>Lab
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin" type="button" role="tab">
                                    <i class="fas fa-user-shield me-2"></i>Admin
                                </button>
                            </li>
                        </ul>
                        <!-- Tab Content -->
                        <div class="tab-content mt-4" id="loginTabContent">
                            <!-- Patient Login -->
                            <div class="tab-pane fade show active" id="patient" role="tabpanel">
                                <form method="post" action="func.php" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0" placeholder="Enter your email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" placeholder="Enter your password" name="password2" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="patsub" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login as Patient
                                    </button>
                                </form>
                                <div class="text-center">
                                    <p class="text-white-50 small mb-2">Don't have an account?</p>
                                    <a href="#" onclick="return showPatientAlert();" class="text-white text-decoration-none">
                                        <i class="fas fa-user-plus me-1"></i>Contact a nurse for registration
                                    </a>
                                </div>
                            </div>

                            <!-- Nurse Login -->
                            <div class="tab-pane fade" id="nurse" role="tabpanel">
                                <form method="post" action="func.php" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" placeholder="Enter username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" placeholder="Enter password" name="password" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="nurse_login" class="btn btn-primary w-100">
                                        <i class="fas fa-user-nurse me-2"></i>Login as Nurse
                                    </button>
                                </form>
                            </div>

                            <!-- Doctor Login -->
                            <div class="tab-pane fade" id="doctor" role="tabpanel">
                                <form method="post" action="func1.php" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-user-md"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" placeholder="Enter username" name="username3" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" placeholder="Enter password" name="password3" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="docsub1" class="btn btn-primary w-100">
                                        <i class="fas fa-stethoscope me-2"></i>Login as Doctor
                                    </button>
                                </form>
                            </div>

                            <!-- Lab User Login -->
                            <div class="tab-pane fade" id="lab" role="tabpanel">
                                <form method="post" action="func.php" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-flask"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" placeholder="Enter username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" placeholder="Enter password" name="password" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="lab_login" class="btn btn-primary w-100">
                                        <i class="fas fa-microscope me-2"></i>Login as Lab User
                                    </button>
                                </form>
                            </div>

                            <!-- Admin Login -->
                            <div class="tab-pane fade" id="admin" role="tabpanel">
                                <form method="post" action="func.php" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-user-shield"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" placeholder="Enter admin username" name="username1" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0" style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" placeholder="Enter admin password" name="password2" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="adsub" class="btn btn-primary w-100">
                                        <i class="fas fa-crown me-2"></i>Login as Admin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Hide loading overlay after page loads
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 500);
        });
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Enhanced navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-glass');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.15)';
                navbar.style.backdropFilter = 'blur(25px)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.1)';
                navbar.style.backdropFilter = 'blur(20px)';
            }
        });
    </script>
</body>
</html>
