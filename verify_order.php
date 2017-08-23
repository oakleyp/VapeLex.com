<?php 
require_once("libs/base.php");
require_once("libs/anet_php_sdk/AuthorizeNet.php");
//error_reporting(E_ALL); ini_set('display_errors', '1'); //Uncomment to show all errors
if(count($_POST) > 0) {
	if(($_POST['x_response_code'] == 1 || $_POST['x_response_code'] == 4)){
		$q = db_query("SELECT * FROM transactions WHERE order_id='%s'", $_POST['x_invoice_num']);
		$md5h = strtoupper(md5("seacow79d6KC7j3" . $_POST['x_trans_id'] . $_POST['x_amount']));
		if(mysql_num_rows($q) > 0 && $md5h == $_POST['x_MD5_Hash']) {
			//Transaction is valid, create email invoice
			db_query("UPDATE transactions SET complete=true WHERE order_id='%s'", $_POST['x_invoice_num']);
			
			//Get cart items from db
			$result = mysql_fetch_array($q);
			$cart_data = json_decode(stripslashes(urldecode($result['cart_data'])));
			
			//Compose e-mail invoice chart
			$line_item = "";
			foreach($cart_data as $item) {
				$data = $cart->getItemData($item[0]);
				//Decrease quantity by amount sold
				db_query("UPDATE items SET stock='%s' WHERE pid='%s'", ($data['stock']-$item[1]), $item[0]);
				$details = str_replace(';', ' ', $item[3]); 
				$line_item .= '<tr align="left" id="noshow">
			  <td style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;">&nbsp;</td>
			  <td align="center" style="font-family: Arial, Helvetica, sans-serif;  font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;">&nbsp;</td>
			  <td align="left" style="font-family: Arial, Helvetica, sans-serif;  font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;" nowrap>'.$data['name'].'</td>
			  <td style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;" align="left">'.$details.'</td>
			  <td align="right" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;">
			  </td>
			  <td align="right" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;" nowrap="nowrap">'.$item[1].'</td>
			  <td align="right"  style="font-family: Arial, Helvetica, sans-serif;  font-size: 11px; color: #656565;  text-decoration: none;  padding: 5px; border-top-width: 1px; border-top-style: none;  border-right-style: none; border-bottom-style: solid; border-left-style: none;  border-top-color: #CFCFCF;" nowrap id="disc_amt_">$'.$cart->getItemPrice($item[0], $item[3]).'</td></tr>';
			}
			$message_base = file_get_contents("libs/receipt_template.html");
			$message_base = str_replace("@INVOICEITEMS@", $line_item, $message_base);
			$message_base = str_replace("@SHIPPING@", number_format($result['ship_total'], 2), $message_base);
			$message_base = str_replace("@DISCOUNT@", checkCoupon($result['coupon']) . "%", $message_base);
			$message_base = str_replace("@TOTAL1@", $_POST['x_amount'], $message_base);
			$message_base = str_replace("@TOTAL2@", $_POST['x_amount'], $message_base);
			$message_base = str_replace("@INVOICENUM@", $_POST['x_invoice_num'], $message_base);
			
			send_mail($result['email'], "Order Invoice - VapeLex.com", $message_base);
			
			 
			
			
		} else if($md5h != $_POST['x_MD5_Hash']) {
			send_mail("oakleypeavler@gmail.com", "Incorrect Hash", "Generated MD5 = ".$md5h." \nPOST MD5 = ".$_POST['x_MD5_Hash']." \nRemote Address:".$_SERVER['REMOTE_ADDR']);	
		}
	}
}

?>