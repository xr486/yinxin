<?php 
	include('includes/session.inc');
	$Title = _('完成生产确认');

	$ViewTopic= '完成生产确认';
	$BookMark = '完成生产确认';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="完成生产确认" alt="完成生产确认">完成生产确认</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table cellpadding="3" class="selection">
		<tr>
			<td>工单：</td>
			<td><input type="text" name ="OrderNumber" value="<?=$_POST['OrderNumber']?>"></td>
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Search" value="查找">
						<input type="submit" name="Sure" value="确认"></div>
</div>
</form>

<?php
	if (isset($_POST['Sure'])) {
		$sql = "Update sf_orders_all 
				set complete_flag = 'Y',
				    start_work_date = 2
				where order_number = '".$_POST['OrderNumber']."'";
		$result = DB_query($sql,$db);
		prnMsg( _('完工确认成功'), 'success');}
?>

<?php
	if (isset($_POST['Search'])) {
		$sql = "select order_number,order_quantity,start_work_date,end_work_date,operating_man from sf_orders_all";
		if (isset($_POST['OrderNumber']) and $_POST['OrderNumber']!= '') {
			$sql = $sql." and order_number like '%".$_POST['OrderNumber']."%' ";
		}
		// echo $sql;
		$result=DB_query($sql, $db);
?>

<table class="selection">
	<tr>
		<th width="100">工单</th>
		<th width="100">数量</th>
		<th width="100">开工时间</th>
		<th width="100">完工时间</th>
		<th width="100">负责人</th>
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
		<td><?=$v['order_number']?></td>
		<td><?=$v['order_quantity']?></td>
		<td><?=$v['start_work_date']?></td>
		<td><?=$v['end_work_date']?></td>
		<td><?=$v['wip_person']?></td>
	</tr>
<?php } ?>
</table>
<?php }?>
<?php
  include('includes/footer.inc');
?>