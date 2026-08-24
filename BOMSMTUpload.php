<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('BOM整批上传');
$ViewTopic = 'BOM整批上传';
$BookMark = 'BOM整批上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('BOM整批上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from bom_smt_liaozhan_upload where created_by='".$_SESSION['UserID']."'";
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
    if($arry>3){
       $line=$line+1; 
	   $ri=substr($row['5'],0,2);
	   $yue=substr($row['5'],3,2);
	   $nian= substr($row['5'],6,4);
	   $heji= $nian.'/'.$yue.'/'.$ri;	
 
	   @$data[$i]['created_by'] =$_SESSION['UserID'];
	   @$data[$i]['assembly_item_no'] = $row['1'];
	   @$data[$i]['assembly_item_name'] = $row['2']; 
	   @$data[$i]['component_item'] = $row['4'];
	   @$data[$i]['youxian'] = $row['5'];
	   @$data[$i]['mian'] = $row['6'];
	   @$data[$i]['jiqiming'] = $row['7'];
	   @$data[$i]['tiezhuangtai'] = $row['8'];
	   @$data[$i]['address'] = $row['9'];
	   @$data[$i]['xingpian_name'] = $row['10'];
	   @$data[$i]['gongliaoqi_name'] = $row['11'];
	   @$data[$i]['baozhuang'] = $row['12'];
	   @$data[$i]['user_qty'] = $row['13'];
	   @$data[$i]['weizhi'] = $row['14'];
	 $sql = "insert into bom_smt_liaozhan_upload (assembly_item_no,assembly_item_name,component_item,youxian,mian,jiqiming,tiezhuangtai,address,
	 xingpian_name,gongliaoqi_name,baozhuang,user_qty,weizhi,created_by) 
			   values (
				   '".@$data[$i]['assembly_item_no']."',	
				   '".@$data[$i]['assembly_item_name']."',
				   '".@$data[$i]['component_item']."',		
				   '".@$data[$i]['youxian']."',					    
				   '".@$data[$i]['mian']."',  
				   '".@$data[$i]['jiqiming']."',
				   '".@$data[$i]['tiezhuangtai']."',
				   '".@$data[$i]['address']."',
				   '".@$data[$i]['xingpian_name']."',
				   '".@$data[$i]['gongliaoqi_name']."',
				   '".@$data[$i]['baozhuang']."',
				   '".@$data[$i]['user_qty']."',
				   '".@$data[$i]['weizhi']."',
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

echo '<a href="' . $RootPath . '/BOMSMTUpload2.php"><h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="BOMSMTUpload.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	   echo '<table><tr><div class="centre"><td><a href="' . $RootPath . '/upload/BomUploadSMTSample.xls" target="_blank">' . '下载标准格式文件' . '</td></tr></div></table>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' ._('选择需要上传的文件') . ': <input name="userfile" type="file" />
		<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>