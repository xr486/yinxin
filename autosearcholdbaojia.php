<?php
include_once("connect.php");

$q = strtolower($_GET["term"]);
$query=mysql_query("select * from quote_lines_all where need_remark like '%$q%' limit 0,10");

while ($row=mysql_fetch_array($query)) {
	$result[] = array(
		    'id' => $row['quote_line_id'],
		    'label' => $row['need_remark']
	);
}
echo json_encode($result);
?>