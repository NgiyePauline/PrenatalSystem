<?php
//session_start();
// appfunc.php - Complete Appointment Functions

/**
 * Get appointments for a specific patient with doctor details
 */
function getPatientAppointments($con, $patientId, $limit = null) {
    $query = "SELECT 
                a.pid,
                a.fname,
                a.lname,
                a.email,
                a.gender,
                a.contact,
                a.doctor,
                a.docFees,
                a.appdate,
                a.apptime,
                a.userStatus,
                a.doctorStatus,
                d.spec as doctor_specialization,
                d.docFees as doctor_fees
              FROM appointmenttb a 
              JOIN doctb d ON a.doctor = d.username 
              WHERE a.pid = ? 
              ORDER BY a.appdate DESC, a.apptime DESC";
              
    if ($limit !== null) {
        $query .= " LIMIT ?";
    }
    
    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        return [];
    }
    
    if ($limit !== null) {
        mysqli_stmt_bind_param($stmt, "ii", $patientId, $limit);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $patientId);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        return [];
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $appointments = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $appointments;
}

/**
 * Get available doctors for prenatal care
 */
function getPrenatalDoctors($con) {
    $query = "SELECT username, spec, docFees FROM doctb 
              WHERE spec LIKE '%Prenatal%' 
                 OR spec LIKE '%Maternity%'
                 OR spec LIKE '%Obstetrics%'
              ORDER BY username";
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        error_log("Doctor query failed: " . mysqli_error($con));
        return [];
    }
    
    $doctors = [];
    while($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
    return $doctors;
}

/**
 * Book a new appointment (fully fixed version)
 */
function bookAppointment($con, $patientId, $patientData, $doctor, $docFees, $date, $time) {
    // Validate required fields
    $requiredFields = ['fname', 'lname', 'gender', 'email', 'contact'];
    foreach ($requiredFields as $field) {
        if (empty($patientData[$field])) {
            error_log("Missing field: $field");
            return ['success' => false, 'message' => "Please provide your $field"];
        }
    }
    
    // Convert docFees to proper decimal format
    $docFees = is_numeric($docFees) ? (float)$docFees : 0.00;
    
    // Prepare SQL statement
    $query = "INSERT INTO appointmenttb 
              (pid, fname, lname, gender, email, contact, doctor, docFees, appdate, apptime, userStatus, doctorStatus) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)";
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        $error = $con->error;
        error_log("Prepare failed: $error");
        return ['success' => false, 'message' => "System error. Please try again later."];
    }
    
    // Bind parameters with correct types
    // Types: i=integer, s=string, d=double
    $bound = $stmt->bind_param(
        "issssssdss", 
        $patientId,
        $patientData['fname'],
        $patientData['lname'],
        $patientData['gender'],
        $patientData['email'],
        $patientData['contact'],
        $doctor,
        $docFees,
        $date,
        $time
    );
    
    if (!$bound) {
        $error = $stmt->error;
        error_log("Bind failed: $error");
        return ['success' => false, 'message' => "System error. Please try again."];
    }
    
    // Execute the statement
    if ($stmt->execute()) {
        $appointmentId = $stmt->insert_id;
        $stmt->close();
        return ['success' => true, 'appointmentId' => $appointmentId];
    } else {
        $error = $stmt->error;
        error_log("Execute failed: $error");
        return ['success' => false, 'message' => "Failed to book appointment. Please try again."];
    }
}

/**
 * Check appointment availability
 */
function checkAppointmentAvailability($con, $doctor, $date, $time) {
    $query = "SELECT id FROM appointmenttb 
              WHERE doctor = ? AND appdate = ? AND apptime = ? 
              LIMIT 1";
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        error_log("Availability check prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("sss", $doctor, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return ($result->num_rows === 0);
}

/**
 * Cancel an existing appointment
 */
function cancelAppointment($con, $appointmentId, $patientId) {
    $query = "UPDATE appointmenttb 
              SET userStatus = 0 
              WHERE id = ? AND pid = ?";
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        error_log("Cancel prepare failed: " . $con->error);
        return false;
    }
    
    $stmt->bind_param("ii", $appointmentId, $patientId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
?>