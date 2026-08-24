<?php 
	include('includes/session.inc');
	if (isset($_GET['CustomerNum']) or isset($_POST['CustomerNum'])) {
	$Title = _('生产人员维护');

	$ViewTopic= '生产人员维护';
	$BookMark = '生产人员维护';}
	else{
	$Title = _('生产人员建立');

	$ViewTopic= '生产人员建立';
	$BookMark = '生产人员建立';
	}
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_POST['Confirm'])) {

		if(mb_strlen($_POST['WorkName']) == 0){
		prnMsg( _('请输入生产人员'), 'error');
		}
		else{
    	$sql = "SELECT COUNT(work_name) FROM workperson WHERE work_name='" . $_POST['WorkName'] . "'";
    	$result = DB_query($sql, $db);	
    	$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0){
		prnMsg( _('生产人员已存在'), 'error');
		}										
		else{
		//$Dis_date = strtotime($_POST['DisDate']);
		//$Eff_date = strtotime($_POST['EffDate']);
		$Eff_date = strtotime(Date('Y-m-d H:i:s'));
		$sql_a = "insert into workperson(work_name,effective_date)values('".$_POST['WorkName']."','".$Eff_date."')";
		$result_a = DB_query($sql_a,$db);
		prnMsg( _('生产人员建立成功'), 'success');
        unset($_POST['WorkName']);
        unset($_POST['CustomerName']);       
        }
	}
}

?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="生产人员建立" alt="生产人员建立">客户资料</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table class="selection">
		<tr>
			<td>生产人员：</td>
			<td><input type="text" name="WorkName"  size="20" value="<?=$_POST['WorkName']?>"></td>
		</tr>
		<tr>
			<td>建立时间：</td>
			<td><?=date('Y-m-d H:i:s')?></td>
			<td><input type="hidden" name="EffDate" size="20" value="<?=date('Y-m-d H:i:s')?>"></td>
	</table>
		</tr>		
			<div class="centre">
			<input type="submit" name="Confirm" value="保存">
			</div>
</form>

<?php
  include('includes/footer.inc');
?>