<?php
include_once("connect.php");

$q = strtolower($_GET["term"]);
$query=mysql_query("select * from fin_bank_alls where bankaccountname like '%$q%' limit 0,10");

while ($row=mysql_fetch_array($query)) {
	$result[] = array(
		    'id' => $row['bankid'],
		    'label' => $row['bankaccountname']
	);
}
echo json_encode($result);
?>