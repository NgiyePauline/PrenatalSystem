<?php

require_once('func.php');
require_once('newfunc.php');
require_once('appfunc.php');

if(!isset($_SESSION['email'])) {
    header("Location: index1.php");
    exit();
}

$con = mysqli_connect("localhost","root","","myhmsdb");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

$email = $_SESSION['email'];
$username = $_SESSION['username'];

// Handle form submission
if(isset($_POST['book-appointment'])) {
    $provider_type = $_POST['provider_type'];
    $provider_id = (int)$_POST['provider_id'];
    $appdate = $_POST['appdate'];
    $apptime = $_POST['apptime'];
    $reason = mysqli_real_escape_string($con, $_POST['reason']);
    
    // Validate appointment date (not in past)
    if(strtotime($appdate) < strtotime('today')) {
        $_SESSION['error_message'] = "Appointment date cannot be in the past";
        header("Location: appointments.php");
        exit();
    }
    
    // Check availability
    $check_query = "SELECT * FROM appointmenttb 
                   WHERE doctor = ? AND appdate = ? AND apptime = ?
                   AND userStatus != 0"; // 0 = cancelled
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("iss", $provider_id, $appdate, $apptime);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $_SESSION['error_message'] = "This time slot is already booked. Please choose another time.";
    } else {
        // Get provider details
        $provider_query = "SELECT email, username, spec, provider_type FROM doctb WHERE email = ?";
        $stmt = $con->prepare($provider_query);
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $provider = $stmt->get_result()->fetch_assoc();
        
        // Book appointment
        $insert_query = "INSERT INTO appointmenttb 
                        (email, fname, doctor, appdate, apptime, userStatus, reason, provider_type) 
                        VALUES (?, ?, ?, ?, ?, 2, ?, ?)"; // 2 = pending
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("sssssss", 
            $email,
            $username,
            $provider['username'],
            $appdate,
            $apptime,
            $reason,
            $provider['provider_type']
        );
        
        if($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment request sent successfully!";
        } else {
            $_SESSION['error_message'] = "Error booking appointment: " . $con->error;
        }
    }
    header("Location: appointments.php");
    exit();
}

// Get available providers grouped by type
$providers = [];
$provider_types = [
    'obstetrician_gynaecologist' => 'Obstetrician/Gynaecologist',
    'midwife' => 'Midwife',
    'general_practitioner' => 'General Practitioner',
    'nurse' => 'Nurse',
    'lab_technician' => 'Lab Technician'
];

foreach($provider_types as $type => $label) {
    $query = "SELECT email, username, spec FROM doctb WHERE provider_type = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $providers[$type] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Appointment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            padding-top: 70px; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .provider-type { 
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .provider-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            transition: transform 0.2s;
        }
        .provider-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            background-color: #f8f9fa;
            border-bottom: 3px solid #342ac1;
        }
        .btn-book {
            background-color: #342ac1;
            border-color: #342ac1;
        }
        .btn-book:hover {
            background-color: #2a238f;
            border-color: #2a238f;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="patient-dashboard.php">
                <i class="fas fa-hospital-alt mr-2"></i>Prenatal Care Portal
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-panel.php"><i class="fas fa-home mr-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="appointments.php"><i class="fas fa-calendar-check mr-1"></i> Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prenatal-register.php"><i class="fas fa-baby mr-1"></i> Prenatal Care</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle mr-1"></i> <?php echo htmlspecialchars($username); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="patient-profile.php"><i class="fas fa-user mr-1"></i> My Profile</a>
                            <a class="dropdown-item" href="view-appointments.php"><i class="fas fa-history mr-1"></i> Appointment History</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="text-center mb-4"><i class="fas fa-calendar-plus mr-2"></i>Book Appointment</h3>
        
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs mb-4" id="providerTabs" role="tablist">
            <?php foreach($provider_types as $type => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $type === 'obstetrician_gynaecologist' ? 'active' : ''; ?>" 
                       id="<?php echo $type; ?>-tab" data-toggle="tab" href="#<?php echo $type; ?>" role="tab">
                       <i class="fas fa-<?php 
                           echo $type === 'obstetrician_gynaecologist' ? 'user-md' : 
                                ($type === 'midwife' ? 'hands-helping' : 
                                ($type === 'general_practitioner' ? 'stethoscope' : 
                                ($type === 'nurse' ? 'user-nurse' : 'flask'))); 
                       ?> mr-1"></i>
                       <?php echo $label; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="tab-content" id="providerTabsContent">
            <?php foreach($provider_types as $type => $label): ?>
                <div class="tab-pane fade <?php echo $type === 'obstetrician_gynaecologist' ? 'show active' : ''; ?>" 
                     id="<?php echo $type; ?>" role="tabpanel">
                     
                    <?php if(empty($providers[$type])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> No <?php echo strtolower($label); ?>s available at this time.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($providers[$type] as $provider): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="provider-card">
                                        <h5><i class="fas fa-user-md mr-2"></i>Dr. <?php echo htmlspecialchars($provider['username']); ?></h5>
                                        <!-- <p class="text-muted mb-2"><i class="fas fa-star mr-1"></i> <?php echo htmlspecialchars($provider['spec']); ?></p> -->
                                        
                                        <button class="btn btn-book btn-sm book-btn" 
                                                data-toggle="modal" 
                                                data-target="#bookModal"
                                                data-provider-id="<?php echo $provider['username']; ?>"
                                                data-provider-name="Dr. <?php echo htmlspecialchars($provider['username']); ?>"
                                                data-provider-type="<?php echo $type; ?>">
                                            <i class="fas fa-calendar-check mr-1"></i> Book Appointment
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Book Appointment with <span id="providerName"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="post" action="appointments.php">
                    <div class="modal-body">
                        <input type="hidden" name="provider_type" id="providerType">
                        <input type="hidden" name="provider_id" id="providerId">
                        
                        <div class="form-group">
                            <label><i class="fas fa-calendar-day mr-1"></i> Appointment Date</label>
                            <input type="date" class="form-control" name="appdate" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-clock mr-1"></i> Appointment Time</label>
                            <input type="time" class="form-control" name="apptime" 
                                   min="08:00" max="17:00" required>
                            <small class="form-text text-muted">Clinic hours: 8:00 AM - 5:00 PM</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-comment-medical mr-1"></i> Reason for Appointment</label>
                            <textarea class="form-control" name="reason" rows="3" 
                                      placeholder="Please describe your reason for the appointment..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Close</button>
                        <button type="submit" name="book-appointment" class="btn btn-primary"><i class="fas fa-calendar-plus mr-1"></i> Book Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize provider selection
        $('.book-btn').click(function() {
            $('#providerId').val($(this).data('provider-id'));
            $('#providerName').text($(this).data('provider-name'));
            $('#providerType').val($(this).data('provider-type'));
        });
        
        // Disable weekends and past dates
        $('input[name="appdate"]').on('change', function() {
            var selectedDate = new Date(this.value);
            var today = new Date();
            today.setHours(0,0,0,0);
            
            // Check if weekend
            if([0, 6].includes(selectedDate.getUTCDay())) {
                alert('Weekends are not available for appointments');
                this.value = '';
                return;
            }
            
            // Check if past date
            if(selectedDate < today) {
                alert('Appointment date cannot be in the past');
                this.value = '';
            }
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
    </script>
</body>
</html>