<?php


// Include required files
require_once('func.php');
require_once('newfunc.php');
require_once('appfunc.php');


// Database connection
$con = mysqli_connect("localhost","root","","myhmsdb");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get session variables with proper validation
$email = $_SESSION['email'] ?? null;
$username = $_SESSION['username'] ?? 'User';

// Get patient appointments using email
try {
    $query = "SELECT 
                a.doctor, 
                d.username as doctorName, 
                d.spec as specialization, 
                DATE_FORMAT(a.appdate, '%Y-%m-%d') as appdate, 
                TIME_FORMAT(a.apptime, '%H:%i') as apptime, 
                a.userStatus 
              FROM appointmenttb a 
              JOIN doctb d ON a.doctor = d.username
              WHERE a.email = ? 
              ORDER BY a.appdate DESC";
              
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error_message'] = "Error loading appointments: " . $e->getMessage();
    $appointments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Appointments</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    
    <!-- CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <style>
        .status-confirmed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .action-btn { margin: 2px; }
        .no-appointments { text-align: center; padding: 20px; }
        body { padding-top: 70px; }
        .btn-disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }
    </style>
</head>
<body>
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

    <div class="container-fluid">
        <h3 class="text-center mb-4">Welcome <?php echo htmlspecialchars($username); ?></h3>
        
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success text-center">
                <?php echo $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger text-center">
                <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Navigation -->
            <div class="col-md-3">
                <div class="list-group" id="list-tab" role="tablist">
                    <a class="list-group-item list-group-item-action" href="admin-panel.php">Dashboard</a>
                    <a class="list-group-item list-group-item-action" href="appointments.php">Book Appointment</a>
                    <a class="list-group-item list-group-item-action" href="appointments.php">Appointments</a>
                    <a class="list-group-item list-group-item-action" href="prescriptions.php">Prescriptions</a>
                    <a class="list-group-item list-group-item-action" href="messages.php">Messages</a>
                </div>
            </div>
            
            <!-- Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">Your Appointments</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($appointments)): ?>
                            <div class="alert alert-info no-appointments">
                                <i class="fa fa-calendar-times fa-2x mb-3"></i>
                                <h5>No Appointments Found</h5>
                                <p>You haven't booked any appointments yet.</p>
                                <a href="appointments.php" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Book New Appointment
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Doctor</th>
                                            <th>Specialization</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $index => $appointment): ?>
                                            <?php 
                                            $appointmentDate = new DateTime($appointment['appdate']);
                                            $isUpcoming = $appointmentDate >= new DateTime('today');
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($appointment['doctorName']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                                <td><?php echo $appointmentDate->format('M d, Y'); ?></td>
                                                <td><?php echo date('h:i A', strtotime($appointment['apptime'])); ?></td>
                                                <td class="<?php 
                                                    echo $appointment['userStatus'] == 1 ? 'status-confirmed' : 
                                                         ($appointment['userStatus'] == 0 ? 'status-cancelled' : 'status-pending'); 
                                                ?>">
                                                    <?php 
                                                    echo $appointment['userStatus'] == 1 ? 'Confirmed' : 
                                                         ($appointment['userStatus'] == 0 ? 'Cancelled' : 'Pending'); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($appointment['userStatus'] == 1 && $isUpcoming): ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-danger btn-disabled" 
                                                                    title="Cannot cancel without appointment ID">
                                                                <i class="fa fa-times"></i> Cancel
                                                            </button>
                                                            <button class="btn btn-primary btn-disabled"
                                                                    title="Cannot reschedule without appointment ID">
                                                                <i class="fa fa-calendar-alt"></i> Reschedule
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>