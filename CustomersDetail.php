<?php
  	include('includes/session.inc');
	$Title = _('查找客户');

	$ViewTopic= '查找客户';
	$BookMark = '查找客户';

	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
?>

<?php
$CustomerNum=isset($_GET['CustomerNum'])?$_GET['CustomerNum']:$_POST['CustomerNum'];
	 

		$sql = "select * from customers where customer_code ='".$CustomerNum."' ";
		
		// echo $sql;
		$result=DB_query($sql, $db);
?>
<table class="selection">
	<tr>
		<th width="100" class="ascending">客户编号</th>
		<th width="100">客户名称</th>
		<th width="300">客户地址</th>
		<th width="100">联系人</th>
		<th width="100">联系电话</th>
		<th width="100">邮箱</th>
		<th width="100">QQ</th>
		<th width="100">业务人员代码</th>
		<th width="100">生效日期</th>
		<th width="100">失效日期</th>
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
		<td><a href="<?=$RootPath?>/CustomersDetail.php?CustomerNum=<?=$v['customer_code']?> "><?=$v['customer_code']?></td>
		<td><?=$v['customer_name']?></td>
		<td><?=$v['customer_address']?></td>
		<td><?=$v['customer_contacts']?></td>
		<td><?=$v['contacts_phone']?></td>
		<td><?=$v['contacts_mail']?></td>
		<td><?=$v['contacts_qq']?></td>
		<td><?=$v['salesmancode']?></td>
		<td><?=$v['effective_date']?></td>
		<td><?=$v['disable_date']?></td>
	</tr>
<?php }?>
</table>

<?php
  include('includes/footer.inc');
?>