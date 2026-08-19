<?php 
header("Content-Type:text/html;charset=utf-8");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
set_time_limit(0); 
ob_start();
 date_default_timezone_set ('Asia/Shanghai');
ob_start();
include('includes/session.inc');
$Title = _('供应商整批上传');
$ViewTopic = '供应商整批上传';
$BookMark = '供应商整批上传';
include('includes/header.inc');
//include('includes/SQL_CommonFunctions.inc');
include("excel/excel.php"); 

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('供应商整批上传') .
 '" alt="" />' . ' ' . $Title . '</p>';



if (isset($_FILES['userfile']) and $_FILES['userfile']['tmp_name']) { //start file processing

	 $sql3 = "delete from vendors_upload  where created_by='".$_SESSION['UserID']."'";
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
	   
	
 //echo $heji;
///echo strtotime($heji);
		@$data[$i]['created_by'] =$_SESSION['UserID'];
		@$data[$i]['vendor_code'] = $row['1'];
		@$data[$i]['vendor_name'] = $row['2'];
		@$data[$i]['vendor_contacts'] = $row['3'];
		
		@$data[$i]['contacts_phone'] = $row['4'];
		@$data[$i]['contacts_mail'] = $row['5'];
		@$data[$i]['contacts_fax'] = $row['6'];
		@$data[$i]['vendor_address'] = $row['7'];
		@$data[$i]['bank_name'] = $row['8'];
		@$data[$i]['bank_address'] = $row['9'];
		@$data[$i]['bank_account'] = $row['10'];
		@$data[$i]['taxpayerid'] = $row['11'];
		
		
		
	 
 
	 

		 $sql = "insert into vendors_upload(vendor_code,vendor_name,vendor_contacts,contacts_phone,
		 contacts_mail,contacts_fax,vendor_address,bank_name,bank_address,bank_account,taxpayerid
		 
		 
		 ,created_by) 
						values (
								'".@$data[$i]['vendor_code']."', 
							  '".@$data[$i]['vendor_name']."',
							  '".@$data[$i]['vendor_contacts']."',
								'".@$data[$i]['contacts_phone']."', 
								'".@$data[$i]['contacts_mail']."', 
								'".@$data[$i]['contacts_fax']."', 
								'".@$data[$i]['vendor_address']."', 
								'".@$data[$i]['bank_name']."', 
								'".@$data[$i]['bank_address']."', 
								'".@$data[$i]['bank_account']."', 
								'".@$data[$i]['taxpayerid']."', 


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

  echo '<a href="' . $RootPath . '/SearchSupplierupload2.php"><h3>' . _('进行上传资料确认界面') . '</h3>';
  
}  
 else  {	echo '<form action="SearchSupplierupload.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<table><tr><div class="centre"><td><a href="' . $RootPath . '/upload/POvendorSample.xls" target="_blank">' . '下载标准格式文件' . '</td></tr></div></table>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			_('选择需要上传的文件') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . _('确认上传') . '" />
        </div>
		</form>'; 
		}
include('includes/footer.inc');

 
?>