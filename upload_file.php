<?php
include('includes/session.inc');

$Title = _('上传附件');
$ViewTopic= '上传附件';
$BookMark = '上传附件';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

// 允许上传的图片后缀 
$getdata=$_POST["OrderNum"]; 

unset($result);
header("Content-Type: text/html;charset=utf-8");
$allowedExts = array("jpeg", "jpg", "png",'doc','zip','rar','xls','txt','docx');
$temp = explode(".", $_FILES["file"]["name"]);
//echo $_FILES["file"]["size"];
$extension = end($temp);     // 获取文件后缀名
if ((($_FILES["file"]["type"] == "application/x-zip-compressed")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/png")
|| ($_FILES["file"]["type"] == "application/msword")
|| ($_FILES["file"]["type"] == "application/octet-stream")
|| ($_FILES["file"]["type"] == "text/plain")
|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document"))
/*&& ($_FILES["file"]["size"] < 204800)*/   // 小于 200 kb
&& in_array($extension, $allowedExts))
{
	if ($_FILES["file"]["error"] > 0)
	{
		echo "错误：: " . $_FILES["file"]["error"] . "<br>";
	}
	else
	{  
	if ( $_FILES["file"]["type"]=='image/png') {
	$_FILES["file"]["type"]='png';
	}
	if ( $_FILES["file"]["type"]=='image/jpg') {
	$_FILES["file"]["type"]='jpg';
	}
	if ( $_FILES["file"]["type"]=='image/jpeg') {
	$_FILES["file"]["type"]='jpeg';
	}
	if ( $_FILES["file"]["type"]=='text/plain') {
	$_FILES["file"]["type"]='txt';
	}
	if ( $_FILES["file"]["type"]=='application/octet-stream') {
	$_FILES["file"]["type"]='rar';
	}
		$_FILES["file"]["name"]=$getdata.".". $_FILES["file"]["type"];
        echo "上传成功!". "<br>";
		echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
		echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
		echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
		echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"] . "<br>";
        echo "订单名称: " . $getdata . "<br>";
        echo "上传时间: " .date('Y-m-d H:i:s',time()). "<br>";
        echo '<br /><div class="centre"><a href="' . $RootPath . '/AddNewOrder.php?New=Y">' . _('继续创建订单') . '</a></div>';
		// 判断当期目录下的 upload 目录是否存在该文件
		// 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
		if (file_exists($RootPath ."/upload/" . $_FILES["file"]["name"]))
		{
			echo $_FILES["file"]["name"] . " 文件已经存在。 ";
		}
		else
		{
			// 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
			move_uploaded_file($_FILES["file"]["tmp_name"], $RootPath ."/upload/" . $_FILES["file"]["name"]);
			//echo "文件存储在: " . "upload/" . $_FILES["file"]["name"];
            $getdata1= $RootPath ."/upload/" . $_FILES["file"]["name"];
            $getdata2= $_FILES["file"]["name"];
             $sql = "INSERT INTO upload (
							order_num,
                            road,
                            create_time,
                            name)
				VALUES ('" . $_POST["OrderNum"] . "',
                '".$getdata1."',
                '".time()."',
                '".$getdata2."'
					)";
               $result = DB_query($sql, $db);     
               
                  
		}
	}
}
else
{
	echo "非法的文件格式";
}
?>