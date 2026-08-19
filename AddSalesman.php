<?php 
	include('includes/session.inc');
	$Title = _('建立业务员');

	$ViewTopic= '建立业务员';
	$BookMark = '建立业务员';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['SalesManCode']) or isset($_POST['SalesManCode'])) {
		if (!isset($_POST['SalesManCode'])) {
			$SalesManCode=isset($_GET['SalesManCode'])?$_GET['SalesManCode']:$_POST['SalesManCode'];
			$sql = "select salesmanname,effective_date,disable_date,identitycardid,systemcode ,salesmancode,smantel from salesmans where salesmancode ='".$SalesManCode."' ";
			$result = DB_query($sql,$db);
			while ($v=DB_fetch_array($result)) {
				$_POST['SalesManName'] = $v['salesmanname'];
				$_POST['EffDate'] = date('Y-m-d',$v['effective_date']);
				$_POST['DisDate'] = date('Y-m-d',$v['disable_date']);
				$_POST['IdentityCardid'] = $v['identitycardid'];
				$_POST['SystemCode'] = $v['systemcode'];
				$_POST['SalesManCode'] = $v['salesmancode'];
				$_POST['Smantel'] = $v['smantel'];
			}
		}
	}
   
	if (isset($_POST['Confirm'])) {
	  //echo 'CC';
	 // echo $_POST['SystemCode'];
	    if (isset($_GET['IdentityCardid']) or isset($_POST['IdentityCardid'])) {
	    $v_date = strtotime(Date('Y-m-d H:i:s'));
	    $sql = "select identitycardid from salesmans where identitycardid = '".$_POST['IdentityCardid']."' and (disable_date is null or disable_date > $v_date)";
	    $result = DB_query($sql,$db);
		$rownum = DB_num_rows($result);
		if ($rownum <> 0){
		prnMsg( _('已存在该身份证'), 'error');
		} 
		else
		{
	  $Eff_date = strtotime($_POST['EffDate']);
	  $Dis_date = strtotime($_POST['DisDate']);
		$sql = "insert into salesmans
		              (salesmancode,
		               salesmanname,
		               systemcode,
		               smantel,
		               identitycardid,
		               effective_date,
		               disable_date)
		        values('".$_POST['SalesManCode']."',
		               '".$_POST['SalesManName']."',
		               '".$_POST['SystemCode']."',
		               '".$_POST['Smantel']."',
		               '".$_POST['IdentityCardid']."',
		               '".$Eff_date."',
		               '".$Dis_date."'
		               )";
		$result = DB_query($sql,$db);
		prnMsg( _('业务员建立成功'), 'success');
		}
		}
	}

	if (isset($_POST['Update'])) {
	    if (isset($_GET['IdentityCardid']) or isset($_POST['IdentityCardid'])) {
	    $v_date = strtotime(Date('Y-m-d H:i:s'));
	    $sql = "select identitycardid from salesmans where identitycardid = '".$_POST['IdentityCardid']."' and (disable_date is null or disable_date > $v_date)";
	    $result = DB_query($sql,$db);
		$rownum = DB_num_rows($result);
		if ($rownum <> 0){
		prnMsg( _('已存在该身份证'), 'error');
		} 
		else
		{

        $Eff_date = strtotime($_POST['EffDate']);
	    $Dis_date = strtotime($_POST['DisDate']);
		$sql = "Update salesmans 
				set salesmanname = '".$_POST['SalesManName']."',
					Smantel = '".$_POST['Smantel']."',
					IdentityCardid = '".$_POST['IdentityCardid']."',
					SystemCode = '".$_POST['SystemCode']."',
					salesmancode = '".$_POST['SalesManCode']."',
					effective_date = '".$Eff_date."',
					disable_date = '".$Dis_date."'
				where salesmancode = '".$_POST['SalesManCode']."'";
		$result = DB_query($sql,$db);
		prnMsg( _('业务员更新成功'), 'success');
		}
		}
	}	


?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="建立业务员" alt="建立业务员">建立业务员</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table class="selection">
		<tr>
			<td>业务员：</td>
			<td><input type="text" name="SalesManName" value="<?=$_POST['SalesManName']?>"></td>
		</tr>
		<tr>
			<td>联系电话：</td>
			<td><input type="text" name="Smantel" value="<?=$_POST['Smantel']?>"></td>
		</tr>
		<tr>
			<td>身份证账号：</td>
			<td><input type="text" name="IdentityCardid" value="<?=$_POST['IdentityCardid']?>"></td>
		</tr>
		<tr>
			<td>业务人员账号：</td>
			<td><select name="SystemCode" id="">
				<?php
				   	$sql = "SELECT userid FROM www_users";
					$result=DB_query($sql, $db); 

					while($v=DB_fetch_array($result)){
						if ($v['userid']==$_POST['SystemCode']) {
				?>
				<option value="<?=$v['userid']?>" selected="selected"><?=$v['userid']?>
				</option>
				<?php }else { ?>
				<option value="<?=$v['userid']?>"><?=$v['userid']?></option>
				<?php }} ?>
			</select></td>
		</tr>

		<tr>
			<td>业务员代码：</td>
			<?php
				if (isset($_GET['SalesManCode']) or isset($_POST['SalesManCode'])) {
			?>
				    <td><?=$_POST['SalesManCode']?></td>
				 	<input type="hidden" name="SalesManCode" value="<?=$_POST['SalesManCode']?>">
			<?php	 	
				 } else{
			?>
				<td><input type="text" name="SalesManCode" value="<?=$_POST['SalesManCode']?>"></td>
			<?php } ?>
		</tr>

		<tr>
			<td>生效日期：</td>
			<td><input type="text" onfocus="WdatePicker()" name="EffDate" 
			value="<?=$_POST['EffDate']?>"></td>
		</tr>
		<tr>
			<td>失效日期：</td>
			<td><input type="text" onfocus="WdatePicker()" name="DisDate"
			 value="<?=$_POST['DisDate']?>"></td>
		</tr>
		
	</table>
</div>
<?php
		 if (isset($_GET['SystemManCode'])) {
?>
<div class="centre"><input type="submit" name="Return" value="返回">
<input type="submit" name="Update" value="更新"></div>

<?php
    }else{
?>
<div class="centre"><input type="submit" name="Confirm" value="提交">
<input type="submit" name="Update" value="更新"></div>
<?php
}?>
</form>

<?php
  include('includes/footer.inc');
?>