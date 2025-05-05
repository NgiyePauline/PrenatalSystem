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

// Get prenatal data if exists
$prenatalQuery = "SELECT * FROM prenatal_reg WHERE national_id='$national_id'";
$prenatalResult = mysqli_query($con, $prenatalQuery);
$prenatalData = mysqli_fetch_assoc($prenatalResult);

// Get current pregnancy week if registered
$pregnancy_week = 0;
if($prenatalData) {
    $lmp = new DateTime($prenatalData['lmp']);
    $today = new DateTime();
    $diff = $today->diff($lmp);
    $pregnancy_week = floor($diff->days / 7);
}

// Get health education content
$educationQuery = "SELECT * FROM health_education 
                  ORDER BY CASE WHEN week = $pregnancy_week THEN 0 ELSE 1 END, week";
$educationResult = mysqli_query($con, $educationQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Health Education | Prenatal Care</title>
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
        
        .education-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .current-week {
            border-left: 4px solid #28a745;
            box-shadow: 0 5px 15px rgba(40,167,69,0.2);
        }
        
        .education-category {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .category-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fa fa-book-medical mr-2"></i>Health Education
                        </h4>
                        
                        <?php if($prenatalData): ?>
                            <div class="alert alert-primary">
                                <h5>
                                    <i class="fa fa-info-circle mr-2"></i>
                                    Week <?php echo $pregnancy_week; ?> Education
                                </h5>
                                <p>Here's what you should know about your current pregnancy week.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <!-- Nutrition -->
                            <div class="col-md-4">
                                <div class="education-category text-center">
                                    <div class="category-icon">
                                        <i class="fa fa-utensils"></i>
                                    </div>
                                    <h4>Nutrition</h4>
                                    <p>Essential dietary guidelines for a healthy pregnancy</p>
                                    <button class="btn btn-outline-primary" data-toggle="collapse" data-target="#nutritionContent">
                                        View Content
                                    </button>
                                </div>
                                <div id="nutritionContent" class="collapse">
                                    <div class="card education-card">
                                        <div class="card-body">
                                            <h5>Nutrition Guidelines</h5>
                                            <ul>
                                                <li>Increase folic acid intake (400-800 mcg daily)</li>
                                                <li>Consume iron-rich foods (lean meats, beans, spinach)</li>
                                                <li>Include calcium sources (dairy, fortified plant milks)</li>
                                                <li>Stay hydrated (8-10 glasses of water daily)</li>
                                                <li>Limit caffeine to 200mg per day</li>
                                                <li>Avoid raw fish, undercooked meats, and unpasteurized dairy</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Exercise -->
                            <div class="col-md-4">
                                <div class="education-category text-center">
                                    <div class="category-icon">
                                        <i class="fa fa-running"></i>
                                    </div>
                                    <h4>Exercise</h4>
                                    <p>Safe physical activities during pregnancy</p>
                                    <button class="btn btn-outline-primary" data-toggle="collapse" data-target="#exerciseContent">
                                        View Content
                                    </button>
                                </div>
                                <div id="exerciseContent" class="collapse">
                                    <div class="card education-card">
                                        <div class="card-body">
                                            <h5>Exercise Recommendations</h5>
                                            <ul>
                                                <li>30 minutes of moderate exercise most days</li>
                                                <li>Walking and swimming are excellent choices</li>
                                                <li>Practice pelvic floor exercises (Kegels)</li>
                                                <li>Modify intensity as pregnancy progresses</li>
                                                <li>Avoid high-impact sports and activities with fall risk</li>
                                                <li>Stop if you feel dizzy, short of breath, or have pain</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Warning Signs -->
                            <div class="col-md-4">
                                <div class="education-category text-center">
                                    <div class="category-icon">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </div>
                                    <h4>Warning Signs</h4>
                                    <p>When to contact your healthcare provider</p>
                                    <button class="btn btn-outline-primary" data-toggle="collapse" data-target="#warningContent">
                                        View Content
                                    </button>
                                </div>
                                <div id="warningContent" class="collapse">
                                    <div class="card education-card">
                                        <div class="card-body">
                                            <h5>Warning Signs to Watch For</h5>
                                            <ul>
                                                <li>Severe headaches or vision changes</li>
                                                <li>Severe abdominal pain or cramping</li>
                                                <li>Vaginal bleeding or fluid leakage</li>
                                                <li>Decreased fetal movement after 28 weeks</li>
                                                <li>Persistent nausea/vomiting</li>
                                                <li>Signs of preterm labor (regular contractions before 37 weeks)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Weekly Education Content -->
                        <h4 class="mt-5 mb-3">
                            <i class="fa fa-calendar-week mr-2"></i>Weekly Pregnancy Education
                        </h4>
                        
                        <?php if(mysqli_num_rows($educationResult) > 0): ?>
                            <div class="row">
                                <?php while($education = mysqli_fetch_assoc($educationResult)): 
                                    $isCurrentWeek = $education['week'] == $pregnancy_week;
                                ?>
                                    <div class="col-md-6">
                                        <div class="card education-card <?php echo $isCurrentWeek ? 'current-week' : ''; ?>">
                                            <div class="card-body">
                                                <h5>Week <?php echo $education['week']; ?>: <?php echo $education['title']; ?></h5>
                                                <p><?php echo $education['content']; ?></p>
                                                <?php if($isCurrentWeek): ?>
                                                    <span class="badge badge-success">Your Current Week</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa fa-book-open fa-3x text-muted mb-3"></i>
                                <h5>No Education Content Available</h5>
                                <p>Health education materials will be added soon.</p>
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