<!DOCTYPE html>
<?php

include('func1.php');

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

// Check connection
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

// Initialize variables
$pid = $ID = $appdate = $apptime = $fname = $lname = '';
$doctor = $_SESSION['dname'];

// Get patient data if parameters are provided
if (isset($_GET['pid']) && isset($_GET['ID']) && isset($_GET['appdate']) && isset($_GET['apptime']) && isset($_GET['fname']) && isset($_GET['lname'])) {
    $pid = mysqli_real_escape_string($con, $_GET['pid']);
    $ID = mysqli_real_escape_string($con, $_GET['ID']);
    $fname = mysqli_real_escape_string($con, $_GET['fname']);
    $lname = mysqli_real_escape_string($con, $_GET['lname']);
    $appdate = mysqli_real_escape_string($con, $_GET['appdate']);
    $apptime = mysqli_real_escape_string($con, $_GET['apptime']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prescribe'])) {
    // Validate and sanitize inputs
    $appdate = mysqli_real_escape_string($con, $_POST['appdate']);
    $apptime = mysqli_real_escape_string($con, $_POST['apptime']);
    $disease = mysqli_real_escape_string($con, $_POST['disease']);
    $allergy = mysqli_real_escape_string($con, $_POST['allergy']);
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $pid = mysqli_real_escape_string($con, $_POST['pid']);
    $ID = mysqli_real_escape_string($con, $_POST['ID']);
    $prescription = mysqli_real_escape_string($con, $_POST['prescription']);
    
    // Insert prescription
    $query = "INSERT INTO prestb (doctor, pid, fname, lname, appdate, apptime, disease, allergy, prescription) 
              VALUES ('$doctor', '$pid',  '$fname', '$lname', '$appdate', '$apptime', '$disease', '$allergy', '$prescription')";
    
    if (mysqli_query($con, $query)) {
        $_SESSION['success'] = 'Prescription added successfully!';
        header("Location: doctor-panel.php#list-pres");
        exit();
    } else {
        $error = 'Unable to process your request. Try again! Error: ' . mysqli_error($con);
    }
}
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    
    <style>
        .bg-primary {
            background: -webkit-linear-gradient(left, #3931af, #00c6ff);
        }
        .text-primary {
            color: #342ac1!important;
        }
        .btn-primary {
            background-color: #3c50c1;
            border-color: #3c50c1;
        }
        button:hover, #inputbtn:hover {
            cursor: pointer;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
    
    <title>Prescribe Medication</title>
</head>
<body style="padding-top:50px;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <a class="navbar-brand" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> PRENATAL HOSPITAL SYSTEM</a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout1.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="doctor-panel.php"><i class="fa fa-arrow-left" aria-hidden="true"></i>Dashboard</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top:50px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <h3 class="text-center mb-4">Prescribe Medication</h3>
                    <p class="text-center">Patient: <strong><?php echo htmlspecialchars($fname . ' ' . $lname); ?></strong> | Appointment ID: <strong><?php echo htmlspecialchars($ID); ?></strong></p>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="prescribe.php">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Disease/Diagnosis:</label>
                            <div class="col-md-9">
                                <textarea id="disease" name="disease" rows="2" required></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Allergies:</label>
                            <div class="col-md-9">
                                <textarea id="allergy" name="allergy" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Prescription:</label>
                            <div class="col-md-9">
                                <textarea id="prescription" name="prescription" rows="4" required></textarea>
                            </div>
                        </div>
                        
                        <input type="hidden" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
                        <input type="hidden" name="lname" value="<?php echo htmlspecialchars($lname); ?>">
                        <input type="hidden" name="appdate" value="<?php echo htmlspecialchars($appdate); ?>">
                        <input type="hidden" name="apptime" value="<?php echo htmlspecialchars($apptime); ?>">
                        <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                        <input type="hidden" name="ID" value="<?php echo htmlspecialchars($ID); ?>">
                        
                        <div class="form-group row">
                            <div class="col-md-12 text-center">
                                <input type="submit" name="prescribe" value="Save Prescription" class="btn btn-primary btn-lg">
                            </div>
                        </div>
                    </form>
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
<?php
mysqli_close($con);
?>