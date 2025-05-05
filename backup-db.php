<?php
session_start();
// if(!isset($_SESSION['username']) || $_SESSION['username'] != 'admin') {
//     header("Location: admin-login.php");
//     exit();
// }

$con = mysqli_connect("localhost","root","","myhmsdb");

// Backup file name with date
$backup_file = 'backups/prenatal_backup_' . date("Y-m-d-H-i-s") . '.sql';

// Command to export database
$command = "mysqldump --user=root --password= --host=localhost myhmsdb > " . $backup_file;

// Execute command
system($command, $output);

if($output === 0) {
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . basename($backup_file) . "\"");
    readfile($backup_file);
    unlink($backup_file); // delete file after download
    exit;
} else {
    echo "<script>alert('Backup failed!'); window.location.href='admin-panel1.php#system-management';</script>";
}
?>