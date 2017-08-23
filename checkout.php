<?php
require_once("libs/base.php");
require_once("libs/usps.php");
require_once('libs/recaptchalib.php');
	$publickey = "6LcZMfQSAAAAAKAtEx2-8unj7t5bqASMCyuAv5LH";
	$privatekey = "6LcZMfQSAAAAAJ3xmojEVA0NswMCWLPfVvMg0-j8";
	function isEmail($email) {
	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i",$email));
	}
if(isset($_COOKIE['vapelex_cart'])) {
	$cart_data = $cart->getCartItems();
	$total = $cart->getCartTotal();
	$coupon = "";
	if(isset($_POST['ccode'])) $coupon = $_POST['ccode'];
	global $cart_data;
	global $total;
	$onloadfunc = "";
	if(isset($_SESSION['logged_in'])) {
		$udetails = explode('|', $_SESSION['address']);
		$onloadfunc = "autoFill('".$udetails[0]."','".$udetails[1]."','".$_SESSION['email']."','".$udetails[2]."','".$udetails[3]."','".$udetails[4]."','".$udetails[5]."','".$udetails[6]."')";	
	}
	
}

$error = "";

if(count($cart_data) > 0 && isset($_GET['submit'])) {
	//Get all items in cart, get price, quantity, info, etc. and submit transaction to third party processor
	
	$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
     
	//Check all fields of form and confirm completion
	if(!(isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['email1']) && isset($_POST['a1']) && isset($_POST['city']) && isset($_POST['select-state']) && isset($_POST['zipcode']) && isset($_POST['bfname']) && isset($_POST['blname']) && isset($_POST['ba1']) && isset($_POST['ba2']) && isset($_POST['bcity']) && isset($_POST['bzipcode']) && isset($_POST['bselect-state']) && isset($_POST['select-service']) && isset($_POST['ppolicy']))) {
		
		$error .= "<h4>Error: All forms must be completed before continuing.</h4>";
		
	} if(!isEmail($_POST['email1'])) {
		
		$error .= "<h4>Error: Invalid E-Mail Address</h4>";
		
	} if (!$resp->is_valid){
		
		$error .= "<h4>Image Verification Failed! Please try again.</h4>";
		
		
		
	} else if($error == "") {
		
		
		if(isset($_POST['a2'])) $address2 = $_POST['a2']; else $address2="";
		$shipping_address = $_POST['fname'].'|'.$_POST['lname'].'|'.$_POST['email1'].'|'.$_POST['a1'].'|'.$address2.'|'.$_POST['city'].'|'.$_POST['select-state'].'|'.$_POST['zipcode'];
		
		if(isset($_POST['ba2'])) $baddress2 = $_POST['ba2']; else $baddress2="";
		if(isset($_POST['bemail'])) $bemail = $_POST['bemail']; else $bemail="";
		$billing_address = $_POST['bfname'].'|'.$_POST['blname'].'|'.$bemail.'|'.$_POST['ba1'].'|'.$baddress2.'|'.$_POST['bcity'].'|'.$_POST['bselect-state'].'|'.$_POST['bzipcode'];
		if(isset($_POST['discreet'])) $discreet = true; else $discreet=false;
		if(isset($_POST['coupon'])) $discount = checkCoupon($_POST['coupon']); else $discount = 0;
	$service = $_POST['select-service'];
	$weight = explode(';', $cart->getCartWeight());
	$weightlbs = $weight[0];
	$weightozs = $weight[1];
	do {
	 $params = USPSParcelRate($weightlbs, $weightozs, $_POST['zipcode'], $_POST['select-service']);
	} while(isset($params['ERROR']));
	if($service == "FIRST CLASS") {
	$shipping = $params['RATEV4RESPONSE']['0']['0']['RATE'];
	} if($service == "PRIORITY") {
	$shipping = $params['RATEV4RESPONSE']['0']['28']['RATE'];
	} if($service == "PRIORITY MAIL EXPRESS") {
	$shipping = $params['RATEV4RESPONSE']['0']['3']['RATE'];
}
//echo('<pre>'); print_r($params); echo('</pre>');
//echo $params['RATEV4RESPONSE']['0']['3']['RATE'];
	$count = 0;
	$order_id = 0;
	
	global $cart_data;
	global $order_id;
	global $total;
	$total = $cart->getCartTotal();
	$total = $total - ($total*($discount/100));
	$total += $shipping;
	do {
		//Create unique transaction ID
		$order_id = rand(99999999, 999999999);
		$q = db_query("SELECT * FROM transactions WHERE order_id='%s'", $order_id);
		$row = mysql_fetch_array($q);
	} while (mysql_num_rows($row) > 0);
	$user_id = 0;
	if(isset($_SESSION['uid'])) $user_id = $_SESSION['uid']; 
	require_once 'libs/anet_php_sdk/AuthorizeNet.php';
	$api_login_id = '9d6KC7j3';
	$transaction_key = '5Mf9ty26yMKTr56W';
	$tapi_login_id = '9AuDc583';
	$ttransaction_key = '99D4p83hHwS2sq9w';
	$amount = $total;
	$fp_timestamp = time();
	$fp_sequence = $order_id; // Enter an invoice or other unique number.
	$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id,
  	$transaction_key, $amount, $fp_sequence, $fp_timestamp);
	$item_fields = "";
	foreach($cart_data as $item) {
		$item_attr = $cart->getItemData($item[0]);
		$item_desc = str_replace(';', ' ', $item[3]);
		$item_fields .= '<input type="hidden" name="x_line_item" value="item'.$item[0].'<|>'.substr($item_attr['name'],0,31).'<|>'.str_replace('_','.',$item_desc).'<|>'.$item[1].'<|>'.$cart->getItemPrice($item[0], $item[3]).'<|>N" />';	
	}
	if($discount>0) { 
	//Show coupon on payment form
		$item_fields .= '<input type="hidden" name="x_line_item" value="Coupon<|>'.strtoupper($_POST['coupon']).'<|>'.$discount.'% off your order<|>1<|>0.00<|>N" />';	
	}
	db_query("INSERT INTO transactions VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', CURDATE())", 0, $order_id, $user_id, $fingerprint, stringToHex(json_encode($cart_data)), $total, $shipping, $_POST['select-service'], $_POST['email1'], $shipping_address, $billing_address, $discreet, strtoupper($_POST['coupon']), false, ""); 
	$formsubstr = '
    <FORM METHOD="POST" ACTION="https://secure.authorize.net/gateway/transact.dll" name="_xclick"> 
	<input type="hidden" name="x_login" value="'.$api_login_id.'" />
	<input type="hidden" name="x_fp_hash" value="'.$fingerprint.'" />
	<input type="hidden" name="x_amount" value="'.$amount.'" />
	<input type="hidden" name="x_freight" value="'.$shipping.'" />
	<input type="hidden" name="x_fp_timestamp" value="'.$fp_timestamp.'" />
	<input type="hidden" name="x_fp_sequence" value="'.$fp_sequence.'" />
	<input type="hidden" name="x_version" value="3.1">
	<input type="hidden" name="x_show_form" value="payment_form">
	<input type="hidden" name="x_test_request" value="false" />
	<input type="hidden" name="x_method" value="cc">
	<input type="hidden" name="x_invoice_num" value="'.$order_id.'" />
	'. $item_fields .'
	</FORM>';
	page_begin("Checkout");
	?>
    <body onLoad="JavaScript: document._xclick.submit();">
    <div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Checkout</li>
					</ul>
        		</div>
        	</div>
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
							<h1 class="title" style="text-align:center">Redirecting you to our secure third-party payment processor...						</h1>
                            <?php echo($formsubstr); ?>
						</header>
       				  <div class="xs-margin"></div><!-- space -->
                      	<div align="center"><img src="images/ajax-loader.gif" /></div>
                       
                    </div>
                </div>
            </div>
           </body>
                     
    
<?php
	page_end();
	}
}if (!isset($_GET['submit']) || $error != "") {
	
		//If user is logged in, set attributes of form elements
		$formemail = "";
		$disabled = "";
		if(isset($_SESSION['logged_in'])) {
			$formemail = $_SESSION['email'];	
			$disabled = "disabled";
			
		}
		
		
	page_begin("Checkout");
?>
        	<div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Checkout</li>
					</ul>
        		</div>
        	</div>
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
							<h1 class="title">Checkout</h1>
						</header>
       				  <div class="xs-margin"></div><!-- space -->
                      <?php echo($error); ?>
        				<form method="post" action="checkout.php?submit=1" id="checkout-form">
        				<div class="panel-group custom-accordion" id="checkout">
							<div class="panel">
								<div class="accordion-header">
									<div class="accordion-title">Step 1: <span>Checkout Option</span></div><!-- End .accordion-title -->
									<a class="accordion-btn opened"  data-toggle="collapse" data-target="#checkout-option"></a>
								</div><!-- End .accordion-header -->
								
								<div id="checkout-option" class="collapse in">
								  <div class="panel-body">
								   <div class="row">
								   	
								   	<div class="col-md-6 col-sm-6 col-xs-12">					   		
								   		<h2 class="checkout-title">New Customer						   		</h2>
							   		  <div class="xs-margin"></div>
								   		<div class="input-group custom-checkbox sm-margin">
											 <input id="guestbox" type="checkbox" <?php echo($disabled); ?>> <span class="checbox-container">
											 	<i class="fa fa-check"></i>
											 </span>
											 Checkout as a Guest
										 
										</div><!-- End .input-group -->
								   		<div class="input-group custom-checkbox sm-margin">
											 <input onChange="window.location='http://vapelex.com/register-account.php'" type="checkbox" <?php echo($disabled); ?>> <span class="checbox-container">
											 	<i class="fa fa-check"></i>
											 </span>
											 Register
										 
										</div><!-- End .input-group -->
								   	<p>By creating an account with our store, you will be able to move through the checkout process faster, view and track orders made on your account, receive exclusive discounts and free e-liquid samples, and more.</p>
									<div class="md-margin"></div>
								   	
								   	</div><!-- End .col-md-6 -->
								   
								   	<div class="col-md-6 col-sm-6 col-xs-12">					   		
								   		<h2 class="checkout-title">Registered Customers</h2>
								   		<p>If you have an account with us, please log in.</p>
								   		<div class="xs-margin"></div>
								   		
										<div class="input-group">
											<span class="input-group-addon"><span class="input-icon input-icon-email"></span><span class="input-text">Email&#42;</span></span>
											<input id="lemail" type="text" name="email" class="form-control input-lg" placeholder="" value="<?php echo($formemail); ?>" <?php echo($disabled); ?>>
										</div><!-- End .input-group -->
										<div class="input-group xs-margin">
											<span id="lpassword" class="input-group-addon"><span class="input-icon input-icon-password"></span><span class="input-text">Password&#42;</span></span>
											<input type="password" id="password" class="form-control input-lg" placeholder="" <?php echo($disabled); ?>>
                                           
										</div><!-- End .input-group -->
								   		<span class="help-block text-right"><a onClick="window.location.href=('http://vapelex.com/login.php?reset='+$('#email').val());" href="javascript:void(0)">Forgot your password?</a></span>
                                        <input type="button" type="button" onClick="qlogin();" class="btn btn-custom-2" value="LOGIN" <?php echo($disabled); ?>>
								   	</div><!-- End .col-md-6 -->
								   	
								   </div><!-- End.row -->
								   
								   <a id="continue1" href="javascript:void(0)" onClick="$('#shipexpander').trigger('click')" class="btn btn-custom-2">CONTINUE</a>
								  </div><!-- End .panel-body -->
								</div><!-- End .panel-collapse -->
							  
							  </div><!-- End .panel -->
							  
							  <div class="panel">
								<div class="accordion-header">
									<div class="accordion-title">Step 2: <span>Shipping Information</span></div><!-- End .accordion-title -->
									<a id="shipexpander" class="accordion-btn" onClick="<?php echo($onloadfunc); ?>" href="javascript:void(0)"  data-toggle="collapse" data-target="#shipping"></a>
								</div><!-- End .accordion-header -->
								
								<div id="shipping" class="collapse">
								  <div class="panel-body">
								   <div class="row">
								   	<div class="col-md-6 col-sm-6 col-xs-12">
								   		
								   		<h2 class="checkout-title">Personal details</h2>
								   		<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-user"></span><span class="input-text">First Name&#42;</span></span>
										<input type="text" name="fname" id="fname" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-user"></span><span class="input-text">Last Name&#42;</span></span>
										<input type="text" name="lname" id="lname" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-email"></span><span class="input-text">Email&#42;</span></span>
										<input type="text" name="email1" id="email1" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-phone"></span><span class="input-text">Phone</span></span>
										<input type="text" name="phone" id="phone" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
								   	<div class="input-group xlg-margin">
										<span class="input-group-addon"><span class="input-icon input-icon-company"></span><span class="input-text">Company</span></span>
										<input type="text" name="company" id="company" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
								   	
							
								   	
								   	<div class="input-group custom-checkbox sm-margin">
											 <input onChange="shipToBill()" type="checkbox"> <span class="checbox-container">
											 	<i class="fa fa-check"></i>
											 </span>
											 My delivery and billing addresses are the same.
										 
									</div><!-- End .input-group -->
								   	
								   	</div><!-- End .col-md-6 -->
								   	
								   	<div class="col-md-6 col-sm-6 col-xs-12">
									<h2 class="checkout-title">Address</h2>
									
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-address"></span><span class="input-text">Address 1&#42;</span></span>
										<input type="text" name="a1" id="a1" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-address"></span><span class="input-text">Address 2</span></span>
										<input type="text" name="a2" id="a2" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-city"></span><span class="input-text">City&#42;</span></span>
										<input type="text" name="city" id="city" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-postcode"></span><span class="input-text">Zip Code&#42;</span></span>
										<input type="text" name="zipcode" id="zipcode" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-country"></span><span class="input-text">Country&#42;</span></span>
										<select name="select-country" id="country" class="form-control input-lg" id="select-country">
                                            <option value="US">US</option>
										</select>
									</div><!-- End .input-group -->
									<div class="input-group lg-margin">
										<span class="input-group-addon"><span class="input-icon input-icon-region"></span><span class="input-text">State&#42;</span></span>
										<select name="select-state" id="select-state" class="form-control input-lg" id="select-state">
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
								   	<div class="input-group custom-checkbox md-margin">
											 <input name="ppolicy" type="checkbox"> <span class="checbox-container">
											 	<i class="fa fa-check"></i>
											 </span>
											 I have read and agree to the <a href="privacypolicy.php">Privacy Policy</a>.
										 
									</div><!-- End .input-group -->
								   	<a href="javascript:void(0)" onClick="$('#billexpander').trigger('click')" class="btn btn-custom-2">CONTINUE</a>
								   	</div><!-- End .col-md-6 -->
								   	
								   </div><!-- End .row -->
								  </div><!-- End .panel-body -->
								</div><!-- End .panel-collapse -->
							  
							  </div><!-- End .panel -->
							  
							  <div class="panel">
								<div class="accordion-header">
									<div class="accordion-title">Step 3: <span>Billing Information</span></div><!-- End .accordion-title -->
									<a id="billexpander" class="accordion-btn"  data-toggle="collapse" data-target="#billing"></a>
								</div><!-- End .accordion-header -->
								
								<div id="billing" class="collapse">
								  <div class="panel-body">
								   <div class="row">
								   	<div class="col-md-6 col-sm-6 col-xs-12">
								   		
								   		<h2 class="checkout-title">Personal details</h2>
								   		<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-user"></span><span class="input-text">First Name&#42;</span></span>
										<input type="text" name="bfname" id="bfname" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-user"></span><span class="input-text">Last Name&#42;</span></span>
										<input type="text" name="blname" id="blname" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-email"></span><span class="input-text">Email&#42;</span></span>
										<input type="text" name="bemail" id="bemail" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-phone"></span><span class="input-text">Phone</span></span>
										<input type="text" name="bphone" id="bemail" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
								   	<div class="input-group xlg-margin">
										<span class="input-group-addon"><span class="input-icon input-icon-company"></span><span class="input-text">Company</span></span>
										<input type="text" name="bcompany" id="bcompany"  class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
								   	
								   	
								   	
								   	</div><!-- End .col-md-6 -->
								   	
								   	<div class="col-md-6 col-sm-6 col-xs-12">
									<h2 class="checkout-title">Address</h2>
									
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-address"></span><span class="input-text">Address 1&#42;</span></span>
										<input type="text" name="ba1" id="ba1" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-address"></span><span class="input-text">Address 2</span></span>
										<input type="text" name="ba2" id="ba2" class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-city"></span><span class="input-text">City&#42;</span></span>
										<input type="text" name="bcity" id="bcity" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-postcode"></span><span class="input-text">Postal Code&#42;</span></span>
										<input type="text" name="bzipcode" id="bzipcode" required class="form-control input-lg" placeholder="">
									</div><!-- End .input-group -->
									<div class="input-group">
										<span class="input-group-addon"><span class="input-icon input-icon-country"></span><span class="input-text">Country&#42;</span></span>
										<select name="bselect-country" class="form-control input-lg" id="bselect-country">
                                            <option value="US">US</option>
										</select>
									</div><!-- End .input-group -->
									<div class="input-group lg-margin">
										<span class="input-group-addon"><span class="input-icon input-icon-region"></span><span class="input-text">Region / State&#42;</span></span>
										<select name="bselect-state" class="form-control input-lg" id="bselect-state">
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
								   	
								   	<a href="javascript:void(0)" onClick="$('#deliveryexpander').trigger('click')" class="btn btn-custom-2">CONTINUE</a>
								   	</div><!-- End .col-md-6 -->
								   	
								   </div><!-- End .row -->
								  </div><!-- End .panel-body -->
								</div><!-- End .panel-collapse -->
							  
							  </div><!-- End .panel -->
							  
							  
							  <div class="panel">
								<div class="accordion-header">
									<div class="accordion-title">Step 4: <span>Delivery Method</span></div><!-- End .accordion-title -->
									<a id="deliveryexpander" class="accordion-btn"  data-toggle="collapse" data-target="#delivery-method"></a>
								</div><!-- End .accordion-header -->
								
								<div id="delivery-method" class="collapse">
								  <div class="panel-body">
								  	<div class="row">
								   	
								   	<div class="col-md-6 col-sm-6 col-xs-12">					   		
								   		<h2 class="checkout-title">USPS Service								   		</h2>
							   		  <div class="xs-margin"></div>
								   		<div class="input-group sm-margin">
											 <select name="select-service" size=5 style="color:#000000;">
                                             	<option id="pexpress" value="PRIORITY MAIL EXPRESS">Priority Express Overnight (Lexington Only) - ~$16.95</option>
                                                <option id="pstandard" value="PRIORITY">Priority Mail 2-day - $5.80</option>
                                                 <?php
														$weight = explode(';', $cart->getCartWeight());
														$weightlbs = $weight[0];
														$weightoz = $weight[1];
														if($weightlbs == 0 && $weightoz <= 13) {
															echo('<option id="firstclass" value="FIRST CLASS">First Class Mail - ~$2.30</option>');	
														}
												?>
                                             </select>
										 
										</div><!-- End .input-group -->
								   		
									<div class="md-margin"></div>
								   	
								   	</div><!-- End .col-md-6 -->
								   
								   	<div class="col-md-6 col-sm-6 col-xs-12">					   		
								   		<h2 class="checkout-title">Packaging Option</h2>
								   		<p>By default, all orders are shipped in branded packaging that displays the VapeLex.com logo. By checking the box below, however, your order will be shipped in discreetly labelled packaging in which the contents are not identifiable from the outside.</p>
								   		<div class="xs-margin"></div>
								   		
										<div class="input-group custom-checkbox sm-margin">
											 <input name="discreet" type="checkbox"> <span class="checbox-container">
											 	<i class="fa fa-check"></i>
											 </span>
											 I would like to use discreet packaging and labeling.
										 
										</div><!-- End .input-group -->
								   	</div><!-- End .col-md-6 -->
								   	
								   </div><!-- End.row -->
								  </div><!-- End .panel-body -->
								</div><!-- End .panel-collapse -->
							  
							  </div><!-- End .panel -->
        					
        					<div class="panel">
								<div class="accordion-header">
									<div class="accordion-title">Step 5: <span>Confirm Order</span></div><!-- End .accordion-title -->
									<a class="accordion-btn opened"  data-toggle="collapse" data-target="#confirm"></a>
								</div><!-- End .accordion-header -->
								
								<div id="confirm" class="collapse in">
								  <div class="panel-body">
							  
								<div class="table-responsive">
									<table class="table checkout-table">
                                    <thead>
        							<tr>
										<th class="table-title">Product Name</th>
										<th class="table-title">Details</th>
										<th class="table-title">Price</th>
										<th class="table-title">Quantity</th>
										<th class="table-title">SubTotal</th>
        							</tr>
        						</thead>
								<tbody>
                                
                                	<?php 
									$cookie_data = $cart->getCartItems();
										if(empty($cookie_data)) {
											echo('<tr><td><h4>Cart is currently empty.</h4></td></tr>');	
										} else {
										foreach ($cookie_data as $cart_item) {
											
										$ship = 0.00;
										
										$item_attr = $cart->getItemData($cart_item[0]);
										$price = number_format($cart->getItemPrice($cart_item[0]), 2);
										$pdet = $cart_item[3];
										$strengthsize = str_replace(';', ' ', $cart_item[3]);
										$id = $cart_item[0] . str_replace(';', 'a', $pdet);
									
											echo('
											<tr id="cart_item_'. $id .'">
                                   
											<td class="item-name-col">
											<figure style="overflow:hidden;">
												<a href="'. $item_attr['href'] .'"><img src="images/products/'. $item_attr['image'] .'"></a>
											</figure>
											<header class="item-name"><a href="'. $item_attr['href'] .'">'. $item_attr['name'] .'</a></header>
	
										</td>
										<td class="item-code">'. $strengthsize .'</td>
										<td class="item-price-col"><span class="item-price-special">$'. $price .'</span></td>
										<td>
												<span id="quantity'. $id .'" name="quantity'. $id .'">'. $cart_item[1] .'</span>
										</td>
										<td class="item-total-col"><span id="subtotal'. $id .'" class="item-price-special">$'. number_format($price * $cart_item[1], 2) .'</span>
										<a href="javascript:delete_cart_item(\''. $id .'\');" class="close-button"></a>
										</td>
									</tr>
											
											');
												
										}
										
									}
									?>
								</tbody>
								 </table>
								
								</div><!-- End .table-reponsive -->
								  <div class="lg-margin"></div><!-- space -->
                                  <div align="right"><?php echo recaptcha_get_html($publickey); ?></div>
								  <div class="text-right">
                                  <input type="hidden" name="coupon" value="<?php echo($coupon); ?>" />
                                  
								  	<input type="submit" class="btn btn-custom-2" value="CONFIRM ORDER">
								  </div>
								  </div><!-- End .panel-body -->
								</div><!-- End .panel-collapse -->
							  
						  	</div><!-- End .panel -->
        				</div><!-- End .panel-group #checkout -->
        				</form>
                        <form id="lform" action="login.php" method="post">
                        	<input type="hidden" id="femail" name="email" value="">
                            <input type="hidden" id="fpassword" name="password" value="">
                            <input type="hidden" name="ref" value="checkout">
                        </form>
        				<div class="xlg-margin"></div><!-- space -->
        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
<?php page_end(); } ?>