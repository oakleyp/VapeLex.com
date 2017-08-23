<?php
require_once("libs/base.php");
if(isset($_GET['id']) && isset($_SESSION['logged_in'])) {
	$data = $cart->getItemData($_GET['id']);
	$name = '<a href="product.php?id='. $_GET['id'] . '">'.$data['name'].'</a>';
	$q = db_query("SELECT * FROM users WHERE uid='%s'", $_SESSION['uid']);
	$result = mysql_fetch_array($q);
	$valid = false;
	if(date('m', strtotime($result['lastsample'])) != date('m')) $valid = true;
	else if($result['lastsample'] == "0000-00-00") $valid = true;
	if($valid == true) {
		db_query("UPDATE users SET lastsample=CURDATE() WHERE uid='%s'", $_SESSION['uid']); 
		send_mail("oakleypeavler@gmail.com", "Sample Request", "
		A sample was requested. \r\n
		Flavor: ". $data['name'] ."\r\n
		Address: ". str_replace('|', ' ', $_SESSION['address']));
	}
}
page_begin(); 
 ?>

<div class="container">
        		<div class="row">
        			<div class="col-md-12">
                    <div class="md-margin2x"></div>
						<div class="hero-unit">
        				  	<h2>Sample request received</h2>

        					<p style="color:#f3f3f3; line-height:1.5em;">
                            	Your request for a sample of <?php echo($name); ?> has been received. We will ship your 5mL sample on the next business day via USPS First Class mail to the address listed on your account, and you can expect to receive it 1-3 days after the shipping date. Thank you for your interest, and we hope you enjoy your sample! 
                            </p>
							<span class="small-bottom-border big"></span>

       				    </div>
                     </div>
       			</div>
</div>
           
<?php page_end(); ?>