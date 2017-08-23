<?php
ob_start();
	require_once("libs/base.php");
	function random_password( $length = 8 ) {
    	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    	$password = substr( str_shuffle( $chars ), 0, $length );
    	return $password;
	}
	if($_SESSION['logged_in']) {
		header("Location: /account.php");
		ob_end_flush();
		exit();	
	}
	if(isset($_GET['reset'])) {
		$email = $_GET['reset'];
		$q = db_query("SELECT * FROM users WHERE email='%s'", $email);
		if(mysql_num_rows($q) == 1) {
			$new_pass = random_password();
			db_query("UPDATE users SET password='%s' WHERE email='%s'", PwdHash($new_pass), $email);
			$user_details = mysql_fetch_assoc($q);
			$Fname = explode('|', $user_details['address']);
			$Fname = $Fname[0];
			
			$messagearr = explode('@CONTENT@', file_get_contents("libs/activation-template.html"));
					   
					  $message = $messagearr[0] . "
Hello $Fname,<br /><br />

A password reset request has been submitted for your account. Here is your new temporary login information:<br /><br />

Username : $email<br />
Password : $new_pass<br /><br />
		
If you did not submit this request, please notify support by replying to this email.<br /><br />

________________________<br />
VapeLex.com Admin" . $messagearr[1];

			send_mail($email, "VapeLex.com Password Reset", $message);
			$onloadscript = "alert('Instructions on resetting your password have been sent to ".$email.".');";
		}
	}
    if($_POST['email'])
	{
		
		$pass = $_POST['password'];
		$p = db_query("SELECT password FROM users WHERE email = '%s'", $_POST['email']);
		$row = mysql_fetch_array($p);
    	$response = $row['password'];
        $q = db_query("SELECT * FROM users WHERE email = '%s' AND password = '%s'", $_POST['email'], PwdHash($pass,substr($response,0,9)));
		//$msg = $p;
        if(mysql_num_rows($q) == 1)
		{
            $result = mysql_fetch_object($q);
			$activated = $result->activated;
			if ($activated == "verify")
			{
				$errormsg = '<span class="help-block text-right">Account has not been activated!</div>';
			}
			else
			{
				if($_POST['from'] && strpos($_POST['from'], $result->type) !== FALSE)
				{
					
					$errormsg = '<span class="help-block text-right">Incorrect username or password</div>';    
				} else {
				if(isset($_POST['ref'])) {
						$from = $_POST['ref'];
				} else $from = "login";
				$ss->Open();
				$USER += array(
					'logged_in' => time(),
					'uid' => $result->uid,
					'email' => $result->email,
					'address' => $result->address,
					'phone' => $result->phone,
					'cart_items' => $result->cart_items,
					'lastip' => $result->lastip,
					'last_login'=> $result->last_login,
					'account_type' => "user",
					'from' => $from
				);
				
				// update last login
				db_query('UPDATE users SET lastip = %d WHERE uid = %d', $_SERVER['REMOTE_ADDR'], $USER['uid']);
				db_query('UPDATE users SET last_login = %d WHERE uid = %d', time(), $USER['uid']);
					
				
				switch($USER['from']){
					case 'theclicker':
						header('Location: /theclicker.php'); 
						break;
					case 'checkout':
						header('Location: /checkout.php');
						break;
					default:
						header('Location: /account.php'); 
				}
				
				exit();
				}
			}    
		}
	} else { 
	}
	ob_end_flush();
	page_begin('My Account');
?>

        	<div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Login</li>
					</ul>
        		</div>
        	</div>
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
							<h1 class="title">Login or Create An Account</h1>
                            <div class="md-margin"></div><!-- space -->
						</header>
        			
						   <div class="row">
							   	
							   	<div class="col-md-6 col-sm-6 col-xs-12">					   		
							   		<h2>New Customer</h2>
							   		
							   	<p>By creating an account with our store, you will be able to move through the checkout process faster, view and track orders made on your account, receive exclusive discounts and free e-liquid samples, and more.</p>
                                <div class="md-margin"></div><!-- space -->
							   	<a href="register-account.php" class="btn btn-custom-2">Create An Account</a>
                                <div class="lg-margin"></div><!-- space -->
							   	</div><!-- End .col-md-6 -->
							   	<div class="col-md-6 col-sm-6 col-xs-12">					   		
							   		<h2>Registered Customers</h2>
							   		<p>If you have an account with us, please log in.</p>
							   		<div class="xs-margin"></div>
							   		
									<form id="login-form" method="post" action="<?php print $_SERVER['PHP_SELF']; ?>">
                                        <div class="input-group">
                                            <span class="input-group-addon"><span class="input-icon input-icon-email"></span><span class="input-text">Email&#42;</span></span>
                                            <input type="text" required class="form-control input-lg" id="email" name="email" placeholder="Your Email">
                                        </div><!-- End .input-group -->
                                         <div class="input-group xs-margin">
                                            <span class="input-group-addon"><span class="input-icon input-icon-password"></span><span class="input-text">Password&#42;</span></span>
                                            <input id="password" name="password" type="password" required class="form-control input-lg" placeholder="Your Password">
                                        </div><!-- End .input-group -->
                                    <span class="help-block text-right"><a onClick="window.location.href=('http://vapelex.com/login.php?reset='+$('#email').val());" href="javascript:void(0)">Forgot your password?</a></span>
                                    <?php if(isset($errormsg)) echo($errormsg); ?>
                                    <button class="btn btn-custom-2">LOGIN</button>
                                    </form>
                                  	
                                    <div class="sm-margin"></div><!-- space -->
							   	</div><!-- End .col-md-6 -->
							   	
						   </div><!-- End.row -->
								   
        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
        
<?php page_end(); ob_end_flush(); ?>