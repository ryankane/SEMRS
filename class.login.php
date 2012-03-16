<?php
//For security reasons, don't display any errors or warnings. Comment out in DEV.
error_reporting(0);

//start session
session_start();

// http://www.emirplicanic.com/php/simple-phpmysql-authentication-class
class logmein {
  //database setup
  //MAKE SURE TO FILL IN DATABASE INFO
  var $hostname_logon = 'localhost';	//Database server LOCATION
  var $database_logon = 'semrs';			//Database NAME
  var $username_logon = 'root';				//Database USERNAME
  var $password_logon = '';						//Database PASSWORD
 
  //table fields
  var $user_table = 'users';          //Users table name
	var $user_id = 'id';
  var $user_column = 'useremail';     //USERNAME column (value MUST be valid email)
  var $pass_column = 'password';      //PASSWORD column
  var $user_level = 'userlevel';      //(optional) userlevel column
	var $f_name = 'fname';
	var $m_name = 'mname';
	var $l_name = 'lname';
	var $facility = 'facility_id';
	var $name = 'Roger';
	
	var $facility_table = 'facility';
	var $facility_id = 'id';
	var $facility_name = 'name';
 
  //encryption
  var $encrypt = false;       //set to true to use md5 encryption for the password
 
  //connect to database
  function dbconnect(){
    $connections = mysql_connect($this->hostname_logon, $this->username_logon, $this->password_logon) or die ('Unabale to connect to the database');
    mysql_select_db($this->database_logon) or die ('Unable to select database!');
    return;
  }
 
  //login function
  function login($table, $username, $password){
    //conect to DB
    $this->dbconnect();
    //make sure table name is set
    if($this->user_table == ""){
      $this->user_table = $table;
    }
    //check if encryption is used
    if($this->encrypt == true){
      $password = md5($password);
    }
    //execute login via qry function that prevents MySQL injections
    $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->user_column."='?' AND ".$this->pass_column." = '?';" , $username, $password);
    $row=mysql_fetch_assoc($result);
    if($row != "Error"){
      if($row[$this->user_column] !="" && $row[$this->pass_column] !=""){
        //register sessions you can add additional sessions here if needed
        $_SESSION['loggedin'] = $row[$this->pass_column];
        //userlevel session is optional. Use it if you have different user levels
        $_SESSION['userlevel'] = $row[$this->user_level];
				
				//////////////////////////////
				// Get Other User Information:
				$userid = $row[$this->user_id];
				
				$result = $this->qry("SELECT ".$this->f_name.",".$this->m_name.",".$this->l_name." FROM ".$this->user_table." WHERE ".$this->user_id." = '?';", $userid);
				$row=mysql_fetch_assoc($result);
				if(row != "Error") {
				  $name = "".$row[$this->f_name]." ".$row[$this->m_name]." ".$row[$this->l_name];
					echo $name;
				}
        return true;
      } else {
        session_destroy();
        return false;
      }
    } else{
      return false;
    }
  }
 
  //prevent injection
  function qry($query) {
    $this->dbconnect();
    $args  = func_get_args();
    $query = array_shift($args);
    $query = str_replace("?", "%s", $query);
    $args  = array_map('mysql_real_escape_string', $args);
    array_unshift($args,$query);
    $query = call_user_func_array('sprintf',$args);
    $result = mysql_query($query) or die(mysql_error());
    if($result){
      return $result;
    } else {
      $error = "Error";
      return $result;
    }
  }
  
	//logout function
  function logout(){
    session_destroy();
  return;
  }
 
  //check if loggedin
  function logincheck($logincode, $user_table, $pass_column, $user_column){
    //conect to DB
    $this->dbconnect();
    //make sure password column and table are set
    if($this->pass_column == ""){
      $this->pass_column = $pass_column;
    }
    if($this->user_column == ""){
      $this->user_column = $user_column;
    }
    if($this->user_table == ""){
      $this->user_table = $user_table;
    }
    //exectue query
    $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = '?';" , $logincode);
    $rownum = mysql_num_rows($result);
    //return true if logged in and false if not
    if($row != "Error"){
      if($rownum > 0){
        return true;
      } else {
        return false;
      }
    }
  }
	
	// I added this
	function newuser($username, $password1, $password2, $level, $facility_option, $lname) {
	  //conect to DB
    $this->dbconnect();
		//if both passwords match
		echo "New User: ";
		if ($password1 == $password2) {
		  $result = $this->qry("INSERT INTO ".$this->user_table." (".$this->user_column.",".$this->pass_column.",".$this->user_level.",".$this->facility.",".$this->l_name.") VALUES ('?', '?', '?', '?', '?');", $username, $password1, $level, $facility_option, $lname);
			echo $result;
      $result = mysql_query($qry) or die(mysql_error());
		} else {
		  echo "Passwords failed to match";
		}
	}
 
    //reset password
  function passwordreset($username){
    //conect to DB
    $this->dbconnect();
    //generate new password
    $newpassword = $this->createPassword();
				
		////////////////////////////////
		// -- GIVES ME THE PASSWORD ! //
		////////////////////////////////
		echo "<p>New Password: ".$newpassword."</p>"; // ADDED THIS
 
    //make sure password column and table are set
    if($this->pass_column == ""){
      $this->pass_column = $pass_column;
    }
    if($this->user_column == ""){
      $this->user_column = $user_column;
    }
    if($this->user_table == ""){
      $this->user_table = $user_table;
    }
    //check if encryption is used
    if($this->encrypt == true){
      $newpassword_db = md5($newpassword);
    } else {
      $newpassword_db = $newpassword;
    }
 
    //update database with new password
    $qry = "UPDATE ".$this->user_table." SET ".$this->pass_column."='".$newpassword_db."' WHERE ".$this->user_column."='".stripslashes($username)."'";
    $result = mysql_query($qry) or die(mysql_error());
 
    $to = stripslashes($username);
    //some injection protection
    $illegals=array("%0A","%0D","%0a","%0d","bcc:","Content-Type","BCC:","Bcc:","Cc:","CC:","TO:","To:","cc:","to:");
    $to = str_replace($illegals, "", $to);
    $getemail = explode("@",$to);
 
    //send only if there is one email
    if(sizeof($getemail) > 2){
      return false;
    } else {
      //send email
      $from = $_SERVER['SERVER_NAME'];
      $subject = "Password Reset: ".$_SERVER['SERVER_NAME'];
      $msg = "Your new password is: ".$newpassword."";
 
      //now we need to set mail headers
      $headers = "MIME-Version: 1.0 rn" ;
      $headers .= "Content-Type: text/html; \r\n" ;
      $headers .= "From: $from  \r\n" ;
 
      //now we are ready to send mail
      $sent = mail($to, $subject, $msg, $headers);
      if($sent){
        return true;
      } else {
        return false;
      }
    }
  }
 
  //create random password with 8 alphanumerical characters
  function createPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;
    while ($i <= 7) { // Random 7 character password
      $num = rand() % 33;
      $tmp = substr($chars, $num, 1);
      $pass = $pass . $tmp;
      $i++;
    }
    return $pass;
  }
 
  //login form
  function loginform($formname, $formclass, $formaction){
    //conect to DB
    $this->dbconnect();
    echo'
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
<div><label for="username">Username</label>
<input name="username" id="username" type="text"></div>
<div><label for="password">Password</label>
<input name="password" id="password" type="password"></div>
<input name="action_login" id="action" value="login" type="hidden">
<div>
<input name="submit" id="submit" value="Login" type="submit"></div>
</form>
';
  }
  
  //reset password form
  function resetform($formname, $formclass, $formaction){
    //conect to DB
    $this->dbconnect();
    echo'
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
<div><label for="username">Username</label>
<input name="username" id="username" type="text"></div>
<input name="action_resetpassword" id="action" value="resetlogin" type="hidden">
<div>
<input name="submit" id="submit" value="Reset Password" type="submit"></div>
</form>
';
  }
	
	  //login form
  function newuserform($formname, $formclass, $formaction){
    //conect to DB
    $this->dbconnect();
    echo'
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
<div><label for="username">Choose A Username</label>
<input name="username" id="username" type="text"></div>
<div><label for="password1">Choose Your Password</label>
<input name="password1" id="password1" type="password"></div>
<div><label for="password2">Re-Enter Your Password</label>
<input name="password2" id="password2" type="password"></div>';
$this->dropdown('Choose User Level','level', 'level', 'type');
$this->dropdown('Choose Facility', 'facility', 'id', 'name');
    echo'
<div><label for="lname">Enter Your Last Name</label>
<input name="lname" id="lname" type="text"></div>
<input name="action_newuser" id="action" value="register" type="hidden">
<div>
<input name="submit" id="submit" value="Register" type="submit"></div>
</form>
';
  }
	
	// Creates a dropdown menu for a specific table
	function dropdown($label, $table, $value, $option) {
	  $this->dbconnect();
		$dropdown_list = '<div><label for="'.$table.'">'.$label.'</label><select name="'.$table.'" id="'.$table.'">';
		$qry = "SELECT * FROM ".$table.";";
    $result = mysql_query($qry) or die(mysql_error());
    while($row = mysql_fetch_assoc($result)) { $dropdown_list .= "<option value ='".$row[$value]."'>".$row[$option]."</option>"; }
    $dropdown_list .= '</select></div>';
		echo $dropdown_list;
	}
	
	//function to install logon table
  function cratetable($tablename){
    //conect to DB
    $this->dbconnect();
		// Update this to reflect current table structure
    $qry = "CREATE TABLE IF NOT EXISTS ".$tablename." (
            id int(11) NOT NULL auto_increment,
            useremail varchar(50) NOT NULL default '',
            password varchar(50) NOT NULL default '',
            userlevel int(11) NOT NULL default '0',
            PRIMARY KEY  (id)
            )";
    $result = mysql_query($qry) or die(mysql_error());
    return;
  }
}
?>