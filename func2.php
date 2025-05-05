<?php
session_start();
$con=mysqli_connect("localhost","root","","myhmsdb");
if(isset($_POST['patsub1'])){
    $fname=$_POST['fname'];
    $lname=$_POST['lname'];
    $gender=$_POST['gender'];
    $email=$_POST['email'];
    $contact=$_POST['contact'];
    $password=$_POST['password'];
    $cpassword=$_POST['cpassword'];
    $dob=$_POST['dob'];
    $national_id=$_POST['national_id'];
    $marital_status=$_POST['marital_status'];
    $emergency_contact=$_POST['emergency_contact'];
    
    if($password==$cpassword){
        $query="insert into patreg(fname,lname,gender,email,contact,password,cpassword,dob,national_id,marital_status,emergency_contact) 
                values ('$fname','$lname','$gender','$email','$contact','$password','$cpassword','$dob','$national_id','$marital_status','$emergency_contact');";
        $result=mysqli_query($con,$query);
        if($result){
            $_SESSION['username'] = $_POST['fname']." ".$_POST['lname'];
            $_SESSION['fname'] = $_POST['fname'];
            $_SESSION['lname'] = $_POST['lname'];
            $_SESSION['gender'] = $_POST['gender'];
            $_SESSION['contact'] = $_POST['contact'];
            $_SESSION['email'] = $_POST['email'];
            $_SESSION['dob'] = $_POST['dob'];
            $_SESSION['national_id'] = $_POST['national_id'];
            $_SESSION['marital_status'] = $_POST['marital_status'];
            $_SESSION['emergency_contact'] = $_POST['emergency_contact'];
            header("Location:index1.php");
        } 

        $query1 = "select * from patreg where contact='$contact';";
        $result1 = mysqli_query($con,$query1);
        if($result1){
            $row = mysqli_fetch_array($result1);
            $_SESSION['pid'] = $row['pid'];
        }
    }
    else{
        header("Location:error1.php");
    }
}

// Rest of your existing code remains the same...
if(isset($_POST['update_data']))
{
    $contact=$_POST['contact'];
    $status=$_POST['status'];
    $query="update appointmenttb set payment='$status' where contact='$contact';";
    $result=mysqli_query($con,$query);
    if($result)
        header("Location:updated.php");
}

if(isset($_POST['doc_sub']))
{
    $name=$_POST['name'];
    $query="insert into doctb(name)values('$name')";
    $result=mysqli_query($con,$query);
    if($result)
        header("Location:adddoc.php");
}

// Rest of your functions (display_docs, display_admin_panel) remain unchanged
?>