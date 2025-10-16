<?php
include('2fa_functions.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$con=mysqli_connect("localhost","root","","myhmsdb");
function shouldBypass2FA($con, $username) {
    $query = "SELECT id FROM emergency_access_logs 
              WHERE staff_username = '$username' 
              AND status = 'approved' 
              AND auto_login_used = 0
              LIMIT 1";
    
    $result = mysqli_query($con, $query);
    return mysqli_num_rows($result) > 0;
}

function useEmergencyAutoLogin($con, $username) {
    $query = "SELECT id FROM emergency_access_logs 
              WHERE staff_username = '$username' 
              AND status = 'approved' 
              AND auto_login_used = 0
              LIMIT 1";
    
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $request_id = $row['id'];
        mysqli_query($con, "UPDATE emergency_access_logs SET auto_login_used = 1 WHERE id = '$request_id'");
        
        return true;
    }
    
    return false;
}
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_found = false;
    $is_email = strpos($username, '@') !== false;
    if (!$user_found) {
        $query = "select * from adminusertb where username='$username' and password='$password';";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) == 1) {
            $user_found = true;
            $role = 'admin';
            if (shouldBypass2FA($con, $username)) {
                if (useEmergencyAutoLogin($con, $username)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $_SESSION['emergency_login'] = true;                    
                    session_regenerate_id(true);
                    header("Location: admin-panel.php");
                    exit();
                }
            }
            if (is2FAEnabled($con, $username, $role)) {
                $code = generate2FACode();
                $email = getUserEmail($con, $username, $role);
                
                if ($email && store2FACode($con, $username, $code, $role) && send2FAEmail($email, $code, $username)) {
                    $_SESSION['2fa_username'] = $username;
                    $_SESSION['2fa_role'] = $role;
                    $_SESSION['2fa_pending'] = true;
                    $_SESSION['2fa_attempts'] = 0;
                    header("Location: 2fa-verification.php");
                    exit();
                } else {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    header("Location: admin-panel.php");
                    exit();
                }
            } else {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: admin-panel.php");
                exit();
            }
        }
    }
    if (!$user_found && $is_email) {
        $query = "SELECT * FROM admissiontb WHERE email='$username'";
        $result = mysqli_query($con, $query);        
        error_log("Patient query: " . $query);
        error_log("Patient results: " . mysqli_num_rows($result));
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            error_log("Stored password: " . $row['password']);
            error_log("Submitted password: " . $password);           
            $password_valid = false;
            if (password_verify($password, $row['password'])) {
                $password_valid = true;
                error_log("Password verified via password_verify()");
            } 
            elseif ($row['password'] === $password) {
                $password_valid = true;
                error_log("Password verified via plain text comparison");
            }
            elseif (md5($password) === $row['password']) {
                $password_valid = true;
                error_log("Password verified via MD5");
            }
            elseif (sha1($password) === $row['password']) {
                $password_valid = true;
                error_log("Password verified via SHA1");
            }
            
            if($password_valid){
                $user_found = true;
                $_SESSION['pid'] = $row['pid'];
                $_SESSION['username'] = $row['fname']." ".$row['lname'];
                $_SESSION['fname'] = $row['fname'];
                $_SESSION['lname'] = $row['lname'];
                $_SESSION['gender'] = $row['gender'];
                $_SESSION['contact'] = $row['contact'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = 'patient';
                
                error_log("Patient login SUCCESS for: " . $row['email']);
                
                header("Location: patient-panel.php");
                exit();
            } else {
                error_log("Patient password INVALID for: " . $row['email']);
            }
        } else {
            error_log("No patient found with email: " . $username);
        }
    }
    if (!$user_found) {
        $query = "select * from nursetb where username='$username' and password='$password';";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) == 1) {
            $user_found = true;
            $role = 'nurse';
            if (shouldBypass2FA($con, $username)) {
                if (useEmergencyAutoLogin($con, $username)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $_SESSION['emergency_login'] = true;
                    
                    session_regenerate_id(true);
                    
                    header("Location: nurse-panel.php");
                    exit();
                }
            }
            if (is2FAEnabled($con, $username, $role)) {
                $code = generate2FACode();
                $email = getUserEmail($con, $username, $role);
                
                if ($email && store2FACode($con, $username, $code, $role) && send2FAEmail($email, $code, $username)) {
                    $_SESSION['2fa_username'] = $username;
                    $_SESSION['2fa_role'] = $role;
                    $_SESSION['2fa_pending'] = true;
                    $_SESSION['2fa_attempts'] = 0;
                    header("Location: 2fa-verification.php");
                    exit();
                } else {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    header("Location: nurse-panel.php");
                    exit();
                }
            } else {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: nurse-panel.php");
                exit();
            }
        }
    }
    if (!$user_found) {
        $query = "select * from doctortb where username='$username' and password='$password';";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) == 1) {
            $user_found = true;
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $role = $row['is_operating_doctor'] ? 'operating_doctor' : 'doctor';
            $_SESSION['doctor_id'] = $row['id'];

            if (shouldBypass2FA($con, $username)) {
                if (useEmergencyAutoLogin($con, $username)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $_SESSION['emergency_login'] = true;
                    session_regenerate_id(true);
                    header("Location: " . ($role == 'operating_doctor' ? 'operating-doctor-panel.php' : 'doctor-panel.php'));
                    exit();
                }
            }

            if (is2FAEnabled($con, $username, $role)) {
                $code = generate2FACode();
                $email = getUserEmail($con, $username, $role);

                if ($email && store2FACode($con, $username, $code, $role) && send2FAEmail($email, $code, $username)) {
                    $_SESSION['2fa_username'] = $username;
                    $_SESSION['2fa_role'] = $role;
                    $_SESSION['2fa_pending'] = true;
                    $_SESSION['2fa_attempts'] = 0;
                    header("Location: 2fa-verification.php");
                    exit();
                } else {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    header("Location: " . ($role == 'operating_doctor' ? 'operating-doctor-panel.php' : 'doctor-panel.php'));
                    exit();
                }
            } else {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: " . ($role == 'operating_doctor' ? 'operating-doctor-panel.php' : 'doctor-panel.php'));
                exit();
            }
        }
    }
    if (!$user_found) {
        $query = "select * from labtb where username='$username' and password='$password';";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) == 1) {
            $user_found = true;
            $role = 'lab';
            
            if (shouldBypass2FA($con, $username)) {
                if (useEmergencyAutoLogin($con, $username)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $_SESSION['emergency_login'] = true;
                    session_regenerate_id(true);
                    header("Location: lab-panel.php");
                    exit();
                }
            }
            
            if (is2FAEnabled($con, $username, $role)) {
                $code = generate2FACode();
                $email = getUserEmail($con, $username, $role);
                
                if ($email && store2FACode($con, $username, $code, $role) && send2FAEmail($email, $code, $username)) {
                    $_SESSION['2fa_username'] = $username;
                    $_SESSION['2fa_role'] = $role;
                    $_SESSION['2fa_pending'] = true;
                    $_SESSION['2fa_attempts'] = 0;
                    header("Location: 2fa-verification.php");
                    exit();
                } else {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    header("Location: lab-panel.php");
                    exit();
                }
            } else {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: lab-panel.php");
                exit();
            }
        }
    }
    if (!$user_found) {
        // Pharmacist login
        $query = "select * from pharmacisttb where username='$username';";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $password_valid = false;
            if (password_verify($password, $row['password'])) {
                $password_valid = true;
            } elseif ($row['password'] === $password) {
                $password_valid = true;
            }
            if ($password_valid) {
                $user_found = true;
                $role = 'pharmacist';
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['pharmacist_id'] = $row['id'];
                header("Location: pharmacist-panel.php");
                exit();
            }
        }
    }
    if (!$user_found) {
        $query = "select * from cashiertb where username='$username';";
        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $password_valid = false;
            if (password_verify($password, $row['password'])) {
                $password_valid = true;
            } elseif ($row['password'] === $password) {
                $password_valid = true;
            }
            if ($password_valid) {
                $user_found = true;
                $role = 'cashier';
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['cid'] = $row['id'];
                $_SESSION['fname'] = $row['fname'];
                $_SESSION['lname'] = $row['lname'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['contact'] = $row['contact'];
                header("Location: cashier-panel.php");
                exit();
            }
        }
    }
    if (!$user_found) {
        echo("<script>alert('Invalid Username/Email or Password. Try Again!');
              window.location.href = 'index.php';</script>");
    }
}
if(isset($_POST['patsub'])){
	$email=$_POST['email'];
	$password=$_POST['password2'];
	$query="select * from admissiontb where email='$email';";
	$result=mysqli_query($con,$query);
	if(mysqli_num_rows($result)==1)
	{
		$row=mysqli_fetch_array($result,MYSQLI_ASSOC);
		if(password_verify($password, $row['password'])){
      $_SESSION['pid'] = $row['pid'];
      $_SESSION['username'] = $row['fname']." ".$row['lname'];
      $_SESSION['fname'] = $row['fname'];
      $_SESSION['lname'] = $row['lname'];
      $_SESSION['gender'] = $row['gender'];
      $_SESSION['contact'] = $row['contact'];
      $_SESSION['email'] = $row['email'];
      $_SESSION['role'] = 'patient';
      header("Location:patient-panel.php");
    } else {
      echo("<script>alert('Invalid Username or Password. Try Again!');
            window.location.href = 'index.php';</script>");
    }
	}
  else {
    echo("<script>alert('Invalid Username or Password. Try Again!');
          window.location.href = 'index.php';</script>");
  }

}
if(isset($_POST['update_data']))
{
	$contact=$_POST['contact'];
	$status=$_POST['status'];
	$query="update appointmenttb set payment='$status' where contact='$contact';";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:updated.php");
}

if(isset($_POST['nurse_login'])){
	$username=$_POST['username'];
	$password=$_POST['password'];
	$query="select * from nursetb where username='$username' and password='$password';";
	$result=mysqli_query($con,$query);
	if(mysqli_num_rows($result)==1)
	{
		$_SESSION['username']=$username;
		$_SESSION['role'] = 'nurse';
		header("Location:nurse-panel.php");
	}
	else{
		echo("<script>alert('Invalid Username or Password. Try Again!');
			  window.location.href = 'index.php';</script>");
	}
}

if(isset($_POST['lab_login'])){
	$username=$_POST['username'];
	$password=$_POST['password'];
	$query="select * from labtb where username='$username' and password='$password';";
	$result=mysqli_query($con,$query);
	if(mysqli_num_rows($result)==1)
	{
		$_SESSION['username']=$username;
		$_SESSION['role'] = 'lab';
		header("Location:lab-panel.php");
	}
	else{
		echo("<script>alert('Invalid Username or Password. Try Again!');
			  window.location.href = 'index.php';</script>");
	}
}

if(isset($_POST['adsub'])){
	$username=$_POST['username1'];
	$password=$_POST['password2'];
	$query="select * from adminusertb where username='$username' and password='$password';";
	$result=mysqli_query($con,$query);
	if(mysqli_num_rows($result)==1)
	{
		$_SESSION['username']=$username;
		$_SESSION['role'] = 'admin';
		header("Location:admin-panel.php");
	}
	else{
		echo("<script>alert('Invalid Username or Password. Try Again!');
			  window.location.href = 'index.php';</script>");
	}
}

if(isset($_POST['doc_sub']))
{
	$doctor=$_POST['doctor'];
  $dpassword=$_POST['dpassword'];
  $demail=$_POST['demail'];
  $docFees=$_POST['docFees'];
	$query="insert into doctb(username,password,email,docFees)values('$doctor','$dpassword','$demail','$docFees')";
	$result=mysqli_query($con,$query);
	if($result)
		header("Location:adddoc.php");
}
function display_admin_panel(){
	echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
      <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
     <ul class="navbar-nav mr-auto">
       <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
      </li>
       <li class="nav-item">
        <a class="nav-link" href="#"></a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0" method="post" action="search.php">
      <input class="form-control mr-sm-2" type="text" placeholder="enter contact number" aria-label="Search" name="contact">
      <input type="submit" class="btn btn-outline-light my-2 my-sm-0 btn btn-outline-light" id="inputbtn" name="search_submit" value="Search">
    </form>
  </div>
</nav>
  </head>
  <style type="text/css">
    button:hover{cursor:pointer;}
    #inputbtn:hover{cursor:pointer;}
  </style>
  <body style="padding-top:50px;">
 <div class="jumbotron" id="ab1"></div>
   <div class="container-fluid" style="margin-top:50px;">
    <div class="row">
  <div class="col-md-4">
    <div class="list-group" id="list-tab" role="tablist">
      <a class="list-group-item list-group-item-action active" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Appointment</a>
      <a class="list-group-item list-group-item-action" href="patientdetails.php" role="tab" aria-controls="home">Patient List</a>
      <a class="list-group-item list-group-item-action" id="list-profile-list" data-toggle="list" href="#list-profile" role="tab" aria-controls="profile">Payment Status</a>
      <a class="list-group-item list-group-item-action" id="list-messages-list" data-toggle="list" href="#list-messages" role="tab" aria-controls="messages">Prescription</a>
      <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Doctors Section</a>
       <a class="list-group-item list-group-item-action" id="list-attend-list" data-toggle="list" href="#list-attend" role="tab" aria-controls="settings">Attendance</a>
    </div><br>
  </div>

  <div class="col-md-8">
    <div class="tab-content" id="nav-tabContent">
      <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <center><h4>Create an appointment</h4></center><br>
              <form class="form-group" method="post" action="appointment.php">
                <div class="row">
                  <div class="col-md-4"><label>First Name:</label></div>
                  <div class="col-md-8"><input type="text" class="form-control" name="fname"></div><br><br>
                  <div class="col-md-4"><label>Last Name:</label></div>
                  <div class="col-md-8"><input type="text" class="form-control"  name="lname"></div><br><br>
                  <div class="col-md-4"><label>Email id:</label></div>
                  <div class="col-md-8"><input type="text"  class="form-control" name="email"></div><br><br>
                  <div class="col-md-4"><label>Contact Number:</label></div>
                  <div class="col-md-8"><input type="text" class="form-control"  name="contact"></div><br><br>
                  <div class="col-md-4"><label>Doctor:</label></div>
                  <div class="col-md-8">
                   <select name="doctor" class="form-control" >

                     <!-- <option value="" disabled selected>Select Doctor</option>
                     <option value="Dr. Punam Shaw">Dr. Punam Shaw</option>
                      <option value="Dr. Ashok Goyal">Dr. Ashok Goyal</option> -->
                      <?php display_docs();?>

                    </select>
                  </div><br><br>
                  <div class="col-md-4"><label>Payment:</label></div>
                  <div class="col-md-8">
                    <select name="payment" class="form-control" >
                      <option value="" disabled selected>Select Payment Status</option>
                      <option value="Paid">Paid</option>
                      <option value="Pay later">Pay later</option>
                    </select>
                  </div><br><br><br>
                  <div class="col-md-4">
                    <input type="submit" name="entry_submit" value="Create new entry" class="btn btn-primary" id="inputbtn">
                  </div>
                  <div class="col-md-8"></div>                  
                </div>
              </form>
            </div>
          </div>
        </div><br>
      </div>
      <div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
        <div class="card">
          <div class="card-body">
            <form class="form-group" method="post" action="func.php">
              <input type="text" name="contact" class="form-control" placeholder="enter contact"><br>
              <select name="status" class="form-control">
               <option value="" disabled selected>Select Payment Status to update</option>
                <option value="paid">paid</option>
                <option value="pay later">pay later</option>
              </select><br><hr>
              <input type="submit" value="update" name="update_data" class="btn btn-primary">
            </form>
          </div>
        </div><br><br>
      </div>
      <div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">...</div>
      <div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">
        <form class="form-group" method="post" action="func.php">
          <label>Doctors name: </label>
          <input type="text" name="name" placeholder="enter doctors name" class="form-control">
          <br>
          <input type="submit" name="doc_sub" value="Add Doctor" class="btn btn-primary">
        </form>
      </div>
       <div class="tab-pane fade" id="list-attend" role="tabpanel" aria-labelledby="list-attend-list">...</div>
    </div>
  </div>
</div>
   </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <!--Sweet alert js-->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>
   <script type="text/javascript">
   $(document).ready(function(){
   	swal({
  title: "Welcome!",
  text: "Have a nice day!",
  imageUrl: "images/sweet.jpg",
  imageWidth: 400,
  imageHeight: 200,
  imageAlt: "Custom image",
  animation: false
})</script>
  </body>
</html>';
}
?>
