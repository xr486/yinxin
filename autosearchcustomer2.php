<?php
include_once("connect.php");

$q = strtolower($_GET["term"]);
$query=mysql_query("select * from customers where customer_name like '%$q%' and enable_flag='Y' limit 0,10");

while ($row=mysql_fetch_array($query)) {
	$result[] = array(
		    'id' => $row['customer_id'],
		    'label' => $row['customer_name']
	);
}
echo json_encode($result);
?>