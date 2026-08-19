<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('料号整批上传');
$ViewTopic = '料号整批上传';
$BookMark = '料号整批上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('料号整批上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from sf_item_upload  where created_by='".$_SESSION['UserID']."'";
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
	   @$data[$i]['item_no'] = $row['1'];
	   @$data[$i]['item_name'] = $row['2'];
	   @$data[$i]['item_desc'] = $row['3'];
	
	   @$data[$i]['uom'] = $row['4'];   
	   @$data[$i]['item_type'] = $row['5'];  
	   @$data[$i]['item_category'] = $row['6'];  
	   @$data[$i]['item_use'] = $row['7'];  
	   @$data[$i]['conditions'] = $row['8'];  
	   @$data[$i]['wendu'] = $row['9'];  
	   @$data[$i]['light'] = $row['10'];  
	   @$data[$i]['shidu'] = $row['11'];  
	   @$data[$i]['youxiaoqi'] = $row['12'];  
	   @$data[$i]['sub_code'] = $row['13'];  
	   @$data[$i]['huohao'] = $row['14'];  
	   @$data[$i]['min_order'] = $row['15'];  
	   @$data[$i]['manufacture_time'] = $row['16'];  
	   @$data[$i]['safe_qty'] = $row['17'];  
	   @$data[$i]['sub_locator'] = $row['18'];  
	   @$data[$i]['project_name'] = $row['19'];  
	   @$data[$i]['so_flag'] = $row['20'];  
	   @$data[$i]['disable_flag'] = $row['21'];  
	   @$data[$i]['item_remark'] = $row['22'];  
	   $sql = "insert into sf_item_upload (item_no,item_name,item_desc,units,item_type,item_category1,created_by,item_use,wendu,light,shidu,youxiaoqi,conditions,sub_code,huohao,min_order,manufacture_time,safe_qty,sub_locator,project_name,so_flag,disable_flag,item_remark) 
			   values (
				   '".@$data[$i]['item_no']."',
				   '".@$data[$i]['item_name']."',
				   '".@$data[$i]['item_desc']."',
				   '".@$data[$i]['uom']."', 
				   '".@$data[$i]['item_type']."',
				   '".@$data[$i]['item_category']."',
				   '".@$data[$i]['created_by']."' ,
				   '".@$data[$i]['item_use']."' ,
				   '".@$data[$i]['wendu']."' ,
				   '".@$data[$i]['light']."' ,
				   '".@$data[$i]['shidu']."' ,
				   '".@$data[$i]['youxiaoqi']."' ,
				   '".@$data[$i]['conditions']."' ,
				   '".@$data[$i]['sub_code']."' ,
				   '".@$data[$i]['huohao']."' ,
				   '".@$data[$i]['min_order']."' ,
				   '".@$data[$i]['manufacture_time']."' ,
				   '".@$data[$i]['safe_qty']."' ,
				   '".@$data[$i]['sub_locator']."' ,
				   '".@$data[$i]['project_name']."',
				   '".@$data[$i]['so_flag']."',
				   '".@$data[$i]['disable_flag']."',
				   '".@$data[$i]['item_remark']."'
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

echo '<a href="' . $RootPath . '/SegmentUpload2.php"><h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="SegmentUpload.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	   echo '<table><tr><div class="centre"><td><a href="' . $RootPath . '/PO/ItemUploadSample.xls" target="_blank">' . '下载标准格式文件' . '</td></tr></div></table>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' ._('选择需要上传的文件') . ': <input name="userfile" type="file" />
		<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>