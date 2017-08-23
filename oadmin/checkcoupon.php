<?PHP
require_once("libs/base.php");
if(isset($_GET["q"]) && strlen($_GET["q"]) > 0) {
$query = strtoupper($_GET["q"]);
$c = db_query("SELECT * FROM coupons WHERE code='%s'", $query);
if(mysql_num_rows($c) > 0) {
	$row = mysql_fetch_object($c);
	$carr = array(
		'id' => $row->id,
		'code' => $row->code,
		'discount' => $row->discount,
		'expires' => $row->expires
	);
	echo(intval($carr['discount']));	
} else {
	echo("false");	
}
} else echo("false");

?>