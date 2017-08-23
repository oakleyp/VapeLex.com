<?php

$en = htmlspecialchars($_GET['email']);

require_once("libs/base.php");
page_begin("Registration Complete | VapeLex.com");
?>
	<div class="container">
        		<div class="row">
        			<div class="col-md-12">
						<div class="md-margin2x"></div>
                        <div class="hero-unit">
                        	<h2>Thank you!</h2>
                            <p style="color:#f3f3f3; line-height:1.5em;">
                            Your account has been created and you are almost able to login. First you must confim your email account. An email has been sent to <?php echo $en; ?>. Once you receive the email, click the activation link and your account will be ready! (Check Spam and Junk folder for email)
                            </p>
                            <span class="small-bottom-border big"></span>
                        </div>
                 </div>
           </div>
        			
						   
  </div>
  

        	
        <?php page_end(); ?>