<?php
  	include('includes/session.inc');
	$Title = _('查找待确认订单');

	$ViewTopic= '查找待确认订单';
	$BookMark = '查找待确认订单';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>/images/magnifier.png" title="查找待确认订单" alt="查找待确认订单">查找待确认订单</p>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	
	<table cellpadding="3" class="selection">
		<tr>
			<td>客户代号：</td>
			<td><input type="text" name ="CustomerID" value="<?=$_POST['CustomerID']?>"></td>
			<td>订单编号：</td> 
			<td><input type="text" name ="OrderNumber" value="<?=$_POST['OrderNumber']?>"></td>			
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Search" value="查找"></div>
</div>
</form>
<?php
	if (isset($_POST['Search'])) {
		$sql = "SELECT
					order_number,
					customer_number,
					customer_name,
					b.realname,
					order_quantity,
					schedule_ship_date
				FROM sf_orders_all a,www_users b
				WHERE order_flag = 'Y'
				and b.userid=a.sales_man_id
				AND (
					ifnull(gerber_flag,'N') != 'Y'
					OR ifnull(coordinate_flag,'N') != 'Y'
					OR ifnull(bom_flag,'N') != 'Y'
					OR ifnull(point_flag,'N') != 'Y'
				) ";
		if (isset($_POST['CustomerID']) and $_POST['CustomerID']!= '') {
			$sql = $sql." and customer_number like '%".$_POST['CustomerID']."%' ";
		}
		if (isset($_POST['OrderNumber']) and $_POST['OrderNumber']!= '') {
			$sql = $sql." and order_number like '%".$_POST['OrderNumber']."%' ";
		}		
		// echo $sql;
		$result=DB_query($sql, $db);
?>
<table class="selection">
	<tr>
		<th width="100" class="ascending">订单编号</th>
		<th width="100">客户代号</th>
		<th width="100">业务人员</th>
		<th width="100">订单数量</th>
		<th width="100">预计出货时间</th>	
	</tr>
<?php
		$k=0;
		while($v=DB_fetch_array($result)){
			$ScheduleDate = date('Y-m-d',$v['schedule_ship_date']);
			if ($k==1){
	            echo '<tr class="EvenTableRows">';
	            $k=0;
	        } else {
	            echo '<tr class="OddTableRows">';
	            $k=1;
	        }
?>		
		<td><a href="<?=$RootPath?>/ProductionInfoConfirm.php?OrderNum=<?=$v['order_number']?> "><?=$v['order_number']?></td>
		<td><?=$v['customer_number']?></td>
		<td><?=$v['realname']?></td>
		<td><?=$v['order_quantity']?></td>
		<td><?=$ScheduleDate?></td>
	</tr>
<?php } ?>
</table>
<?php }?>
<?php
  include('includes/footer.inc');
?>