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

// Get patient data using national_id
$national_id = $_SESSION['national_id'];
$query = "SELECT * FROM patreg WHERE national_id='$national_id'";
$result = mysqli_query($con, $query);
$patient = mysqli_fetch_assoc($result);

// Handle prenatal registration form submission
if(isset($_POST['prenatal-submit'])) {
    // Validate all required fields
    if(empty($_POST['lmp']) || empty($_POST['gravida']) || empty($_POST['parity']) || 
       empty($_POST['blood_group']) || empty($_POST['rh_factor'])) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: prenatal-register.php");
        exit();
    }

    $lmp = mysqli_real_escape_string($con, $_POST['lmp']);
    
    // Calculate EDC from LMP (40 weeks later)
    try {
        $lmpDate = new DateTime($lmp);
        $edcDate = clone $lmpDate;
        $edcDate->add(new DateInterval('P280D')); // 280 days = 40 weeks
        $edc = $edcDate->format('Y-m-d');
    } catch (Exception $e) {
        $_SESSION['error'] = "Invalid date format for Last Menstrual Period";
        header("Location: prenatal-register.php");
        exit();
    }
    
    $gravida = (int)$_POST['gravida'];
    $parity = (int)$_POST['parity'];
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $rh_factor = mysqli_real_escape_string($con, $_POST['rh_factor']);
    
    // Calculate pregnancy week
    $today = new DateTime();
    $diff = $today->diff($lmpDate);
    $pregnancy_week = floor($diff->days / 7);
    
    // Validate pregnancy week is reasonable (0-42 weeks)
    if($pregnancy_week < 0 || $pregnancy_week > 42) {
        $_SESSION['error'] = "Invalid pregnancy duration. Please check your Last Menstrual Period.";
        header("Location: prenatal-register.php");
        exit();
    }
    
    $query = "INSERT INTO prenatal_reg (national_id, lmp, edc, gravida, parity, blood_group, rh_factor, pregnancy_week) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sssiissi", $national_id, $lmp, $edc, $gravida, $parity, $blood_group, $rh_factor, $pregnancy_week);
    

    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['pregnancy_week'] = $pregnancy_week;
        $_SESSION['success'] = "Prenatal registration successful!";
        header("Location: admin-panel.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($con);
        header("Location: prenatal-register.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Prenatal Registration</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    
    <!-- CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'IBM Plex Sans', sans-serif;
            padding-top: 60px;
            background-color: #f8f9fa;
        }
        .registration-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #342ac1;
            border-color: #342ac1;
        }
        #edc {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="admin-panel.php">
                <i class="fa fa-arrow-left mr-2"></i>Prenatal Care Portal
            </a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Alerts -->
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>
                
                <div class="card registration-card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">
                            <i class="fa fa-baby mr-2"></i> Prenatal Care Registration
                        </h3>
                        
                        <form method="post" action="prenatal-register.php" id="prenatalForm">
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
                                    <button type="submit" name="prenatal-submit" class="btn btn-primary btn-block btn-lg">
                                        <i class="fa fa-check-circle mr-2"></i>Complete Registration
                                    </button>
                                </div>
                            </div>
                        </form>
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
        // Calculate EDC when LMP is entered (40 weeks later)
        $('#lmp').change(function() {
            if(this.value) {
                try {
                    var lmpDate = new Date(this.value);
                    if(isNaN(lmpDate.getTime())) {
                        alert('Please enter a valid date for Last Menstrual Period');
                        return;
                    }
                    
                    var edcDate = new Date(lmpDate);
                    edcDate.setDate(edcDate.getDate() + 280); // 40 weeks
                    
                    // Format as YYYY-MM-DD
                    var edcFormatted = edcDate.toISOString().split('T')[0];
                    $('#edc').val(edcFormatted);
                } catch(e) {
                    console.error("Date calculation error:", e);
                    $('#edc').val('');
                }
            } else {
                $('#edc').val('');
            }
        });

        // Prevent form submission if EDC isn't set
        $('#prenatalForm').on('submit', function(e) {
            if(!$('#edc').val()) {
                e.preventDefault();
                alert('Please enter a valid Last Menstrual Period to calculate the Estimated Due Date.');
                $('#lmp').focus();
            }
        });
    });
    </script>
</body>
</html>