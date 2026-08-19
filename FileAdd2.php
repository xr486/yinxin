<?php
 $filename = $_GET['file_name'];
  $fdir="doc/temp/";
$filename=$fdir.$filename;
// 使用basename函数可以获得文件的名称而不是路径信息，保护了服务器的目录安全性
header("content-disposition:attachment;filename=".$filename);
header("content-length:".filesize($filename));
readfile($filename);

 
?>