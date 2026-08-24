<?php 
	include('includes/session.inc');
	$Title = _('问题反馈单');
	$ViewTopic= '问题反馈单';
	$BookMark = '问题反馈单';
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
 require_once 'upload.class.php';
 if (isset($_GET['OrderNum'])) {
$_SESSION['OrderNum' . $identifier]=$_GET['OrderNum'];
 }
$msg = '问题反馈单编号'.$_SESSION['OrderNum' . $identifier].'建立成功！';
                prnMsg($msg, success);
                 echo '<br /><div class="centre"><a href="' . $RootPath . '/ProblemFeedback.php?New=Y">' . _('继续创建问题反馈单') . '</a></div>';
                 //   echo '<br /><div class="centre"><a href="' . $RootPath . '/PrintSo.php?Updatedelivery_num='.$_SESSION['OrderNum' . $identifier].'" target="_blank"  >' . _('打印') . '</a></div>';


                 $sql2 = "SELECT * FROM so_qc_bad_all_file  where  order_number = '" .$_SESSION['OrderNum' . $identifier]."'";
        $result2 = DB_query($sql2, $db);
        if (DB_num_rows($result2) == 0) {
            unset($result2);
          //  prnMsg(_('无附件'), 'info');
        } else {
			echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('产品文件信息') .
 '" alt="" />' . ' ' . _('产品文件信息') . '
	</p>';
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name = "identifier" value ="' . $identifier . '">';
            echo '<div>';
            echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
            echo '<table class="selection" align="center" >';
            $tableheader = '<tr>
	                           
                                        <th width =100 >' . '附件名称' . '</th>
										
										<th width =150 >' . '上传时间' . '</th>
										<th width =80 >' . '上传人员' . '</th>
										
                                        <th  width =50>' . '下载' . '</th>
                                        <th  width =50>' . '删除' . '</th>
									 
                                       
                                       
				</tr>';
                       
            echo $tableheader;
            $RowCounter = 1;
            $k = 0; //row colour counter
            while ($myrow = DB_fetch_array($result2)) {
                if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="EvenTableRows">';
                    $k++;
                }
 
                echo '<td>' . $myrow['file_name'] . '</td>
		             
                      <td>' . date('Y-m-d h:i:s',$myrow['creation_date']) . '</td>
					  <td>' . $myrow['created_by'] . '</td>  
                          
					  <td><a href="' . $RootPath . '/' . $myrow['file_patch'] . '" target="_blank">' . '下载' . '</td>
					  <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?file_11patch=' .$myrow['file_patch'] .'&bom_header_id=' .$myrow['bom_header_id'] .'&bom_file_id=' .$myrow['bom_file_id'] . '&amp;delete=1" onclick="return confirm(\'' . _('是否要删除这个文件?') . '\');">' . _('删除')  . '</a></td>
                    
                        

        </tr>';
                $RowCounter++;
                If ($RowCounter == 500) {
                    $RowCounter = 1;
                    echo $tableheader;
                }
            }
            echo '</table> ';


            echo '</div>
          </form>';
        }
				   
                 echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . '为问题反馈单上传附件' .
 '" alt="" />' . ' ' .'为问题反馈单上传附件' . '
	</p>';
	echo "可以上传'pptx','docx','dotx','xlsx','ppt','xls','doc','pdf','7z','rar','zip','bmp','jpeg','jpg','png','gif'后缀的文件";

		$uploadflag = 1;
	if (isset($_POST['Save'])) {
     $time = time();
$upload=new upload('Pic','SO');
$dest=$upload->uploadFile();

$sql = "insert into so_qc_bad_all_file (file_name,order_number,file_patch,creation_date,created_by) values ('".$_POST['file_name']."','".$_POST['OrderNum1']."','".$dest."','".$time."','".$_SESSION['UserID']."')";
			$result = DB_query($sql,$db);
//echo $sql;
		 prnMsg( _('附件上传成功,还可以继续上传！'), 'success');
         echo '<meta http-equiv="refresh" content="0; url=' . $RootPath .'/SussCreateProblem.php?OrderNum='. $_POST['OrderNum1'] . '" />';

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