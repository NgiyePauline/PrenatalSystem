<!DOCTYPE html>
<?php 

$con = mysqli_connect("localhost","root","","myhmsdb");
include('newfunc.php');

// Get registered patients
$patients = [];
$patients_query = "SELECT * FROM patreg";
$patients_result = mysqli_query($con, $patients_query);
if($patients_result) {
    while($row = mysqli_fetch_assoc($patients_result)) {
        $patients[] = $row;
    }
}

// Get appointments with patient and doctor details
$appointments = [];
$appointments_query = "SELECT a.*, p.fname as patient_fname, p.lname as patient_lname, 
                      d.username as doctor_name, d.spec as doctor_spec 
                      FROM appointmenttb a
                      LEFT JOIN patreg p ON a.email = p.email
                      LEFT JOIN doctb d ON a.email = d.email"; 
$appointments_result = mysqli_query($con, $appointments_query);

if(!$appointments_result) {
    echo "<script>console.error('Appointments query error: " . mysqli_error($con) . "');</script>";
} else {
    while($row = mysqli_fetch_assoc($appointments_result)) {
        $appointments[] = $row;
    }
}


// Get patient messages
$messages = [];
$messages_query = "SELECT m.*, p.fname, p.lname, p.email as patient_email, p.contact 
                  FROM contact m
                  LEFT JOIN patreg p ON m.email = p.email
                  ORDER BY p.fname ASC, p.lname ASC";  // Changed to sort by name
$messages_result = mysqli_query($con, $messages_query);
if($messages_result) {
    while($row = mysqli_fetch_assoc($messages_result)) {
        $messages[] = $row;
    }
} else {
    echo "<script>console.error('Messages query error: " . mysqli_error($con) . "');</script>";
}
// Get inventory items
$inventory = [];
$inventory_query = "SELECT * FROM inventory ORDER BY mname ASC";
$inventory_result = mysqli_query($con, $inventory_query);
if($inventory_result) {
    while($row = mysqli_fetch_assoc($inventory_result)) {
        $inventory[] = $row;
    }
}

// Handle form submissions
if(isset($_POST['docsub'])) {
    $doctor = $_POST['doctor'];
    $dpassword = $_POST['dpassword'];
    $demail = $_POST['demail'];
    $spec = $_POST['special'];
    $phone = $_POST['phone'];
    $docFees = $_POST['docFees'];
    
    $query = "INSERT INTO doctb(username,password,email,spec,docFees,phoneno) VALUES('$doctor','$dpassword','$demail','$spec','$docFees','$phone')";
    $result = mysqli_query($con,$query);
}

if(isset($_POST['addmedic'])) {
    $medicname = $_POST['medicname'];
    $special = $_POST['special'];
    $quantity = $_POST['quantity'];  
    $mdate = $_POST['mdate'];
    $edate = $_POST['edate'];
    $desstore = $_POST['desstore'];
    
    $query = "INSERT INTO inventory(mname,spec,quantity,mdate,edate,des) VALUES('$medicname','$special','$quantity','$mdate','$edate','$desstore')";
    $result = mysqli_query($con,$query);
    if($result) {
        // echo "<script>alert('Medicine added successfully!');</script>";
        // Refresh the page to show the new item
        echo "<script>window.location.href='admin-panel1.php#inventory';</script>";
    } else {
        echo "<script>alert('Failed to add the medicine!');</script>";
    }
}

if(isset($_POST['docsub1'])) {
    $demail = $_POST['demail'];
    $query = "DELETE FROM doctb WHERE email='$demail'";
    $result = mysqli_query($con,$query);
    if(!$result) {
        echo "<script>alert('Unable to delete!');</script>";
    }
}

if(isset($_POST['delete_medicine'])) {
    $medName = $_POST['med_name']; // Changed from med_id to med_name
    $query = "DELETE FROM inventory WHERE mname='$medName'"; // Changed to use mname instead of id
    $result = mysqli_query($con,$query);
    if($result) {
        // echo "<script>alert('Medicine deleted successfully!');</script>";
        // Refresh the page to update the inventory list
        echo "<script>window.location.href='admin-panel1.php#inventory';</script>";
    } else {
        echo "<script>alert('Failed to delete medicine!');</script>";
    }
}
?>

<html lang="en">
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
    <!-- Modern Navbar -->
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
                        <i class="fas fa-user-md mr-1"></i>Doctors
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
            <div class="col-md-12">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <h3 class="mb-4"><i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard</h3>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card dashboard-card text-center p-4">
                                    <div class="card-body">
                                        <i class="fas fa-user-md card-icon"></i>
                                        <h5>Doctors</h5>
                                        <p class="text-muted">Manage medical staff</p>
                                        <a href="#doctors" class="btn btn-primary btn-sm" data-toggle="tab">View Doctors</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card dashboard-card text-center p-4">
                                    <div class="card-body">
                                        <i class="fas fa-procedures card-icon"></i>
                                        <h5>Patients</h5>
                                        <p class="text-muted">View patient records</p>
                                        <a href="#patients" class="btn btn-primary btn-sm" data-toggle="tab">View Patients</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card dashboard-card text-center p-4">
                                    <div class="card-body">
                                        <i class="fas fa-calendar-check card-icon"></i>
                                        <h5>Appointments</h5>
                                        <p class="text-muted">Manage bookings</p>
                                        <a href="#appointments" class="btn btn-primary btn-sm" data-toggle="tab">View Appointments</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card dashboard-card text-center p-4">
                                    <div class="card-body">
                                        <i class="fas fa-pills card-icon"></i>
                                        <h5>Inventory</h5>
                                        <p class="text-muted">Medicine stock</p>
                                        <a href="#inventory" class="btn btn-primary btn-sm" data-toggle="tab">View Inventory</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card dashboard-card text-center p-4">
                                    <div class="card-body">
                                        <i class="fas fa-envelope card-icon"></i>
                                        <h5>Messages</h5>
                                        <p class="text-muted">Patient queries</p>
                                        <a href="#messages" class="btn btn-primary btn-sm" data-toggle="tab">View Messages</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Doctors Tab -->
                    <div class="tab-pane fade" id="doctors">
                        <h3 class="mb-4"><i class="fas fa-user-md mr-2"></i>Doctors</h3>
                        
                        <form class="form-group mb-4" action="doctorsearch.php" method="post">
                            <div class="row">
                                <div class="col-md-10">
                                    <input type="text" name="doctor_contact" placeholder="Search by email..." class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="doctor_search_submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search mr-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Doctor Name</th>
                                        <th>Specialization</th>
                                        <th>Email</th>
                                        <th>Fees</th>
                                        <th>Phone</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $query = "SELECT * FROM doctb";
                                        $result = mysqli_query($con,$query);
                                        while ($row = mysqli_fetch_array($result)){
                                            echo "<tr>
                                                <td>{$row['username']}</td>
                                                <td>{$row['spec']}</td>
                                                <td>{$row['email']}</td>
                                                <td>{$row['docFees']}</td>
                                                <td>{$row['phoneno']}</td>
                                                <td>
                                                    <form method='post' action='admin-panel1.php' style='display:inline;'>
                                                        <input type='hidden' name='demail' value='{$row['email']}'>
                                                        <button type='submit' name='docsub1' class='btn btn-danger btn-sm' 
                                                                onclick='return confirm(\"Are you sure you want to delete this doctor?\")'>
                                                            <i class='fas fa-trash-alt'></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Patients Tab -->
                    <div class="tab-pane fade" id="patients">
                        <h3 class="mb-4"><i class="fas fa-procedures mr-2"></i>Patients</h3>
                        
                        <div class="form-group mb-4">
                            <input type="text" id="patientSearch" class="form-control" placeholder="Search patients...">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>Medical History</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($patients as $patient): ?>
                                    <tr>
                                        <td><?php echo $patient['pid'] ?? ''; ?></td>
                                        <td><?php echo htmlspecialchars($patient['fname'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($patient['lname'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($patient['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($patient['contact'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($patient['gender'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($patient['age'] ?? ''); ?></td>
                                        <td class="medical-history" title="<?php echo htmlspecialchars($patient['medicalhis'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($patient['medicalhis'] ?? ''); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Appointments Tab -->
                    <div class="tab-pane fade" id="appointments">
                        <h3 class="mb-4"><i class="fas fa-calendar-check mr-2"></i>Appointments</h3>
                        
                        <div class="form-group mb-4">
                            <input type="text" id="appointmentSearch" class="form-control" placeholder="Search appointments...">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Doctor</th>
                                        <th>Specialization</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($appointments as $appointment): 
                                        // Status styling
                                        $status_class = '';
                                        if($appointment['userStatus'] == 1) {
                                            $status_class = 'appointment-status-approved';
                                            $status_text = 'Approved';
                                        } elseif($appointment['userStatus'] == 0) {
                                            $status_class = 'appointment-status-pending';
                                            $status_text = 'Pending';
                                        } else {
                                            $status_class = 'appointment-status-rejected';
                                            $status_text = 'Rejected';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $appointment['id'] ?? ''; ?></td>
                                        <td><?php echo htmlspecialchars(($appointment['patient_fname'] ?? '') . ' ' . ($appointment['patient_lname'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['doctor_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['doctor_spec'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appdate'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['apptime'] ?? ''); ?></td>
                                        <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Inventory Tab -->
                    <div class="tab-pane fade" id="inventory">
                        <h3 class="mb-4"><i class="fas fa-pills mr-2"></i>Medicine Inventory</h3>
                        
                        <div class="form-group mb-4">
                            <input type="text" id="inventorySearch" class="form-control" placeholder="Search medicines...">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Medicine Name</th>
                                        <th>Specialization</th>
                                        <th>Quantity</th>
                                        <th>Manufacture Date</th>
                                        <th>Expiry Date</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($inventory as $item): 
                                        // Determine stock level class
                                        $quantity = $item['quantity'] ?? 0;
                                        $stock_class = '';
                                        if ($quantity < 10) {
                                            $stock_class = 'low-stock';
                                        } elseif ($quantity < 30) {
                                            $stock_class = 'medium-stock';
                                        } else {
                                            $stock_class = 'good-stock';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $item['id'] ?? ''; ?></td>
                                        <td><?php echo htmlspecialchars($item['mname'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['spec'] ?? ''); ?></td>
                                        <td class="<?php echo $stock_class; ?>"><?php echo htmlspecialchars($quantity); ?></td>
                                        <td><?php echo htmlspecialchars($item['mdate'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['edate'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['des'] ?? ''); ?></td>
                                        <td>
                                            <form method="post" action="admin-panel1.php" style="display:inline;">
                                            <input type="hidden" name="med_name" value="<?php echo htmlspecialchars($item['mname']); ?>">
                                            <button type="submit" name="delete_medicine" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($item['mname']); ?>?')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                            </form>
                                        </td>
    
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Add Medicine Tab -->
                    <div class="tab-pane fade" id="add-medicine">
                        <h3 class="mb-4"><i class="fas fa-plus-circle mr-2"></i>Add New Medicine</h3>
                        
                        <form class="form-group" method="post" action="admin-panel1.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Medicine Name:</label>
                                    <input type="text" class="form-control" name="medicname" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Specialization:</label>
                                    <select name="special" class="form-control" required>
                                        <option value="" disabled selected>Select Specialization</option>
                                        <option value="General Maternity Services">General Maternity Services</option>
                                        <option value="Clinic Services">Clinic Services</option>
                                        <option value="Check Up">Check Up</option>
                                        <option value="Post Maternal">Post Maternal</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Quantity:</label>
                                    <input type="number" class="form-control" name="quantity" min="1" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Manufacture Date:</label>
                                    <input type="date" class="form-control" name="mdate" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Expiry Date:</label>
                                    <input type="date" class="form-control" name="edate" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Description/Storage:</label>
                                    <input type="text" class="form-control" name="desstore" required>
                                </div>
                                
                                <div class="col-md-12 mt-3">
                                    <button type="submit" name="addmedic" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Add Medicine
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Messages Tab -->
                    <div class="tab-pane fade" id="messages">
                        <h3 class="mb-4"><i class="fas fa-envelope mr-2"></i>Patient Messages</h3>
                        
                        <div class="form-group mb-4">
                            <input type="text" id="messageSearch" class="form-control" placeholder="Search messages...">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Message</th>
                                        <th>Date Sent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($messages as $message): ?>
                                    <tr>
                                        <td><?php echo $message['id'] ?? ''; ?></td>
                                        <td><?php echo htmlspecialchars(($message['fname'] ?? '') . ' ' . ($message['lname'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($message['patient_email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($message['contact'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($message['message'] ?? ''); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($message['created_at'] ?? '')); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Add Doctor Tab -->
                    <div class="tab-pane fade" id="add-doctor">
                        <h3 class="mb-4"><i class="fas fa-user-plus mr-2"></i>Add New Doctor</h3>
                        
                        <form class="form-group" method="post" action="admin-panel1.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Doctor Name:</label>
                                    <input type="text" class="form-control" name="doctor" onkeydown="return alphaOnly(event);" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Specialization:</label>
                                    <select name="special" class="form-control" required>
                                        <option value="" disabled selected>Select Specialization</option>
                                        <option value="General Maternity Services">General Maternity Services</option>
                                        <option value="Clinic Services">Clinic Services</option>
                                        <option value="Check Up">Check Up</option>
                                        <option value="Post Maternal">Post Maternal</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Email ID:</label>
                                    <input type="email" class="form-control" name="demail" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Password:</label>
                                    <input type="password" class="form-control" name="dpassword" id="dpassword" onkeyup='check();' required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Confirm Password:</label>
                                    <input type="password" class="form-control" name="cdpassword" id="cdpassword" onkeyup='check();' required>
                                    <small id='message' class="form-text"></small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Consultancy Fees:</label>
                                    <input type="text" class="form-control" name="docFees" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label>Phone Number:</label>
                                    <input type="text" class="form-control" name="phone" required>
                                </div>
                                
                                <div class="col-md-12 mt-3">
                                    <button type="submit" name="docsub" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Add Doctor
                                    </button>
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