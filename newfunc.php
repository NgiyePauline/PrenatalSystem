<?php
// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Database connection with error handling
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("System error. Please try again later.");
}

// Set charset to prevent encoding issues
mysqli_set_charset($con, "utf8mb4");

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Secure display_specs function
if (!function_exists('display_specs')) {
    function display_specs() {
        global $con;
        
        $query = "SELECT DISTINCT spec FROM doctb";
        $result = mysqli_query($con, $query);
        
        if(!$result) {
            error_log("Database error: " . mysqli_error($con));
            return;
        }
        
        while($row = mysqli_fetch_assoc($result)) {
            $spec = htmlspecialchars($row['spec'], ENT_QUOTES, 'UTF-8');
            echo '<option value="' . $spec . '">' . $spec . '</option>';
        }
    }
}

// Secure display_docs function
if (!function_exists('display_docs')) {
    function display_docs() {
        global $con;
        
        $query = "SELECT username, docFees, spec FROM doctb";
        $result = mysqli_query($con, $query);
        
        if(!$result) {
            error_log("Database error: " . mysqli_error($con));
            return;
        }
        
        while($row = mysqli_fetch_assoc($result)) {
            $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $price = htmlspecialchars($row['docFees'], ENT_QUOTES, 'UTF-8');
            $spec = htmlspecialchars($row['spec'], ENT_QUOTES, 'UTF-8');
            
            echo '<option value="' . $username . '" data-fees="' . $price . '" data-spec="' . $spec . '">' 
                 . $username . '</option>';
        }
    }
}

// Secure doctor submission handler
if(isset($_POST['doc_sub'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("CSRF token validation failed");
        die("Security validation failed. Please try again.");
    }

    // Sanitize and validate input
    $required = ['username', 'dpassword', 'demail', 'docFees'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Required field '$field' is missing");
        }
    }

    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = password_hash($_POST['dpassword'], PASSWORD_DEFAULT);
    $email = filter_var($_POST['demail'], FILTER_VALIDATE_EMAIL);
    $fees = (float)$_POST['docFees'];

    if (!$email) {
        die("Invalid email format");
    }

    // Use prepared statement
    $stmt = $con->prepare("INSERT INTO doctb (username, password, email, docFees) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $username, $password, $email, $fees);
    
    if($stmt->execute()) {
        header("Location: adddoc.php?success=1");
        exit();
    } else {
        error_log("Doctor insert failed: " . $stmt->error);
        die("Failed to add doctor. Please try again.");
    }
}

// Secure update data handler
if(isset($_POST['update_data'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Validate inputs
    if (empty($_POST['contact']) || empty($_POST['status'])) {
        die("Required fields are missing");
    }

    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $status = in_array($_POST['status'], ['paid', 'pay later']) ? $_POST['status'] : 'pay later';

    // Use prepared statement
    $stmt = $con->prepare("UPDATE appointmenttb SET payment = ? WHERE contact = ?");
    $stmt->bind_param("ss", $status, $contact);
    
    if($stmt->execute()) {
        header("Location: updated.php");
        exit();
    } else {
        error_log("Update failed: " . $stmt->error);
        die("Update failed. Please try again.");
    }
}

// Close database connection at the end of script execution
register_shutdown_function(function() use ($con) {
    if ($con) {
        mysqli_close($con);
    }
});
?>