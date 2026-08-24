<?php
  	include('includes/session.inc');
	$Title = _('查找业务员');

	$ViewTopic= '查找业务员';
	$BookMark = '查找业务员';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>/images/magnifier.png" title="查找业务员" alt="查找业务员">查找业务员</p>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	
	<table cellpadding="3" class="selection">
		<tr>
			<td>业务员编号：</td>
			<td><input type="text" name ="Salesmancode" value="<?=$_POST['Salesmancode']?>"></td>
			<td>业务员名称：</td> 
			<td><input type="text" name ="Salesmanname" value="<?=$_POST['Salesmanname']?>"></td>
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Search" value="查找">
						<input type="submit" name="Add" value="新增"></div>
</div>
</form>
<?php
	if (isset($_POST['Add'])) {
	prnMsg( _('即将跳转业务员新增界面'), 'error');
	header("Location: AddSalesman.php");
	}
?>

<?php
	if (isset($_POST['Search'])) {
		$sql = "select salesmancode,salesmanname,systemcode,smantel,identitycardid
		from salesmans";
		if (isset($_POST['Salesmancode']) and $_POST['Salesmancode']!= '') {
			$sql = $sql." where salesmancode like '%".$_POST['Salesmancode']."%' ";
		}
		if (isset($_POST['Salesmanname']) and $_POST['Salesmanname']!= '') {
			$sql = $sql." where salesmanname like '%".$_POST['Salesmanname']."%' ";
		}
		// echo $sql;
		$result=DB_query($sql, $db);
?>
<table class="selection">
	<tr>
		<th width="100" class="ascending">业务员编号</th>
		<th width="100">业务员名称</th>
		<th width="100">联系电话</th>
		<th width="100">身份证号</th>
		<th width="100">业务人员账号</th>
	</tr>
<?php
		$k=0;
		while($v=DB_fetch_array($result)){
			//$ScheduleDate = date('Y-m-d',$v['schedule_ship_date']);
			if ($k==1){
	            echo '<tr class="EvenTableRows">';
	            $k=0;
	        } else {
	            echo '<tr class="OddTableRows">';
	            $k=1;
	        }
?>		
		<?php 
		    
		?>
		<td><a href="<?=$RootPath?>/AddSalesman.php?SalesManCode=<?=$v['salesmancode']?> "><?=$v['salesmancode']?></td>
		<td><?=$v['salesmanname']?></td>
		<td><?=$v['smantel']?></td>
		<td><?=$v['identitycardid']?></td>
		<td><?=$v['systemcode']?></td>
	</tr>
<?php }?>
</table>
<?php }?>
<?php
  include('includes/footer.inc');
?>