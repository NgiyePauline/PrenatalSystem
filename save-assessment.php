<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (mysqli_connect_errno()) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("System error. Please try again later.");
}

// Check if user is logged in (uncomment when ready)
// if(!isset($_SESSION['national_id'])) {
//     header("Location: index1.php");
//     exit();
// }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    if(empty($_POST['assessment_date'])) {
        $_SESSION['error'] = "Assessment date is required";
        header("Location: patient-dashboard.php#list-assessments");
        exit();
    }

    $pid = $_SESSION['national_id'];
    $assessment_date = mysqli_real_escape_string($con, $_POST['assessment_date']);
    
    // Sanitize and validate numeric inputs
    $blood_pressure_systolic = isset($_POST['blood_pressure_systolic']) ? (int)$_POST['blood_pressure_systolic'] : null;
    $blood_pressure_diastolic = isset($_POST['blood_pressure_diastolic']) ? (int)$_POST['blood_pressure_diastolic'] : null;
    $pulse = isset($_POST['pulse']) ? (int)$_POST['pulse'] : null;
    $temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : null;
    $weight = isset($_POST['weight']) ? (float)$_POST['weight'] : null;
    $height = isset($_POST['height']) ? (float)$_POST['height'] : null;
    $blood_sugar = isset($_POST['blood_sugar']) ? (float)$_POST['blood_sugar'] : null;
    $haemoglobin = isset($_POST['haemoglobin']) ? (float)$_POST['haemoglobin'] : null;
    $fetal_heart_rate = isset($_POST['fetal_heart_rate']) ? (int)$_POST['fetal_heart_rate'] : null;
    
    // Sanitize text inputs
    $urine_protein = isset($_POST['urine_protein']) ? mysqli_real_escape_string($con, $_POST['urine_protein']) : null;
    $urine_glucose = isset($_POST['urine_glucose']) ? mysqli_real_escape_string($con, $_POST['urine_glucose']) : null;
    $ultrasound_details = isset($_POST['ultrasound_details']) ? mysqli_real_escape_string($con, $_POST['ultrasound_details']) : null;
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($con, $_POST['notes']) : null;
    
    // Calculate BMI if weight and height are provided
    $bmi = null;
    if($weight && $height) {
        $height_m = $height / 100; // convert cm to m
        $bmi = $weight / ($height_m * $height_m);
    }
    
    $query = "INSERT INTO patient_assessments (
                pid, assessment_date, blood_pressure_systolic, blood_pressure_diastolic, 
                pulse, temperature, weight, height, bmi, blood_sugar, urine_protein, 
                urine_glucose, haemoglobin, fetal_heart_rate, ultrasound_details, notes
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $query);
    if(!$stmt) {
        $_SESSION['error'] = "Database error: " . mysqli_error($con);
        header("Location: patient-dashboard.php#list-assessments");
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "ssiiiddddddssdss", 
        $pid,
        $assessment_date,
        $blood_pressure_systolic,
        $blood_pressure_diastolic,
        $pulse,
        $temperature,
        $weight,
        $height,
        $bmi,
        $blood_sugar,
        $urine_protein,
        $urine_glucose,
        $haemoglobin,
        $fetal_heart_rate,
        $ultrasound_details,
        $notes
    );
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Assessment saved successfully!";
    } else {
        $_SESSION['error'] = "Error saving assessment: " . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
    header("Location: admin-panel.php#list-assessments");
    exit();
} else {
    // Not a POST request - redirect to dashboard
    header("Location: admin-panel.php");
    exit();
}
?>