<?php
include_once("connect.php");

$q = strtolower($_GET["term"]);
$query=mysql_query("select * from sf_item_no_v where item_category1<>'成品料号' and  item_no like '%$q%' limit 0,10");

while ($row=mysql_fetch_array($query)) {
	$result[] = array(
		    'id' => $row['item_id'],
		    'label' => $row['item_no']
	);
}
echo json_encode($result);
?>