<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');

include('includes/session.inc');
$Title = _('产品工艺上传');
$ViewTopic = '产品工艺上传';
$BookMark = '产品工艺上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('产品工艺上传') .
 '" alt="" />' . ' ' . $Title . '</p>';

 $item_id=$_GET['UpdateBOMItem'];
if (isset($item_id)  ) {
	$sql4="select b.*  from sf_item_no b  where   b.item_id='".$item_id."'";
	$result4 = DB_query($sql4,$db);
	$myrow4 = DB_fetch_array($result4);
}


if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from bom_routings_upload  where created_by='".$_SESSION['UserID']."'";
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

	   @$data[$i]['created_by'] =$_SESSION['UserID'];
	   @$data[$i]['operation_seq_num'] = $row['1'];
	   @$data[$i]['operation_code'] = $row['2']; 
	   @$data[$i]['remarks'] = $row['3'];  
	   @$data[$i]['rate'] = $row['4'];
	   @$data[$i]['channeng'] = $row['5'];   
	   if (@$data[$i]['operation_seq_num']>0 and @$data[$i]['operation_seq_num']<10000) {
	     if (@$data[$i]['operation_code']<>'') {

			   $sql2 = "select * from  bom_routings_upload  where assembly_item_no ='".$_POST['assembly_item_no']."'
			   and operation_seq_num = '".@$data[$i]['operation_seq_num']."'  
			   and  created_by = '".@$data[$i]['created_by']."'  ";
			    $result2 = DB_query($sql2,$db);
				if (DB_num_rows($result2)==0) {


	      $sql = "insert into bom_routings_upload (assembly_item_no,bom_header_id,operation_seq_num,operation_code,rate,channeng,remarks,created_by) 
			   values ( 
				   '".$_POST['assembly_item_no']."',
				   '".$_POST['item_id']."',
				   '".@$data[$i]['operation_seq_num']."', 
				   '".@$data[$i]['operation_code']."',
				   '".@$data[$i]['rate']."',
				   '".@$data[$i]['channeng']."',
				   '".@$data[$i]['remarks']."',
				   '".@$data[$i]['created_by']."' 
			   )";
			//   echo $sql;
			    $result = DB_query($sql,$db);
				}
	      }
	   }  
	  
		 
		 //echo $sql;
	}
	$i++;
}

 

//echo '上传完成' ;
//exit;
unset($_SESSION['Request']);
echo '<h3>' . _('您已完成 '.$line.' 笔资料上传，请进入确认界面保存资料') . '</h3>';

echo ' 
<a href="' . $RootPath . '/BOMRoutes5.php?UpdateBOMItem=' . $_POST['item_id']. '" >  <h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="BOMRoutes4.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	   echo '<table><tr><div class="centre"><td><a href="' . $RootPath . '/upload/RouteUploadSample.xls" target="_blank">' . '下载标准格式文件' . '</td></tr></div></table>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden"  name="item_id"  readonly="readonly" value="' .  $_GET['UpdateBOMItem']. '" />  ';
echo '<div class="text-nav"> 
<div class="text-nav-1"><div>' . _('料号') . ':</div>
<input type="text" name="assembly_item_no"  readonly="readonly" value="' .  $myrow4['item_no']. '" /> </div>
<div class="text-nav-1"><div>' . _('料号名称') . ':</div>
<input type="text" name="assembly_item_name"  readonly="readonly" value="' .  $myrow4['item_name']. '" /> 
</div>
<div class="text-nav-1"><div>' . _('规格型号') . ':</div>
<input type="text" name="item_desc"  readonly="readonly" value="' .  $myrow4['item_desc']. '" /> 
</div> '; 
 	 	

	echo '</div><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' ._('选择需要上传的文件') . ': <input name="userfile" type="file" />
		<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>