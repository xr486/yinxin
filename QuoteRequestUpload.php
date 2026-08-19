<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('报价单整批上传');
$ViewTopic = '报价单整批上传';
$BookMark = '报价单整批上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('报价单整批上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from quote_lines_upload  where created_by='".$_SESSION['UserID']."'";
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
	  if ($row['2']=='') {
        
	  }
       $line=$line+1; 
		$ri=substr($row['5'],0,2);
	 $yue=substr($row['5'],3,2);
	 $nian= substr($row['5'],6,4);
	 $heji= $nian.'/'.$yue.'/'.$ri;	

		@$data[$i]['created_by'] =$_SESSION['UserID'];
		@$data[$i]['item_no'] = $row['1'];
		@$data[$i]['item_name'] = $row['2'];
		@$data[$i]['item_desc'] = $row['3'];
		@$data[$i]['need_remark'] = $row['4'];  
		@$data[$i]['uom'] = $row['5']; 
		@$data[$i]['need_qty'] = $row['6'];  
		@$data[$i]['price'] = $row['6'];  
 
	 

		 $sql = "insert into quote_lines_upload(item_no,item_name,item_desc,need_remark,uom,
		 need_qty,price,created_by) 
						values (
								'".@$data[$i]['item_no']."',
								'".@$data[$i]['item_name']."',
							  '".@$data[$i]['item_desc']."',
								'".@$data[$i]['need_remark']."',
								'".@$data[$i]['uom']."',
								'".@$data[$i]['need_qty']."',
								'".@$data[$i]['price']."',
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

  echo '<a href="' . $RootPath . '/QuoteRequestUpload2.php"><h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="QuoteRequestUpload.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			_('选择需要上传的文件') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>