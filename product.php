<?php
	require_once("libs/base.php");
	if(isset($_GET['id'])) {
		$item_details = $cart->getItemData($_GET['id']);
		global $item_details;
		$discount_price = number_format($cart->getItemPrice($_GET['id']), 2);
		
		if(isset($_POST['uid']) && isset($_POST['rating'])) {
			$cart->addRating($_GET['id'], $_POST['uid'], $_POST['rating']);
		}
		$onloadscript = 
		"function goToReview() {
			$('#revlink').trigger('click');
			$('html, body').animate({ scrollTop: $('#review').offset().top }, 1000);	
		}
		if(window.location.hash) {
			$(window).bind('load', function() {
   				goToReview();
			});
		}
		";
		$meta_description=strip_tags($item_details['description']);
		page_begin($item_details['name'], trim(preg_replace('/\s+/', ' ', $meta_description)));
	}
?>
        	<div id="breadcrumb-container">
        		<div class="container">
					<ul class="breadcrumb">
						<li><a href="index.php">Home</a></li>
						<li class="active">Product</li>
					</ul>
        		</div>
        	</div>
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
        				
        				<div class="row">
        				
        				<div class="col-md-6 col-sm-12 col-xs-12 product-viewer clearfix">

        					<div id="product-image-carousel-container">
        						<ul id="product-carousel" class="celastislide-list">
	        						<li class="active-slide"><a data-rel='prettyPhoto' href="images/products/<?php global $item_details; echo($item_details['image']);  ?>" ><img src="images/products/<?php global $item_details; echo($item_details['image']);  ?>" alt=""></a></li>
                                    <?php if($item_details['image_list'] != "") {
									$imagelist = explode(';', $item_details['image_list']);
									foreach ($imagelist as $image) {
										echo('<li><a data-rel=\'prettyPhoto\' href="images/products/'.$image.'" ><img src="images/products/thumbnails/'.$image.'" alt=""></a></li>');
									}
								}?>

        					</ul><!-- End product-carousel -->
        					</div>

        					<div id="product-image-container">
        						<figure><img src="images/products/big-<?php global $item_details; echo($item_details['image']);  ?>" alt="" id="product-image" data-big="images/products/big-<?php global $item_details; echo($item_details['image']);  ?>">
        							<figcaption class="item-price-container">
										<span class="item-price">$<?php if($item_details['discount'] == 0) { echo($item_details['price']); } else { echo($discount_price); } ?></span>
									</figcaption>
        						</figure>
        					</div><!-- product-image-container -->        				 
        				</div><!-- End .col-md-6 -->

        				<div class="col-md-6 col-sm-12 col-xs-12 product">
                        <div class="lg-margin visible-sm visible-xs"></div><!-- Space -->
        					<h1 class="product-name"><?php echo($item_details['name']); ?></h1>
        					<div class="ratings-container">
								<div class="ratings separator">
									<div class="ratings-result" data-result="<?php echo($cart->getRatingAverage($_GET['id'])); ?>"></div>
								</div><!-- End .ratings -->
								<span class="ratings-amount separator">
									<?php echo($cart->countItemRatings($_GET['id'])); ?> Rating(s)
								</span>
								<span class="separator">|</span>
								<a href="javascript:goToReview();" class="rate-this">Add Your Rating</a>
							</div><!-- End .rating-container -->
        				<ul class="product-list">
        					<li><span style="color:#fff">Availability:</span><?php if($item_details['stock'] > 0) echo("<span style=\"color:#fff\">In Stock</span>"); else echo ("Out of Stock");?> 					<?php 
							$q = db_query("SELECT * FROM users WHERE uid='%s'", $_SESSION['uid']);
							$result = mysql_fetch_array($q);
							if(date('m', strtotime($result['lastsample'])) != date('m')) $valid = true;
							else if($result['lastsample'] == "0000-00-00") $valid = true;
							if(isset($_SESSION['logged_in']) && $item_details['category'] == "liquid" && $valid) { echo('  |  <a onClick="if(confirm(\'By clicking OK, you confirm that you would like to receive a 5mL sample of this flavor e-liquid to the address listed on your account. There is a one sample per month limit.\')) { window.location.href = \'http://vapelex.com/req_sample.php?id='.$_GET['id'].'\'; }" href="javascript:void(0);">Request a sample</a>'); } ?></li>
        				</ul>
                        <?php if($item_details['category'] == "liquid") { ?>
        				<hr>
                        <div class="product-color-filter-container">
                            <span style="color:#fff">Strength:</span>
                            <div class="xs-margin"></div>
                            <ul class="filter-size-list clearfix">
                            	<li id="strength0"><a href="javascript:void(0)" onclick="selectStrength(0);">0mg</a></li>
                                <li id="strength6"><a href="javascript:void(0)" onclick="selectStrength(6);">6mg</a></li>
                                <li id="strength12" class="active"><a href="javascript:void(0)" onclick="selectStrength(12);">12mg</a></li>
                                <li id="strength24"><a href="javascript:void(0)" onclick="selectStrength(24);">24mg</a></li>
                            </ul>
                        </div><!-- End .product-size-filter-container-->
                       <div class="product-size-filter-container">
                            <span style="color:#fff">Select Size:</span>
                            <div class="xs-margin"></div>
                            <ul class="filter-size-list clearfix">
                                <li id="size15"><a href="javascript:void(0)" onclick="selectSize(15);">15mL</a></li>
                                <li id="size30" class="active"><a href="javascript:void(0)" onclick="selectSize(30);">30mL</a></li>
                            </ul>
                        </div><!-- End .product-size-filter-container-->
                        <hr>
                        <?php } else if ($item_details['options'] != "") {
							echo("<hr>");
							$optionsarr = explode(';', $item_details['options']);
							echo("<script>
									function selectOpt(option) {\n");
							foreach($optionsarr as $opt) {
									$option_proper = str_replace('.', '_', $opt);
									echo("$('#".$option_proper."').removeClass('active');\n");
							}
							echo("$('#' + option).addClass('active');
								  $('#form_". $item_details['option_title']."').val(option);
									}
									</script>");
							echo('<strong><div class="product-color-filter-container">
                            		<span>'.$item_details['option_title'].':</span></strong>
									<div class="xs-margin"></div>
									<ul class="filter-size-list clearfix">');
							
							foreach($optionsarr as $option) {
									$option_proper = str_replace('.', '_', $option);
									echo('<li id="'.$option_proper.'"><a onClick="selectOpt(\''.$option_proper.'\');" href="javascript:void(0)">'.$option.'</a></li>');
							}
							echo("<br />
							<hr>");
							
						} else {
							echo("<hr>");
							if(strlen($item_details['description']) > 350) {
								/*$cut = substr($item_details['description'],0, 500);
								//Make sure it is cut at a space and not the middle of a word:
								echo(substr($cut, 0, strpos($cut, ' ')) . "<a href=\"#description\">More</a>");*/
								echo(html_cut($item_details['description'], 350) . "... <a href=\"#description\">More</a>");
							} else echo($item_details['description']);
							echo("<hr>");
						 //echo ('<div class="xlg-margin2x"></div><!-- space -->
                            //<div class="xlg-margin2x"></div><!-- space -->');
						} 
						
						?>
							<div class="product-add clearfix">
							

								</div>
                                
                                <form method="POST" action="cart.php?cart=<?php echo($item_details['pid']); ?>">
                                <?php if($item_details['category'] == "liquid") { ?>
                         		<input id="sstrength" name="sstrength" type="hidden" value="12mg" />
                                <input id="ssize" name="ssize" type="hidden" value="30ml" />
								<?php } else if ($item_details['options'] != "") {
									
									echo('<input id="form_'.$item_details['option_title'].'" name="form_'.$item_details['option_title'].'" type="hidden" value="');
									if(isset($optionsarr)) echo($optionsarr[0]);
									echo('" required />'); 
																		
								} ?>                                
								<input <?php if($item_details['stock'] == 0) echo('disabled'); ?> type="submit" class="btn btn-custom-2" value="ADD TO CART"/>
                                </form>
                                
							</div><!-- .product-add -->
        					
        					
        				</div><!-- End .row -->
                       
        				
        				<div class="row">
              
        					<div class="col-md-9 col-sm-12 col-xs-12">
        						<hr />
        						<div class="tab-container left product-detail-tab clearfix">
        						  <ul class="nav-tabs">
								    <li class="active"><a href="#description" data-toggle="tab">Description</a></li>
								    <li><a id="revlink" href="#review" data-toggle="tab">Review</a></li>
									</ul>
        								<div class="tab-content clearfix">
        																
							  				<div class="tab-pane active" id="description">
												<?php echo($item_details['description']); ?>
        									</div><!-- End .tab-pane -->
        									
        									<div class="tab-pane" id="review">
        										<?php 
												$ratings = $cart->getItemRatings($_GET['id']);
												if($_SESSION['logged_in']) {
												//Show rating box
												$q = db_query("SELECT * FROM ratings WHERE uid='%s' AND pid='%s' ORDER by pdate LIMIT 15", $_SESSION['uid'], $_GET['id']);
												if(mysql_num_rows($q) == 0) {
												echo('
													<p>
                                                	<div class="rateit" id="rateit1"></div>
													<button class="btn btn-custom-2" onClick="fill_rating_form()">Rate</button>
													<form id="rateform" method="post" action="product.php?id='.$_GET['id'].'">
													<input type="hidden" id="rating" name="rating" value="" />
													<input type="hidden" id="uid" name="uid" value="'.$_SESSION['uid'].'" />
													</form>
                                                    
													</p>
													<hr>');
												} else { 
													echo('<p>You have already submitted a rating for this item.</p><hr>');
												}
                       
												} else {
													echo('<p>Please <a href="login.php">sign in</a> or <a href="register-account.php">register</a> to rate this item.</p><hr>');
												}
													
													if($ratings != false) { // If ratings exist
														foreach($ratings as $rating) {
															echo('<span style="font-weight:bold">'.date("d-m-Y", strtotime($rating['pdate'])).'</span>');
															echo('<p><div class="ratings-container">
        											<div class="ratings">
        												<div class="ratings-result" data-result="'. $rating['rating'] .'"></div>
        											</div><!-- End .ratings -->
        											<span class="ratings-amount">
        											</span>
        										</div><!-- End .rating-container --></p> <hr>');
														}
													} else {
														echo('<p>No new ratings</p>');	
													}
												?>
        										
        									</div><!-- End .tab-pane -->
        									
        					
   								  </div><!-- End .tab-content -->
        						</div><!-- End .tab-container -->
        						<div class="lg-margin visible-xs"></div>
        					</div><!-- End .col-md-9 -->
        					<div class="lg-margin2x visible-sm visible-xs"></div><!-- Space --><!-- End .col-md-4 -->
        				</div><!-- End .row -->
        				<div class="lg-margin2x"></div><!-- Space --><!-- End .purchased-items-container -->

        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
<?php page_end(); ?>