<?php
ob_start();
	require_once('libs/database.php');
	require_once('libs/base.php');
	if(isset($_GET['user']) && !empty($_GET['activ_code']) && !empty($_GET['user']) && is_numeric($_GET['activ_code']) ) 
	{
		$err = array();
		$msg = array();
		$user = $_GET['user'];
		$activ = $_GET['activ_code'];
		$rs_check = db_query("SELECT uid from users where email='$user' and activation_code='$activ'"); 
		$num = mysql_num_rows($rs_check);
		
	if ( $num <= 0 ) 
	{ 
		$err[] = "Invalid Authorization Code!";
	}

	if(empty($err)) 
	{
		$rs_activ = db_query("UPDATE users set activated='activated' WHERE email='$user' AND activation_code = '$activ' ");
		$msg[] = "Your account has now been activated, you will be redirected to the Login page momentarily...";
 	}
}
page_begin("Activated");
?>
<body onload="JavaScript: document._xclick.submit();">
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
                    <div class="xs-margin"></div><!-- space -->
						<header class="content-title">
                                            <div class="xs-margin"></div><!-- space -->
							<h1 class="title" align="center">Congratulations!</h1>
							<p class="title-desc">&nbsp;</p>
						</header>
        				<div class="xs-margin"></div><!-- space -->
	    <?php
	  /******************** ERROR MESSAGES*************************************************
	  This code is to show error messages 
	  **************************************************************************/
	if(!empty($err))  {
	   echo "<div class=\"msg\">";
	  foreach ($err as $e) {
	    echo "* $e <br>";
	    }
	  echo "</div>";	
	   }
	   if(!empty($msg))  {
	    echo "<div align=\"center\" class=\"msg\">" . $msg[0] . "</div>";

	   }	
	  /******************************* END ********************************/	  
	  ?></strong></p>
               <p align="center"><img src="images/loading.GIF" title="Redirecting you to Login..." /><br /></p>
            </div>
            
            </form>
            <form action="http://vapelex.com/login.php" method="post" name="_xclick"/>
</form>
</div>
</div>
</div>
</body>
<?php page_end(); ?>

