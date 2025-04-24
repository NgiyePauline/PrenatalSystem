<?php
session_start();
$con=mysqli_connect("localhost","root","","myhmsdb");
$patient=$_POST['patient'];
$doctor=$_POST['doctor'];
$message=$_POST['message'];
$query="insert into messages(message,doctor,patient) values ('$message','$doctor','$patient');";
    $result=mysqli_query($con,$query);
    include'mail.php';
header("Location: admin-panel.php")
    ?>