<?php
ob_start();
require_once("libs/base.php");
/*error_reporting(E_ALL);
	ini_set('display_errors', '1');*/

ob_end_flush();
page_begin("E-Liquid");
?>
        	
        	
        	<br />
        	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
        				
        				<div class="row">
        					
        					<div class="col-md-9 col-sm-8 col-xs-12 main-content">
        						<header class="content-title">
									<h2 class="title">VapeLex E-Liquid</h2>
									<p class="title-desc">All our e-liquids are hand-crafted in the US, using an 80/20 VG:PG blend for the best taste and vapor production. No need to take our word for it, though: Open an account with us, and you're welcome to a free 5mL sample of any flavor, every month. </p>
								</header>
        						<ul id="products-tabs-list" class="tab-style-1 clearfix">
        							<li class="active"><a href="#all" data-toggle="tab">All</a></li>
        							<li><a href="#latest" data-toggle="tab">Latest</a></li>
        							<li><a href="#bestsellers" data-toggle="tab">Top Rated</a></li>
        							<li><a href="#special" data-toggle="tab">Specials</a></li>
        						</ul>
                                <div class="toolbox-pagination clearfix">

										<ul class="pagination">

											<?php 
											if(isset($_GET['page'])) $page = $_GET['page']; else $page = 1;
											if(isset($_GET['view'])) $view = $_GET['view']; else $view = 9;
											$start_from = ($page-1) * $view;
											$result = db_query("SELECT * FROM items WHERE category='liquid' ORDER BY id LIMIT $start_from, $view");
											//Pagination
					
											$q = db_query("SELECT COUNT(name) FROM items WHERE category='liquid'");  
											$row = mysql_fetch_row($q); 
											$total_records = $row[0]; 
											$total_pages = ceil($total_records / $view); 
											for ($i=1; $i<=$total_pages; $i++) { 
												if($i == $page) {
            										echo "<li class=\"active\"><a href='eliquid.php?page=".$i."'>".$i."</a></li>";
												} else {
													echo "<li><a href='eliquid.php?page=".$i."'>".$i."</a></li>";	
												}
											}; 
											if($page < $total_pages) {
													echo("<li><a href=\"eliquid.php?page=".($page+1)."\"><i class=\"fa fa-angle-right\"></i></a></li>");
											}
											
											?>

										</ul>
										

									</div><!-- End .toolbox-pagination -->

        						<div id="products-tabs-content" class="row tab-content">
        							<div class="tab-pane active" id="all">
                                <?php 
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
                                    <?php
										$topq = db_query("SELECT * FROM items WHERE category='liquid'");
										$ccount = 0;
										$tcount = 0;
										$table = array();
										$trow = array();
							 			while($r = mysql_fetch_array($topq)) {
											$table[$ccount] = $r;
											$ccount++;
										}
										
										$seqarray = array();
										$idlib = array();
										$count = 0;
										foreach ($table as $item) {
											$seqarray[$count] = $cart->getRatingAverage($item['pid']);
											$idlib[$count] = $item['pid']; 
										}
										rsort($seqarray);
										for($i = 0; $i<9; $i++) {
											$id = $idlib[$i];
											$ratingaverage = $cart->getRatingAverage($id);
											$ratingcount = $cart->countItemRatings($id);
											$product = $seqarray[$i];
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
        							<div class="tab-pane" id="special">
                                	<?php
										$result2 = db_query("SELECT * FROM items WHERE category='liquid' AND discount!='0' ORDER BY id");
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
        			
                    <div class="pagination-container clearfix">

        							<div class="pull-right">

										<ul class="pagination">
                                        
                                        	<?php 
											//Pagination
  
											for ($i=1; $i<=$total_pages; $i++) { 
												if($i == $page) {
            										echo "<li class=\"active\"><a href='eliquid.php?page=".$i."'>".$i."</a></li>";
												} else {
													echo "<li><a href='eliquid.php?page=".$i."'>".$i."</a></li>";	
												}
											}; 
											if($page < $total_pages) {
												if($page != $total_pages) {
													echo("<li><a href=\"eliquid.php?page=".($page+1)."\"><i class=\"fa fa-angle-right\"></i></a></li>");
												}
											}
					?>

											

											</ul>

        							</div><!-- End .pull-right -->

        						</div><!-- End pagination-container -->
                                
        			</div><!-- End .col-md-12 -->
        		</div><!-- End .row -->
			</div><!-- End .container -->
        
<?php page_end(); ?>