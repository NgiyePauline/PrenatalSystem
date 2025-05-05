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

// Get all reminders for this patient
$reminderQuery = "SELECT * FROM reminders 
                  WHERE pid = '$national_id'
                  ORDER BY reminder_date DESC, reminder_time DESC";
$reminderResult = mysqli_query($con, $reminderQuery);

// Handle reminder dismissal
if(isset($_POST['dismiss-reminder'])) {
    $reminderId = mysqli_real_escape_string($con, $_POST['reminder_id']);
    
    $updateQuery = "UPDATE reminders SET status = 'dismissed' WHERE id = '$reminderId' AND pid = '$national_id'";
    if(mysqli_query($con, $updateQuery)) {
        $_SESSION['success'] = "Reminder dismissed!";
        header("Refresh:0");
        exit();
    } else {
        $_SESSION['error'] = "Error dismissing reminder: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Reminders | Prenatal Care</title>
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
        
        .reminder-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 15px;
            transition: all 0.2s ease;
        }
        
        .reminder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .reminder-important {
            border-left: 4px solid #dc3545;
        }
        
        .reminder-completed {
            opacity: 0.7;
            border-left: 4px solid #6c757d;
        }
        
        .btn-dismiss {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-dismiss:hover {
            background-color: #5a6268;
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
                            <i class="fa fa-bell mr-2"></i>Your Reminders
                        </h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-primary filter-btn" data-filter="pending">Pending</button>
                                    <button type="button" class="btn btn-outline-primary filter-btn" data-filter="completed">Completed</button>
                                </div>
                            </div>
                        </div>
                        
                        <?php if(mysqli_num_rows($reminderResult) > 0): ?>
                            <div class="reminders-list">
                                <?php while($reminder = mysqli_fetch_assoc($reminderResult)): 
                                    $isCompleted = $reminder['status'] == 'completed' || $reminder['status'] == 'dismissed';
                                    $isImportant = strpos(strtolower($reminder['message']), 'important') !== false;
                                ?>
                                    <div class="card reminder-card <?php echo $isImportant ? 'reminder-important' : ''; ?> <?php echo $isCompleted ? 'reminder-completed' : ''; ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5><?php echo $reminder['message']; ?></h5>
                                                    <p class="mb-1">
                                                        <i class="fa fa-calendar-day mr-2"></i>
                                                        <?php echo date('M j, Y', strtotime($reminder['reminder_date'])); ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="fa fa-clock mr-2"></i>
                                                        <?php echo date('g:i A', strtotime($reminder['reminder_time'])); ?>
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <?php if(!$isCompleted): ?>
                                                        <form method="post" action="">
                                                            <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                                            <button type="submit" name="dismiss-reminder" class="btn btn-sm btn-dismiss">
                                                                <i class="fa fa-check mr-1"></i>Dismiss
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Completed</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h5>No Reminders Found</h5>
                                <p>You don't have any reminders scheduled yet.</p>
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
    
    <script>
    $(document).ready(function() {
        // Filter reminders
        $('.filter-btn').click(function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            var filter = $(this).data('filter');
            
            if(filter === 'all') {
                $('.reminder-card').show();
            } else if(filter === 'pending') {
                $('.reminder-card').hide();
                $('.reminder-card:not(.reminder-completed)').show();
            } else if(filter === 'completed') {
                $('.reminder-card').hide();
                $('.reminder-card.reminder-completed').show();
            }
        });
    });
    </script>
</body>
</html>