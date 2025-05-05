<?php
session_start();


$con = mysqli_connect("localhost","root","","myhmsdb");

// Get patient data
$national_id = $_GET['national_id'] ?? 0;
$query = "SELECT * FROM patreg WHERE national_id='$national_id'";
$result = mysqli_query($con, $query);
$patient = mysqli_fetch_assoc($result);

// Handle form submission
if(isset($_POST['update_patient'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $medicalhis = $_POST['medicalhis'];
    
    $update_query = "UPDATE patreg SET 
                    fname='$fname', 
                    lname='$lname', 
                    email='$email', 
                    contact='$contact', 
                    gender='$gender', 
                    age='$age', 
                    medicalhis='$medicalhis' 
                    WHERE national_id='$national_id'";
    
    if(mysqli_query($con, $update_query)) {
        echo "<script>alert('Patient updated successfully!'); window.location.href='admin-panel1.php#patients';</script>";
    } else {
        echo "<script>alert('Error updating patient: " . mysqli_error($con) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Prenatal System</title>
    
    <style>
        :root {
            --primary-color: #3f51b5;
            --secondary-color: #00c6ff;
            --accent-color: #ff6b6b;
        }
        
        body {
            font-family: 'IBM Plex Sans', sans-serif;
            padding-top: 70px;
            background-color: #f8f9fa;
        }
        
        .navbar {
            padding: 0.6rem 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 1.2rem;
        }
        
        .nav-link {
            padding: 0.5rem 1rem;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .nav-link:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.5rem 1.5rem;
        }
        
        .dropdown-item:hover {
            background-color: #f1f8ff;
        }
        
        .nav-item.active .nav-link {
            font-weight: bold;
            position: relative;
        }
        
        .nav-item.active .nav-link:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1rem;
            right: 1rem;
            height: 2px;
            background: white;
        }
        
        .tab-content {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 2rem;
        }
        
        .dashboard-card {
            border: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .form-control {
            border-radius: 0.5rem;
            padding: 0.8rem 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
            padding: 0.6rem 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .medical-history {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .appointment-status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .appointment-status-approved {
            color: #28a745;
            font-weight: bold;
        }
        
        .appointment-status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
        
        .low-stock {
            color: #dc3545;
            font-weight: bold;
        }
        
        .medium-stock {
            color: #ffc107;
            font-weight: bold;
        }
        
        .good-stock {
            color: #28a745;
            font-weight: bold;
        }
        
        @media (max-width: 992px) {
            .navbar-collapse {
                padding: 1rem;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            }
        }
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hospital mr-2"></i>PRENATAL SYSTEM</a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin-panel1.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="admin-panel1.php">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="doctorsDropdown" data-toggle="dropdown">
                        <i class="fas fa-user-md mr-1"></i>Providers
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#doctors" data-toggle="tab">View All</a>
                        <a class="dropdown-item" href="#add-doctor" data-toggle="tab">Add New</a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="#patients" data-toggle="tab">
                        <i class="fas fa-procedures mr-1"></i>Patients
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="#appointments" data-toggle="tab">
                        <i class="fas fa-calendar-check mr-1"></i>Appointments
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" data-toggle="dropdown">
                        <i class="fas fa-pills mr-1"></i>Inventory
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#inventory" data-toggle="tab">All Medicines</a>
                        <a class="dropdown-item" href="#add-medicine" data-toggle="tab">Add Medicine</a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="#messages" data-toggle="tab">
                        <i class="fas fa-envelope mr-1"></i>Messages
                    </a>
                </li>

                <li class="nav-item">
    <a class="nav-link" href="#system-management" data-toggle="tab">
        <i class="fas fa-cogs mr-1"></i>System Management
    </a>
</li>

            </ul>
            
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="nav-text text-light mr-3 d-flex align-items-center">
                        <i class="fas fa-user-shield mr-2"></i> Admin
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-outline-light" href="logout1.php">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-user-edit"></i> Edit Patient</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="edit-patient.php?national_id=<?php echo $national_id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>First Name:</label>
                                    <input type="text" class="form-control" name="fname" value="<?php echo htmlspecialchars($patient['fname'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Last Name:</label>
                                    <input type="text" class="form-control" name="lname" value="<?php echo htmlspecialchars($patient['lname'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Email:</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Contact:</label>
                                    <input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($patient['contact'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Gender:</label>
                                    <select class="form-control" name="gender" required>
                                        <option value="Male" <?php echo ($patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($patient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Age:</label>
                                    <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($patient['age'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label>Medical History:</label>
                                    <textarea class="form-control" name="medicalhis" rows="3"><?php echo htmlspecialchars($patient['medicalhis'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-12 mt-3">
                                    <button type="submit" name="update_patient" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Patient
                                    </button>
                                    <a href="admin-panel1.php#patients" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <script>
        // Password match checker
        function check() {
            if (document.getElementById('dpassword').value == document.getElementById('cdpassword').value) {
                document.getElementById('message').style.color = '#28a745';
                document.getElementById('message').innerHTML = 'Passwords match';
            } else {
                document.getElementById('message').style.color = '#dc3545';
                document.getElementById('message').innerHTML = 'Passwords do not match';
            }
        }
        
        // Alpha only validation
        function alphaOnly(event) {
            var key = event.keyCode;
            return ((key >= 65 && key <= 90) || key == 8 || key == 32);
        };
        
        // Activate current tab in navbar
        $(document).ready(function(){
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                $('.nav-item').removeClass('active');
                $(this).closest('.nav-item').addClass('active');
            });
            
            // Patient search functionality
            $("#patientSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#patients tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Appointment search functionality
            $("#appointmentSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#appointments tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Message search functionality
            $("#messageSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#messages tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Inventory search functionality
            $("#inventorySearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#inventory tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>