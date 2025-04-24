<!DOCTYPE html>
<?php 
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
//session_start();

// Include required files
include('func.php');
include('newfunc.php');
include('appfunc.php');

// Database connection
$con = mysqli_connect("localhost","root","","myhmsdb");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Initialize session variables with proper checks
$pid = $_SESSION['pid'] ?? null;
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$fname = $_SESSION['fname'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$contact = $_SESSION['contact'] ?? '';

// Handle appointment booking
if(isset($_POST['app-submit'])) {
    // Validate and sanitize inputs
    $spec = mysqli_real_escape_string($con, $_POST['spec'] ?? '');
    $doctor = mysqli_real_escape_string($con, $_POST['doctor'] ?? '');
    $docFees = mysqli_real_escape_string($con, $_POST['docFees'] ?? '');
    $appdate = mysqli_real_escape_string($con, $_POST['appdate'] ?? '');
    $apptime = mysqli_real_escape_string($con, $_POST['apptime'] ?? '');
    
    // Validate required fields
    if(empty($spec) || empty($doctor) || empty($appdate) || empty($apptime)) {
        echo "<script>alert('Please fill all required fields!');</script>";
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $con->prepare("INSERT INTO appointmenttb(pid, doctor, docFees, appdate, apptime, userStatus, doctorStatus) 
                             VALUES(?, ?, ?, ?, ?, '1', '1')");
        // $stmt = $con->prepare("INSERT INTO appointmenttb(pid, fname, lname, gender, email, contact, doctor, docFees, appdate, apptime, userStatus, doctorStatus) 
        //                      VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', '1')");
        $stmt->bind_param("iisss", $pid,$doctor, $docFees, $appdate, $apptime);
        
        if($stmt->execute()) {
            // Set success message and redirect
            $_SESSION['success_message'] = "Appointment booked successfully!";
            header("Location: appointments.php");
            exit();
        } else {
            echo "<script>alert('Error booking appointment: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Patient Dashboard</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    
    <!-- CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <style>
        .bg-primary {
            background: -webkit-linear-gradient(left, #3931af, #00c6ff);
        }
        .list-group-item.active {
            z-index: 2;
            color: #fff;
            background-color: #342ac1;
            border-color: #007bff;
        }
        .btn-primary {
            background-color: #3c50c1;
            border-color: #3c50c1;
        }
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
            opacity: 1;
        }
        .panel-body a:hover {
            text-decoration: underline;
            cursor: pointer;
        }
        .card-link {
            color: #342ac1;
            text-decoration: none;
        }
        .card-link:hover {
            text-decoration: underline;
            cursor: pointer;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 20px;
        }
    </style>
</head>
<body style="padding-top:50px;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Prenatal Online Care System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top:50px;">
        <h3 class="text-center mb-4">Welcome <?php echo htmlspecialchars($username); ?></h3>
        
        <div class="row">
            <!-- Navigation -->
            <div class="col-md-3">
                <div class="list-group" id="list-tab" role="tablist">
                    <a class="list-group-item list-group-item-action active" href="#list-dash" data-toggle="tab">Dashboard</a>
                    <a class="list-group-item list-group-item-action" href="#list-home" data-toggle="tab">Book Appointment</a>
                    <a class="list-group-item list-group-item-action" href="appointments.php">Appointments</a>
                    <a class="list-group-item list-group-item-action" href="#list-pres" data-toggle="tab">Prescriptions</a>
                    <a class="list-group-item list-group-item-action" href="contact.php" data-toggle="tab">Messages</a>
                    
                </div>
            </div>
            
            <!-- Content -->
            <div class="col-md-9">
                <div class="tab-content" id="nav-tabContent">
                    
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="list-dash">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-calendar fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4>Book My Appointment</h4>
                                            <a href="#list-home" class="card-link" data-toggle="tab">Book Appointment</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-history fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4>Appointment History</h4>
                                            <a href="appointments.php" class="card-link">View your appointments</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-file-text fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4>Prescriptions</h4>
                                            <a href="#list-pres" class="card-link" data-toggle="tab">View prescriptions</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Book Appointment Tab -->
                    <div class="tab-pane fade" id="list-home">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title text-center mb-4">Book Your Appointment</h4>
                                <form class="form-group" method="post" action="">
                                    <div class="row">
                                        <div class="col-md-4"><label>Specialization:</label></div>
                                        <div class="col-md-8">
                                            <select name="spec" class="form-control" id="spec" required>
                                                <option value="" disabled selected>Select Specialization</option>
                                                <?php display_specs(); ?>
                                            </select>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"><label>Doctor:</label></div>
                                        <div class="col-md-8">
                                            <select name="doctor" class="form-control" id="doctor" required>
                                                <option value="" disabled selected>Select Doctor</option>
                                                <?php display_docs(); ?>
                                            </select>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"><label>Consultancy Fees:</label></div>
                                        <div class="col-md-8">
                                            <input class="form-control" type="text" name="docFees" id="docFees" readonly>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"><label>Appointment Date:</label></div>
                                        <div class="col-md-8">
                                            <input type="date" class="form-control" name="appdate" min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"><label>Appointment Time:</label></div>
                                        <div class="col-md-8">
                                            <select name="apptime" class="form-control" required>
                                                <option value="" disabled selected>Select Time</option>
                                                <option value="08:00:00">8:00 AM</option>
                                                <option value="10:00:00">10:00 AM</option>
                                                <option value="12:00:00">12:00 PM</option>
                                                <option value="14:00:00">2:00 PM</option>
                                                <option value="16:00:00">4:00 PM</option>
                                            </select>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"></div>
                                        <div class="col-md-8">
                                            <input type="submit" name="app-submit" value="Book Appointment" class="btn btn-primary btn-block">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                   
                    
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Update doctor fees when doctor is selected
        $('#doctor').change(function() {
            var fees = $(this).find(':selected').data('fees');
            $('#docFees').val(fees);
        });
        
        // Initialize doctor fees if already selected
        if($('#doctor').val()) {
            $('#doctor').trigger('change');
        }
        
        // Filter doctors based on specialization
        $('#spec').change(function() {
            var spec = $(this).val();
            if(spec) {
                $.ajax({
                    url: 'get_doctors.php',
                    type: 'POST',
                    data: {spec: spec},
                    success: function(response) {
                        $('#doctor').html(response);
                        $('#docFees').val('');
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching doctors: " + error);
                    }
                });
            } else {
                $('#doctor').html('<option value="" disabled selected>Select Doctor</option>');
                $('#docFees').val('');
            }
        });
    });
    </script>
</body>
</html>