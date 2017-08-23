<?php
	require_once("libs/base.php");
	if(isset($_GET['q'])) {
		 //if(preg_match("^/[A-Za-z]+/", ($_GET['q']))){ 
		 	$name = $_GET['q']; 
			$q = db_query("SELECT * FROM `items` WHERE `name` LIKE '%".$name."%' OR `description` LIKE '%".$name."%'");
			print_r($q);
			 while($result = mysql_fetch_array($q)) {
								echo('<h4><a href="'.$result['href'].'">'.$result['name'].'</a></h4>\n');
								echo('<p>'.$result['description'].'</p>\n');
								echo('<hr>\n');
							} 
			
		 //} else echo ("NOOO");
	}
	page_begin("Search Results");
?>
<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<div class="md-margin2x"></div>
                        <div class="hero-unit">
                        	<h2>Search Results:</h2>
                            <span class="small-bottom-border big"></span>
                            </div>
                        	<div class="xs-margin"></div><!-- Space -->
                            
                  </div>
              </div>
</div>
<?php page_end(); ?>
                            