<?php
  	include('includes/session.inc');
	$Title = _('查询生产日报');

	$ViewTopic= '查询生产日报';
	$BookMark = '查询生产日报';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>
<p class="page_title_text"><img src="<?php echo $RootPath; ?>/css/<?php echo $Theme; ?>/images/magnifier.png" title="查询生产日报" alt="查询生产日报">查询生产日报</p>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method ="POST">
<div>
	<input type="hidden" name="FormID" value = "<?php echo $_SESSION['FormID']; ?>">
	
	<table cellpadding="3" class="selection">
		<tr>
			<td>订单编号：</td>
			<td><input type="text" name ="OrderNumber" value="<?=$_POST['OrderNumber']?>"></td>
			<td>客户代号：</td>
			<td><input type="text" name ="CustomerID" value="<?=$_POST['CustomerID']?>"></td>
			<td>负责人：</td> 
			<td><input type="text" name ="OperatingMan" value="<?=$_POST['OperatingMan']?>"></td>		
		</tr>
	</table>
	<div class="centre"><input type="submit" name="Search" value="查找"></div>
</div>
</form>

<?php
	if (isset($_POST['Add'])) {
	prnMsg( _('即将跳转生产日报填写界面'), 'error');
	header("Location: WorkReportConfirm.php");
	}
?>

<?php
	if (isset($_POST['Search'])) {
		$sql = "select sf.order_number,sf.customer_name,wt.operating_man,wt.complete_quantity,wt.scrap_quantity,wt.time_issued,sf.start_work_date,sf.end_work_date from wip_transactions wt,sf_orders_all sf where ifnull(sf.work_flag,'N') = 'Y' and sf.order_number = wt.order_number";
		if (isset($_POST['OrderNumber']) and $_POST['OrderNumber']!= '') {
			$sql = $sql." and sf.order_number like '%".$_POST['OrderNumber']."%' ";
		}
		if (isset($_POST['OperatingMan']) and $_POST['OperatingMan']!= '') {
			$sql = $sql." and wt.operating_man like '%".$_POST['OperatingMan']."%' ";
		}
		if (isset($_POST['CustomerID']) and $_POST['CustomerID']!= '') {
			$sql = $sql." and wt.customer_number like '%".$_POST['CustomerID']."%' ";
		}	
		$result=DB_query($sql, $db);
?>
<table class="selection">
	<tr>
		<th width="100" class="ascending">订单编号</th>
		<th width="100">负责人</th>
		<th width="100">完工数量</th>
		<th width="100">报废数量</th>
		<th width="100">生产用时(H)</th>
		<th width="100">开工时间</th>
		<th width="100">完工时间</th>
	</tr>
<?php
		$k=0;
		while($v=DB_fetch_array($result)){
			if (mb_strlen($v['start_work_date']) <> 0){
			$v['start_work_date'] = date('Y-m-d H:i:s',$v['start_work_date']);
			}
			if (mb_strlen($v['end_work_date']) <> 0){
			$v['end_work_date'] = date('Y-m-d H:i:s',$v['end_work_date']);
			}
			if ($k==1){
	            echo '<tr class="EvenTableRows">';
	            $k=0;
	        } else {
	            echo '<tr class="OddTableRows">';
	            $k=1;
	        }
?>		
		<td><a href="<?=$RootPath?>/WorkDailyDetail.php?OrderNum=<?=$v['order_number']?> "><?=$v['order_number']?></td>
		<td><?=$v['operating_man']?></td>
		<td><?=$v['complete_quantity']?></td>
		<td><?=$v['scrap_quantity']?></td>
		<td><?=$v['time_issued']?></td>
		<td><?=$v['start_work_date']?></td>
		<td><?=$v['end_work_date']?></td>		
	</tr>
<?php }?>
</table>
<?php }?>
<?php
  include('includes/footer.inc');
?>