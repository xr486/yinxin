<?php

// 兼容垫片：仅在当前 PHP 无 mysql 扩展时（如 PHP7+/IDE 另配的解释器）用 mysqli 兜底，
// 使下方 mysql_connect 等老调用不再报 undefined function。WAMP 已加载 mysql 时此文件为 no-op。
require_once __DIR__ . '/mysql_compat.php';

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
