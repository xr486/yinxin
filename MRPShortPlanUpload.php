<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('MRP建议采购资料上传');
$ViewTopic = 'MRP建议采购资料上传';
$BookMark = 'MRP建议采购资料上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('MRP建议采购资料上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from mrp_short_import_temp  where create_by='".$_SESSION['UserID']."'";
		// echo $sql;
		 
	    $result = DB_query($sql3,$db);

$excel = new Excel();       
 
$xls = $_FILES['userfile']['tmp_name']; 

$excel->setOutputEncoding('utf-8');
 
$excel->read($xls); 	

$arr = $excel->sheets[0]['cells']; 
$i = 0; 
$time = time();
foreach($arr as $arry=>$row){
    if($arry>1){
	  if ($row['4']=='') {
        
	  }
        
		@$data[$i]['create_by'] =$_SESSION['UserID'];
		@$data[$i]['item_no'] = $row['1'];  //工作单号 gzdh 
		@$data[$i]['book_order_date'] = $row['5'];  
		@$data[$i]['quantity'] = $row['6'];   
		
		 $sql = "insert into mrp_short_import_temp (item_no,book_order_date,quantity,create_by,creation_date) values ('".@$data[$i]['item_no']."',
		 '".@$data[$i]['book_order_date']."',
		 '".@$data[$i]['quantity']."',
		 '".@$data[$i]['create_by']."',
		 '".$time."'
		 )";
		 $result = DB_query($sql,$db);
		 
		//echo $sql;
	}
	$i++;
}

 

//echo '上传完成' ;
//exit;
unset($_SESSION['Request']);
   echo '<h3>' . _('已上传完成，您可以进界面查询') . '</h3>';

  echo '<a href="' . $RootPath . '/MRPShortPlanUploadConfirm.php"><h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="MRPShortPlanUpload.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			_('上传文件') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>