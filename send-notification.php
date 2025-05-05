<?php
session_start();


$con = mysqli_connect("localhost","root","","myhmsdb");

$message = $_POST['message'];
$recipient = $_POST['recipient'];


switch($recipient) {
    case 'all':
        $query = "INSERT INTO notifications (message, recipient_type) VALUES ('$message', 'all')";
        break;
    case 'patients':
        $query = "INSERT INTO notifications (message, recipient_type) VALUES ('$message', 'patient')";
        break;
    case 'providers':
        $query = "INSERT INTO notifications (message, recipient_type) VALUES ('$message', 'provider')";
        break;
}

if(mysqli_query($con, $query)) {
    echo "<script>alert('Notification sent successfully!'); window.location.href='admin-panel1.php#system-management';</script>";
} else {
    echo "<script>alert('Failed to send notification: " . mysqli_error($con) . "'); window.location.href='admin-panel1.php#system-management';</script>";
}
?>