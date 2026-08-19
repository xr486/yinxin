<?php 
	include('includes/session.inc');
	$Title = _('客户资料确认');

	$ViewTopic= '客户资料确认';
	$BookMark = '客户资料确认';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>//images/transactions.png" title="客户资料确认" alt="客户资料确认">客户资料确认</p>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	<table cellpadding="3" class="selection">
		<tr>
			<td>订单：</td>
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
				set order_flag = 'Y'
				where order_number = '".$_POST['OrderNumber']."'";
		$result = DB_query($sql,$db);
		prnMsg( _('客户资料确认成功'), 'success');}
?>

<?php
	if (isset($_POST['Search'])) {
		$sql = "select order_number,order_quantity,customer_number,customer_name,sales_man_name,unit_price from sf_orders_all";
		if (isset($_POST['OrderNumber']) and $_POST['OrderNumber']!= '') {
			$sql = $sql." and order_number like '%".$_POST['OrderNumber']."%' ";
		}
		// echo $sql;
		$result=DB_query($sql, $db);
?>

<table class="selection">
	<tr>
		<th width="100">订单</th>
		<th width="100">客户编号</th>
		<th width="100">客户名称</th>
		<th width="100">联系人</th>
		<th width="100">数量</th>
		<th width="100">单价</th>
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
		<td><?=$v['customer_number']?></td>
		<td><?=$v['customer_name']?></td>
		<td><?=$v['sales_man_name']?></td>
		<td><?=$v['order_quantity']?></td>
		<td><?=$v['unit_price']?></td>		
	</tr>
<?php } ?>
</table>
<?php }?>
<?php
  include('includes/footer.inc');
?>