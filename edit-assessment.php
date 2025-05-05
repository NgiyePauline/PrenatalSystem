<?php
session_start();
require_once 'db_connection.php';

if(!isset($_SESSION['national_id'])) {
    header("Location: index1.php");
    exit();
}

$assessment_id = $_GET['id'] ?? 0;
$pid = $_SESSION['national_id'];

// Fetch the assessment to edit
$query = "SELECT * FROM patient_assessments WHERE id=? AND pid=?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "is", $assessment_id, $pid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assessment = mysqli_fetch_assoc($result);

if(!$assessment) {
    $_SESSION['error'] = "Assessment not found";
    header("Location: patient-dashboard.php#list-assessments");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Calculate BMI if weight and height are provided
    $bmi = null;
    if(!empty($_POST['weight']) && !empty($_POST['height'])) {
        $height_m = $_POST['height'] / 100; // convert cm to m
        $bmi = $_POST['weight'] / ($height_m * $height_m);
    }
    
    $updateQuery = "UPDATE patient_assessments SET
                    assessment_date = ?,
                    blood_pressure_systolic = ?,
                    blood_pressure_diastolic = ?,
                    pulse = ?,
                    temperature = ?,
                    weight = ?,
                    height = ?,
                    bmi = ?,
                    blood_sugar = ?,
                    urine_protein = ?,
                    urine_glucose = ?,
                    haemoglobin = ?,
                    fetal_heart_rate = ?,
                    ultrasound_details = ?,
                    notes = ?
                    WHERE id = ? AND pid = ?";
    
    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, "siiiddddddssdssis", 
        $_POST['assessment_date'],
        $_POST['blood_pressure_systolic'],
        $_POST['blood_pressure_diastolic'],
        $_POST['pulse'],
        $_POST['temperature'],
        $_POST['weight'],
        $_POST['height'],
        $bmi,
        $_POST['blood_sugar'],
        $_POST['urine_protein'],
        $_POST['urine_glucose'],
        $_POST['haemoglobin'],
        $_POST['fetal_heart_rate'],
        $_POST['ultrasound_details'],
        $_POST['notes'],
        $assessment_id,
        $pid
    );
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Assessment updated successfully!";
        header("Location: view-assessment.php?id=".$assessment_id);
        exit();
    } else {
        $_SESSION['error'] = "Error updating assessment: " . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Assessment</title>
    <!-- Include your CSS files -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'IBM Plex Sans', sans-serif;
            padding-top: 60px;
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include your navigation bar -->

    <div class="container mt-4">
        <!-- Alerts -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fa fa-edit mr-2"></i>Edit Assessment - <?php echo date('M j, Y', strtotime($assessment['assessment_date'])); ?></h4>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Assessment Date</label>
                                    <input type="datetime-local" class="form-control" name="assessment_date" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($assessment['assessment_date'])); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5><i class="fa fa-heartbeat mr-2"></i>Vital Signs</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Blood Pressure (Systolic)</label>
                                    <input type="number" class="form-control" name="blood_pressure_systolic" 
                                           min="50" max="250" value="<?php echo $assessment['blood_pressure_systolic'] ?? ''; ?>">
                                    <small class="text-muted">mmHg</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Blood Pressure (Diastolic)</label>
                                    <input type="number" class="form-control" name="blood_pressure_diastolic" 
                                           min="30" max="150" value="<?php echo $assessment['blood_pressure_diastolic'] ?? ''; ?>">
                                    <small class="text-muted">mmHg</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Pulse Rate</label>
                                    <input type="number" class="form-control" name="pulse" 
                                           min="30" max="200" value="<?php echo $assessment['pulse'] ?? ''; ?>">
                                    <small class="text-muted">bpm</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Temperature</label>
                                    <input type="number" step="0.1" class="form-control" name="temperature" 
                                           min="35" max="42" value="<?php echo $assessment['temperature'] ?? ''; ?>">
                                    <small class="text-muted">°C</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5><i class="fa fa-weight mr-2"></i>Weight and BMI</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Weight</label>
                                    <input type="number" step="0.1" class="form-control" name="weight" 
                                           value="<?php echo $assessment['weight'] ?? ''; ?>">
                                    <small class="text-muted">kg</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Height</label>
                                    <input type="number" step="0.1" class="form-control" name="height" 
                                           value="<?php echo $assessment['height'] ?? ''; ?>">
                                    <small class="text-muted">cm</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>BMI</label>
                                    <input type="number" step="0.1" class="form-control" name="bmi" 
                                           value="<?php echo $assessment['bmi'] ?? ''; ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5><i class="fa fa-flask mr-2"></i>Lab Tests</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Blood Sugar</label>
                                    <input type="number" step="0.1" class="form-control" name="blood_sugar" 
                                           value="<?php echo $assessment['blood_sugar'] ?? ''; ?>">
                                    <small class="text-muted">mmol/L</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Urine Protein</label>
                                    <select class="form-control" name="urine_protein">
                                        <option value="">Select</option>
                                        <option value="Negative" <?php echo ($assessment['urine_protein'] ?? '') == 'Negative' ? 'selected' : ''; ?>>Negative</option>
                                        <option value="Trace" <?php echo ($assessment['urine_protein'] ?? '') == 'Trace' ? 'selected' : ''; ?>>Trace</option>
                                        <option value="1+" <?php echo ($assessment['urine_protein'] ?? '') == '1+' ? 'selected' : ''; ?>>1+</option>
                                        <option value="2+" <?php echo ($assessment['urine_protein'] ?? '') == '2+' ? 'selected' : ''; ?>>2+</option>
                                        <option value="3+" <?php echo ($assessment['urine_protein'] ?? '') == '3+' ? 'selected' : ''; ?>>3+</option>
                                        <option value="4+" <?php echo ($assessment['urine_protein'] ?? '') == '4+' ? 'selected' : ''; ?>>4+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Urine Glucose</label>
                                    <select class="form-control" name="urine_glucose">
                                        <option value="">Select</option>
                                        <option value="Negative" <?php echo ($assessment['urine_glucose'] ?? '') == 'Negative' ? 'selected' : ''; ?>>Negative</option>
                                        <option value="Trace" <?php echo ($assessment['urine_glucose'] ?? '') == 'Trace' ? 'selected' : ''; ?>>Trace</option>
                                        <option value="1+" <?php echo ($assessment['urine_glucose'] ?? '') == '1+' ? 'selected' : ''; ?>>1+</option>
                                        <option value="2+" <?php echo ($assessment['urine_glucose'] ?? '') == '2+' ? 'selected' : ''; ?>>2+</option>
                                        <option value="3+" <?php echo ($assessment['urine_glucose'] ?? '') == '3+' ? 'selected' : ''; ?>>3+</option>
                                        <option value="4+" <?php echo ($assessment['urine_glucose'] ?? '') == '4+' ? 'selected' : ''; ?>>4+</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Haemoglobin</label>
                                    <input type="number" step="0.1" class="form-control" name="haemoglobin" 
                                           value="<?php echo $assessment['haemoglobin'] ?? ''; ?>">
                                    <small class="text-muted">g/dL</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5><i class="fa fa-baby mr-2"></i>Fetal Monitoring</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fetal Heart Rate</label>
                                    <input type="number" class="form-control" name="fetal_heart_rate" 
                                           min="60" max="200" value="<?php echo $assessment['fetal_heart_rate'] ?? ''; ?>">
                                    <small class="text-muted">bpm</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Ultrasound Details</label>
                                    <textarea class="form-control" name="ultrasound_details" rows="2"><?php echo htmlspecialchars($assessment['ultrasound_details'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($assessment['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save mr-2"></i>Update Assessment
                        </button>
                        <a href="view-assessment.php?id=<?php echo $assessment_id; ?>" class="btn btn-secondary">
                            <i class="fa fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Calculate BMI when weight or height changes
        function calculateBMI() {
            var weight = parseFloat($('input[name="weight"]').val());
            var height = parseFloat($('input[name="height"]').val());
            
            if(weight && height) {
                var height_m = height / 100; // convert cm to m
                var bmi = weight / (height_m * height_m);
                $('input[name="bmi"]').val(bmi.toFixed(1));
            } else {
                $('input[name="bmi"]').val('');
            }
        }
        
        $('input[name="weight"], input[name="height"]').on('change keyup', calculateBMI);
        
        // Initial BMI calculation if values exist
        if($('input[name="weight"]').val() && $('input[name="height"]').val()) {
            calculateBMI();
        }
    });
    </script>
</body>
</html>