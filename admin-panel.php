<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check authentication
session_start();

// Database connection
$con = mysqli_connect("localhost","root","","myhmsdb");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get patient data
$national_id = $_SESSION['national_id'];

$query = "SELECT * FROM patreg WHERE national_id='$national_id'";
$result = mysqli_query($con, $query);
$patient = mysqli_fetch_assoc($result);

// Get prenatal data if exists
$prenatalQuery = "SELECT * FROM prenatal_reg WHERE national_id='$national_id'";
$prenatalResult = mysqli_query($con, $prenatalQuery);
$prenatalData = mysqli_fetch_assoc($prenatalResult);

// Initialize variables
$pregnancy_week = 0;
$milestone = [];

// Calculate current pregnancy week if registered
if($prenatalData) {
    $lmp = new DateTime($prenatalData['lmp']);
    $today = new DateTime();
    $diff = $today->diff($lmp);
    $pregnancy_week = floor($diff->days / 7);
    $_SESSION['pregnancy_week'] = $pregnancy_week;
    
    // Get milestones
    $milestoneQuery = "SELECT * FROM pregnancy_milestones WHERE $pregnancy_week BETWEEN 
                      SUBSTRING_INDEX(week_range, '-', 1) AND SUBSTRING_INDEX(week_range, '-', -1)";
    $milestoneResult = mysqli_query($con, $milestoneQuery);
    $milestone = mysqli_fetch_assoc($milestoneResult) ?: [];
}

// Handle prenatal registration
if(isset($_POST['prenatal-submit'])) {
    $lmp = mysqli_real_escape_string($con, $_POST['lmp']);
    $edc = mysqli_real_escape_string($con, $_POST['edc']);
    $gravida = mysqli_real_escape_string($con, $_POST['gravida']);
    $parity = mysqli_real_escape_string($con, $_POST['parity']);
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $rh_factor = mysqli_real_escape_string($con, $_POST['rh_factor']);
    
    $query = "INSERT INTO prenatal_reg (pid, lmp, edc, gravida, parity, blood_group, rh_factor, pregnancy_week) 
              VALUES ('$national_id', '$lmp', '$edc', '$gravida', '$parity', '$blood_group', '$rh_factor', '$pregnancy_week')";
    
    if(mysqli_query($con, $query)) {
        $_SESSION['success'] = "Prenatal registration successful!";
        header("Refresh:0");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($con);
    }
}

// Handle appointment booking
if(isset($_POST['app-submit'])) {
    $spec = mysqli_real_escape_string($con, $_POST['spec']);
    $doctor = mysqli_real_escape_string($con, $_POST['doctor']);
    $docFees = mysqli_real_escape_string($con, $_POST['docFees']);
    $appdate = mysqli_real_escape_string($con, $_POST['appdate']);
    $apptime = mysqli_real_escape_string($con, $_POST['apptime']);
    $apptype = mysqli_real_escape_string($con, $_POST['apptype']);
    
    $query = "INSERT INTO appointmenttb (pid, fname, lname, gender, email, contact, doctor, docFees, appdate, apptime, apptype, userStatus, doctorStatus) 
              VALUES ('$national_id', '{$patient['fname']}', '{$patient['lname']}', '{$patient['gender']}', '{$patient['email']}', '{$patient['contact']}', '$doctor', '$docFees', '$appdate', '$apptime', '$apptype', '1', '1')";
    
    if(mysqli_query($con, $query)) {
        $_SESSION['success'] = "Appointment booked successfully!";
        
        // Add reminder
        $reminderDate = date('Y-m-d', strtotime('-1 day', strtotime($appdate)));
        $reminderQuery = "INSERT INTO reminders (pid, reminder_date, reminder_time, message, status) 
                         VALUES ('$national_id', '$reminderDate', '$apptime', 'Reminder: You have a prenatal appointment tomorrow', 'pending')";
        mysqli_query($con, $reminderQuery);
        
        header("Location: appointments.php");
        exit();
    } else {
        $_SESSION['error'] = "Error booking appointment: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Patient Dashboard | Prenatal Care</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    
    <!-- CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #342ac1;
            --secondary-color: #00c6ff;
        }
        
        body {
            font-family: 'IBM Plex Sans', sans-serif;
            padding-top: 60px;
            background-color: #f8f9fa;
        }
        
        .bg-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .pregnancy-tracker {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .week-display {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .milestone-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .milestone-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .video-conf-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .video-conf-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,198,255,0.4);
        }
        
        .list-group-item.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            font-weight: bold;
        }

        .list-group-item.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            padding: 20px;
            background: white;
            border-radius: 0 0 15px 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fa fa-baby-carriage mr-2"></i>Prenatal Care Portal
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fa fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Alerts -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="images/user-icon.png" class="rounded-circle mb-3" width="100" alt="User">
                        <h5><?php echo $patient['fname'] . ' ' . $patient['lname']; ?></h5>
                        <p class="text-muted">Patient ID: <?php echo $national_id; ?></p>
                        
                        <hr>
                        
                        <div class="list-group">
                            <a href="#list-dash" class="list-group-item list-group-item-action active" data-toggle="tab">
                                <i class="fa fa-tachometer-alt mr-2"></i>Dashboard
                            </a>

                            <a href="#list-assessments" class="list-group-item list-group-item-action" data-toggle="tab">
                                <i class="fa fa-clipboard-check mr-2"></i>Health Assessments
                            </a>

                            <?php if(!$prenatalData): ?>
                                <a href="prenatal-register.php" class="list-group-item list-group-item-action" data-toggle="tab">
                                    <i class="fa fa-baby mr-2"></i>Register for Prenatal
                                </a>
                            <?php endif; ?>
                            <a href="appointments.php" class="list-group-item list-group-item-action" data-toggle="tab">
                                <i class="fa fa-calendar-check mr-2"></i>Book Appointment
                            </a>
                            <a href="view-appointments.php" class="list-group-item list-group-item-action">
                                <i class="fa fa-history mr-2"></i>Appointments
                            </a>
                            <a href="view-reminders.php" class="list-group-item list-group-item-action" data-toggle="tab">
                                <i class="fa fa-bell mr-2"></i>Reminders
                            </a>
                            <a href="health-education.php" class="list-group-item list-group-item-action" data-toggle="tab">
                                <i class="fa fa-book-medical mr-2"></i>Health Education
                            </a>
                            <a href="#list-messages" class="list-group-item list-group-item-action" data-toggle="tab">
                                <i class="fa fa-comments mr-2"></i>Messages
                            </a>
                            <a href="#list-video" class="list-group-item list-group-item-action" data-toggle="tab">
                                <i class="fa fa-video mr-2"></i>Video Consult
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="list-dash">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">
                                    <i class="fa fa-home mr-2"></i>Patient Dashboard
                                </h4>
                                
                                <?php if($prenatalData): ?>
                                <!-- Pregnancy Tracker -->
                                <div class="pregnancy-tracker">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4><i class="fa fa-baby mr-2"></i>Your Pregnancy Progress</h4>
                                        <span class="badge badge-pill badge-primary">Week <?php echo $pregnancy_week; ?></span>
                                    </div>
                                    
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar progress-bar-striped bg-success" 
                                             style="width: <?php echo min(($pregnancy_week/40)*100, 100); ?>%">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small>Week 1</small>
                                        <small>Week 40</small>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="milestone-card p-3">
                                                <h5><i class="fa fa-baby text-primary mr-2"></i>Baby Development</h5>
                                                <p class="mt-3"><?php echo $milestone['baby_development'] ?? 'No milestone information available for this week.'; ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="milestone-card p-3">
                                                <h5><i class="fa fa-female text-primary mr-2"></i>Mom's Health</h5>
                                                <p class="mt-3"><?php echo $milestone['moms_health'] ?? 'No health information available for this week.'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <h5><i class="fa fa-info-circle mr-2"></i>Welcome to your dashboard!</h5>
                                        <p>To get started with prenatal care services, please register for prenatal care.</p>
                                        <a href="prenatal-register.php" class="btn btn-primary" data-toggle="tab">
                                            <i class="fa fa-baby mr-2"></i>Register Now
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Quick Actions -->
                                <h5 class="mt-5 mb-3"><i class="fa fa-rocket mr-2"></i>Quick Actions</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <div class="icon-circle bg-primary mb-3">
                                                    <i class="fa fa-calendar text-white"></i>
                                                </div>
                                                <h5>Book Appointment</h5>
                                                <a href="appointments.php" class="btn btn-sm btn-outline-primary mt-2" data-toggle="tab">
                                                    Schedule Now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <div class="icon-circle bg-success mb-3">
                                                    <i class="fa fa-file-medical text-white"></i>
                                                </div>
                                                <h5>Health Tips</h5>
                                                <a href="health-education.php" class="btn btn-sm btn-outline-success mt-2" data-toggle="tab">
                                                    View Tips
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <div class="icon-circle bg-info mb-3">
                                                    <i class="fa fa-comments text-white"></i>
                                                </div>
                                                <h5>Messages</h5>
                                                <a href="#list-messages" class="btn btn-sm btn-outline-info mt-2" data-toggle="tab">
                                                    Contact Provider
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Upcoming Appointments -->
                                <h5 class="mt-5 mb-3"><i class="fa fa-calendar-day mr-2"></i>Upcoming Appointments</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <?php
                                        $appQuery = "SELECT * FROM appointmenttb WHERE email='{$patient['email']}' AND appdate >= CURDATE() ORDER BY appdate ASC LIMIT 3";
                                        $appResult = mysqli_query($con, $appQuery);
                                        
                                        if(mysqli_num_rows($appResult) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Time</th>
                                                            <th>Provider</th>
                                                            <th>Type</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while($app = mysqli_fetch_assoc($appResult)): ?>
                                                            <tr>
                                                                <td><?php echo date('M j, Y', strtotime($app['appdate'])); ?></td>
                                                                <td><?php echo date('g:i A', strtotime($app['apptime'])); ?></td>
                                                                <td>Dr. <?php echo $app['doctor']; ?></td>
                                                                <td>
                                                                <span class="badge badge-<?php echo isset($app['apptype']) && $app['apptype'] == 'video' ? 'info' : 'primary'; ?>">
                                                                <?php echo isset($app['apptype']) ? ucfirst((string)$app['apptype']) : 'In-person'; ?>
                                                                </span>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <a href="view-appointments.php" class="btn btn-primary btn-sm float-right">
                                                View All Appointments
                                            </a>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fa fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <h5>No Upcoming Appointments</h5>
                                                <p>You don't have any scheduled appointments yet.</p>
                                                <a href="#list-appointment" class="btn btn-primary" data-toggle="tab">
                                                    <i class="fa fa-plus mr-2"></i>Book Appointment
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Health Assessments Tab -->
                    <div class="tab-pane fade" id="list-assessments">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="fa fa-clipboard-check mr-2"></i>Previous Assessments
                                </h4>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>BP</th>
                                                <th>Pulse</th>
                                                <th>Temp</th>
                                                <th>Weight</th>
                                                <th>BMI</th>
                                                <th>Fetal HR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $assessmentQuery = "SELECT * FROM patient_assessments WHERE pid='$national_id' ORDER BY assessment_date DESC";
                                            $assessmentResult = mysqli_query($con, $assessmentQuery);
                                            
                                            if(mysqli_num_rows($assessmentResult)) {
                                                while($assessment = mysqli_fetch_assoc($assessmentResult)) {
                                                    echo "<tr>
                                                        <td>".date('M j, Y', strtotime($assessment['assessment_date']))."</td>
                                                        <td>".($assessment['blood_pressure_systolic'] ? $assessment['blood_pressure_systolic']."/".$assessment['blood_pressure_diastolic'] : '-')."</td>
                                                        <td>".($assessment['pulse'] ?? '-')."</td>
                                                        <td>".($assessment['temperature'] ?? '-')."</td>
                                                        <td>".($assessment['weight'] ?? '-')."</td>
                                                        <td>".($assessment['bmi'] ?? '-')."</td>
                                                        <td>".($assessment['fetal_heart_rate'] ?? '-')."</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>No assessments found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prenatal Registration Tab -->
                    <div class="tab-pane fade" id="list-prenatal">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title text-center mb-4">
                                    <i class="fa fa-baby mr-2"></i>Prenatal Care Registration
                                </h4>
                                
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Menstrual Period (LMP)</label>
                                                <input type="date" class="form-control" name="lmp" id="lmp" required 
                                                       max="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Estimated Due Date (EDC)</label>
                                                <input type="date" class="form-control" name="edc" id="edc" readonly required>
                                            </div>
                                            <div class="form-group">
                                                <label>Gravida (Number of pregnancies including current one)</label>
                                                <input type="number" class="form-control" name="gravida" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Parity (Number of live births)</label>
                                                <input type="number" class="form-control" name="parity" min="0" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Blood Group</label>
                                                <select class="form-control" name="blood_group" required>
                                                    <option value="">Select</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="AB">AB</option>
                                                    <option value="O">O</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Rh Factor</label>
                                                <select class="form-control" name="rh_factor" required>
                                                    <option value="+">Rh+</option>
                                                    <option value="-">Rh-</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="consent-check" required>
                                                <label class="form-check-label" for="consent-check">
                                                    I consent to prenatal care services and data collection
                                                </label>
                                            </div>
                                            <button type="submit" name="prenatal-submit" class="btn btn-primary btn-block">
                                                <i class="fa fa-check-circle mr-2"></i>Register for Prenatal Care
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Book Appointment Tab -->
                    <div class="tab-pane fade" id="list-appointment">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title text-center mb-4">
                                    <i class="fa fa-calendar-check mr-2"></i>Book Appointment
                                </h4>
                                
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-4"><label>Specialization:</label></div>
                                        <div class="col-md-8">
                                            <select name="spec" class="form-control" id="spec" required>
                                                <option value="" disabled selected>Select Specialization</option>
                                                <option value="Obstetrics">Obstetrics</option>
                                                <option value="Gynecology">Gynecology</option>
                                                <option value="Midwifery">Midwifery</option>
                                            </select>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"><label>Doctor:</label></div>
                                        <div class="col-md-8">
                                            <select name="doctor" class="form-control" id="doctor" required>
                                                <option value="" disabled selected>Select Doctor</option>
                                                <?php
                                                $docQuery = "SELECT * FROM doctb";
                                                $docResult = mysqli_query($con, $docQuery);
                                                while($doc = mysqli_fetch_assoc($docResult)) {
                                                    echo "<option value='{$doc['id']}' data-fees='{$doc['docFees']}'>{$doc['name']} ({$doc['spec']})</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <br><br>
                                        
                                        <div class="col-md-4"><label>Consultation Type:</label></div>
                                        <div class="col-md-8">
                                            <select name="apptype" class="form-control" required>
                                                <option value="in-person">In-Person</option>
                                                <option value="video">Video Consultation</option>
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
                                            <input type="date" class="form-control" name="appdate" id="appdate" 
                                                   min="<?php echo date('Y-m-d'); ?>" required>
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
                                            <button type="submit" name="app-submit" class="btn btn-primary btn-block">
                                                <i class="fa fa-calendar-plus mr-2"></i>Book Appointment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reminders Tab -->
                    <div class="tab-pane fade" id="list-reminders">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="fa fa-bell mr-2"></i>Your Reminders
                                </h4>
                                
                                <?php
                                $reminderQuery = "SELECT * FROM reminders WHERE pid='$national_id' ORDER BY reminder_date DESC";
                                $reminderResult = mysqli_query($con, $reminderQuery);
                                
                                if(mysqli_num_rows($reminderResult) > 0): ?>
                                    <div class="list-group">
                                        <?php while($reminder = mysqli_fetch_assoc($reminderResult)): ?>
                                            <div class="list-group-item list-group-item-action flex-column align-items-start">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1"><?php echo $reminder['message']; ?></h5>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($reminder['reminder_date'])); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1">
                                                    <i class="fa fa-clock mr-2"></i>
                                                    <?php echo date('g:i A', strtotime($reminder['reminder_time'])); ?>
                                                </p>
                                                <small class="text-<?php echo $reminder['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($reminder['status']); ?>
                                                </small>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fa fa-bell-slash fa-3x text-muted mb-3"></i>
                                        <h5>No Reminders Yet</h5>
                                        <p>You don't have any reminders scheduled.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Health Education Tab -->
                    <div class="tab-pane fade" id="list-education">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="fa fa-book-medical mr-2"></i>Health Education
                                </h4>
                                
                                <?php if($prenatalData && !empty($milestone)): ?>
                                    <div class="alert alert-primary">
                                        <h5>
                                            <i class="fa fa-info-circle mr-2"></i>
                                            Week <?php echo $pregnancy_week; ?> Education
                                        </h5>
                                        <p><?php echo $milestone['education_tips'] ?? 'No educational tips available for this week.'; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <h5 class="mt-4 mb-3">
                                    <i class="fa fa-graduation-cap mr-2"></i>Pregnancy Resources
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="fa fa-utensils fa-3x text-success"></i>
                                                </div>
                                                <h5 class="text-center">Nutrition Guide</h5>
                                                <ul class="mt-3">
                                                    <li>Increase folic acid intake</li>
                                                    <li>Eat iron-rich foods</li>
                                                    <li>Stay hydrated</li>
                                                    <li>Limit caffeine</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="fa fa-running fa-3x text-primary"></i>
                                                </div>
                                                <h5 class="text-center">Exercise Tips</h5>
                                                <ul class="mt-3">
                                                    <li>30 minutes of moderate exercise daily</li>
                                                    <li>Walking and swimming are excellent</li>
                                                    <li>Pelvic floor exercises</li>
                                                    <li>Avoid high-impact sports</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="fa fa-heartbeat fa-3x text-danger"></i>
                                                </div>
                                                <h5 class="text-center">Warning Signs</h5>
                                                <ul class="mt-3">
                                                    <li>Severe headaches</li>
                                                    <li>Vision changes</li>
                                                    <li>Severe abdominal pain</li>
                                                    <li>Vaginal bleeding</li>
                                                    <li>Decreased fetal movement</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Trimester Guides -->
                                <h5 class="mt-5 mb-3">
                                    <i class="fa fa-calendar-alt mr-2"></i>Trimester Guides
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                First Trimester (Weeks 1-12)
                                            </div>
                                            <div class="card-body">
                                                <ul>
                                                    <li>Morning sickness management</li>
                                                    <li>First ultrasound</li>
                                                    <li>Genetic screening tests</li>
                                                    <li>Prenatal vitamin importance</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                Second Trimester (Weeks 13-26)
                                            </div>
                                            <div class="card-body">
                                                <ul>
                                                    <li>Anatomy scan ultrasound</li>
                                                    <li>Feeling baby movements</li>
                                                    <li>Gestational diabetes screening</li>
                                                    <li>Maternity clothes shopping</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-warning text-dark">
                                                Third Trimester (Weeks 27-40)
                                            </div>
                                            <div class="card-body">
                                                <ul>
                                                    <li>Birth plan preparation</li>
                                                    <li>Braxton Hicks contractions</li>
                                                    <li>Hospital bag checklist</li>
                                                    <li>Signs of labor</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages Tab -->
                    <div class="tab-pane fade" id="list-messages">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="fa fa-comments mr-2"></i>Messages
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action active">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">Dr. Smith</h6>
                                                    <small>Today</small>
                                                </div>
                                                <p class="mb-1">Obstetrician</p>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">Nurse Johnson</h6>
                                                    <small>2 days ago</small>
                                                </div>
                                                <p class="mb-1">Midwife</p>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">Nutritionist</h6>
                                                    <small>1 week ago</small>
                                                </div>
                                                <p class="mb-1">Dietary advice</p>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="message-area border rounded p-3" style="height: 500px; overflow-y: auto;">
                                            <!-- Sample conversation -->
                                            <div class="message received mb-3 p-3 bg-light rounded">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <strong>Dr. Smith</strong>
                                                    <small class="text-muted">Today, 10:30 AM</small>
                                                </div>
                                                <p>Hello <?php echo $patient['fname']; ?>, how are you feeling today? Any concerns about your pregnancy?</p>
                                            </div>
                                            
                                            <div class="message sent mb-3 p-3 bg-primary text-white rounded">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <strong>You</strong>
                                                    <small class="text-white">Today, 10:35 AM</small>
                                                </div>
                                                <p>I've been having some back pain and mild nausea. Is this normal?</p>
                                            </div>
                                            
                                            <div class="message received mb-3 p-3 bg-light rounded">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <strong>Dr. Smith</strong>
                                                    <small class="text-muted">Today, 10:40 AM</small>
                                                </div>
                                                <p>Mild back pain and nausea can be normal during pregnancy. Try these suggestions:
                                                   <ul>
                                                       <li>Use a pregnancy pillow for support</li>
                                                       <li>Do gentle stretching exercises</li>
                                                       <li>Eat small, frequent meals</li>
                                                   </ul>
                                                   If symptoms worsen, let me know immediately.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <form class="mt-3">
                                            <div class="form-group">
                                                <textarea class="form-control" rows="3" placeholder="Type your message..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-paper-plane mr-2"></i>Send Message
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video Consultation Tab -->
                    <div class="tab-pane fade" id="list-video">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="fa fa-video mr-2"></i>Video Consultation
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white">
                                                Upcoming Video Consultations
                                            </div>
                                            <div class="card-body">
                                                <?php
                                                $videoQuery = "SELECT * FROM appointmenttb 
                                                              WHERE pid='$national_id' AND apptype='video' AND appdate >= CURDATE() 
                                                              ORDER BY appdate ASC";
                                                $videoResult = mysqli_query($con, $videoQuery);
                                                
                                                if(mysqli_num_rows($videoResult) > 0): ?>
                                                    <div class="list-group">
                                                        <?php while($video = mysqli_fetch_assoc($videoResult)): ?>
                                                            <div class="list-group-item">
                                                                <div class="d-flex w-100 justify-content-between">
                                                                    <h6 class="mb-1">Dr. <?php echo $video['doctor']; ?></h6>
                                                                    <small><?php echo date('M j', strtotime($video['appdate'])); ?></small>
                                                                </div>
                                                                <p class="mb-1">
                                                                    <i class="fa fa-clock mr-2"></i>
                                                                    <?php echo date('g:i A', strtotime($video['apptime'])); ?>
                                                                </p>
                                                                <button class="btn btn-sm video-conf-btn mt-2">
                                                                    <i class="fa fa-video mr-1"></i>Join Consultation
                                                                </button>
                                                            </div>
                                                        <?php endwhile; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-3">
                                                        <i class="fa fa-video-slash fa-3x text-muted mb-3"></i>
                                                        <h5>No Upcoming Video Consults</h5>
                                                        <p>You don't have any scheduled video consultations.</p>
                                                        <a href="#list-appointment" class="btn btn-primary" data-toggle="tab">
                                                            <i class="fa fa-plus mr-2"></i>Schedule One Now
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-success text-white">
                                                Start Instant Consultation
                                            </div>
                                            <div class="card-body text-center">
                                                <div class="py-4">
                                                    <i class="fa fa-video fa-4x text-success mb-4"></i>
                                                    <h5>On-Demand Video Consult</h5>
                                                    <p class="mb-4">Check if a provider is available for an immediate consultation</p>
                                                    <button class="btn btn-lg video-conf-btn">
                                                        <i class="fa fa-video mr-2"></i>Check Availability
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Video Consultation Guide -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5><i class="fa fa-question-circle mr-2"></i>Video Consultation Guide</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="fa fa-check-circle text-success mr-2"></i>Before Your Consultation</h6>
                                                <ul>
                                                    <li>Test your internet connection</li>
                                                    <li>Use Chrome or Firefox browser</li>
                                                    <li>Enable camera and microphone permissions</li>
                                                    <li>Prepare your questions in advance</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="fa fa-check-circle text-success mr-2"></i>During Your Consultation</h6>
                                                <ul>
                                                    <li>Find a quiet, private space</li>
                                                    <li>Have good lighting on your face</li>
                                                    <li>Have your medical information ready</li>
                                                    <li>Take notes during the session</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
            $('#docFees').val('$' + fees);
        });
        
        // Calculate EDC when LMP is entered (40 weeks later)
        $('#lmp').change(function() {
            if(this.value) {
                var lmpDate = new Date(this.value);
                var edcDate = new Date(lmpDate);
                edcDate.setDate(edcDate.getDate() + 280); // 40 weeks
                
                // Format as YYYY-MM-DD
                var edcFormatted = edcDate.toISOString().split('T')[0];
                $('#edc').val(edcFormatted);
            }
        });
        
        // Video consultation button handler
        $('.video-conf-btn').click(function() {
            // In a real implementation, this would connect to a video API
            alert("Connecting to video consultation service...\nThis would integrate with a service like Zoom, Jitsi, or custom WebRTC solution in a production environment.");
        });
        
        // Disable past dates in appointment calendar
        var today = new Date().toISOString().split('T')[0];
        $('#appdate').attr('min', today);

        // Initialize tabs
        $('a[data-toggle="tab"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('.tab-pane').removeClass('show active');
            $(target).addClass('show active');
            
            // Update active state in sidebar
            $('.list-group-item').removeClass('active');
            $(this).addClass('active');
        });

        // Handle direct URL fragments (like #list-assessments)
        if(window.location.hash) {
            var hash = window.location.hash;
            $('.list-group-item[href="'+hash+'"]').click();
        }
    });
    </script>
</body>
</html>