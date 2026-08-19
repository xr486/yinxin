<?php
include_once("connect.php");

$q = strtolower($_GET["term"]);
$query=mysql_query("select * from vendors where vendor_name like '%$q%' limit 0,10");

while ($row=mysql_fetch_array($query)) {
	$result[] = array(
		    'id' => $row['vendor_id'],
		    'label' => $row['vendor_name']
	);
}
echo json_encode($result);
?>