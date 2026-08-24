<?php
error_reporting(E_ALL ^ E_DEPRECATED);
$host="localhost";
$db_user="yidao";
$db_pass="yidao123456";
$db_name="yidao";
$timezone="Asia/Shanghai";

$db=mysql_connect($host,$db_user,$db_pass);
mysql_select_db($db_name,$db);
mysql_query("SET names UTF8");

header("Content-Type: text/html; charset=utf-8");
?>
