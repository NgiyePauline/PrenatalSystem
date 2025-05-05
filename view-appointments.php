<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check authentication
session_start();
if(!isset($_SESSION['national_id'])) {
    header("Location: index1.php");
    exit();
}

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

// Get all appointments for this patient
$appQuery = "SELECT a.*, d.username as doctor_name, d.spec as specialization 
             FROM appointmenttb a
             JOIN doctb d ON a.doctor = d.email
             WHERE a.pid = '$national_id'
             ORDER BY a.appdate DESC, a.apptime DESC";
$appResult = mysqli_query($con, $appQuery);

// Handle appointment cancellation
if(isset($_POST['cancel-appointment'])) {
    $appointmentId = mysqli_real_escape_string($con, $_POST['appointment_id']);
    
    // Check if appointment can be canceled (at least 24 hours before)
    $checkQuery = "SELECT appdate, apptime FROM appointmenttb WHERE id = '$appointmentId' AND pid = '$national_id'";
    $checkResult = mysqli_query($con, $checkQuery);
    $appointment = mysqli_fetch_assoc($checkResult);
    
    if($appointment) {
        $appointmentDateTime = new DateTime($appointment['appdate'] . ' ' . $appointment['apptime']);
        $currentDateTime = new DateTime();
        $interval = $currentDateTime->diff($appointmentDateTime);
        
        if($interval->days >= 1 || ($interval->days == 0 && $interval->h >= 24)) {
            $cancelQuery = "UPDATE appointmenttb SET userStatus = 0 WHERE id = '$appointmentId'";
            if(mysqli_query($con, $cancelQuery)) {
                $_SESSION['success'] = "Appointment canceled successfully!";
                // Also cancel the reminder
                $reminderQuery = "DELETE FROM reminders WHERE pid = '$national_id' AND 
                                 reminder_date = DATE_SUB('{$appointment['appdate']}', INTERVAL 1 DAY) AND
                                 message LIKE '%appointment%'";
                mysqli_query($con, $reminderQuery);
                header("Refresh:0");
                exit();
            } else {
                $_SESSION['error'] = "Error canceling appointment: " . mysqli_error($con);
            }
        } else {
            $_SESSION['error'] = "Appointments can only be canceled at least 24 hours in advance.";
        }
    } else {
        $_SESSION['error'] = "Appointment not found or you don't have permission to cancel it.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Appointments | Prenatal Care</title>
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
        
        .appointment-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .appointment-upcoming {
            border-left: 4px solid #28a745;
        }
        
        .appointment-past {
            border-left: 4px solid #6c757d;
        }
        
        .appointment-canceled {
            border-left: 4px solid #dc3545;
        }
        
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        
        .btn-cancel:hover {
            background-color: #c82333;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="admin-panel.php">
                <i class="fa fa-baby-carriage mr-2"></i>Prenatal Care Portal
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-panel.php">
                            <i class="fa fa-home mr-1"></i>Dashboard
                        </a>
                    </li>
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fa fa-calendar-alt mr-2"></i>Your Appointments
                        </h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <a href="appointments.php" class="btn btn-primary">
                                    <i class="fa fa-plus mr-2"></i>Book New Appointment
                                </a>
                            </div>
                            <!-- <div class="col-md-6 text-right">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-primary filter-btn" data-filter="upcoming">Upcoming</button>
                                    <button type="button" class="btn btn-outline-primary filter-btn" data-filter="past">Past</button>
                                </div>
                            </div>
                        </div> -->
                        
                        <?php if(mysqli_num_rows($appResult) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Doctor</th>
                                            <th>Specialization</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($app = mysqli_fetch_assoc($appResult)): 
                                            $appointmentDate = new DateTime($app['appdate']);
                                            $currentDate = new DateTime();
                                            $isUpcoming = $appointmentDate > $currentDate;
                                            $isCanceled = $app['userStatus'] == 0;
                                        ?>
                                            <tr class="appointment-row <?php echo $isUpcoming ? 'upcoming' : 'past'; ?> <?php echo $isCanceled ? 'canceled' : ''; ?>">
                                                <td><?php echo $appointmentDate->format('M j, Y'); ?></td>
                                                <td><?php echo date('g:i A', strtotime($app['apptime'])); ?></td>
                                                <td>Dr. <?php echo $app['doctor_name']; ?></td>
                                                <td><?php echo $app['specialization']; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $app['apptype'] == 'video' ? 'info' : 'primary'; ?>">
                                                        <?php echo ucfirst($app['apptype']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($isCanceled): ?>
                                                        <span class="badge badge-danger">Canceled</span>
                                                    <?php elseif($isUpcoming): ?>
                                                        <span class="badge badge-success">Upcoming</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Completed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($isUpcoming && !$isCanceled): ?>
                                                        <button class="btn btn-sm btn-cancel" data-toggle="modal" data-target="#cancelModal" 
                                                                data-appid="<?php echo $app['id']; ?>"
                                                                data-appdate="<?php echo $appointmentDate->format('M j, Y'); ?>"
                                                                data-apptime="<?php echo date('g:i A', strtotime($app['apptime'])); ?>"
                                                                data-docname="Dr. <?php echo $app['doctor_name']; ?>">
                                                            <i class="fa fa-times mr-1"></i>Cancel
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>No Appointments Found</h5>
                                <p>You don't have any appointments scheduled yet.</p>
                                <a href="appointments.php" class="btn btn-primary">
                                    <i class="fa fa-plus mr-2"></i>Book Appointment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Appointment Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this appointment?</p>
                        <p><strong>Date:</strong> <span id="modalAppDate"></span></p>
                        <p><strong>Time:</strong> <span id="modalAppTime"></span></p>
                        <p><strong>Doctor:</strong> <span id="modalDocName"></span></p>
                        <input type="hidden" name="appointment_id" id="modalAppId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="cancel-appointment" class="btn btn-danger">Confirm Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Filter appointments
        $('.filter-btn').click(function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            var filter = $(this).data('filter');
            
            if(filter === 'all') {
                $('.appointment-row').show();
            } else {
                $('.appointment-row').hide();
                $('.appointment-row.' + filter).show();
            }
        });
        
        // Cancel appointment modal
        $('#cancelModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var appId = button.data('appid');
            var appDate = button.data('appdate');
            var appTime = button.data('apptime');
            var docName = button.data('docname');
            
            var modal = $(this);
            modal.find('#modalAppId').val(appId);
            modal.find('#modalAppDate').text(appDate);
            modal.find('#modalAppTime').text(appTime);
            modal.find('#modalDocName').text(docName);
        });
    });
    </script>
</body>
</html>