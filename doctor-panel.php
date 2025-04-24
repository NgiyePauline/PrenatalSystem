<!DOCTYPE html>
<?php 


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

// Ensure doctor is logged in
if (!isset($_SESSION['dname'])) {
    header("Location: doctor-login.php");
    exit();
}

$doctor = $_SESSION['dname'];

// Handle appointment cancellation
if (isset($_GET['cancel']) && isset($_GET['ID'])) {
    $appointment_id = (int)$_GET['ID'];
    $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET doctorStatus='0' WHERE ID = ? AND doctor = ?");
    mysqli_stmt_bind_param($stmt, "is", $appointment_id, $doctor);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Appointment successfully cancelled';
    } else {
        $_SESSION['error'] = 'Failed to cancel appointment';
    }
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle appointment approval
if (isset($_GET['approve']) && isset($_GET['ID'])) {
    $appointment_id = (int)$_GET['ID'];
    $stmt = mysqli_prepare($con, "UPDATE appointmenttb SET doctorStatus='0', userStatus='0' WHERE ID = ? AND doctor = ?");
    mysqli_stmt_bind_param($stmt, "is", $appointment_id, $doctor);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Appointment successfully approved';
    } else {
        $_SESSION['error'] = 'Failed to approve appointment';
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
    <title>Doctor Panel - Prenatal Hospital System</title>
    
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
        .action-buttons .btn {
            margin: 2px;
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
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
                    <h5 style="color:orange;">Welcome Dr. <?php echo htmlspecialchars($_SESSION['dname'], ENT_QUOTES, 'UTF-8') ?></h5>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="post" action="search.php"> 
                <input class="form-control mr-sm-2" type="text" placeholder="Enter contact number" aria-label="Search" name="contact" required>
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
                    <a class="list-group-item list-group-item-action" href="#list-app" data-toggle="list">Appointments</a>
                    <a class="list-group-item list-group-item-action" href="prescribe.php">Prescribe</a>
                    <a class="list-group-item list-group-item-action" href="contact.html">Messages</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10">
                <div class="tab-content" id="nav-tabContent">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="list-dash" role="tabpanel">
                        <div class="container-fluid bg-white p-4 rounded">
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-list fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4 class="card-title">View Appointments</h4>
                                            <a href="#list-app" class="btn btn-primary" onclick="showAppointmentsTab()">Go to Appointments</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-list-ul fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4 class="card-title">Prescriptions</h4>
                                            <a href="prescribe.php" class="btn btn-primary">Prescribe</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <span class="fa-stack fa-2x mb-3">
                                                <i class="fa fa-square fa-stack-2x text-primary"></i>
                                                <i class="fa fa-envelope fa-stack-1x fa-inverse"></i>
                                            </span>
                                            <h4 class="card-title">Messages</h4>
                                            <a href="messages.php" class="btn btn-primary">Go to Messages</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Tab -->
                    <div class="tab-pane fade" id="list-app" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Appointment ID</th>
                                        <th>Patient Name</th>
                                        <th>Gender</th>
                                        <th>Contact</th>
                                        <th>Appointment Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $query = "SELECT pid, ID, CONCAT(fname, ' ', lname) AS patient_name, gender, contact, 
                                              appdate, apptime, userStatus, doctorStatus 
                                              FROM appointmenttb 
                                              WHERE doctor = ? 
                                              ORDER BY appdate DESC, apptime DESC";
                                    $stmt = mysqli_prepare($con, $query);
                                    mysqli_stmt_bind_param($stmt, "s", $doctor);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $status_class = '';
                                            $status_text = '';
                                            
                                            if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) {
                                                $status_class = 'status-active';
                                                $status_text = 'Active';
                                            } elseif ($row['userStatus'] == 0 && $row['doctorStatus'] == 1) {
                                                $status_class = 'status-cancelled';
                                                $status_text = 'Cancelled by Patient';
                                            } elseif ($row['userStatus'] == 0 && $row['doctorStatus'] == 0) {
                                                $status_class = 'status-approved';
                                                $status_text = 'Approved';
                                            } elseif ($row['userStatus'] == 1 && $row['doctorStatus'] == 0) {
                                                $status_class = 'status-cancelled';
                                                $status_text = 'Cancelled by You';
                                            }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['pid']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                        <td><?php echo htmlspecialchars($row['appdate']); ?></td>
                                        <td><?php echo htmlspecialchars($row['apptime']); ?></td>
                                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                        <td class="action-buttons">
                                            <?php if ($row['userStatus'] == 1 && $row['doctorStatus'] == 1) { ?>
                                                <a href="?ID=<?php echo $row['ID']; ?>&approve=update" 
                                                   class="btn btn-sm btn-success"
                                                   onclick="return confirm('Are you sure you want to approve this appointment?')">
                                                   Approve
                                                </a>
                                                <a href="?ID=<?php echo $row['ID']; ?>&cancel=update" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                   Cancel
                                                </a>
                                            <?php } elseif ($row['userStatus'] == 0 && $row['doctorStatus'] == 0) { ?>
                                                <a href="prescribe.php?pid=<?php echo $row['pid']; ?>&ID=<?php echo $row['ID']; ?>&fname=<?php echo urlencode(explode(' ', $row['patient_name'])[0]); ?>&lname=<?php echo urlencode(explode(' ', $row['patient_name'])[1]); ?>&appdate=<?php echo $row['appdate']; ?>&apptime=<?php echo $row['apptime']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                   Prescribe
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted">No actions available</span>
                                            <?php } ?>
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
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <script>
        // Function to show appointments tab
        function showAppointmentsTab() {
            // Remove active class from all list items
            $('.list-group-item').removeClass('active');
            // Add active class to appointments tab
            $('.list-group-item[href="#list-app"]').addClass('active');
            
            // Hide all tab content
            $('.tab-pane').removeClass('show active');
            // Show appointments tab content
            $('#list-app').addClass('show active');
            
            // Update URL hash
            window.location.hash = '#list-app';
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
        });
    </script>
</body>
</html>
<?php
mysqli_close($con);
?>