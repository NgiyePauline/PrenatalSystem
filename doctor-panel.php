<!DOCTYPE html>
<?php 
session_start();

$allowed_includes = ['func1.php'];
$include_file = 'func1.php';

if(in_array($include_file, $allowed_includes) && file_exists(__DIR__.'/'.$include_file)) {
    include(__DIR__.'/'.$include_file);
} else {
    die('Invalid file inclusion attempt');
}

$con = mysqli_connect("localhost","root","","myhmsdb");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
error_reporting(0);

// Ensure provider is logged in


$provider_id = $_SESSION['provider_id'];
$provider_name = $_SESSION['provider_name'];
$provider_type = $_SESSION['provider_type']; // 'obgyn', 'midwife', 'nurse'

// Handle appointment actions for OBGYNs and midwives
if (($provider_type == 'obgyn' || $provider_type == 'midwife') && isset($_GET['action']) && isset($_GET['ID'])) {
    $appointment_id = (int)$_GET['ID'];
    $action = $_GET['action'];
    
    if ($action == 'cancel') {
        $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET providerStatus='0' WHERE ID = ? AND provider_id = ?");
        $success_msg = 'Appointment successfully cancelled';
        $error_msg = 'Failed to cancel appointment';
    } elseif ($action == 'approve') {
        $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET providerStatus='1', userStatus='0' WHERE ID = ? AND provider_id = ?");
        $success_msg = 'Appointment successfully approved';
        $error_msg = 'Failed to approve appointment';
    } elseif ($action == 'complete') {
        $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET status='completed' WHERE ID = ? AND provider_id = ?");
        $success_msg = 'Appointment marked as completed';
        $error_msg = 'Failed to complete appointment';
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $provider_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = $success_msg;
    } else {
        $_SESSION['error'] = $error_msg;
    }
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle health data submission (for all providers)
if (isset($_POST['submit_health_data'])) {
    $patient_id = mysqli_real_escape_string($con, $_POST['patient_id']);
    $blood_pressure = mysqli_real_escape_string($con, $_POST['blood_pressure']);
    $weight = mysqli_real_escape_string($con, $_POST['weight']);
    $fetal_heart_rate = isset($_POST['fetal_heart_rate']) ? mysqli_real_escape_string($con, $_POST['fetal_heart_rate']) : null;
    $uterus_height = isset($_POST['uterus_height']) ? mysqli_real_escape_string($con, $_POST['uterus_height']) : null;
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    
    $query = "INSERT INTO health_data (patient_id, provider_id, provider_type, blood_pressure, weight, fetal_heart_rate, uterus_height, notes) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "iisdsdds", $patient_id, $provider_id, $provider_type, $blood_pressure, $weight, $fetal_heart_rate, $uterus_height, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Health data successfully recorded';
    } else {
        $_SESSION['error'] = 'Failed to record health data';
    }
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle nurse observations
if ($provider_type == 'nurse' && isset($_POST['submit_observation'])) {
    $patient_id = mysqli_real_escape_string($con, $_POST['patient_id']);
    $observation_type = mysqli_real_escape_string($con, $_POST['observation_type']);
    $value = mysqli_real_escape_string($con, $_POST['value']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    
    $stmt = mysqli_prepare($con, "INSERT INTO nurse_observations (patient_id, nurse_id, observation_type, value, notes) 
                                  VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisss", $patient_id, $provider_id, $observation_type, $value, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Observation successfully recorded';
    } else {
        $_SESSION['error'] = 'Failed to record observation';
    }
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Display success/error messages
if (isset($_SESSION['success'])) {
    echo "<script>alert('".addslashes($_SESSION['success'])."');</script>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<script>alert('".addslashes($_SESSION['error'])."');</script>";
    unset($_SESSION['error']);
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo ucfirst($provider_type); ?> Panel - Prenatal Hospital System</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    
    <style>
        .btn-outline-light:hover {
            color: #25bef7;
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }
        .bg-primary {
            background: -webkit-linear-gradient(left, #3931af, #00c6ff);
        }
        .list-group-item.active {
            z-index: 2;
            color: orange;
            background-color: whitesmoke;
        }
        .text-primary {
            color: #342ac1!important;
        }
        .navbar {
            padding: 0.75rem 1rem;
        }
        .welcome-message {
            color: orange;
            padding: 0.5rem 1rem;
        }
        .tab-content {
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .status-approved {
            background: #cce5ff;
            color: #004085;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        .action-buttons .btn {
            margin: 2px;
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .health-indicator {
            margin-bottom: 15px;
        }
        .indicator-value {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .indicator-name {
            color: #666;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
            border: 2px solid white;
        }
        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .timeline-content {
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body style="padding-top:50px;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> PRENATAL HOSPITAL SYSTEM</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <h5 style="color:orange;">Welcome <?php 
                        $title = '';
                        switch($provider_type) {
                            case 'obgyn': $title = 'OB/GYN'; break;
                            case 'midwife': $title = 'Midwife'; break;
                            case 'nurse': $title = 'Nurse'; break;
                            default: $title = 'Provider';
                        }
                        echo $title . ' ' . htmlspecialchars($provider_name, ENT_QUOTES, 'UTF-8'); 
                    ?></h5>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="post" action="search.php"> 
                <input class="form-control mr-sm-2" type="text" placeholder="Enter patient ID" aria-label="Search" name="patient_id" required>
                <button type="submit" class="btn btn-outline-light my-2 my-sm-0" name="search_submit">Search</button>
                <a class="btn btn-outline-light ml-2" href="logout1.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
            </form>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2">
                <div class="list-group" id="list-tab" role="tablist">
                    <a class="list-group-item list-group-item-action active" href="#list-dash" data-toggle="list">Dashboard</a>
                    
                    <?php if ($provider_type == 'obgyn' || $provider_type == 'midwife'): ?>
                        <a class="list-group-item list-group-item-action" href="#list-app" data-toggle="list">Appointments</a>
                        <a class="list-group-item list-group-item-action" href="#list-patients" data-toggle="list">Patient Records</a>
                        <a class="list-group-item list-group-item-action" href="#list-monitor" data-toggle="list">Pregnancy Monitoring</a>
                        <a class="list-group-item list-group-item-action" href="#list-labreq" data-toggle="list">Lab Requests</a>
                        <a class="list-group-item list-group-item-action" href="#list-ultrasound" data-toggle="list">Ultrasound</a>
                        <?php if ($provider_type == 'obgyn'): ?>
                            <a class="list-group-item list-group-item-action" href="#list-surgical" data-toggle="list">Surgical Cases</a>
                        <?php endif; ?>
                    <?php elseif ($provider_type == 'nurse'): ?>
                        <a class="list-group-item list-group-item-action" href="#list-vitals" data-toggle="list">Vital Signs</a>
                        <a class="list-group-item list-group-item-action" href="#list-observations" data-toggle="list">Observations</a>
                        <a class="list-group-item list-group-item-action" href="#list-admissions" data-toggle="list">Admissions</a>
                    <?php endif; ?>
                    
                    <a class="list-group-item list-group-item-action" href="#list-messages" data-toggle="list">Messages</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10">
                <div class="tab-content" id="nav-tabContent">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="list-dash" role="tabpanel">
                        <div class="container-fluid bg-white p-4 rounded">
                            <div class="row">
                                <?php if ($provider_type == 'obgyn' || $provider_type == 'midwife'): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-calendar fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Appointments</h4>
                                                <a href="#list-app" class="btn btn-primary" onclick="showTab('list-app')">Manage Appointments</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-user-circle fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Patient Records</h4>
                                                <a href="#list-patients" class="btn btn-primary" onclick="showTab('list-patients')">View Records</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-heartbeat fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Pregnancy Monitoring</h4>
                                                <a href="#list-monitor" class="btn btn-primary" onclick="showTab('list-monitor')">Monitor Patients</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-flask fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Lab Requests</h4>
                                                <a href="#list-labreq" class="btn btn-primary" onclick="showTab('list-labreq')">Request Tests</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-image fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Ultrasound</h4>
                                                <a href="#list-ultrasound" class="btn btn-primary" onclick="showTab('list-ultrasound')">View Scans</a>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($provider_type == 'obgyn'): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card text-center h-100">
                                                <div class="card-body">
                                                    <span class="fa-stack fa-2x mb-3">
                                                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                        <i class="fa fa-scissors fa-stack-1x fa-inverse"></i>
                                                    </span>
                                                    <h4 class="card-title">Surgical Cases</h4>
                                                    <a href="#list-surgical" class="btn btn-primary" onclick="showTab('list-surgical')">View Cases</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif ($provider_type == 'nurse'): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-heartbeat fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Vital Signs</h4>
                                                <a href="#list-vitals" class="btn btn-primary" onclick="showTab('list-vitals')">Record Vitals</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-clipboard fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Observations</h4>
                                                <a href="#list-observations" class="btn btn-primary" onclick="showTab('list-observations')">Record Observations</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <span class="fa-stack fa-2x mb-3">
                                                    <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                    <i class="fa fa-procedures fa-stack-1x fa-inverse"></i>
                                                </span>
                                                <h4 class="card-title">Admissions</h4>
                                                <a href="#list-admissions" class="btn btn-primary" onclick="showTab('list-admissions')">Manage Admissions</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-envelope fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4 class="card-title">Messages</h4>
                                            <a href="#list-messages" class="btn btn-primary" onclick="showTab('list-messages')">View Messages</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Tab (for OBGYNs and midwives) -->
                    <?php if ($provider_type == 'obgyn' || $provider_type == 'midwife'): ?>
                    <div class="tab-pane fade" id="list-app" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Appointment ID</th>
                                        <th>Patient Name</th>
                                        <th>Gestation (weeks)</th>
                                        <th>Appointment Date</th>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $query = "SELECT a.pid, a.ID, CONCAT(p.fname, ' ', p.lname) AS patient_name, 
                                              TIMESTAMPDIFF(WEEK, p.edd, CURDATE()) AS gestation_weeks,
                                              a.appdate, a.apptime, a.apptype, a.userStatus, a.providerStatus, a.status 
                                              FROM appointmenttb a
                                              JOIN patienttb p ON a.pid = p.pid
                                              WHERE a.provider_id = ? AND a.provider_type = ?
                                              ORDER BY a.appdate DESC, a.apptime DESC";
                                    $stmt = mysqli_prepare($con, $query);
                                    mysqli_stmt_bind_param($stmt, "is", $provider_id, $provider_type);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $status_class = '';
                                            $status_text = '';
                                            
                                            if ($row['status'] == 'completed') {
                                                $status_class = 'status-completed';
                                                $status_text = 'Completed';
                                            } elseif ($row['userStatus'] == 1 && $row['providerStatus'] == 1) {
                                                $status_class = 'status-active';
                                                $status_text = 'Active';
                                            } elseif ($row['userStatus'] == 0 && $row['providerStatus'] == 1) {
                                                $status_class = 'status-cancelled';
                                                $status_text = 'Cancelled by Patient';
                                            } elseif ($row['userStatus'] == 1 && $row['providerStatus'] == 0) {
                                                $status_class = 'status-cancelled';
                                                $status_text = 'Cancelled by Provider';
                                            } elseif ($row['userStatus'] == 0 && $row['providerStatus'] == 0) {
                                                $status_class = 'status-approved';
                                                $status_text = 'Approved';
                                            }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['pid']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gestation_weeks']); ?></td>
                                        <td><?php echo htmlspecialchars($row['appdate']); ?></td>
                                        <td><?php echo htmlspecialchars($row['apptime']); ?></td>
                                        <td><?php echo htmlspecialchars($row['apptype']); ?></td>
                                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                        <td class="action-buttons">
                                            <?php if ($row['status'] != 'completed'): ?>
                                                <?php if ($row['userStatus'] == 1 && $row['providerStatus'] == 1): ?>
                                                    <a href="?ID=<?php echo $row['ID']; ?>&action=approve" 
                                                       class="btn btn-sm btn-success"
                                                       onclick="return confirm('Approve this appointment?')">
                                                       Approve
                                                    </a>
                                                    <a href="?ID=<?php echo $row['ID']; ?>&action=cancel" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Cancel this appointment?')">
                                                       Cancel
                                                    </a>
                                                <?php elseif ($row['userStatus'] == 0 && $row['providerStatus'] == 0): ?>
                                                    <a href="?ID=<?php echo $row['ID']; ?>&action=complete" 
                                                       class="btn btn-sm btn-primary"
                                                       onclick="return confirm('Mark this appointment as completed?')">
                                                       Complete
                                                    </a>
                                                    <a href="patient_record.php?pid=<?php echo $row['pid']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                       Record
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="patient_record.php?pid=<?php echo $row['pid']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                   View Record
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo '<tr><td colspan="9" class="text-center">No appointments found</td></tr>';
                                    }
                                    mysqli_stmt_close($stmt);
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Patient Records Tab -->
                    <div class="tab-pane fade" id="list-patients" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gestation</th>
                                        <th>Blood Type</th>
                                        <th>Last Visit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $query = "SELECT p.pid, p.fname, p.lname, p.blood_type, p.edd,
                                              TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age,
                                              TIMESTAMPDIFF(WEEK, p.edd, CURDATE()) AS gestation_weeks,
                                              MAX(a.appdate) AS last_visit
                                              FROM patienttb p
                                              LEFT JOIN appointmenttb a ON p.pid = a.pid
                                              WHERE a.provider_id = ? AND a.provider_type = ? OR p.primary_provider_id = ?
                                              GROUP BY p.pid
                                              ORDER BY last_visit DESC";
                                    $stmt = mysqli_prepare($con, $query);
                                    mysqli_stmt_bind_param($stmt, "isi", $provider_id, $provider_type, $provider_id);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['pid']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gestation_weeks']); ?> weeks</td>
                                        <td><?php echo htmlspecialchars($row['blood_type']); ?></td>
                                        <td><?php echo $row['last_visit'] ? htmlspecialchars($row['last_visit']) : 'Never'; ?></td>
                                        <td>
                                            <a href="patient_record.php?pid=<?php echo $row['pid']; ?>" class="btn btn-sm btn-info">View Record</a>
                                            <a href="#list-monitor" class="btn btn-sm btn-warning" onclick="loadPatientHealth(<?php echo $row['pid']; ?>)">Monitor</a>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">No patients found</td></tr>';
                                    }
                                    mysqli_stmt_close($stmt);
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pregnancy Monitoring Tab -->
                    <div class="tab-pane fade" id="list-monitor" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Pregnancy Monitoring</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newHealthDataModal">
                                    <i class="fa fa-plus"></i> New Entry
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="patientHealthInfo">
                                    <div class="alert alert-info">
                                        Please select a patient from the Patient Records tab to view their pregnancy monitoring data.
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h5>Pregnancy Timeline</h5>
                                    <div class="timeline" id="pregnancyTimeline">
                                        <!-- Timeline will be loaded via AJAX -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Health Data Modal -->
                    <div class="modal fade" id="newHealthDataModal" tabindex="-1" role="dialog" aria-labelledby="newHealthDataModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newHealthDataModalLabel">Record New Health Data</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="">
                                    <div class="modal-body">
                                        <input type="hidden" name="patient_id" id="modal_patient_id">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="modal_blood_pressure">Blood Pressure</label>
                                                    <input type="text" class="form-control" id="modal_blood_pressure" name="blood_pressure" placeholder="120/80">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="modal_weight">Weight (kg)</label>
                                                    <input type="number" step="0.1" class="form-control" id="modal_weight" name="weight">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="modal_fetal_heart_rate">Fetal Heart Rate</label>
                                                    <input type="number" class="form-control" id="modal_fetal_heart_rate" name="fetal_heart_rate">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="modal_uterus_height">Uterus Height (cm)</label>
                                                    <input type="number" step="0.1" class="form-control" id="modal_uterus_height" name="uterus_height">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="modal_urine_test">Urine Test</label>
                                                    <select class="form-control" id="modal_urine_test" name="urine_test">
                                                        <option value="">Select</option>
                                                        <option value="normal">Normal</option>
                                                        <option value="proteinuria">Proteinuria</option>
                                                        <option value="glucosuria">Glucosuria</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="modal_edema">Edema</label>
                                                    <select class="form-control" id="modal_edema" name="edema">
                                                        <option value="none">None</option>
                                                        <option value="mild">Mild</option>
                                                        <option value="moderate">Moderate</option>
                                                        <option value="severe">Severe</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="modal_notes">Clinical Notes</label>
                                            <textarea class="form-control" id="modal_notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="submit_health_data" class="btn btn-primary">Save Data</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Requests Tab -->
                    <div class="tab-pane fade" id="list-labreq" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Lab Test Requests</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newLabRequestModal">
                                    <i class="fa fa-plus"></i> New Request
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Patient</th>
                                                <th>Test Type</th>
                                                <th>Date Requested</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT lr.id, p.fname, p.lname, lr.test_type, lr.date_requested, lr.status 
                                                      FROM lab_requests lr
                                                      JOIN patienttb p ON lr.patient_id = p.pid
                                                      WHERE lr.provider_id = ? AND lr.provider_type = ?
                                                      ORDER BY lr.date_requested DESC";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "is", $provider_id, $provider_type);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $status_class = $row['status'] == 'completed' ? 'status-approved' : 'status-pending';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['test_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['date_requested']); ?></td>
                                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                                <td>
                                                    <?php if ($row['status'] == 'completed'): ?>
                                                        <a href="view_lab_result.php?request_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View Results</a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Pending lab results</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">No lab requests found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Lab Request Modal -->
                    <div class="modal fade" id="newLabRequestModal" tabindex="-1" role="dialog" aria-labelledby="newLabRequestModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newLabRequestModalLabel">New Lab Test Request</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="submit_lab_request.php">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="lab_patient_select">Patient</label>
                                            <select class="form-control" id="lab_patient_select" name="patient_id" required>
                                                <option value="">Select Patient</option>
                                                <?php 
                                                $query = "SELECT p.pid, p.fname, p.lname 
                                                          FROM patienttb p
                                                          JOIN appointmenttb a ON p.pid = a.pid
                                                          WHERE a.provider_id = ? AND a.provider_type = ? AND a.userStatus = 0 AND a.providerStatus = 0
                                                          GROUP BY p.pid";
                                                $stmt = mysqli_prepare($con, $query);
                                                mysqli_stmt_bind_param($stmt, "is", $provider_id, $provider_type);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<option value="' . htmlspecialchars($row['pid']) . '">' . 
                                                         htmlspecialchars($row['fname'] . ' ' . $row['lname']) . ' (ID: ' . 
                                                         htmlspecialchars($row['pid']) . ')</option>';
                                                }
                                                mysqli_stmt_close($stmt);
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="prenatal_test_type">Test Type</label>
                                            <select class="form-control" id="prenatal_test_type" name="test_type" required>
                                                <option value="">Select Test Type</option>
                                                <option value="blood_count">Complete Blood Count</option>
                                                <option value="blood_group">Blood Group & Rh</option>
                                                <option value="glucose">Glucose Tolerance</option>
                                                <option value="urinalysis">Urinalysis</option>
                                                <option value="strep_b">Group B Strep</option>
                                                <option value="std">STD Screening</option>
                                                <option value="genetic">Genetic Testing</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="test_reason">Reason for Test</label>
                                            <textarea class="form-control" id="test_reason" name="test_reason" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="test_urgency">Urgency</label>
                                            <select class="form-control" id="test_urgency" name="urgency" required>
                                                <option value="routine">Routine</option>
                                                <option value="urgent">Urgent</option>
                                                <option value="stat">STAT (Immediate)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Submit Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Ultrasound Tab -->
                    <div class="tab-pane fade" id="list-ultrasound" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Ultrasound Records</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newUltrasoundModal">
                                    <i class="fa fa-plus"></i> New Scan
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Scan ID</th>
                                                <th>Patient</th>
                                                <th>Date</th>
                                                <th>Gestation</th>
                                                <th>Type</th>
                                                <th>Findings</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT u.id, p.fname, p.lname, u.scan_date, 
                                                      TIMESTAMPDIFF(WEEK, p.edd, u.scan_date) AS gestation_weeks,
                                                      u.scan_type, u.summary_findings
                                                      FROM ultrasound_scans u
                                                      JOIN patienttb p ON u.patient_id = p.pid
                                                      WHERE u.provider_id = ? AND u.provider_type = ?
                                                      ORDER BY u.scan_date DESC";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "is", $provider_id, $provider_type);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['scan_date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['gestation_weeks']); ?> weeks</td>
                                                <td><?php echo htmlspecialchars($row['scan_type']); ?></td>
                                                <td><?php echo strlen($row['summary_findings']) > 50 ? 
                                                    htmlspecialchars(substr($row['summary_findings'], 0, 50)) . '...' : 
                                                    htmlspecialchars($row['summary_findings']); ?></td>
                                                <td>
                                                    <a href="view_ultrasound.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center">No ultrasound records found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Ultrasound Modal -->
                    <div class="modal fade" id="newUltrasoundModal" tabindex="-1" role="dialog" aria-labelledby="newUltrasoundModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newUltrasoundModalLabel">New Ultrasound Record</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="submit_ultrasound.php" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="us_patient_select">Patient</label>
                                                    <select class="form-control" id="us_patient_select" name="patient_id" required>
                                                        <option value="">Select Patient</option>
                                                        <?php 
                                                        $query = "SELECT p.pid, p.fname, p.lname 
                                                                  FROM patienttb p
                                                                  JOIN appointmenttb a ON p.pid = a.pid
                                                                  WHERE a.provider_id = ? AND a.provider_type = ?
                                                                  GROUP BY p.pid";
                                                        $stmt = mysqli_prepare($con, $query);
                                                        mysqli_stmt_bind_param($stmt, "is", $provider_id, $provider_type);
                                                        mysqli_stmt_execute($stmt);
                                                        $result = mysqli_stmt_get_result($stmt);
                                                        
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            echo '<option value="' . htmlspecialchars($row['pid']) . '">' . 
                                                                 htmlspecialchars($row['fname'] . ' ' . $row['lname']) . ' (ID: ' . 
                                                                 htmlspecialchars($row['pid']) . ')</option>';
                                                        }
                                                        mysqli_stmt_close($stmt);
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="scan_date">Scan Date</label>
                                                    <input type="date" class="form-control" id="scan_date" name="scan_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="scan_type">Scan Type</label>
                                                    <select class="form-control" id="scan_type" name="scan_type" required>
                                                        <option value="">Select Scan Type</option>
                                                        <option value="dating">Dating Scan</option>
                                                        <option value="nt">NT Scan</option>
                                                        <option value="anomaly">Anomaly Scan</option>
                                                        <option value="growth">Growth Scan</option>
                                                        <option value="doppler">Doppler Scan</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="scan_image">Scan Image</label>
                                                    <input type="file" class="form-control-file" id="scan_image" name="scan_image">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="findings">Findings</label>
                                            <textarea class="form-control" id="findings" name="findings" rows="5" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="interpretation">Interpretation</label>
                                            <textarea class="form-control" id="interpretation" name="interpretation" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Record</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Surgical Cases Tab (for OBGYNs only) -->
                    <?php if ($provider_type == 'obgyn'): ?>
                    <div class="tab-pane fade" id="list-surgical" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Surgical Cases</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newSurgicalCaseModal">
                                    <i class="fa fa-plus"></i> New Case
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Case ID</th>
                                                <th>Patient</th>
                                                <th>Procedure</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT s.id, p.fname, p.lname, s.procedure_type, s.procedure_date, s.status 
                                                      FROM surgical_cases s
                                                      JOIN patienttb p ON s.patient_id = p.pid
                                                      WHERE s.provider_id = ?
                                                      ORDER BY s.procedure_date DESC";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "i", $provider_id);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $status_class = '';
                                                    if ($row['status'] == 'completed') $status_class = 'status-completed';
                                                    elseif ($row['status'] == 'scheduled') $status_class = 'status-approved';
                                                    elseif ($row['status'] == 'cancelled') $status_class = 'status-cancelled';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['procedure_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['procedure_date']); ?></td>
                                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                                <td>
                                                    <a href="view_surgical_case.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">No surgical cases found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Surgical Case Modal -->
                    <div class="modal fade" id="newSurgicalCaseModal" tabindex="-1" role="dialog" aria-labelledby="newSurgicalCaseModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newSurgicalCaseModalLabel">New Surgical Case</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="submit_surgical_case.php">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="surgical_patient_select">Patient</label>
                                                    <select class="form-control" id="surgical_patient_select" name="patient_id" required>
                                                        <option value="">Select Patient</option>
                                                        <?php 
                                                        $query = "SELECT p.pid, p.fname, p.lname 
                                                                  FROM patienttb p
                                                                  JOIN appointmenttb a ON p.pid = a.pid
                                                                  WHERE a.provider_id = ? AND a.provider_type = ?
                                                                  GROUP BY p.pid";
                                                        $stmt = mysqli_prepare($con, $query);
                                                        mysqli_stmt_bind_param($stmt, "is", $provider_id, $provider_type);
                                                        mysqli_stmt_execute($stmt);
                                                        $result = mysqli_stmt_get_result($stmt);
                                                        
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            echo '<option value="' . htmlspecialchars($row['pid']) . '">' . 
                                                                 htmlspecialchars($row['fname'] . ' ' . $row['lname']) . ' (ID: ' . 
                                                                 htmlspecialchars($row['pid']) . ')</option>';
                                                        }
                                                        mysqli_stmt_close($stmt);
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="procedure_date">Procedure Date</label>
                                                    <input type="datetime-local" class="form-control" id="procedure_date" name="procedure_date" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="procedure_type">Procedure Type</label>
                                                    <select class="form-control" id="procedure_type" name="procedure_type" required>
                                                        <option value="">Select Procedure</option>
                                                        <option value="c_section">Cesarean Section</option>
                                                        <option value="hysterectomy">Hysterectomy</option>
                                                        <option value="myomectomy">Myomectomy</option>
                                                        <option value="laparoscopy">Laparoscopy</option>
                                                        <option value="dilation_curettage">Dilation & Curettage</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="procedure_status">Status</label>
                                                    <select class="form-control" id="procedure_status" name="status" required>
                                                        <option value="scheduled">Scheduled</option>
                                                        <option value="completed">Completed</option>
                                                        <option value="cancelled">Cancelled</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="procedure_notes">Notes</label>
                                            <textarea class="form-control" id="procedure_notes" name="notes" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="post_op_instructions">Post-Op Instructions</label>
                                            <textarea class="form-control" id="post_op_instructions" name="post_op_instructions" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Case</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php elseif ($provider_type == 'nurse'): ?>
                    <!-- Vital Signs Tab -->
                    <div class="tab-pane fade" id="list-vitals" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Vital Signs Monitoring</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newVitalSignsModal">
                                    <i class="fa fa-plus"></i> New Record
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date/Time</th>
                                                <th>BP</th>
                                                <th>Pulse</th>
                                                <th>Temp (°C)</th>
                                                <th>Resp. Rate</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT v.id, p.fname, p.lname, v.recorded_at, v.blood_pressure, 
                                                      v.heart_rate, v.temperature, v.respiratory_rate
                                                      FROM vital_signs v
                                                      JOIN patienttb p ON v.patient_id = p.pid
                                                      WHERE v.recorded_by = ?
                                                      ORDER BY v.recorded_at DESC
                                                      LIMIT 50";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "i", $provider_id);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['recorded_at']); ?></td>
                                                <td><?php echo htmlspecialchars($row['blood_pressure']); ?></td>
                                                <td><?php echo htmlspecialchars($row['heart_rate']); ?></td>
                                                <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                                                <td><?php echo htmlspecialchars($row['respiratory_rate']); ?></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center">No vital signs records found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Vital Signs Modal -->
                    <div class="modal fade" id="newVitalSignsModal" tabindex="-1" role="dialog" aria-labelledby="newVitalSignsModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newVitalSignsModalLabel">Record Vital Signs</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="submit_vital_signs.php">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="vitals_patient_select">Patient</label>
                                            <select class="form-control" id="vitals_patient_select" name="patient_id" required>
                                                <option value="">Select Patient</option>
                                                <?php 
                                                $query = "SELECT p.pid, p.fname, p.lname 
                                                          FROM patienttb p
                                                          JOIN admissions a ON p.pid = a.patient_id
                                                          WHERE a.discharge_date IS NULL";
                                                $stmt = mysqli_prepare($con, $query);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<option value="' . htmlspecialchars($row['pid']) . '">' . 
                                                         htmlspecialchars($row['fname'] . ' ' . $row['lname']) . ' (ID: ' . 
                                                         htmlspecialchars($row['pid']) . ')</option>';
                                                }
                                                mysqli_stmt_close($stmt);
                                                ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="blood_pressure">Blood Pressure</label>
                                                    <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" placeholder="120/80" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="heart_rate">Heart Rate (bpm)</label>
                                                    <input type="number" class="form-control" id="heart_rate" name="heart_rate" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="temperature">Temperature (°C)</label>
                                                    <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="respiratory_rate">Respiratory Rate</label>
                                                    <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="vitals_notes">Notes</label>
                                            <textarea class="form-control" id="vitals_notes" name="notes" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Record</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Observations Tab -->
                    <div class="tab-pane fade" id="list-observations" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Nursing Observations</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newObservationModal">
                                    <i class="fa fa-plus"></i> New Observation
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date/Time</th>
                                                <th>Observation</th>
                                                <th>Value</th>
                                                <th>Notes</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT o.id, p.fname, p.lname, o.observation_date, o.observation_type, o.value, o.notes 
                                                      FROM nurse_observations o
                                                      JOIN patienttb p ON o.patient_id = p.pid
                                                      WHERE o.nurse_id = ?
                                                      ORDER BY o.observation_date DESC
                                                      LIMIT 50";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "i", $provider_id);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['observation_date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['observation_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['value']); ?></td>
                                                <td><?php echo strlen($row['notes']) > 30 ? 
                                                    htmlspecialchars(substr($row['notes'], 0, 30)) . '...' : 
                                                    htmlspecialchars($row['notes']); ?></td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">No observations found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Observation Modal -->
                    <div class="modal fade" id="newObservationModal" tabindex="-1" role="dialog" aria-labelledby="newObservationModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newObservationModalLabel">New Observation</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="obs_patient_select">Patient</label>
                                            <select class="form-control" id="obs_patient_select" name="patient_id" required>
                                                <option value="">Select Patient</option>
                                                <?php 
                                                $query = "SELECT p.pid, p.fname, p.lname 
                                                          FROM patienttb p
                                                          JOIN admissions a ON p.pid = a.patient_id
                                                          WHERE a.discharge_date IS NULL";
                                                $stmt = mysqli_prepare($con, $query);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<option value="' . htmlspecialchars($row['pid']) . '">' . 
                                                         htmlspecialchars($row['fname'] . ' ' . $row['lname']) . ' (ID: ' . 
                                                         htmlspecialchars($row['pid']) . ')</option>';
                                                }
                                                mysqli_stmt_close($stmt);
                                                ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="observation_type">Observation Type</label>
                                                    <select class="form-control" id="observation_type" name="observation_type" required>
                                                        <option value="">Select Type</option>
                                                        <option value="fetal_movement">Fetal Movement</option>
                                                        <option value="contractions">Contractions</option>
                                                        <option value="fluid_loss">Fluid Loss</option>
                                                        <option value="pain_level">Pain Level</option>
                                                        <option value="bleeding">Bleeding</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="observation_value">Value</label>
                                                    <input type="text" class="form-control" id="observation_value" name="value" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="observation_notes">Notes</label>
                                            <textarea class="form-control" id="observation_notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="submit_observation" class="btn btn-primary">Save Observation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Admissions Tab -->
                    <div class="tab-pane fade" id="list-admissions" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4>Patient Admissions</h4>
                                <button class="btn btn-light" data-toggle="modal" data-target="#newAdmissionModal">
                                    <i class="fa fa-plus"></i> New Admission
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Admission Date</th>
                                                <th>Reason</th>
                                                <th>Ward/Bed</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT a.id, p.fname, p.lname, a.admission_date, a.reason, 
                                                      a.ward, a.bed_number, a.discharge_date
                                                      FROM admissions a
                                                      JOIN patienttb p ON a.patient_id = p.pid
                                                      WHERE a.admitted_by = ?
                                                      ORDER BY a.admission_date DESC";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "i", $provider_id);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $status = $row['discharge_date'] ? 'Discharged' : 'Admitted';
                                                    $status_class = $row['discharge_date'] ? 'status-completed' : 'status-approved';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['admission_date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                                <td><?php echo htmlspecialchars($row['ward']) . ' / ' . htmlspecialchars($row['bed_number']); ?></td>
                                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                                                <td>
                                                    <?php if (!$row['discharge_date']): ?>
                                                        <a href="#" class="btn btn-sm btn-success" onclick="dischargePatient(<?php echo $row['id']; ?>)">Discharge</a>
                                                    <?php endif; ?>
                                                    <a href="#" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">No admissions found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Admission Modal -->
                    <div class="modal fade" id="newAdmissionModal" tabindex="-1" role="dialog" aria-labelledby="newAdmissionModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="newAdmissionModalLabel">New Patient Admission</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="post" action="submit_admission.php">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="adm_patient_select">Patient</label>
                                            <select class="form-control" id="adm_patient_select" name="patient_id" required>
                                                <option value="">Select Patient</option>
                                                <?php 
                                                $query = "SELECT p.pid, p.fname, p.lname 
                                                          FROM patienttb p
                                                          LEFT JOIN admissions a ON p.pid = a.patient_id AND a.discharge_date IS NULL
                                                          WHERE a.id IS NULL";
                                                $stmt = mysqli_prepare($con, $query);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<option value="' . htmlspecialchars($row['pid']) . '">' . 
                                                         htmlspecialchars($row['fname'] . ' ' . $row['lname']) . ' (ID: ' . 
                                                         htmlspecialchars($row['pid']) . ')</option>';
                                                }
                                                mysqli_stmt_close($stmt);
                                                ?>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="admission_reason">Reason</label>
                                                    <select class="form-control" id="admission_reason" name="reason" required>
                                                        <option value="">Select Reason</option>
                                                        <option value="labor">Labor</option>
                                                        <option value="observation">Observation</option>
                                                        <option value="scheduled_delivery">Scheduled Delivery</option>
                                                        <option value="complications">Complications</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="admission_date">Admission Date</label>
                                                    <input type="datetime-local" class="form-control" id="admission_date" name="admission_date" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ward">Ward</label>
                                                    <select class="form-control" id="ward" name="ward" required>
                                                        <option value="">Select Ward</option>
                                                        <option value="antenatal">Antenatal</option>
                                                        <option value="postnatal">Postnatal</option>
                                                        <option value="labor">Labor Ward</option>
                                                        <option value="high_risk">High Risk</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bed_number">Bed Number</label>
                                                    <input type="text" class="form-control" id="bed_number" name="bed_number" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="adm_notes">Notes</label>
                                            <textarea class="form-control" id="adm_notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Admit Patient</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                    <!-- Messages Tab -->
                    <div class="tab-pane fade" id="list-messages" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h4>Messages</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>From</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT m.id, u.name AS sender, m.subject, m.sent_at, m.read_status 
                                                      FROM messages m
                                                      JOIN users u ON m.sender_id = u.id
                                                      WHERE m.recipient_id = ?
                                                      ORDER BY m.sent_at DESC";
                                            $stmt = mysqli_prepare($con, $query);
                                            mysqli_stmt_bind_param($stmt, "i", $provider_id);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $status_class = $row['read_status'] ? 'status-completed' : 'status-pending';
                                                    $status_text = $row['read_status'] ? 'Read' : 'Unread';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['sender']); ?></td>
                                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                                <td>
                                                    <a href="view_message.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo '<tr><td colspan="5" class="text-center">No messages found</td></tr>';
                                            }
                                            mysqli_stmt_close($stmt);
                                            ?>
                                        </tbody>
                                    </table>
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
        // Function to show specific tab
        function showTab(tabId) {
            // Remove active class from all list items
            $('.list-group-item').removeClass('active');
            // Add active class to target tab
            $('.list-group-item[href="#' + tabId + '"]').addClass('active');
            
            // Hide all tab content
            $('.tab-pane').removeClass('show active');
            // Show target tab content
            $('#' + tabId).addClass('show active');
            
            // Update URL hash
            window.location.hash = '#' + tabId;
        }

        // Auto-click tab from URL hash
        $(document).ready(function() {
            if (window.location.hash) {
                $('.list-group-item[href="' + window.location.hash + '"]').tab('show');
            }
            
            // Update URL hash when tab changes
            $('a[data-toggle="list"]').on('shown.bs.tab', function(e) {
                window.location.hash = e.target.hash;
            });
            
            // For OBGYNs/Midwives: Load patient health data
            window.loadPatientHealth = function(patientId) {
                $('#modal_patient_id').val(patientId);
                $('#health_patient_id').val(patientId);
                
                // Show loading message
                $('#patientHealthInfo').html('<div class="alert alert-info">Loading health data...</div>');
                $('#pregnancyTimeline').html('<div class="alert alert-info">Loading timeline...</div>');
                
                // Load health data
                $.ajax({
                    url: 'get_patient_health.php',
                    type: 'GET',
                    data: { patient_id: patientId },
                    success: function(response) {
                        $('#patientHealthInfo').html(response);
                    },
                    error: function() {
                        $('#patientHealthInfo').html('<div class="alert alert-danger">Failed to load health data</div>');
                    }
                });
                
                // Load timeline
                $.ajax({
                    url: 'get_pregnancy_timeline.php',
                    type: 'GET',
                    data: { patient_id: patientId },
                    success: function(response) {
                        $('#pregnancyTimeline').html(response);
                    },
                    error: function() {
                        $('#pregnancyTimeline').html('<div class="alert alert-danger">Failed to load timeline</div>');
                    }
                });
                
                showTab('list-monitor');
            };
            
            // For Nurses: Discharge patient
            window.dischargePatient = function(admissionId) {
                if (confirm('Are you sure you want to discharge this patient?')) {
                    $.ajax({
                        url: 'discharge_patient.php',
                        type: 'POST',
                        data: { admission_id: admissionId },
                        success: function(response) {
                            alert('Patient discharged successfully');
                            location.reload();
                        },
                        error: function() {
                            alert('Failed to discharge patient');
                        }
                    });
                }
            };
        });
    </script>
</body>
</html>
<?php
mysqli_close($con);
?>