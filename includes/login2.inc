<?php

error_reporting(E_ALL ^ E_DEPRECATED);
$db_host='localhost';

$db_user='root';

$db_pass='';

$db_name='yixin';

$con=mysql_connect($db_host,$db_user,$db_pass);

mysql_query("set names 'utf8'");

if(!$con)die(mysql_error()); 

mysql_select_db($db_name); 
?>
