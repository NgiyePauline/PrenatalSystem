<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//Import PHPMailers Classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$hostname=$_SERVER['HTTP_HOST'];
$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->Host = 'noderizon.com';
$mail->isHTML(true);
$mail->SMTPAuth = true;
$mail->Username = 'admin@noderizon.com';
$mail->Password = 'o@MdmvFbazah';
$mail->setFrom('admin@noderizon.com','noderizon.com');
$email='faithchepkorirk@gmail.com';
$message='renyyrozz@gmail.com';
 $mail->addAddress($email);
$mail->addReplyTo('admin@noderizon.com', 'noderizon.com');
$mail->FromName     = 'noderizon.com';
$host = 'https://'.$_SERVER['HTTP_HOST'];
$mail->Subject = "Message From Patient Prenatal online care system!";
$mail->Body  ="
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN''http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width'>
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='x-apple-disable-message-reformatting'>
<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T' crossorigin='anonymous'>
<link href='https://fonts.googleapis.com/css?family=Work+Sans:200,300,400,500,600,700' rel='stylesheet'>  
</head>
<body width='100%' style='margin: 0;  padding: 0 !important; mso-line-height-rule: exactly; background-color: #fff;margin-top:40px;margin-bottom:40px;'>
<center style='width: 100%;;'>
<div style='display: none; font-size: 1px;max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;'>
&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
</div>
</br>
<div style='max-width: 600px; margin: 0 auto;' class='email-container' >
<tr>
</tr>
<tr>
<td class='bg_white email-section' style='background-color:#f1f1f1;box-shadow:0px 0px 0px 0px lightgrey;'>
<div class='heading-section' style='text-align: center; padding: 0 1px;'>
<table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0'  style='margin:auto; border-width:1px; border: 3px 3px 3px 3px;'>
<tr>
</tr>
<tr>
<td class='bg_white email-section' style='background-color:#fff;box-shadow:0px 0px 0px 1px lightgrey;'>
<table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0'  style='width:100%;margin:auto; border-width:1px; border: 3px 3px 3px 3px;background-color:#43b06c;'>
</table>
<div class='heading-section' style='text-align: center; padding: 0 30px;'>
<h2 style='color:black;margin-top:16px;padding-bottom:10px;'>Email $email</h2>
<p style='color:black;text-align:left;'>Message '$message'</p>            
</div>
</td>
</tr>
</div>
<td>
</tr>
</table>
</table>
</div>
</br>
</center>
</body>
</html>
";
$mail->send();
//send Email
  ;?>