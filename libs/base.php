<?php
    require_once('class/securesession.class.php');
    require_once('database.php');
	require_once('libs/cartlib.php');
	require_once('libs/phpmailer/PHPMailerAutoload.php');
	
	header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	// error_reporting(E_ALL); ini_set('display_errors', '1'); #Uncomment to show all errors
	
    set_time_limit(60);
    date_default_timezone_set('Europe/Kiev');
    
    ini_set('session.cookie_lifetime', 60*60*12);
    ini_set('session.gc_maxlifetime', 60*60*12+600);
    ini_set('session.name', 'vapelex');
    ini_set('session.use_only_cookies', 1);

    session_start();

    $ss = new SecureSession(); 
    $ss->check_browser = true; 
    $ss->check_ip_blocks = 2; 
    $ss->secure_word = '98í65b4312'; 
    $ss->regenerate_id = true;
	
	
    // assign main session variable
    $USER = &$_SESSION;
	
	//globals
	$cart = new Cart();
	global $cart;
	
	$cart_total = $cart->getCartTotal();
	global $cart_total;
	$cart_total_items = $cart->countCartItems();
	global $cart_total_items;
	$onloadscript = "";
	global $onloadscript;

	if(isset($_GET['cart']) && isset($_POST['sstrength']) && isset($_POST['ssize'])) {
		
		$pdetails = $_POST['sstrength'] . ';' . $_POST['ssize'];
		$price = $cart->getItemPrice($_GET['cart'], $_POST['ssize']);
		$onloadscript = $cart->addToCart($_GET['cart'], $pdetails);
		$cart_total = $cart->getCartTotal();
		
		
	} else if (isset($_GET['cart'])) {
		$attributes = $cart->getItemData($_GET['cart']);
		if($attributes['option_title'] != "") {
			$title = $attributes['option_title'];
			$pdetails = $title . ';' . $_POST['form_' . $title];
			$onloadscript = $cart->addToCart($_GET['cart'], $pdetails);	
		} else {
			$onloadscript = $cart->addToCart($_GET['cart']);
		}
			$cart_total = $cart->getCartTotal();
			
	} else if (isset($_GET['updatecart'])) {
		
		$current_cartitems = json_decode(stripslashes($_COOKIE['vapelex_cart']), true);
		$updated_cartitems = array();
		$count = 0;
		foreach ($current_cartitems as $cart_item) {
			if(!(intval($_POST['quantity'.$cart_item[0]]) == 0)) {
				//Update quantity
				$cart_item[1] = intval($_POST['quantity'.$cart_item]);
				$updated_cartitems[$count] = $cart_item;
			} //Else skip item addition to array
			$count = $count + 1;
		}
		setcookie("vapelex_cart", json_encode($updated_cartitems, true), time()+60*60*24*30);
				
	} else if (isset($_GET['logout'])) {
		 $_SESSION = $USER = array();
    	if (ini_get("session.use_cookies")) {
        	$params = session_get_cookie_params();
        	setcookie(session_name(), '', time() - 42000,
            	$params["path"], $params["domain"],
            	$params["secure"], $params["httponly"]
        	);
    	}
    	session_unset();
    	session_destroy();
		//Reload the page
			echo(" 
			<script language=javascript>
			function redirect(){
 		 	window.location = \"". strtok($_SERVER["REQUEST_URI"],'?') ."\"
			}
			</script>

			<body onload=\"redirect()\">
			");
	}
		
	/*//Synchronize cookie and DB cart items; Update cart total
	if(isset($_COOKIE['vapelex_cart'])) {
		$cookie = $_COOKIE['vapelex_cart'];
		$cookie = stripslashes($cookie);
		$cookie_data = json_decode($cookie, true);
		global $cart_total;
		foreach($cookie_data as $item) {
					$item_attr = $cart->{getItemData($item[0])};
					$iprice = $cart->{getItemPrice($item[0])};
					$cart_total += ($iprice - ($iprice * ($item_attr['discount'] / 100))) * $item[1] ;	
		}
		global $cart_total_items;
		$cart_total_items = count(json_decode(stripslashes($_COOKIE["vapelex_cart"]), true));
		
		if($_SESSION['logged_in']) {
			//Compare DB and cookie items. If more items in DB, add to cookies. If more items in cookies, add to DB 
				//Get sql data
				$result = db_query("SELECT * FROM cart WHERE user_id='". $USER['uid']."'"); 
				
				
				//Get cookie data
				$cookie = $_COOKIE['vapelex_cart'];
				$cookie = stripslashes($cookie);
				$cookie_data = json_decode($cookie, true);
			
				
				$num = mysql_num_rows($result);
				if($num > count($cookie_data)) { // Compare sizes ;)
				//If db contains a greater number of items, add them to cookie data
					$addqueue = array();
					$addcount = 0;
					$newcookie = array();
					$ncc = count($cookie_data); //Counter for new cookie array
					while ($row = mysql_fetch_object($result)) {
						foreach ($cookie_data as $cookie_item) {
								if($row['product_id'] == $cookie_item[0]) {
									break;	
								} else {
									$rowitem_attr = getItemDataById($row['product_id']);
									$addqueue[$addcount] = array(
										'product_id' => $row->product_id,
										'quantity' => $row-quantity,
										'product_details' => $row->product_details,
										'user_id' => $row->user_id,
										'price' => getItemPriceBySize($row->price, $row->product_details)
										);
										
								}
						}
						$addcount++;
					}
					foreach ($addqueue as $additem) {
						$newcookie[$ncc][0] = $additem['product_id'];
						$newcookie[$ncc][1] = $additem['quantity'];
						$newcookie[$ncc][2] = $additem['price'];
						$newcookie[$ncc][3] = $additem['product_details'];
					}
					for($i = 0; $i<(count($cookie_data)-1); $i++) {
						$newcookie[$i] = $cookie_data[$i];	
					}
					
					
				} else if(count($cookie_data) > $num) { //Update sql db
					//Get product info to insert
					foreach($cookie_data as $cookie_item_num) {
						
							db_query("INSERT INTO cart (id, product_id, product_details, user_id, quantity, timestamp, expired) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')", 0, (int)$cookie_item_num[0], $cookie_item_num[3], $USER['uid'], $cookie_item_num[1], 0, 'false');
							
					}

				}
				
				
		}
	} else {
		global $cart_total;
		$cart_total = 0.00;
			
	}*/
	
	
	define('SALT_LENGTH', 9); // salt for password
    // Hosszu nevek roviditese
    function shortit($mit, $mennyire, $zarotag, $encode=FALSE){
        if(strlen($mit) > ($mennyire - strlen($zarotag))) $mit = substr($mit, 0, ($mennyire - strlen($zarotag))).$zarotag;
        return($encode ? htmlspecialchars($mit) : $mit);
    }
	
	function checkCoupon($code) {
		$query = strtoupper($code);
		$c = db_query("SELECT * FROM coupons WHERE code='%s'", $query);
		if(mysql_num_rows($c) > 0) {
			$row = mysql_fetch_object($c);
			$carr = array(
				'id' => $row->id,
				'code' => $row->code,
				'discount' => $row->discount,
				'expires' => $row->expires
			);
			return intval($carr['discount']);
		} else return 0;
	}

	function syncCarts() {
		
	}
	function printMoney($price) {
		echo(number_format($price, 2));
	}

    // url sallang eltavolitasa
    function strip_url($url){
        if(substr($url, -1) == '/') $url = substr($url, 0, -1);
        return strtr($url, array('http://' => '', 'www.' => ''));    
    }
	function html_cut($text, $max_length)
	{
    	$tags   = array();
    	$result = "";

    	$is_open   = false;
    	$grab_open = false;
    	$is_close  = false;
    	$in_double_quotes = false;
    	$in_single_quotes = false;
    	$tag = "";

    	$i = 0;
    	$stripped = 0;

    	$stripped_text = strip_tags($text);

    	while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
    	{
        	$symbol  = $text{$i};
        	$result .= $symbol;

        switch ($symbol)
        {
           	case '<':
                $is_open   = true;
                $grab_open = true;
                break;

           	case '"':
               	if ($in_double_quotes)
                   $in_double_quotes = false;
               	else
               	    $in_double_quotes = true;

            	break;

            case "'":
              	if ($in_single_quotes)
                	  $in_single_quotes = false;
              	else
                	  $in_single_quotes = true;

            	break;

            case '/':
                	if ($is_open && !$in_double_quotes && !$in_single_quotes)
                	{
                    	$is_close  = true;
                    	$is_open   = false;
                    	$grab_open = false;
                	}

                	break;

            case ' ':
                	if ($is_open)
                    	$grab_open = false;
                	else
                    	$stripped++;

                	break;

            case '>':
                	if ($is_open)
                	{
                    	$is_open   = false;
                    	$grab_open = false;
                    	array_push($tags, $tag);
                    	$tag = "";
                	}
                	else if ($is_close)
                	{
                    	$is_close = false;
                    	array_pop($tags);
                    	$tag = "";
                	}

                	break;

            	default:
                	if ($grab_open || $is_close)
                   	 $tag .= $symbol;

            	    if (!$is_open && !$is_close)
                    	$stripped++;
        	}

        	$i++;
    	}

    	while ($tags)
        	$result .= "</".array_pop($tags).">";

    	return $result;
	}

    function censore_email($mail){
        return preg_replace('#@(.+)\.#', '@***.', $mail, 1);
    }

    function errorpage($title, $message, $buttons=NULL){
        page_begin($title);
        echo '<div class="x12"><p>'. $message .'</p>';
        if($buttons != NULL) {
            $elements = explode(";", $buttons);
            foreach($elements as $element) {
                $part = explode("|", $element);
                echo '<a class="btn" href="'.($part[1] ? $part[1] : ($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : 'index.php')).'" title="'. $part[0] .'">'. $part[0] .'</a>&nbsp;';
            }
        }
        echo '</div>';
        page_end();
        exit();
    }

	
    /*function send_mail($to, $subject, $message){
        $headers  = "From: support@vapelex.com". PHP_EOL;
        $headers  = "Reply-To: support@vapelex.com". PHP_EOL;
        $headers .= "MIME-Version: 1.0". PHP_EOL;
        $headers .= "Content-Type: text/html; charset=ISO-8859-1". PHP_EOL;

        return mail($to, $subject, $message, $headers, '-fno-reply@vapelex.com');
    }*/
	
	function send_mail($to, $subject, $message){
        $mail = new PHPMailer;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.vapelex.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'support@vapelex.com';                 // SMTP username
		$mail->Password = 'seacow7';                           // SMTP password

		$mail->From = 'support@vapelex.com';
		$mail->FromName = 'VapeLex.com';
		$mail->addAddress($to);     // Add a recipient
		$mail->addReplyTo('support@vapelex.com', 'VapeLex.com');

		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = $subject;
		$mail->Body    = $message;

		if(!$mail->send()) {
			
		} else {
    		
		}

    }

    function time_ago($tm, $rcs = 0, $cur_tm = 0) {
        $cur_tm = ($cur_tm == 0 ? time() : $cur_tm);
        $dif = abs($cur_tm-$tm);
        $pds = array('second','minute','hour','day','week','month','year','decade');
        $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
        for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

        $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
        if(($rcs > 0)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm, --$rcs);
        return $x;
    }
	function stringToHex($string) {
    	$hexString = '';
    	for ($i=0; $i < strlen($string); $i++) {
        	$hexString .= '%' . bin2hex($string[$i]);
    	}
    	return strtoupper($hexString);
	}

function PwdHash($pwd, $salt = null)
{
    if ($salt === null)     {
        $salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
    }
    else     {
        $salt = substr($salt, 0, SALT_LENGTH);
    }
    return $salt . sha1($pwd . $salt);
}



    function page_begin($title = '' , $meta_description="Lexington's First Online E-Cig Supply Co."){
        global $USER;

        // things
        $valid_until = date('Y/m/d', $USER['valid_date']);
        $current_time = date('Y/m/d H:i:s');

        // title
        if($title){
            $head_title = $title.' | VapeLex.com';
        }else{
            $head_title = $title = 'VapeLex.com - E-Cig Supply';    
        }
		
		        // navs
        $navs = array(
        '/index.php' => 'HOME',
        '/eliquid.php' => 'E-LIQUID',
        '/starterkits.php' => 'STARTER KITS',
		'/accessories.php' => 'ACCESSORIES'
        );

        foreach($navs as $url => $url_title){
            $classes = '';
			$color = '';
            if($_SERVER['PHP_SELF'] == $url){ $classes .= 'active'; $color='style="color:#7bae23;"';}
            $navs_print .= '<li><a class="'. $classes .'" '. $color .' href="'. $url .'">'. $url_title .'</a></li>';    
        }
    ?>
    <!DOCTYPE html>
<!--[if IE 8]> <html class="ie8"> <![endif]-->
<!--[if IE 9]> <html class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title><?php print $head_title; ?></title>
        <meta name="description" content="<?php echo($meta_description); ?>">
        <!--[if IE]> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href='//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic%7CPT+Gudea:400,700,400italic%7CPT+Oswald:400,700,300' rel='stylesheet' id="googlefont">
        
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/prettyPhoto.css">
        <link rel="stylesheet" href="css/sequence-slider.css">
        <link rel="stylesheet" href="css/owl.carousel.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/responsive.css">
        <link rel="stylesheet" href="js/rateit/rateit.css">
        
        <!-- Favicon and Apple Icons -->
        <link rel="icon" type="image/png" href="images/icons/icon.png">
        <link rel="apple-touch-icon" sizes="57x57" href="images/icons/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="72x72" href="images/icons/apple-icon-72x72.png">
        
        <!--- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/jquery-1.11.0.min.js"><\/script>')</script>
        <script src="js/vapelex.js"></script>
    	<script><?php global $onloadscript; echo($onloadscript); ?></script>
        <script src="js/accounting.js"></script>
        <script src="js/rateit/jquery.rateit.min.js"></script>
		<!--[if lt IE 9]>
			<script src="js/html5shiv.js"></script>
			<script src="js/respond.min.js"></script>
		<![endif]-->
	    
		<style id="custom-style">
		
		</style>
	</head>
    <?php 
		//Check age verification status & disable scrolling
		if(!isset($_COOKIE['is_legal'])) {
	?>
    <body class="opaque">
    <div id="overlay">
        	<div id="AVpattern">
            	<div id="AVcontentBG">
                	<a id="AVBGLink">*Warning: the safety of nicotine-based products is currently inconclusive. By entering this site, you agree that all use is at your own risk.</a>
                </div>
            </div>
    </div>
    <div id="age-overlay">
    	Welcome!
        <br><br>
        <p>Please verify your<br>
        age to enter.</p>
        
        
        <a href="javascript:history.go(-1)"><input id="under" type="button" value="Under 18"></a>
        <a onClick="createCookie('is_legal', '1', 3650); window.location.reload();" href="javascript:void(0);"><input id="over" type="button" value="18 &amp; Over"></a>
        <br><br>
        
    </div>
    <script>
		$('html, body').css({
    	'overflow': 'hidden',
    	'height': '100%'
		})
	</script>
	<?php } else { ?> 
    <body>
    
    <?php } ?>
	<script>
  		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  		ga('create', 'UA-33666024-1', 'vapelex.com');
  		ga('send', 'pageview');

	</script>
    <div id="wrapper" class="boxed">
    	<header id="header">
    		<div id="header-top">
    			<div class="container">
    				<div class="row">
                        <div class="col-md-12">
        					<div class="header-top-left">
        						<ul id="top-links" class="clearfix">
  
        							<li><a href="login.php" title="My Account"><span class="top-icon top-icon-user"></span><span class="hide-for-xs">My Account</span></a></li>
        							<li><a href="cart.php" title="My Cart"><span class="top-icon top-icon-cart"></span><span class="hide-for-xs">My Cart</span></a></li>
        							<li><a href="checkout.php" title="Checkout"><span class="top-icon top-icon-check"></span><span class="hide-for-xs">Checkout</span></a></li>
        						</ul>
                                
        					</div><!-- End .header-top-left -->
        					<div class="header-top-right">
        						
        						<div class="header-top-dropdowns pull-right">
    								
    							</div><!-- End .header-top-dropdowns -->
    							<div class="header-text-container pull-right">
    								<p class="header-text"></p>
        							<p class="header-link"><span class="header-box-icon header-box-icon-email"></span><a href="mailto:support@vapelex.com">support@vapelex.com</a></p>
    							</div><!-- End .pull-right -->
    						</div><!-- End .header-top-right -->
    					</div><!-- End .col-md-12 -->
    				</div><!-- End .row -->
    			</div><!-- End .container -->
    		</div><!-- End #header-top -->
    		
    		<div id="inner-header">
            <div class="header-box contact-phones pull-right clearfix">
    								<span class="header-box-icon header-box-icon-earphones"></span>
    								<ul class="pull-left">
    									<li>+1-844-830-0221</li>
    								</ul>
    							</div><!-- End .contact-phones -->
    			<div class="container">
    				<div class="row">
                    
						<div class="col-md-5 col-sm-5 col-xs-12 logo-container">
                        
               
							<h1 class="logo clearfix">
								
								
							</h1>
                            
						</div><!-- End .col-md-5 -->
    					<div class="col-md-7 col-sm-7 col-xs-12 header-inner-right">
    							
    							
    							
    					</div><!-- End .col-md-7 -->

    				</div><!-- End .row -->
    			</div><!-- End .container -->
    			
    			<div id="main-nav-container">
    				<div class="container">
    					<div class="row">
    						<div class="col-md-12 clearfix">
    							
    							<nav id="main-nav">
    								<div id="responsive-nav">
    									<div id="responsive-nav-button">
											Menu <span id="responsive-nav-button-icon"></span>
										</div><!-- responsive-nav-button -->
    								</div>
    								<ul class="menu clearfix">
    									<?php print $navs_print; ?>
									</ul>
    								
    							</nav>
    							
    							<div id="quick-access">
    								<div class="dropdown-cart-menu-container pull-right">
    								<div class="btn-group dropdown-cart">
									<button type="button" class="btn btn-custom dropdown-toggle" data-toggle="dropdown">
										<span class="cart-menu-icon"></span>
										<span id="noitems"><?php global $cart_total_items; echo($cart_total_items); ?></span> item(s) <span id="total1" class="drop-price">
										$<?php global $cart_total; echo(number_format($cart_total, 2)); ?>
                                                </span>
									</button>
									
										<div class="dropdown-menu dropdown-cart-menu pull-right clearfix" role="menu">
											<p class="dropdown-cart-description">Recently added item(s).</p>
											<ul class="dropdown-cart-product-list">
                                            
<?php
	if(isset($_COOKIE["vapelex_cart"])) {
										$cookie_data = json_decode(stripslashes($_COOKIE["vapelex_cart"]), true);
										if(!empty($cookie_data)) {
											foreach ($cookie_data as $item) {
												$item_attr = array();
												global $cart;
												$price = $cart->getItemPrice($item[0], $item[3]);
												$item_attr = $cart->getItemData($item[0]);
												$xid = str_replace(';', 'a', $item[3]);
												$id = $item[0].$xid;
				
				echo('<li name="'. $item_attr['pid'] .'" id="'. $id .'" class="item clearfix">
												<a href="Javascript:delete_cart_item(\''. $id .'\')" title="Delete item" class="delete-item"><i class="fa fa-times"></i></a>
												<a href="http://'. $_SERVER['HTTP_HOST'] .'/cart.php#'. $id .'" title="Edit item" class="edit-item"><i class="fa fa-pencil"></i></a>
													<figure>
														<a href="'. $item_attr['href'] .'"><img src="images/products/'. $item_attr['image'] .'" alt=""></a>
													</figure>
													<div class="dropdown-cart-details">
														<p class="item-name">
														<a href="'. $item_attr['href'] .'">'. $item_attr['name'] .'</a>
														</p>
														<p id="dd_cart_q'. $id .'">
															x'. $item[1] .'
															<span class="item-price">'. $price .'</span>
														</p>
													</div><!-- End .dropdown-cart-details -->
												</li>
				');
				
												
												
													
											}
										}
	}
											?>
												
											</ul>
											
											<ul class="dropdown-cart-total">
												<li><span class="dropdown-cart-total-title">Total: </span>
												 <span id="total2">$<?php printMoney($cart_total); ?></span></li>
											</ul><!-- .dropdown-cart-total -->
											<div class="dropdown-cart-action">
												<p><a href="cart.php" class="btn btn-custom-2 btn-block">Cart</a></p>
												<p><a href="checkout.php" class="btn btn-custom btn-block">Checkout</a></p>
											</div><!-- End .dropdown-cart-action -->
											
										</div><!-- End .dropdown-cart -->
										</div><!-- End .btn-group -->
									</div><!-- End .dropdown-cart-menu-container -->
									
									
    							<form class="form-inline quick-search-form" role="form" method="get" action="search.php">
									<div class="form-group">
                                          <input type="text" name="q" class="form-control" placeholder="Search here">
									</div><!-- End .form-inline -->
									<button type="submit" id="quick-search" class="btn btn-custom"></button>
								</form>
    							</div><!-- End #quick-access -->
    						</div><!-- End .col-md-12 -->
    				</div><!-- End .row -->
    			</div><!-- End .container -->
    				
    			</div><!-- End #nav -->
    		</div><!-- End #inner-header -->
    	</header><!-- End #header -->
        
        <section id="content">

     	<?php    
      	}

              function page_end(){
                        global $USER;
         ?>
                        </section><!-- End #content -->
   		</body>
        
        <footer id="footer">
        	
        	
        	<div id="footer-bottom">
        		<div class="container">
        			<div class="row">
        				<div class="col-md-2 col-sm-2 col-xs-12 footer-social-links-container">
        					<ul class="social-links clearfix">
        						<li><a href="https://twitter.com/share" class="social-icon icon-twitter" data-url="www.VapeLex.com" data-via="VapeLex"></a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script></li>
        						<li><a href="#" class="social-icon icon-facebook"></a></li>
        					</ul>
        				</div><!-- End .col-md-7 -->
        				<div class="col-md-10 col-sm-10 col-xs-12 footer-text-container">
        					<p><a href="guarantee.php">VapeLex.com Guarantee &amp; Contact Info</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="privacypolicy.php">Privacy Policy</a>&nbsp;&nbsp;|&nbsp;&nbsp;&copy; 2014 Powered by VapeLex&trade;&nbsp;&mdash;&nbsp;<script type="text/javascript" language="javascript">var ANS_customer_id="2f5e24cc-87a3-4d8a-aa28-8d7b7283b18b";</script> <script type="text/javascript" language="javascript" src="//vapelex.com/js/seal.js" ></script></p>
        				</div><!-- End .col-md-5 -->
                        
        			</div><!-- End .row -->
				</div><!-- End .container -->
        	</div><!-- End #footer-bottom -->
        	
        </footer><!-- End #footer -->
    </div><!-- End #wrapper -->
        <a href="#" id="scroll-top" title="Scroll to Top"><i class="fa fa-angle-up"></i></a><!-- End #scroll-top -->
	<!-- END -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/smoothscroll.js"></script>
    <script src="js/retina-1.1.0.min.js"></script>
    <script src="js/jquery.placeholder.js"></script>
    <script src="js/jquery.hoverIntent.min.js"></script>
    <script src="js/twitter/jquery.tweet.min.js"></script>
    <script src="js/jquery.flexslider-min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jflickrfeed.min.js"></script>
    <script src="js/jquery.prettyPhoto.js"></script>
    <script src="js/jquery.sequence-min.js"></script>
    <script src="js/main.js"></script>
	
    <script>
    	$(function() {
    		// Sequence.js Slider Plugin
			var options = {
				nextButton: true,
				prevButton: true,
				pagination:true,
				autoPlay: true,
				autoPlayDelay: 8500,
				pauseOnHover: true,
				preloader: true,
				theme: 'slide',
				speed: 700,
				animateStartingFrameIn: true
                },
				homeSlider = $('#slider-sequence').sequence(options).data("sequence");
    	
    	});
    </script>
    </body>
</html>
    <?php 
    }
	?>