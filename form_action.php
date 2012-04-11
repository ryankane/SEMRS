<?php
//instantiate if needed
include("class.login.php");
$log = new logmein();
$log->encrypt = true; //set encryption

if($_POST['action'] == "login"){
    if($log->login("logon", $_POST['username'], $_POST['password']) == true){
        //do something on successful login
				// CHECK USER LEVELS AND REDIRECT TO APPROPRIATE FILES
				//echo "Successful Login: ".$_SESSION['userfullname']."! You are now being redirected.";
				switch($_SESSION['userlevel']) {
				  case 0: // Admin
						header("Location:admin.php");
						break;
				  case 1: // Physician
						header("Location:physician.php");
						break;
				  case 2: // Receptionist
					  header("Location:receptionist.php");
						break;
				  case 3: // Pharmacist
					  header("Location:pharmacist.php");
						break;
					default: // Unknown User
					  $log->logout();
						header("location:logout.php");
				}
    } else{
        //do something on FAILED login
				echo "Failed Login";
    }
}

if($_POST['action'] == "resetlogin"){
    if($log->passwordreset($_POST['username']) == true){
        //do something on successful password reset
				echo "Successful Password Reset";
    }else{
        //do something on failed password reset
				echo "Failed Password Reset";
    }
}

if($_POST['action'] == "register"){
    if($log->newuser($_POST['username'], $_POST['password1'], $_POST['password2'], $_POST['level'], $_POST['facility'], $_POST['lname']) == true){
        //do something on successful password reset
				echo "Created New User!";
    } else{
        //do something on failed password reset
				echo "Failed to Create User";
    }
}

if($_POST['action'] == "changepassword"){
    if($log->changepassword($_POST['username'], $_POST['oldpassword'], $_POST['newpassword1'], $_POST['newpassword2']) == true){
        //do something on successful password reset
				echo "Successfully Change Password!";
    } else {
        //do something on failed password reset
				echo "Failed to Change Password!";
    }
}

if($_POST['action'] == "addpatient"){
    if($log->createpatient($_POST['title'], $_POST['fname'],	$_POST['mname'], $_POST['lname'], $_POST['dob'], $_POST['sex'], $_POST['race'], $_POST['ethnicity'], $_POST['street'], $_POST['city'], $_POST['postal_code'], $_POST['country'], $_POST['phone_home'], $_POST['phone_cell'], $_POST['regdate']) == true){
        //do something on successful password reset
				echo "Successfully Added Patient!";
    } else {
        //do something on failed password reset
				echo "Failed to Add Patient!";
    }
}

if($_POST['action'] == "searchpatient"){
    if($log->displaypatientinfo($_POST['type'], $_POST['input']) == true){
        //do something after finding patients...
				//echo "Found Patient!";
    } else {
        //do something on failed patient search...
				echo "Failed to Find Patient!";
    }
}

if($_POST['action'] == "logout"){
  $log->logout();
	//echo "Redirecting...";
	//alertbox("Successfully logged out!", "index.php");
	header("location:logout.php");
}

function alertbox($message, $origin_page) {
  return '
	<script type="text/javascript">
	if ($message.length < 1) alert("'.$message.'");
	window.location = "'.$origin_page.'";
	</script>
	';
}
?>