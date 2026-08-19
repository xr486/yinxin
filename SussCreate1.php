<?php 
	include('includes/session.inc');
	$Title = _('订单创建');
	$ViewTopic= '订单创建';
	$BookMark = '订单创建';
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
 require_once 'upload.class.php';
 if (isset($_GET['OrderNum'])) {
$_SESSION['OrderNum' . $identifier]=$_GET['OrderNum'];
 }
$msg = '订单编号'.$_SESSION['OrderNum' . $identifier].'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/AddNewOrder.php?New=Y">' . _('继续创建订单') . '</a></div>';
                 //   echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintSo.php?Updatedelivery_num='.$_SESSION['OrderNum' . $identifier].'" target="_blank"  >' . _('打印') . '</a></div>';
				   
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '为订单上传附件' .
 '" alt="" />' . ' ' .'为订单上传附件' . '
	</p>';
	echo "可以上传'pptx','docx','dotx','xlsx','ppt','xls','doc','pdf','7z','rar','zip','bmp','jpeg','jpg','png','gif'后缀的文件";

		$uploadflag = 1;
$_POST['ItemNo'];
	if (isset($_POST['Save'])) {
     $time = time();
$upload=new upload('Pic','SO');
$dest=$upload->uploadFile();

$sql = "insert into so_headers_all_file (file_name,order_number,file_patch,creation_date,created_by) values ('".$_POST['file_name']."','".$_POST['OrderNum1']."','".$dest."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
//echo $sql;
		 prnMsg( _('附件上传成功,还可以继续上传！'), 'success');

	}


?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST" enctype="multipart/form-data">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
<br>
<table class="selection">
	
     
	<tr>
	<td>附件名称：</td>
  <td ><input type="text"  required="required"  maxlength="200" size="20" name="file_name"  value="<?=$_POST['file_name']?>" /> </td>
		<td>上传附件：</td>
		<td><input type="file" required="required"  name="Pic"></td>
		
		<td><input  type="hidden" name="OrderNum1"   value="<?=$_SESSION['OrderNum' . $identifier]?>" size="8" maxlength="25"/> 
		 
	</tr>

	
</table>
</div>
<div class="centre">
	<input type="submit" name="Save" value="保存" >
</div>
</form>
<?php
  include('includes/footer.inc');
?>