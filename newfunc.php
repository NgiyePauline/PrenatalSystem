<?php
//session_start();
// Start session if not already started
// if (session_status() === PHP_SESSION_NONE) {
//     session_start([
//         'cookie_secure' => true,
//         'cookie_httponly' => true,
//         'use_strict_mode' => true
//     ]);

// Database connection with error handling
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to prevent encoding issues
mysqli_set_charset($con, "utf8mb4");

// Secure update data function
if(isset($_POST['update_data'])) {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Sanitize input
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

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

// Secure display_specs function
function display_specs() {
    global $con;
    
    $query = "SELECT DISTINCT(spec) FROM doctb";
    $result = mysqli_query($con, $query);
    
    if(!$result) {
        error_log("Database error: " . mysqli_error($con));
        return;
    }
    
    while($row = mysqli_fetch_assoc($result)) {
        $spec = htmlspecialchars($row['spec'], ENT_QUOTES, 'UTF-8');
        echo '<option data-value="' . $spec . '">' . $spec . '</option>';
    }
}

// Secure display_docs function
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
        
        echo '<option value="' . $username . '" data-value="' . $price . '" data-spec="' . $spec . '">' . $username . '</option>';
    }
}

// Secure doctor submission
if(isset($_POST['doc_sub'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Sanitize input
    $username = mysqli_real_escape_string($con, $_POST['username']);

    // Use prepared statement
    $stmt = $con->prepare("INSERT INTO doctb(username) VALUES (?)");
    $stmt->bind_param("s", $username);
    
    if($stmt->execute()) {
        header("Location: adddoc.php");
        exit();
    } else {
        error_log("Insert failed: " . $stmt->error);
        die("Failed to add doctor. Please try again.");
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>