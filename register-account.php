<?php
/*error_reporting(E_ALL);
ini_set('display_errors', '1');*/
ob_start();
require_once("libs/base.php");
    require_once('libs/database.php');
	require_once('libs/recaptchalib.php');
	$publickey = "6LcZMfQSAAAAAKAtEx2-8unj7t5bqASMCyuAv5LH";
	$privatekey = "6LcZMfQSAAAAAJ3xmojEVA0NswMCWLPfVvMg0-j8";
	define('SALT_LENGTH', 9); // salt for password
	function PwdHash2($pwd, $salt = null)
		{
		if ($salt === null)     
		{
			$salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
		}
		else     
		{
			$salt = substr($salt, 0, SALT_LENGTH);
		}
			return $salt . sha1($pwd . $salt);
		}
	function isEmail($email) {
	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i",$email));
	}
    if(isset($_POST['new'])) 
	{  
	 	if(empty($_POST['email']) || empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['address1']) || empty($_POST['city']) || empty($_POST['pcode']) || empty($_POST['country']) || empty($_POST['state']) || empty($_POST['privpol']) || empty($_POST['cfmpassword']) || empty($_POST['password']))
		{
			$error = '<div class="error_message">All fields are required</div>';
        }
		else
		{
			$Fname = $_POST['fname'];
			$Lname = $_POST['lname'];
			$Email = $_POST['email'];
			$activ_code = rand(1000,9999);
			$Orginalpassword = $_POST['password'];
			$Orginalpasswordcfm = $_POST['cfmpassword'];
			$Password = PwdHash2($_POST['password']);
			$CfmPassword  = PwdHash2($_POST['cfmpassword']);
			$address = $_POST['fname'].'|'.$_POST['lname'].'|'.$_POST['address1'].'|'.$_POST['address2'].'|'.$_POST['city'].'|'.$_POST['state'].'|'.$_POST['pcode'].'|'.$_POST['country'];
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$host  = $_SERVER['HTTP_HOST'];
			$host_upper = strtoupper($host);
			$path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$a_link = "http://$host$path/activate.php?user=$Email&activ_code=$activ_code"; 


			if (!isEmail($Email)) {
				$error = '<div class="error_message">Attention! Invalid e-mail address, try again.</div>';
			}elseif (strlen($Orginalpassword) < 5) {
				$error = '<div class="error_message">Attention! Your password must be at least 5 characters.</div>';
			}elseif ($Orginalpassword != $Orginalpasswordcfm) {
				$error = '<div class="error_message">Attention! Your passwords did not match.</div>';
			}else{
				
			$email_duplicate = mysql_num_rows(db_query("SELECT * FROM users WHERE email='".$Email."'"));
			if($email_duplicate > 0) 
			{
				$error = '<div class="error_message">Sorry, Email is in use.</div>';
			}
			
			//$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
     	   // if (!$resp->is_valid) 
			//{
        		//$error = '<div class="error_message">Image Verification failed!. Please try again.</div>';			
      		// }	
			  if($error == '')
			  {
				  if(empty($error)) {
					  db_query("INSERT INTO `users` (`email`, `password`, `activation_code`, `address`) VALUES ('%s', '%s', '%s', '%s')", $Email, $Password, $activ_code, $address);
					  $messagearr = explode('@CONTENT@', file_get_contents("libs/activation-template.html"));
					   
					  $message = $messagearr[0] . "
Hello $Fname,<br /><br />

Thank you for signing up at VapeLex.com! Here are your account details:<br /><br />

Username : $Email<br />
Password : $Orginalpassword<br /><br />
		
Your Account is awaiting activation, please click the link below to activate your account.<br /><br />

$a_link<br /><br />

________________________<br />
VapeLex.com Admin" . $messagearr[1];

			send_mail($Email, "Account infomation for VapeLex.com", $message);
					  header("Location: register_complete.php?username=$Username&email=$Email");
					  ob_end_flush();
					  
				  } 
			  }
		  }
	  }
  }
page_begin("Register");
?>
        	<div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Register Account</li>
					</ul>
        		</div>
        	</div>
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
							<h1 class="title">Register Account</h1>
							<p class="title-desc">If you already have an account, please <a href="login.php">login</a>.</p>
						</header>
        				<div class="xs-margin"></div><!-- space -->
						<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" id="register-form">
        				<div class="row">
        					
								<div class="col-md-6 col-sm-6 col-xs-12">

									<fieldset>
									<h2 class="sub-title">YOUR PERSONAL DETAILS</h2>
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-user"></span><span class="input-text">First Name&#42;</span></span>
										<input type="text" required class="form-control input-lg" id="fname" name="fname" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-user"></span><span class="input-text">Last Name&#42;</span></span>
										<input type="text" required class="form-control input-lg" id="lname" name="lname" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-email"></span><span class="input-text">Email&#42;</span></span>
										<input type="text" required class="form-control input-lg" id="email" name="email" placeholder="">
									</div><!-- End .input-group -->
									<span style="color:#777;">*Note: We do not send spam! Read our <a href="privacypolicy.php">Privacy Policy</a> for more information.</span>
									</fieldset>
									
									<fieldset>
									<h2 class="sub-title">YOUR PASSWORD</h2>
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-password"></span><span class="input-text">Password&#42;</span></span>
										<input type="password" required class="form-control input-lg" id="password" name="password" minlength="5" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-password"></span><span class="input-text">Re-type Password&#42;</span></span>
										<input type="password" required class="form-control input-lg" id="cfmpassword" name="cfmpassword" minlength="5" placeholder="">
									</div><!-- End .input-group -->
									</fieldset>
									
									
								</div><!-- End .col-md-6 -->
        						
        						<div class="col-md-6 col-sm-6 col-xs-12">
        						<fieldset>
									<h2 class="sub-title">YOUR ADDRESS</h2>
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-address"></span><span class="input-text">Address 1&#42;</span></span>
										<input type="text" required class="form-control input-lg" id="address1" name="address1" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-address"></span><span class="input-text">Address 2</span></span>
										<input type="text" class="form-control input-lg" id="address2" name="address2" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-city"></span><span class="input-text">City&#42;</span></span>
										<input type="text" required class="form-control input-lg" id="city" name="city"placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-postcode"></span><span class="input-text">Postal Code&#42;</span></span>
										<input type="text" required class="form-control input-lg" id="pcode" name="pcode" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-country"></span><span class="input-text">Country&#42;</span></span>
										<select name="country" class="form-control input-lg" id="country">
											<option value=" US" selected="selected">US</option>
										</select>
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-region"></span><span class="input-text">Region / State&#42;</span></span>
										<select name="state" class="form-control input-lg" id="state">
	<option value="AL">Alabama</option>
	<option value="AK">Alaska</option>
	<option value="AZ">Arizona</option>
	<option value="AR">Arkansas</option>
	<option value="CA">California</option>
	<option value="CO">Colorado</option>
	<option value="CT">Connecticut</option>
	<option value="DE">Delaware</option>
	<option value="DC">District Of Columbia</option>
	<option value="FL">Florida</option>
	<option value="GA">Georgia</option>
	<option value="HI">Hawaii</option>
	<option value="ID">Idaho</option>
	<option value="IL">Illinois</option>
	<option value="IN">Indiana</option>
	<option value="IA">Iowa</option>
	<option value="KS">Kansas</option>
	<option value="KY" selected="selected">Kentucky</option>
	<option value="LA">Louisiana</option>
	<option value="ME">Maine</option>
	<option value="MD">Maryland</option>
	<option value="MA">Massachusetts</option>
	<option value="MI">Michigan</option>
	<option value="MN">Minnesota</option>
	<option value="MS">Mississippi</option>
	<option value="MO">Missouri</option>
	<option value="MT">Montana</option>
	<option value="NE">Nebraska</option>
	<option value="NV">Nevada</option>
	<option value="NH">New Hampshire</option>
	<option value="NJ">New Jersey</option>
	<option value="NM">New Mexico</option>
	<option value="NY">New York</option>
	<option value="NC">North Carolina</option>
	<option value="ND">North Dakota</option>
	<option value="OH">Ohio</option>
	<option value="OK">Oklahoma</option>
	<option value="OR">Oregon</option>
	<option value="PA">Pennsylvania</option>
	<option value="RI">Rhode Island</option>
	<option value="SC">South Carolina</option>
	<option value="SD">South Dakota</option>
	<option value="TN">Tennessee</option>
	<option value="TX">Texas</option>
	<option value="UT">Utah</option>
	<option value="VT">Vermont</option>
	<option value="VA">Virginia</option>
	<option value="WA">Washington</option>
	<option value="WV">West Virginia</option>
	<option value="WI">Wisconsin</option>
	<option value="WY">Wyoming</option>			
		
										</select>
									</div><!-- End .input-group -->
									
									</fieldset>
        						</div><!-- End .col-md-6 -->
        					
							<div class="col-md-6 col-sm-6 col-xs-12">
								<fieldset class="half-margin">
									
									<div class="input-group custom-checkbox">
									 <input type="checkbox" id="privpol" name="privpol"> <span class="checbox-container">
									 <i class="fa fa-check"></i>
									 </span>
									 I have read and agree to the <a href="privacypolicy.php">Privacy Policy</a>.
									 
									</div><!-- End .input-group -->
                                                      <?php 
					require_once('libs/recaptchalib.php');
					echo recaptcha_get_html($publickey);
				?>                     
								</fieldset>
								
								<input type="submit" value="CREATE MY ACCCOUNT" id="new" name="new" class="btn btn-custom-2 btn-lg md-margin">
                                <?php echo $error; ?>
							</div><!-- End .col-md-6 -->
						</div><!-- End .row -->
        				</form>
        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
<?php page_end(); ?>