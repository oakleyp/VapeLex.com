<?php
ob_start();
require_once("libs/base.php");
/*error_reporting(E_ALL);
	ini_set('display_errors', '1');*/

ob_end_flush();
page_begin("Accessories");
?>
        	
        	
        	<br />
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
        				
        				<div class="row">
        					
        					<div class="col-md-9 col-sm-8 col-xs-12 main-content">
        						<header class="content-title">
									<h2 class="title">Batteries &amp; Atomizers</h2>
								</header>
        						<ul id="products-tabs-list" class="tab-style-1 clearfix">
        							<li class="active"><a href="#all" data-toggle="tab">All</a></li>
        							<li><a href="#latest" data-toggle="tab">Latest</a></li>
        							<li><a href="#bestsellers" data-toggle="tab">Top Rated</a></li>
        							<li><a href="#special" data-toggle="tab">Specials</a></li>
        						</ul>
        						<div id="products-tabs-content" class="row tab-content">
        							<div class="tab-pane active" id="all">
                                <?php 
								$result = db_query("SELECT * FROM items WHERE category='accessories' ORDER BY id");
								$rname = "";
								$ccount = 0;
								$tcount = 0;
								$table = array();
								$trow = array();
							 	while($r = mysql_fetch_array($result)) {
									$table[$ccount] = $r;
									$ccount++;
								}
								
							

								foreach ($table as $product) {
									$ratingaverage = $cart->getRatingAverage($product['pid']);
									$ratingcount = $cart->countItemRatings($product['pid']);
									if($product['discount'] != 0) {
										$discount_line = "<span class=\"discount-rect\">-" .$product['discount'] ."%</span>";
										$old_price = "<span class=\"old-price\">$". $product['price'] ."</span>";
										$new_price = number_format($product['price'] - ($product['price'] * ($product['discount'] / 100)), 2);
									} else {
										$discount_line = "";
										$old_price = "";
										$new_price = $product['price'];
									}
									echo('<div class="col-md-4 col-sm-6 col-xs-12">
        								<div class="item">
												<div class="item-image-container">
													<figure>
													<a href="product.php?id='. $product['pid'] .'">
														<img src="'. $product['image'] .'" alt="" class="item-image">
														<img src="'. $product['image_hover'] .'" alt="" class="item-image-hover">
													</a>
													</figure>
													
													
												</div>
												<!-- End .item-image -->
											'.$discount_line.'
        									<div class="item-meta-container">
        										<div class="ratings-container">
        											<div class="ratings">
        												<div class="ratings-result" data-result="'. $ratingaverage .'"></div>
        											</div><!-- End .ratings -->
        											<span class="ratings-amount">
        												'. $ratingcount .' Users
        											</span>
        										</div><!-- End .rating-container -->
        										<h3 class="item-name"><a href="product.php?id='. $product['pid'] .'">'. $product['name'] .'</a></h3>
        										<div class="item-action">
        											<a href="product.php?id='. $product['pid'] .'" class="item-add-btn">
														<span class="icon-cart-text">Item Details</span>
													</a>
        											<div class="item-action-inner">
        												<a href="product.php?id='.$product['pid'].'#review" class="icon-button icon-like">Favourite</a>
        												<a href="cart.php?cart='.$product['pid'].'" class="icon-button icon-compare">Add to Cart</a>
        											</div><!-- End .item-action-inner -->
        										</div><!-- End .item-action -->
        									</div><!-- End .item-meta-container -->	
        								</div><!-- End .item -->
        								
        								
        							</div><!-- End .col-md-4 -->
									');	
								}
								
								?>
        						
        							
        							</div><!-- End .tab-pane -->
        							
        							<div class="tab-pane" id="latest">
        								
        							</div><!-- End .tab-pane -->
        							
        							<div class="tab-pane" id="featured">
									
        							</div><!-- End .tab-pane -->
        							<div class="tab-pane" id="bestsellers">
        				
        							</div><!-- End .tab-pane -->
        							<div class="tab-pane" id="special">
                                	<?php
										$result2 = db_query("SELECT * FROM items WHERE category='accessories' AND discount!='0' ORDER BY id");
										$rname = "";
								$ccount = 0;
								$tcount = 0;
								$table = array();
								$trow = array();
							 	while($r = mysql_fetch_array($result2)) {
									$table[$ccount] = $r;
									$ccount++;
								}
								
							

								foreach ($table as $product) {
									$ratingaverage = $cart->getRatingAverage($product['pid']);
									$ratingcount = $cart->countItemRatings($product['pid']);
									if($product['discount'] != 0) {
										$discount_line = "<span class=\"discount-rect\">-" .$product['discount'] ."%</span>";
										$old_price = "<span class=\"old-price\">$". $product['price'] ."</span>";
										$new_price = number_format($product['price'] - ($product['price'] * ($product['discount'] / 100)), 2);
									} else {
										$discount_line = "";
										$old_price = "";
										$new_price = $product['price'];
									}
										echo('<div class="col-md-4 col-sm-6 col-xs-12">
        								<div class="item">
												<div class="item-image-container">
													<figure>
													<a href="product.php?id='. $product['pid'] .'">
														<img src="'. $product['image'] .'" alt="" class="item-image">
														<img src="'. $product['image_hover'] .'" alt="" class="item-image-hover">
													</a>
													</figure>
													
													
												</div>
												<!-- End .item-image -->
											'.$discount_line.'
        									<div class="item-meta-container">
        										<div class="ratings-container">
        											<div class="ratings">
        												<div class="ratings-result" data-result="'. $ratingaverage .'"></div>
        											</div><!-- End .ratings -->
        											<span class="ratings-amount">
        												'. $ratingcount .' Users
        											</span>
        										</div><!-- End .rating-container -->
        										<h3 class="item-name"><a href="product.php?id='. $product['pid'] .'">'. $product['name'] .'</a></h3>
        										<div class="item-action">
        											<a href="product.php?id='. $product['pid'] .'" class="item-add-btn">
														<span class="icon-cart-text">Item Details</span>
													</a>
        											<div class="item-action-inner">
        												<a href="product.php?id='.$product['pid'].'#review" class="icon-button icon-like">Favourite</a>
        												<a href="cart.php?cart='.$product['pid'].'" class="icon-button icon-compare">Add to Cart</a>
        											</div><!-- End .item-action-inner -->
        										</div><!-- End .item-action -->
        									</div><!-- End .item-meta-container -->	
        								</div><!-- End .item -->
        								
        								
        							</div><!-- End .col-md-4 -->
									');	
								}
									?>
        								
        							</div><!-- End .tab-pane -->
        						</div><!-- End #products-tabs-content -->
        						
								<div class="row">
									<div class="col-md-7 col-sm-7 col-xs-12">
                                      
									</div><!-- End .col-md-7 -->
									<div class="col-md-5 col-sm-5 col-xs-12">
									</div><!-- End .col-md-5 -->
								</div><!-- End .row -->
        						

        					</div><!-- End .col-md-9 -->
        					
        					<div class="col-md-3 col-sm-4 col-xs-12 sidebar">
        						
        						<div class="widget latest-posts">
        							<h3>VL News</h3>
        							
        							<div class="latest-posts-slider flexslider sidebarslider">
        								<ul class="latest-posts-list clearfix">
        									<li>
                                                    </figure>
                                                </a>
        										<h4>Grand Opening Sale: 20% Discount on all orders!</h4>
        										<p></p>
        										<div class="latest-posts-meta-container clearfix">
        											<div class="pull-left">
        											</div><!-- End .pull-left -->
        											<div class="pull-right">
        												04.13.13
        											</div><!-- End .pull-right -->
        										</div><!-- End .latest-posts-meta-container -->
        									</li>
                                            <li>
                                            <h4>Flavor of the Week:<br /> LC Special</h4>
                                            <p></p>
        										<div class="latest-posts-meta-container clearfix">
        											<div class="pull-left">
        											</div><!-- End .pull-left -->
        											<div class="pull-right">
        												04.13.13
        											</div><!-- End .pull-right -->
        										</div><!-- End .latest-posts-meta-container -->
        									</li>
        									
        									
        							</div><!-- End .latest-posts-slider -->
        						</div><!-- End .widget -->
        						
        						
        					</div><!-- End .col-md-3 -->
        				</div><!-- End .row -->
        			
        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
<?php page_end(); ?>