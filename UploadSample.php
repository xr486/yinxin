<?php 
	include('includes/session.inc');
	$Title = _('文件上传范例');
	$ViewTopic= '文件上传范例';
	$BookMark = '文件上传范例';
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
 require_once 'upload.class.php';
 
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '为报价申请上传附件' .
 '" alt="" />' . ' ' .'为报价申请上传附件' . '
	</p>';
	echo "可以上传'pptx','docx','dotx','xlsx','ppt','xls','doc','pdf','7z','rar','zip','bmp','jpeg','jpg','png','gif'后缀的文件";

		$uploadflag = 1;
$_POST['ItemNo'];
	if (isset($_POST['Save'])) {
     $time = time();
$upload=new upload('Pic','SO');
$dest=$upload->uploadFile();

$sql = "insert into upload_all_file (file_name,file_patch,creation_date,created_by) values ('".$_POST['file_name']."','".$dest."','".$time."','".$_SESSION['UserID']."')";
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