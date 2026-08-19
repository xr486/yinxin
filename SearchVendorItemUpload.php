<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('供应商料号关系上传');
$ViewTopic = '供应商料号关系上传';
$BookMark = '供应商料号关系上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('供应商料号关系上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from vendor_item_relation_upload  where created_by='".$_SESSION['UserID']."'";
		// echo $sql3;
		 
	    $result = DB_query($sql3,$db);

$excel = new Excel();       
 
$xls = $_FILES['userfile']['tmp_name']; 

$excel->setOutputEncoding('utf-8');
 
$excel->read($xls); 	

$arr = $excel->sheets[0]['cells']; 
$i = 0;
$line=0;
$time = time();
foreach($arr as $arry=>$row){
    if($arry>1){
       $line=$line+1; 
	   $ri=substr($row['5'],0,2);
	   $yue=substr($row['5'],3,2);
	   $nian= substr($row['5'],6,4);
	   $heji= $nian.'/'.$yue.'/'.$ri;	

	   @$data[$i]['created_by'] =$_SESSION['UserID'];
	   @$data[$i]['vendor_code'] = $row['1'];
	   @$data[$i]['item_no'] = $row['2'];
	    
	   $sql = "insert into vendor_item_relation_upload (vendor_code,item_no,created_by) 
			   values (
				   '".@$data[$i]['vendor_code']."',
				   '".@$data[$i]['item_no']."',
				  
				   '".@$data[$i]['created_by']."' 
				   
			   )";
	   $result = DB_query($sql,$db);
		 
		 //echo $sql;
	}
	$i++;
}

 

//echo '上传完成' ;
//exit;
unset($_SESSION['Request']);
echo '<h3>' . _('您已完成 '.$line.' 笔资料上传，请进入确认界面保存资料') . '</h3>';

echo '<a href="' . $RootPath . '/SearchVendorItemUpload2.php"><h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="SearchVendorItemUpload.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	   echo '<table><tr><div class="centre"><td><a href="' . $RootPath . '/PO/VendorItemUpload.xls" target="_blank">' . '下载标准格式文件' . '</td></tr></div></table>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' ._('选择需要上传的文件') . ': <input name="userfile" type="file" />
		<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>