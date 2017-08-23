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
	}
	$udetails = explode('|', $_SESSION['address']);
	$onloadfunc = "autoFill('".$udetails[0]."','".$udetails[1]."','".$_SESSION['email']."','".$udetails[2]."','".$udetails[3]."','".$udetails[4]."','".$udetails[5]."','".$udetails[6]."');";
	ob_end_flush();
	page_begin("Account Details");
?>
<div class="container">
<div class="xs-margin"></div>
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
                        	
							<h1 class="title">Account Details</h1>
                            <div style="float:right; padding-bottom:20px;">
                            <button onClick="parent.location='http://<?php echo($_SERVER['HTTP_HOST']); ?>/login.php?logout=1'" class="btn btn-custom-2">LOGOUT</button>
                            </div>
                            <div class="xs-margin"></div>
                            <div class="xs-margin"></div>
                            <div class="xs-margin"></div>
                            
                        </header>
                        <div class="xs-margin"></div>
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
                                                            <select name="select-state" class="form-control" id="select-state">
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
	<option selected="selected" value="KY">Kentucky</option>
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
   									    <form action="#">
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
        										<input type="submit" class="btn btn-custom-2" value="CHANGE PASSWORD">
        										</form>
                                               
                                                
        									</div><!-- End .tab-pane -->
        									

        									
        								</div><!-- End .tab-content -->
        						</div><!-- End .tab-container -->
                     </div><!-- End col-md-12 -->
                </div><!-- End row -->

<?php 
page_end(); }  ?>