<?php
require_once("libs/base.php");
require_once("libs/usps.php");
page_begin("My Cart");
?>
        	<div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Shopping Cart</li>
					</ul>
        		</div>
        	</div>
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<header class="content-title">
							<h1 class="title">Shopping Cart</h1>
							<p class="title-desc">In celebration of our opening week, enjoy 20% off all orders!</p>
						</header>
        				<div class="xs-margin"></div><!-- space -->
        				<div class="row">
        					
        					<div class="col-md-12 table-responsive">
								<form method="post" action="checkout.php">
        						<table class="table cart-table">
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
											echo('<tr><td><h4 style="color:#000">Cart is currently empty.</td></tr>');	
										} else {
										foreach ($cookie_data as $cart_item) {
											
										$ship = 0.00;
										
										$item_attr = $cart->getItemData($cart_item[0]);
										$strengthsize = str_replace(';', ' ', $cart_item[3]);
										$price = $cart->getItemPrice($cart_item[0], $cart_item[3]);
									
											echo('
											<tr id="cart_item_'. $item_attr['pid'] .'">
                                   
											<td class="item-name-col">
											<figure style="overflow:hidden;">
												<a href="'. $item_attr['href'] .'"><img src="'. $item_attr['image'] .'"></a>
											</figure>
											<header class="item-name"><a href="'. $item_attr['href'] .'">'. $item_attr['name'] .'</a></header>
	
										</td>
										<td class="item-code">'. $strengthsize .'</td>
										<td class="item-price-col"><span class="item-price-special">$'. $price .'</span></td>
										<td>
											<div class="custom-quantity-input">
												<input type="text" id="quantity'. $cart_item[0] .'" name="quantity'. $cart_item[0] .'" value="'. $cart_item[1] .'">
												<a href="javascript:void(0)" onclick="document.getElementById(\'quantity'. $cart_item[0] .'\').value = parseInt(document.getElementById(\'quantity'. $cart_item[0] .'\').value, 10) + 1" class="quantity-btn quantity-input-up"><i class="fa fa-angle-up"></i></a>
												<a href="javascript:void(0)" onclick="if(document.getElementById(\'quantity'. $cart_item[0] .'\').value != 0) document.getElementById(\'quantity'. $cart_item[0] .'\').value = parseInt(document.getElementById(\'quantity'. $cart_item[0] .'\').value, 10) - 1" class="quantity-btn quantity-input-down"><i class="fa fa-angle-down"></i></a>
											</div>
										</td>
										<td class="item-total-col"><span id="subtotal'. $cart_item[0] .'" class="item-price-special">$'. number_format($price * $cart_item[1], 2) .'</span>
										<a href="javascript:delete_cart_item('. $cart_item[0] .');" class="close-button"></a>
										</td>
									</tr>
											
											');
												
										}
										
									}
									?>
								</tbody>
							  </table>
        						
        					</div><!-- End .col-md-12 -->
        					
        				</div><!-- End .row -->
                        <div class="lg-margin visible-sm visible-xs"></div><!-- space -->
                        <div style="float:right; padding-top:30px; padding-bottom:30px;">
                            <button type="button" onClick="update_cart();" class="btn btn-custom-2" value="UPDATE CART">UPDATE CART</button>
                            <div class="lg-margin visible-sm visible-xs"></div><!-- space -->
                         </div>
                         
        				<div class="lg-margin"></div><!-- End .space -->
        				<div class="lg-margin visible-sm visible-xs"></div><!-- space -->
        				<div class="row">
        					<div class="col-md-8 col-sm-12 col-xs-12">
        						
        						<div class="tab-container left clearfix">
        								<ul class="nav-tabs">
										  <li class="active"><a href="#shipping" data-toggle="tab">Shipping &amp; Taxes</a></li>
										  <li><a href="#discount" data-toggle="tab">Coupon Code</a></li>									
										  
										</ul>
        								<div class="tab-content clearfix">
        									<div class="tab-pane active" id="shipping">
        			
        											<p>Enter your destination to get a shipping estimate.</p>
                                                    <div class="xs-margin"></div>
													<div class="form-group">
														
														<div class="input-container">
                                                           
                                                        </div><!-- End .select-container -->
													</div><!-- End .form-group -->
													<div class="sm-margin"></div>
													<div class="form-group">
                                                        <label for="select-state" class="control-label">State&#42;</label>
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
													<label for="zip" class="control-label"  >Zip Code&#42;</label>
													<div class="input-container">
                                                        <input name="zip" id="zip" type="text" class="form-control" placeholder="40503">
                                                    </div>
												</div><!-- End .form-group -->
        										<div class="sm-margin"></div>
        										<p class="text-right">
        											<button type="button" id="estbutton" class="btn btn-custom-2" onclick="getShipEstimate()">GET ESTIMATE</button>
        										</p>
        										
        									</div><!-- End .tab-pane -->
        									
        									<div class="tab-pane" id="discount">
        										<p>Enter your discount coupon code here.</p>
        											<div class="input-group">
														<input name="ccode" id="ccode" type="text" class="form-control" placeholder="Coupon code">
														
													</div><!-- End .input-group -->	
        										<button type="button" id="coupbutton" onClick="checkCoupon();" class="btn btn-custom-2">APPLY COUPON</button>
        									</div><!-- End .tab-pane -->
        									

        									
        								</div><!-- End .tab-content -->
        						</div><!-- End .tab-container -->
        						
        					</div><!-- End .col-md-8 -->
							
        					<div class="col-md-4 col-sm-12 col-xs-12">
        						
        						<table class="table total-table">
        							<tbody>
        								<tr>
        									<td class="total-table-title">Subtotal:</td>
        									<td id="psubtotal">$<?php global $cart_total; printMoney($cart_total, 2); ?></td>
        								</tr>
        								<tr>
        									<td class="total-table-title">Shipping:</td>
        									<td id="shipest">$0.00</td>
        								</tr>
        								<tr>
        									<td class="total-table-title">TAX (0%):</td>
        									<td>$0.00</td>
        								</tr>
        							</tbody>
        							<tfoot>
        								<tr>
											<td>Total:</td>
											<td id="ptotal">$<?php global $cart_total; printMoney($cart_total + $ship);?></td>
        								</tr>
        							</tfoot>
        						</table>
        						<div class="md-margin"></div><!-- End .space -->
        						<input type="submit" name="submit" class="btn btn-custom-2" value="CHECKOUT"  />
                                </form>
        					</div><!-- End .col-md-4 -->
        				</div><!-- End .row -->
        				
        				
        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
<?php page_end(); ?>