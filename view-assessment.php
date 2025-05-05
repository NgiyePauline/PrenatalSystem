<?php
session_start();
require_once 'db_connection.php';

if(!isset($_SESSION['national_id'])) {
    header("Location: index1.php");
    exit();
}

$assessment_id = $_GET['id'] ?? 0;
$pid = $_SESSION['national_id'];

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Assessment</title>
    <!-- Include your CSS files -->
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Assessment Details - <?php echo date('M j, Y', strtotime($assessment['assessment_date'])); ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Vital Signs</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="50%">Blood Pressure</th>
                                <td><?php echo $assessment['blood_pressure_systolic'] ? $assessment['blood_pressure_systolic'].'/'.$assessment['blood_pressure_diastolic'].' mmHg' : 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>Pulse Rate</th>
                                <td><?php echo $assessment['pulse'] ? $assessment['pulse'].' bpm' : 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>Temperature</th>
                                <td><?php echo $assessment['temperature'] ? $assessment['temperature'].' °C' : 'Not recorded'; ?></td>
                            </tr>
                        </table>
                        
                        <h5 class="mt-4">Weight and BMI</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="50%">Weight</th>
                                <td><?php echo $assessment['weight'] ? $assessment['weight'].' kg' : 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>Height</th>
                                <td><?php echo $assessment['height'] ? $assessment['height'].' cm' : 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>BMI</th>
                                <td><?php echo $assessment['bmi'] ? number_format($assessment['bmi'], 1) : 'Not calculated'; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Lab Tests</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="50%">Blood Sugar</th>
                                <td><?php echo $assessment['blood_sugar'] ? $assessment['blood_sugar'].' mmol/L' : 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>Urine Protein</th>
                                <td><?php echo $assessment['urine_protein'] ?: 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>Urine Glucose</th>
                                <td><?php echo $assessment['urine_glucose'] ?: 'Not recorded'; ?></td>
                            </tr>
                            <tr>
                                <th>Haemoglobin</th>
                                <td><?php echo $assessment['haemoglobin'] ? $assessment['haemoglobin'].' g/dL' : 'Not recorded'; ?></td>
                            </tr>
                        </table>
                        
                        <h5 class="mt-4">Fetal Monitoring</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="50%">Fetal Heart Rate</th>
                                <td><?php echo $assessment['fetal_heart_rate'] ? $assessment['fetal_heart_rate'].' bpm' : 'Not recorded'; ?></td>
                            </tr>
                        </table>
                        
                        <?php if($assessment['ultrasound_details']): ?>
                        <div class="mt-3">
                            <h5>Ultrasound Details</h5>
                            <div class="border p-3"><?php echo nl2br($assessment['ultrasound_details']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($assessment['notes']): ?>
                <div class="mt-4">
                    <h5>Notes</h5>
                    <div class="border p-3"><?php echo nl2br($assessment['notes']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="patient-dashboard.php#list-assessments" class="btn btn-secondary">
                        <i class="fa fa-arrow-left mr-2"></i>Back to Assessments
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>