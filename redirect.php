
<?php 
// this code is for redirecting to different pages if the credentials are correct.
   session_start();
   include "db_conn.php";
   if (isset($_SESSION['username']) && isset($_SESSION['id'])) {   
         //admin
      	if ($_SESSION['role'] == 'farmer'){
			header("Location: pages/farmer.php");
			exit();
      	 }
		 //teacher
		 else if ($_SESSION['role'] == 'transporter'){ 
			header("Location: pages/transporter.php");
			exit();
      	} 
		//student
		  else if ($_SESSION['role'] == 'buyer'){ 
			header("Location: pages/buyer.php");
			exit();
		}
 }
else{
	header("Location:index.php");
	exit();
} ?>
