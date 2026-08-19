<?php
include_once("connect.php");
$invoice_num = $_GET['invoice_num'];
$vendor_code = $_GET['vendor_code']; 
$type = $_GET['type']; 

$result = mysql_query("SELECT * FROM ap_invoice_headers_all WHERE invoice_num='$invoice_num' and vendor_code='$vendor_code' 
  ", $db);
 
$rows = mysql_num_rows($result);
if($rows >= 1){
echo("0");
}else{
echo("1");
}
?> 