<?php

function deletefile($file_name){
header("Content-Type: text/html;charset=utf-8");
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

    
    $sql = "SELECT
    order_num,
    road,
	 name 
FROM upload 
WHERE  order_num = '" .$file_name."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_array($result);
    $_POST['name'] = $myrow['name'];
    $_POST['road'] = $myrow['road'];
    $file_name=iconv("utf-8", "gb2312", $_POST['name']);
    $file_path=$_POST['road'];
    
    //$file_name=iconv("utf-8", "gb2312", $file_name);
    //$file_path="./upload/".$file_name;
    if (!file_exists($file_path)) {
        //echo "删除文件不存在";
        echo "<script>alert('删除文件不存在!');</script>";
        echo '<script>window.close();</script>';  
        return;
    }
    $sql = "delete from upload 
WHERE  name = '" .$file_name."'";
    $result = DB_query($sql, $db);
    unlink($file_path);
    //echo "<script>alert('成功删除附件!');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
    echo "<script>alert('成功删除附件!');</script>";
    echo '<script>window.close();</script>';  
    /*$fp=fopen($file_path, "r");
    $file_size=filesize($file_path);
    //  下载需要的头文件
    header("Content-type:application/octed-stream");
    header("Accept-Ranges:bytes");
    header("Accept-Lenght:$file_size");
    header("Content-Disposition:attachment;filename=".$file_name);
    $buffer=1024;
    $file_count=0;
    //读取文件内容
    while (!feof($fp) &&($file_size-$file_count>0)) {
        $file_data=fread($fp, $buffer);
        echo $file_data;
        $file_count+=$buffer;
    }
    fclose($fp);*/
}
?>