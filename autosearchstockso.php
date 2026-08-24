<?php
include_once("connect.php");

$q = strtolower($_GET["term"]);
$query=mysql_query("select * from sf_item_no where item_no like '$q%' order by substr(item_no,-4,4) desc limit 0,10 ");

while ($row=mysql_fetch_array($query)) {
	$result[] = array(
		    'id' => $row['item_id'],
		    'label' => $row['item_no']
	);
}
echo json_encode($result);
?>