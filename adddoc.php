<?php
session_start();

// // Check if user is logged in and has admin privileges


// Include database connection
require_once 'newfunc.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Sanitize and validate input
    $username = trim($_POST['username']);
    $spec = trim($_POST['spec']);
    $docFees = (float)$_POST['docFees'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($spec)) {
        $errors[] = "Specialization is required";
    }
    
    if ($docFees <= 0) {
        $errors[] = "Invalid fee amount";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statement to prevent SQL injection
        $stmt = $con->prepare("INSERT INTO doctb (username, password, spec, docFees) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $username, $hashed_password, $spec, $docFees);

        if ($stmt->execute()) {
            $success = "Provider added successfully!";
        } else {
            $errors[] = "Error adding Provider: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - Prenatal Care System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            max-width: 600px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(to right, #3931af, #00c6ff);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-primary {
            background-color: #3931af;
            border-color: #3931af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Add New Doctor</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="adddoc.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="spec" class="form-label">Specialization</label>
                        <input type="text" class="form-control" id="spec" name="spec" required
                               value="<?php echo isset($_POST['spec']) ? htmlspecialchars($_POST['spec']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="docFees" class="form-label">Consultation Fees</label>
                        <input type="number" class="form-control" id="docFees" name="docFees" step="0.01" min="0" required
                               value="<?php echo isset($_POST['docFees']) ? htmlspecialchars($_POST['docFees']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
                        <a href="admin-panel1.php" class="btn btn-secondary">Home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>