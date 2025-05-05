<?php
session_start();

// Database connection with error handling
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Patient Login
if (isset($_POST['patsub'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password2']);
    
    $query = "SELECT * FROM patreg WHERE email=? AND password=?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['pid'] = $row['pid'];
        $_SESSION['username'] = $row['fname'] . " " . $row['lname'];
        $_SESSION['fname'] = $row['fname'];
        $_SESSION['lname'] = $row['lname'];
        $_SESSION['gender'] = $row['gender'];
        $_SESSION['contact'] = $row['contact'];
        $_SESSION['email'] = $row['email'];
        
        // Check if patient has prenatal registration
        $prenatalQuery = "SELECT * FROM prenatal_reg WHERE pid=?";
        $prenatalStmt = mysqli_prepare($con, $prenatalQuery);
        mysqli_stmt_bind_param($prenatalStmt, "i", $row['pid']);
        mysqli_stmt_execute($prenatalStmt);
        $prenatalResult = mysqli_stmt_get_result($prenatalStmt);
        
        if (mysqli_num_rows($prenatalResult) > 0) {
            $prenatalData = mysqli_fetch_assoc($prenatalResult);
            $_SESSION['pregnancy_week'] = calculatePregnancyWeek($prenatalData['lmp']);
        }
        
        header("Location: admin-panel.php");
        exit();
    } else {
        echo "<script>
                alert('Invalid Username or Password. Try Again!');
                window.location.href = 'index1.php';
              </script>";
        exit();
    }
}

// Update Payment Status
if (isset($_POST['update_data'])) {
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    $query = "UPDATE appointmenttb SET payment=? WHERE contact=?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $status, $contact);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: updated.php");
        exit();
    } else {
        echo "<script>alert('Update failed!'); window.history.back();</script>";
    }
}

// Update Appointment Status
if (isset($_POST['update_AppStatus'])) {
    $appointmentId = mysqli_real_escape_string($con, $_POST['appointment_id']);
    
    $query = "UPDATE appointmenttb SET userStatus='1', doctorStatus='0' WHERE ID=?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $appointmentId);
    
    mysqli_stmt_execute($stmt);
}

// Add Doctor
if (isset($_POST['doc_sub'])) {
    $doctor = mysqli_real_escape_string($con, $_POST['doctor']);
    $dpassword = mysqli_real_escape_string($con, $_POST['dpassword']);
    $demail = mysqli_real_escape_string($con, $_POST['demail']);
    $docFees = mysqli_real_escape_string($con, $_POST['docFees']);
    
    $query = "INSERT INTO doctb (username, password, email, docFees) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $doctor, $dpassword, $demail, $docFees);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: adddoc.php");
        exit();
    } else {
        echo "<script>alert('Failed to add doctor!'); window.history.back();</script>";
    }
}

// Prenatal Registration
if (isset($_POST['prenatal-submit'])) {
    $pid = $_SESSION['pid'];
    $lmp = mysqli_real_escape_string($con, $_POST['lmp']);
    $edc = mysqli_real_escape_string($con, $_POST['edc']);
    $gravida = (int)$_POST['gravida'];
    $parity = (int)$_POST['parity'];
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $rh_factor = mysqli_real_escape_string($con, $_POST['rh_factor']);
    
    // Calculate pregnancy week
    $pregnancy_week = calculatePregnancyWeek($lmp);
    
    $query = "INSERT INTO prenatal_reg (pid, lmp, edc, gravida, parity, blood_group, rh_factor, pregnancy_week) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "issiissi", $pid, $lmp, $edc, $gravida, $parity, $blood_group, $rh_factor, $pregnancy_week);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pregnancy_week'] = $pregnancy_week;
        $_SESSION['success'] = "Prenatal registration successful!";
        header("Location: admin-panel.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($con);
        header("Location: admin-panel.php#list-prenatal");
        exit();
    }
}

// Function to calculate pregnancy week
function calculatePregnancyWeek($lmp) {
    $lmpDate = new DateTime($lmp);
    $today = new DateTime();
    $diff = $today->diff($lmpDate);
    return floor($diff->days / 7);
}

// Display Doctors
function display_docs() {
    global $con;
    $query = "SELECT * FROM doctb";
    $result = mysqli_query($con, $query);
    
    while ($row = mysqli_fetch_array($result)) {
        echo '<option value="' . $row['username'] . '" data-fees="' . $row['docFees'] . '">' . $row['username'] . '</option>';
    }
}

// Display Specializations (only declared once)
function display_specs() {
    global $con;
    $query = "SELECT DISTINCT spec FROM doctb";
    $result = mysqli_query($con, $query);
    
    while ($row = mysqli_fetch_array($result)) {
        echo '<option value="' . $row['spec'] . '">' . $row['spec'] . '</option>';
    }
}

// Display Admin Panel
function display_admin_panel() {
    echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
      <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Global Hospital</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>
          </li>
        </ul>
        <form class="form-inline my-2 my-lg-0" method="post" action="search.php">
          <input class="form-control mr-sm-2" type="text" placeholder="enter contact number" name="contact">
          <input type="submit" class="btn btn-outline-light" id="inputbtn" name="search_submit" value="Search">
        </form>
      </div>
    </nav>
  </head>
  
  <style>
    button:hover, #inputbtn:hover { cursor:pointer; }
    .jumbotron { background: url("images/hospital-bg.jpg") center/cover; height: 200px; }
  </style>
  
  <body style="padding-top:50px;">
    <div class="jumbotron" id="ab1"></div>
    <div class="container-fluid" style="margin-top:50px;">
      <div class="row">
        <div class="col-md-4">
          <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="list-home-list" data-toggle="list" href="#list-home">Appointment</a>
            <a class="list-group-item list-group-item-action" href="patientdetails.php">Patient List</a>
            <a class="list-group-item list-group-item-action" id="list-profile-list" data-toggle="list" href="#list-profile">Payment Status</a>
            <a class="list-group-item list-group-item-action" id="list-messages-list" data-toggle="list" href="#list-messages">Prescription</a>
            <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings">Doctors Section</a>
            <a class="list-group-item list-group-item-action" id="list-attend-list" data-toggle="list" href="#list-attend">Attendance</a>
          </div>
          <br>
        </div>

        <div class="col-md-8">
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="list-home">
              <div class="container-fluid">
                <div class="card">
                  <div class="card-body">
                    <center><h4>Create an appointment</h4></center><br>
                    <form class="form-group" method="post" action="appointment.php">
                      <div class="row">
                        <div class="col-md-4"><label>First Name:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="fname" required></div><br><br>
                        <div class="col-md-4"><label>Last Name:</label></div>
                        <div class="col-md-8"><input type="text" class="form-control" name="lname" required></div><br><br>
                        <div class="col-md-4"><label>Email id:</label></div>
                        <div class="col-md-8"><input type="email" class="form-control" name="email" required></div><br><br>
                        <div class="col-md-4"><label>Contact Number:</label></div>
                        <div class="col-md-8"><input type="tel" class="form-control" name="contact" required></div><br><br>
                        <div class="col-md-4"><label>Doctor:</label></div>
                        <div class="col-md-8">
                          <select name="doctor" class="form-control" required>';
                            display_docs();
                          echo '</select>
                        </div><br><br>
                        <div class="col-md-4"><label>Payment:</label></div>
                        <div class="col-md-8">
                          <select name="payment" class="form-control" required>
                            <option value="" disabled selected>Select Payment Status</option>
                            <option value="Paid">Paid</option>
                            <option value="Pay later">Pay later</option>
                          </select>
                        </div><br><br><br>
                        <div class="col-md-4">
                          <input type="submit" name="entry_submit" value="Create new entry" class="btn btn-primary" id="inputbtn">
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div><br>
            </div>
            
            <div class="tab-pane fade" id="list-profile">
              <div class="card">
                <div class="card-body">
                  <form class="form-group" method="post" action="func.php">
                    <input type="text" name="contact" class="form-control" placeholder="enter contact" required><br>
                    <select name="status" class="form-control" required>
                      <option value="" disabled selected>Select Payment Status to update</option>
                      <option value="paid">paid</option>
                      <option value="pay later">pay later</option>
                    </select><br><hr>
                    <input type="submit" value="update" name="update_data" class="btn btn-primary">
                  </form>
                </div>
              </div><br><br>
            </div>
            
            <div class="tab-pane fade" id="list-messages">Prescription content goes here</div>
            
            <div class="tab-pane fade" id="list-settings">
              <form class="form-group" method="post" action="func.php">
                <div class="form-group">
                  <label>Doctor\'s Name:</label>
                  <input type="text" name="doctor" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Email:</label>
                  <input type="email" name="demail" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Password:</label>
                  <input type="password" name="dpassword" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Consultation Fees:</label>
                  <input type="number" name="docFees" class="form-control" required>
                </div>
                <input type="submit" name="doc_sub" value="Add Doctor" class="btn btn-primary">
              </form>
            </div>
            
            <div class="tab-pane fade" id="list-attend">Attendance content goes here</div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>
    <script>
      $(document).ready(function(){
        swal({
          title: "Welcome!",
          text: "Have a nice day!",
          imageUrl: "images/sweet.jpg",
          imageWidth: 400,
          imageHeight: 200,
          imageAlt: "Custom image",
          animation: false
        });
      });
    </script>
  </body>
</html>';
}

// Close database connection at the end
mysqli_close($con);
?>