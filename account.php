<?php
ob_start();
require_once("libs/base.php");
if(!$_SESSION['logged_in']) {
	$USER = array();
	header("Location: /login.php");
	ob_end_flush();
	exit();
} else {
	if(isset($_POST['submitpinfo'])) {
		if(isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['address1']) && isset($_POST['city']) && isset($_POST['select-state']) && isset($_POST['zipcode'])) {
			if(!(isset($_POST['address2']))) $address2 = ""; else $address2 = $_POST['address2'];
			$address = $_POST['fname'].'|'.$_POST['lname'].'|'.$_POST['address1'].'|'.$address2.'|'.$_POST['city'].'|'.$_POST['select-state'].'|'.$_POST['zipcode'];
			db_query("UPDATE users SET address='%s' WHERE uid='%s'", $address, $_SESSION['uid']); 
			$_SESSION['address'] = $address;
		}
	} else if(isset($_POST['setpass'])) {
		if(isset($_POST['opassword']) && isset($_POST['npassword']) && isset($_POST['npassword2'])) {
			
			$oldpass = $_POST['opassword'];
			$newpass = $_POST['npassword'];
			$newpass2 = $_POST['npassword2'];
			
			if(strlen($newpass) < 5) {
				$onloadscript = "alert('Error: Password must be at least 5 characters.');";
			}
			else if($newpass == $newpass2) {
				$p = db_query("SELECT password FROM users WHERE email = '%s'", $_SESSION['email']);
		        $row = mysql_fetch_array($p);
				$response = $row['password'];
				if($response ==  PwdHash($oldpass,substr($response,0,9))) {
					db_query("UPDATE users SET password='%s' WHERE email='%s'", PwdHash($newpass), $_SESSION['email']);
					$onloadscript = "alert('Your password was changed successfully.');";
				} else {
					$onloadscript = "alert('Error: Current password was incorrect. Please try again.');";
				}
					
			} else {
				$onloadscript = "alert('Error: Passwords did not match. Please try again.');";	
			}
				
		} else {
			$onloadscript = "alert('Error: All fields must be completed.');";	
		}
	}
	$udetails = explode('|', $_SESSION['address']);
	ob_end_flush();
	page_begin("Account Details");
?>
<div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Account</li>
					</ul>
        		</div>
        	</div>
<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
                        	<table style="table-layout:fixed">
							<td style="white-space:nowrap"><h1 class="title">Account Details</h1></td>
                            	<td style="width:90%"></td>
                            <td><button onClick="parent.location='http://<?php echo($_SERVER['HTTP_HOST']); ?>/login.php?logout=1'" class="btn btn-custom-2">LOGOUT</button></td>
                            </table>
                        </header>
                        
                        <div class="tab-container left clearfix">
        								<ul class="nav-tabs">
                                          <li class="active"><a href="#orders" data-toggle="tab">Recent Orders</a></li>
										  <li><a href="#personalinfo" data-toggle="tab">Personal Information</a></li>
										  <li><a href="#account" data-toggle="tab">Account Settings</a></li>									
										  
										</ul>
                                        
        								<div class="tab-content clearfix">
                                        	<div class="tab-pane active" id="orders">
                                            <div style="color:#000000">
   									    		<?php 
													$q = db_query("SELECT * FROM transactions WHERE user_id='%s' ORDER BY id DESC LIMIT 7", $_SESSION['uid']);
													if(mysql_num_rows($q) > 0) {
														while($result = mysql_fetch_assoc($q)) {
															
															$cart_block = '<div class=\"table-responsive\"><table style="border-collapse: separate; border-spacing: 10px;"><tr><th>Item</th><th>Details</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr>';
															$cart_data = json_decode(stripslashes(urldecode($result['cart_data'])));
															foreach($cart_data as $item) {
																$idata = $cart->getItemData($item[0]);
																$details = str_replace(';', ' ', $item[3]);
																$cart_block .= '<tr><td>'.$idata['name'].'</td><td>'.$details.'</td><td>'.$cart->getItemPrice($item[0], $item[3]).'</td><td>'.$item[1].'</td><td>'.$cart->getItemPrice($item[0], $item[3]) * $item[1].'</td></tr>';	
															}
															$cart_block .= '<tr><td></td><td></td><td></td><td>Shipping:</td><td>$'.number_format($result['ship_total'], 2).'</td></tr><tr><td></td><td></td><td></td><td>Discount:</td><td>'.checkCoupon($result['coupon']).'%</td></tr><tr><td></td><td></td><td></td><td>Total:</td><td>$'.number_format($result['cart_total'], 2).'</td></tr></table></div>';
													 		echo('<div class="panel"><div class="accordion-header"><div class="accordion-title">Order #'.$result['order_id'].'<span> - '.date('d/m/Y', strtotime($result['odate'])).'</span></div><a class="accordion-btn"  data-toggle="collapse" data-target="#'.$result['order_id'].'"></a></div><div id="'.$result['order_id'].'" class="collapse"><div class="panel-body">
															
																	<p>'.$cart_block);
															if($result['tracking_number'] != "") {
															
													 			echo('<b>Tracking Number: </b><a href="https://tools.usps.com/go/TrackConfirmAction.action?tRef=fullpage&tLc=1&text28777=&tLabels='.$result['tracking_number'].'">'.$result['tracking_number'].'</a>');
															} else {
																echo("<b>Tracking Number: </b> Not yet shipped");	
															}
													 		echo('</p></div><!-- End .panel-body -->
								</div><!-- End .panel-collapse --></div>');
														}
													} else {
														echo("<h2>You have no orders on file yet.</h2>");
													}	
												?>
                                                </div>
        									</div><!-- End .tab-pane -->
        									<div class="tab-pane" id="personalinfo">
        										
        										<form action="account.php" method="post" id="pinfo-form">
        											<p>&nbsp;</p>
                                                    <div class="xs-margin"></div>
                                                            										<div class="form-group">
													<label for="fname" class="control-label"  >First Name&#42;</label>
													<div class="input-container">
                                                        <input id="fname" name="fname" type="text" required class="form-control" placeholder="" value="<?php echo($udetails[0]); ?>" >
                                                    </div>
												</div><!-- End .form-group -->
													<div class="sm-margin"></div>
                                                    <div class="form-group">
													<label for="lname" class="control-label"  >Last Name&#42;</label>
													<div class="input-container">
                                                        <input id="lname" name="lname" type="text" required class="form-control" placeholder="" value="<?php echo($udetails[1]); ?>">
                                                    </div>
												</div><!-- End .form-group -->
													<div class="sm-margin"></div>
                                                    <div class="form-group">
													<label for="address1" class="control-label"  >Address 1&#42;</label>
													<div class="input-container">
                                                        <input id="a1" name="address1" type="text" required class="form-control" placeholder="" value="<?php echo($udetails[2]); ?>">
                                                    </div>
												</div><!-- End .form-group -->
													<div class="sm-margin"></div>
                                                    <div class="form-group">
													<label for="address2" class="control-label"  >Address 2</label>
													<div class="input-container">
                                                      <input id="a2" name="address2" type="text" class="form-control" placeholder="" value="<?php echo($udetails[3]); ?>">
                                                    </div>
												</div><!-- End .form-group -->
													<div class="sm-margin"></div>
                                                                                                        <div class="form-group">
													<label for="city" class="control-label"  >City&#42;</label>
													<div class="input-container">
                                                        <input id="city" name="city" type="text" required class="form-control" placeholder="" value="<?php echo($udetails[4]); ?>">
                                                    </div>
												</div><!-- End .form-group -->
													<div class="sm-margin"></div>
													<div class="form-group">
                                                        <label for="select-state" class="control-label">Region&amp;State&#42;</label>
                                                        <div class="input-container">
                                                            <?php
    							function showOptionsDrop($array, $active, $echo=true){
									
       								$string = '';

        							foreach($array as $k => $v){
           								$s = ($active == $k)? ' selected="selected"' : '';
           								$string .= '<option value="'.$k.'"'.$s.'>'.$v.'</option>'."\n";
        							}

        							if($echo)   echo $string;
        							else        return $string;
   	 							}
								$states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
?>
														<select class="form-control" name="select-state">
    														<?php showOptionsDrop($states_arr, $udetails[5], true); ?>
														</select>
                                                        </div><!-- End .select-container -->
                                                    </div><!-- End .form-group -->
        										  <div class="sm-margin"></div>
        										<div class="form-group">
													<label for="select-country" class="control-label"  >Postal Code&#42;</label>
													<div class="input-container">
                                                        <input type="text" id="zipcode" name="zipcode" required class="form-control" placeholder="" value="<?php echo($udetails[6]); ?>">
                                                    </div>
												</div><!-- End .form-group -->
        										<div class="sm-margin"></div>
        										<p class="text-right">
        											<input type="submit" name="submitpinfo" class="btn btn-custom-2" value="SUBMIT CHANGES">
        										</p>
        										</form>
        										
        									</div><!-- End .tab-pane -->
        									
   									  <div class="tab-pane" id="account">
   									    <form action="account.php" method="post">
   											<div class="input-group">
										  <label for="opassword" class="control-label">Old Password</label>
													<div class="input-container">
                                                      <input name="opassword" id="opassword" type="password" required class="form-control" placeholder="">
													</div>
                                              <label for="npassword" class="control-label">New Password</label>
													<div class="input-container">
                                                      <input name="npassword" id="npassword" type="password" required class="form-control" placeholder="">
													</div>
											  	 <label for="npassword2" class="control-label">Confirm New Password</label>
													<div class="input-container">
                                                      <input name="npassword2" id="npassword2" type="password" required class="form-control" placeholder="">
													</div>
											  </div><!-- End .form-group -->		
        										<input name="setpass" type="submit" class="btn btn-custom-2" value="CHANGE PASSWORD">
        										</form>
                                               
                                                
        									</div><!-- End .tab-pane -->
        									

        									
        								</div><!-- End .tab-content -->
        						</div><!-- End .tab-container -->
                     </div><!-- End col-md-12 -->
                </div><!-- End row -->

<?php 
page_end(); }  ?>