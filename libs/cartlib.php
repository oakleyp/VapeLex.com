<?php
class Cart {
/*
*	VapeLex Shopping Cart & Item functions
*	Markdown:
*
*		public:
*		getCartTotal() - returns cart total as double
*		countCartItems() - returns total number of cart items
*		getCartItems() - returns cart items from cookie as array
*		getItemPrice($id, optional $product_size) - returns item price after discount and volume
*		getItemData($id) - returns all item info from the database as an array
*		addToCart($id) - Adds item to cart & updates quantity
*		countItemRatings($id) - Returns number of item ratings
*		getItemRatings($id) - Returns array of user ratings on item
*		addRating($pid, $uid, $rating) - Adds or updates a user's item rating from 1-5 stars
*		getRatingAverage($id) - returns the average user rating for an item
*		getCartWeight() - returns the weight of all cart items for shipping estimate - format of returned string: lb;oz
*		
*		
*		private:
*		getItemPriceBySize($details) - returns item price based on volume (for e-juice) as double
*		
*		Cart Cookie Item Structure:
*		[0] - id
*		[1] - quantity
*		[2] - price
*		[3] - details - string stored with ';' delimiter in format: "strength;size"
*		
*	-Oakley Peavler
*/

	// Begin Public Functions & Vars:
	
	public function getCartTotal() {
		
		$total = 0.00;
		if(isset($_COOKIE['vapelex_cart'])) {
			$cookie = $_COOKIE['vapelex_cart'];
			$cookie = stripslashes($cookie);
			$cookie_data = json_decode($cookie, true);
			foreach($cookie_data as $item) { 
				$item_attr = $this->getItemData($item[0]);	
				$item_price = $this->getItemPrice($item[0], $item[3]);
				$total += $item_price * $item[1];
			}
		
		}
		return $total;
	} 
	
	public function countCartItems() {
		$total = 0;
		if(isset($_COOKIE['vapelex_cart'])) {
			$cookie = $_COOKIE['vapelex_cart'];
			$cookie = stripslashes($cookie);
			$cookie_data = json_decode($cookie, true);
			foreach($cookie_data as $item) { 
				$total += $item[1];
			}
		}
		return $total;
	}
	
	public function getCartItems() {
		if(isset($_COOKIE['vapelex_cart'])) {
			$cookie = $_COOKIE['vapelex_cart'];
			$cookie = stripslashes($cookie);
			$cookie_data = json_decode($cookie, true);
			return $cookie_data;
		} else return false;
	}
	
	public function getItemPrice($id, $product_size = "none") {
		
		$price = 0.00;
		$product_attr = $this->getItemData($id);
		if($product_size != "none") { // Product has attribute-specific price
			$product_size = explode(';', $product_size);
			$product_size = $product_size[1];
			$arrkey = "price_var1";
			switch($product_size) {
				case "15ml":
					$arrkey = "price_var2";
					break;
				case "30ml":
					$arrkey = "price_var1";
					break;
				case "1.8ohm":
					$arrkey = "price_var1";
					break;
				case "2.0ohm":
					$arrkey = "price_var2";
					break;
				case "2.2ohm":
					$arrkey = "price_var3";
					break;
				default:
					break;
			}
			$price = $product_attr[$arrkey] - ($product[$arrkey] * ($product_attr['discount']/100));
		} else { 
			$price = $product_attr['price'] - ($product_attr['price'] * ($product_attr['discount']/100));	
		}
		
		return $price;		
	}
	
	public function getItemData($id) {
		$iq = db_query("SELECT * FROM items WHERE pid='%s'", $id);
		$result = mysql_fetch_object($iq);
		$data = array(
			'name'	=> $result->name,
			'pid' 	=> $result->pid,
			'description' => $result->description,
			'category' => $result->category,
			'image' => $result->image,
			'image_hover' => $result->image_hover,
			'image_list' => $result->image_list,
			'options' => $result->options,
			'option_title' => $result->option_title,
			'price' => $result->price,
			'price_var1' => $result->price_var1,
			'price_var2' => $result->price_var2,
			'price_var3' => $result->price_var3,
			'price_var4' => $result->price_var4,
			'price_var5' => $result->price_var5,
			'href'	=> $result->href,
			'discount' => $result->discount,
			'stock' => $result->stock
		);
		return $data;
	}
	
	public function addToCart($id, $pdetails = "none") {
		
		$price = $this->getItemPrice($id, $pdetails);
		$onloadscript = ""; // Javascript to add items to cart on page load 
		if(isset($_COOKIE['vapelex_cart'])) {
			
				//Check if item exists in cart already, if so, update quantity
				$current_cartitems = json_decode(stripslashes($_COOKIE['vapelex_cart']), true);
				$ccount = 0;
				$duplicate = false;
				foreach ($current_cartitems as $citem) {
					if($citem[0] == $_GET['cart'] && $citem[3] == $pdetails) {
						
						$current_cartitems[$ccount][1] = $citem[1] + 1;
						$duplicate = true;
						if($_SESSION['logged_in']) { // If user is logged in, save to cart
							db_query("UPDATE cart SET quantity='%s' WHERE product_id='%s' AND user_id='%s'", $currentquantity+1, $citem[0], $USER['uid']);
						}
						break;
					}
					$ccount += 1;
				}
				
				if(!$duplicate) {	
				
					$current_cartitems[count($current_cartitems)] = array(
					0 => $_GET['cart'], 
					1 => 1, 
					2 => $price, 
					3 => $pdetails
					);
				}	
				
				$onloadscript = ('function redirect(){
 		 	window.location = "'. strtok($_SERVER["REQUEST_URI"],"?") .'"
			} eraseCookie("vapelex_cart"); createCookie("vapelex_cart", "'. urlencode(json_encode($current_cartitems, true)) .'", 30); 		redirect();');
			
		} else {
			
			// Cart cookie does not exist - create new cookie
			$current_cartitems = array(
						0 => array(
							0 => $_GET['cart'], 
							1 => 1, 
							2 => $price,
							3 => $pdetails
						)
			);	
			$onloadscript = ('function redirect(){
 		 	window.location = "'. strtok($_SERVER["REQUEST_URI"],"?") .'"
			} createCookie("vapelex_cart", "'. urlencode(json_encode($current_cartitems, true)) .'", 30); redirect();');
			
		}
		
		return $onloadscript;
		
	}
	
	function getCartByUid($uid) {
		$iq = db_query("SELECT * FROM cart WHERE uid='%s'", $uid);
		$result = mysql_fetch_object($iq);
		$data = array(
			'product_id' => $result->product_id,
			'product_details' => $result->product_details,
			'quantity' => $result->quantity
		);
		return $data;
	}
	
	public function getItemRatings($id) {
		$returnarr = array();
		$i=0;
		$q = db_query("SELECT * FROM ratings WHERE pid='%s'", $id);
		while($result = mysql_fetch_assoc($q)) {
			$returnarr[$i] = array(
				'id' => $result['id'],
				'uid' => $result['uid'],
				'pid' => $result['pid'],
				'rating' => $result['rating'],
				'review' => htmlspecialchars($result['review']),
				'pdate' => $result['pdate']
			);
			$i++;
		}
		if(mysql_num_rows($q)> 0) {
			return $returnarr;
		} else return false;
	}
	
	public function addRating($pid, $uid, $rating) {
		$q = db_query("SELECT * FROM ratings WHERE uid='%s' AND pid='%s'", $_POST['uid'], $_GET['id']);
		if(mysql_num_rows($q) == 0) {
			db_query("INSERT INTO ratings VALUES ('%s', '%s', '%s', '%s', '%s', CURDATE())", 0, $_POST['uid'], $_GET['id'], ($_POST['rating']*20), "");
		}	
	}
	
	public function countItemRatings($id) {
		$q = db_query("SELECT * FROM ratings WHERE pid='%s'", $id);
		return mysql_num_rows($q);	
	}
	
	public function getRatingAverage($id) {
		$q = db_query("SELECT rating FROM ratings WHERE pid='%s'", $id);
		$count = 0;
		$total = 0;
		while($result = mysql_fetch_assoc($q)) {
			$total += $result['rating'];
			$count++;
		}
		return $total/$count;
	}
	public function getCartWeight() {
		$weightoz = 0;
		$weightlb = 0;
		if(isset($_COOKIE['vapelex_cart'])) {
			$cookie = $_COOKIE['vapelex_cart'];
			$cookie = stripslashes($cookie);
			$cookie_data = json_decode($cookie, true);
			foreach($cookie_data as $item) { 
				$details = $this->getItemData($item[0]);
				if($details['category'] == 'liquid') {
					$weightoz += $item[1];	
				} else if($details['category'] == 'starter') {
					$weightoz += ($item[1] * 3);	
				} else if($details['category'] == 'accessories') {
					$weightoz += ($item[1] * 2);
				}
			}
			if($weightoz > 16) {
				$weightlb = $weightoz/16;
				$weightoz = $weightoz%16;	
			}
		}
		return ($weightlb . ';' . $weightoz);
				
	}


	// Begin Private Functions & Vars:

}


?>