<?php
require_once("database.php");
require_once("cartlib.php");
function USPSParcelRate($weightlbs, $weightozs, $dest_zip, $service = "PRIORITY") {

if($service == "PRIORITY") {
	$container = "SM FLAT RATE BOX";
} else if ($service == "PRIORITY MAIL EXPRESS") {
	$container = "VARIABLE";	
}

// This script was written by Mark Sanborn at http://www.marksanborn.net  
// If this script benefits you are your business please consider a donation  
// You can donate at http://www.marksanborn.net/donate.  

// ========== CHANGE THESE VALUES TO MATCH YOUR OWN ===========

$userName = '794VAPEL0543'; // Your USPS Username
$orig_zip = '40516'; // Zipcode you are shipping FROM

// =============== DON'T CHANGE BELOW THIS LINE ===============

$url = "http://Production.ShippingAPIs.com/ShippingAPI.dll";
$ch = curl_init();

// set the target url
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

// parameters to post
curl_setopt($ch, CURLOPT_POST, 1);

$jd = gregoriantojd(date('M'), date('d'), date('Y'));

$month = jdmonthname($jd, 0);
$date = date('Y-m-d');
$data = "API=RateV4&XML=<RateV4Request USERID=\"$userName\"><Package ID=\"0\"><Service>$service</Service><FirstClassMailType>PARCEL</FirstClassMailType><ZipOrigination>$orig_zip</ZipOrigination><ZipDestination>$dest_zip</ZipDestination><Pounds>$weightlbs</Pounds><Ounces>$weightozs</Ounces><Container>$container</Container><Size>Regular</Size><Machinable>TRUE</Machinable><ReturnLocations>FALSE</ReturnLocations> 
<ShipDate Option=\"HFP\">$date</ShipDate></Package></RateV4Request>";
//echo($data);
// send the POST values to USPS
curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

$result=curl_exec ($ch);
$data = strstr($result, '<?');
// echo '<!-- '. $data. ' -->'; // Uncomment to show XML in comments
$xml_parser = xml_parser_create();
xml_parse_into_struct($xml_parser, $data, $vals, $index);
xml_parser_free($xml_parser);
$params = array();
$level = array();
foreach ($vals as $xml_elem) {
    if ($xml_elem['type'] == 'open') {
        if (array_key_exists('attributes',$xml_elem)) {
            list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
        } else {
        $level[$xml_elem['level']] = $xml_elem['tag'];
        }
    }
    if ($xml_elem['type'] == 'complete') {
    $start_level = 1;
    $php_stmt = '$params';
    while($start_level < $xml_elem['level']) {
        $php_stmt .= '[$level['.$start_level.']]';
        $start_level++;
    }
    $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
    eval($php_stmt);
    }
}
curl_close($ch);
//echo '<pre>'; print_r($params); echo'</pre>'; // Uncomment to see xml tags
//print_r($params);
return $params;


}

if(isset($_GET['q']) && isset($_GET['q2'])) {
	$cart = new Cart();
	$weight = explode(';', $cart->getCartWeight());
	$weightlbs = $weight[0];
	$weightozs = $weight[1];
	$service = $_GET['q2']; 
	$params = USPSParcelRate($weightlbs, $weightozs, $_GET['q'], $service);
	if($service == "FIRST CLASS") {
		$result = $params['RATEV4RESPONSE']['0']['0']['RATE'];
	} if($service == "PRIORITY") {
		$result = $params['RATEV4RESPONSE']['0']['28']['RATE'];
	} if($service == "PRIORITY MAIL EXPRESS") {
		$result = $params['RATEV4RESPONSE']['0']['3']['RATE'];
	}
	echo($result);
	print_r($params);
	return $result;
}

?>